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
        um.id,
        um.fullname,
        um.role,
        um.availability_status,
        COUNT(DISTINCT ta.id) as total_tasks,
        SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        COALESCE(SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(DISTINCT ta.id), 0), 0) as completion_rate
    FROM user_member um
    LEFT JOIN task_assignments ta ON um.id = ta.member_id
    WHERE um.status = 1
    GROUP BY um.id, um.fullname, um.role, um.availability_status
    ORDER BY completion_rate DESC
    LIMIT 10";
    
    $result = $conn->query($query);
    
    if (!$result) {
        return [];
    }
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = [
            'id' => intval($row['id']),
            'name' => $row['fullname'],
            'role' => ucfirst($row['role']),
            'status' => ucfirst($row['availability_status']),
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
        um.fullname,
        um.role,
        pl.prod_line_id,
        pl.product_name,
        ta.status,
        ta.created_at,
        ta.updated_at
    FROM task_assignments ta
    JOIN user_member um ON ta.member_id = um.id
    JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
    WHERE ta.status IN ('pending', 'in_progress')
    ORDER BY ta.created_at DESC
    LIMIT 15";
    
    $result = $conn->query($query);
    
    if (!$result) {
        return [];
    }
    
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'member' => $row['fullname'],
            'role' => ucfirst($row['role']),
            'productCode' => 'PL-' . $row['prod_line_id'],
            'productName' => $row['product_name'],
            'status' => $row['status'],
            'dateAssigned' => $row['created_at'],
            'dateStarted' => $row['updated_at']
        ];
    }
    
    return $tasks;
}

function getMemberProductivity($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        um.role,
        COUNT(DISTINCT um.id) as member_count,
        COUNT(DISTINCT ta.id) as total_tasks,
        SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        COALESCE(SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(DISTINCT ta.id), 0), 0) as completion_rate
    FROM user_member um
    LEFT JOIN task_assignments ta ON um.id = ta.member_id
    WHERE um.status = 1
    GROUP BY um.role
    ORDER BY completion_rate DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        return [];
    }
    
    $productivity = [];
    while ($row = $result->fetch_assoc()) {
        $productivity[] = [
            'role' => ucfirst($row['role']),
            'memberCount' => intval($row['member_count']),
            'totalTasks' => intval($row['total_tasks']),
            'completedTasks' => intval($row['completed_tasks']),
            'completionRate' => floatval($row['completion_rate'])
        ];
    }
    
    return $productivity;
}
?>

