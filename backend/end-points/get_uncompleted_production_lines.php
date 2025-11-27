<?php
require_once '../dbconnect.php';
require_once '../class.php';

$db = new DB_con();
$conn = $db->conn;

header('Content-Type: application/json');

$query = "SELECT prod_line_id, product_name FROM production_line WHERE status != 'completed' ORDER BY prod_line_id DESC";
$result = mysqli_query($conn, $query);

if ($result) {
    $production_lines = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $production_lines[] = $row;
    }
    echo json_encode(['success' => true, 'production_lines' => $production_lines]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch production lines: ' . mysqli_error($conn)]);
}
?>