<?php
session_start();
require_once '../dbconnect.php';
require_once '../class.php';

$db = new DB_con();
$conn = $db->conn;

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$member_id = $_SESSION['id'];
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'pending';

$query = "SELECT 
            ta.id, 
            ta.prod_line_id,
            pl.product_name,
            ta.role, 
            ta.deadline 
          FROM task_assignments ta
          JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
          WHERE ta.member_id = '$member_id' AND ta.status = '$status'";

$result = mysqli_query($conn, $query);

if ($result) {
    $assignments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = [
            'id' => $row['id'],
            'prod_line_id' => $row['prod_line_id'],
            'product_name' => $row['product_name'],
            'role' => $row['role'],
            'deadline' => $row['deadline']
        ];
    }
    echo json_encode(['success' => true, 'assignments' => $assignments]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch assignments: ' . mysqli_error($conn)]);
}
?>