<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();
    $action = isset($_GET['action']) ? $_GET['action'] : 'get';
    
    if ($action === 'get') {
        // Fetch pending orders as notifications
        $query = "SELECT 
                    o.order_id AS id,
                    CONCAT('New Order #', o.order_id, ' from ', o.full_name) AS message,
                    o.full_name AS customer_name,
                    uc.customer_email AS customer_email,
                    o.total_amount,
                    o.payment_method,
                    o.date_created AS created_at,
                    0 AS is_read
                  FROM orders o
                  LEFT JOIN user_customer uc ON o.order_user_id = uc.customer_id
                  WHERE o.order_status = 'Pending'
                  ORDER BY o.date_created DESC
                  LIMIT 20";
        
        $result = $db->conn->query($query);
        $notifications = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ]);
    } elseif ($action === 'mark-read') {
        // Handle marking notifications as read
        $data = json_decode(file_get_contents('php://input'), true);
        
        // For now, just return success as we're using real orders
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
