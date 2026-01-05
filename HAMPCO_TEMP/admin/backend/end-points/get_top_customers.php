<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();

    $query = "
        SELECT 
            c.customer_id,
            c.customer_fullname,
            COUNT(o.order_id) AS total_orders,
            SUM(o.total_amount) AS total_spent
        FROM user_customer c
        JOIN orders o ON c.customer_id = o.order_user_id
        GROUP BY c.customer_id, c.customer_fullname
        ORDER BY total_orders DESC
        LIMIT 20;
    ";
    
    $result = $db->conn->query($query);
    
    if (!$result) {
        throw new Exception($db->conn->error);
    }
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'customers' => $customers
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
