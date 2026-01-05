<?php
require_once "../../../function/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['task_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$task_id = $_POST['task_id'];
$status = $_POST['status'];

// Validate status
$valid_statuses = ['pending', 'in_progress', 'completed'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $query = "UPDATE task_assignments 
              SET status = :status, 
                  updated_at = CURRENT_TIMESTAMP 
              WHERE id = :task_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':task_id', $task_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
    }
} catch(PDOException $e) {
    error_log("Error updating task status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the task']);
}
?> 