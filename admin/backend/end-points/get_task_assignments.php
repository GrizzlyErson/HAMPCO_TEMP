<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ["success" => false, "data" => [], "message" => "Unknown error."];

try {
    $sql = "SELECT 
        pl.prod_line_id,
        pl.product_name,
        pl.status,
        pl.date_created,
        GROUP_CONCAT(
            JSON_OBJECT(
                'task_id', ta.id,
                'member_id', ta.member_id,
                'role', ta.role,
                'task_status', ta.status,
                'deadline', ta.deadline,
                'member_name', um.fullname,
                'member_role', um.role
            ) ORDER BY ta.id ASC
        ) as assignments_json
    FROM production_line pl
    LEFT JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
    LEFT JOIN user_member um ON ta.member_id = um.id
    GROUP BY pl.prod_line_id, pl.product_name, pl.status, pl.date_created
    ORDER BY pl.date_created DESC";
    
    $result = $conn->query($sql);
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Format the date
            $date = new DateTime($row['date_created']);
            $formatted_date = $date->format('Y-m-d H:i:s');

            // Decode the assignments_json
            $assignments = $row['assignments_json'] ? json_decode('[' . $row['assignments_json'] . ']', true) : [];

            // Filter out null assignments if there are no tasks for a prod_line (LEFT JOIN)
            if (isset($assignments[0]['task_id']) === false && $assignments[0]['task_id'] === null) {
                 $assignments = []; // No actual assignments, just a null entry from LEFT JOIN
            }
            
            // Format production ID to match monitoring tab format
            $display_id = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
            
            $data[] = [
                'prod_line_id' => $display_id,
                'raw_id' => $row['prod_line_id'],
                'product_name' => $row['product_name'],
                'status' => $row['status'],
                'date_created' => $formatted_date,
                'assignments' => $assignments
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