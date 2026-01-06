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
        GROUP_CONCAT(ta.id) as task_ids,
        GROUP_CONCAT(ta.member_id) as member_ids,
        GROUP_CONCAT(ta.role) as roles,
        GROUP_CONCAT(ta.status) as task_statuses,
        GROUP_CONCAT(ta.deadline) as deadlines,
        GROUP_CONCAT(ta.updated_at) as completion_dates,
        GROUP_CONCAT(um.fullname) as member_names,
        GROUP_CONCAT(um.role) as member_roles,
        pl.date_created,
        GROUP_CONCAT(ta.decline_status) as decline_statuses, /* Direct from task_assignments */
        GROUP_CONCAT(ta.decline_reason) as decline_reasons /* Direct from task_assignments */
    FROM production_line pl
    LEFT JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
    LEFT JOIN user_member um ON ta.member_id = um.id
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
                    // Removed decline_ids as it's no longer needed from tdn table
                    $decline_statuses = $row['decline_statuses'] ? explode(',', $row['decline_statuses']) : []; // Direct from ta
                    $decline_reasons = $row['decline_reasons'] ? explode(',', $row['decline_reasons']) : []; // Direct from ta
        
                    // Format production ID to match monitoring tab format
                    $display_id = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
                    
                    $assignments = [];
                    $max_index = max(count($task_ids), count($member_ids), count($roles), count($task_statuses), count($deadlines), count($completion_dates), count($member_names), count($member_roles), count($decline_statuses), count($decline_reasons));

                    for ($i = 0; $i < $max_index; $i++) {
                        $assignments[] = [
                            'task_id' => $task_ids[$i] ?? null,
                            'member_id' => $member_ids[$i] ?? null,
                            'role' => $roles[$i] ?? null,
                            'task_status' => $task_statuses[$i] ?? null,
                            'deadline' => $deadlines[$i] ?? null,
                            'completion_date' => $completion_dates[$i] ?? null,
                            'member_name' => $member_names[$i] ?? null,
                            'member_role' => $member_roles[$i] ?? null,
                            'decline_status' => $decline_statuses[$i] ?? null,
                            'decline_reason' => $decline_reasons[$i] ?? null
                        ];
                    }

                    $data[] = [
                        'prod_line_id' => $display_id,
                        'raw_id' => $row['prod_line_id'],
                        'product_name' => $row['product_name'],
                        'status' => $row['status'],
                        'date_created' => $formatted_date,
                        'assignments' => $assignments
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