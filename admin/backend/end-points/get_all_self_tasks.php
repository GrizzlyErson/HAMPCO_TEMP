<?php
require_once '../../../function/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$db = new Database();

try {
    $stmt = $db->conn->prepare("
        SELECT 
            mst.id,
            mst.production_id,
            mst.product_name,
            mst.weight_g,
            mst.status,
            mst.raw_materials,
            mst.date_created,
            mst.date_submitted,
            um.fullname as member_name,
            um.role,
            COALESCE(tar.status, 'pending') as approval_status
        FROM member_self_tasks mst
        LEFT JOIN user_member um ON mst.member_id = um.id
        LEFT JOIN task_approval_requests tar ON mst.production_id = tar.production_id
        WHERE mst.status IN ('pending', 'in_progress', 'submitted')
        ORDER BY mst.date_created DESC
    ");

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $tasks = [];

    while ($row = $result->fetch_assoc()) {
        // Format dates
        $row['date_created'] = date('Y-m-d H:i:s', strtotime($row['date_created']));
        $row['date_submitted'] = $row['date_submitted'] ? date('Y-m-d H:i:s', strtotime($row['date_submitted'])) : null;
        
        $tasks[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'tasks' => $tasks,
        'count' => count($tasks)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching self tasks: ' . $e->getMessage()
    ]);
}
?>
