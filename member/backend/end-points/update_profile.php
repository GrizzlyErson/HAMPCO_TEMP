<?php
session_start();
require_once '../dbconnect.php';
require_once '../class.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['id'])) {
        $response['message'] = 'User not authenticated.';
        echo json_encode($response);
        exit;
    }

    $userId = $_SESSION['id'];
    $db = new global_class();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_profile':
            $fullname = $_POST['fullname'] ?? '';
            $role = $_POST['role'] ?? '';

            $nameParts = explode(' ', $fullname, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $query = "UPDATE user_member SET first_name = ?, last_name = ?, role = ? WHERE id = ?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('sssi', $firstName, $lastName, $role, $userId);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Profile updated successfully.';
            } else {
                $response['message'] = 'Error updating profile: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'update_contact':
            $phone = $_POST['phone'] ?? '';

            $query = "UPDATE user_member SET contact_number = ? WHERE id = ?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('si', $phone, $userId);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Contact information updated successfully.';
            } else {
                $response['message'] = 'Error updating contact information: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'update_password':
            $currentPassword = $_POST['currentPassword'] ?? '';
            $newPassword = $_POST['newPassword'] ?? '';

            $query = "SELECT password FROM user_member WHERE id = ?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $query = "UPDATE user_member SET password = ? WHERE id = ?";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('si', $hashedPassword, $userId);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Password updated successfully.';
                } else {
                    $response['message'] = 'Error updating password: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Incorrect current password.';
            }
            break;

        default:
            $response['message'] = 'Invalid action.';
            break;
    }

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
