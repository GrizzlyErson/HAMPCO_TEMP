<?php
require_once __DIR__ . '/../../../function/connection.php';
require_once __DIR__ . '/../raw_material_calculator.php';

header('Content-Type: application/json');

try {
    // Get request parameters
    $productId = isset($_GET['prod_line_id']) ? intval($_GET['prod_line_id']) : null;
    
    if (!$productId) {
        throw new Exception('Product line ID is required');
    }
    
    // Get product details from production_line table
    $query = "SELECT * FROM production_line WHERE prod_line_id = ?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Product not found');
    }
    
    $product = $result->fetch_assoc();
    
    // Initialize calculator
    $calculator = new RawMaterialCalculator($db);
    
    // Calculate materials needed
    $materials = $calculator->calculateMaterialsNeeded(
        $product['product_name'],
        intval($product['quantity']),
        $product['length_m'],
        $product['width_m'],
        $product['weight_g']
    );
    
    // Return response
    echo json_encode([
        'success' => true,
        'product' => [
            'name' => $product['product_name'],
            'quantity' => intval($product['quantity']),
            'length' => $product['length_m'],
            'width' => $product['width_m'],
            'weight' => $product['weight_g']
        ],
        'materials' => $materials
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 