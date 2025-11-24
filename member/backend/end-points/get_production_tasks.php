<?php
session_start();
header('Content-Type: application/json');

require_once '../../../function/database.php';
$db = new Database();

try {
    if (!isset($_SESSION['id'])) {
        throw new Exception('Not logged in');
    }

    $member_id = $_SESSION['id'];
    $response = [
        'success' => true,
        'new_tasks' => [],
        'assigned_tasks' => []
    ];

    // Get new tasks (tasks specifically assigned to this member but not yet accepted)
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
        ta.id as task_id
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
        ORDER BY pl.date_created DESC";

    $stmt = $db->conn->prepare($new_tasks_query);
    if (!$stmt) {
        throw new Exception("Prepare failed for new tasks: " . $db->conn->error);
    }
    
    $stmt->bind_param("i", $member_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed for new tasks: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['display_id'] = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
            $row['status'] = $row['task_status'] ?? 'pending';
            $response['new_tasks'][] = $row;
        }
    }
    $stmt->close();

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
        ta.updated_at as date_submitted
        FROM production_line pl
        JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
        WHERE ta.member_id = ? 
        AND ta.status NOT IN ('pending', 'completed')  -- Exclude pending and completed tasks
        AND pl.status NOT IN ('completed', 'submitted')  -- Exclude tasks from completed/submitted production lines
        ORDER BY ta.created_at DESC";

    $stmt = $db->conn->prepare($assigned_tasks_query);
    if (!$stmt) {
        throw new Exception("Prepare failed for assigned tasks: " . $db->conn->error);
    }
    
    $stmt->bind_param("i", $member_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed for assigned tasks: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['display_id'] = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
            $response['assigned_tasks'][] = $row;
        }
    }
    $stmt->close();

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 