<?php
require_once '../dbconnect.php';
require_once '../class.php';

$db = new DB_con();
$conn = $db->conn;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_line_id = mysqli_real_escape_string($conn, $_POST['prod_line_id']);
    $product_details = mysqli_real_escape_string($conn, $_POST['product_details']); // This might not be directly used for DB, but good to capture
    $is_reassignment = isset($_POST['is_reassignment']) && $_POST['is_reassignment'] === 'true';

    // Start a transaction
    mysqli_begin_transaction($conn);

    try {
        if ($is_reassignment) {
            // Delete existing assignments for this production line
            $delete_query = "DELETE FROM task_assignments WHERE prod_line_id = '$prod_line_id'";
            if (!mysqli_query($conn, $delete_query)) {
                throw new Exception("Failed to delete old assignments: " . mysqli_error($conn));
            }
        }

        $assigned_count = 0;
        $error_messages = [];

        // Handle Knotter assignment
        if (isset($_POST['knotter_id']) && is_array($_POST['knotter_id'])) {
            foreach ($_POST['knotter_id'] as $index => $knotter_id) {
                if (!empty($knotter_id) && !empty($_POST['deadline'][$index])) {
                    $member_id = mysqli_real_escape_string($conn, $knotter_id);
                    $deadline = mysqli_real_escape_string($conn, $_POST['deadline'][$index]);
                    $role = 'knotter';
                    $status = 'pending'; // Default status for new assignments

                    $insert_query = "INSERT INTO task_assignments (prod_line_id, member_id, role, deadline, status) 
                                     VALUES ('$prod_line_id', '$member_id', '$role', '$deadline', '$status')";
                    if (!mysqli_query($conn, $insert_query)) {
                        $error_messages[] = "Knotter assignment failed: " . mysqli_error($conn);
                    } else {
                        $assigned_count++;
                    }
                }
            }
        }

        // Handle Warper assignment
        if (isset($_POST['warper_id']) && !empty($_POST['warper_id'])) {
            if (!empty($_POST['warper_deadline'])) {
                $member_id = mysqli_real_escape_string($conn, $_POST['warper_id']);
                $deadline = mysqli_real_escape_string($conn, $_POST['warper_deadline']);
                $role = 'warper';
                $status = 'pending';

                $insert_query = "INSERT INTO task_assignments (prod_line_id, member_id, role, deadline, status) 
                                 VALUES ('$prod_line_id', '$member_id', '$role', '$deadline', '$status')";
                if (!mysqli_query($conn, $insert_query)) {
                    $error_messages[] = "Warper assignment failed: " . mysqli_error($conn);
                } else {
                    $assigned_count++;
                }
            }
        }

        // Handle Weaver assignment
        if (isset($_POST['weaver_id']) && !empty($_POST['weaver_id'])) {
            if (!empty($_POST['weaver_deadline'])) {
                $member_id = mysqli_real_escape_string($conn, $_POST['weaver_id']);
                $deadline = mysqli_real_escape_string($conn, $_POST['weaver_deadline']);
                $role = 'weaver';
                $status = 'pending';

                $insert_query = "INSERT INTO task_assignments (prod_line_id, member_id, role, deadline, status) 
                                 VALUES ('$prod_line_id', '$member_id', '$role', '$deadline', '$status')";
                if (!mysqli_query($conn, $insert_query)) {
                    $error_messages[] = "Weaver assignment failed: " . mysqli_error($conn);
                } else {
                    $assigned_count++;
                }
            }
        }

        if ($assigned_count > 0 && empty($error_messages)) {
            // Update the production_line status
            $update_pl_status = "UPDATE production_line SET status = 'assigned' WHERE prod_line_id = '$prod_line_id'";
            if (!mysqli_query($conn, $update_pl_status)) {
                throw new Exception("Failed to update production line status: " . mysqli_error($conn));
            }
            mysqli_commit($conn);
            $response['success'] = true;
            $response['message'] = $is_reassignment ? 'Tasks reassigned successfully!' : 'Tasks assigned successfully!';
        } else if (empty($error_messages)) {
            // No assignments were made, but no errors
            mysqli_rollback($conn); // Rollback if no assignments, even if no errors
            $response['message'] = 'No tasks were assigned or reassigned. Please select members and deadlines.';
        } else {
            mysqli_rollback($conn);
            $response['message'] = 'Errors occurred during assignment: ' . implode('; ', $error_messages);
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>