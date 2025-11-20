<?php
session_start();
require_once "../../../function/database.php";
require_once "../class/RawMaterialsManager.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = new Database();
$member_id = $_SESSION['id'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['production_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

try {
    $db->conn->begin_transaction();

    switch ($data['action']) {
        case 'start':
            // First, get the task details
            $get_task = $db->conn->prepare("
                SELECT product_name, weight_g 
                FROM member_self_tasks 
                WHERE production_id = ? AND member_id = ? AND status = 'pending'
            ");
            $get_task->bind_param("si", $data['production_id'], $member_id);
            $get_task->execute();
            $task_result = $get_task->get_result();
            $task = $task_result->fetch_assoc();

            if (!$task) {
                throw new Exception('Task not found or already started');
            }

            // Calculate and deduct required materials
            $materialsManager = new RawMaterialsManager($db);
            $required_materials = $materialsManager->calculateRequiredMaterials(
                $task['product_name'], 
                $task['weight_g']
            );

            // Deduct materials from inventory
            $materialsManager->deductMaterials($required_materials);

            // Update task status
            $stmt = $db->conn->prepare("
                UPDATE member_self_tasks 
                SET status = 'in_progress' 
                WHERE production_id = ? AND member_id = ? AND status = 'pending'
            ");
            $stmt->bind_param("si", $data['production_id'], $member_id);
            break;

        case 'submit':
            $stmt = $db->conn->prepare("
                UPDATE member_self_tasks 
                SET status = 'submitted', date_submitted = CURRENT_TIMESTAMP 
                WHERE production_id = ? AND member_id = ? AND status = 'in_progress'
            ");
            $stmt->bind_param("si", $data['production_id'], $member_id);
            break;

        case 'delete':
            $stmt = $db->conn->prepare("
                DELETE FROM member_self_tasks 
                WHERE production_id = ? AND member_id = ? AND status != 'submitted'
            ");
            $stmt->bind_param("si", $data['production_id'], $member_id);
            break;

        default:
            throw new Exception('Invalid action');
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to " . $data['action'] . " task");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('No task was updated. It might have been already processed or deleted.');
    }

    $db->conn->commit();

    echo json_encode([
        'success' => true,
        'message' => ucfirst($data['action']) . ' task successful'
    ]);

} catch (Exception $e) {
    $db->conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error processing task: ' . $e->getMessage()
    ]);
} 