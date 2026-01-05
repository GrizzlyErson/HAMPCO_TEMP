<?php
header('Content-Type: application/json');
include('../class.php');

$db = new global_class();

try {
    // Query to get all verified members with their availability status
    $query = "SELECT 
        id,
        id_number,
        fullname,
        role,
        availability_status
    FROM user_member 
    WHERE status = 1 
    ORDER BY role, fullname ASC";

    $result = $db->conn->query($query);
    
    $members = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $members[] = [
                'id' => $row['id'],
                'fullname' => $row['fullname'],
                'role' => strtolower($row['role']),
                'availability_status' => $row['availability_status']
            ];
        }
    }

    echo json_encode($members);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch workforce members',
        'message' => $e->getMessage()
    ]);
}
?>
