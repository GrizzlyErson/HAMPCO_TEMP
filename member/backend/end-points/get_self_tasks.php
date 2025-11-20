<?php
session_start();
require_once "../../../function/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = new Database();
$member_id = $_SESSION['id'];

try {
    $stmt = $db->conn->prepare("
        SELECT 
            mst.production_id,
            mst.product_name,
            mst.weight_g,
            mst.status,
            mst.raw_materials,
            mst.date_created,
            mst.date_submitted,
            COALESCE(tar.status, 'pending') as approval_status
        FROM member_self_tasks mst
        LEFT JOIN task_approval_requests tar ON mst.production_id = tar.production_id
        WHERE mst.member_id = ?
        ORDER BY mst.date_created DESC
    ");

    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates
        $row['date_created'] = date('Y-m-d H:i:s', strtotime($row['date_created']));
        $row['date_submitted'] = $row['date_submitted'] ? date('Y-m-d H:i:s', strtotime($row['date_submitted'])) : null;
        
        $tasks[] = $row;
    }

    echo json_encode([
        'success' => true,
        'tasks' => $tasks
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching tasks: ' . $e->getMessage()
    ]);
} 