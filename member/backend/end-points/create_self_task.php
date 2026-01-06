<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../../../function/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = new Database();
$member_id = $_SESSION['id'];

// Get member role
$role_query = "SELECT role FROM user_member WHERE id = ?";
$stmt = $db->conn->prepare($role_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$role_result = $stmt->get_result();
$role_data = $role_result->fetch_assoc();
$member_role = strtolower($role_data['role']);

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Validate required fields dynamically based on member_role
if ($member_role === 'knotter' || $member_role === 'warper') {
    if (!isset($data['product_name']) || !isset($data['weight']) || !is_numeric($data['weight'])) {
        echo json_encode(['success' => false, 'message' => 'Missing or invalid fields for ' . $member_role]);
        exit;
    }
} elseif ($member_role === 'weaver') {
    if (!isset($data['product_name']) || !isset($data['length']) || !is_numeric($data['length']) || !isset($data['width']) || !is_numeric($data['width']) || !isset($data['quantity']) || !is_numeric($data['quantity'])) {
        echo json_encode(['success' => false, 'message' => 'Missing or invalid fields for ' . $member_role]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unknown member role']);
    exit;
}

try {
    // Generate a unique production ID
    $production_id = uniqid('PL', true);

    // Prepare dynamic INSERT statement
    $insert_sql = "";
    $bind_types = "";
    $bind_params = [];

    if ($member_role === 'knotter' || $member_role === 'warper') {
        $insert_sql = "
            INSERT INTO member_self_tasks 
            (production_id, member_id, product_name, weight_g, status) 
            VALUES (?, ?, ?, ?, 'pending')
        ";
        $bind_types = "sisd";
        $bind_params = [
            $production_id,
            $member_id,
            $data['product_name'],
            $data['weight']
        ];
    } elseif ($member_role === 'weaver') {
        $insert_sql = "
            INSERT INTO member_self_tasks 
            (production_id, member_id, product_name, length_m, width_in, quantity, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ";
        $bind_types = "sisddd";
        $bind_params = [
            $production_id,
            $member_id,
            $data['product_name'],
            $data['length'],
            $data['width'],
            $data['quantity']
        ];
    }

    $stmt = $db->conn->prepare($insert_sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $db->conn->error);
    }
    
    // Use call_user_func_array to bind parameters dynamically
    call_user_func_array([$stmt, 'bind_param'], array_merge([$bind_types], $bind_params));

    if ($stmt->execute()) {
        $task_id = $stmt->insert_id;
        
        // Get the created task details dynamically based on role
        $select_fields = "production_id, product_name, status, date_created";
        if ($member_role === 'knotter' || $member_role === 'warper') {
            $select_fields .= ", weight_g";
        } elseif ($member_role === 'weaver') {
            $select_fields .= ", length_m, width_in, quantity";
        }

        $stmt = $db->conn->prepare("
            SELECT $select_fields
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
        throw new Exception("Failed to create task: " . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error creating task: ' . $e->getMessage()
    ]);
} 