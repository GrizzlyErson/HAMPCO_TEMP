<?php
session_start();
require_once '../../../function/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit;
}

$db = new Database();
$member_id = $_SESSION['id'];
$filter = $_GET['filter'] ?? 'all';

try {
    // Prepare date filter condition
    $date_condition = '';
    switch ($filter) {
        case 'this_month':
            $date_condition = 'AND MONTH(ta.updated_at) = MONTH(CURRENT_DATE()) AND YEAR(ta.updated_at) = YEAR(CURRENT_DATE())';
            break;
        case 'last_month':
            $date_condition = 'AND MONTH(ta.updated_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(ta.updated_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))';
            break;
        case 'this_year':
            $date_condition = 'AND YEAR(ta.updated_at) = YEAR(CURRENT_DATE())';
            break;
        default:
            $date_condition = '';
    }

    // Get completed tasks and earnings
    $query = "SELECT 
        ta.id as task_id,
        pl.product_name,
        ta.updated_at as completion_date,
        CASE 
            WHEN um.role = 'knotter' THEN pl.weight_g * 50
            WHEN um.role = 'warper' THEN pl.weight_g * 60
            WHEN um.role = 'weaver' THEN (pl.length_m * pl.width_m * 100)
            ELSE 0
        END as amount,
        COALESCE(p.status, 'pending') as payment_status
    FROM task_assignments ta
    JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
    JOIN user_member um ON ta.member_id = um.id
    LEFT JOIN payments p ON ta.id = p.task_id
    WHERE ta.member_id = ? 
    AND ta.status = 'completed'
    $date_condition
    ORDER BY ta.updated_at DESC";

    $stmt = $db->conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $earnings = [];
    $total_earnings = 0;
    $pending_payments = 0;
    $completed_tasks = 0;

    while ($row = $result->fetch_assoc()) {
        $earnings[] = [
            'task_id' => $row['task_id'],
            'product_name' => $row['product_name'],
            'completion_date' => date('Y-m-d', strtotime($row['completion_date'])),
            'amount' => floatval($row['amount']),
            'payment_status' => $row['payment_status']
        ];

        $completed_tasks++;
        if ($row['payment_status'] === 'paid') {
            $total_earnings += $row['amount'];
        } else {
            $pending_payments += $row['amount'];
        }
    }

    echo json_encode([
        'success' => true,
        'earnings' => $earnings,
        'total_earnings' => $total_earnings,
        'pending_payments' => $pending_payments,
        'completed_tasks' => $completed_tasks
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 