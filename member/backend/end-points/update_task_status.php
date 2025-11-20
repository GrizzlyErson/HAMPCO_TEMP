<?php
// Prevent any output before setting headers
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display, we'll handle errors ourselves
session_start();

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    if ($data !== null) {
        // Merge all data into the root level
        echo json_encode(array_merge(
            ['success' => $success, 'message' => $message],
            $data
        ));
    } else {
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
    }
    exit;
}

// Function to log errors
function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    error_log("[$timestamp] $message $contextStr");
}

try {
    require_once "../dbconnect.php";
    require_once "../class.php";

    if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'member') {
        sendJsonResponse(false, 'Unauthorized access', null, 401);
    }

    // Initialize database connection
    $db = new db_connect();
    $db->connect();

    if (!$db->conn) {
        throw new Exception('Database connection failed');
    }

    $member_id = $_SESSION['id'];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Invalid request method', null, 405);
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!isset($data['task_id']) || !isset($data['action'])) {
        sendJsonResponse(false, 'Missing required parameters', null, 400);
    }

    $task_id = intval($data['task_id']);
    $action = $data['action'];

    // Start transaction
    if (!$db->conn->begin_transaction()) {
        throw new Exception("Failed to start transaction");
    }

    // Check if task exists and is available
    $check_task = $db->conn->prepare("
        SELECT ta.*, pl.prod_line_id, pl.product_name, pl.weight_g, pl.length_m, pl.width_m, pl.quantity
        FROM task_assignments ta
        JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
        WHERE ta.id = ? AND ta.member_id = ? AND ta.status = 'pending'
    ");

    if (!$check_task) {
        throw new Exception("Database error: " . $db->conn->error);
    }

    $check_task->bind_param("ii", $task_id, $member_id);
    
    if (!$check_task->execute()) {
        throw new Exception("Failed to check task: " . $check_task->error);
    }

    $result = $check_task->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Task not found or already processed');
    }

    $task_data = $result->fetch_assoc();
    $check_task->close();

    if ($action === 'accept') {
        // Get raw materials calculator
        require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/raw_material_calculator.php";
        $calculator = new RawMaterialCalculator($db);
        
        // Calculate materials needed
        $materials = $calculator->calculateMaterialsNeeded(
            $task_data['product_name'],
            intval($task_data['quantity']),
            $task_data['length_m'],
            $task_data['width_m'],
            $task_data['weight_g']
        );

        if (!$materials['success']) {
            throw new Exception("Failed to calculate required materials: " . ($materials['error'] ?? 'Unknown error'));
        }

        // Validate material availability before deducting
        try {
            $calculator->validateMaterialAvailability(
                $task_data['product_name'],
                intval($task_data['quantity']),
                $task_data['length_m'],
                $task_data['width_m'],
                $task_data['weight_g']
            );
        } catch (Exception $e) {
            throw new Exception("Insufficient materials: " . $e->getMessage());
        }

        // Get global class for StockOut functionality
        require_once __DIR__ . "/../class.php";
        $global_db = new global_class();

        // Get raw material IDs and deduct from inventory
        foreach ($materials['materials'] as $material) {
            // Check if this is a processed material
            $is_processed = in_array($material['name'], ['Knotted Bastos', 'Warped Silk', 'Knotted Liniwan']);
            
            if ($is_processed) {
                // Use ProcessedStockOut for processed materials
                $stock_out_result = $global_db->ProcessedStockOut($member_id, $material['name'], $material['amount']);
                if (!$stock_out_result) {
                    throw new Exception("Failed to deduct " . $material['name'] . " from processed materials");
                }
            } else {
                // Get raw material ID
                $get_material = $db->conn->prepare("
                    SELECT id 
                    FROM raw_materials 
                    WHERE raw_materials_name = ? 
                    AND (category = ? OR (? IS NULL AND (category IS NULL OR category = '')))
                ");
                $get_material->bind_param("sss", $material['name'], $material['category'], $material['category']);
                $get_material->execute();
                $material_result = $get_material->get_result();
                
                if ($material_result->num_rows === 0) {
                    throw new Exception("Raw material not found: " . $material['name'] . 
                        ($material['category'] ? " (" . $material['category'] . ")" : ""));
                }
                
                $raw_id = $material_result->fetch_assoc()['id'];
                
                // Deduct from inventory using StockOut
                $stock_out_result = $global_db->StockOut($member_id, $raw_id, $material['amount']);
                if (!$stock_out_result) {
                    throw new Exception("Failed to deduct " . $material['name'] . " from inventory");
                }
            }
        }

        // Update task_assignments table
        $update_task = $db->conn->prepare("
            UPDATE task_assignments 
            SET status = 'in_progress', 
                updated_at = NOW()
            WHERE id = ? AND member_id = ? AND status = 'pending'
        ");

        if (!$update_task) {
            throw new Exception("Database error: " . $db->conn->error);
        }

        $update_task->bind_param("ii", $task_id, $member_id);
        
        if (!$update_task->execute()) {
            throw new Exception("Failed to update task: " . $update_task->error);
        }

        if ($update_task->affected_rows === 0) {
            throw new Exception("No rows were updated in task_assignments");
        }

        $update_task->close();

        // Update production_line table status
        $update_prod = $db->conn->prepare("
            UPDATE production_line 
            SET status = 'in_progress' 
            WHERE prod_line_id = ?
        ");

        if (!$update_prod) {
            throw new Exception("Database error: " . $db->conn->error);
        }

        $update_prod->bind_param("i", $task_data['prod_line_id']);
        
        if (!$update_prod->execute()) {
            throw new Exception("Failed to update production line: " . $update_prod->error);
        }

        $update_prod->close();

        // Commit transaction
        if (!$db->conn->commit()) {
            throw new Exception("Failed to commit transaction");
        }

        // Format the response data
        $display_id = 'PL' . str_pad($task_data['prod_line_id'], 4, '0', STR_PAD_LEFT);
        sendJsonResponse(true, 'Task accepted successfully', [
            'success' => true,
            'message' => 'Task accepted successfully',
            'task' => [
                'id' => $task_data['prod_line_id'],
                'display_id' => $display_id,
                'product_name' => $task_data['product_name'],
                'weight_g' => $task_data['weight_g'],
                'length_m' => $task_data['length_m'],
                'width_m' => $task_data['width_m'],
                'quantity' => $task_data['quantity'] ?? 1,
                'status' => 'in_progress',
                'date_started' => date('Y-m-d'),
                'date_submitted' => null
            ]
        ]);

    } elseif ($action === 'decline') {
        // Update task_assignments table for decline
        $update_task = $db->conn->prepare("
            UPDATE task_assignments 
            SET status = 'declined', 
                updated_at = NOW() 
            WHERE id = ? AND member_id = ? AND status = 'pending'
        ");

        if (!$update_task) {
            throw new Exception("Database error: " . $db->conn->error);
        }

        $update_task->bind_param("ii", $task_id, $member_id);
        
        if (!$update_task->execute()) {
            throw new Exception("Failed to decline task: " . $update_task->error);
        }

        if ($update_task->affected_rows === 0) {
            throw new Exception("No rows were updated in task_assignments for decline");
        }

        $update_task->close();

        // Commit transaction
        if (!$db->conn->commit()) {
            throw new Exception("Failed to commit transaction");
        }

        sendJsonResponse(true, 'Task declined successfully');
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($db) && isset($db->conn)) {
        $db->conn->rollback();
    }
    
    logError("Error in update_task_status.php", [
        'error' => $e->getMessage(),
        'task_id' => $task_id ?? null,
        'member_id' => $member_id ?? null,
        'action' => $action ?? null,
        'trace' => $e->getTraceAsString()
    ]);
    
    sendJsonResponse(false, $e->getMessage(), null, 500);
} finally {
    if (isset($db) && isset($db->conn)) {
        $db->conn->autocommit(TRUE);
        $db->conn->close();
    }
}
?> 