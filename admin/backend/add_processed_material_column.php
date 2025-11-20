<?php
require_once '../../function/connection.php';

try {
    // Add is_processed_material column to stock_history table if it doesn't exist
    $sql = "ALTER TABLE stock_history 
            ADD COLUMN IF NOT EXISTS is_processed_material TINYINT(1) NOT NULL DEFAULT 0";

    // Execute the query
    $conn->exec($sql);
    echo "Column 'is_processed_material' added successfully to stock_history table";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 