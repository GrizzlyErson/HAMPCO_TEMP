<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/class.php";

$db = new global_class();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['role'])) {
    echo json_encode(['error' => 'Role is required']);
    exit;
}

$role = $_POST['role'];

// Validate role
$valid_roles = ['knotter', 'warper', 'weaver'];
if (!in_array($role, $valid_roles)) {
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

try {
    // Debug: Log the role being requested
    error_log("Fetching members for role: " . $role);
    
    // Get all verified members for the specified role
    $query = "SELECT id, fullname, role, status 
              FROM user_member 
              WHERE role = ? AND status = 1 
              ORDER BY fullname";
    
    $stmt = $db->conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare query: " . $db->conn->error);
        throw new Exception("Failed to prepare query");
    }

    $stmt->bind_param("s", $role);
    if (!$stmt->execute()) {
        error_log("Failed to execute query: " . $stmt->error);
        throw new Exception("Failed to execute query");
    }

    $result = $stmt->get_result();
    if (!$result) {
        error_log("Failed to get result: " . $stmt->error);
        throw new Exception("Failed to get result");
    }
    
    // Debug: Log the SQL query
    error_log("SQL Query: " . $query . " [role = " . $role . "]");
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        // Debug: Log each member found
        error_log("Found member: " . json_encode($row));
        
        $members[] = [
            'id' => $row['id'],
            'name' => $row['fullname']
        ];
    }
    
    // Debug: Log the final response
    error_log("Sending response: " . json_encode($members));
    
    echo json_encode($members);
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error fetching members: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch members: ' . $e->getMessage()]);
}
?> 