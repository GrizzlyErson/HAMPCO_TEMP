<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/class.php";

try {
    $db = new global_class();
    
    $data = [
        'totalCustomers' => 0,
        'totalMembers' => 0,
        'activeMembers' => 0,
        'pendingMembers' => 0,
        'totalProducts' => 0,
        'activeTasks' => 0,
        'pendingTasks' => 0,
        'inProgressTasks' => 0,
        'memberDistribution' => [
            'knotters' => 0,
            'warpers' => 0,
            'weavers' => 0
        ],
        'recentTasks' => [],
        'rawMaterials' => []
    ];

    // Get total customers
    $result = $db->conn->query("SELECT COUNT(*) as count FROM user_customer WHERE status = '1'");
    if ($result) {
        $data['totalCustomers'] = (int)$result->fetch_assoc()['count'];
    }

    // Get member statistics
    $result = $db->conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = '1' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) as pending
        FROM user_member");
    if ($result) {
        $memberStats = $result->fetch_assoc();
        $data['totalMembers'] = (int)$memberStats['total'];
        $data['activeMembers'] = (int)$memberStats['active'];
        $data['pendingMembers'] = (int)$memberStats['pending'];
    }

    // Get member distribution
    $result = $db->conn->query("SELECT 
        role,
        COUNT(*) as count
        FROM user_member
        WHERE status = '1'
        GROUP BY role");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            switch (strtolower($row['role'])) {
                case 'knotter':
                    $data['memberDistribution']['knotters'] = (int)$row['count'];
                    break;
                case 'warper':
                    $data['memberDistribution']['warpers'] = (int)$row['count'];
                    break;
                case 'weaver':
                    $data['memberDistribution']['weavers'] = (int)$row['count'];
                    break;
            }
        }
    }

    // Get total products from production_line
    $result = $db->conn->query("SELECT COUNT(*) as count FROM production_line WHERE status != 'cancelled'");
    if ($result) {
        $data['totalProducts'] = (int)$result->fetch_assoc()['count'];
    }

    // Get task statistics
    $result = $db->conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
        FROM task_assignments
        WHERE status != 'cancelled'");
    if ($result) {
        $taskStats = $result->fetch_assoc();
        $data['activeTasks'] = (int)$taskStats['total'];
        $data['pendingTasks'] = (int)$taskStats['pending'];
        $data['inProgressTasks'] = (int)$taskStats['in_progress'];
    }

    // Get recent tasks
    $result = $db->conn->query("SELECT 
        ta.status,
        pl.product_name,
        um.fullname as member_name
        FROM task_assignments ta
        JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
        JOIN user_member um ON ta.member_id = um.id
        WHERE ta.status != 'cancelled'
        ORDER BY ta.created_at DESC
        LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data['recentTasks'][] = [
                'product_name' => $row['product_name'],
                'member_name' => $row['member_name'],
                'status' => ucfirst($row['status'])
            ];
        }
    }

    // Get raw materials overview
    $result = $db->conn->query("SELECT 
        raw_materials_name as name,
        category,
        rm_quantity as stock,
        CASE 
            WHEN category = 'Liniwan/Washout' THEN 1000
            WHEN category = 'Bastos' THEN 800
            ELSE 500
        END as min_stock
        FROM raw_materials
        WHERE rm_status = 'Available'
        ORDER BY rm_quantity ASC
        LIMIT 6");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data['rawMaterials'][] = [
                'name' => $row['name'],
                'category' => $row['category'] ?: 'Uncategorized',
                'stock' => (float)$row['stock'],
                'min_stock' => (float)$row['min_stock']
            ];
        }
    }

    echo json_encode($data);

} catch (Exception $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load dashboard data: ' . $e->getMessage()]);
}
?> 