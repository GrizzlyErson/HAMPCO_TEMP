<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../dbconnect.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
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
    // Mark member as rejected (set member_verify to -1)
    $stmt = $db->prepare("UPDATE user_member SET member_verify = -1 WHERE id = ?");
    $stmt->bind_param("i", $member_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Member rejected']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject member']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$db->close();
?>
