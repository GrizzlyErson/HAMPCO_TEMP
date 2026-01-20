<?php
require_once __DIR__ . "/../class.php";

$db = new global_class();
$response = array();

try {
    // Get the production identifier (either code or ID) and member IDs
    $identifier = $_POST['identifier'];
    $knotter_ids = !empty($_POST['knotter_id']) ? (is_array($_POST['knotter_id']) ? $_POST['knotter_id'] : [$_POST['knotter_id']]) : [];
    $warper_id = !empty($_POST['warper_id']) ? $_POST['warper_id'] : null;
    $weaver_id = !empty($_POST['weaver_id']) ? $_POST['weaver_id'] : null;

    // Get estimated times and deadlines with default values
    $knotter_estimated_time = isset($_POST['knotter_estimated_time']) ? intval($_POST['knotter_estimated_time']) : 0;
    $warper_estimated_time = isset($_POST['warper_estimated_time']) ? intval($_POST['warper_estimated_time']) : 0;
    $weaver_estimated_time = isset($_POST['weaver_estimated_time']) ? intval($_POST['weaver_estimated_time']) : 0;

    $knotter_deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
    $warper_deadline = !empty($_POST['warper_deadline']) ? $_POST['warper_deadline'] : null;
    $weaver_deadline = !empty($_POST['weaver_deadline']) ? $_POST['weaver_deadline'] : null;

    // Define weight and quantity, as they might be used by included scripts
    $weight = isset($_POST['weight']) ? $_POST['weight'] : null;
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : null;

    // Log received data for debugging
    error_log("Received data for task assignment:");
    error_log("Identifier: " . $identifier);
    error_log("Knotter IDs: " . print_r($knotter_ids, true));
    error_log("Knotter deadline: " . $knotter_deadline);

    // Start transaction
    $db->conn->begin_transaction();

    // Get the prod_line_id - handle both production_code and direct prod_line_id
    if (is_numeric($identifier)) {
        $query = "SELECT prod_line_id FROM production_line WHERE prod_line_id = ?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param("i", $identifier);
    } else {
        $query = "SELECT prod_line_id FROM production_line WHERE production_code = ?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param("s", $identifier);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Production item not found");
    }
    
    $row = $result->fetch_assoc();
    $prod_line_id = $row['prod_line_id'];

    // Get product details to check if it's Piña Seda or Knotted Liniwan
    $product_query = "SELECT product_name FROM production_line WHERE prod_line_id = ?";
    $product_stmt = $db->conn->prepare($product_query);
    $product_stmt->bind_param("i", $prod_line_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result()->fetch_assoc();
    $product_name = $product_result['product_name'];
    $is_pina_seda = $product_name === 'Piña Seda';
    $is_pure_pina = $product_name === 'Pure Piña Cloth';
    $is_knotted_liniwan = $product_name === 'Knotted Liniwan';
    $is_warped_silk = $product_name === 'Warped Silk';
    $is_knotted_bastos = $product_name === 'Knotted Bastos';

    // If it's Knotted Liniwan or Knotted Bastos, only allow knotter assignments
    if ($is_knotted_liniwan || $is_knotted_bastos) {
        if (empty($knotter_ids)) {
            throw new Exception("Knotter must be assigned for " . $product_name . " products");
        }
        // Clear any warper or weaver assignments
        $warper_id = null;
        $weaver_id = null;
    }
    // If it's Warped Silk, only allow warper assignments
    else if ($is_warped_silk) {
        if (empty($warper_id)) {
            throw new Exception("Warper must be assigned for Warped Silk products");
        }
        // Clear any knotter or weaver assignments
        $knotter_ids = [];
        $weaver_id = null;
    }
    // If it's Piña Seda or Pure Piña Cloth, only allow weaver assignments
    else if ($is_pina_seda || $is_pure_pina) {
        if (empty($weaver_id)) {
            throw new Exception("Weaver must be assigned for " . $product_name . " products");
        }
        // Clear any knotter or warper assignments
        $knotter_ids = [];
        $warper_id = null;
        
        // Skip processed materials inventory check for final products
    }
    // For other products, check knotted inventory
    else {
        // Check if there's enough knotted material in inventory
        $check_inventory_url = __DIR__ . "/check_knotted_inventory.php";

        // Temporarily store original $_POST
        $original_post = $_POST;

        // Set $_POST for the required script
        $_POST['product_name'] = $product_name;
        $_POST['weight'] = $weight;
        $_POST['quantity'] = $quantity;
        
        require $check_inventory_url;

        // Restore original $_POST
        $_POST = $original_post;
    }

    // Check if tasks are already assigned
    $check_query = "SELECT COUNT(*) as count FROM task_assignments WHERE prod_line_id = ?";
    $check_stmt = $db->conn->prepare($check_query);
    $check_stmt->bind_param("i", $prod_line_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result()->fetch_assoc();
    
    if ($check_result['count'] > 0) {
        throw new Exception("Tasks are already assigned to this production item");
    }

    // Prepare task assignments
    $assignments = array();
    
    // Handle multiple knotters
    if (!empty($knotter_ids)) {
        // Use array_unique to prevent duplicate knotter IDs
        $knotter_ids = array_unique($knotter_ids);
        foreach ($knotter_ids as $knotter_id) {
            // Check task limit
            $limitCheck = $db->checkMemberTaskLimit($knotter_id);
            if (!$limitCheck['allowed']) {
                throw new Exception("Member (ID: $knotter_id) has reached their task limit of " . $limitCheck['limit'] . " active tasks.");
            }

            if (!empty($knotter_id)) {
                $assignments[] = array(
                    'id' => $knotter_id,
                    'role' => 'knotter',
                    'estimated_time' => $knotter_estimated_time,
                    'deadline' => $knotter_deadline
                );
            }
        }
    }
    
    if ($warper_id) {
        // Check task limit
        $limitCheck = $db->checkMemberTaskLimit($warper_id);
        if (!$limitCheck['allowed']) {
            throw new Exception("Warper (ID: $warper_id) has reached their task limit of " . $limitCheck['limit'] . " active tasks.");
        }

        $assignments[] = array(
            'id' => $warper_id,
            'role' => 'warper',
            'estimated_time' => $warper_estimated_time,
            'deadline' => $warper_deadline
        );
    }
    if ($weaver_id) {
        // Check task limit
        $limitCheck = $db->checkMemberTaskLimit($weaver_id);
        if (!$limitCheck['allowed']) {
            throw new Exception("Weaver (ID: $weaver_id) has reached their task limit of " . $limitCheck['limit'] . " active tasks.");
        }

        $assignments[] = array(
            'id' => $weaver_id,
            'role' => 'weaver',
            'estimated_time' => $weaver_estimated_time,
            'deadline' => $weaver_deadline
        );
    }

    if (empty($assignments)) {
        throw new Exception("No members selected for assignment");
    }

    // Check for duplicate assignments
    $unique_assignments = [];
    foreach ($assignments as $assignment) {
        $key = $assignment['id'] . '-' . $assignment['role'];
        if (!isset($unique_assignments[$key])) {
            $unique_assignments[$key] = $assignment;
        }
    }
    $assignments = array_values($unique_assignments);

    // Insert task assignments
    $insert_query = "INSERT INTO task_assignments (prod_line_id, member_id, role, status, estimated_time, deadline) 
                    VALUES (?, ?, ?, 'pending', ?, ?)";
    $insert_stmt = $db->conn->prepare($insert_query);

    foreach ($assignments as $assignment) {
        $insert_stmt->bind_param("iisis", 
            $prod_line_id, 
            $assignment['id'], 
            $assignment['role'],
            $assignment['estimated_time'],
            $assignment['deadline']
        );
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Error assigning task to " . $assignment['role'] . ": " . $db->conn->error);
        }
    }

    // Update production line status
    $update_query = "UPDATE production_line SET status = 'in_progress' WHERE prod_line_id = ?";
    $update_stmt = $db->conn->prepare($update_query);
    $update_stmt->bind_param("i", $prod_line_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Error updating production line status: " . $db->conn->error);
    }

    // Commit transaction
    $db->conn->commit();

    $response['success'] = true;
    $response['message'] = 'Tasks assigned successfully';

} catch (Exception $e) {
    // Rollback on error
    if (isset($db->conn)) {
        $db->conn->rollback();
    }
    error_log("Error in assign_tasks.php: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?> 