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
    
    // Get order details with customer info
    $query = "
        SELECT 
            o.order_id,
            o.full_name as customer_name,
            uc.customer_email,
            o.contact_number,
            o.delivery_address,
            o.total_amount,
            o.order_status as status,
            o.payment_method,
            o.payment_proof,
            o.order_notes,
            o.date_created as created_at
        FROM orders o
        LEFT JOIN user_customer uc ON o.order_user_id = uc.customer_id
        WHERE o.order_id = ?
    ";
    
    $stmt = $db->conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $db->conn->error);
    }
    
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Order not found'
        ]);
        exit;
    }
    
    // Get order items
    $items_query = "
        SELECT 
            oi.order_item_id,
            oi.prod_id,
            oi.product_name,
            oi.quantity,
            oi.unit_price,
            oi.subtotal
        FROM order_items oi
        WHERE oi.order_id = ?
    ";
    
    $stmtItems = $db->conn->prepare($items_query);
    if (!$stmtItems) {
        throw new Exception("Database error: " . $db->conn->error);
    }
    
    $stmtItems->bind_param("i", $order_id);
    $stmtItems->execute();
    $items_result = $stmtItems->get_result();
    
    $items = [];
    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
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
