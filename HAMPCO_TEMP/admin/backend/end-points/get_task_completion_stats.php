<?php
/**
 * Get Task Completion Statistics by Role
 * Returns percentage of completed tasks for each role (Weaver, Knotter, Warper)
 */

header('Content-Type: application/json');
include('../class.php');

$db = new global_class();

try {
    // Get completed tasks by role
    $query = "SELECT 
        um.role,
        COUNT(ta.id) as completed_tasks,
        (
            SELECT COUNT(DISTINCT um2.id) 
            FROM user_member um2 
            WHERE um2.role = um.role
        ) as total_members
    FROM task_assignments ta
    JOIN user_member um ON ta.member_id = um.id
    WHERE ta.status = 'completed'
    GROUP BY um.role";
    
    $result = mysqli_query($db->conn, $query);
    
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($db->conn));
    }

    $stats = [
        'knotter' => ['completed' => 0, 'percentage' => 0],
        'warper' => ['completed' => 0, 'percentage' => 0],
        'weaver' => ['completed' => 0, 'percentage' => 0]
    ];

    $totalCompleted = 0;
    $roleData = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $role = strtolower($row['role']);
        if (isset($stats[$role])) {
            $stats[$role]['completed'] = intval($row['completed_tasks']);
            $roleData[$role] = $row;
            $totalCompleted += intval($row['completed_tasks']);
        }
    }

    // Calculate percentages
    if ($totalCompleted > 0) {
        foreach ($stats as $role => $data) {
            $stats[$role]['percentage'] = round(($data['completed'] / $totalCompleted) * 100);
        }
    } else {
        // If no completed tasks, distribute evenly
        foreach ($stats as $role => &$data) {
            $data['percentage'] = 0;
        }
    }

    // Get total statistics
    $total_query = "SELECT 
        COUNT(*) as total_completed,
        COUNT(DISTINCT member_id) as unique_members
    FROM task_assignments
    WHERE status = 'completed'";
    
    $total_result = mysqli_query($db->conn, $total_query);
    $total_stats = mysqli_fetch_assoc($total_result);

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'summary' => [
            'total_completed_tasks' => intval($total_stats['total_completed']),
            'unique_members_with_completed_tasks' => intval($total_stats['unique_members'])
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}