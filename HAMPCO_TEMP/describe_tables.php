<?php
define('ALLOW_ACCESS', true);
require_once 'function/db_connect.php';

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

describeTable($db->conn, 'product_materials');
describeTable($db->conn, 'raw_materials');
describeTable($db->conn, 'processed_materials');

$db->conn->close();
?>