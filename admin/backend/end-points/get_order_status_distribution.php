<?php
header('Content-Type: application/json');
require_once '../dbconnect.php';

$response = ['success' => false];

try {
    if(!isset($db) || $db->conn->connect_error) {
        $db = new dbconnect();
    }
    
    $sql = "SELECT order_status, COUNT(*) as status_count
            FROM orders
            GROUP BY order_status";
    $stmt = $db->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $status_distribution = [];
    while($row = $result->fetch_assoc()) {
        $status_distribution[] = $row;
    }

    $response['success'] = true;
    $response['status_distribution'] = $status_distribution;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
