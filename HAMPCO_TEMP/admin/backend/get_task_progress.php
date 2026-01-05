<?php
require_once '../backend/dbconnect.php';

// Set JSON content type header
header('Content-Type: application/json');

try {
    if (!isset($_GET['prod_line_id'])) {
        throw new Exception('Missing production line ID');
    }

    $prodLineId = intval($_GET['prod_line_id']);

    // Check if database connection is valid
    if (!$db->conn || $db->conn->connect_error) {
        error_log("Database connection failed: " . ($db->conn ? $db->conn->connect_error : "No connection"));
        throw new Exception('Database connection failed');
    }

    // Add debug logging
    error_log("Fetching progress for production line ID: " . $prodLineId);

    $query = "SELECT at.*, m.fullname as member_name,
              (SELECT completion_percentage 
               FROM task_progress 
               WHERE assigned_task_id = at.id 
               ORDER BY date_created DESC 
               LIMIT 1) as completion_percentage,
              (SELECT progress_note 
               FROM task_progress 
               WHERE assigned_task_id = at.id 
               ORDER BY date_created DESC 
               LIMIT 1) as latest_note,
              (SELECT date_created 
               FROM task_progress 
               WHERE assigned_task_id = at.id 
               ORDER BY date_created DESC 
               LIMIT 1) as last_update
              FROM assigned_tasks at 
              JOIN user_member m ON at.member_id = m.id 
              WHERE at.prod_line_id = ? 
              ORDER BY at.role, at.date_created";

    if (!($stmt = $db->conn->prepare($query))) {
        error_log("Query preparation failed: " . $db->conn->error);
        error_log("Query was: " . $query);
        throw new Exception('Query preparation failed: ' . $db->conn->error);
    }

    $stmt->bind_param('i', $prodLineId);
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        error_log("Production Line ID: " . $prodLineId);
        throw new Exception('Query execution failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    
    $progress = [];
    while ($row = $result->fetch_assoc()) {
        // Add debug logging
        error_log("Processing row: " . json_encode($row));
        
        $progress[] = [
            'member_name' => $row['member_name'],
            'role' => $row['role'],
            'status' => $row['status'],
            'completion_percentage' => $row['completion_percentage'],
            'latest_note' => $row['latest_note'],
            'last_update' => $row['last_update'] ? date('F j, Y g:i A', strtotime($row['last_update'])) : null
        ];
    }

    error_log("Successfully fetched progress data: " . json_encode($progress));
    echo json_encode(['success' => true, 'progress' => $progress]);

} catch (Exception $e) {
    error_log("Error in get_task_progress.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 