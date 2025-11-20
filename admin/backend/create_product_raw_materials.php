<?php
require_once __DIR__ . '/class.php';
$db = new global_class();

try {
    // Drop the existing table
    $drop_table_sql = "DROP TABLE IF EXISTS `product_raw_materials`;";
    if (!$db->conn->query($drop_table_sql)) {
        throw new Exception("Error dropping table: " . $db->conn->error);
    }

    // Create the product_raw_materials table
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS `product_raw_materials` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_name` varchar(50) NOT NULL,
        `raw_material_name` varchar(50) NOT NULL,
        `raw_material_category` varchar(50) DEFAULT NULL,
        `consumption_rate` decimal(10,3) NOT NULL COMMENT 'Amount of raw material needed per unit',
        `consumption_unit` varchar(20) NOT NULL COMMENT 'Unit of measurement (g/m² or g/g)',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    
    if (!$db->conn->query($create_table_sql)) {
        throw new Exception("Error creating table: " . $db->conn->error);
    }
    
    // Insert initial data
    $insert_data_sql = "
    INSERT INTO `product_raw_materials` 
    (`product_name`, `raw_material_name`, `raw_material_category`, `consumption_rate`, `consumption_unit`) 
    VALUES
    ('Piña Seda', 'Piña Loose', 'Bastos', 15.000, 'g/m²'),
    ('Piña Seda', 'Silk', NULL, 7.000, 'g/m²'),
    ('Pure Piña Cloth', 'Piña Loose', 'Liniwan/Washout', 22.000, 'g/m²'),
    ('Knotted Liniwan', 'Piña Loose', 'Liniwan/Washout', 1.400, 'g/g'),
    ('Knotted Bastos', 'Piña Loose', 'Bastos', 1.100, 'g/g'),
    ('Warped Silk', 'Silk', NULL, 1.200, 'g/g');
    ";
    
    if (!$db->conn->query($insert_data_sql)) {
        throw new Exception("Error inserting data: " . $db->conn->error);
    }
    
    echo "Table recreated and data inserted successfully!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 