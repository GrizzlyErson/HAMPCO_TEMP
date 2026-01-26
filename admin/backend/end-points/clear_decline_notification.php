<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once '../dbconnect.php';

// Check if user is admin
if (!isset($_SESSION['id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
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
    // Mark the task as archived or clear from decline notifications
    // We'll store this in a notification_read or hidden status
    $stmt = $db->conn->prepare("UPDATE task_assignments SET decline_status = 'cleared' WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->conn->error);
    }
    $stmt->bind_param("i", $task_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification cleared']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to clear notification']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
