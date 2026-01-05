<?php
require_once '../class.php';
$db = new global_class();

try {
    // Query that combines both regular and self-assigned tasks
    $query = "SELECT 
        pl.product_name,
        um.fullname AS member_name,
        um.role,
        CASE 
            WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') 
            THEN CONCAT(pl.length_m, 'm x ', pl.width_m, 'm')
            ELSE '-'
        END AS measurements,
        pl.weight_g,
        CASE 
            WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') 
            THEN pl.quantity
            ELSE '-'
        END AS quantity,
        COALESCE(ta.updated_at, mst.date_submitted) as completed_date,
        CASE 
            WHEN ta.id IS NOT NULL THEN 'Regular'
            ELSE 'Self-Assigned'
        END as task_type
    FROM production_line pl
    LEFT JOIN (
        SELECT prod_line_id, member_id, updated_at, id,
               ROW_NUMBER() OVER (PARTITION BY prod_line_id ORDER BY updated_at DESC) as rn
        FROM task_assignments 
        WHERE status = 'completed'
    ) ta ON pl.prod_line_id = ta.prod_line_id AND ta.rn = 1
    LEFT JOIN (
        SELECT production_id, member_id, date_submitted, id,
               ROW_NUMBER() OVER (PARTITION BY production_id ORDER BY date_submitted DESC) as rn
        FROM member_self_tasks 
        WHERE status = 'completed'
    ) mst ON pl.prod_line_id = mst.production_id AND mst.rn = 1
    INNER JOIN user_member um 
        ON COALESCE(ta.member_id, mst.member_id) = um.id
    WHERE ta.id IS NOT NULL OR mst.id IS NOT NULL
    ORDER BY completed_date DESC";

    $stmt = $db->conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $completedTasks = [];
    while ($row = $result->fetch_assoc()) {
        // Format the weight to 3 decimal places
        $row['weight_g'] = number_format($row['weight_g'], 3);
        
        // Format the completed date
        $row['completed_date'] = date('Y-m-d H:i', strtotime($row['completed_date']));
        
        $completedTasks[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $completedTasks
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading completed tasks: ' . $e->getMessage()
    ]);
}
?>