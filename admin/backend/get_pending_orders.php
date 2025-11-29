<?php
header('Content-Type: application/json');

require_once 'dbconnect.php';

$response = ['success' => false, 'pending_orders' => 0, 'error' => ''];

try {
    $sql = "SELECT COUNT(order_id) AS pending_orders FROM orders WHERE order_status = 'Pending'";
    $stmt = $db->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $response['success'] = true;
    $response['pending_orders'] = $row['pending_orders'] ? $row['pending_orders'] : 0;

} catch (Exception $e) {
    $response['error'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>