<?php
require_once "../../../function/database.php";

header('Content-Type: application/json');

try {
    $db = new Database();
    session_start();
    
    $member_id = $_SESSION['id'];
    
    // Get member role
    $role_query = "SELECT role FROM user_member WHERE id = ?";
    $stmt = $db->conn->prepare($role_query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $role_result = $stmt->get_result();
    $member_role = strtolower($role_result->fetch_assoc()['role']);

    // Query to get tasks created by the member
    $query = "SELECT 
        pl.prod_line_id,
        CONCAT('PL', LPAD(pl.prod_line_id, 4, '0')) as display_id,
        pl.product_name,
        pl.length_m,
        pl.width_m,
        pl.weight_g,
        pl.quantity,
        pl.status,
        pl.date_created as date_added,
        t.date_submitted
    FROM production_line pl
    JOIN task t ON pl.prod_line_id = t.prod_line_id
    WHERE t.created_by = ?
    ORDER BY pl.date_created DESC";

    $stmt = $db->conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Failed to get result: " . $db->conn->error);
    }

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates
        $row['date_added'] = $row['date_added'] ? date('Y-m-d', strtotime($row['date_added'])) : null;
        $row['date_submitted'] = $row['date_submitted'] ? date('Y-m-d', strtotime($row['date_submitted'])) : null;
        
        // Convert required_materials to array if not null
        $row['required_materials'] = $row['required_materials'] ? explode(',', $row['required_materials']) : [];
        
        // Format status
        $row['status'] = ucfirst($row['status']);
        
        $tasks[] = $row;
    }

    echo json_encode([
        'success' => true,
        'tasks' => $tasks
    ]);

} catch (Exception $e) {
    error_log("Error in get_created_tasks.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 