<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();

    $query = "
        SELECT 
            p.prod_id AS product_id,
            p.prod_name AS product_name,
            SUM(oi.quantity) AS total_quantity_sold
        FROM order_items oi
        JOIN product p ON oi.prod_id = p.prod_id
        GROUP BY p.prod_id, p.prod_name
        ORDER BY total_quantity_sold DESC
        LIMIT 10;
    ";
    
    $result = $db->conn->query($query);
    
    if (!$result) {
        throw new Exception($db->conn->error);
    }
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
