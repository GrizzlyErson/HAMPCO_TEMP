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
error_log("create_self_task.php: Received data: " . print_r($data, true));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

error_log("create_self_task.php: Member role: " . $member_role);

// Validate required fields dynamically based on member_role
if ($member_role === 'knotter' || $member_role === 'warper') {
    if (!isset($data['product_name']) || !isset($data['weight']) || !is_numeric($data['weight'])) {
        error_log("create_self_task.php: Validation failed for " . $member_role . ". Data: " . print_r($data, true));
        echo json_encode(['success' => false, 'message' => 'Missing or invalid fields for ' . $member_role]);
        exit;
    }
} elseif ($member_role === 'weaver') {
    if (!isset($data['product_name']) || !isset($data['length']) || !is_numeric($data['length']) || !isset($data['width']) || !is_numeric($data['width']) || !isset($data['quantity']) || !is_numeric($data['quantity'])) {
        error_log("create_self_task.php: Validation failed for " . $member_role . ". Data: " . print_r($data, true));
        echo json_encode(['success' => false, 'message' => 'Missing or invalid fields for ' . $member_role]);
        exit;
    }
} else {
    error_log("create_self_task.php: Unknown member role: " . $member_role);
    echo json_encode(['success' => false, 'message' => 'Unknown member role']);
    exit;
}

// Check task limit before proceeding
$limit_query = "SELECT task_limit FROM user_member WHERE id = ?";
$stmt = $db->conn->prepare($limit_query);
if ($stmt) {
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $limit_result = $stmt->get_result();
    $limit_row = $limit_result->fetch_assoc();
    $task_limit = $limit_row['task_limit'] ?? 10; // Default to 10 if not set
    $stmt->close();
} else {
    $task_limit = 10;
}

// Count active tasks (self-assigned)
$active_tasks = 0;
$self_query = "SELECT COUNT(*) as count FROM member_self_tasks WHERE member_id = ? AND status IN ('pending', 'in_progress', 'submitted')";
$stmt = $db->conn->prepare($self_query);
if ($stmt) {
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $active_tasks += $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}

// Count active tasks (assigned by admin)
$assign_query = "SELECT COUNT(*) as count FROM task_assignments WHERE member_id = ? AND status IN ('pending', 'in_progress', 'submitted')";
$stmt = $db->conn->prepare($assign_query);
if ($stmt) {
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $active_tasks += $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}

if ($active_tasks >= $task_limit) {
    echo json_encode(['success' => false, 'message' => "Task limit reached. You have $active_tasks active tasks (Limit: $task_limit)."]);
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
        error_log("create_self_task.php: Knotter/Warper INSERT details - SQL: $insert_sql, Types: $bind_types, Params: " . print_r($bind_params, true));
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
        error_log("create_self_task.php: Weaver INSERT details - SQL: $insert_sql, Types: $bind_types, Params: " . print_r($bind_params, true));
    }

    $stmt = $db->conn->prepare($insert_sql);
    if (!$stmt) {
        error_log("create_self_task.php: Failed to prepare statement: " . $db->conn->error);
        throw new Exception("Failed to prepare statement: " . $db->conn->error);
    }
    
    // Use call_user_func_array to bind parameters dynamically
    // The bind_param method requires parameters to be passed by reference.
    // We need to create an array of references for the values.
    $refs = [];
    foreach ($bind_params as $key => $value) {
        $refs[$key] = &$bind_params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], array_merge([$bind_types], $refs));

    if ($stmt->execute()) {
        error_log("create_self_task.php: Statement executed successfully.");
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
        error_log("create_self_task.php: Failed to execute statement: " . $stmt->error);
        throw new Exception("Failed to create task: " . $stmt->error);
    }
} catch (Exception $e) {
    error_log("create_self_task.php: Caught exception: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error creating task: ' . $e->getMessage()
    ]);
} 