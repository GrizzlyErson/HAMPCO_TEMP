<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../class.php';
$db = new global_class();

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['customer_id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit();
}

$customer_id = $_SESSION['customer_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// Server-side validation
if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit();
}

if ($new_password !== $confirm_new_password) {
    $response['message'] = 'New password and confirmation do not match.';
    echo json_encode($response);
    exit();
}

if (strlen($new_password) < 6) {
    $response['message'] = 'New password must be at least 6 characters long.';
    echo json_encode($response);
    exit();
}

try {
    // Fetch current hashed password from the database
    $stmt = $db->conn->prepare("SELECT customer_password FROM customer WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $response['message'] = 'User not found.';
        echo json_encode($response);
        exit();
    }

    $hashed_current_password_db = $user['customer_password'];

    // Verify current password
    if (!password_verify($current_password, $hashed_current_password_db)) {
        $response['message'] = 'Incorrect current password.';
        echo json_encode($response);
        exit();
    }

    // Hash the new password
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in the database using the global_class method
    if ($db->updateCustomerPassword($customer_id, $hashed_new_password)) {
        $response['success'] = true;
        $response['message'] = 'Password updated successfully.';
    } else {
        $response['message'] = 'Password update failed. No changes made or an error occurred.';
    }

} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

echo json_encode($response);
?>