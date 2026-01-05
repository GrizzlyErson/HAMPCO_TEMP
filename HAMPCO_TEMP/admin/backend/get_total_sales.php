<?php
header('Content-Type: application/json');

require_once 'dbconnect.php';

$response = ['success' => false, 'total_sales' => 0, 'error' => ''];

try {
    $sql = "SELECT SUM(total_amount) AS total_sales FROM orders WHERE order_status = 'Shipped' OR order_status = 'Delivered'";
    $stmt = $db->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $response['success'] = true;
    $response['total_sales'] = $row['total_sales'] ? $row['total_sales'] : 0;

} catch (Exception $e) {
    $response['error'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>