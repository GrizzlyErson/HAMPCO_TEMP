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

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $length = $_POST['length'] ?? null;
    $width = $_POST['width'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    $deadline = $_POST['deadline'] ?? null; // Generic deadline for the assigned member

    $assigned_member_id = $_POST['assigned_member_id'] ?? null;
    $assigned_member_role = $_POST['assigned_member_role'] ?? null;

    $conn->begin_transaction();

    try {
        $sql = "INSERT INTO production_line (product_name, length_m, width_m, weight_g, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sdddi', $product_name, $length, $width, $weight, $quantity);
        $stmt->execute();
        $prod_line_id = $conn->insert_id;

        // Process selected materials for deduction
        $selected_materials_json = $_POST['selected_materials'] ?? '[]';
        $selected_materials = json_decode($selected_materials_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid materials data provided.");
        }

        foreach ($selected_materials as $material) {
            $material_id = $material['id'] ?? null;
            $material_type = $material['type'] ?? null;
            $deduct_quantity = $material['deduct_quantity'] ?? 0;
            $material_name = $material['name'] ?? 'Unknown Material';

            if (!$material_id || !$material_type || $deduct_quantity <= 0) {
                throw new Exception("Invalid material data for deduction. Material: " . $material_name);
            }

            if ($material_type === 'raw') {
                // Check and deduct from raw_materials
                $check_sql = "SELECT rm_quantity FROM raw_materials WHERE id = ? FOR UPDATE";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param('i', $material_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $current_stock = $check_result->fetch_assoc()['rm_quantity'] ?? 0;
                $check_stmt->close();

                if ($current_stock < $deduct_quantity) {
                    throw new Exception("Insufficient stock for raw material: " . $material_name . ". Available: " . $current_stock . ", Requested: " . $deduct_quantity);
                }

                $update_sql = "UPDATE raw_materials SET rm_quantity = rm_quantity - ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param('di', $deduct_quantity, $material_id);
                $update_stmt->execute();
                if ($update_stmt->affected_rows === 0) {
                    throw new Exception("Failed to deduct raw material: " . $material_name);
                }
                $update_stmt->close();

            } elseif ($material_type === 'processed') {
                // Check and deduct from processed_materials
                $check_sql = "SELECT weight FROM processed_materials WHERE id = ? FOR UPDATE";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param('i', $material_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $current_stock = $check_result->fetch_assoc()['weight'] ?? 0;
                $check_stmt->close();

                if ($current_stock < $deduct_quantity) {
                    throw new Exception("Insufficient stock for processed material: " . $material_name . ". Available: " . $current_stock . ", Requested: " . $deduct_quantity);
                }

                $update_sql = "UPDATE processed_materials SET weight = weight - ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param('di', $deduct_quantity, $material_id);
                $update_stmt->execute();
                if ($update_stmt->affected_rows === 0) {
                    throw new Exception("Failed to deduct processed material: " . $material_name);
                }
                $update_stmt->close();
            } else {
                throw new Exception("Unknown material type for deduction: " . $material_name);
            }
        }

        // --- Automatic Member Assignment Logic ---
        if (!$assigned_member_id || !$assigned_member_role) {
            $required_role = '';
            switch ($product_name) {
                case 'Piña Seda':
                case 'Pure Piña Cloth':
                    $required_role = 'weaver';
                    break;
                case 'Knotted Liniwan':
                case 'Knotted Bastos':
                    $required_role = 'knotter';
                    break;
                case 'Warped Silk':
                    $required_role = 'warper';
                    break;
                default:
                    // If product name doesn't map to a specific role, require manual assignment
                    throw new Exception("Product does not have an auto-assignable role. Please assign a member manually.");
            }

            // Find an available member with the required role
            // Prioritize 'Verified' or 'Active' members who are not 'Work In Progress' and have fewest assigned tasks
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
            $find_member_stmt->bind_param('s', $required_role);
            $find_member_stmt->execute();
            $member_result = $find_member_stmt->get_result();
            $auto_assigned_member = $member_result->fetch_assoc();
            $find_member_stmt->close();

            if ($auto_assigned_member) {
                $assigned_member_id = $auto_assigned_member['id'];
                $assigned_member_role = $auto_assigned_member['role'];
            } else {
                throw new Exception("No available " . $required_role . " found for automatic assignment. Please assign a member manually.");
            }
        }
        // --- End Automatic Member Assignment Logic ---

        // Assign task to the single member
        if ($assigned_member_id && $assigned_member_role && $deadline) {
            $sql = "INSERT INTO task_assignments (prod_line_id, member_id, role, status, deadline) VALUES (?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iiss', $prod_line_id, $assigned_member_id, $assigned_member_role, $deadline);
            $stmt->execute();
            if ($stmt->affected_rows === 0) {
                throw new Exception("Failed to assign task to member.");
            }
        } else {
            // This case should ideally be caught by frontend validation, but as a fallback
            throw new Exception("Task assignment details are incomplete.");
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