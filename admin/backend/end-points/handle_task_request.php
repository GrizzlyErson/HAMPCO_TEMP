<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
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
$raw_request_id = trim($_POST['request_id']);
$action = $_POST['action'];

try {
    // Start transaction
    mysqli_begin_transaction($db->conn);

    // Get request details
    $query = "SELECT tar.*, um.role 
              FROM task_approval_requests tar 
              LEFT JOIN user_member um ON tar.member_id = um.id 
              WHERE tar.id = ? AND tar.status = 'pending'";
    $stmt = mysqli_prepare($db->conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $request_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $request = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // If not found by ID, try finding by production_id (in case frontend sent that)
    if (!$request) {
        $query = "SELECT tar.*, um.role 
                  FROM task_approval_requests tar 
                  LEFT JOIN user_member um ON tar.member_id = um.id 
                  WHERE tar.production_id = ? AND tar.status = 'pending'";
        $stmt = mysqli_prepare($db->conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $raw_request_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $request = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }

    // Self-Healing: If still not found, check if it exists in member_self_tasks and create the missing request
    if (!$request) {
        $mst_query = "SELECT member_id, production_id, status FROM member_self_tasks WHERE production_id = ?";
        $stmt = mysqli_prepare($db->conn, $mst_query);
        mysqli_stmt_bind_param($stmt, "s", $raw_request_id);
        mysqli_stmt_execute($stmt);
        $mst_result = mysqli_stmt_get_result($stmt);
        $mst_row = mysqli_fetch_assoc($mst_result);
        mysqli_stmt_close($stmt);

        if (!$mst_row) {
            $mst_query = "SELECT member_id, production_id, status FROM member_self_tasks WHERE id = ?";
            $stmt = mysqli_prepare($db->conn, $mst_query);
            mysqli_stmt_bind_param($stmt, "i", $request_id);
            mysqli_stmt_execute($stmt);
            $mst_result = mysqli_stmt_get_result($stmt);
            $mst_row = mysqli_fetch_assoc($mst_result);
            mysqli_stmt_close($stmt);
        }

        if (!$mst_row) {
            // Try loose search on production_id (e.g. finding 'PL-146' when searching '146')
            // This handles cases where frontend sends '146' but DB has 'PL-146'
            $mst_query = "SELECT member_id, production_id, status FROM member_self_tasks WHERE production_id LIKE ? ORDER BY id DESC LIMIT 1";
            $stmt = mysqli_prepare($db->conn, $mst_query);
            $like_param = "%" . $raw_request_id;
            mysqli_stmt_bind_param($stmt, "s", $like_param);
            mysqli_stmt_execute($stmt);
            $mst_result = mysqli_stmt_get_result($stmt);
            $mst_row = mysqli_fetch_assoc($mst_result);
            mysqli_stmt_close($stmt);
        }

        if ($mst_row) {
            // Check status (allow pending or submitted, case-insensitive)
            $mst_status = strtolower($mst_row['status']);
            if ($mst_status !== 'pending' && $mst_status !== 'submitted') {
                throw new Exception("Member task found but status is '" . $mst_row['status'] . "' (expected 'pending' or 'submitted')");
            }

            // Check if an approval request already exists for this production_id to avoid duplicate insert failure
            $check_tar = "SELECT id, status FROM task_approval_requests WHERE production_id = ?";
            $stmt_check = mysqli_prepare($db->conn, $check_tar);
            mysqli_stmt_bind_param($stmt_check, "s", $mst_row['production_id']);
            mysqli_stmt_execute($stmt_check);
            $res_check = mysqli_stmt_get_result($stmt_check);
            $existing_tar = mysqli_fetch_assoc($res_check);
            mysqli_stmt_close($stmt_check);

            if ($existing_tar) {
                // Use the existing request
                $request = ['id' => $existing_tar['id'], 'member_id' => $mst_row['member_id'], 'production_id' => $mst_row['production_id']];
            } else {
                // Create the missing approval request
                $insert_query = "INSERT INTO task_approval_requests (member_id, production_id, status) VALUES (?, ?, 'pending')";
                $stmt = mysqli_prepare($db->conn, $insert_query);
                mysqli_stmt_bind_param($stmt, "is", $mst_row['member_id'], $mst_row['production_id']);
                if (mysqli_stmt_execute($stmt)) {
                    $request_id = mysqli_insert_id($db->conn);
                    // Re-fetch the request to ensure we have the correct structure for downstream logic
                    $request = ['id' => $request_id, 'member_id' => $mst_row['member_id'], 'production_id' => $mst_row['production_id']];
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if (!$request) {
        // Check if it exists but is not pending to provide a better error message
        $check_query = "SELECT status FROM task_approval_requests WHERE id = ? OR production_id = ?";
        $stmt = mysqli_prepare($db->conn, $check_query);
        mysqli_stmt_bind_param($stmt, "is", $request_id, $raw_request_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Task request already processed (Status: ' . $row['status'] . ')');
        }
        mysqli_stmt_close($stmt);
        error_log("Task request not found. Request ID: " . $request_id . ", Raw: " . $raw_request_id);
        throw new Exception("Task request not found. Searched ID: $request_id, ProdID: $raw_request_id in approval & self-tasks tables.");
    }

    // Update request_id to the actual ID found (in case we found it by production_id)
    $request_id = $request['id'];

    // Update request status
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $update_query = "UPDATE task_approval_requests SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($db->conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $status, $request_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to update request status');
    }

    // Note: member_self_tasks status is updated via database trigger 'after_update_approval_status'
    // on table task_approval_requests. No need to update it manually here to avoid conflicts.

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