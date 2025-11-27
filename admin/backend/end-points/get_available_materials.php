<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ['success' => false, 'raw_materials' => [], 'processed_materials' => []];

try {
    // Get raw materials
    $raw_sql = "SELECT raw_materials_name, SUM(rm_quantity) as total_stocks FROM raw_materials WHERE rm_status = 'Available' GROUP BY raw_materials_name";
    $raw_result = $conn->query($raw_sql);
    if ($raw_result) {
        while ($row = $raw_result->fetch_assoc()) {
            $response['raw_materials'][] = ['rm_name' => $row['raw_materials_name'], 'rm_stocks' => $row['total_stocks']];
        }
    }

    // Get processed materials
    $processed_sql = "SELECT processed_materials_name, weight FROM processed_materials WHERE status = 'Available'";
    $processed_result = $conn->query($processed_sql);
    if ($processed_result) {
        while ($row = $processed_result->fetch_assoc()) {
            $response['processed_materials'][] = ['material_name' => $row['processed_materials_name'], 'quantity' => $row['weight']];
        }
    }

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>