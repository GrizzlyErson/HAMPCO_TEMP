<?php
session_start();
header('Content-Type: application/json');
require_once '../../function/database.php';
require_once '../../backend/class.php';

$db = new Database();
$member_id = $_SESSION['id'] ?? 0;

if ($member_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Member not logged in.']);
    exit();
}

$sql = "SELECT
            tar.id,
            tar.production_id,
            pl.product_name,
            tar.status,
            tar.reason,
            tar.submitted_at
        FROM
            task_approval_requests tar
        JOIN
            production_line pl ON tar.production_id = pl.prod_line_id
        WHERE
            tar.member_id = ? AND tar.read_status = 0
        ORDER BY
            tar.submitted_at DESC";

try {
    $stmt = $db->conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->conn->error);
    }
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'notifications' => $notifications]);

} catch (Exception $e) {
    error_log("Error fetching task approval notifications for member_id $member_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching task approval notifications: ' . $e->getMessage()]);
}
?>