<?php
require_once '../../function/connection.php';

try {
    // Create finished_products table
    $sql = "CREATE TABLE IF NOT EXISTS finished_products (
        id INT(11) NOT NULL AUTO_INCREMENT,
        product_name VARCHAR(255) NOT NULL,
        length_m DECIMAL(10,3) NOT NULL,
        width_m DECIMAL(10,3) NOT NULL,
        quantity INT(11) NOT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    // Execute the query
    $conn->exec($sql);
    echo "Table 'finished_products' created successfully";

} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 