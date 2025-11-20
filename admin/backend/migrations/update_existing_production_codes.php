<?php
require_once __DIR__ . '/../class.php';
$db = new global_class();

try {
    // Start transaction
    $db->conn->begin_transaction();
    
    // Get all records without production code
    $query = "SELECT prod_line_id FROM production_line WHERE production_code IS NULL OR production_code = ''";
    $result = $db->conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        // Generate a unique production code for each record
        $timestamp = date('YmdHis');
        $random = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3);
        $production_code = 'PROD-' . $timestamp . '-' . $random;
        
        // Update the record
        $update_query = "UPDATE production_line SET production_code = ? WHERE prod_line_id = ?";
        $stmt = $db->conn->prepare($update_query);
        $stmt->bind_param("si", $production_code, $row['prod_line_id']);
        $stmt->execute();
        
        // Add a small delay to ensure unique timestamps
        usleep(1000); // 1ms delay
    }
    
    // Commit transaction
    $db->conn->commit();
    echo "Successfully updated all existing records with production codes.";
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($db->conn)) {
        $db->conn->rollback();
    }
    echo "Error updating records: " . $e->getMessage();
}
?> 