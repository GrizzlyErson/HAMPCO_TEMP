<?php
session_start();
header('Content-Type: application/json');
require_once '../../function/database.php';
require_once '../../backend/class.php';

$db = new Database();
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
            $table = '';
            $id_column = '';
            $member_id_column = 'member_id';

            switch ($type) {
                case 'assigned_task':
                    // This assumes that new assignments are treated as notifications and need to be marked as read.
                    // If assigned tasks are managed differently (e.g., through a status change on the task itself),
                    // this part might need adjustment based on the actual task management system.
                    // For now, we'll assume a dedicated 'member_assigned_task_notifications' table or similar
                    // that tracks if a member has 'read' an assignment notification.
                    // For simplicity, let's assume `get_assignments.php` is retrieving
                    // 'production_line_members' entries and we need a way to mark them as 'viewed' by the member.
                    // A proper notification system would involve a notifications table where each entry has a read status.
                    // Since there's no explicit table for `assigned_task_notifications`,
                    // I will skip marking assigned tasks as read for now, as it would require altering core task tables.
                    // If assigned tasks are to be explicitly marked as 'read' like other notifications,
                    // a new table `member_task_notifications` would be required, or adding a `read_status`
                    // column to `production_line_members` if that's where assignments are stored.
                    echo json_encode(['success' => true, 'message' => 'Assigned task notifications are not individually markable as read through this endpoint.']);
                    exit();
                case 'task_approval':
                    $table = 'task_approval_requests';
                    $id_column = 'id';
                    break;
                case 'admin_message':
                    $table = 'admin_messages';
                    $id_column = 'id';
                    $member_id_column = 'recipient_member_id'; // Assuming this column name
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid notification type.']);
                    exit();
            }

            $sql = "UPDATE $table SET read_status = 1 WHERE $id_column = ? AND $member_id_column = ?";
            $stmt = $db->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $db->conn->error);
            }
            $stmt->bind_param("ii", $notification_id, $member_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Notification not found or already read.']);
            }
            $stmt->close();

        } catch (Exception $e) {
            error_log("Error marking notification as read for member_id $member_id: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error marking notification as read: ' . $e->getMessage()]);
        }
        break;

    case 'mark-all-read':
        try {
            $updated_count = 0;

            // Mark task_approval_requests as read
            $sql = "UPDATE task_approval_requests SET read_status = 1 WHERE member_id = ? AND read_status = 0";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $updated_count += $stmt->affected_rows;
            $stmt->close();

            // Mark admin_messages as read
            $sql = "UPDATE admin_messages SET read_status = 1 WHERE recipient_member_id = ? AND read_status = 0";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $updated_count += $stmt->affected_rows;
            $stmt->close();

            // For assigned tasks, if a read_status column is implemented in production_line_members or a separate notification table.
            // For now, assigned tasks are not considered markable as 'read all' this way, similar to 'mark-read' logic.

            echo json_encode(['success' => true, 'message' => "$updated_count notifications marked as read."]);

        } catch (Exception $e) {
            error_log("Error marking all notifications as read for member_id $member_id: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error marking all notifications as read: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>