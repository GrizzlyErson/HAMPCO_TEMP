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
    // Query to get all task requests with member details, excluding approved tasks
    $query = "SELECT 
        tar.id as request_id,
        tar.production_id,
        tar.member_id,
        tar.product_name,
        tar.weight_g,
        tar.quantity,
        tar.date_created,
        tar.status,
        um.fullname as member_name,
        um.role
    FROM task_approval_requests tar
    JOIN user_member um ON tar.member_id = um.id
    WHERE tar.status != 'approved'
    ORDER BY tar.date_created DESC";

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
            'quantity' => $row['quantity'],
            'date_created' => date('Y-m-d H:i', strtotime($row['date_created'])),
            'status' => $row['status'],
            'type' => 'approval_request'
        ];
    }

    // Also get member self-created tasks
    $self_tasks_query = "SELECT 
        mst.id as request_id,
        mst.production_id,
        mst.member_id,
        mst.product_name,
        mst.weight_g,
        NULL as quantity,
        mst.date_created,
        mst.status,
        um.fullname as member_name,
        um.role
    FROM member_self_tasks mst
    JOIN user_member um ON mst.member_id = um.id
    WHERE mst.status IN ('pending', 'in_progress', 'submitted')
    ORDER BY mst.date_created DESC";

    $self_result = mysqli_query($db->conn, $self_tasks_query);

    if ($self_result) {
        while ($row = mysqli_fetch_assoc($self_result)) {
            $requests[] = [
                'request_id' => $row['request_id'],
                'production_id' => $row['production_id'],
                'member_name' => $row['member_name'],
                'role' => ucfirst($row['role']),
                'product_name' => $row['product_name'],
                'weight_g' => $row['weight_g'],
                'quantity' => $row['quantity'],
                'date_created' => date('Y-m-d H:i', strtotime($row['date_created'])),
                'status' => $row['status'],
                'type' => 'self_task'
            ];
        }
    }

    // Sort all requests by date_created (most recent first)
    usort($requests, function($a, $b) {
        return strtotime($b['date_created']) - strtotime($a['date_created']);
    });

    header('Content-Type: application/json');
    echo json_encode($requests);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>