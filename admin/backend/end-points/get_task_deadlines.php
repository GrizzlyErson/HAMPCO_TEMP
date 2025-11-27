<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ["success" => false, "data" => [], "message" => "Unknown error."];

try {
    // Corrected SQL query
    $sql = "SELECT 
                ta.prod_line_id,
                pl.product_name,
                um.fullname AS assigned_to,
                um.role,
                ta.deadline,
                ta.status
            FROM task_assignments ta
            JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
            JOIN user_member um ON ta.member_id = um.id
            WHERE ta.status NOT IN ('completed', 'cancelled')
            ORDER BY ta.deadline ASC";
    
    $result = $conn->query($sql);
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $response["success"] = true;
        $response["data"] = $data;
        $response["message"] = "Task deadlines fetched successfully.";
    } else {
        throw new Exception("Error fetching data: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error in get_task_deadlines.php: " . $e->getMessage());
    $response["message"] = "Error: " . $e->getMessage();
}

$conn->close();
echo json_encode($response);
