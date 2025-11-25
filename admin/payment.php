<?php
require_once "components/header.php";
?>

    <h1 class="text-3xl font-bold text-gray-800 mb-8">Payment Management</h1>

    <!-- Search and Filter Section -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                    placeholder="Search members..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    id="memberSearch">
            </div>
            <div class="flex gap-4">
                <select name="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Paid">Paid</option>
                    <option value="Adjusted">Adjusted</option>
                </select>
                <select name="role-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Roles</option>
                    <option value="knotter">Knotter</option>
                    <option value="warper">Warper</option>
                    <option value="weaver">Weaver</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-600 text-sm">Total Payments</h2>
                    <p id="totalPayments" class="text-2xl font-semibold text-gray-800">₱0.00</p>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-600 text-sm">Pending Payments</h2>
                    <p id="pendingPayments" class="text-2xl font-semibold text-gray-800">₱0.00</p>
                </div>
            </div>
        </div>

        <!-- Completed Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-600 text-sm">Completed Payments</h2>
                    <p id="completedPayments" class="text-2xl font-semibold text-gray-800">₱0.00</p>
                </div>
            </div>
        </div>

        <!-- Total Members -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-600 text-sm">Total Members</h2>
                    <p id="totalMembers" class="text-2xl font-semibold text-gray-800">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Records -->
    <div class="rounded-xl shadow overflow-hidden bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-6 py-4 border-b border-gray-100">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400">Records</p>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Payment Activity</h2>
            </div>
            <span id="paymentResultCount" class="text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                0 results
            </span>
        </div>
        <div id="paymentRecordsList" class="divide-y divide-gray-100">
            <div class="py-8 text-center text-sm text-gray-500">
                Loading payment records...
            </div>
        </div>
    </div>


<!-- Include SweetAlert2 for better alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Include the payment records JavaScript -->
<script src="assets/js/payment-records.js"></script>

<?php
require_once "components/footer.php";
?>