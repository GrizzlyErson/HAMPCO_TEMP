<?php
/**
 * Task Status Update Notification
 * This endpoint updates and logs task status changes for admin dashboard notifications
 */

header('Content-Type: application/json');
include('../class.php');

$db = new global_class();

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    if (!$action) {
        throw new Exception('Action parameter is required');
    }

    switch ($action) {
        case 'get-summary':
            // Get quick summary of current tasks for dashboard
            $query = "SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN ta.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN ta.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN ta.status = 'submitted' THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN DATEDIFF(ta.deadline, NOW()) < 0 AND ta.status != 'completed' THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM task_assignments ta
            WHERE ta.status IN ('pending', 'in_progress', 'submitted', 'completed')";
            
            $result = mysqli_query($db->conn, $query);
            $summary = mysqli_fetch_assoc($result);
            
            echo json_encode([
                'success' => true,
                'data' => $summary,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'get-urgent-tasks':
            // Get urgent/overdue tasks for dashboard alert
            $query = "SELECT 
                ta.id as task_id,
                CONCAT('PL', LPAD(pl.prod_line_id, 4, '0')) as production_id,
                pl.product_name,
                um.fullname as member_name,
                ta.role,
                ta.deadline,
                ta.status,
                DATEDIFF(ta.deadline, NOW()) as days_remaining,
                CASE 
                    WHEN DATEDIFF(ta.deadline, NOW()) < 0 THEN 'Overdue'
                    WHEN DATEDIFF(ta.deadline, NOW()) = 0 THEN 'Due Today'
                    WHEN DATEDIFF(ta.deadline, NOW()) = 1 THEN 'Due Tomorrow'
                    ELSE CONCAT('Due in ', DATEDIFF(ta.deadline, NOW()), ' days')
                END as deadline_label
            FROM task_assignments ta
            JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
            JOIN user_member um ON ta.member_id = um.id
            WHERE ta.status IN ('pending', 'in_progress', 'submitted')
            AND (DATEDIFF(ta.deadline, NOW()) <= 1 OR DATEDIFF(ta.deadline, NOW()) < 0)
            ORDER BY DATEDIFF(ta.deadline, NOW()) ASC, ta.deadline ASC
            LIMIT 10";
            
            $result = mysqli_query($db->conn, $query);
            $urgent_tasks = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $urgent_tasks[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'urgent_count' => count($urgent_tasks),
                'tasks' => $urgent_tasks,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'get-task-by-id':
            // Get specific task details
            $task_id = intval($_GET['task_id'] ?? $_POST['task_id'] ?? 0);
            if (!$task_id) {
                throw new Exception('task_id parameter is required');
            }

            $query = "SELECT 
                ta.*,
                pl.product_name,
                pl.length_m,
                pl.width_m,
                pl.weight_g,
                pl.quantity,
                pl.status as production_status,
                um.fullname as member_name,
                um.member_email,
                um.member_phone,
                CONCAT('PL', LPAD(pl.prod_line_id, 4, '0')) as production_id,
                DATEDIFF(ta.deadline, NOW()) as days_remaining
            FROM task_assignments ta
            JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
            JOIN user_member um ON ta.member_id = um.id
            WHERE ta.id = ?";
            
            $stmt = mysqli_prepare($db->conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $task_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                echo json_encode([
                    'success' => true,
                    'task' => $row,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('Task not found');
            }
            break;

        case 'log-status-change':
            // Log task status changes for audit trail
            $task_id = intval($_POST['task_id'] ?? 0);
            $old_status = $_POST['old_status'] ?? '';
            $new_status = $_POST['new_status'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (!$task_id || !$old_status || !$new_status) {
                throw new Exception('task_id, old_status, and new_status are required');
            }

            $query = "INSERT INTO task_status_logs (task_id, old_status, new_status, notes, logged_at) 
                      VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = mysqli_prepare($db->conn, $query);
            if (!$stmt) {
                // Table might not exist, create it
                $create_query = "CREATE TABLE IF NOT EXISTS task_status_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    task_id INT NOT NULL,
                    old_status VARCHAR(50),
                    new_status VARCHAR(50),
                    notes TEXT,
                    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (task_id) REFERENCES task_assignments(id)
                )";
                mysqli_query($db->conn, $create_query);
                $stmt = mysqli_prepare($db->conn, $query);
            }
            
            mysqli_stmt_bind_param($stmt, 'isss', $task_id, $old_status, $new_status, $notes);
            mysqli_stmt_execute($stmt);
            
            echo json_encode([
                'success' => true,
                'message' => 'Status change logged successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'get-dashboard-widget':
            // Get data for dashboard widget with all current task info
            $query = "SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN ta.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN ta.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN ta.status = 'submitted' THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN ta.status != 'completed' AND DATEDIFF(ta.deadline, NOW()) < 0 THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN ta.status != 'completed' AND DATEDIFF(ta.deadline, NOW()) <= 1 AND DATEDIFF(ta.deadline, NOW()) >= 0 THEN 1 ELSE 0 END) as due_soon
            FROM task_assignments ta";
            
            $result = mysqli_query($db->conn, $query);
            $stats = mysqli_fetch_assoc($result);

            // Get recent task updates
            $recent_query = "SELECT 
                ta.id as task_id,
                CONCAT('PL', LPAD(pl.prod_line_id, 4, '0')) as production_id,
                pl.product_name,
                um.fullname as member_name,
                ta.role,
                ta.status,
                ta.updated_at,
                CASE 
                    WHEN ta.status = 'pending' THEN 'Pending'
                    WHEN ta.status = 'in_progress' THEN 'In Progress'
                    WHEN ta.status = 'submitted' THEN 'Submitted for Review'
                    WHEN ta.status = 'completed' THEN 'Completed'
                END as status_label
            FROM task_assignments ta
            JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
            JOIN user_member um ON ta.member_id = um.id
            WHERE ta.status IN ('pending', 'in_progress', 'submitted', 'completed')
            ORDER BY ta.updated_at DESC
            LIMIT 5";
            
            $recent_result = mysqli_query($db->conn, $recent_query);
            $recent_updates = [];
            while ($row = mysqli_fetch_assoc($recent_result)) {
                $recent_updates[] = $row;
            }

            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'recent_updates' => $recent_updates,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        default:
            throw new Exception('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}