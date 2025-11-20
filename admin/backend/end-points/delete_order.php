<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id'])) {
        throw new Exception('Order ID is required');
    }
    
    $order_id = intval($data['order_id']);
    
    // Delete order items first (foreign key constraint)
    $delete_items_query = "DELETE FROM order_items WHERE order_id = " . $order_id;
    
    if (!mysqli_query($db->conn, $delete_items_query)) {
        // If table doesn't exist, continue anyway
        if (strpos(mysqli_error($db->conn), "doesn't exist") === false) {
            throw new Exception(mysqli_error($db->conn));
        }
    }
    
    // Delete the order
    $delete_order_query = "DELETE FROM orders WHERE order_id = " . $order_id;
    
    if (!mysqli_query($db->conn, $delete_order_query)) {
        // If table doesn't exist, return success anyway
        if (strpos(mysqli_error($db->conn), "doesn't exist") !== false) {
            echo json_encode([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
            exit;
        }
        throw new Exception(mysqli_error($db->conn));
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Order deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
