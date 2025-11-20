<?php


include ('dbconnect.php');

date_default_timezone_set('Asia/Manila');

class global_class extends db_connect
{
    public function __construct()
    {
        $this->connect();
    }

    public function ProcessedStockOut($user_id, $material_name, $amount)
    {
        // Step 1: Get current weight
        $query = $this->conn->prepare("SELECT id, weight, processed_materials_name FROM processed_materials WHERE processed_materials_name = ? AND status = 'Available'");
        $query->bind_param("s", $material_name);
        $query->execute();
        $result = $query->get_result();
        $material = $result->fetch_assoc();
        $query->close();

        if (!$material) {
            throw new Exception("Processed material not found: " . $material_name);
        }

        $current_qty = $material['weight'];
        $material_id = $material['id'];

        // Step 2: Subtract quantity
        $new_qty = $current_qty - $amount;
        if ($new_qty < 0) {
            throw new Exception(sprintf(
                "Insufficient stock for %s. Required: %.2fg, Available: %.2fg",
                $material_name,
                $amount,
                $current_qty
            ));
        }

        // Step 3: Update weight
        $updateQty = $this->conn->prepare("UPDATE processed_materials SET weight = ? WHERE id = ?");
        $updateQty->bind_param("di", $new_qty, $material_id);
        $resultQty = $updateQty->execute();
        $updateQty->close();

        if (!$resultQty) {
            throw new Exception("Failed to update processed material quantity");
        }

        $change_log = sprintf("%.3f -> %.3f", $current_qty, $new_qty);
        $insertLog = $this->conn->prepare("INSERT INTO stock_history (stock_raw_id, stock_user_type, stock_type, stock_outQty, stock_changes, stock_user_id, is_processed_material) VALUES (?, 'member', 'Stock Out', ?, ?, ?, 1)");
        $insertLog->bind_param("idsi", $material_id, $amount, $change_log, $user_id);
        $resultLog = $insertLog->execute();
        $insertLog->close();

        if (!$resultLog) {
            // If log insert fails, rollback the quantity update
            $rollback = $this->conn->prepare("UPDATE processed_materials SET weight = ? WHERE id = ?");
            $rollback->bind_param("di", $current_qty, $material_id);
            $rollback->execute();
            $rollback->close();
            throw new Exception("Failed to log processed material change");
        }

        return true;
    }

    public function StockOut($user_id, $raw_used, $raw_qty)
    {
        // Step 1: Get current rm_quantity
        $query = $this->conn->prepare("SELECT rm_quantity, raw_materials_name, category FROM raw_materials WHERE id = ?");
        $query->bind_param("i", $raw_used);
        $query->execute();
        $result = $query->get_result();
        $material = $result->fetch_assoc();
        $query->close();

        if (!$material) {
            throw new Exception("Raw material not found");
        }

        $current_qty = $material['rm_quantity'];
        $material_name = $material['raw_materials_name'];
        $category = $material['category'];

        // Step 2: Subtract quantity
        $new_qty = $current_qty - $raw_qty;
        if ($new_qty < 0) {
            throw new Exception(sprintf(
                "Insufficient stock for %s%s. Required: %.2fg, Available: %.2fg",
                $material_name,
                $category ? " ($category)" : "",
                $raw_qty,
                $current_qty
            ));
        }

        // Step 3: Update rm_quantity
        $updateQty = $this->conn->prepare("UPDATE raw_materials SET rm_quantity = ? WHERE id = ?");
        $updateQty->bind_param("di", $new_qty, $raw_used);
        $resultQty = $updateQty->execute();
        $updateQty->close();

        if (!$resultQty) {
            throw new Exception("Failed to update inventory quantity");
        }

        $change_log = sprintf("%.3f -> %.3f", $current_qty, $new_qty);
        $insertLog = $this->conn->prepare("INSERT INTO stock_history (stock_raw_id,stock_user_type, stock_type,stock_outQty, stock_changes, stock_user_id) VALUES (?,'member', 'Stock Out',?, ?, ?)");
        $insertLog->bind_param("idsi", $raw_used, $raw_qty, $change_log, $user_id);
        $resultLog = $insertLog->execute();
        $insertLog->close();

        if (!$resultLog) {
            // If log insert fails, rollback the quantity update
            $rollback = $this->conn->prepare("UPDATE raw_materials SET rm_quantity = ? WHERE id = ?");
            $rollback->bind_param("di", $current_qty, $raw_used);
            $rollback->execute();
            $rollback->close();
            throw new Exception("Failed to log inventory change");
        }

        return true;
    }

    public function get_raw_materials_details($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM raw_materials WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();  
        $stmt->close();
        return $data;
    }

    public function fetch_all_materials() {
        $query = $this->conn->prepare("SELECT * FROM `raw_materials`");

        if ($query->execute()) {
            $result = $query->get_result();
            return $result;
        }
    }

    public function check_account($id, $type = 'member') {
        $id = intval($id);
        $table = ($type === 'admin') ? 'user_admin' : 'user_member';
        
        $query = "SELECT * FROM {$table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        $stmt->close();
        return $items;
    }
}