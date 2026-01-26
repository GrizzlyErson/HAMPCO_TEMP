<?php
session_start();
require_once '../../../function/connection.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = array('success' => false);

try {
    // Log the incoming request
    error_log("Submit task request received: " . print_r($_POST, true));
    error_log("Raw input: " . file_get_contents('php://input'));

    if (!isset($_SESSION['id'])) {
        throw new Exception('Not logged in');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Log the decoded data
    error_log("Decoded data: " . print_r($data, true));

    if (!isset($data['prod_line_id'])) {
        error_log("Missing prod_line_id. Data received: " . print_r($data, true));
        throw new Exception('Production line ID is required. Received data: ' . json_encode($data));
    }

    $member_id = $_SESSION['id'];
    $prod_line_id = intval($data['prod_line_id']); // Ensure integer

    error_log("Processing submission for member_id: $member_id, prod_line_id: $prod_line_id");
    
    // Check if we have a valid PDO connection
    if (!($conn instanceof PDO)) {
        throw new Exception("Invalid database connection");
    }

    // Start transaction
    if (!$conn->beginTransaction()) {
        throw new Exception("Failed to start transaction");
    }

    try {
        // First check if the task is a self-assigned task or assigned task
        // Try member_self_tasks first - search by numeric production_id
        // The production_id might be stored as "PL0077" or as integer 77
        $check_task = $conn->prepare("
            SELECT status, 'self' as task_type, production_id
            FROM member_self_tasks 
            WHERE member_id = ? 
            AND (production_id = ? OR production_id = CONCAT('PL', LPAD(?, 4, '0')))
        ");
        
        if (!$check_task) {
            throw new Exception("Failed to prepare self-task check query: " . implode(", ", $conn->errorInfo()));
        }

        $check_task->bindParam(1, $member_id, PDO::PARAM_INT);
        $check_task->bindParam(2, $prod_line_id, PDO::PARAM_INT);
        $check_task->bindParam(3, $prod_line_id, PDO::PARAM_INT);
        if (!$check_task->execute()) {
            throw new Exception("Failed to check self-task status: " . implode(", ", $check_task->errorInfo()));
        }

        $result = $check_task->fetch(PDO::FETCH_ASSOC);
        $task_type = 'self';

        // If not found in self_tasks, try task_assignments
        if (!$result) {
            $check_task = $conn->prepare("
                SELECT status, 'assigned' as task_type
                FROM task_assignments 
                WHERE member_id = ? 
                AND prod_line_id = ?
            ");
            
            if (!$check_task) {
                throw new Exception("Failed to prepare task check query: " . implode(", ", $conn->errorInfo()));
            }

            $check_task->bindParam(1, $member_id, PDO::PARAM_INT);
            $check_task->bindParam(2, $prod_line_id, PDO::PARAM_INT);
            if (!$check_task->execute()) {
                throw new Exception("Failed to check task status: " . implode(", ", $check_task->errorInfo()));
            }

            $result = $check_task->fetch(PDO::FETCH_ASSOC);
            $task_type = 'assigned';
        }

        if (!$result) {
            error_log("Task not found for member_id: $member_id, prod_line_id: $prod_line_id");
            throw new Exception("Task not found");
        }

        $task_status = $result['status'];
        error_log("Current task status: $task_status (task_type: $task_type)");

        if ($task_status !== 'in_progress') {
            throw new Exception("Task cannot be submitted because it is not in progress (current status: " . $task_status . ")");
        }

        // Update task status based on task type
        if ($task_type === 'self') {
            $update_task = $conn->prepare("
                UPDATE member_self_tasks 
                SET status = 'submitted',
                    date_submitted = NOW()
                WHERE member_id = ? 
                AND (production_id = ? OR production_id = CONCAT('PL', LPAD(?, 4, '0')))
                AND status = 'in_progress'
            ");
            $update_task->bindParam(1, $member_id, PDO::PARAM_INT);
            $update_task->bindParam(2, $prod_line_id, PDO::PARAM_INT);
            $update_task->bindParam(3, $prod_line_id, PDO::PARAM_INT);
        } else {
            $update_task = $conn->prepare("
                UPDATE task_assignments 
                SET status = 'submitted',
                    updated_at = NOW()
                WHERE member_id = ? 
                AND prod_line_id = ? 
                AND status = 'in_progress'
            ");
            $update_task->bindParam(1, $member_id, PDO::PARAM_INT);
            $update_task->bindParam(2, $prod_line_id, PDO::PARAM_INT);
        }

        if (!$update_task) {
            throw new Exception("Failed to prepare task update query: " . implode(", ", $conn->errorInfo()));
        }

        // Only bind parameters if not already bound for self-tasks
        if ($task_type !== 'self') {
            $update_task->bindParam(1, $member_id, PDO::PARAM_INT);
            $update_task->bindParam(2, $prod_line_id, PDO::PARAM_INT);
        }

        if (!$update_task->execute()) {
            throw new Exception("Failed to update task status: " . implode(", ", $update_task->errorInfo()));
        }

        if ($update_task->rowCount() === 0) {
            throw new Exception("No task found or task is not in progress");
        }

        error_log("Task status updated successfully");

        // Check if all members assigned to this production line have submitted their tasks
        $check_all_submitted = $conn->prepare("
            SELECT COUNT(*) as total_tasks,
                   SUM(CASE WHEN status = 'submitted' OR status = 'completed' THEN 1 ELSE 0 END) as submitted_tasks
            FROM task_assignments
            WHERE prod_line_id = ?
        ");

        if (!$check_all_submitted) {
            throw new Exception("Failed to prepare submission check query: " . implode(", ", $conn->errorInfo()));
        }

        $check_all_submitted->bindParam(1, $prod_line_id, PDO::PARAM_INT);
        if (!$check_all_submitted->execute()) {
            throw new Exception("Failed to check submission status: " . implode(", ", $check_all_submitted->errorInfo()));
        }
        
        $result = $check_all_submitted->fetch(PDO::FETCH_ASSOC);
        
        // Convert to integers for comparison
        $total_tasks = intval($result['total_tasks']);
        $submitted_tasks = intval($result['submitted_tasks']);

        error_log("Submission check - Total tasks: {$total_tasks}, Submitted tasks: {$submitted_tasks}");

        // Update production line with actual measurements if provided
        if (isset($data['actual_weight']) || (isset($data['actual_length']) && isset($data['actual_width']))) {
            $update_pl_sql = "UPDATE production_line SET ";
            $pl_params = [];
            $pl_types = "";

            if (isset($data['actual_weight'])) {
                $update_pl_sql .= "weight_g = ?, ";
                $pl_params[] = floatval($data['actual_weight']);
                $pl_types .= "d";
            }
            if (isset($data['actual_length']) && isset($data['actual_width'])) {
                $update_pl_sql .= "length_m = ?, width_m = ?, ";
                $pl_params[] = floatval($data['actual_length']);
                $pl_params[] = floatval($data['actual_width']);
                $pl_types .= "dd";
            }

            // Remove trailing comma and space
            $update_pl_sql = rtrim($update_pl_sql, ", ");
            $update_pl_sql .= " WHERE prod_line_id = ?";
            $pl_params[] = $prod_line_id;
            $pl_types .= "i";

            $stmt_pl = $conn->prepare($update_pl_sql);
            // PDO binding is different from mysqli, doing manual bind for PDO
            // But wait, the context shows this file uses PDO ($conn->prepare).
            // Let's stick to PDO syntax.
            
            $update_pl_sql = "UPDATE production_line SET ";
            $update_values = [];
            
            if (isset($data['actual_weight'])) {
                $update_pl_sql .= "weight_g = :weight, ";
                $update_values[':weight'] = floatval($data['actual_weight']);
            }
            if (isset($data['actual_length']) && isset($data['actual_width'])) {
                $update_pl_sql .= "length_m = :length, width_m = :width, ";
                $update_values[':length'] = floatval($data['actual_length']);
                $update_values[':width'] = floatval($data['actual_width']);
            }
            
            $update_pl_sql = rtrim($update_pl_sql, ", ");
            $update_pl_sql .= " WHERE prod_line_id = :prod_id";
            $update_values[':prod_id'] = $prod_line_id;
            
            if (!empty($update_values) && count($update_values) > 1) { // Ensure at least one field + ID
                $stmt_pl = $conn->prepare($update_pl_sql);
                if ($stmt_pl->execute($update_values)) {
                    error_log("Production line measurements updated");
                } else {
                    error_log("Failed to update production line measurements: " . implode(", ", $stmt_pl->errorInfo()));
                }
            }
        }

        // Update production line status to 'submitted' only if all tasks are submitted
        if ($total_tasks === $submitted_tasks) {
        $update_prod = $conn->prepare("
            UPDATE production_line 
            SET status = 'submitted' 
            WHERE prod_line_id = ?
        ");
        
        if (!$update_prod) {
            throw new Exception("Failed to prepare production line update query: " . implode(", ", $conn->errorInfo()));
        }

        $update_prod->bindParam(1, $prod_line_id, PDO::PARAM_INT);
        if (!$update_prod->execute()) {
            throw new Exception("Failed to update production line status: " . implode(", ", $update_prod->errorInfo()));
        }

        error_log("Production line status updated to submitted");
        } else {
            error_log("Not all tasks are submitted yet. Total: $total_tasks, Submitted: $submitted_tasks");
        }

        // If we got here, commit the transaction
        if (!$conn->commit()) {
            throw new Exception("Failed to commit transaction: " . implode(", ", $conn->errorInfo()));
        }

        error_log("Transaction committed successfully");

        $response['success'] = true;
        $response['message'] = 'Product has been submitted to the admin.';

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Transaction rolled back");
        throw $e; // Re-throw to be caught by outer try-catch
    }

} catch (Exception $e) {
    error_log("Error in submit_task.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

error_log("Final response: " . print_r($response, true));
echo json_encode($response); 