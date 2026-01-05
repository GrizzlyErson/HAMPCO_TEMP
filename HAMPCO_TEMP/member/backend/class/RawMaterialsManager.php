<?php
class RawMaterialsManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function calculateRequiredMaterials($product_name, $weight_g) {
        $materials = [];
        
        switch ($product_name) {
            case 'Knotted Liniwan':
                // For Knotted Liniwan, we need 122% of the target weight in Piña Loose (Liniwan/Washout)
                $materials[] = [
                    'name' => 'Piña Loose',
                    'category' => 'Liniwan/Washout',
                    'amount' => $weight_g * 1.22 // 122% of target weight
                ];
                break;
                
            case 'Knotted Bastos':
                // For Knotted Bastos, we need 122% of the target weight in Piña Loose (Bastos)
                $materials[] = [
                    'name' => 'Piña Loose',
                    'category' => 'Bastos',
                    'amount' => $weight_g * 1.22 // 122% of target weight
                ];
                break;
                
            case 'Warped Silk':
                // For Warped Silk, we need 120% of the target weight in Silk
                $materials[] = [
                    'name' => 'Silk',
                    'category' => '',  // Silk has no category
                    'amount' => $weight_g * 1.20 // 120% of target weight
                ];
                break;
        }
        
        return $materials;
    }

    public function deductMaterials($materials) {
        try {
            $this->db->conn->begin_transaction();

            foreach ($materials as $material) {
                // Check if we have enough stock - handle both with and without category
                if (empty($material['category'])) {
                    $check_stock_query = "SELECT rm_quantity FROM raw_materials 
                                        WHERE raw_materials_name = ? 
                                        AND (category IS NULL OR category = '')
                                        AND rm_quantity >= ?";
                    $stmt = $this->db->conn->prepare($check_stock_query);
                    $stmt->bind_param("sd", 
                        $material['name'],
                        $material['amount']
                    );
                } else {
                    $check_stock_query = "SELECT rm_quantity FROM raw_materials 
                                        WHERE raw_materials_name = ? 
                                        AND category = ? 
                                        AND rm_quantity >= ?";
                    $stmt = $this->db->conn->prepare($check_stock_query);
                    $stmt->bind_param("ssd", 
                        $material['name'],
                        $material['category'],
                        $material['amount']
                    );
                }
                
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception("Insufficient stock for {$material['name']}" . 
                        (!empty($material['category']) ? " ({$material['category']})" : ""));
                }

                // Deduct the materials - handle both with and without category
                if (empty($material['category'])) {
                    $update_query = "UPDATE raw_materials 
                                   SET rm_quantity = rm_quantity - ? 
                                   WHERE raw_materials_name = ? 
                                   AND (category IS NULL OR category = '')";
                    $stmt = $this->db->conn->prepare($update_query);
                    $stmt->bind_param("ds", 
                        $material['amount'],
                        $material['name']
                    );
                } else {
                    $update_query = "UPDATE raw_materials 
                                   SET rm_quantity = rm_quantity - ? 
                                   WHERE raw_materials_name = ? 
                                   AND category = ?";
                    $stmt = $this->db->conn->prepare($update_query);
                    $stmt->bind_param("dss", 
                        $material['amount'],
                        $material['name'],
                        $material['category']
                    );
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to deduct {$material['name']} from inventory");
                }

                // Add stock history record - handle both with and without category
                if (empty($material['category'])) {
                    $stock_history_query = "INSERT INTO stock_history 
                        (stock_user_type, stock_raw_id, stock_user_id, stock_type, stock_outQty, stock_changes) 
                        SELECT 'member', 
                               id, 
                               ?, 
                               'Stock Out', 
                               ?, 
                               CONCAT(rm_quantity + ?, ' -> ', rm_quantity) 
                        FROM raw_materials 
                        WHERE raw_materials_name = ? 
                        AND (category IS NULL OR category = '')";
                    
                    $stmt = $this->db->conn->prepare($stock_history_query);
                    $stmt->bind_param("idds",
                        $_SESSION['id'],
                        $material['amount'],
                        $material['amount'],
                        $material['name']
                    );
                } else {
                    $stock_history_query = "INSERT INTO stock_history 
                        (stock_user_type, stock_raw_id, stock_user_id, stock_type, stock_outQty, stock_changes) 
                        SELECT 'member', 
                               id, 
                               ?, 
                               'Stock Out', 
                               ?, 
                               CONCAT(rm_quantity + ?, ' -> ', rm_quantity) 
                        FROM raw_materials 
                        WHERE raw_materials_name = ? 
                        AND category = ?";
                    
                    $stmt = $this->db->conn->prepare($stock_history_query);
                    $stmt->bind_param("iddss",
                        $_SESSION['id'],
                        $material['amount'],
                        $material['amount'],
                        $material['name'],
                        $material['category']
                    );
                }

                if (!$stmt->execute()) {
                    throw new Exception("Failed to record stock history for {$material['name']}");
                }
            }

            $this->db->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->db->conn->rollback();
            throw $e;
        }
    }
}
?> 