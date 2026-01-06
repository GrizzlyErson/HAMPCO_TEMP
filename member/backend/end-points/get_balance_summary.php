<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../admin/backend/class.php';

header('Content-Type: application/json');

// Log start of script execution
error_log("get_balance_summary.php: Script started.");

if (!isset($_SESSION['id'])) {
    error_log("get_balance_summary.php: Session ID not set. Not logged in.");
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit;
}

$db = new global_class();
$member_id = $_SESSION['id'];
$filter = $_GET['filter'] ?? 'all';

error_log("get_balance_summary.php: Member ID: " . $member_id . ", Filter: " . $filter);

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
        throw new Exception("Member not found for ID: " . $member_id);
    }
    $member_role = strtolower($role_data['role']);
    
    error_log("get_balance_summary.php: Member role: " . $member_role);

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
    error_log("get_balance_summary.php: Date condition: " . $date_condition);

    // Query the balance summary view
    $query = "SELECT * FROM member_balance_view WHERE member_id = ? $date_condition ORDER BY date_created DESC";
    error_log("get_balance_summary.php: Balance query: " . $query . " with member_id: " . $member_id);
    
    $stmt = $db->conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed for balance query: " . $db->conn->error);
    }

    $stmt->bind_param("i", $member_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed for balance query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $balance_data = [];
    
    while ($row = $result->fetch_assoc()) {
        // Format the data
        $row['weight_g'] = $row['weight_g'] ? $row['weight_g'] : '-';
        $row['unit_rate'] = number_format((float)str_replace(',', '', $row['unit_rate']), 2, '.', '');
        $row['total_amount'] = number_format((float)str_replace(',', '', $row['total_amount']), 2, '.', '');
        $row['date_paid'] = $row['date_paid'] ? date('Y-m-d H:i', strtotime($row['date_paid'])) : null;
        $row['date_created'] = date('Y-m-d H:i', strtotime($row['date_created']));
        
        $balance_data[] = $row;
    }
    error_log("get_balance_summary.php: Fetched " . count($balance_data) . " balance records.");


    // Get earnings summary
    $summary_query = "SELECT * FROM member_earnings_summary WHERE member_id = ?";
    error_log("get_balance_summary.php: Summary query: " . $summary_query . " with member_id: " . $member_id);
    $stmt = $db->conn->prepare($summary_query);
    if (!$stmt) {
        throw new Exception("Prepare failed for summary query: " . $db->conn->error);
    }
    $stmt->bind_param("i", $member_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed for summary query: " . $stmt->error);
    }
    $summary_result = $stmt->get_result();
    $summary = $summary_result->fetch_assoc();

    if ($summary) {
        $summary['total_earnings'] = number_format((float)str_replace(',', '', $summary['total_earnings']), 2, '.', '');
        $summary['pending_payments'] = number_format((float)str_replace(',', '', $summary['pending_payments']), 2, '.', '');
        $summary['completed_payments'] = number_format((float)str_replace(',', '', $summary['completed_payments']), 2, '.', '');
        error_log("get_balance_summary.php: Fetched summary data: " . print_r($summary, true));
    } else {
        error_log("get_balance_summary.php: No summary data found for member ID: " . $member_id);
    }

    echo json_encode([
        'success' => true,
        'data' => $balance_data,
        'summary' => $summary,
        'member_role' => $member_role
    ]);
    error_log("get_balance_summary.php: Script finished successfully.");

} catch (Exception $e) {
    error_log("Error in get_balance_summary.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching balance summary: ' . $e->getMessage()
    ]);
}
?>