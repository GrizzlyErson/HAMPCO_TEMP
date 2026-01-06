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

// Get assigned tasks (tasks that have been accepted/started)
$assigned_tasks_query = "SELECT 
    pl.prod_line_id,
    pl.product_name,
    pl.length_m,
    pl.width_m,
    pl.weight_g,
    pl.quantity,
    ta.status,
    ta.created_at as date_started,
    CASE 
        WHEN ta.status = 'completed' OR ta.status = 'submitted' THEN ta.updated_at 
        ELSE NULL 
    END as date_submitted
    FROM production_line pl
    JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
    WHERE ta.member_id = ? 
    AND ta.status NOT IN ('pending', 'completed')  -- Exclude pending and completed tasks
    AND pl.status NOT IN ('completed', 'submitted')  -- Exclude tasks from completed/submitted production lines
    ORDER BY ta.created_at DESC";

$stmt = $db->conn->prepare($assigned_tasks_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$assigned_tasks_result = $stmt->get_result();
$assigned_tasks = [];

while ($row = $assigned_tasks_result->fetch_assoc()) {
    $row['display_id'] = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
    $assigned_tasks[] = $row;
}

echo json_encode(['success' => true, 'tasks' => $assigned_tasks]);
?>