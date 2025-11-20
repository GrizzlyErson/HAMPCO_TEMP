<?php
include('dbconnect.php');

$db = new db_connect();
if (!$db->connect()) {
    die("Database connection failed: " . $db->error);
}

$sql = "ALTER TABLE `user_member`
        ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) NOT NULL AFTER `email`,
        ADD COLUMN IF NOT EXISTS `role` VARCHAR(20) NOT NULL AFTER `phone`,
        ADD COLUMN IF NOT EXISTS `sex` VARCHAR(10) NOT NULL AFTER `role`,
        ADD COLUMN IF NOT EXISTS `id_number` VARCHAR(20) NOT NULL AFTER `sex`";

if ($db->conn->query($sql)) {
    echo "Table altered successfully";
} else {
    echo "Error altering table: " . $db->conn->error;
}
?> 