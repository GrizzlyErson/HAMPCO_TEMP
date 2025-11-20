<?php
session_start();
header('Content-Type: application/json');

if (!isset($db) || !($db instanceof global_class)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/function/connection.php";
    $db = new global_class();
}

try {
    if (!isset($_SESSION['id'])) {
        throw new Exception('Not logged in');
    }

    $member_id = $_SESSION['id'];
    $data = [
        'taskStats' => [
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0
        ],
        'recentTasks' => [],
        'currentTask' => null
    ];

    // Get task statistics
    $result = $db->conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM task_assignments
        WHERE member_id = $member_id");
    
    if ($result) {
        $stats = $result->fetch_assoc();
        $data['taskStats'] = [
            'pending' => (int)$stats['pending'],
            'in_progress' => (int)$stats['in_progress'],
            'completed' => (int)$stats['completed']
        ];
    }

    // Get recent tasks
    $result = $db->conn->query("SELECT 
        ta.status,
        ta.deadline,
        pl.product_name,
        pl.length_m,
        pl.width_m,
        pl.weight_g,
        pl.quantity
        FROM task_assignments ta
        JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
        WHERE ta.member_id = $member_id
        ORDER BY ta.created_at DESC
        LIMIT 5");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data['recentTasks'][] = $row;
        }
    }

    // Get current task (first non-completed task)
    $result = $db->conn->query("SELECT 
        ta.status,
        ta.deadline,
        ta.created_at as assigned_date,
        pl.product_name,
        pl.length_m,
        pl.width_m,
        pl.weight_g,
        pl.quantity
        FROM task_assignments ta
        JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
        WHERE ta.member_id = $member_id
        AND ta.status != 'completed'
        ORDER BY ta.created_at ASC
        LIMIT 1");

    if ($result && $result->num_rows > 0) {
        $data['currentTask'] = $result->fetch_assoc();
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 