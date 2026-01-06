<?php
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

    // Start transaction
    $db->begin_transaction();

    try {
        // Get task completion details - check both task_completion_confirmations and task_assignments
        $get_task = $db->prepare("
            SELECT 
                COALESCE(tcc.production_id, ta.prod_line_id) as production_id,
                COALESCE(tcc.product_name, pl.product_name) as product_name,
                COALESCE(tcc.weight, pl.weight_g) as weight,
                COALESCE(tcc.member_id, ta.member_id) as member_id,
                um.role,
                CASE 
                    WHEN tcc.production_id IS NOT NULL THEN 'self_assigned'
                    ELSE 'regular_assigned'
                END as task_type
            FROM user_member um
            LEFT JOIN task_completion_confirmations tcc ON um.id = tcc.member_id AND tcc.production_id = ? AND tcc.status = 'submitted'
            LEFT JOIN task_assignments ta ON um.id = ta.member_id AND ta.prod_line_id = ? AND ta.status = 'submitted'
            LEFT JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
            WHERE (tcc.production_id = ? OR ta.prod_line_id = ?)
            AND um.id = COALESCE(tcc.member_id, ta.member_id)
            LIMIT 1
        ");

        if (!$get_task) {
            throw new Exception("Failed to prepare task query: " . $db->error);
        }

        $get_task->bind_param("ssss", $production_id, $production_id, $production_id, $production_id);
        if (!$get_task->execute()) {
            throw new Exception("Failed to get task details: " . $get_task->error);
        }

        $task_result = $get_task->get_result();
        $task = $task_result->fetch_assoc();
        
        if (!$task) {
            throw new Exception("Task not found or not submitted");
        }

        // Add to processed materials
        $check_processed = $db->prepare("
            SELECT id, weight 
            FROM processed_materials 
            WHERE processed_materials_name = ? 
            AND status = 'Available'
        ");
        
        if (!$check_processed) {
            throw new Exception("Failed to prepare processed materials check query: " . $db->error);
        }

        $check_processed->bind_param("s", $task['product_name']);
        if (!$check_processed->execute()) {
            throw new Exception("Failed to check processed materials: " . $check_processed->error);
        }

        $processed_result = $check_processed->get_result();
        $processed_material = $processed_result->fetch_assoc();
        $new_weight = $task['weight'];

        if ($processed_material) {
            // Update existing processed material
            $update_processed = $db->prepare("
                UPDATE processed_materials 
                SET weight = weight + ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            if (!$update_processed) {
                throw new Exception("Failed to prepare processed materials update query: " . $db->error);
            }

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

            if (!$insert_processed) {
                throw new Exception("Failed to prepare processed materials insert query: " . $db->error);
            }

            $insert_processed->bind_param("sd", $task['product_name'], $new_weight);

            if (!$insert_processed->execute()) {
                throw new Exception("Failed to insert processed materials: " . $insert_processed->error);
            }
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
                        ? $pl_details['quantity'] 
                        : 1;

                    // Calculate total amount based on product type
                    $total_amount = 0.00;
                    if ($pl_details['weight_g'] > 0) {
                        $total_amount = $pl_details['weight_g'] * $unit_rate;
                    } elseif ($pl_details['length_m'] > 0 && $pl_details['width_m'] > 0) {
                        $total_amount = $pl_details['length_m'] * $pl_details['width_m'] * $unit_rate;
                    } else {
                        $total_amount = $quantity * $unit_rate;
                    }

                    // Insert payment record
                    $insert_payment = $db->prepare("
                        INSERT INTO payment_records 
                        (member_id, production_id, length_m, width_m, weight_g, quantity, unit_rate, total_amount, is_self_assigned, payment_status, date_created)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'Pending', NOW())
                    ");

                    if (!$insert_payment) {
                        throw new Exception("Failed to prepare payment insert: " . $db->error);
                    }

                    $insert_payment->bind_param("isddddi", $task['member_id'], $production_id, $pl_details['length_m'], $pl_details['width_m'], $pl_details['weight_g'], $quantity, $unit_rate, $total_amount);
                    if (!$insert_payment->execute()) {
                        throw new Exception("Failed to create payment record: " . $insert_payment->error);
                    }
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

echo json_encode($response); 