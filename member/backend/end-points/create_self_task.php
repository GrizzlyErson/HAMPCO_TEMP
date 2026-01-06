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

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['product_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data: product_name is empty']);
    exit;
}

try {
    $stmt = $db->conn->prepare("
        INSERT INTO member_self_tasks 
        (member_id, product_name, weight_g, length_m, width_in, quantity, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");

    $weight = $data['weight'] ?? null;
    $length = $data['length'] ?? null;
    $width = $data['width'] ?? null;
    $quantity = $data['quantity'] ?? null;

    $stmt->bind_param("isdddi",
        $member_id,
        $data['product_name'],
        $weight,
        $length,
        $width,
        $quantity
    );

    if ($stmt->execute()) {
        $task_id = $stmt->insert_id;
        
        $stmt = $db->conn->prepare("
            SELECT * 
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