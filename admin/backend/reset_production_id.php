<?php
require_once 'class.php';
$db = new global_class();

try {
    // Start transaction
    $db->conn->begin_transaction();

    // Get all production items ordered by date
    $query = "SELECT * FROM production_line ORDER BY date_created ASC";
    $result = $db->conn->query($query);
    
    if (!$result) {
        throw new Exception("Error fetching production items: " . $db->conn->error);
    }

    // Store all records
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    // Truncate the table to reset auto-increment
    $truncate = "TRUNCATE TABLE production_line";
    if (!$db->conn->query($truncate)) {
        throw new Exception("Error truncating table: " . $db->conn->error);
    }

    // Reset auto-increment to 1
    $reset = "ALTER TABLE production_line AUTO_INCREMENT = 1";
    if (!$db->conn->query($reset)) {
        throw new Exception("Error resetting auto-increment: " . $db->conn->error);
    }

    // Reinsert all records in order
    foreach ($records as $record) {
        $insert = "INSERT INTO production_line (product_name, length_m, width_m, quantity, date_created, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->conn->prepare($insert);
        
        if (!$stmt) {
            throw new Exception("Error preparing insert statement: " . $db->conn->error);
        }

        $stmt->bind_param("siiiss", 
            $record['product_name'],
            $record['length_m'],
            $record['width_m'],
            $record['quantity'],
            $record['date_created'],
            $record['status']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error inserting record: " . $stmt->error);
        }

        $stmt->close();
    }

    // Commit transaction
    $db->conn->commit();
    echo "Production line IDs have been successfully reset and reordered.";

} catch (Exception $e) {
    // Rollback on error
    if ($db->conn) {
        $db->conn->rollback();
    }
    echo "Error: " . $e->getMessage();
}
?> 