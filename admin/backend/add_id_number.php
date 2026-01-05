<?php
require_once 'dbconnect.php';

$sql = "ALTER TABLE `user_member` ADD COLUMN IF NOT EXISTS `id_number` VARCHAR(20) NOT NULL AFTER `password`";

if ($conn->query($sql) === TRUE) {
    echo "Column id_number added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?> 