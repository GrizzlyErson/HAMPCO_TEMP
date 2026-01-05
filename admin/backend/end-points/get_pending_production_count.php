<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../class.php';

try {
    $db = new global_class();
    $conn = $db->conn;
    
    // Count pending/incomplete tasks (status != 'completed' OR status IS NULL)
    $query = "SELECT COUNT(*) as pending_count
    FROM task_assignments ta
    LEFT JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
    WHERE ta.status != 'completed' OR ta.status IS NULL";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'pendingCount' => intval($row['pending_count'])
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
