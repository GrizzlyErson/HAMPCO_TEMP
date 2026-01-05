<?php
/**
 * Update Member Profile
 * Handles profile information, password, and contact updates
 */

header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

require_once '../backend/class.php';
$db = new global_class();

try {
    $action = $_POST['action'] ?? null;
    $user_id = intval($_SESSION['id']);

    if (!$action) {
        throw new Exception('Action parameter is required');
    }

    switch ($action) {
        case 'update_profile':
            // Update fullname and role
            $fullname = trim($_POST['fullname'] ?? '');
            $role = trim($_POST['role'] ?? '');

            if (empty($fullname)) {
                throw new Exception('Full name is required');
            }

            if (empty($role)) {
                throw new Exception('Role is required');
            }

            // Validate role
            $valid_roles = ['knotter', 'warper', 'weaver'];
            if (!in_array($role, $valid_roles)) {
                throw new Exception('Invalid role selected');
            }

            // Update user_member table
            $update_query = "UPDATE user_member SET fullname = ?, role = ? WHERE id = ?";
            $stmt = mysqli_prepare($db->conn, $update_query);
            
            if (!$stmt) {
                throw new Exception('Database error: ' . mysqli_error($db->conn));
            }

            mysqli_stmt_bind_param($stmt, 'ssi', $fullname, $role, $user_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to update profile: ' . mysqli_error($db->conn));
            }

            // Update session
            $_SESSION['fullname'] = $fullname;
            $_SESSION['role'] = $role;

            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
            break;

        case 'update_password':
            // Update password
            $current_password = $_POST['currentPassword'] ?? '';
            $new_password = $_POST['newPassword'] ?? '';

            if (empty($current_password) || empty($new_password)) {
                throw new Exception('Current and new passwords are required');
            }

            if (strlen($new_password) < 6) {
                throw new Exception('New password must be at least 6 characters');
            }

            // Get current password from database
            $get_query = "SELECT password FROM user_member WHERE id = ?";
            $stmt = mysqli_prepare($db->conn, $get_query);
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            if (!$row) {
                throw new Exception('User not found');
            }

            // Verify current password
            if (!password_verify($current_password, $row['password'])) {
                throw new Exception('Current password is incorrect');
            }

            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password
            $update_query = "UPDATE user_member SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($db->conn, $update_query);
            
            if (!$stmt) {
                throw new Exception('Database error: ' . mysqli_error($db->conn));
            }

            mysqli_stmt_bind_param($stmt, 'si', $hashed_password, $user_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to update password: ' . mysqli_error($db->conn));
            }

            echo json_encode([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
            break;

        case 'update_contact':
            // Update contact information
            $phone = trim($_POST['phone'] ?? '');

            if (empty($phone)) {
                throw new Exception('Phone number is required');
            }

            // Validate phone format (basic validation)
            if (strlen($phone) < 10) {
                throw new Exception('Invalid phone number format');
            }

            // Update user_member table
            $update_query = "UPDATE user_member SET member_phone = ? WHERE id = ?";
            $stmt = mysqli_prepare($db->conn, $update_query);
            
            if (!$stmt) {
                throw new Exception('Database error: ' . mysqli_error($db->conn));
            }

            mysqli_stmt_bind_param($stmt, 'si', $phone, $user_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to update contact information: ' . mysqli_error($db->conn));
            }

            echo json_encode([
                'success' => true,
                'message' => 'Contact information updated successfully'
            ]);
            break;

        default:
            throw new Exception('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}