<?php
session_start();
header('Content-Type: application/json');
require_once '../dbconnect.php';
require_once '../class.php';

$db = new global_class();
$conn = $db->conn;

$member_id = $_SESSION['id'] ?? 0;

if ($member_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Member not logged in.']);
    exit();
}

$notifications = [];

try {
    // Fetch task approval requests (approved/rejected tasks)
    $sql_approvals = "SELECT
                tar.id,
                tar.production_id,
                pl.product_name,
                um.fullname as member_name,
                tar.status,
                tar.reason,
                tar.submitted_at,
                'approval_request' as notification_type
            FROM
                task_approval_requests tar
            JOIN
                production_line pl ON tar.production_id = pl.prod_line_id
            LEFT JOIN
                user_member um ON tar.member_id = um.id
            WHERE
                tar.member_id = ? AND tar.is_read_by_member = 0
            ORDER BY
                tar.submitted_at DESC";

    $stmt_approvals = $conn->prepare($sql_approvals);
    if (!$stmt_approvals) {
        throw new Exception("Prepare failed for approvals: " . $conn->error);
    }
    $stmt_approvals->bind_param("i", $member_id);
    $stmt_approvals->execute();
    $result_approvals = $stmt_approvals->get_result();
    while ($row = $result_approvals->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt_approvals->close();

    // Fetch declined tasks from task_assignments
    $sql_declines = "SELECT
                ta.id,
                pl.prod_line_id as production_id,
                pl.product_name,
                um.fullname as member_name,
                'declined' as status,
                ta.decline_reason as reason,
                ta.updated_at as submitted_at,
                'task_decline' as notification_type
            FROM
                task_assignments ta
            JOIN
                production_line pl ON ta.prod_line_id = pl.prod_line_id
            JOIN
                user_member um ON ta.member_id = um.id
            WHERE
                ta.member_id = ? 
                AND ta.decline_status = 'pending'
                AND ta.decline_reason IS NOT NULL
            ORDER BY
                ta.updated_at DESC";

    $stmt_declines = $conn->prepare($sql_declines);
    if (!$stmt_declines) {
        throw new Exception("Prepare failed for declines: " . $conn->error);
    }
    $stmt_declines->bind_param("i", $member_id);
    $stmt_declines->execute();
    $result_declines = $stmt_declines->get_result();
    while ($row = $result_declines->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt_declines->close();

    // Sort all notifications by date (most recent first)
    usort($notifications, function($a, $b) {
        return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
    });

    echo json_encode(['success' => true, 'notifications' => $notifications]);

} catch (Exception $e) {
    error_log("Error fetching task approval notifications for member_id $member_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching task approval notifications: ' . $e->getMessage()]);
}
?>
