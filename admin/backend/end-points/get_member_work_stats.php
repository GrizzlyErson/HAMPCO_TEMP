<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../class.php';

try {
    $db = new global_class();
    $conn = $db->conn;
    
    // Get member statistics
    $stats = getMemberStats($db);
    
    // Get current tasks by members
    $currentTasks = getCurrentMemberTasks($db);
    
    // Get member productivity
    $productivity = getMemberProductivity($db);

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'currentTasks' => $currentTasks,
        'productivity' => $productivity
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getMemberStats($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        um.member_id,
        um.member_fullname,
        um.member_role,
        um.work_status,
        COUNT(DISTINCT ta.task_id) as total_tasks,
        SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        COALESCE(SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(DISTINCT ta.task_id), 0), 0) as completion_rate
    FROM user_member um
    LEFT JOIN task_assignments ta ON um.member_id = ta.member_id
    WHERE um.member_status = 'active'
    GROUP BY um.member_id, um.member_fullname, um.member_role, um.work_status
    ORDER BY completion_rate DESC
    LIMIT 10";
    
    $result = $conn->query($query);
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = [
            'id' => intval($row['member_id']),
            'name' => $row['member_fullname'],
            'role' => $row['member_role'],
            'status' => $row['work_status'],
            'totalTasks' => intval($row['total_tasks']),
            'completedTasks' => intval($row['completed_tasks']),
            'completionRate' => floatval($row['completion_rate'])
        ];
    }
    
    return $members;
}

function getCurrentMemberTasks($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        um.member_fullname,
        um.member_role,
        pl.prod_code,
        p.prod_name,
        ta.status,
        ta.date_assigned,
        ta.date_started
    FROM task_assignments ta
    JOIN user_member um ON ta.member_id = um.member_id
    JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
    JOIN product p ON pl.product_id = p.prod_id
    WHERE ta.status IN ('pending', 'in_progress')
    ORDER BY ta.date_assigned DESC
    LIMIT 15";
    
    $result = $conn->query($query);
    
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'member' => $row['member_fullname'],
            'role' => $row['member_role'],
            'productCode' => $row['prod_code'],
            'productName' => $row['prod_name'],
            'status' => $row['status'],
            'dateAssigned' => $row['date_assigned'],
            'dateStarted' => $row['date_started']
        ];
    }
    
    return $tasks;
}

function getMemberProductivity($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        um.member_role,
        COUNT(DISTINCT um.member_id) as member_count,
        COUNT(DISTINCT ta.task_id) as total_tasks,
        SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        COALESCE(SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(DISTINCT ta.task_id), 0), 0) as completion_rate
    FROM user_member um
    LEFT JOIN task_assignments ta ON um.member_id = ta.member_id
    WHERE um.member_status = 'active'
    GROUP BY um.member_role
    ORDER BY completion_rate DESC";
    
    $result = $conn->query($query);
    
    $productivity = [];
    while ($row = $result->fetch_assoc()) {
        $productivity[] = [
            'role' => $row['member_role'],
            'memberCount' => intval($row['member_count']),
            'totalTasks' => intval($row['total_tasks']),
            'completedTasks' => intval($row['completed_tasks']),
            'completionRate' => floatval($row['completion_rate'])
        ];
    }
    
    return $productivity;
}
?>
