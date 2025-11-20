<?php include "components/header.php";?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Orders</h1>
    <!-- Notification Bell Icon -->
    <button class="relative focus:outline-none" title="Notifications">
        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Notification dot -->
        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full ring-2 ring-white bg-red-500 hidden"></span>
    </button>
</div>

<!-- Filter Bar -->
<div class="mb-4 flex gap-4 bg-white p-4 rounded-md shadow">
    <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="all">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="processing">Processing</option>
        <option value="shipped">Shipped</option>
        <option value="delivered">Delivered</option>
        <option value="cancelled">Cancelled</option>
    </select>
    
    <input type="text" id="searchInput" placeholder="Search by Order ID or Customer..." class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
    
    <button id="refreshBtn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
        Refresh
    </button>
</div>

<!-- Orders Table -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
    <table class="min-w-full table-auto" id="ordersTable">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Order ID</th>
                <th class="py-3 px-6 text-left">Customer</th>
                <th class="py-3 px-6 text-left">Email</th>
                <th class="py-3 px-6 text-left">Total Amount</th>
                <th class="py-3 px-6 text-left">Status</th>
                <th class="py-3 px-6 text-left">Payment Method</th>
                <th class="py-3 px-6 text-left">Date</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm" id="ordersTableBody">
            <tr>
                <td colspan="8" class="py-4 px-6 text-center text-gray-500">Loading orders...</td>
            </tr>
        </tbody>
    </table>
</div>

<?php include "components/footer.php";?>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Order Details</h2>
            <button id="closeOrderModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        <div id="orderDetailsContent" class="space-y-4">
            <!-- Order details will be loaded here -->
        </div>
        
        <div class="mt-6 flex justify-end gap-2">
            <button id="closeOrderBtn" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Close</button>
            <button id="updateStatusBtn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Update Status</button>
        </div>
    </div>
</div>

<script>
    let currentOrderId = null;
    let allOrders = [];

    // Load orders on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadOrders();
        
        // Event listeners
        document.getElementById('statusFilter').addEventListener('change', loadOrders);
        document.getElementById('searchInput').addEventListener('input', filterOrders);
        document.getElementById('refreshBtn').addEventListener('click', loadOrders);
        document.getElementById('closeOrderModal').addEventListener('click', closeOrderModal);
        document.getElementById('closeOrderBtn').addEventListener('click', closeOrderModal);
    });

    function loadOrders() {
        const status = document.getElementById('statusFilter').value;
        const url = 'backend/end-points/get_orders.php' + (status !== 'all' ? '?status=' + status : '');

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.orders) {
                    allOrders = data.orders;
                    displayOrders(allOrders);
                } else {
                    displayNoOrders();
                }
            })
            .catch(error => {
                console.error('Error loading orders:', error);
                displayNoOrders();
            });
    }

    function displayOrders(orders) {
        const tbody = document.getElementById('ordersTableBody');
        
        if (orders.length === 0) {
            displayNoOrders();
            return;
        }

        tbody.innerHTML = orders.map(order => `
            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                <td class="py-3 px-6">#${order.order_id || 'N/A'}</td>
                <td class="py-3 px-6">${order.customer_name || 'N/A'}</td>
                <td class="py-3 px-6">${order.customer_email || 'N/A'}</td>
                <td class="py-3 px-6">₱${parseFloat(order.total_amount || 0).toFixed(2)}</td>
                <td class="py-3 px-6">
                    <span class="status-badge ${getStatusColor(order.status)}">
                        ${order.status || 'pending'}
                    </span>
                </td>
                <td class="py-3 px-6">${order.payment_method || 'N/A'}</td>
                <td class="py-3 px-6">${formatDate(order.created_at)}</td>
                <td class="py-3 px-6 text-center">
                    <button onclick="viewOrderDetails('${order.order_id}')" class="text-blue-500 hover:text-blue-700 font-semibold text-sm mr-2">
                        View
                    </button>
                    <button onclick="deleteOrder('${order.order_id}')" class="text-red-500 hover:text-red-700 font-semibold text-sm">
                        Delete
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function displayNoOrders() {
        const tbody = document.getElementById('ordersTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="py-4 px-6 text-center text-gray-500">No orders found</td>
            </tr>
        `;
    }

    function filterOrders() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const filtered = allOrders.filter(order => {
            return (order.order_id && order.order_id.toString().includes(searchText)) ||
                   (order.customer_name && order.customer_name.toLowerCase().includes(searchText)) ||
                   (order.customer_email && order.customer_email.toLowerCase().includes(searchText));
        });
        displayOrders(filtered);
    }

    function viewOrderDetails(orderId) {
        currentOrderId = orderId;
        
        fetch('backend/end-points/get_order_details.php?order_id=' + orderId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.order) {
                    displayOrderDetails(data.order, data.items || []);
                    document.getElementById('orderDetailsModal').classList.remove('hidden');
                } else {
                    alert('Could not load order details');
                }
            })
            .catch(error => {
                console.error('Error loading order details:', error);
                alert('Error loading order details');
            });
    }

    function displayOrderDetails(order, items) {
        const content = document.getElementById('orderDetailsContent');
        
        let itemsHTML = '<h4 class="font-semibold">Items:</h4><ul class="list-disc pl-5">';
        if (items && items.length > 0) {
            itemsHTML += items.map(item => `
                <li>${item.product_name || 'Unknown'} x${item.quantity || 1} - ₱${parseFloat(item.price || 0).toFixed(2)}</li>
            `).join('');
        } else {
            itemsHTML += '<li>No items</li>';
        }
        itemsHTML += '</ul>';

        content.innerHTML = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Order ID:</p>
                    <p class="font-semibold">#${order.order_id}</p>
                </div>
                <div>
                    <p class="text-gray-600">Status:</p>
                    <p class="font-semibold"><span class="status-badge ${getStatusColor(order.status)}">${order.status}</span></p>
                </div>
                <div>
                    <p class="text-gray-600">Customer:</p>
                    <p class="font-semibold">${order.customer_name}</p>
                </div>
                <div>
                    <p class="text-gray-600">Email:</p>
                    <p class="font-semibold">${order.customer_email}</p>
                </div>
                <div>
                    <p class="text-gray-600">Total Amount:</p>
                    <p class="font-semibold">₱${parseFloat(order.total_amount).toFixed(2)}</p>
                </div>
                <div>
                    <p class="text-gray-600">Payment Method:</p>
                    <p class="font-semibold">${order.payment_method}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-600">Date Ordered:</p>
                    <p class="font-semibold">${formatDate(order.created_at)}</p>
                </div>
                <div class="col-span-2">
                    ${itemsHTML}
                </div>
            </div>
        `;
    }

    function closeOrderModal() {
        document.getElementById('orderDetailsModal').classList.add('hidden');
        currentOrderId = null;
    }

    function deleteOrder(orderId) {
        if (confirm('Are you sure you want to delete this order?')) {
            fetch('backend/end-points/delete_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ order_id: orderId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order deleted successfully');
                    loadOrders();
                } else {
                    alert('Error deleting order: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting order');
            });
        }
    }

    function getStatusColor(status) {
        const colors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'processing': 'bg-blue-100 text-blue-800',
            'shipped': 'bg-indigo-100 text-indigo-800',
            'delivered': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
</script>

<style>
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    #orderDetailsModal {
        display: none;
    }

    #orderDetailsModal.hidden {
        display: none;
    }

    #orderDetailsModal:not(.hidden) {
        display: flex;
    }
</style>
