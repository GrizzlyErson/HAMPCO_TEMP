<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();
    
    if (!isset($_GET['order_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Order ID is required'
        ]);
        exit;
    }
    
    $order_id = intval($_GET['order_id']);
    
    // Get order details
    $query = "SELECT * FROM orders WHERE order_id = " . $order_id;
    $result = mysqli_query($db->conn, $query);
    
    if (!$result) {
        // If table doesn't exist, return empty
        if (strpos(mysqli_error($db->conn), "doesn't exist") !== false) {
            echo json_encode([
                'success' => true,
                'order' => null,
                'items' => []
            ]);
            exit;
        }
        throw new Exception(mysqli_error($db->conn));
    }
    
    $order = mysqli_fetch_assoc($result);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Order not found'
        ]);
        exit;
    }
    
    // Get order items
    $items_query = "SELECT * FROM order_items WHERE order_id = " . $order_id;
    $items_result = mysqli_query($db->conn, $items_query);
    
    $items = [];
    if ($items_result) {
        while ($row = mysqli_fetch_assoc($items_result)) {
            $items[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
