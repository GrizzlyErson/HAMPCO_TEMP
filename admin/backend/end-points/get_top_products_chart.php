<?php
header('Content-Type: application/json');
require_once '../dbconnect.php';

$response = ['success' => false];

try {
    // It's better to connect to the database using the existing class if available
    if(!isset($db) || $db->conn->connect_error) {
        $db = new dbconnect();
    }

    $sql = "SELECT p.prod_name AS product_name, SUM(oi.quantity) AS total_quantity_sold
            FROM order_items oi
            JOIN product p ON oi.prod_id = p.prod_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.order_status IN ('Shipped', 'Delivered')
            GROUP BY p.prod_name
            ORDER BY total_quantity_sold DESC
            LIMIT 5";
    $stmt = $db->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $response['success'] = true;
    $response['products'] = $products;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
