<?php 
include "components/header.php";

// Get current user data
$user_id = $On_Session[0]['id'];
$current_name = $On_Session[0]['fullname'] ?? '';
$current_email = $On_Session[0]['member_email'] ?? '';
$current_role = $On_Session[0]['role'] ?? '';
$current_phone = $On_Session[0]['member_phone'] ?? '';
?>
<body class="hampco-admin-sidebar-layout">
    <!-- Top bar with user profile -->
    <div class="max-w-6xl mx-auto flex justify-between items-center bg-white p-4 mb-6 rounded-md shadow-md">
        <h2 class="text-lg font-semibold text-gray-700">Account Settings</h2>
        <div class="flex items-center space-x-3">
            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($current_name); ?></span>
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                <?php echo strtoupper(substr($current_name, 0, 1)); ?>
            </div>
        </div>
    </div>

    <?php if($On_Session[0]['status'] == 1): ?>
    <!-- Settings Container -->
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Sidebar Navigation -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="font-semibold text-gray-800 mb-4">Settings</h3>
            <nav class="space-y-2">
                <button class="settings-tab w-full text-left px-4 py-2 rounded-lg text-white font-medium" style="background-color: #D4AF37;" data-tab="profile">
                    👤 Profile Information
                </button>
                <button class="settings-tab w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700" data-tab="password">
                    🔐 Change Password
                </button>
                <button class="settings-tab w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700" data-tab="contact">
                    📞 Contact Information
                </button>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- Profile Information Tab -->
            <div id="profile-tab" class="settings-content bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Profile Information</h3>
                
                <form id="profileForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($current_name); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select id="role" name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="">Select Role</option>
                                <option value="knotter" <?php echo $current_role === 'knotter' ? 'selected' : ''; ?>>Knotter</option>
                                <option value="warper" <?php echo $current_role === 'warper' ? 'selected' : ''; ?>>Warper</option>
                                <option value="weaver" <?php echo $current_role === 'weaver' ? 'selected' : ''; ?>>Weaver</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                   disabled>
                            <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50" id="cancelProfile">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 text-white rounded-lg hover:opacity-90" style="background-color: #D4AF37;">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Tab -->
            <div id="password-tab" class="settings-content hidden bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Change Password</h3>
                
                <form id="passwordForm" class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" id="currentPassword" name="currentPassword" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required minlength="6">
                        <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                    </div>

                    <div id="passwordStrength" class="space-y-2">
                        <p class="text-xs text-gray-600">Password Strength:</p>
                        <div class="flex space-x-1">
                            <div class="flex-1 h-1 bg-gray-300 rounded"></div>
                            <div class="flex-1 h-1 bg-gray-300 rounded"></div>
                            <div class="flex-1 h-1 bg-gray-300 rounded"></div>
                            <div class="flex-1 h-1 bg-gray-300 rounded"></div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50" id="cancelPassword">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 text-white rounded-lg hover:opacity-90" style="background-color: #D4AF37;">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Contact Information Tab -->
            <div id="contact-tab" class="settings-content hidden bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Contact Information</h3>
                
                <form id="contactForm" class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($current_phone); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="+63 (123) 456-7890"
                               required>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50" id="cancelContact">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 text-white rounded-lg hover:opacity-90" style="background-color: #D4AF37;">
                            Update Contact
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <?php else: ?>
    <div class="w-full flex items-center p-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded-2xl shadow-lg">
        <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png" alt="Warning Icon" class="w-12 h-12 mr-4">
        <div>
            <p class="font-bold text-xl mb-1">Account Not Verified</p>
            <p class="text-base">Please wait for Administrator Verification.</p>
        </div>
    </div>
    <?php endif; ?>

<?php include "components/footer.php"; ?>
<script src="js/settings.js"></script>
</body>
</html>