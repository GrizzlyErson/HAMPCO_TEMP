<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ["success" => false, "data" => [], "message" => "Unknown error."];

try {
    // Get member-created tasks (from member_self_tasks) that are pending approval
    $sql = "SELECT 
                mst.id,
                mst.production_id,
                mst.product_name,
                mst.member_id,
                um.fullname as member_name,
                um.role,
                mst.weight_g,
                mst.quantity,
                mst.length_m,
                mst.width_in,
                mst.date_created,
                mst.status,
                mst.approval_status
            FROM member_self_tasks mst
            LEFT JOIN user_member um ON mst.member_id = um.id
            WHERE mst.approval_status = 'pending'
            ORDER BY mst.date_created DESC";
            
    $result = $conn->query($sql);
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => $row['id'],
                'production_id' => $row['production_id'],
                'product_name' => $row['product_name'],
                'member_id' => $row['member_id'],
                'member_name' => $row['member_name'],
                'member_role' => $row['role'],
                'weight_g' => $row['weight_g'],
                'quantity' => $row['quantity'],
                'length_m' => $row['length_m'],
                'width_in' => $row['width_in'],
                'date_created' => $row['date_created'],
                'status' => $row['status']
            ];
        }
        $response["success"] = true;
        $response["data"] = $data;
        $response["message"] = "Member tasks fetched successfully.";
    } else {
        throw new Exception("Error fetching member tasks: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error in get_member_created_tasks.php: " . $e->getMessage());
    $response["message"] = "Error: " . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
