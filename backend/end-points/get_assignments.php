<?php
require_once '../dbconnect.php';
require_once '../class.php';

$db = new DB_con();
$conn = $db->conn;

header('Content-Type: application/json');

if (!isset($_GET['prod_line_id'])) {
    echo json_encode(['success' => false, 'message' => 'Production Line ID is required.']);
    exit();
}

$prod_line_id = mysqli_real_escape_string($conn, $_GET['prod_line_id']);

$query = "SELECT id, member_id, role, deadline FROM task_assignments WHERE prod_line_id = '$prod_line_id'";
$result = mysqli_query($conn, $query);

if ($result) {
    $assignments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = [
            'task_id' => $row['id'],
            'member_id' => $row['member_id'],
            'role' => $row['role'],
            'deadline' => $row['deadline']
        ];
    }
    echo json_encode(['success' => true, 'assignments' => $assignments]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch assignments: ' . mysqli_error($conn)]);
}
?>