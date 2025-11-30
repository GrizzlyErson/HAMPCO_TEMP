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
            id,
            title,
            content,
            sent_at
        FROM
            admin_messages
        WHERE
            recipient_member_id = ? AND read_status = 0
        ORDER BY
            sent_at DESC";

try {
    $stmt = $db->conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->conn->error);
    }
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'messages' => $messages]);

} catch (Exception $e) {
    error_log("Error fetching admin messages for member_id $member_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching admin messages: ' . $e->getMessage()]);
}
?>