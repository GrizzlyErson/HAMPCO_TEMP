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

    $query = "SELECT ta.member_id, ta.role, ta.status, ta.estimated_time, ta.deadline, m.fullname as name 
              FROM task_assignments ta 
              JOIN user_member m ON ta.member_id = m.id 
              WHERE ta.prod_line_id = ? 
              GROUP BY ta.member_id, ta.role
              ORDER BY ta.role, ta.created_at";

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
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = [
            'name' => $row['name'],
            'role' => $row['role'],
            'status' => $row['status'],
            'estimated_time' => $row['estimated_time'],
            'deadline' => date('F j, Y', strtotime($row['deadline']))
        ];
    }

    error_log("Successfully fetched " . count($members) . " members for production line ID: " . $prodLineId);
    echo json_encode(['success' => true, 'members' => $members]);

} catch (Exception $e) {
    error_log("Error in get_assigned_members.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 