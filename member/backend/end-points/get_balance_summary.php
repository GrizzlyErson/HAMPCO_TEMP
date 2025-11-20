<?php
session_start();
require_once '../class.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit;
}

$db = new global_class();
$member_id = $_SESSION['id'];
$filter = $_GET['filter'] ?? 'all';

try {
    // Get member role
    $role_query = "SELECT role FROM user_member WHERE id = ?";
    $stmt = $db->conn->prepare($role_query);
    if (!$stmt) {
        throw new Exception("Failed to prepare role query: " . $db->conn->error);
    }
    $stmt->bind_param("i", $member_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute role query: " . $stmt->error);
    }
    $role_result = $stmt->get_result();
    $role_data = $role_result->fetch_assoc();
    if (!$role_data) {
        throw new Exception("Member not found");
    }
    $member_role = strtolower($role_data['role']);
    
    // Debug log
    error_log("Member role: " . $member_role);

    // Prepare date filter condition
    $date_condition = '';
    switch ($filter) {
        case 'this_month':
            $date_condition = 'AND MONTH(date_created) = MONTH(CURRENT_DATE()) AND YEAR(date_created) = YEAR(CURRENT_DATE())';
            break;
        case 'last_month':
            $date_condition = 'AND MONTH(date_created) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(date_created) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))';
            break;
        case 'this_year':
            $date_condition = 'AND YEAR(date_created) = YEAR(CURRENT_DATE())';
            break;
        default:
            $date_condition = '';
    }

    // Query the balance summary view
    $query = "SELECT * FROM member_balance_view WHERE member_id = ? $date_condition ORDER BY date_created DESC";
    
    $stmt = $db->conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->conn->error);
    }

    $stmt->bind_param("i", $member_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $balance_data = [];
    
    while ($row = $result->fetch_assoc()) {
        // Format the data
        $row['weight_g'] = $row['weight_g'] ? number_format($row['weight_g'], 3) : '-';
        $row['unit_rate'] = number_format($row['unit_rate'], 2);
        $row['total_amount'] = number_format($row['total_amount'], 2);
        $row['date_paid'] = $row['date_paid'] ? date('Y-m-d H:i', strtotime($row['date_paid'])) : null;
        $row['date_created'] = date('Y-m-d H:i', strtotime($row['date_created']));
        
        $balance_data[] = $row;
    }

    // Get earnings summary
    $summary_query = "SELECT * FROM member_earnings_summary WHERE member_id = ?";
    $stmt = $db->conn->prepare($summary_query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $summary_result = $stmt->get_result();
    $summary = $summary_result->fetch_assoc();

    if ($summary) {
        $summary['total_earnings'] = number_format($summary['total_earnings'], 2);
        $summary['pending_payments'] = number_format($summary['pending_payments'], 2);
        $summary['completed_payments'] = number_format($summary['completed_payments'], 2);
    }

    echo json_encode([
        'success' => true,
        'data' => $balance_data,
        'summary' => $summary,
        'member_role' => $member_role
    ]);

} catch (Exception $e) {
    error_log("Error in get_balance_summary.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching balance summary: ' . $e->getMessage()
    ]);
}
?>