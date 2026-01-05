<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ['success' => false, 'message' => ''];

try {
    // Create product_materials table
    $create_table = "CREATE TABLE IF NOT EXISTS `product_materials` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_name` varchar(255) NOT NULL,
        `member_role` varchar(20) NOT NULL,
        `material_type` enum('raw','processed') NOT NULL,
        `material_name` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `product_name` (`product_name`),
        KEY `member_role` (`member_role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$conn->query($create_table)) {
        throw new Exception("Failed to create table: " . $conn->error);
    }
    
    // Clear existing data
    $conn->query("DELETE FROM `product_materials`");
    
    // Insert material requirements with correct product names
    $insert_materials = "INSERT INTO `product_materials` (`product_name`, `member_role`, `material_type`, `material_name`) VALUES
    -- Knotter materials
    ('Knotted Liniwan', 'knotter', 'raw', 'Pina Loose Liniwan'),
    ('Knotted Bastos', 'knotter', 'raw', 'Pina Loose Bastos'),
    
    -- Weaver materials
    ('Piña Seda', 'weaver', 'processed', 'Knotted Bastos'),
    ('Piña Seda', 'weaver', 'processed', 'Warped Silk'),
    ('Pure Piña Cloth', 'weaver', 'processed', 'Knotted Liniwan'),
    
    -- Warper materials  
    ('Warped Silk', 'warper', 'raw', 'Pina Loose')";
    
    if (!$conn->query($insert_materials)) {
        throw new Exception("Failed to insert materials: " . $conn->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Product materials table created and populated successfully';
    
} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

echo json_encode($response);
?>
