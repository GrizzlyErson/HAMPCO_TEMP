<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once '../dbconnect.php';

// Check if user is admin
if (!isset($_SESSION['id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get member_id from POST request
$data = json_decode(file_get_contents("php://input"), true);
$member_id = isset($data['member_id']) ? intval($data['member_id']) : 0;

if ($member_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
    exit();
}

try {
    // Update member verification status
    $stmt = $db->conn->prepare("UPDATE user_member SET status = 1 WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->conn->error);
    }
    $stmt->bind_param("i", $member_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Member verified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to verify member']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
