<?php
header('Content-Type: application/json');

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if prod_line_id is provided
if (!isset($_POST['prod_line_id']) || !is_numeric($_POST['prod_line_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid production line ID']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/class.php";
$db = new global_class();

try {
    // Start transaction
    $db->conn->begin_transaction();

    $prod_line_id = intval($_POST['prod_line_id']);

    // Delete from task_assignments table
    $delete_assignments = $db->conn->prepare("DELETE FROM task_assignments WHERE prod_line_id = ?");
    if (!$delete_assignments) {
        throw new Exception("Failed to prepare delete statement: " . $db->conn->error);
    }

    $delete_assignments->bind_param("i", $prod_line_id);
    if (!$delete_assignments->execute()) {
        throw new Exception("Failed to delete task assignments: " . $delete_assignments->error);
    }

    // Check if any rows were affected
    if ($delete_assignments->affected_rows === 0) {
        throw new Exception("No task assignments found for the specified production line");
    }

    // Commit transaction
    $db->conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Task assignments deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->conn->connect_errno === 0) {
        $db->conn->rollback();
    }

    error_log("Error in delete_assigned_task.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($delete_assignments)) {
        $delete_assignments->close();
    }
    if (isset($db)) {
        $db->conn->close();
    }
}
?> 