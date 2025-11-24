<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../class.php';

try {
    $db = new global_class();

    $query = "
        SELECT 
            um.id,
            um.id_number,
            um.fullname,
            um.role,
            um.availability_status,
            um.date_created,
            COALESCE(SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END), 0) AS tasks_completed,
            COALESCE(COUNT(DISTINCT CASE 
                WHEN ta.status IN ('completed','in_progress','submitted') THEN DATE(ta.updated_at)
                WHEN ta.id IS NOT NULL THEN DATE(ta.created_at)
                ELSE NULL
            END), 0) AS days_worked,
            MAX(COALESCE(ta.updated_at, ta.created_at)) AS last_active,
            MAX(CASE WHEN ta.status = 'in_progress' THEN 1 ELSE 0 END) AS has_active_task
        FROM user_member um
        LEFT JOIN task_assignments ta ON ta.member_id = um.id
        WHERE um.status = 1
          AND um.role IN ('knotter','warper','weaver')
        GROUP BY 
            um.id,
            um.id_number,
            um.fullname,
            um.role,
            um.availability_status,
            um.date_created
        ORDER BY FIELD(um.role, 'knotter', 'warper', 'weaver'), um.fullname ASC
    ";

    $result = $db->conn->query($query);

    if (!$result) {
        throw new Exception("Failed to fetch worker data: " . $db->conn->error);
    }

    $workers = [];
    while ($row = $result->fetch_assoc()) {
        $lastActive = $row['last_active'] ?: $row['date_created'];

        $workers[] = [
            'id' => (int)$row['id'],
            'memberCode' => $row['id_number'],
            'name' => $row['fullname'],
            'role' => ucfirst($row['role']),
            'tasksCompleted' => (int)$row['tasks_completed'],
            'daysWorked' => (int)$row['days_worked'],
            'lastActive' => $lastActive ? date('Y-m-d', strtotime($lastActive)) : null,
            'status' => ((int)$row['has_active_task']) === 1 ? 'active' : 'lazy',
            'availability' => $row['availability_status'] ?? 'available'
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $workers
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

