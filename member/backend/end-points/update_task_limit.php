<?php
session_start();
// Disable error display to prevent HTML output breaking JSON
ini_set('display_errors', 0);
require_once "../../../function/database.php";
ini_set('display_errors', 0); // Ensure it stays off after include

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$task_limit = isset($_POST['task_limit']) ? intval($_POST['task_limit']) : 0;

if ($task_limit < 1) {
    echo json_encode(['success' => false, 'message' => 'Task limit must be at least 1']);
    exit;
}

$db = new Database();
$member_id = $_SESSION['id'];

try {
    // Check if column exists and add it if missing
    $check_col = $db->conn->query("SHOW COLUMNS FROM user_member LIKE 'task_limit'");
    if ($check_col && $check_col->num_rows == 0) {
        $alter_res = $db->conn->query("ALTER TABLE user_member ADD COLUMN task_limit INT DEFAULT 10");
        if (!$alter_res) {
             throw new Exception("Failed to add task_limit column: " . $db->conn->error);
        }
    }

    $stmt = $db->conn->prepare("UPDATE user_member SET task_limit = ? WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Database error: " . $db->conn->error);
    }
    
    $stmt->bind_param("ii", $task_limit, $member_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task limit updated successfully']);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    error_log("Update task limit error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating task limit: ' . $e->getMessage()]);
}
?>