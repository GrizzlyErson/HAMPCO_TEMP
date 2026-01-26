<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once '../../../function/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['production_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Production ID is required']);
    exit;
}

$production_id = $_GET['production_id'];

try {
    // Create mysqli connection using the same credentials from connection.php
    $db = new mysqli($host, $username, $password, $dbname);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8mb4");
    
    // First try to get from member_self_tasks (self-assigned tasks) - this is the primary source for task completions
    $query = "
        SELECT 
            mst.production_id,
            mst.product_name,
            mst.weight_g,
            mst.length_m,
            mst.width_in as width_m,
            um.fullname as member_name,
            um.role,
            mst.status
        FROM member_self_tasks mst
        JOIN user_member um ON mst.member_id = um.id
        WHERE mst.production_id = ?
        LIMIT 1
    ";
    
    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    
    $stmt->bind_param("s", $production_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    $stmt->close();
    
    // If not found in member_self_tasks, try task_assignments (assigned tasks)
    if (!$task) {
        $query = "
            SELECT 
                ta.prod_line_id as production_id,
                pl.product_name,
                pl.weight_g,
                pl.length_m,
                pl.width_m,
                um.fullname as member_name,
                um.role,
                pl.status
            FROM task_assignments ta
            JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
            JOIN user_member um ON ta.member_id = um.id
            WHERE ta.prod_line_id = ?
            LIMIT 1
        ";
        
        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }
        
        $stmt->bind_param("s", $production_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $task = $result->fetch_assoc();
        $stmt->close();
    }
    
    $db->close();
    
    if (!$task) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Task not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $task
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_task_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
