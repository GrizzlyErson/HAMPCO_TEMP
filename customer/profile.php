<?php
include "component/header.php";

// Handle profile update
$update_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = mysqli_real_escape_string($db->conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($db->conn, $_POST['email']);
    $phone = mysqli_real_escape_string($db->conn, $_POST['phone']);
    $address = mysqli_real_escape_string($db->conn, $_POST['address']);
    
    $update_query = "UPDATE user_customer SET 
                     customer_fullname = '$fullname',
                     customer_email = '$email',
                     customer_phone = '$phone',
                     customer_address = '$address'
                     WHERE customer_id = $customer_id";
    
    if ($db->conn->query($update_query)) {
        $update_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Profile updated successfully!</div>';
        // Refresh user info
        $fetch_user_info = $db->fetch_user_info($customer_id);
        foreach ($fetch_user_info as $user):
            $user_fullname = $user['customer_fullname'];
            $user_email = $user['customer_email'];
            $user_phone = $user['customer_phone'];
            $user_address = $user['customer_address'] ?? '';
        endforeach;
    } else {
        $update_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Error updating profile. Please try again.</div>';
    }
}

// Fetch current user information
$fetch_user_info = $db->fetch_user_info($customer_id);
$user_data = [];
foreach ($fetch_user_info as $user):
    $user_data = $user;
    $user_fullname = $user['customer_fullname'];
    $user_email = $user['customer_email'];
    $user_phone = $user['customer_phone'];
    $user_address = $user['customer_address'] ?? '';
    $user_created = $user['created_at'] ?? 'N/A';
endforeach;
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <?php echo $update_message; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <!-- Profile Avatar -->
                    <div class="mb-4">
                        <div class="w-24 h-24 mx-auto bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- User Name -->
                    <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo ucfirst($user_fullname); ?></h2>
                    <p class="text-gray-600 mb-4">Customer Account</p>
                    
                    <!-- Quick Info -->
                    <div class="space-y-3 text-left">
                        <div class="bg-gray-50 p-3 rounded">
                            <p class="text-xs text-gray-600">Member Since</p>
                            <p class="text-sm font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($user_created)); ?></p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <p class="text-xs text-gray-600">Account Status</p>
                            <p class="text-sm font-semibold text-green-600">Active</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Edit Profile Information</h3>
                    
                    <form method="POST" action="" class="space-y-6">
                        <!-- Full Name -->
                        <div>
                            <label for="fullname" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user_fullname); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                   required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                   required>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                   required>
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">Delivery Address</label>
                            <textarea id="address" name="address" rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                      placeholder="Enter your complete delivery address"><?php echo htmlspecialchars($user_address); ?></textarea>
                        </div>

                        <!-- Update Button -->
                        <div class="flex gap-4">
                            <button type="submit" name="update_profile" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-semibold transition">
                                <span class="material-icons align-middle mr-2 inline-block" style="font-size: 20px;">save</span>
                                Save Changes
                            </button>
                            <a href="customer_home_page.php" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white py-3 px-4 rounded-lg font-semibold transition text-center">
                                <span class="material-icons align-middle mr-2 inline-block" style="font-size: 20px;">close</span>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <!-- Total Orders -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php
                            $order_query = "SELECT COUNT(*) as count FROM orders WHERE order_user_id = $customer_id";
                            $order_result = $db->conn->query($order_query);
                            $order_count = $order_result->fetch_assoc()['count'];
                            echo $order_count;
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Spent -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-3 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Total Spent</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php
                            $spent_query = "SELECT SUM(total_amount) as total FROM orders WHERE order_user_id = $customer_id";
                            $spent_result = $db->conn->query($spent_query);
                            $spent_row = $spent_result->fetch_assoc();
                            $total_spent = $spent_row['total'] ?? 0;
                            echo 'PHP ' . number_format($total_spent, 2);
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Account Level -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Account Level</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php
                            if ($total_spent >= 50000) {
                                echo 'Gold';
                            } elseif ($total_spent >= 20000) {
                                echo 'Silver';
                            } elseif ($total_spent >= 5000) {
                                echo 'Bronze';
                            } else {
                                echo 'Standard';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="checkout_history.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition">
                    <span class="material-icons text-green-600 mr-3">history</span>
                    <div>
                        <p class="font-semibold text-gray-800">View Checkout History</p>
                        <p class="text-sm text-gray-600">See all your past purchases</p>
                    </div>
                </a>
                <a href="orders.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition">
                    <span class="material-icons text-green-600 mr-3">shopping_cart</span>
                    <div>
                        <p class="font-semibold text-gray-800">My Purchases</p>
                        <p class="text-sm text-gray-600">Track your orders</p>
                    </div>
                </a>
                <a href="password_setting.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition">
                    <span class="material-icons text-green-600 mr-3">lock</span>
                    <div>
                        <p class="font-semibold text-gray-800">Change Password</p>
                        <p class="text-sm text-gray-600">Update your security</p>
                    </div>
                </a>
                <a href="customer_home_page.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition">
                    <span class="material-icons text-green-600 mr-3">shopping_bag</span>
                    <div>
                        <p class="font-semibold text-gray-800">Continue Shopping</p>
                        <p class="text-sm text-gray-600">Explore more products</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include "component/footer.php"; ?>
