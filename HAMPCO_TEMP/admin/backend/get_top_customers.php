<?php
header('Content-Type: application/json');

require_once 'dbconnect.php';

$response = ['success' => false, 'data' => [], 'error' => ''];

$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    $sql = "SELECT u.user_fullname AS customer_name, COUNT(o.order_id) AS total_orders
            FROM orders o
            JOIN users u ON o.order_user_id = u.user_id";
    
    if (!empty($search)) {
        $sql .= " WHERE u.user_fullname LIKE ?";
    }
    
    $sql .= " GROUP BY u.user_fullname
            ORDER BY total_orders DESC
            LIMIT 20";

    $stmt = $db->conn->prepare($sql);

    if (!empty($search)) {
        $searchTerm = "%{$search}%";
        $stmt->bind_param("s", $searchTerm);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $customers = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }

    $response['success'] = true;
    $response['data'] = $customers;

} catch (Exception $e) {
    $response['error'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>