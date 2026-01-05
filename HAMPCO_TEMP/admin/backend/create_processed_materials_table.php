<?php
require_once '../../function/connection.php';

try {
    // Create processed_materials table
    $sql = "CREATE TABLE IF NOT EXISTS processed_materials (
        id INT(11) NOT NULL AUTO_INCREMENT,
        processed_materials_name VARCHAR(60) NOT NULL,
        weight DECIMAL(10,3) NOT NULL DEFAULT 0.000,
        status VARCHAR(60) NOT NULL DEFAULT 'Available',
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    // Execute the query
    $conn->exec($sql);
    echo "Table 'processed_materials' created successfully<br>";

    // Insert initial data
    $initial_data = [
        ['Knotted Bastos', 1000.000],
        ['Knotted Liniwan', 1000.000],
        ['Warped Silk', 1000.000]
    ];

    $insert_sql = "INSERT INTO processed_materials (processed_materials_name, weight, status) 
                   VALUES (?, ?, 'Available')";
    $stmt = $conn->prepare($insert_sql);

    foreach ($initial_data as $data) {
        $stmt->execute([$data[0], $data[1]]);
    }
    echo "Initial processed materials data added successfully";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 