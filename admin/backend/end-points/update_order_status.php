<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();
    
    // Get JSON data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['order_id']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Order ID and status are required'
        ]);
        exit;
    }
    
    $order_id = intval($input['order_id']);
    $status = trim($input['status']);
    
    // Validate status
    $valid_statuses = ['Pending', 'Accepted', 'Processing', 'Shipped', 'Delivered', 'Declined', 'Cancelled'];
    if (!in_array($status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid status value'
        ]);
        exit;
    }
    
    if ($order_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid order ID'
        ]);
        exit;
    }
    
    // Update order status using correct column name
    $query = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $db->conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->conn->error);
    }
    
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Order not found'
            ]);
        }
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
