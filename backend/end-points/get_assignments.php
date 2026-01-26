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

// If status is 'pending', get only pending assignments
// Otherwise, get all assignments to show real-time status
$where_clause = "";
if ($status === 'pending') {
    $where_clause = "AND ta.status = 'pending'";
} else {
    // Get all statuses when called without specific status filter
    $where_clause = "AND ta.status IN ('pending', 'in_progress', 'submitted', 'approved', 'declined')";
}

$query = "SELECT 
            ta.id, 
            ta.prod_line_id,
            pl.product_name,
            ta.role, 
            ta.deadline,
            ta.status,
            ta.decline_reason,
            ta.updated_at,
            um.fullname as member_name
          FROM task_assignments ta
          JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
          LEFT JOIN user_member um ON ta.member_id = um.id
          WHERE ta.member_id = '$member_id'
          $where_clause
          ORDER BY ta.updated_at DESC";

$result = mysqli_query($conn, $query);

if ($result) {
    $assignments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = [
            'id' => $row['id'],
            'prod_line_id' => $row['prod_line_id'],
            'product_name' => $row['product_name'],
            'role' => $row['role'],
            'deadline' => $row['deadline'],
            'status' => $row['status'],
            'decline_reason' => $row['decline_reason'],
            'updated_at' => $row['updated_at'],
            'member_name' => $row['member_name']
        ];
    }
    echo json_encode(['success' => true, 'assignments' => $assignments]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch assignments: ' . mysqli_error($conn)]);
}
?>