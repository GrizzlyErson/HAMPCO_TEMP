<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ALLOW_ACCESS', true);

// Define a base path for includes
define('BASE_PATH', realpath(__DIR__));

require_once BASE_PATH . '/admin/backend/dbconnect.php';

function describeTable($conn, $tableName) {
    $result = $conn->query("DESCRIBE `$tableName`");
    if ($result) {
        echo "<h3>Table: $tableName</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "Error describing table $tableName: " . $conn->error . "<br>";
    }
}

// Check if the database connection is valid
if (isset($db) && $db->conn) {
    $conn = $db->conn;
    describeTable($conn, 'product_materials');
    describeTable($conn, 'raw_materials');
    describeTable($conn, 'processed_materials');
    $conn->close();
} else {
    echo "Database connection failed.";
}
?>