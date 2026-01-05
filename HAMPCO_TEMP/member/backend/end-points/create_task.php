<?php
require_once "../../../function/database.php";

header('Content-Type: application/json');

try {
    $db = new Database();
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_name']) || empty($data['product_name'])) {
        throw new Exception('Product name is required');
    }

    session_start();
    $member_id = $_SESSION['id'];
    
    // Get member role
    $role_query = "SELECT role FROM user_member WHERE id = ?";
    $stmt = $db->conn->prepare($role_query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $role_result = $stmt->get_result();
    $member_role = strtolower($role_result->fetch_assoc()['role']);

    // Start transaction
    $db->conn->begin_transaction();

    try {
        // Insert into production_line table
        $insert_query = "INSERT INTO production_line (product_name, length_m, width_m, weight_g, quantity, date_created, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pending')";
        $stmt = $db->conn->prepare($insert_query);

        // Set parameters based on member role
        switch ($member_role) {
            case 'weaver':
                if (!isset($data['length']) || !isset($data['width']) || !isset($data['quantity'])) {
                    throw new Exception('Length, width, and quantity are required for weaver tasks');
                }
                $length = $data['length'];
                $width = $data['width'];
                $quantity = $data['quantity'];
                $weight = null;
                $stmt->bind_param("sdddi", $data['product_name'], $length, $width, $weight, $quantity);
                break;

            case 'knotter':
            case 'warper':
                if (!isset($data['weight']) || empty($data['weight'])) {
                    throw new Exception('Weight is required for ' . $member_role . ' tasks');
                }
                $length = null;
                $width = null;
                $quantity = 1;
                $weight = $data['weight'];
                $stmt->bind_param("sdddi", $data['product_name'], $length, $width, $weight, $quantity);
                break;

            default:
                throw new Exception('Invalid member role');
        }

        $stmt->execute();
        $prod_line_id = $db->conn->insert_id;

        // Create task entry
        $task_insert_query = "INSERT INTO task (prod_line_id, created_by, status, date_created) VALUES (?, ?, 'pending', NOW())";
        $stmt = $db->conn->prepare($task_insert_query);
        $stmt->bind_param("ii", $prod_line_id, $member_id);
        $stmt->execute();

        // Create task assignment for the member who created it
        $task_query = "INSERT INTO task_assignments (prod_line_id, member_id, status, created_at) VALUES (?, ?, 'pending', NOW())";
        $stmt = $db->conn->prepare($task_query);
        $stmt->bind_param("ii", $prod_line_id, $member_id);
        $stmt->execute();

        // Commit transaction
        $db->conn->commit();

        // Format production ID for display
        $display_id = 'PL' . str_pad($prod_line_id, 4, '0', STR_PAD_LEFT);

        echo json_encode([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => [
                'prod_line_id' => $prod_line_id,
                'display_id' => $display_id,
                'product_name' => $data['product_name'],
                'length_m' => $length,
                'width_m' => $width,
                'weight_g' => $weight,
                'quantity' => $quantity,
                'status' => 'pending'
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in create_task.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 