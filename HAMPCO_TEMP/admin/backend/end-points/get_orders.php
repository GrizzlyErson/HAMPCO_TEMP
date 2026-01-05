<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();
    
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    // Query to retrieve orders with customer info
    $query = "
        SELECT 
            o.order_id,
            o.full_name as customer_name,
            uc.customer_email,
            o.total_amount,
            o.order_status as status,
            o.payment_method,
            o.date_created as created_at
        FROM orders o
        LEFT JOIN user_customer uc ON o.order_user_id = uc.customer_id
        WHERE 1=1
    ";
    
    if ($status !== 'all') {
        $query .= " AND o.order_status = '" . $db->conn->real_escape_string($status) . "'";
    }
    
    $query .= " ORDER BY o.date_created DESC LIMIT 100";
    
    $result = $db->conn->query($query);
    
    if (!$result) {
        // Check if table doesn't exist
        if (strpos($db->conn->error, "doesn't exist") !== false) {
            echo json_encode([
                'success' => true,
                'orders' => []
            ]);
            exit;
        }
        throw new Exception($db->conn->error);
    }
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
