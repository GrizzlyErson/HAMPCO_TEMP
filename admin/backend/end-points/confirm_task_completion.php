<?php
ob_start();
session_start();
require_once '../../../function/connection.php';

header('Content-Type: application/json');
$response = array('success' => false);

try {
    // Check if user is logged in and is admin
    if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Validate input
    if (!isset($_POST['production_id'])) {
        throw new Exception('Production ID is required');
    }

    $production_id = $_POST['production_id'];

    // Get database connection
    $db = new mysqli($host, $username, $password, $dbname);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8mb4");

    // Debug logging
    error_log("confirm_task_completion.php - Received production_id: " . $production_id);

    // Start transaction
    $db->begin_transaction();

    try {
        // First, check if this is a member_self_task or a regular task_assignment
        $check_source = $db->prepare("
            SELECT 'self_assigned' as source
            FROM member_self_tasks
            WHERE production_id = ? AND status IN ('submitted', 'in_progress')
            
            UNION
            
            SELECT 'regular_assigned' as source
            FROM task_assignments ta
            JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
            WHERE ta.prod_line_id = ? AND ta.status = 'submitted'
            
            LIMIT 1
        ");

        if (!$check_source) {
            throw new Exception("Failed to prepare source check query: " . $db->error);
        }

        $check_source->bind_param("ss", $production_id, $production_id);
        if (!$check_source->execute()) {
            throw new Exception("Failed to check task source: " . $check_source->error);
        }

        $source_result = $check_source->get_result();
        $source_row = $source_result->fetch_assoc();
        
        if (!$source_row) {
            error_log("Task source not found for production_id: " . $production_id);
            throw new Exception("Task not found or not submitted. Production ID: " . $production_id);
        }

        $task_type = $source_row['source'];
        error_log("Found task source: " . $task_type . " for production_id: " . $production_id);

        // Now get the full task details based on source
        if ($task_type === 'self_assigned') {
            $get_task = $db->prepare("
                SELECT 
                    mst.production_id,
                    mst.product_name,
                    mst.weight_g,
                    mst.length_m,
                    mst.width_in as width_m,
                    mst.quantity,
                    mst.member_id,
                    um.role,
                    'self_assigned' as task_type
                FROM member_self_tasks mst
                LEFT JOIN user_member um ON mst.member_id = um.id
                WHERE mst.production_id = ? AND mst.status IN ('submitted', 'in_progress')
                LIMIT 1
            ");
            
            if (!$get_task) {
                throw new Exception("Failed to prepare self-assigned task query: " . $db->error);
            }
            
            $get_task->bind_param("s", $production_id);
        } else {
            $get_task = $db->prepare("
                SELECT 
                    ta.prod_line_id as production_id,
                    pl.product_name,
                    pl.weight_g,
                    pl.length_m,
                    pl.width_m,
                    pl.quantity,
                    ta.member_id,
                    um.role,
                    'regular_assigned' as task_type
                FROM task_assignments ta
                JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
                LEFT JOIN user_member um ON ta.member_id = um.id
                WHERE ta.prod_line_id = ? AND ta.status = 'submitted'
                LIMIT 1
            ");
            
            if (!$get_task) {
                throw new Exception("Failed to prepare regular task query: " . $db->error);
            }
            
            $get_task->bind_param("s", $production_id);
        }

        if (!$get_task->execute()) {
            throw new Exception("Failed to get task details: " . $get_task->error);
        }

        $task_result = $get_task->get_result();
        $task = $task_result->fetch_assoc();
        
        // Debug logging
        error_log("Task lookup result: " . json_encode($task));
        
        if (!$task) {
            // Try a broader search to see what's actually in the database
            error_log("Task not found with strict query. Trying broader search...");
            $broad_search = $db->prepare("
                SELECT 
                    ta.prod_line_id,
                    ta.member_id,
                    ta.status,
                    um.role,
                    um.fullname,
                    pl.product_name,
                    pl.weight_g
                FROM task_assignments ta
                LEFT JOIN user_member um ON ta.member_id = um.id
                LEFT JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
                WHERE ta.prod_line_id = ?
                LIMIT 1
            ");
            $broad_search->bind_param("s", $production_id);
            $broad_search->execute();
            $broad_result = $broad_search->get_result();
            $broad_task = $broad_result->fetch_assoc();
            error_log("Broad search result: " . json_encode($broad_task));
            
            throw new Exception("Task not found or not submitted. Expected status 'submitted' for task_id: " . $production_id);
        }

        // Update Inventory (Processed Materials or Finished Products)
        if (!empty($task['product_name']) && !is_null($task['product_name'])) {
            $product_name = trim($task['product_name']);
            // Normalize product names to ensure consistency (handle Pina vs Piña)
            if (strcasecmp($product_name, 'Pina Seda') === 0) $product_name = 'Piña Seda';
            if (strcasecmp($product_name, 'Pure Pina Cloth') === 0) $product_name = 'Pure Piña Cloth';

            $processed_materials_list = ['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'];
            $finished_products_list = ['Piña Seda', 'Pure Piña Cloth'];

            if (in_array($product_name, $processed_materials_list)) {
                // Handle Processed Materials (Update Weight)
                $new_weight = floatval($task['weight_g']); // Fix: Use weight_g instead of weight

                if ($new_weight > 0) {
                    // Check for existing record (case-insensitive)
                    $check_processed = $db->prepare("
                        SELECT id, weight 
                        FROM processed_materials 
                        WHERE LOWER(TRIM(processed_materials_name)) = LOWER(?) 
                        AND status = 'Available'
                        LIMIT 1
                    ");
                    
                    if (!$check_processed) {
                        throw new Exception("Failed to prepare processed materials check query: " . $db->error);
                    }

                    $check_processed->bind_param("s", $product_name);
                    if (!$check_processed->execute()) {
                        throw new Exception("Failed to check processed materials: " . $check_processed->error);
                    }

                    $processed_result = $check_processed->get_result();
                    $processed_material = $processed_result->fetch_assoc();

                    if ($processed_material) {
                        // Update existing processed material
                        $update_processed = $db->prepare("
                            UPDATE processed_materials 
                            SET weight = weight + ?,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $update_processed->bind_param("di", $new_weight, $processed_material['id']);
                        if (!$update_processed->execute()) {
                            throw new Exception("Failed to update processed materials: " . $update_processed->error);
                        }
                    } else {
                        // Insert new processed material
                        $insert_processed = $db->prepare("
                            INSERT INTO processed_materials 
                            (processed_materials_name, weight, status, updated_at)
                            VALUES (?, ?, 'Available', NOW())
                        ");
                        $insert_processed->bind_param("sd", $product_name, $new_weight);
                        if (!$insert_processed->execute()) {
                            throw new Exception("Failed to insert processed materials: " . $insert_processed->error);
                        }
                    }
                }
            } elseif (in_array($product_name, $finished_products_list)) {
                // Handle Finished Products (Update Quantity/Dimensions)
                $length = floatval($task['length_m']);
                $width = floatval($task['width_m']);
                $quantity = intval($task['quantity']);

                if ($quantity > 0) {
                    // Check if a product with same dimensions exists (using epsilon for float comparison)
                    // Increased epsilon to 0.01 to handle slight rounding differences
                    error_log("Checking finished products for: $product_name, L:$length, W:$width");
                    $check_finished = $db->prepare("
                        SELECT id, quantity 
                        FROM finished_products 
                        WHERE LOWER(TRIM(product_name)) = LOWER(?) 
                        AND ABS(length_m - ?) < 0.01 
                        AND ABS(width_m - ?) < 0.01
                        LIMIT 1
                    ");
                    $check_finished->bind_param("sdd", $product_name, $length, $width);
                    $check_finished->execute();
                    $finished_result = $check_finished->get_result();
                    $finished_product = $finished_result->fetch_assoc();

                    if ($finished_product) {
                        // Update quantity
                        $update_finished = $db->prepare("UPDATE finished_products SET quantity = quantity + ?, updated_at = NOW() WHERE id = ?");
                        $update_finished->bind_param("ii", $quantity, $finished_product['id']);
                        $update_finished->execute();
                    } else {
                        // Insert new finished product
                        $insert_finished = $db->prepare("INSERT INTO finished_products (product_name, length_m, width_m, quantity, updated_at) VALUES (?, ?, ?, ?, NOW())");
                        $insert_finished->bind_param("sddi", $product_name, $length, $width, $quantity);
                        $insert_finished->execute();
                    }
                }
            }
        } else {
            error_log("Skipping processed materials update - product_name is NULL or empty for production_id: " . $production_id);
        }

        // Update task status to completed based on task type
        if ($task['task_type'] === 'self_assigned') {
            // Update task_completion_confirmations for self-assigned tasks
            $update_task = $db->prepare("
                UPDATE task_completion_confirmations 
                SET status = 'completed'
                WHERE production_id = ?
            ");

            if (!$update_task) {
                throw new Exception("Failed to prepare task update query: " . $db->error);
            }

            $update_task->bind_param("s", $production_id);
            if (!$update_task->execute()) {
                throw new Exception("Failed to update task status: " . $update_task->error);
            }

            // Update member_self_tasks status (triggers payment_records creation)
            $update_self_task = $db->prepare("
                UPDATE member_self_tasks 
                SET status = 'completed'
                WHERE production_id = ?
            ");

            if (!$update_self_task) {
                throw new Exception("Failed to prepare self task update query: " . $db->error);
            }

            $update_self_task->bind_param("s", $production_id);
            if (!$update_self_task->execute()) {
                throw new Exception("Failed to update self task status: " . $update_self_task->error);
            }
        } else {
            // Update task_assignments for regular assigned tasks (should trigger payment_records creation)
            $update_task = $db->prepare("
                UPDATE task_assignments 
                SET status = 'completed', updated_at = NOW()
                WHERE prod_line_id = ?
            ");

            if (!$update_task) {
                throw new Exception("Failed to prepare task update query: " . $db->error);
            }

            $update_task->bind_param("s", $production_id);
            if (!$update_task->execute()) {
                throw new Exception("Failed to update task status: " . $update_task->error);
            }

            // Manually create payment record if trigger didn't fire (backup method)
            // First, check if payment record already exists
            $check_payment = $db->prepare("
                SELECT id FROM payment_records 
                WHERE member_id = ? AND production_id = ? AND is_self_assigned = 0
            ");

            if (!$check_payment) {
                throw new Exception("Failed to check payment records: " . $db->error);
            }

            $check_payment->bind_param("is", $task['member_id'], $production_id);
            if (!$check_payment->execute()) {
                throw new Exception("Failed to execute payment check: " . $check_payment->error);
            }

            $payment_exists = $check_payment->get_result()->fetch_assoc();

            if (!$payment_exists) {
                // Get production line details for payment calculation
                $get_pl_details = $db->prepare("
                    SELECT product_name, length_m, width_m, weight_g, quantity 
                    FROM production_line 
                    WHERE prod_line_id = ?
                ");

                if (!$get_pl_details) {
                    throw new Exception("Failed to prepare production line query: " . $db->error);
                }

                $get_pl_details->bind_param("s", $production_id);
                if (!$get_pl_details->execute()) {
                    throw new Exception("Failed to get production line details: " . $get_pl_details->error);
                }

                $pl_details = $get_pl_details->get_result()->fetch_assoc();

                if ($pl_details) {
                    // Calculate unit rate based on product
                    $unit_rate = 0.00;
                    switch ($pl_details['product_name']) {
                        case 'Knotted Liniwan':
                            $unit_rate = 50.00;
                            break;
                        case 'Knotted Bastos':
                            $unit_rate = 50.00;
                            break;
                        case 'Warped Silk':
                            $unit_rate = 19.00;
                            break;
                        case 'Piña Seda':
                        case 'Pure Piña Cloth':
                            $unit_rate = 550.00;
                            break;
                    }

                    // Calculate quantity
                    $quantity = ($pl_details['product_name'] === 'Piña Seda' || $pl_details['product_name'] === 'Pure Piña Cloth') 
                        ? intval($pl_details['quantity']) 
                        : 1;

                    // Cast dimensions and weights to float
                    $length_m = floatval($pl_details['length_m']);
                    $width_m = floatval($pl_details['width_m']);
                    $weight_g = floatval($pl_details['weight_g']);
                    $unit_rate = floatval($unit_rate);

                    // Calculate total amount based on product type
                    $total_amount = 0.00;
                    if ($weight_g > 0) {
                        $total_amount = $weight_g * $unit_rate;
                    } elseif ($length_m > 0 && $width_m > 0) {
                        $total_amount = $length_m * $width_m * $unit_rate;
                    } else {
                        $total_amount = $quantity * $unit_rate;
                    }

                    error_log("Creating payment record - member_id: " . $task['member_id'] . ", production_id: " . $production_id . ", product: " . $pl_details['product_name'] . ", unit_rate: " . $unit_rate . ", total: " . $total_amount);

                    // Insert payment record
                    $insert_payment = $db->prepare("
                        INSERT INTO payment_records 
                        (member_id, production_id, length_m, width_m, weight_g, quantity, unit_rate, total_amount, is_self_assigned, payment_status, date_created)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'Pending', NOW())
                    ");

                    if (!$insert_payment) {
                        throw new Exception("Failed to prepare payment insert: " . $db->error);
                    }

                    $insert_payment->bind_param("isddidd", $task['member_id'], $production_id, $length_m, $width_m, $weight_g, $quantity, $unit_rate, $total_amount);
                    if (!$insert_payment->execute()) {
                        throw new Exception("Failed to create payment record: " . $insert_payment->error);
                    }
                    error_log("Payment record created successfully for member_id: " . $task['member_id'] . ", production_id: " . $production_id);
                } else {
                    error_log("Could not find production line details for production_id: " . $production_id);
                }
            }
        }

        // Commit transaction
        $db->commit();

        $response['success'] = true;
        $response['message'] = 'Task completion confirmed successfully';

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    } finally {
        $db->close();
    }

} catch (Exception $e) {
    error_log("Error in confirm_task_completion.php: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

ob_clean();
echo json_encode($response); 