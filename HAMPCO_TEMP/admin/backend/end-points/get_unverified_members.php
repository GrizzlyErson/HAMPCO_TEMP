<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();
    
    $unverified = $db->fetch_members_by_status(0);
    $members = [];
    
    if ($unverified && $unverified->num_rows > 0) {
        while ($row = $unverified->fetch_assoc()) {
            $members[] = [
                'id' => $row['umid'],
                'member_fullname' => $row['umfullname'],
                'member_role' => $row['umrole'],
                'member_phone' => $row['umphone'],
                'member_email' => $row['umemail']
            ];
        }
    }
    
    echo json_encode($members);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
