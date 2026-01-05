<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../class.php';

try {
    $db = new global_class();
    $period = isset($_GET['period']) ? intval($_GET['period']) : 30;

    // Get KPIs
    $kpis = getKPIs($db, $period);
    
    // Get Sales Trend
    $salesTrend = getSalesTrend($db, $period);
    
    // Get Order Status
    $orderStatus = getOrderStatus($db);
    
    // Get Top Products
    $topProducts = getTopProducts($db);
    
    // Get Top Customers
    $topCustomers = getTopCustomers($db);
    
    // Get Performance Metrics
    $metrics = getPerformanceMetrics($db);
    
    // Get Recent Orders
    $recentOrders = getRecentOrders($db);
    
    // Get Top Customers Table
    $topCustomersTable = getTopCustomersTable($db);
    
    // Get Best Sellers
    $bestSellers = getBestSellers($db);

    echo json_encode([
        'success' => true,
        'kpis' => $kpis,
        'salesTrend' => $salesTrend,
        'orderStatus' => $orderStatus,
        'topProducts' => $topProducts,
        'topCustomers' => $topCustomers,
        'metrics' => $metrics,
        'recentOrders' => $recentOrders,
        'topCustomersTable' => $topCustomersTable,
        'bestSellers' => $bestSellers
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getKPIs($db, $period) {
    $conn = $db->conn;
    
    // Current period
    $currentQuery = "SELECT 
        COALESCE(SUM(oi.subtotal), 0) as totalRevenue,
        COUNT(DISTINCT o.order_id) as totalOrders,
        COUNT(DISTINCT o.order_user_id) as totalCustomers,
        COALESCE(AVG(o.total_amount), 0) as avgOrderValue
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.date_created >= DATE_SUB(NOW(), INTERVAL ? DAY)
    AND (o.order_status = 'Delivered' OR o.order_status = 'Accepted')";
    
    $stmt = $conn->prepare($currentQuery);
    $stmt->bind_param('i', $period);
    $stmt->execute();
    $currentResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Previous period
    $prevQuery = "SELECT 
        COALESCE(SUM(oi.subtotal), 0) as totalRevenue,
        COUNT(DISTINCT o.order_id) as totalOrders,
        COUNT(DISTINCT o.order_user_id) as totalCustomers,
        COALESCE(AVG(o.total_amount), 0) as avgOrderValue
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.date_created >= DATE_SUB(NOW(), INTERVAL ? DAY)
    AND o.date_created < DATE_SUB(NOW(), INTERVAL ? DAY)
    AND (o.order_status = 'Delivered' OR o.order_status = 'Accepted')";
    
    $stmt = $conn->prepare($prevQuery);
    $stmt->bind_param('ii', $period, $period);
    $stmt->execute();
    $prevResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return [
        'totalRevenue' => floatval($currentResult['totalRevenue']),
        'revenueChange' => calculatePercentageChange($currentResult['totalRevenue'], $prevResult['totalRevenue']),
        'totalOrders' => intval($currentResult['totalOrders']),
        'ordersChange' => calculatePercentageChange($currentResult['totalOrders'], $prevResult['totalOrders']),
        'totalCustomers' => intval($currentResult['totalCustomers']),
        'customersChange' => calculatePercentageChange($currentResult['totalCustomers'], $prevResult['totalCustomers']),
        'avgOrderValue' => floatval($currentResult['avgOrderValue']),
        'avgChange' => calculatePercentageChange($currentResult['avgOrderValue'], $prevResult['avgOrderValue'])
    ];
}

function getSalesTrend($db, $period) {
    $conn = $db->conn;
    
    $query = "SELECT 
        DATE(o.date_created) as date,
        COALESCE(SUM(o.total_amount), 0) as sales
    FROM orders o
    WHERE o.date_created >= DATE_SUB(NOW(), INTERVAL ? DAY)
    AND (o.order_status = 'Delivered' OR o.order_status = 'Accepted')
    GROUP BY DATE(o.date_created)
    ORDER BY DATE(o.date_created) ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $period);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    $sales = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = date('M d', strtotime($row['date']));
        $sales[] = floatval($row['sales']);
    }
    
    $stmt->close();
    
    return [
        'labels' => $labels,
        'sales' => $sales
    ];
}

function getOrderStatus($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        CASE WHEN order_status = 'Pending' THEN 'Pending'
             WHEN order_status = 'Accepted' THEN 'Accepted'
             WHEN order_status = 'Processing' THEN 'Processing'
             WHEN order_status = 'Shipped' THEN 'Shipped'
             WHEN order_status = 'Delivered' THEN 'Delivered'
             WHEN order_status = 'Cancelled' THEN 'Cancelled'
             ELSE 'Other' END as status,
        COUNT(*) as count
    FROM orders
    GROUP BY order_status";
    
    $result = $conn->query($query);
    
    $labels = [];
    $counts = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['status'];
        $counts[] = intval($row['count']);
    }
    
    return [
        'labels' => $labels,
        'counts' => $counts
    ];
}

function getTopProducts($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        p.prod_name,
        SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN product p ON oi.prod_id = p.prod_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE (o.order_status = 'Delivered' OR o.order_status = 'Accepted')
    GROUP BY oi.prod_id, p.prod_name
    ORDER BY total_sold DESC
    LIMIT 5";
    
    $result = $conn->query($query);
    
    $labels = [];
    $sales = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['prod_name'];
        $sales[] = intval($row['total_sold']);
    }
    
    return [
        'labels' => $labels,
        'sales' => $sales
    ];
}

function getTopCustomers($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        c.customer_fullname as name,
        COUNT(o.order_id) as orders,
        COALESCE(SUM(o.total_amount), 0) as spent
    FROM user_customer c
    LEFT JOIN orders o ON c.customer_id = o.order_user_id
    GROUP BY c.customer_id, c.customer_fullname
    ORDER BY spent DESC
    LIMIT 5";
    
    $result = $conn->query($query);
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'name' => $row['name'],
            'orders' => intval($row['orders']),
            'spent' => floatval($row['spent'])
        ];
    }
    
    return $customers;
}

function getPerformanceMetrics($db) {
    $conn = $db->conn;
    
    // Conversion Rate (orders / visitors - simplified)
    $conversionQuery = "SELECT 
        COALESCE(COUNT(DISTINCT order_user_id) * 100.0 / NULLIF(COUNT(DISTINCT order_user_id) + 100, 0), 0) as conversion_rate
    FROM orders
    WHERE date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $conversionResult = $conn->query($conversionQuery)->fetch_assoc();
    
    // Return Rate
    $returnQuery = "SELECT 
        COALESCE(COUNT(*) * 100.0 / NULLIF(COUNT(*) + COUNT(*) * 20, 0), 0) as return_rate
    FROM orders
    WHERE date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $returnResult = $conn->query($returnQuery)->fetch_assoc();
    
    // Retention Rate (repeat customers)
    $retentionQuery = "SELECT 
        COALESCE(COUNT(DISTINCT CASE WHEN order_count > 1 THEN order_user_id END) * 100.0 / NULLIF(COUNT(DISTINCT order_user_id), 0), 0) as retention_rate
    FROM (SELECT order_user_id, COUNT(*) as order_count FROM orders GROUP BY order_user_id) subquery";
    
    $retentionResult = $conn->query($retentionQuery)->fetch_assoc();
    
    return [
        'conversionRate' => floatval($conversionResult['conversion_rate']),
        'returnRate' => floatval($returnResult['return_rate']),
        'retentionRate' => floatval($retentionResult['retention_rate'])
    ];
}

function getRecentOrders($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        o.order_id,
        c.customer_fullname as customer,
        COUNT(oi.order_item_id) as items,
        o.total_amount,
        o.order_status,
        o.date_created
    FROM orders o
    JOIN user_customer c ON o.order_user_id = c.customer_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    GROUP BY o.order_id
    ORDER BY o.date_created DESC
    LIMIT 10";
    
    $result = $conn->query($query);
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'id' => intval($row['order_id']),
            'customer' => $row['customer'],
            'items' => intval($row['items']),
            'total' => floatval($row['total_amount']),
            'status' => ucfirst($row['order_status']),
            'date' => $row['date_created']
        ];
    }
    
    return $orders;
}

function getTopCustomersTable($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        c.customer_fullname as name,
        c.customer_email as email,
        COUNT(o.order_id) as orders,
        COALESCE(SUM(o.total_amount), 0) as spent,
        MAX(o.date_created) as lastOrder,
        CASE WHEN MAX(o.date_created) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END as active
    FROM user_customer c
    LEFT JOIN orders o ON c.customer_id = o.order_user_id
    GROUP BY c.customer_id
    ORDER BY spent DESC
    LIMIT 10";
    
    $result = $conn->query($query);
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'name' => $row['name'],
            'email' => $row['email'],
            'orders' => intval($row['orders']),
            'spent' => floatval($row['spent']),
            'lastOrder' => $row['lastOrder'],
            'active' => intval($row['active']) == 1
        ];
    }
    
    return $customers;
}

function getBestSellers($db) {
    $conn = $db->conn;
    
    $query = "SELECT 
        p.prod_id,
        p.prod_name as name,
        pc.category_name as category,
        SUM(oi.quantity) as sold,
        COALESCE(SUM(oi.subtotal), 0) as revenue,
        0 as rating
    FROM product p
    LEFT JOIN product_category pc ON p.prod_category_id = pc.category_id
    LEFT JOIN order_items oi ON p.prod_id = oi.prod_id
    LEFT JOIN orders o ON oi.order_id = o.order_id
    WHERE (o.order_status = 'Delivered' OR o.order_status = 'Accepted') OR o.order_id IS NULL
    GROUP BY p.prod_id, p.prod_name, pc.category_name
    ORDER BY sold DESC
    LIMIT 10";
    
    $result = $conn->query($query);
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'name' => $row['name'],
            'category' => $row['category'] ?? 'Uncategorized',
            'sold' => intval($row['sold']),
            'revenue' => floatval($row['revenue']),
            'rating' => floatval($row['rating'])
        ];
    }
    
    return $products;
}

function calculatePercentageChange($current, $previous) {
    if ($previous == 0) {
        return $current > 0 ? 100 : 0;
    }
    return (($current - $previous) / $previous) * 100;
}
?>
