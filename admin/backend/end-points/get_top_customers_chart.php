<?php
header('Content-Type: application/json');
require_once '../dbconnect.php';

$response = ['success' => false];

try {
    if(!isset($db) || $db->conn->connect_error) {
        $db = new dbconnect();
    }

    $sql = "SELECT c.customer_fullname, SUM(o.total_amount) AS total_spent
            FROM orders o
            JOIN user_customer c ON o.order_user_id = c.customer_id
            WHERE o.order_status IN ('Shipped', 'Delivered')
            GROUP BY c.customer_fullname
            ORDER BY total_spent DESC
            LIMIT 5";
    $stmt = $db->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    $response['success'] = true;
    $response['customers'] = $customers;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
