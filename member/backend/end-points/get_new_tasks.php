<?php
require_once "../../../function/database.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = new Database();
$member_id = $_SESSION['id'];

// Get new tasks (tasks specifically assigned to this member with pending status)
// This includes newly reassigned tasks with pending status
$new_tasks_query = "SELECT 
    pl.prod_line_id,
    pl.product_name,
    pl.length_m,
    pl.width_m,
    pl.weight_g,
    pl.quantity,
    pl.status as prod_status,
    ta.status as task_status,
    ta.deadline,
    ta.id as task_id,
    ta.role,
    ta.created_at
    FROM production_line pl
    JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
    WHERE ta.member_id = ? 
    AND ta.status IN ('pending', 'reassigned')
    ORDER BY ta.created_at DESC";

$stmt = $db->conn->prepare($new_tasks_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$new_tasks_result = $stmt->get_result();
$new_tasks = [];

while ($row = $new_tasks_result->fetch_assoc()) {
    $row['display_id'] = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
    $row['status'] = $row['task_status'] ?? 'pending';
    $new_tasks[] = $row;
}

echo json_encode(['success' => true, 'tasks' => $new_tasks]);
?>