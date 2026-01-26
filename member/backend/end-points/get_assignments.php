<?php
session_start();
header('Content-Type: application/json');
require_once '../dbconnect.php';
require_once '../class.php';

$db = new global_class();
$conn = $db->conn;

$member_id = $_SESSION['id'] ?? 0;

if ($member_id === 0) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

try {
    $status = isset($_GET['status']) ? $_GET['status'] : 'pending';
    
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
              WHERE ta.member_id = ?
              $where_clause
              ORDER BY ta.updated_at DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
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
    
    $stmt->close();
    echo json_encode(['success' => true, 'assignments' => $assignments]);

} catch (Exception $e) {
    error_log("Error fetching assignments: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching assignments: ' . $e->getMessage()]);
}
?>
