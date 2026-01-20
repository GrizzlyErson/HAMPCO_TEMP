<?php
header('Content-Type: application/json');
require_once '../class.php';
$db = new global_class();

try {
    // Fetch completed tasks with payment info
    $query = "SELECT 
        pl.prod_line_id,
        pl.product_name,
        pl.weight_g,
        pl.length_m,
        pl.width_m,
        pl.quantity,
        ta.created_at as start_time,
        ta.updated_at as end_time,
        um.fullname as member_name,
        COALESCE(pr.total_amount, 0) as labor_cost
    FROM task_assignments ta
    JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
    JOIN user_member um ON ta.member_id = um.id
    LEFT JOIN payment_records pr ON pr.production_id = pl.prod_line_id AND pr.member_id = ta.member_id
    WHERE ta.status = 'completed'
    ORDER BY ta.updated_at DESC";

    $result = $db->conn->query($query);
    $data = [];

    // Fetch average unit costs for estimation
    $costs = [];
    $cost_query = "SELECT raw_materials_name, AVG(unit_cost) as cost FROM raw_materials GROUP BY raw_materials_name";
    $cost_res = $db->conn->query($cost_query);
    while($r = $cost_res->fetch_assoc()) {
        $costs[$r['raw_materials_name']] = floatval($r['cost']);
    }
    // Fallback cost if not found
    $default_cost = 0.5; 

    while ($row = $result->fetch_assoc()) {
        // Calculate Duration
        $start = new DateTime($row['start_time']);
        $end = new DateTime($row['end_time']);
        $interval = $start->diff($end);
        
        $duration_parts = [];
        if ($interval->d > 0) $duration_parts[] = $interval->d . 'd';
        if ($interval->h > 0) $duration_parts[] = $interval->h . 'h';
        if ($interval->i > 0 && empty($duration_parts)) $duration_parts[] = $interval->i . 'm';
        $duration = !empty($duration_parts) ? implode(' ', $duration_parts) : '< 1m';

        // Calculate Wastage and Material Cost (Estimates)
        $wastage = 0;
        $wastage_rate = 0;
        $material_cost = 0;
        $actual_output_display = '';

        if (in_array($row['product_name'], ['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'])) {
            $weight = floatval($row['weight_g']);
            $actual_output_display = number_format($weight, 2) . ' g';
            
            // Multipliers based on standard consumption
            $multiplier = 1.22; // Default for knotted
            if ($row['product_name'] == 'Warped Silk') $multiplier = 1.2;
            
            $input_weight = $weight * $multiplier;
            $wastage = $input_weight - $weight;
            $wastage_rate = ($input_weight > 0) ? ($wastage / $input_weight) * 100 : 0;
            
            // Estimate material cost
            $mat_name = ($row['product_name'] == 'Warped Silk') ? 'Silk' : 'Piña Loose';
            // Simple matching for cost
            $unit_cost = $default_cost;
            foreach($costs as $k => $v) {
                if (stripos($k, $mat_name) !== false) {
                    $unit_cost = $v;
                    break;
                }
            }
            
            $material_cost = $input_weight * $unit_cost;

        } elseif (in_array($row['product_name'], ['Piña Seda', 'Pure Piña Cloth'])) {
            $actual_output_display = number_format($row['length_m'], 2) . 'm x ' . number_format($row['width_m'], 0) . 'in';
            $wastage = 0; // Hard to calculate without fiber weight input
            $wastage_rate = 0;
            
            // Placeholder material cost calculation for fabric
            // Assuming standard costs per meter
            $len = floatval($row['length_m']);
            $material_cost = ($row['product_name'] == 'Piña Seda') ? ($len * 350) : ($len * 450); 
        }

        $data[] = [
            'prod_id' => 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT),
            'product' => $row['product_name'],
            'member' => $row['member_name'],
            'duration' => $duration,
            'actual_output' => $actual_output_display,
            'wastage' => $wastage > 0 ? number_format($wastage, 2) . ' g' : '-',
            'wastage_rate' => $wastage_rate > 0 ? number_format($wastage_rate, 1) . '%' : '-',
            'labor_cost' => floatval($row['labor_cost']),
            'material_cost' => $material_cost,
            'total_cost' => floatval($row['labor_cost']) + $material_cost
        ];
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>