<?php
require_once 'class.php';

$db = new global_class();

$alterQueries = [
    "ALTER TABLE `user_member` 
     ADD COLUMN IF NOT EXISTS `id_number` varchar(20) DEFAULT NULL AFTER `id`,
     ADD COLUMN IF NOT EXISTS `phone` varchar(20) DEFAULT NULL AFTER `email`,
     ADD COLUMN IF NOT EXISTS `role` varchar(20) DEFAULT NULL AFTER `phone`,
     ADD COLUMN IF NOT EXISTS `sex` varchar(10) DEFAULT NULL AFTER `role`,
     ADD COLUMN IF NOT EXISTS `availability_status` ENUM('available', 'unavailable') DEFAULT 'available' AFTER `status`"
];

foreach ($alterQueries as $query) {
    if ($db->conn->query($query)) {
        echo "Table altered successfully: " . $query . "\n";
    } else {
        echo "Error altering table: " . $db->conn->error . "\n";
    }
}

echo "All alterations completed.";
?> 