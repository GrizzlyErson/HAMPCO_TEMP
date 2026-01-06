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

// Get new tasks (tasks specifically assigned to this member but not yet accepted)
$new_tasks_query = "SELECT DISTINCT
    pl.prod_line_id,
    pl.product_name,
    pl.length_m,
    pl.width_m,
    pl.weight_g,
    pl.quantity,
    pl.status as prod_status,
    MIN(ta.status) as task_status,
    MIN(ta.deadline) as deadline,
    MIN(ta.id) as task_id
    FROM production_line pl
    JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
    WHERE ta.member_id = ? 
    AND ta.status = 'pending'
    AND pl.status NOT IN ('completed', 'submitted')
    AND NOT EXISTS (
        SELECT 1 
        FROM task_assignments ta2 
        WHERE ta2.prod_line_id = pl.prod_line_id 
        AND ta2.member_id = ta.member_id 
        AND ta2.status IN ('in_progress', 'completed', 'submitted', 'declined')
    )
    GROUP BY pl.prod_line_id
    ORDER BY pl.date_created DESC";

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