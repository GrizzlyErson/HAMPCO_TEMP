<?php
session_start();
header('Content-Type: application/json');

require_once '../../../admin/backend/class.php';
$db = new global_class();

try {
    // Check if user is logged in and is admin
    if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        error_log("Admin not logged in - Session: " . print_r($_SESSION, true));
        throw new Exception('Unauthorized access');
    }

    // Get the production line ID from POST data
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Received data: " . print_r($data, true));
    
    if (!isset($data['prod_line_id'])) {
        throw new Exception('Production line ID is required');
    }
    
    $prod_line_id = intval($data['prod_line_id']);
    error_log("Attempting to delete production line ID: " . $prod_line_id);

    // Start transaction
    $db->conn->begin_transaction();

    try {
        // First check if there are any task assignments for this production line
        $check_query = "SELECT task_assignments.id FROM task_assignments WHERE prod_line_id = ?";
        $stmt = $db->conn->prepare($check_query);
        $stmt->bind_param("i", $prod_line_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            error_log("Found task assignments for production line ID: " . $prod_line_id);
            // Delete task assignments first
            $delete_tasks = "DELETE FROM task_assignments WHERE prod_line_id = ?";
            $stmt = $db->conn->prepare($delete_tasks);
            $stmt->bind_param("i", $prod_line_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to delete task assignments: ' . $stmt->error);
            }
            $stmt->close();
        }

        // Check for tasks in the task table
        $check_tasks = "SELECT task_id FROM task WHERE task_id IN (SELECT id FROM task_assignments WHERE prod_line_id = ?)";
        $stmt = $db->conn->prepare($check_tasks);
        $stmt->bind_param("i", $prod_line_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            error_log("Found tasks for production line ID: " . $prod_line_id);
            // Delete tasks first
            $delete_tasks = "DELETE FROM task WHERE task_id IN (SELECT id FROM task_assignments WHERE prod_line_id = ?)";
            $stmt = $db->conn->prepare($delete_tasks);
            $stmt->bind_param("i", $prod_line_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to delete tasks: ' . $stmt->error);
            }
            $stmt->close();
        }

        // Now delete the production line item
        $delete_query = "DELETE FROM production_line WHERE prod_line_id = ?";
        $stmt = $db->conn->prepare($delete_query);
        $stmt->bind_param("i", $prod_line_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete production line item: ' . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception('Production line item not found');
        }
        $stmt->close();

        // Commit transaction
        $db->conn->commit();
        error_log("Successfully deleted production line ID: " . $prod_line_id);

        echo json_encode([
            'success' => true,
            'message' => 'Production line item deleted successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->conn->rollback();
        throw $e; // Re-throw to be caught by outer try-catch
    }

} catch (Exception $e) {
    error_log("Error in delete_production_item.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 