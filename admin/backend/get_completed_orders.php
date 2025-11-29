<?php
header('Content-Type: application/json');

require_once 'dbconnect.php';

$response = ['success' => false, 'completed_orders' => 0, 'error' => ''];

try {
    $sql = "SELECT COUNT(order_id) AS completed_orders FROM orders WHERE order_status = 'Delivered'";
    $stmt = $db->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $response['success'] = true;
    $response['completed_orders'] = $row['completed_orders'] ? $row['completed_orders'] : 0;

} catch (Exception $e) {
    $response['error'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>