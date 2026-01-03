<?php
session_start();
header('Content-Type: application/json');

define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php'; // Adjust path as necessary
require_once '../class.php'; // For global_class and other helpers
require_once '../helpers/task_decline_helper.php'; // For updateTaskDeclineStatus

$response = ["success" => false, "message" => "Unknown error."];

$db = new global_class();
$conn = $db->conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response["message"] = "Invalid request method.";
    echo json_encode($response);
    exit();
}

// Get the POST data
$input = json_decode(file_get_contents('php://input'), true);

$prod_line_id = $input['prod_line_id'] ?? null;
$original_task_id = $input['original_task_id'] ?? null;
$decline_notification_id = $input['decline_notification_id'] ?? null;
$assignments = $input['assignments'] ?? []; // Array of { member_id, role, deadline }

// Basic validation
if (!$prod_line_id || !$original_task_id || !$decline_notification_id || !is_array($assignments) || empty($assignments)) {
    $response["message"] = "Missing or invalid required parameters for reassignment.";
    echo json_encode($response);
    exit();
}

$conn->begin_transaction();

try {
    // 1. Mark the original declined task assignment as 'reassigned' or 'declined'
    $update_original_task_sql = "UPDATE task_assignments SET status = 'reassigned', updated_at = NOW() WHERE id = ?";
    $stmt_update_original = $conn->prepare($update_original_task_sql);
    if (!$stmt_update_original) {
        throw new Exception("Failed to prepare original task update statement: " . $conn->error);
    }
    $stmt_update_original->bind_param("i", $original_task_id);
    if (!$stmt_update_original->execute()) {
        throw new Exception("Failed to update original task status: " . $stmt_update_original->error);
    }
    $stmt_update_original->close();

    // 2. Create new task assignments for the selected members
    $insert_new_assignment_sql = "INSERT INTO task_assignments (prod_line_id, member_id, role, status, deadline, created_at, updated_at) VALUES (?, ?, ?, 'pending', ?, NOW(), NOW())";
    $stmt_insert_new = $conn->prepare($insert_new_assignment_sql);
    if (!$stmt_insert_new) {
        throw new Exception("Failed to prepare new task assignment statement: " . $conn->error);
    }

    foreach ($assignments as $assignment) {
        $member_id = $assignment['member_id'] ?? null;
        $role = $assignment['role'] ?? null;
        $deadline = $assignment['deadline'] ?? null;

        if (!$member_id || !$role || !$deadline) {
            throw new Exception("Invalid new assignment details provided.");
        }
        $stmt_insert_new->bind_param("iiss", $prod_line_id, $member_id, $role, $deadline);
        if (!$stmt_insert_new->execute()) {
            throw new Exception("Failed to create new task assignment: " . $stmt_insert_new->error);
        }
    }
    $stmt_insert_new->close();

    // 3. Mark the decline notification as handled/acknowledged
    updateTaskDeclineStatus($conn, $decline_notification_id, 'acknowledged', 'Admin reassigned task.');

    $conn->commit();
    $response["success"] = true;
    $response["message"] = "Task successfully reassigned and decline notification handled.";

} catch (Exception $e) {
    $conn->rollback();
    $response["message"] = "Reassignment failed: " . $e->getMessage();
    error_log("Task reassignment error: " . $e->getMessage());
}

$conn->close();
echo json_encode($response);
?>