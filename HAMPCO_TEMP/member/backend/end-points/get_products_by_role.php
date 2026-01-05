<?php
require_once "../../../function/database.php";

header('Content-Type: application/json');

try {
    $db = new Database();
    
    // Get member role from request
    $member_role = isset($_GET['role']) ? strtolower($_GET['role']) : '';
    
    if (empty($member_role)) {
        throw new Exception('Member role is required');
    }
    
    $products = [];
    
    // Return specific products based on role
    switch ($member_role) {
        case 'weaver':
            $products = ['Piña Seda', 'Pure Piña Cloth'];
            break;
        case 'knotter':
            $products = ['Knotted Liniwan', 'Knotted Bastos'];
            break;
        case 'warper':
            $products = ['Warped Silk'];
            break;
        default:
            throw new Exception('Invalid member role');
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_products_by_role.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 