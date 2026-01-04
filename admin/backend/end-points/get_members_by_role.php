<?php
header('Content-Type: application/json');
include('../class.php');

$db = new global_class();

if (!isset($_GET['role'])) {
    echo json_encode(['error' => 'Role parameter is required']);
    exit;
}

$role = $_GET['role'];
$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : null;
$valid_roles = ['knotter', 'warper', 'weaver', 'all'];

if (!in_array($role, $valid_roles)) {
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

// Map product names to required roles
$product_role_map = [
    'Piña Seda' => ['weaver'],
    'Pure Piña Cloth' => ['weaver'],
    'Knotted Liniwan' => ['knotter'],
    'Knotted Bastos' => ['knotter'],
    'Warped Silk' => ['warper']
];

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

// If product_name is provided, filter by the required roles for that product
if ($product_name && isset($product_role_map[$product_name])) {
    $required_roles = $product_role_map[$product_name];
    $placeholders = implode(',', array_fill(0, count($required_roles), '?'));
    $query .= " AND um.role IN (" . $placeholders . ")";
} elseif ($role !== 'all') {
    // If role is specified, filter by that role
    $query .= " AND um.role = ?";
}

$query .= " ORDER BY work_status ASC, um.fullname ASC";

$stmt = $db->conn->prepare($query);

if ($product_name && isset($product_role_map[$product_name])) {
    // Bind product-based roles
    $required_roles = $product_role_map[$product_name];
    $types = str_repeat('s', count($required_roles));
    $stmt->bind_param($types, ...$required_roles);
} elseif ($role !== 'all') {
    // Bind specific role
    $stmt->bind_param("s", $role);
}

$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    // Only include available members
    if ($row['work_status'] === 'Available') {
        $members[] = $row;
    }
}

echo json_encode($members);
$stmt->close(); 