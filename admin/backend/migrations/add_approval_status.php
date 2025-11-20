<?php
require_once '../class.php';

$db = new global_class();

try {
    // Start transaction
    mysqli_begin_transaction($db->conn);

    // Add approval_status column if it doesn't exist
    $alter_query = "ALTER TABLE member_self_tasks 
                   ADD COLUMN IF NOT EXISTS approval_status 
                   ENUM('pending', 'approved', 'rejected') 
                   NOT NULL DEFAULT 'pending' 
                   AFTER status";
    
    if (!mysqli_query($db->conn, $alter_query)) {
        throw new Exception('Failed to add approval_status column: ' . mysqli_error($db->conn));
    }

    // Update existing records to match their task_approval_requests status
    $update_query = "UPDATE member_self_tasks mst 
                    LEFT JOIN task_approval_requests tar 
                    ON mst.production_id = tar.production_id 
                    SET mst.approval_status = COALESCE(tar.status, 'pending')";
    
    if (!mysqli_query($db->conn, $update_query)) {
        throw new Exception('Failed to update existing records: ' . mysqli_error($db->conn));
    }

    // Commit transaction
    mysqli_commit($db->conn);
    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($db->conn);
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?> 