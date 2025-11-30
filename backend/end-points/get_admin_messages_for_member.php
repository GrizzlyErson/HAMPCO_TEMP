<?php
// backend/end-points/get_admin_messages_for_member.php
require_once '../../function/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$memberId = $_SESSION['id'];
$db = new Database();

try {
    // Fetch unread messages for the specific member
    // Assumes there's a table `admin_messages` with a `recipient_member_id` and a `is_read` column
    // The query should join with a linking table if messages are sent to multiple members,
    // or check a specific column if messages are targeted individually.
    // This example assumes a simple `admin_messages` table for demonstration.
    
    // Let's assume a table structure like:
    // admin_messages(id, title, content, sent_at, is_read, recipient_member_id)
    
    // A more robust approach would use a pivot table like `message_recipients`
    // message_recipients(message_id, member_id, is_read)
    
    $query = "SELECT 
                m.id, 
                m.title, 
                m.content, 
                m.sent_at 
              FROM admin_messages m
              LEFT JOIN admin_message_read_status mrs ON m.id = mrs.message_id AND mrs.member_id = ?
              WHERE mrs.id IS NULL OR mrs.is_read = 0
              ORDER BY m.sent_at DESC";

    $stmt = $db->conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $db->conn->error);
    }
    
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'messages' => $messages]);

} catch (Exception $e) {
    error_log("Error fetching admin messages for member $memberId: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching messages.']);
}

$db->conn->close();
?>