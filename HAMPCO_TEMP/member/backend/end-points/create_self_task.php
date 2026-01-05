<?php
session_start();
require_once "../../../function/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = new Database();
$member_id = $_SESSION['id'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Validate required fields
if (!isset($data['product_name']) || !isset($data['weight'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Insert the task
    $stmt = $db->conn->prepare("
        INSERT INTO member_self_tasks 
        (member_id, product_name, weight_g, status) 
        VALUES (?, ?, ?, 'pending')
    ");

    $stmt->bind_param("isd", 
        $member_id,
        $data['product_name'],
        $data['weight']
    );

    if ($stmt->execute()) {
        $task_id = $stmt->insert_id;
        
        // Get the created task details
        $stmt = $db->conn->prepare("
            SELECT production_id, product_name, weight_g, status, date_created 
            FROM member_self_tasks 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $task = $result->fetch_assoc();

        echo json_encode([
            'success' => true, 
            'message' => 'Task created successfully',
            'task' => $task
        ]);
    } else {
        throw new Exception("Failed to create task");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error creating task: ' . $e->getMessage()
    ]);
} 