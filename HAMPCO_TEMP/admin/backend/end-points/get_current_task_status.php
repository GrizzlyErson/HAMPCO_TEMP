<?php
/**
 * Get Current Task Status and Details
 * This endpoint fetches all active tasks with their current status and details
 * Used by the admin dashboard to display real-time task information
 */

header('Content-Type: application/json');
include('../class.php');

$db = new global_class();

try {
    // First, let's check what tasks exist
    $check_query = "SELECT COUNT(*) as total_tasks, 
                           SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
                           SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) as in_progress,
                           SUM(CASE WHEN status='submitted' THEN 1 ELSE 0 END) as submitted,
                           SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed
                    FROM task_assignments";
    $check_result = mysqli_query($db->conn, $check_query);
    $task_counts = mysqli_fetch_assoc($check_result);
    
    // Fetch active task assignments with their current status
    $query = "SELECT 
        ta.id as task_id,
        ta.prod_line_id,
        CONCAT('PL', LPAD(pl.prod_line_id, 4, '0')) as production_id,
        pl.product_name,
        pl.length_m,
        pl.width_m,
        pl.weight_g,
        pl.quantity,
        pl.date_created as production_created_date,
        ta.member_id,
        um.fullname as member_name,
        um.member_phone as member_phone,
        um.member_email as member_email,
        ta.role,
        ta.status as task_status,
        ta.deadline,
        ta.created_at as task_assigned_date,
        ta.updated_at as task_updated_date,
        NULL as date_started,
        NULL as date_submitted,
        CASE 
            WHEN ta.status = 'completed' THEN 'Completed'
            WHEN ta.status = 'submitted' THEN 'Submitted for Review'
            WHEN ta.status = 'in_progress' THEN 'In Progress'
            WHEN ta.status = 'pending' THEN 'Pending'
            ELSE ta.status
        END as status_label,
        CASE 
            WHEN ta.status = 'completed' THEN 'success'
            WHEN ta.status = 'submitted' THEN 'warning'
            WHEN ta.status = 'in_progress' THEN 'info'
            WHEN ta.status = 'pending' THEN 'secondary'
            ELSE 'secondary'
        END as status_badge,
        DATEDIFF(ta.deadline, NOW()) as days_remaining,
        CASE 
            WHEN DATEDIFF(ta.deadline, NOW()) < 0 THEN 'overdue'
            WHEN DATEDIFF(ta.deadline, NOW()) <= 1 THEN 'urgent'
            WHEN DATEDIFF(ta.deadline, NOW()) <= 3 THEN 'warning'
            ELSE 'on_track'
        END as deadline_status
    FROM task_assignments ta
    LEFT JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
    LEFT JOIN user_member um ON ta.member_id = um.id
    WHERE ta.status IN ('pending', 'in_progress', 'submitted')
    ORDER BY 
        CASE 
            WHEN DATEDIFF(ta.deadline, NOW()) < 0 THEN 0
            WHEN DATEDIFF(ta.deadline, NOW()) <= 1 THEN 1
            WHEN DATEDIFF(ta.deadline, NOW()) <= 3 THEN 2
            ELSE 3
        END ASC,
        ta.deadline ASC,
        ta.updated_at DESC";

    $result = mysqli_query($db->conn, $query);
    
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($db->conn));
    }

    $tasks = [];
    $count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $count++;
        // Calculate progress based on task status
        $progress = 0;
        switch ($row['task_status']) {
            case 'pending':
                $progress = 0;
                break;
            case 'in_progress':
                $progress = 50;
                break;
            case 'submitted':
                $progress = 75;
                break;
            case 'completed':
                $progress = 100;
                break;
        }

        // Determine if task is urgent
        $is_urgent = $row['deadline_status'] === 'overdue' || $row['deadline_status'] === 'urgent';

        $tasks[] = [
            'task_id' => $row['task_id'],
            'production_id' => $row['production_id'],
            'prod_line_id' => $row['prod_line_id'],
            'product_name' => $row['product_name'],
            'product_details' => [
                'length_m' => $row['length_m'],
                'width_m' => $row['width_m'],
                'weight_g' => $row['weight_g'],
                'quantity' => $row['quantity']
            ],
            'member' => [
                'id' => $row['member_id'],
                'name' => $row['member_name'],
                'phone' => $row['member_phone'],
                'email' => $row['member_email'],
                'role' => $row['role']
            ],
            'status' => [
                'current' => $row['task_status'],
                'label' => $row['status_label'],
                'badge' => $row['status_badge'],
                'progress' => $progress,
                'is_urgent' => $is_urgent
            ],
            'dates' => [
                'production_created' => $row['production_created_date'],
                'task_assigned' => $row['task_assigned_date'],
                'task_started' => $row['date_started'],
                'task_submitted' => $row['date_submitted'],
                'task_updated' => $row['task_updated_date'],
                'deadline' => $row['deadline'],
                'days_remaining' => $row['days_remaining'],
                'deadline_status' => $row['deadline_status']
            ]
        ];
    }

    // Summary statistics
    $summary = [
        'total_active_tasks' => count($tasks),
        'pending_tasks' => (int)($task_counts['pending'] ?? 0),
        'in_progress_tasks' => (int)($task_counts['in_progress'] ?? 0),
        'submitted_tasks' => (int)($task_counts['submitted'] ?? 0),
        'completed_tasks' => (int)($task_counts['completed'] ?? 0),
        'total_tasks' => (int)($task_counts['total_tasks'] ?? 0),
        'overdue_tasks' => count(array_filter($tasks, fn($t) => $t['status']['is_urgent'] && $t['dates']['deadline_status'] === 'overdue')),
        'urgent_tasks' => count(array_filter($tasks, fn($t) => $t['status']['is_urgent']))
    ];

    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'summary' => $summary,
        'tasks' => $tasks,
        'debug' => [
            'query_executed' => true,
            'rows_fetched' => $count,
            'task_counts' => $task_counts
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}