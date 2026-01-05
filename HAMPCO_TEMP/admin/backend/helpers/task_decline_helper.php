<?php

/**
 * Helper utilities for managing task decline notifications between members and admins.
 */

/**
 * Ensure the task_decline_notifications table exists.
 */
function ensureTaskDeclineTable(mysqli $conn): void {
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS task_decline_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            task_assignment_id INT NOT NULL,
            prod_line_id INT NOT NULL,
            member_id INT NOT NULL,
            member_reason TEXT NULL,
            admin_message TEXT NULL,
            status ENUM('pending','responded','acknowledged') NOT NULL DEFAULT 'pending',
            declined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            admin_message_at TIMESTAMP NULL,
            member_ack_at TIMESTAMP NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_decline_task FOREIGN KEY (task_assignment_id) REFERENCES task_assignments(id) ON DELETE CASCADE,
            CONSTRAINT fk_decline_prod FOREIGN KEY (prod_line_id) REFERENCES production_line(prod_line_id) ON DELETE CASCADE,
            CONSTRAINT fk_decline_member FOREIGN KEY (member_id) REFERENCES user_member(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    if (!$conn->query($createTableSql)) {
        throw new Exception('Failed to ensure task_decline_notifications table exists: ' . $conn->error);
    }
}

/**
 * Create or refresh a decline notification entry whenever a member declines an assigned task.
 */
function logTaskDecline(mysqli $conn, int $taskAssignmentId, int $prodLineId, int $memberId, ?string $memberReason = null): void {
    ensureTaskDeclineTable($conn);

    $memberReason = $memberReason !== null ? trim($memberReason) : null;

    $checkStmt = $conn->prepare("SELECT id FROM task_decline_notifications WHERE task_assignment_id = ?");
    if (!$checkStmt) {
        throw new Exception('Failed to prepare decline lookup: ' . $conn->error);
    }
    $checkStmt->bind_param('i', $taskAssignmentId);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($existing) {
        $updateStmt = $conn->prepare("
            UPDATE task_decline_notifications
            SET member_reason = ?, status = 'pending', declined_at = NOW(),
                admin_message = NULL, admin_message_at = NULL, member_ack_at = NULL
            WHERE id = ?
        ");
        if (!$updateStmt) {
            throw new Exception('Failed to prepare decline update: ' . $conn->error);
        }
        $updateStmt->bind_param('si', $memberReason, $existing['id']);
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update decline notification: ' . $updateStmt->error);
        }
        $updateStmt->close();
    } else {
        $insertStmt = $conn->prepare("
            INSERT INTO task_decline_notifications (task_assignment_id, prod_line_id, member_id, member_reason)
            VALUES (?, ?, ?, ?)
        ");
        if (!$insertStmt) {
            throw new Exception('Failed to prepare decline insert: ' . $conn->error);
        }
        $insertStmt->bind_param('iiis', $taskAssignmentId, $prodLineId, $memberId, $memberReason);
        if (!$insertStmt->execute()) {
            throw new Exception('Failed to insert decline notification: ' . $insertStmt->error);
        }
        $insertStmt->close();
    }
}

/**
 * Persist an admin response/explanation for a declined assignment.
 */
function saveDeclineAdminMessage(mysqli $conn, int $declineId, string $message): void {
    ensureTaskDeclineTable($conn);

    $message = trim($message);
    $stmt = $conn->prepare("
        UPDATE task_decline_notifications
        SET admin_message = ?, admin_message_at = NOW(), status = 'responded'
        WHERE id = ?
    ");
    if (!$stmt) {
        throw new Exception('Failed to prepare admin message update: ' . $conn->error);
    }
    $stmt->bind_param('si', $message, $declineId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to save admin message: ' . $stmt->error);
    }
    if ($stmt->affected_rows === 0) {
        throw new Exception('Decline notification not found.');
    }
    $stmt->close();
}

/**
 * Mark a decline record as acknowledged by the member.
 */
function acknowledgeDeclineMessage(mysqli $conn, int $declineId, int $memberId): void {
    ensureTaskDeclineTable($conn);

    $stmt = $conn->prepare("
        UPDATE task_decline_notifications
        SET status = 'acknowledged', member_ack_at = NOW()
        WHERE id = ? AND member_id = ?
    ");
    if (!$stmt) {
        throw new Exception('Failed to prepare acknowledgement update: ' . $conn->error);
    }
    $stmt->bind_param('ii', $declineId, $memberId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to acknowledge message: ' . $stmt->error);
    }
    if ($stmt->affected_rows === 0) {
        throw new Exception('Decline message not found or already acknowledged.');
    }
    $stmt->close();
}
