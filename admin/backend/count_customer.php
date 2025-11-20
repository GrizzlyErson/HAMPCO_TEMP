<?php


$db = new db_connect();
if (!$db->connect()) {
    die("Database connection failed: " . $db->error);
}

try {
    // Create a new PDO connection
    $host = 'localhost';
    $dbname = 'hampco';
    $username = 'root';
    $password = '';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to count users
    $stmt = $pdo->query("SELECT COUNT(*) AS total_customers FROM user_customer");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Output the count
    echo $result['total_customers'];
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>