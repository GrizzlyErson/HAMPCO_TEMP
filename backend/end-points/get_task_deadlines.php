<?php
require_once '../dbconnect.php';
require_once '../class.php';

$db = new DB_con();
$conn = $db->conn;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'deadlines' => []];

$query = "SELECT 
            ta.prod_line_id,
            pl.product_name,
            um.fullname AS member_name,
            ta.role,
            ta.deadline,
            ta.status AS task_status
          FROM task_assignments ta
          JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
          JOIN user_member um ON ta.member_id = um.id
          WHERE ta.status NOT IN ('completed', 'approved')
          ORDER BY ta.deadline ASC";

$result = mysqli_query($conn, $query);

if ($result) {
    $deadlines = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $deadlines[] = $row;
    }
    $response['success'] = true;
    $response['deadlines'] = $deadlines;
} else {
    $response['message'] = 'Failed to fetch task deadlines: ' . mysqli_error($conn);
}

echo json_encode($response);
?>