<?php
session_start();
require_once '../class.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['id']) || $_SESSION['user_type'] !== 'member') {
        throw new Exception('Not logged in');
    }

    if (!isset($_POST['status']) || !in_array($_POST['status'], ['available', 'unavailable'])) {
        throw new Exception('Invalid status');
    }

    $db = new global_class();
    $member_id = $_SESSION['id'];
    $status = $_POST['status'];

    $query = $db->conn->prepare("UPDATE user_member SET availability_status = ? WHERE id = ?");
    $query->bind_param("si", $status, $member_id);

    if ($query->execute()) {
        $_SESSION['availability_status'] = $status; // Update session
        $response['success'] = true;
        $response['message'] = 'Status updated successfully';
    } else {
        throw new Exception('Failed to update status');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?> 