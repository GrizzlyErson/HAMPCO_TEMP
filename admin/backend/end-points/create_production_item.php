<?php
ob_start();
// Prevent any output before JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Define constant to allow access to db_connect.php
define('ALLOW_ACCESS', true);

// Get the absolute path to the database connection file
$db_path = __DIR__ . '/../../../function/db_connect.php';
if (!file_exists($db_path)) {
    error_log("Database connection file not found at: " . $db_path);
    ob_clean();
    echo json_encode([
        "success" => false,
        "message" => "Database configuration error: Connection file not found"
    ]);
    exit;
}

$response = ["success" => false, "message" => "Unknown error."];

try {
    require_once $db_path;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_name = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
        $length = isset($_POST['length']) && $_POST['length'] !== '' ? floatval($_POST['length']) : 0;
        $width = isset($_POST['width']) && $_POST['width'] !== '' ? floatval($_POST['width']) : 0;
        $weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? floatval($_POST['weight']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
        $assigned_to = isset($_POST['assigned_to']) && $_POST['assigned_to'] !== '' ? intval($_POST['assigned_to']) : null;
        $deadline = isset($_POST['deadline']) && $_POST['deadline'] !== '' ? $_POST['deadline'] : null;
        $status = 'pending';

        if ($product_name === '' || !$quantity) {
            $response['message'] = 'Product name and quantity are required.';
            ob_clean();
            echo json_encode($response);
            exit;
        }

        // Set fields based on product type
        if (in_array($product_name, ['Piña Seda', 'Pure Piña Cloth'])) {
            // Require length and width, weight is 0
            if ($length <= 0 || $width <= 0) {
                $response['message'] = 'Length and width are required for this product.';
                echo json_encode($response);
                exit;
            }
            $weight = 0;
        } else if (in_array($product_name, ['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'])) {
            // Require weight, length and width are 0
            if ($weight <= 0) {
                $response['message'] = 'Weight is required for this product.';
                echo json_encode($response);
                exit;
            }
            $length = 0;
            $width = 0;
        }

        // Check if database connection is successful
        if (!isset($conn) || !$conn) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        $sql = "INSERT INTO production_line (product_name, length_m, width_m, weight_g, quantity, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param('sdddis', $product_name, $length, $width, $weight, $quantity, $status);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $prod_line_id = $stmt->insert_id;
        $stmt->close();
        $stmt = null; // Prevent double closing in catch block

        // If a member was selected, assign the task to them
        if ($assigned_to && $deadline) {
            // Get the member's role
            $role_sql = "SELECT role FROM user_member WHERE id = ?";
            $role_stmt = $conn->prepare($role_sql);
            if (!$role_stmt) {
                throw new Exception("Failed to prepare role query: " . $conn->error);
            }
            $role_stmt->bind_param('i', $assigned_to);
            if (!$role_stmt->execute()) {
                throw new Exception("Failed to execute role query: " . $role_stmt->error);
            }
            $role_result = $role_stmt->get_result();
            
            if ($role_result && $role_result->num_rows > 0) {
                $role_row = $role_result->fetch_assoc();

                // Check task limit
                $task_limit = 10; // Default
                $limit_sql = "SELECT task_limit FROM user_member WHERE id = ?";
                $limit_stmt = $conn->prepare($limit_sql);
                if ($limit_stmt) {
                    $limit_stmt->bind_param('i', $assigned_to);
                    $limit_stmt->execute();
                    $limit_res = $limit_stmt->get_result();
                    if ($limit_res && $limit_row = $limit_res->fetch_assoc()) {
                        $task_limit = $limit_row['task_limit'] ?? 10;
                    }
                    $limit_stmt->close();
                }

                $current_tasks = 0;
                $count_sql = "SELECT COUNT(*) as count FROM task_assignments WHERE member_id = ? AND status IN ('pending', 'in_progress', 'submitted')";
                $count_stmt = $conn->prepare($count_sql);
                if ($count_stmt) {
                    $count_stmt->bind_param('i', $assigned_to);
                    $count_stmt->execute();
                    $count_res = $count_stmt->get_result();
                    if ($count_res && $count_row = $count_res->fetch_assoc()) {
                        $current_tasks = $count_row['count'];
                    }
                    $count_stmt->close();
                }

                if ($current_tasks >= $task_limit) {
                    // Rollback: Delete the production line item we just created since assignment failed
                    $del_sql = "DELETE FROM production_line WHERE prod_line_id = ?";
                    $del_stmt = $conn->prepare($del_sql);
                    if ($del_stmt) {
                        $del_stmt->bind_param('i', $prod_line_id);
                        $del_stmt->execute();
                        $del_stmt->close();
                    }
                    throw new Exception("Selected member has reached their task limit of $task_limit active tasks.");
                }

                $member_role = $role_row['role'];
                
                // Insert task assignment
                $task_sql = "INSERT INTO task_assignments (prod_line_id, member_id, role, deadline, status) VALUES (?, ?, ?, ?, 'pending')";
                $task_stmt = $conn->prepare($task_sql);
                if (!$task_stmt) {
                    throw new Exception("Failed to prepare task assignment: " . $conn->error);
                }
                $task_stmt->bind_param('iiss', $prod_line_id, $assigned_to, $member_role, $deadline);
                if (!$task_stmt->execute()) {
                    throw new Exception("Failed to assign task: " . $task_stmt->error);
                }
                $task_stmt->close();
                error_log("Task assigned successfully: prod_line_id=$prod_line_id, member_id=$assigned_to, role=$member_role");
            } else {
                error_log("Member not found: $assigned_to");
                throw new Exception("Selected member not found");
            }
            $role_stmt->close();
        } elseif ($assigned_to || $deadline) {
            error_log("Incomplete assignment data: assigned_to=$assigned_to, deadline=$deadline");
        }

        $response['success'] = true;
        $response['message'] = 'Production item created successfully!';
        $conn->close();
    } else {
        $response['message'] = 'Invalid request method.';
    }
} catch (Throwable $e) {
    error_log("Production item creation error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    
    // Close connections if they exist
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

ob_clean();
echo json_encode($response); 