<?php
require_once '../class.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$db = new global_class();

try {
    // Query to get all task requests with member details, excluding approved/rejected tasks
    // Left join with member_self_tasks to get product_name if it's not available in task_approval_requests
    $query = "SELECT 
        mst.id as request_id,
        mst.production_id,
        mst.member_id,
        mst.product_name,
        mst.weight_g,
        mst.quantity,
        mst.length_m,
        mst.width_in,
        mst.date_created,
        mst.status,
        um.fullname as member_name,
        um.role
    FROM member_self_tasks mst
    JOIN user_member um ON mst.member_id = um.id
    WHERE mst.status = 'pending'
    ORDER BY mst.date_created DESC";

    $result = mysqli_query($db->conn, $query);

    if (!$result) {
        throw new Exception(mysqli_error($db->conn));
    }

    $requests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = [
            'request_id' => $row['request_id'],
            'production_id' => $row['production_id'],
            'member_name' => $row['member_name'],
            'role' => ucfirst($row['role']),
            'product_name' => $row['product_name'],
            'weight_g' => $row['weight_g'],
            'length_m' => $row['length_m'],
            'width_in' => $row['width_in'],
            'quantity' => $row['quantity'],
            'date_created' => date('Y-m-d H:i', strtotime($row['date_created'])),
            'status' => $row['status'],
            'type' => 'approval_request'
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($requests);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>