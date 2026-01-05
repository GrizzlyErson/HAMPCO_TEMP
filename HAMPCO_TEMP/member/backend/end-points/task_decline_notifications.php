<?php
session_start();
header('Content-Type: application/json');

require_once "../dbconnect.php";
require_once dirname(__DIR__, 3) . "/admin/backend/helpers/task_decline_helper.php";

try {
    if (!isset($_SESSION['id']) || ($_SESSION['user_type'] ?? '') !== 'member') {
        throw new Exception('Unauthorized', 401);
    }

    $memberId = intval($_SESSION['id']);

    $db = new db_connect();
    $db->connect();
    $conn = $db->conn;

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    ensureTaskDeclineTable($conn);

    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

    switch ($action) {
        case 'count':
            $countStmt = $conn->prepare("
                SELECT COUNT(*) AS unread
                FROM task_decline_notifications
                WHERE member_id = ? AND status = 'responded'
            ");
            $countStmt->bind_param('i', $memberId);
            $countStmt->execute();
            $count = $countStmt->get_result()->fetch_assoc();
            $countStmt->close();

            echo json_encode([
                'success' => true,
                'unread' => intval($count['unread'] ?? 0)
            ]);
            break;

        case 'acknowledge':
            $declineId = intval($_POST['decline_id'] ?? 0);
            if ($declineId <= 0) {
                throw new Exception('Decline ID is required');
            }

            acknowledgeDeclineMessage($conn, $declineId, $memberId);

            echo json_encode([
                'success' => true,
                'message' => 'Message marked as read.'
            ]);
            break;

        case 'list':
        default:
            $stmt = $conn->prepare("
                SELECT 
                    tdn.id,
                    tdn.prod_line_id,
                    CONCAT('PL', LPAD(tdn.prod_line_id, 4, '0')) AS production_code,
                    pl.product_name,
                    tdn.member_reason,
                    tdn.admin_message,
                    tdn.status,
                    tdn.declined_at,
                    tdn.admin_message_at
                FROM task_decline_notifications tdn
                JOIN production_line pl ON tdn.prod_line_id = pl.prod_line_id
                WHERE tdn.member_id = ?
                ORDER BY 
                    CASE WHEN tdn.status = 'responded' THEN 0 ELSE 1 END,
                    tdn.admin_message_at DESC,
                    tdn.declined_at DESC
                LIMIT 50
            ");
            $stmt->bind_param('i', $memberId);
            $stmt->execute();
            $result = $stmt->get_result();
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            $stmt->close();

            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            break;
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

