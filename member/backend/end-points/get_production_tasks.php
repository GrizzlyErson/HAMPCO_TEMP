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
        'pending_tasks' => [],
        'in_progress_tasks' => [],
        'completed_tasks' => []
    ];

    // Get pending tasks (tasks specifically assigned to this member but not yet accepted)
    $pending_tasks_query = "SELECT 
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

    $stmt = $db->conn->prepare($pending_tasks_query);
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
            $response['pending_tasks'][] = $row;
        }
    }
    $stmt->close();

    // Get in-progress tasks
    $in_progress_tasks_query = "SELECT 
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
        AND ta.status = 'in_progress'
        AND pl.status NOT IN ('completed', 'submitted')  -- Exclude tasks from completed/submitted production lines
        ORDER BY ta.created_at DESC";

    $stmt = $db->conn->prepare($in_progress_tasks_query);
    if (!$stmt) {
        throw new Exception("Prepare failed for in-progress tasks: " . $db->conn->error);
    }
    
    $stmt->bind_param("i", $member_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed for in-progress tasks: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['display_id'] = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
            $response['in_progress_tasks'][] = $row;
        }
    }
    $stmt->close();

    // Get completed tasks
    $completed_tasks_query = "SELECT 
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
        AND ta.status IN ('completed', 'submitted')
        ORDER BY ta.created_at DESC";

    $stmt = $db->conn->prepare($completed_tasks_query);
    if (!$stmt) {
        throw new Exception("Prepare failed for completed tasks: " . $db->conn->error);
    }
    
    $stmt->bind_param("i", $member_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed for completed tasks: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['display_id'] = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
            $response['completed_tasks'][] = $row;
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