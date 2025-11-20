<?php
header('Content-Type: application/json');
include('../class.php');

$db = new global_class();

try {
    // Query to get counts for each role with their work status
    $query = "SELECT 
        um.role,
        COUNT(*) as total,
        SUM(CASE WHEN ta.status = 'in_progress' THEN 0 ELSE 1 END) as available,
        SUM(CASE WHEN ta.status = 'in_progress' THEN 1 ELSE 0 END) as unavailable
    FROM user_member um
    LEFT JOIN (
        SELECT member_id, status 
        FROM task_assignments 
        WHERE status = 'in_progress'
        GROUP BY member_id
    ) ta ON um.id = ta.member_id
    WHERE um.status = 1
    GROUP BY um.role";

    $result = $db->conn->query($query);

    $data = [
        'knotters' => ['total' => 0, 'available' => 0, 'unavailable' => 0],
        'warpers' => ['total' => 0, 'available' => 0, 'unavailable' => 0],
        'weavers' => ['total' => 0, 'available' => 0, 'unavailable' => 0]
    ];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $role = strtolower($row['role']) . 's'; // Convert to plural
            if (isset($data[$role])) {
                $data[$role] = [
                    'total' => (int)$row['total'],
                    'available' => (int)$row['available'],
                    'unavailable' => (int)$row['unavailable']
                ];
            }
        }
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch member availability',
        'message' => $e->getMessage()
    ]);
}
?> 