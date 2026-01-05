<?php
require_once __DIR__ . '/../../function/connection.php';

class RawMaterialCalculator {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function calculateMaterialsNeeded($productName, $quantity, $length = null, $width = null, $weight = null) {
        try {
            $materials = [];
            
            // For Piña Seda and Pure Piña Cloth (calculations per 1m x 30in)
            if ($productName === 'Piña Seda') {
                // Calculate based on length (materials needed per meter)
                if ($length !== null) {
                    // Calculate for Knotted Bastos (15g per meter)
                    $requiredKnottedBastos = 15 * floatval($length) * intval($quantity);
                    $materials[] = [
                        'name' => 'Knotted Bastos',
                        'category' => null,
                        'amount' => round($requiredKnottedBastos, 2),
                        'unit' => 'g'
                    ];
                    
                    // Calculate for Warped Silk (7g per meter)
                    $requiredWarpedSilk = 7 * floatval($length) * intval($quantity);
                    $materials[] = [
                        'name' => 'Warped Silk',
                        'category' => null,
                        'amount' => round($requiredWarpedSilk, 2),
                        'unit' => 'g'
                    ];
                }
            }
            else if ($productName === 'Pure Piña Cloth') {
                // Calculate based on length (materials needed per meter)
                if ($length !== null) {
                    // Calculate for Knotted Liniwan (22g per meter)
                    $requiredKnottedLiniwan = 22 * floatval($length) * intval($quantity);
                    $materials[] = [
                        'name' => 'Knotted Liniwan',
                        'category' => null,
                        'amount' => round($requiredKnottedLiniwan, 2),
                        'unit' => 'g'
                    ];
                }
            }
            // For Knotted products (based on weight)
            else if ($productName === 'Knotted Liniwan' && $weight !== null) {
                $requiredPinaLoose = 1.22 * floatval($weight) * intval($quantity);
                $materials[] = [
                    'name' => 'Piña Loose',
                    'category' => 'Liniwan/Washout',
                    'amount' => round($requiredPinaLoose, 2),
                    'unit' => 'g'
                ];
            }
            else if ($productName === 'Knotted Bastos' && $weight !== null) {
                $requiredPinaLoose = 1.22 * floatval($weight) * intval($quantity);
                $materials[] = [
                    'name' => 'Piña Loose',
                    'category' => 'Bastos',
                    'amount' => round($requiredPinaLoose, 2),
                    'unit' => 'g'
                ];
            }
            else if ($productName === 'Warped Silk' && $weight !== null) {
                $requiredSilk = 1.2 * floatval($weight) * intval($quantity);
                $materials[] = [
                    'name' => 'Silk',
                    'category' => null,
                    'amount' => round($requiredSilk, 2),
                    'unit' => 'g'
                ];
            }
            
            return [
                'success' => true,
                'materials' => $materials
            ];
            
        } catch (Exception $e) {
            error_log("Error calculating materials needed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkMaterialAvailability($materialName, $category, $amountNeeded) {
        // For processed materials (Knotted Bastos, Warped Silk, etc)
        if (in_array($materialName, ['Knotted Bastos', 'Warped Silk', 'Knotted Liniwan'])) {
            $query = "SELECT weight as available_quantity FROM processed_materials 
                     WHERE processed_materials_name = ? 
                     AND status = 'Available'";
            $stmt = $this->db->conn->prepare($query);
            $stmt->bind_param("s", $materialName);
        } else {
            // For raw materials (like Silk), check the raw_materials table
            if ($category === null) {
                $query = "SELECT rm_quantity as available_quantity FROM raw_materials 
                         WHERE raw_materials_name = ? 
                         AND (category IS NULL OR category = '')
                         AND rm_status = 'Available'";
                $stmt = $this->db->conn->prepare($query);
                $stmt->bind_param("s", $materialName);
            } else {
                $query = "SELECT rm_quantity as available_quantity FROM raw_materials 
                         WHERE raw_materials_name = ? 
                         AND category = ?
                         AND rm_status = 'Available'";
                $stmt = $this->db->conn->prepare($query);
                $stmt->bind_param("ss", $materialName, $category);
            }
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Material not found: $materialName" . ($category ? " ($category)" : ""));
        }

        $row = $result->fetch_assoc();
        $availableQuantity = $row['available_quantity'];

        if ($availableQuantity < $amountNeeded) {
            throw new Exception(sprintf(
                "Insufficient stock for %s%s. Required: %.0fg, Available: %.0fg",
                $materialName,
                $category ? " ($category)" : "",
                $amountNeeded,
                $availableQuantity
            ));
        }

        return true;
    }

    public function validateMaterialAvailability($productName, $quantity, $length = null, $width = null, $weight = null) {
        // Calculate required materials without deducting
        $result = $this->calculateMaterialsNeeded($productName, $quantity, $length, $width, $weight);
        
        if (!$result['success']) {
            throw new Exception($result['error'] ?? 'Failed to calculate required materials');
        }
        
        // Check availability for each material
        foreach ($result['materials'] as $material) {
            $this->checkMaterialAvailability(
                $material['name'],
                $material['category'],
                $material['amount']
            );
        }
        
        return true;
    }
} 