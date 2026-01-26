<?php
session_start();
header('Content-Type: application/json');
require_once '../dbconnect.php';
require_once '../class.php';

$db = new global_class();
$conn = $db->conn;

$member_id = $_SESSION['id'] ?? 0;

if ($member_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Member not logged in.']);
    exit();
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'mark-read':
        $notification_id = $input['notification_id'] ?? null;
        $type = $input['type'] ?? null;

        if (!$notification_id || !$type) {
            echo json_encode(['success' => false, 'message' => 'Missing notification_id or type.']);
            exit();
        }

        try {
            switch ($type) {
                case 'assigned_task':
                    echo json_encode(['success' => true, 'message' => 'Assigned task acknowledged.']);
                    exit();

                case 'task_approval':
                    $sql = "UPDATE task_approval_requests SET is_read_by_member = 1 WHERE id = ? AND member_id = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
                    $stmt->bind_param("ii", $notification_id, $member_id);
                    break;

                case 'admin_message':
                    $sql = "INSERT INTO admin_message_read_status (message_id, member_id, is_read) VALUES (?, ?, 1)
                            ON DUPLICATE KEY UPDATE is_read = 1";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
                    $stmt->bind_param("ii", $notification_id, $member_id);
                    break;

                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid notification type.']);
                    exit();
            }

            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Notification not found or already marked as read.']);
            }
            $stmt->close();

        } catch (Exception $e) {
            error_log("Error in 'mark-read' for member_id $member_id: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while marking the notification as read.']);
        }
        break;

    case 'mark-all-read':
        try {
            $conn->begin_transaction();
            
            $sql_approvals = "UPDATE task_approval_requests SET is_read_by_member = 1 WHERE member_id = ? AND is_read_by_member = 0";
            $stmt_approvals = $conn->prepare($sql_approvals);
            if (!$stmt_approvals) throw new Exception("Prepare failed for approvals: " . $conn->error);
            $stmt_approvals->bind_param("i", $member_id);
            $stmt_approvals->execute();
            $stmt_approvals->close();

            $sql_find_messages = "SELECT id FROM admin_messages WHERE id NOT IN 
                                  (SELECT message_id FROM admin_message_read_status WHERE member_id = ?)";
            $stmt_find = $conn->prepare($sql_find_messages);
            if (!$stmt_find) throw new Exception("Prepare failed for finding messages: " . $conn->error);
            $stmt_find->bind_param("i", $member_id);
            $stmt_find->execute();
            $result = $stmt_find->get_result();
            $unread_message_ids = [];
            while ($row = $result->fetch_assoc()) {
                $unread_message_ids[] = $row['id'];
            }
            $stmt_find->close();

            if (!empty($unread_message_ids)) {
                $sql_mark_messages = "INSERT INTO admin_message_read_status (message_id, member_id, is_read) VALUES ";
                $params = [];
                $types = "";
                foreach ($unread_message_ids as $msg_id) {
                    $sql_mark_messages .= "(?, ?, 1),";
                    $params[] = $msg_id;
                    $params[] = $member_id;
                    $types .= "ii";
                }
                $sql_mark_messages = rtrim($sql_mark_messages, ',') . " ON DUPLICATE KEY UPDATE is_read = 1";
                
                $stmt_mark = $conn->prepare($sql_mark_messages);
                if (!$stmt_mark) throw new Exception("Prepare failed for marking messages: " . $conn->error);
                $stmt_mark->bind_param($types, ...$params);
                $stmt_mark->execute();
                $stmt_mark->close();
            }
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read.']);

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error in 'mark-all-read' for member_id $member_id: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while marking all notifications as read.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>


if ($member_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Member not logged in.']);
    exit();
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'mark-read':
        $notification_id = $input['notification_id'] ?? null;
        $type = $input['type'] ?? null;

        if (!$notification_id || !$type) {
            echo json_encode(['success' => false, 'message' => 'Missing notification_id or type.']);
            exit();
        }

        try {
            switch ($type) {
                case 'assigned_task':
                    // Assigned tasks do not have a separate read status in this implementation.
                    // They are considered "acknowledged" when the member starts working on them.
                    echo json_encode(['success' => true, 'message' => 'Assigned task acknowledged.']);
                    exit();

                case 'task_approval':
                    $sql = "UPDATE task_approval_requests SET is_read_by_member = 1 WHERE id = ? AND member_id = ?";
                    $stmt = $GLOBALS['conn']->prepare($sql);
                    if (!$stmt) throw new Exception("Prepare failed: " . $GLOBALS['conn']->error);
                    $stmt->bind_param("ii", $notification_id, $member_id);
                    break;

                case 'admin_message':
                    // Insert a record into the read status table to mark it as read for the specific member.
                    $sql = "INSERT INTO admin_message_read_status (message_id, member_id, is_read) VALUES (?, ?, 1)
                            ON DUPLICATE KEY UPDATE is_read = 1";
                    $stmt = $GLOBALS['conn']->prepare($sql);
                    if (!$stmt) throw new Exception("Prepare failed: " . $GLOBALS['conn']->error);
                    $stmt->bind_param("ii", $notification_id, $member_id);
                    break;

                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid notification type.']);
                    exit();
            }

            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Notification not found or already marked as read.']);
            }
            $stmt->close();

        } catch (Exception $e) {
            error_log("Error in 'mark-read' for member_id $member_id: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while marking the notification as read.']);
        }
        break;

    case 'mark-all-read':
        try {
            $GLOBALS['conn']->begin_transaction();
            
            // Mark all task approval notifications as read for the member
            $sql_approvals = "UPDATE task_approval_requests SET is_read_by_member = 1 WHERE member_id = ? AND is_read_by_member = 0";
            $stmt_approvals = $GLOBALS['conn']->prepare($sql_approvals);
            if (!$stmt_approvals) throw new Exception("Prepare failed for approvals: " . $GLOBALS['conn']->error);
            $stmt_approvals->bind_param("i", $member_id);
            $stmt_approvals->execute();
            $stmt_approvals->close();

            // Find all unread admin messages for the member
            $sql_find_messages = "SELECT id FROM admin_messages WHERE id NOT IN 
                                  (SELECT message_id FROM admin_message_read_status WHERE member_id = ?)";
            $stmt_find = $GLOBALS['conn']->prepare($sql_find_messages);
            if (!$stmt_find) throw new Exception("Prepare failed for finding messages: " . $GLOBALS['conn']->error);
            $stmt_find->bind_param("i", $member_id);
            $stmt_find->execute();
            $result = $stmt_find->get_result();
            $unread_message_ids = [];
            while ($row = $result->fetch_assoc()) {
                $unread_message_ids[] = $row['id'];
            }
            $stmt_find->close();

            // If there are unread messages, mark them all as read
            if (!empty($unread_message_ids)) {
                $sql_mark_messages = "INSERT INTO admin_message_read_status (message_id, member_id, is_read) VALUES ";
                $params = [];
                $types = "";
                foreach ($unread_message_ids as $msg_id) {
                    $sql_mark_messages .= "(?, ?, 1),";
                    $params[] = $msg_id;
                    $params[] = $member_id;
                    $types .= "ii";
                }
                // Remove trailing comma and add ON DUPLICATE KEY UPDATE
                $sql_mark_messages = rtrim($sql_mark_messages, ',') . " ON DUPLICATE KEY UPDATE is_read = 1";
                
                $stmt_mark = $GLOBALS['conn']->prepare($sql_mark_messages);
                if (!$stmt_mark) throw new Exception("Prepare failed for marking messages: " . $GLOBALS['conn']->error);
                $stmt_mark->bind_param($types, ...$params);
                $stmt_mark->execute();
                $stmt_mark->close();
            }
            
            $GLOBALS['conn']->commit();
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read.']);

        } catch (Exception $e) {
            $GLOBALS['conn']->rollback();
            error_log("Error in 'mark-all-read' for member_id $member_id: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while marking all notifications as read.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>
