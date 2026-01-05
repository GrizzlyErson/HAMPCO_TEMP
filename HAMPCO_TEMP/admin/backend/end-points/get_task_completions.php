<?php
session_start();
require_once '../../../function/connection.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in and is admin
    if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Get database connection
    $db = new mysqli($host, $username, $password, $dbname);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }

    // Query to get task completion confirmations
    // Only show tasks that have been submitted
    // Exclude in_progress and completed tasks
    $query = "
        SELECT 
            tcc.production_id,
            um.fullname as member_name,
            um.role,
            tcc.product_name,
            tcc.weight,
            DATE_FORMAT(tcc.date_started, '%Y-%m-%d %H:%i') as date_started,
            DATE_FORMAT(tcc.date_submitted, '%Y-%m-%d %H:%i') as date_submitted,
            tcc.status
        FROM task_completion_confirmations tcc
        JOIN user_member um ON tcc.member_id = um.id
        WHERE tcc.status = 'submitted'
        AND tcc.date_submitted IS NOT NULL
        GROUP BY tcc.production_id, um.id
        ORDER BY tcc.date_submitted DESC
    ";

    $result = $db->query($query);
    if (!$result) {
        error_log("Error in get_task_completions.php: " . $db->error);
        throw new Exception("Error executing query: " . $db->error);
    }

    $completions = array();
    while ($row = $result->fetch_assoc()) {
        // Format the dates
        $row['date_started'] = $row['date_started'] ?: null;
        $row['date_submitted'] = $row['date_submitted'] ?: null;
        $completions[] = $row;
    }

    echo json_encode($completions);

} catch (Exception $e) {
    error_log("Error in get_task_completions.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 