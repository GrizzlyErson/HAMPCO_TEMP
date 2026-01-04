<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ['success' => false, 'tables' => [], 'product_materials_data' => [], 'sample_products' => []];

try {
    // Check if product_materials table exists
    $tables_query = "SHOW TABLES LIKE 'product_materials'";
    $result = $conn->query($tables_query);
    
    if ($result && $result->num_rows > 0) {
        $response['tables']['product_materials'] = 'EXISTS';
        
        // Get all data from product_materials
        $data_query = "SELECT * FROM product_materials LIMIT 20";
        $data_result = $conn->query($data_query);
        
        if ($data_result) {
            while ($row = $data_result->fetch_assoc()) {
                $response['product_materials_data'][] = $row;
            }
        }
        
        // Get distinct product names
        $product_query = "SELECT DISTINCT product_name FROM product_materials";
        $product_result = $conn->query($product_query);
        
        if ($product_result) {
            while ($row = $product_result->fetch_assoc()) {
                $response['sample_products'][] = $row['product_name'];
            }
        }
    } else {
        $response['tables']['product_materials'] = 'DOES NOT EXIST';
    }
    
    // Check raw_materials table
    $raw_table = "SHOW TABLES LIKE 'raw_materials'";
    $raw_result = $conn->query($raw_table);
    $response['tables']['raw_materials'] = $raw_result && $raw_result->num_rows > 0 ? 'EXISTS' : 'DOES NOT EXIST';
    
    // Check processed_materials table
    $proc_table = "SHOW TABLES LIKE 'processed_materials'";
    $proc_result = $conn->query($proc_table);
    $response['tables']['processed_materials'] = $proc_result && $proc_result->num_rows > 0 ? 'EXISTS' : 'DOES NOT EXIST';
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
