<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    public $conn;
    
    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'hampco');
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }
}

$db = new Database();

// Check current rates
$query = "SELECT * FROM product_raw_materials WHERE product_name = 'Piña Seda'";
$result = $db->conn->query($query);

echo "<pre>\n";
echo "Current Consumption Rates:<br><br>";
while ($row = $result->fetch_assoc()) {
    echo "Product: {$row['product_name']}<br>";
    echo "Material: {$row['raw_material_name']}<br>";
    echo "Category: {$row['raw_material_category']}<br>";
    echo "Rate: {$row['consumption_rate']} {$row['consumption_unit']}<br><br>";
}

// Update to new rates
$query = "UPDATE product_raw_materials 
          SET consumption_rate = CASE 
              WHEN raw_material_name = 'Piña Loose' AND product_name = 'Piña Seda' THEN 19.680
              WHEN raw_material_name = 'Silk' AND product_name = 'Piña Seda' THEN 9.180
              ELSE consumption_rate
          END
          WHERE product_name = 'Piña Seda'";
$db->conn->query($query);

// Check updated rates
$query = "SELECT * FROM product_raw_materials WHERE product_name = 'Piña Seda'";
$result = $db->conn->query($query);

echo "Updated Consumption Rates:<br><br>";
while ($row = $result->fetch_assoc()) {
    echo "Product: {$row['product_name']}<br>";
    echo "Material: {$row['raw_material_name']}<br>";
    echo "Category: {$row['raw_material_category']}<br>";
    echo "Rate: {$row['consumption_rate']} {$row['consumption_unit']}<br><br>";
}
echo "</pre>";
?> 