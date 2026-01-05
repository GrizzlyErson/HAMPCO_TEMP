<?php
require_once '../class.php';
$db = new global_class();

try {
    // Get filter parameters
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $role = isset($_GET['role']) ? $_GET['role'] : 'all';
    
    // Base query that includes both regular and self-assigned tasks
    $query = "SELECT 
        pr.*,
        um.fullname AS member_name,
        um.role AS member_role,
        CASE 
            WHEN pr.is_self_assigned = 1 THEN mst.product_name
            ELSE pl.product_name
        END as product_name,
        CASE 
            WHEN pr.is_self_assigned = 1 THEN mst.weight_g
            ELSE pl.weight_g
        END as weight_g,
        pl.length_m,
        pl.width_m,
        CASE 
            WHEN pr.is_self_assigned = 1 THEN 'Self-Assigned'
            ELSE 'Regular'
        END as task_type
    FROM payment_records pr
    INNER JOIN user_member um ON pr.member_id = um.id
    LEFT JOIN member_self_tasks mst ON pr.production_id = mst.production_id AND pr.is_self_assigned = 1
    LEFT JOIN production_line pl ON 
        CASE 
            WHEN pr.is_self_assigned = 0 THEN pl.prod_line_id = CAST(pr.production_id AS UNSIGNED)
            ELSE pl.prod_line_id = CAST(SUBSTRING(pr.production_id, 3) AS UNSIGNED)
        END
    WHERE 1=1";

    // Add filters
    if ($status !== 'all') {
        $query .= " AND pr.payment_status = ?";
    }
    if ($role !== 'all') {
        $query .= " AND um.role = ?";
    }
    
    $query .= " ORDER BY pr.date_created DESC";

    $stmt = $db->conn->prepare($query);

    // Bind parameters if filters are active
    $paramTypes = '';
    $params = [];
    
    if ($status !== 'all') {
        $paramTypes .= 's';
        $params[] = $status;
    }
    if ($role !== 'all') {
        $paramTypes .= 's';
        $params[] = $role;
    }

    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        // Format measurements based on product type (m x in)
        if (in_array($row['product_name'], ['Piña Seda', 'Pure Piña Cloth']) && !is_null($row['length_m']) && !is_null($row['width_m'])) {
            $row['measurements'] = number_format($row['length_m'], 3) . 'm x ' . number_format($row['width_m'], 0) . 'in';
        } else {
            $row['measurements'] = '-';
        }

        // Format weight based on product type
        if (in_array($row['product_name'], ['Piña Seda', 'Pure Piña Cloth'])) {
            $row['weight_g'] = '-';
        } else {
            $row['weight_g'] = number_format($row['weight_g'], 3);
        }
        
        // Format quantity based on product type
        if (in_array($row['product_name'], ['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'])) {
            $row['quantity'] = '-';
        } else {
            $row['quantity'] = 1;
        }
        
        // Format total amount
        $row['total'] = number_format($row['total_amount'], 2);
        
        // Format unit rate
        $row['unit_rate'] = number_format($row['unit_rate'], 2);
        
        // Format date paid
        $row['date_paid'] = $row['date_paid'] ? date('Y-m-d H:i', strtotime($row['date_paid'])) : '-';

        $records[] = $row;
    }

    // Calculate summary statistics
    $summary = [
        'totalPayments' => 0,
        'pendingPayments' => 0,
        'completedPayments' => 0,
        'totalMembers' => count(array_unique(array_column($records, 'member_id')))
    ];

    foreach ($records as $record) {
        $amount = floatval($record['total_amount']);
        $summary['totalPayments'] += $amount;
        if ($record['payment_status'] === 'Pending') {
            $summary['pendingPayments'] += $amount;
        } else if ($record['payment_status'] === 'Paid') {
            $summary['completedPayments'] += $amount;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $records,
        'summary' => $summary
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading payment records: ' . $e->getMessage()
    ]);
}
?>