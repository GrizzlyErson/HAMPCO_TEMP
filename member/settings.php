<?php
include 'components/header.php';

$user_id = $_SESSION['id'];
$fullname = $On_Session[0]['fullname'];
$contact_info = $On_Session[0]['phone']; // Using 'phone' as the column name in user_member table

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_fullname = $_POST['fullname'] ?? '';
    $new_contact_info = $_POST['contact_info'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';
    $task_limit = isset($_POST['task_limit']) ? intval($_POST['task_limit']) : 10;

    // Basic validation
    if (empty($new_fullname) || empty($new_contact_info)) {
        $message = 'Full name and contact information cannot be empty.';
        $message_type = 'error';
    } else {
        $password_update = false;
        if (!empty($new_password)) {
            // Validate current password before allowing new password set
            // Assuming the `check_account` method doesn't return password directly,
            // we might need another method or an adjusted check.
            // For now, let's assume we can get the password hash for verification.
            // A more secure way would be to have a dedicated login-like verification for current password.

            // To verify current password, we need to fetch the user's current hashed password
            $stmt = $db->conn->prepare("SELECT password FROM user_member WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_new_password) {
                    $password_update = true;
                } else {
                    $message = 'New password and confirm new password do not match.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Incorrect current password.';
                $message_type = 'error';
            }
        }

        if ($message_type === '') { // Only proceed if no previous errors
            $update_success = false;
            $update_result = false; // Initialize to avoid undefined variable warning
            if ($password_update) {
                $update_result = $db->updateMemberDetails($user_id, $new_fullname, $new_contact_info, $new_password);
            } else {
                $update_result = $db->updateMemberDetails($user_id, $new_fullname, $new_contact_info);
            }

            if ($update_result['success']) {
                // Update task limit
                $stmt = $db->conn->prepare("UPDATE user_member SET task_limit = ? WHERE id = ?");
                $stmt->bind_param("ii", $task_limit, $user_id);
                $stmt->execute();
                $stmt->close();

                // Update session data to reflect changes immediately
                // This might not be strictly necessary if navbar.php re-fetches from On_Session,
                // but good practice if fullname is used elsewhere directly from $_SESSION.
                // $_SESSION['fullname'] = $new_fullname;

                // Re-fetch the On_Session array to get updated data for the current page load
                $On_Session = $db->check_account($user_id, 'member');

                $message = 'Profile updated successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to update profile. Please try again. Error: ' . ($update_result['error'] ?? 'Unknown database error.');
                $message_type = 'error';
            }
        }
    }
}
?>

<link rel="stylesheet" href="../admin/mobile-fix.css">
<style>
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }
    .form-group input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ccc;
        border-radius: 0.375rem;
        box-sizing: border-box;
    }
    .btn-primary {
        background-color: #4CAF50;
        color: white;
        padding: 0.75rem 1.25rem;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        font-size: 1rem;
    }
    .btn-primary:hover {
        background-color: #45a049;
    }
    .container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background-color: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
</style>
<body class="hampco-admin-sidebar-layout">
<div class="container mx-auto p-6 md:p-10 bg-white shadow-md rounded-lg mt-10">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">Settings</h2>

    <form method="POST" action="">
        <div class="form-group">
            <label for="fullname" class="block text-gray-700 text-sm font-bold mb-2">Full Name:</label>
            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($On_Session[0]['fullname'] ?? '') ?>" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="form-group">
            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number:</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($On_Session[0]['phone'] ?? '') ?>" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="form-group">
            <label for="task_limit" class="block text-gray-700 text-sm font-bold mb-2">Task Limit (Max active tasks):</label>
            <input type="number" id="task_limit" name="task_limit" value="<?= htmlspecialchars($On_Session[0]['task_limit'] ?? 10) ?>" min="1" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="form-group">
            <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Current Password (Required for password change):</label>
            <input type="password" id="current_password" name="current_password"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="form-group">
            <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password:</label>
            <input type="password" id="new_password" name="new_password"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="form-group">
            <label for="confirm_new_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password:</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit"
                    class="btn-primary w-full py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                Update Profile
            </button>
        </div>
    </form>
</div>

<?php if (!empty($message)): ?>
    <script>
        Swal.fire({
            icon: '<?= $message_type ?>',
            title: '<?= ucfirst($message_type) ?>',
            text: <?= json_encode($message) ?>,
        });
    </script>
<?php endif; ?>

<?php include 'components/footer.php'; ?>
