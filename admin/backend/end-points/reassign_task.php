<?php
session_start();
header('Content-Type: application/json');

define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';
require_once '../class.php';

$response = ["success" => false, "message" => "Unknown error."];

$db = new global_class();
$conn = $db->conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response["message"] = "Invalid request method.";
    echo json_encode($response);
    exit();
}

// Get the POST data from form
$task_id = $_POST['task_id'] ?? null;
$prod_line_id = $_POST['prod_line_id'] ?? null;
$new_member_id = $_POST['new_member_id'] ?? null;
$deadline = $_POST['deadline'] ?? null;

// Basic validation
if (!$task_id || !$prod_line_id || !$new_member_id || !$deadline) {
    $response["message"] = "Missing required parameters for reassignment.";
    echo json_encode($response);
    exit();
}

try {
    // Fetch the new member's role from user_member table
    $member_role_sql = "SELECT role FROM user_member WHERE member_id = ?";
    $stmt_role = $conn->prepare($member_role_sql);
    if (!$stmt_role) {
        throw new Exception("Failed to prepare member role statement: " . $conn->error);
    }
    $stmt_role->bind_param("i", $new_member_id);
    if (!$stmt_role->execute()) {
        throw new Exception("Failed to fetch member role: " . $stmt_role->error);
    }
    
    $result = $stmt_role->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Member not found.");
    }
    
    $member_row = $result->fetch_assoc();
    $new_role = $member_row['role'];
    $stmt_role->close();

    // Update the task assignment with new member and deadline
    $update_task_sql = "UPDATE task_assignments SET member_id = ?, role = ?, deadline = ?, updated_at = NOW() WHERE id = ?";
    $stmt_update = $conn->prepare($update_task_sql);
    if (!$stmt_update) {
        throw new Exception("Failed to prepare task update statement: " . $conn->error);
    }
    
    $stmt_update->bind_param("issi", $new_member_id, $new_role, $deadline, $task_id);
    if (!$stmt_update->execute()) {
        throw new Exception("Failed to update task assignment: " . $stmt_update->error);
    }
    $stmt_update->close();

    $response["success"] = true;
    $response["message"] = "Task successfully reassigned to the new member.";

} catch (Exception $e) {
    $response["message"] = "Reassignment failed: " . $e->getMessage();
    error_log("Task reassignment error: " . $e->getMessage());
}

$conn->close();
echo json_encode($response);
?>