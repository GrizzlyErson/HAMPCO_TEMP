<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/class.php";

header('Content-Type: application/json');

$db = new global_class();
$response = array('success' => false);

try {
    if (!isset($_GET['prod_line_id'])) {
        throw new Exception('Production line ID is required');
    }

    $prod_line_id = $_GET['prod_line_id'];

    $query = "SELECT 
        ta.id,
        ta.member_id,
        m.fullname,
        ta.role,
        ta.status,
        ta.estimated_time,
        ta.deadline
    FROM task_assignments ta
    INNER JOIN user_member m ON ta.member_id = m.id
    WHERE ta.prod_line_id = ?
    GROUP BY ta.member_id, ta.role
    ORDER BY ta.role, m.fullname";

    $stmt = $db->conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $db->conn->error);
    }

    $stmt->bind_param("i", $prod_line_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $members = array();

    while ($row = $result->fetch_assoc()) {
        $row['deadline'] = date('M d, Y', strtotime($row['deadline']));
        $members[] = $row;
    }

    $response['success'] = true;
    $response['members'] = $members;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 