<?php
header('Content-Type: application/json');
include('../class.php');

$db = new global_class();

if (!isset($_GET['role'])) {
    echo json_encode(['error' => 'Role parameter is required']);
    exit;
}

$role = $_GET['role'];
$valid_roles = ['knotter', 'warper', 'weaver', 'all'];

if (!in_array($role, $valid_roles)) {
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

// Get verified members by role with their current task status
$query = "SELECT 
    um.id, 
    um.fullname,
    um.role, 
    CASE 
        WHEN ta.status = 'in_progress' THEN 'Work In Progress'
        WHEN ta.status = 'pending' THEN 'Occupied (Pending)'
        ELSE 'Available'
    END as work_status
FROM user_member um
LEFT JOIN (
    SELECT member_id, status 
    FROM task_assignments 
    WHERE status = 'in_progress' OR status = 'pending'
    GROUP BY member_id
) ta ON um.id = ta.member_id
WHERE um.status = 1";

if ($role !== 'all') {
    $query .= " AND um.role = ?";
}

$query .= " ORDER BY um.fullname ASC";

$stmt = $db->conn->prepare($query);
if ($role !== 'all') {
    $stmt->bind_param("s", $role);
}
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

echo json_encode($members);
$stmt->close(); 