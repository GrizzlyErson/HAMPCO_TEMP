<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../dbconnect.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get task_id from POST request
$data = json_decode(file_get_contents("php://input"), true);
$task_id = isset($data['task_id']) ? intval($data['task_id']) : 0;

if ($task_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit();
}

try {
    // Update member_self_task status to 'rejected'
    $stmt = $db->prepare("UPDATE member_self_tasks SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task rejected']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject task']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$db->close();
?>
