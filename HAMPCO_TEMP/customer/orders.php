<?php
include "component/header.php";
?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Checkout History</h1>

    <?php
    // Fetch all orders for this customer
    $query = "SELECT o.order_id, o.full_name, o.delivery_address, o.order_status, o.total_amount, o.date_created
              FROM orders o
              WHERE o.order_user_id = ?
              ORDER BY o.date_created DESC";
    
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0):
    ?>
        <!-- Orders List -->
        <div class="space-y-6">
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Order Header -->
                    <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 border-l-4 border-green-600">
                        <div class="flex justify-between items-start flex-wrap gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Order ID</p>
                                <p class="text-lg font-bold text-gray-800">#<?php echo $order['order_id']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Order Date</p>
                                <p class="text-lg font-semibold text-gray-800"><?php echo date('M d, Y H:i', strtotime($order['date_created'])); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                                    <?php 
                                    if ($order['order_status'] === 'Completed') echo 'bg-green-100 text-green-800';
                                    elseif ($order['order_status'] === 'Pending') echo 'bg-yellow-100 text-yellow-800';
                                    elseif ($order['order_status'] === 'Cancelled') echo 'bg-red-100 text-red-800';
                                    else echo 'bg-green-100 text-green-800';
                                    ?>
                                ">
                                    <?php echo $order['order_status']; ?>
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Total Amount</p>
                                <p class="text-2xl font-bold text-red-600">PHP <?php echo number_format($order['total_amount'], 2); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="p-6">
                        <h3 class="font-bold text-gray-800 mb-4">Items Ordered</h3>
                        
                        <?php
                        // Fetch items for this order
                        $items_query = "SELECT oi.quantity, oi.unit_price, oi.subtotal, p.prod_id, p.prod_name, p.prod_image
                                       FROM order_items oi
                                       JOIN product p ON oi.prod_id = p.prod_id
                                       WHERE oi.order_id = ?";
                        
                        $items_stmt = $db->conn->prepare($items_query);
                        $items_stmt->bind_param("i", $order['order_id']);
                        $items_stmt->execute();
                        $items_result = $items_stmt->get_result();
                        ?>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b-2 border-gray-200">
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
                                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Quantity</th>
                                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Unit Price</th>
                                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items_result->fetch_assoc()): ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                            <td class="py-4 px-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-16 h-16 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                                        <img src="../upload/<?php echo $item['prod_image']; ?>" alt="<?php echo $item['prod_name']; ?>" class="w-full h-full object-cover">
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-800"><?php echo $item['prod_name']; ?></p>
                                                        <p class="text-sm text-gray-600">ID: <?php echo $item['prod_id']; ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 text-center font-semibold text-gray-800"><?php echo $item['quantity']; ?></td>
                                            <td class="py-4 px-4 text-right text-gray-800">PHP <?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td class="py-4 px-4 text-right font-semibold text-gray-800">PHP <?php echo number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="bg-gray-50 p-6 border-t border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-4">Delivery Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Recipient Name</p>
                                <p class="font-semibold text-gray-800"><?php echo $order['full_name'] ?? 'N/A'; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Delivery Address</p>
                                <p class="font-semibold text-gray-800"><?php echo $order['delivery_address'] ?? 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <!-- No Orders -->
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">No Checkout History</h2>
            <p class="text-gray-600 mb-6">You haven't made any purchases yet.</p>
            <a href="customer_home_page.php" class="inline-block bg-green-600 hover:bg-green-700 text-white py-2 px-6 rounded-lg font-semibold transition">
                Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include "component/footer.php"; ?>
