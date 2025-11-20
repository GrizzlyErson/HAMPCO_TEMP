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
        GROUP_CONCAT(DISTINCT um.fullname) as member_names,
        GROUP_CONCAT(DISTINCT um.role) as member_roles,
        pl.date_created
    FROM production_line pl
    LEFT JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
    LEFT JOIN user_member um ON ta.member_id = um.id
    WHERE pl.status != 'completed' AND (ta.status != 'completed' OR ta.status IS NULL)
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
            $member_names = $row['member_names'] ? explode(',', $row['member_names']) : [];
            $member_roles = $row['member_roles'] ? explode(',', $row['member_roles']) : [];

            // Format production ID to match monitoring tab format
            $display_id = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
            
            $data[] = [
                'prod_line_id' => $display_id,
                'raw_id' => $row['prod_line_id'],
                'product_name' => $row['product_name'],
                'status' => $row['status'],
                'date_created' => $formatted_date,
                'assignments' => array_map(function($i) use ($task_ids, $member_ids, $roles, $task_statuses, $deadlines, $member_names, $member_roles) {
                    return [
                        'task_id' => $task_ids[$i] ?? null,
                        'member_id' => $member_ids[$i] ?? null,
                        'role' => $roles[$i] ?? null,
                        'task_status' => $task_statuses[$i] ?? null,
                        'deadline' => $deadlines[$i] ?? null,
                        'member_name' => $member_names[$i] ?? null,
                        'member_role' => $member_roles[$i] ?? null
                    ];
                }, array_keys($task_ids))
            ];
        }
        $response["success"] = true;
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