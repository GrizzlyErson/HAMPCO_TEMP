<?php
require_once __DIR__ . '/../../function/db_connect.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Query to count production items from finished_products table
    $sql = "SELECT COUNT(*) AS total_items FROM finished_products";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo $row['total_items'];
    } else {
        echo "0";
    }
} catch (Exception $e) {
    // In case of error, return 0
    echo "0";
}

$conn->close();
?>
