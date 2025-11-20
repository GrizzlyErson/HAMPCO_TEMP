<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();
    
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : 'all';
    
    // Since there's no orders table yet, return empty result
    // In a real application, this would query the database
    $query = "SELECT * FROM orders WHERE 1=1";
    
    if ($status !== 'all') {
        $query .= " AND status = '" . mysqli_real_escape_string($db->conn, $status) . "'";
    }
    
    if ($payment_method !== 'all') {
        $query .= " AND payment_method = '" . mysqli_real_escape_string($db->conn, $payment_method) . "'";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $result = mysqli_query($db->conn, $query);
    
    if (!$result) {
        // If table doesn't exist, return empty array
        if (strpos(mysqli_error($db->conn), "doesn't exist") !== false) {
            echo json_encode([
                'success' => true,
                'orders' => []
            ]);
            exit;
        }
        throw new Exception(mysqli_error($db->conn));
    }
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
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
