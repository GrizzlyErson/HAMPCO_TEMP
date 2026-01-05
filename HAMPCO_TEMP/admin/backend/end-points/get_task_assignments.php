<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ["success" => false, "data" => [], "message" => "Unknown error."];

try {
    $sql = "SELECT DISTINCT
        pl.prod_line_id,
        pl.product_name,
        pl.status,
        GROUP_CONCAT(DISTINCT ta.id) as task_ids,
        GROUP_CONCAT(DISTINCT ta.member_id) as member_ids,
        GROUP_CONCAT(DISTINCT ta.role) as roles,
        GROUP_CONCAT(DISTINCT ta.status) as task_statuses,
        GROUP_CONCAT(DISTINCT ta.deadline) as deadlines,
        GROUP_CONCAT(DISTINCT ta.updated_at) as completion_dates, /* Added completion_dates */
            GROUP_CONCAT(DISTINCT um.fullname) as member_names,
            GROUP_CONCAT(DISTINCT um.role) as member_roles,
            pl.date_created,
            -- New columns for decline information
            GROUP_CONCAT(DISTINCT tdn.id) as decline_ids,
            GROUP_CONCAT(DISTINCT tdn.status) as decline_statuses,
            GROUP_CONCAT(DISTINCT tdn.member_reason) as decline_reasons
        FROM production_line pl
        LEFT JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
        LEFT JOIN user_member um ON ta.member_id = um.id
        LEFT JOIN task_decline_notifications tdn ON ta.id = tdn.task_assignment_id AND (tdn.status = 'pending' OR tdn.status = 'responded')
        GROUP BY pl.prod_line_id
        ORDER BY pl.date_created DESC";
            
            $result = $conn->query($sql);
            
            if ($result) {
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    // Format the date
                    $date = new DateTime($row['date_created']);
                    $formatted_date = $date->format('Y-m-d H:i:s');
        
                    // Split concatenated values into arrays
                    $task_ids = $row['task_ids'] ? explode(',', $row['task_ids']) : [];
                    $member_ids = $row['member_ids'] ? explode(',', $row['member_ids']) : [];
                    $roles = $row['roles'] ? explode(',', $row['roles']) : [];
                    $task_statuses = $row['task_statuses'] ? explode(',', $row['task_statuses']) : [];
                    $deadlines = $row['deadlines'] ? explode(',', $row['deadlines']) : [];
                    $completion_dates = $row['completion_dates'] ? explode(',', $row['completion_dates']) : [];
                    $member_names = $row['member_names'] ? explode(',', $row['member_names']) : [];
                    $member_roles = $row['member_roles'] ? explode(',', $row['member_roles']) : [];
                    $decline_ids = $row['decline_ids'] ? explode(',', $row['decline_ids']) : [];
                    $decline_statuses = $row['decline_statuses'] ? explode(',', $row['decline_statuses']) : [];
                    $decline_reasons = $row['decline_reasons'] ? explode(',', $row['decline_reasons']) : [];
        
                    // Format production ID to match monitoring tab format
                    $display_id = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
                    
                    $data[] = [
                        'prod_line_id' => $display_id,
                        'raw_id' => $row['prod_line_id'],
                        'product_name' => $row['product_name'],
                        'status' => $row['status'],
                        'date_created' => $formatted_date,
                        'assignments' => array_map(function($i) use ($task_ids, $member_ids, $roles, $task_statuses, $deadlines, $completion_dates, $member_names, $member_roles, $decline_ids, $decline_statuses, $decline_reasons) {
                            return [
                                'task_id' => $task_ids[$i] ?? null,
                                'member_id' => $member_ids[$i] ?? null,
                                'role' => $roles[$i] ?? null,
                                'task_status' => $task_statuses[$i] ?? null,
                                'deadline' => $deadlines[$i] ?? null,
                                'completion_date' => $completion_dates[$i] ?? null,
                                'member_name' => $member_names[$i] ?? null,
                                'member_role' => $member_roles[$i] ?? null,
                                'decline_id' => $decline_ids[$i] ?? null,
                                'decline_status' => $decline_statuses[$i] ?? null,
                                'decline_reason' => $decline_reasons[$i] ?? null
                            ];
                        }, array_keys($task_ids))
                    ];
                }        $response["success"] = true;
        $response["data"] = $data;
        $response["message"] = "Task assignments data fetched successfully.";
    } else {
        throw new Exception("Error fetching data: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error in get_task_assignments.php: " . $e->getMessage());
    $response["message"] = "Error: " . $e->getMessage();
}

$conn->close();
echo json_encode($response); 