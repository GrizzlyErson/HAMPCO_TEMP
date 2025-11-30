<?php
include "component/header.php";

// Check if the user is logged in, redirect to login if not
if (!isset($_SESSION['customer_id'])) {
    header('location: ../login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Change Password</h2>

        <form id="changePasswordForm" class="space-y-6">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" id="current_password" name="current_password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="new_password" name="new_password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                <p id="newPasswordError" class="text-sm text-red-600 mt-1 hidden">Password must be at least 6 characters long.</p>
            </div>

            <div>
                <label for="confirm_new_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                <p id="confirmPasswordError" class="text-sm text-red-600 mt-1 hidden">New password and confirmation do not match.</p>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Change Password
                </button>
            </div>
        </form>

        <div id="responseMessage" class="mt-4 text-center text-sm"></div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();

        const currentPassword = $('#current_password').val();
        const newPassword = $('#new_password').val();
        const confirmNewPassword = $('#confirm_new_password').val();
        let isValid = true;

        // Reset error messages
        $('#newPasswordError').addClass('hidden');
        $('#confirmPasswordError').addClass('hidden');
        $('#responseMessage').removeClass('text-green-600 text-red-600').html('');

        // Basic validation
        if (newPassword.length < 6) {
            $('#newPasswordError').removeClass('hidden');
            isValid = false;
        }
        if (newPassword !== confirmNewPassword) {
            $('#confirmPasswordError').removeClass('hidden');
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        // Send data to backend
        $.ajax({
            url: 'backend/end-points/update_password.php',
            type: 'POST',
            data: {
                current_password: currentPassword,
                new_password: newPassword,
                confirm_new_password: confirmNewPassword
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#responseMessage').addClass('text-green-600').html(response.message);
                    // Clear form fields on success
                    $('#changePasswordForm')[0].reset();
                } else {
                    $('#responseMessage').addClass('text-red-600').html(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error, xhr.responseText);
                $('#responseMessage').addClass('text-red-600').html('An unexpected error occurred. Please try again.');
            }
        });
    });
});
</script>

<?php
include "component/footer.php";
?>