<?php
session_start();
require_once "../../../function/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_GET['product_name']) || !isset($_GET['weight'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$db = new Database();
$product_name = $_GET['product_name'];
$weight = floatval($_GET['weight']);

try {
    // Calculate required materials based on product type
    if ($product_name === 'Knotted Liniwan') {
        $multiplier = 1.22;
        $material_name = 'Piña Loose (Liniwan/Washout)';
    } elseif ($product_name === 'Warped Silk') {
        $multiplier = 1.2; // Silk uses 1.2 multiplier as per admin calculator
        $material_name = 'Silk';
    } else { // Knotted Bastos
        $multiplier = 1.22;
        $material_name = 'Piña Loose (Bastos)';
    }

    $required_material = round($weight * $multiplier, 2);

    $response = [
        'success' => true,
        'data' => [
            'product' => $product_name,
            'weight' => number_format($weight, 3), // Match admin's 3 decimal places
            'materials' => [
                [
                    'name' => $material_name,
                    'amount' => number_format($required_material, 2) // Match admin's 2 decimal places
                ]
            ]
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error getting materials information: ' . $e->getMessage()
    ]);
} 