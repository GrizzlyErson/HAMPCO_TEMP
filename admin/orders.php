<?php include "components/header.php";?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Orders</h1>
    <button id="AddProduct" class="mb-3 bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition flex items-center gap-2">
        <span class="material-icons">add</span>
        Add Products
    </button>
</div>

<!-- Filter Bar -->
<div class="mb-4 flex flex-col sm:flex-row gap-4 bg-white p-4 rounded-md shadow">
    <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm">
        <option value="all">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="processing">Processing</option>
        <option value="shipped">Shipped</option>
        <option value="delivered">Delivered</option>
        <option value="cancelled">Cancelled</option>
    </select>
    
    <input type="text" id="searchInput" placeholder="Search by Order ID or Customer..." class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm">
    
    <button id="refreshBtn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-sm whitespace-nowrap">
        Refresh
    </button>
</div>

<!-- Orders Table -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-3 sm:p-4">
    <table class="w-full table-auto" id="ordersTable">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-4 text-left">Order ID</th>
                <th class="py-3 px-4 text-left">Customer</th>
                <th class="py-3 px-4 text-left">Email</th>
                <th class="py-3 px-4 text-left">Amount</th>
                <th class="py-3 px-4 text-left">Status</th>
                <th class="py-3 px-4 text-left">Payment</th>
                <th class="py-3 px-4 text-left">Date</th>
                <th class="py-3 px-4 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm" id="ordersTableBody">
            <tr>
                <td colspan="8" class="py-3 px-4 text-center text-gray-500">Loading orders...</td>
            </tr>
        </tbody>
    </table>
</div>

<?php include "components/footer.php";?>

<!-- Add Product Modal -->
<div id="AddProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center " style="display:none;">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4">Add Product</h2>
        <form id="AddProductForm">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="rm_name" class="w-full border rounded p-2" placeholder="" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Description</label>
                <input type="text" name="rm_description" id="rm_description" class="w-full border rounded p-2" placeholder="" >
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Price</label>
                <input type="text" name="rm_price" id="rm_price" class="w-full border rounded p-2" placeholder="" required>
            </div>


             <div class="mb-4">
                    <label for="productCategory" class="block text-sm font-medium text-gray-700">Choose a Category</label>
                    <select id="productCategory" name="rm_product_Category" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="" disabled selected>Select a Category</option>
                        <?php $fetch_all_category = $db->fetch_all_category();
                            if ($fetch_all_category): 
                                foreach ($fetch_all_category as $category): ?>
                                    <option value="<?=$category['category_id']?>"><?=$category['category_name']?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No record found.</option>
                            <?php endif; ?>
                    </select>
                </div>
           
           
            <div class="mb-4">
                <label for="productImage" class="block text-gray-700">Product Image</label>
                <input type="file" id="productImage" name="rm_product_image" class="w-full p-2 border border-gray-300 rounded-md" accept="image/*" required>
            </div>


            <div class="flex justify-end gap-2">
                <button type="button" id="closeAddProductModal" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" id="submitAddRawMaterials" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add</button>
            </div>
        </form>
    </div>
</div>

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

<!-- Order Notifications Modal -->
<div id="orderNotificationsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Pending Orders</h2>
            <button id="closeNotificationsModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        <div id="notificationsContent" class="space-y-3">
            <p class="text-center text-gray-500">Loading notifications...</p>
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
                    attachStatusChangeListeners();
                } else {
                    displayNoOrders();
                }
            })
            .catch(error => {
                console.error('Error loading orders:', error);
                displayNoOrders();
            });
    }

    function attachStatusChangeListeners() {
        const dropdowns = document.querySelectorAll('.status-dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                const orderId = this.getAttribute('data-order-id');
                const newStatus = this.value;
                const currentStatus = this.getAttribute('data-current-status');

                if (newStatus === '' || newStatus === currentStatus) {
                    return;
                }

                updateOrderStatus(orderId, newStatus, this);
            });
        });
    }

    function updateOrderStatus(orderId, newStatus, dropdown) {
        if (confirm(`Are you sure you want to change this order status to "${newStatus}"?`)) {
            fetch('backend/end-points/update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertify.success('Order status updated successfully');
                    // Update the dropdown to reflect current status
                    if (dropdown) {
                        dropdown.setAttribute('data-current-status', newStatus);
                    }
                    loadOrders();
                } else {
                    alertify.error('Error updating status: ' + (data.error || 'Unknown error'));
                    // Reset dropdown to previous value
                    if (dropdown) {
                        dropdown.value = dropdown.getAttribute('data-current-status');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alertify.error('Error updating order status');
                // Reset dropdown to previous value
                if (dropdown) {
                    dropdown.value = dropdown.getAttribute('data-current-status');
                }
            });
        } else {
            // Reset dropdown if user cancels
            if (dropdown) {
                dropdown.value = dropdown.getAttribute('data-current-status');
            }
        }
    }

    function displayOrders(orders) {
        const tbody = document.getElementById('ordersTableBody');
        
        if (orders.length === 0) {
            displayNoOrders();
            return;
        }

        tbody.innerHTML = orders.map(order => `
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="py-3 px-4 text-left truncate">${order.order_id ? '#' + order.order_id : 'N/A'}</td>
                <td class="py-3 px-4 text-left font-medium truncate">${order.customer_name || 'N/A'}</td>
                <td class="py-3 px-4 text-left truncate">${order.customer_email || 'N/A'}</td>
                <td class="py-3 px-4 text-left whitespace-nowrap">₱${parseFloat(order.total_amount || 0).toFixed(2)}</td>
                <td class="py-3 px-4 text-left">
                    <span class="status-badge ${getStatusColor(order.status)}">
                        ${order.status || 'pending'}
                    </span>
                </td>
                <td class="py-3 px-4 text-left truncate">${order.payment_method || 'N/A'}</td>
                <td class="py-3 px-4 text-left whitespace-nowrap">${formatDate(order.created_at)}</td>
                <td class="py-3 px-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <select class="status-dropdown px-2 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" data-order-id="${order.order_id}" data-current-status="${order.status || 'pending'}">
                            <option value="">Status</option>
                            ${order.status === 'Accepted' ? `<option value="Accepted" selected style="display:none;">Accepted</option>` : ''}
                            <option value="Pending" ${order.status === 'Pending' ? 'selected' : ''}>Pending</option>
                            <option value="Processing" ${order.status === 'Processing' ? 'selected' : ''}>Processing</option>
                            <option value="Shipped" ${order.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
                            <option value="Delivered" ${order.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
                            <option value="Declined" ${order.status === 'Declined' ? 'selected' : ''}>Declined</option>
                            <option value="Cancelled" ${order.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                        <button onclick="viewOrderDetails('${order.order_id}')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-xs hover:bg-blue-200 transition-colors whitespace-nowrap">
                            Details
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function displayNoOrders() {
        const tbody = document.getElementById('ordersTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="py-3 px-4 text-center text-gray-500">No orders found</td>
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

<script>
    $(document).ready(function(){
        // Add Product button click
        $('#AddProduct').on('click', function(){
            $('#AddProductModal').fadeIn();
        });

        // Close modal button
        $('#closeAddProductModal').on('click', function(){
            $('#AddProductModal').fadeOut();
        });

        // Close modal when clicking outside
        $('#AddProductModal').on('click', function(e){
            if (e.target === this) {
                $('#AddProductModal').fadeOut();
            }
        });

        // Add Product form submission
        $('#AddProductForm').on('submit', function(e){
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('requestType', 'AddProduct');

            $.ajax({
                type: "POST",
                url: "backend/end-points/controller.php",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (response) {
                    if (response && response.status === 'success') {
                        alertify.success(response.message || 'Product added successfully');
                        $('#AddProductForm')[0].reset();
                        $('#AddProductModal').fadeOut();
                        setTimeout(function () {
                            location.reload();
                        }, 800);
                    } else {
                        alertify.error((response && response.message) ? response.message : 'Failed to add product');
                    }
                },
                error: function(xhr, status, err) {
                    console.error('AddProduct error', xhr.responseText, status, err);
                    alertify.error('Server error while adding product');
                }
            });
        });
    });
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
