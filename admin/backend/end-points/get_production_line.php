<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ["success" => false, "data" => [], "message" => "Unknown error."];

try {
    $sql = "SELECT * FROM production_line ORDER BY date_created DESC";
    $result = $conn->query($sql);
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Format the date
            $date = new DateTime($row['date_created']);
            $row['date_created'] = $date->format('Y-m-d H:i:s');
            
            $data[] = [
                'prod_line_id' => $row['prod_line_id'],
                'product_name' => $row['product_name'],
                'length_m' => $row['length_m'],
                'width_m' => $row['width_m'],
                'weight_g' => $row['weight_g'],
                'quantity' => $row['quantity'],
                'date_created' => $row['date_created'],
                'status' => $row['status']
            ];
        }
        $response["success"] = true;
        $response["data"] = $data;
        $response["message"] = "Production line data fetched successfully.";
    } else {
        throw new Exception("Error fetching data: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error in get_production_line.php: " . $e->getMessage());
    $response["message"] = "Error: " . $e->getMessage();
}

$conn->close();
echo json_encode($response); 