<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
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
    
    // Log the request for debugging
    error_log("update_self_task.php - Action: " . $data['action'] . ", ID/ProdID: " . $data['production_id'] . ", Member: " . $member_id);

    $prod_id_input = trim($data['production_id']);

    if (empty($prod_id_input)) {
        throw new Exception("Production ID is empty/invalid");
    }

    // --- ROBUST TASK SEARCH START ---
    $task = null;

    // 1. Exact match (Production ID or ID)
    $stmt = $db->conn->prepare("SELECT * FROM member_self_tasks WHERE (production_id = ? OR id = ?) AND member_id = ?");
    $stmt->bind_param("ssi", $prod_id_input, $prod_id_input, $member_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 2. Loose match (if not found) - e.g. finding 'PL-146' when searching '146'
    if (!$task) {
        $stmt = $db->conn->prepare("SELECT * FROM member_self_tasks WHERE production_id LIKE ? AND member_id = ? LIMIT 1");
        $like_param = "%" . $prod_id_input;
        $stmt->bind_param("si", $like_param, $member_id);
        $stmt->execute();
        $task = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    // 3. Numeric ID extraction (if not found) - e.g. finding ID 147 from input "PL-147"
    if (!$task) {
        $numeric_id = preg_replace('/[^0-9]/', '', $prod_id_input);
        if (!empty($numeric_id)) {
            $stmt = $db->conn->prepare("SELECT * FROM member_self_tasks WHERE id = ? AND member_id = ?");
            $stmt->bind_param("i", $numeric_id, $member_id);
            $stmt->execute();
            $task = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }

    if (!$task) {
        // Check if it exists in task_assignments (User confusion check)
        $ta_check = $db->conn->prepare("SELECT id FROM task_assignments WHERE prod_line_id = ? AND member_id = ?");
        $ta_check->bind_param("si", $prod_id_input, $member_id);
        $ta_check->execute();
        if ($ta_check->get_result()->fetch_assoc()) {
             $ta_check->close();
             throw new Exception("This is an Assigned Task. Please submit it via the Assigned Tasks section.");
        }
        $ta_check->close();

        // Log available tasks for debugging
        // Check if task exists for ANY member to diagnose ownership issues
        $any_check = $db->conn->prepare("SELECT member_id FROM member_self_tasks WHERE production_id = ? OR id = ?");
        $any_check->bind_param("ss", $prod_id_input, $prod_id_input);
        $any_check->execute();
        $any_res = $any_check->get_result();
        if ($row = $any_res->fetch_assoc()) {
            $any_check->close();
            throw new Exception("Task found but belongs to Member ID " . $row['member_id'] . " (You are ID $member_id)");
        }
        $any_check->close();

        $debug_q = $db->conn->query("SELECT id, production_id FROM member_self_tasks WHERE member_id = $member_id ORDER BY id DESC LIMIT 5");
        $available = [];
        while($row = $debug_q->fetch_assoc()) { $available[] = "ID:" . $row['id'] . "/Prod:" . $row['production_id']; }
        $avail_str = implode(", ", $available);
        throw new Exception("Task not found. Input: '$prod_id_input'. Your recent tasks: [$avail_str]");
    }
    // --- ROBUST TASK SEARCH END ---

    switch ($data['action']) {
        case 'start':
            if ($task['status'] === 'in_progress') {
                 echo json_encode(['success' => true, 'message' => 'Task already started']);
                 exit;
            }
            if ($task['status'] !== 'pending') {
                throw new Exception("Cannot start task. Current status: " . $task['status']);
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
            $stmt = $db->conn->prepare("UPDATE member_self_tasks SET status = 'in_progress' WHERE id = ?");
            $stmt->bind_param("i", $task['id']);
            break;

        case 'submit':
            if ($task['status'] === 'submitted') {
                 echo json_encode(['success' => true, 'message' => 'Task already submitted']);
                 exit;
            }
            if ($task['status'] !== 'in_progress') {
                throw new Exception("Cannot submit task. Current status: " . $task['status'] . " (Required: in_progress)");
            }

            $stmt = $db->conn->prepare("UPDATE member_self_tasks SET status = 'submitted', date_submitted = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("i", $task['id']);
            break;

        case 'delete':
            if ($task['status'] === 'submitted') {
                throw new Exception("Cannot delete submitted task");
            }
            $stmt = $db->conn->prepare("DELETE FROM member_self_tasks WHERE id = ?");
            $stmt->bind_param("i", $task['id']);
            break;

        default:
            throw new Exception('Invalid action');
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to " . $data['action'] . " task");
    }

    // Note: affected_rows might be 0 if data didn't change, but we handled status checks above.

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