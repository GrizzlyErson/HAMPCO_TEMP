<?php
require_once '../class.php';
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$db = new global_class();
$conn = $db->conn;

// Helper function to find an available member for a given role
function findAvailableMemberForRole($conn, $role) {
    $find_member_sql = "
        SELECT 
            um.id, um.role, COUNT(ta.member_id) as assigned_tasks_count
        FROM 
            user_member um
        LEFT JOIN 
            task_assignments ta ON um.id = ta.member_id AND ta.status != 'completed'
        WHERE 
            um.role = ? AND um.work_status != 'Work In Progress' AND (um.status = 'Verified' OR um.status = 'Active')
        GROUP BY
            um.id, um.role
        ORDER BY
            assigned_tasks_count ASC, um.id ASC
        LIMIT 1
    ";
    $find_member_stmt = $conn->prepare($find_member_sql);
    $find_member_stmt->bind_param('s', $role);
    $find_member_stmt->execute();
    $member_result = $find_member_stmt->get_result();
    $auto_assigned_member = $member_result->fetch_assoc();
    $find_member_stmt->close();
    return $auto_assigned_member;
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("create_task.php received POST data: " . print_r($_POST, true));
    $product_name = $_POST['product_name'] ?? '';
    $length = $_POST['length'] ?? null;
    $width = $_POST['width'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;

    // Multi-role assignment fields
    $knotter_ids = $_POST['knotter_id'] ?? []; // Expecting an array
    $warper_id = $_POST['warper_id'] ?? null;
    $weaver_id = $_POST['weaver_id'] ?? null;
    
    $knotter_deadline = $_POST['knotter_deadline'] ?? null; // Specific deadline for knotter
    $warper_deadline = $_POST['warper_deadline'] ?? null;
    $weaver_deadline = $_POST['weaver_deadline'] ?? null;

    $conn->begin_transaction();

    try {
        $sql = "INSERT INTO production_line (product_name, length_m, width_m, weight_g, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sdddi', $product_name, $length, $width, $weight, $quantity);
        $stmt->execute();
        $prod_line_id = $conn->insert_id;

        // Determine required roles based on product type for auto-assignment guidance
        $required_roles_for_product = [];
        switch ($product_name) {
            case 'Piña Seda':
            case 'Pure Piña Cloth':
                $required_roles_for_product[] = 'weaver';
                break;
            case 'Knotted Liniwan':
            case 'Knotted Bastos':
                $required_roles_for_product[] = 'knotter';
                break;
            case 'Warped Silk':
                $required_roles_for_product[] = 'warper';
                break;
        }

        // --- Multi-role Automatic Assignment and Deadline Logic ---

        // Knotter Assignment Check
        if (in_array('knotter', $required_roles_for_product) && empty($knotter_ids)) {
            $auto_assigned_knotter = findAvailableMemberForRole($conn, 'knotter');
            if ($auto_assigned_knotter) {
                $knotter_ids[] = $auto_assigned_knotter['id']; // Add auto-assigned knotter
                if (empty($knotter_deadline)) { // Auto-set deadline if not provided
                    $knotter_deadline = date('Y-m-d H:i:s', strtotime('+5 days')); // Default deadline for knotters
                }
            } else {
                throw new Exception("No available knotter found for automatic assignment for " . $product_name . ". Please assign manually.");
            }
        }

        // Warper Assignment Check
        if (in_array('warper', $required_roles_for_product) && empty($warper_id)) {
            $auto_assigned_warper = findAvailableMemberForRole($conn, 'warper');
            if ($auto_assigned_warper) {
                $warper_id = $auto_assigned_warper['id']; // Add auto-assigned warper
                if (empty($warper_deadline)) { // Auto-set deadline if not provided
                    $warper_deadline = date('Y-m-d H:i:s', strtotime('+3 days')); // Default deadline for warpers
                }
            } else {
                throw new Exception("No available warper found for automatic assignment for " . $product_name . ". Please assign manually.");
            }
        }

        // Weaver Assignment Check
        if (in_array('weaver', $required_roles_for_product) && empty($weaver_id)) {
            $auto_assigned_weaver = findAvailableMemberForRole($conn, 'weaver');
            if ($auto_assigned_weaver) {
                $weaver_id = $auto_assigned_weaver['id']; // Add auto-assigned weaver
                if (empty($weaver_deadline)) { // Auto-set deadline if not provided
                    $weaver_deadline = date('Y-m-d H:i:s', strtotime('+10 days')); // Default deadline for weavers
                }
            } else {
                throw new Exception("No available weaver found for automatic assignment for " . $product_name . ". Please assign manually.");
            }
        }
        // --- End Multi-role Automatic Assignment Logic ---

        // Task Assignment Logic
        $assignments = [];
        // Knotter assignment
        if (!empty($knotter_ids)) { // knotter_ids is an array from frontend
            foreach ($knotter_ids as $k_id) {
                if (!empty($k_id)) {
                    $assignments[] = ['member_id' => $k_id, 'role' => 'knotter', 'deadline' => $knotter_deadline];
                }
            }
        }
        // Warper assignment
        if (!empty($warper_id)) {
            $assignments[] = ['member_id' => $warper_id, 'role' => 'warper', 'deadline' => $warper_deadline];
        }
        // Weaver assignment
        if (!empty($weaver_id)) {
            $assignments[] = ['member_id' => $weaver_id, 'role' => 'weaver', 'deadline' => $weaver_deadline];
        }

        if (!empty($assignments)) {
            $sql = "INSERT INTO task_assignments (prod_line_id, member_id, role, status, deadline) VALUES (?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($sql);

            foreach ($assignments as $assignment) {
                $stmt->bind_param('iiss', $prod_line_id, $assignment['member_id'], $assignment['role'], $assignment['deadline']);
                $stmt->execute();
                if ($stmt->affected_rows === 0) {
                    throw new Exception("Failed to assign task to member: " . $assignment['member_id'] . " for role " . $assignment['role']);
                }
            }
        } else {
            // If no assignments are made, it might be intentional or an error. 
            // For now, allow tasks without immediate assignments, but this might need refinement.
            throw new Exception("No members assigned to any role for this task. Please assign at least one member.");
        }
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Task created successfully!';

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }

    echo json_encode($response);
}
?>