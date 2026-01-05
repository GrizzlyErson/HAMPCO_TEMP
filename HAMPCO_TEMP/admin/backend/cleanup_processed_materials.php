<?php
require_once '../../function/connection.php';

try {
    // Remove any final products from processed_materials table
    $sql = "DELETE FROM processed_materials 
            WHERE processed_materials_name IN ('Piña Seda', 'Pure Piña Cloth')";

    // Execute the query
    $conn->exec($sql);
    echo "Successfully removed final products from processed_materials table";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 