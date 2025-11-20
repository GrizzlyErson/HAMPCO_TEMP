<?php
// Prevent this file from being included multiple times
if (defined('TASKS_LOADED')) {
    return;
}
define('TASKS_LOADED', true);

// Ensure this is an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo "<tr><td colspan='4' class='py-3 px-6 text-center text-gray-500'>Loading tasks...</td></tr>";
    return;
}

// Ensure we have a database connection
if (!isset($db) || !($db instanceof global_class)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/class.php";
    $db = new global_class();
}

try {
    // Check database connection
    if (!$db->conn || $db->conn->connect_error) {
        error_log("Database connection error in list_assigned_tasks.php");
        throw new Exception("Database connection failed. Please try again later.");
    }

    // First check if the task_assignments table exists
    $check_table = $db->conn->query("SHOW TABLES LIKE 'task_assignments'");
    if ($check_table->num_rows === 0) {
        error_log("task_assignments table does not exist");
        throw new Exception("Required database table not found.");
    }

    $query = "SELECT DISTINCT
        p.prod_line_id,
        p.product_name,
        p.length_m,
        p.width_m,
        p.weight_g,
        p.quantity,
        CASE 
            WHEN COUNT(*) = SUM(CASE WHEN ta.status = 'completed' THEN 1 ELSE 0 END) THEN 'completed'
            WHEN SUM(CASE WHEN ta.status IN ('in_progress', 'completed') THEN 1 ELSE 0 END) > 0 THEN 'in_progress'
            ELSE 'pending'
        END as status
    FROM production_line p
    INNER JOIN task_assignments ta ON p.prod_line_id = ta.prod_line_id
    GROUP BY p.prod_line_id
    ORDER BY p.prod_line_id DESC";

    $stmt = $db->conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $db->conn->error);
        throw new Exception("Failed to prepare query: " . $db->conn->error);
    }

    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Failed to execute query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result === false) {
        error_log("Get result failed: " . $stmt->error);
        throw new Exception("Failed to get query results: " . $stmt->error);
    }
    
    if ($result->num_rows === 0) {
        echo "<tr><td colspan='4' class='py-3 px-6 text-center text-gray-500'>No tasks assigned yet.</td></tr>";
    } else {
        while ($task = $result->fetch_assoc()) {
            $statusClass = '';
            $statusBadge = '';
            
            switch($task['status']) {
                case 'pending':
                    $statusClass = 'bg-yellow-100 text-yellow-800';
                    $statusBadge = 'Pending';
                    break;
                case 'in_progress':
                    $statusClass = 'bg-blue-100 text-blue-800';
                    $statusBadge = 'In Progress';
                    break;
                case 'completed':
                    $statusClass = 'bg-green-100 text-green-800';
                    $statusBadge = 'Completed';
                    break;
                default:
                    $statusClass = 'bg-gray-100 text-gray-800';
                    $statusBadge = ucfirst($task['status']);
            }

            // Generate display ID
            $display_id = 'PROD-' . str_pad($task['prod_line_id'], 6, '0', STR_PAD_LEFT);

            echo "<tr class='border-b border-gray-200 hover:bg-gray-50'>";
            echo "<td class='py-3 px-6 font-mono text-sm'>" . htmlspecialchars($display_id) . "</td>";
            echo "<td class='py-3 px-6'>" . htmlspecialchars($task['product_name']) . "</td>";
            echo "<td class='py-3 px-6'><span class='px-2 py-1 rounded-full text-xs {$statusClass}'>" . $statusBadge . "</span></td>";
            echo "<td class='py-3 px-6'>";
            echo "<div class='flex space-x-2'>";
            
            // Details button
            echo "<button onclick='showDetails(" . json_encode([
                "product_name" => $task['product_name'],
                "length" => $task['length_m'],
                "width" => $task['width_m'],
                "weight" => $task['weight_g'],
                "quantity" => $task['quantity']
            ]) . ")' class='bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200'>Details</button>";
            
            // View Assigned Members button
            echo "<button onclick='viewAssignedMembers(" . $task['prod_line_id'] . ")' class='bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200'>View Assigned Members</button>";
            
            // Delete button
            echo "<button onclick='deleteAssignedTask(" . $task['prod_line_id'] . ")' class='bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200'>Delete</button>";
            
            echo "</div>";
            echo "</td>";
            echo "</tr>";
        }
    }
    
    $stmt->close();

} catch(Exception $e) {
    error_log("Error in list_assigned_tasks.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo "<tr><td colspan='4' class='py-3 px-6 text-center text-red-500'>Error loading tasks: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}
?> 