<?php
require_once '../class.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$db = new global_class();

// Validate request parameters
if (!isset($_POST['request_id']) || !isset($_POST['action']) || 
    !in_array($_POST['action'], ['approve', 'reject'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
    exit();
}

$request_id = intval($_POST['request_id']);
$action = $_POST['action'];

try {
    // Start transaction
    mysqli_begin_transaction($db->conn);

    // Get request details
    $query = "SELECT tar.*, um.role 
              FROM task_approval_requests tar 
              JOIN user_member um ON tar.member_id = um.id 
              WHERE tar.id = ? AND tar.status = 'pending'";
    $stmt = mysqli_prepare($db->conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $request_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $request = mysqli_fetch_assoc($result);

    if (!$request) {
        throw new Exception('Task request not found or already processed');
    }

    // Update request status
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $update_query = "UPDATE task_approval_requests SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($db->conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $status, $request_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to update request status');
    }

    // Update member_self_tasks status
    $update_self_task_query = "UPDATE member_self_tasks 
                              SET status = ? 
                              WHERE production_id = ? AND member_id = ?";
    $task_status = $action === 'approve' ? 'pending' : 'rejected';
    $stmt = mysqli_prepare($db->conn, $update_self_task_query);
    mysqli_stmt_bind_param($stmt, "ssi", $task_status, $request['production_id'], $request['member_id']);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to update member self task status');
    }

    // Commit transaction
    mysqli_commit($db->conn);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($db->conn);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 