<?php
session_start();
header('Content-Type: application/json');

require_once "../class.php";
require_once "../helpers/task_decline_helper.php";

try {
    $db = new global_class();
    $conn = $db->conn;

    if (!$conn) {
        throw new Exception('Database connection failed.');
    }

    ensureTaskDeclineTable($conn);

    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

    switch ($action) {
        case 'count':
            $countSql = "SELECT 
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'responded' THEN 1 ELSE 0 END) AS awaiting_member
            FROM task_decline_notifications";
            $countResult = $conn->query($countSql);
            $counts = $countResult ? $countResult->fetch_assoc() : ['pending' => 0, 'awaiting_member' => 0];
            echo json_encode([
                'success' => true,
                'counts' => [
                    'pending' => intval($counts['pending'] ?? 0),
                    'awaiting_member' => intval($counts['awaiting_member'] ?? 0),
                ]
            ]);
            break;

        case 'respond':
            $declineId = intval($_POST['decline_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');

            if ($declineId <= 0 || $message === '') {
                throw new Exception('Decline ID and message are required.');
            }

            saveDeclineAdminMessage($conn, $declineId, $message);

            echo json_encode([
                'success' => true,
                'message' => 'Explanation sent to member.'
            ]);
            break;

        case 'list':
        default:
            $statusFilter = $_GET['status'] ?? 'all';
            $validStatuses = ['pending', 'responded', 'acknowledged', 'all'];
            if (!in_array($statusFilter, $validStatuses, true)) {
                $statusFilter = 'all';
            }

            $query = "
                SELECT 
                    tdn.id,
                    tdn.task_assignment_id,
                    tdn.prod_line_id,
                    CONCAT('PL', LPAD(tdn.prod_line_id, 4, '0')) AS production_code,
                    pl.product_name,
                    um.fullname AS member_name,
                    um.member_role,
                    tdn.member_reason,
                    tdn.admin_message,
                    tdn.status,
                    tdn.declined_at,
                    tdn.admin_message_at
                FROM task_decline_notifications tdn
                LEFT JOIN production_line pl ON tdn.prod_line_id = pl.prod_line_id
                LEFT JOIN user_member um ON tdn.member_id = um.id
            ";

            if ($statusFilter !== 'all') {
                $query .= " WHERE tdn.status = ? ";
            }

            $query .= " ORDER BY tdn.declined_at DESC LIMIT 50";

            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare decline query: ' . $conn->error);
            }

            if ($statusFilter !== 'all') {
                $stmt->bind_param('s', $statusFilter);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $declines = [];
            while ($row = $result->fetch_assoc()) {
                $declines[] = $row;
            }
            $stmt->close();

            echo json_encode([
                'success' => true,
                'declines' => $declines,
                'count' => count($declines)
            ]);
            break;
            $stmt->close();

            echo json_encode([
                'success' => true,
                'declines' => $declines
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

