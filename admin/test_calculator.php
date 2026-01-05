<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'hampco';
$username = 'root';
$password = '';

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

require_once 'backend/raw_material_calculator.php';

// Create database connection
$db = new Database();

// Create calculator instance
$calculator = new RawMaterialCalculator($db);

// Test case: 1m × 0.762m Piña Seda (standard measurements)
$result = $calculator->calculateMaterialsNeeded('Piña Seda', 1, 1.0, 0.762);

// Display results
echo "Test Case: 1m × 0.762m Piña Seda (Standard Measurements)\r\n";
echo "Expected:\r\n";
echo "- Piña Loose (Bastos): 15.24g\r\n";
echo "- Silk: 6.86g\r\n\r\n";

echo "Actual Results:\r\n";
foreach ($result as $material) {
    echo "- {$material['name']}";
    if ($material['category']) {
        echo " ({$material['category']})";
    }
    echo ": " . number_format($material['amount'], 2) . "g\r\n";
}

// Debug output
echo "\r\nDebug Info:\r\n";
var_dump($result);
?> 