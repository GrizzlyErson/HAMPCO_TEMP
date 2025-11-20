<?php
if (!isset($db) || !($db instanceof global_class)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/class.php";
    $db = new global_class();
}

// Get production line items with their materials
$query = "SELECT pl.*, 
          GROUP_CONCAT(
            JSON_OBJECT(
                'name', rm.rm_name,
                'category', rm.category,
                'amount', plm.amount
            )
          ) as materials
          FROM production_line pl
          LEFT JOIN production_line_materials plm ON pl.prod_line_id = plm.prod_line_id
          LEFT JOIN raw_materials rm ON plm.material_id = rm.rm_id
          WHERE pl.status != 'completed'  -- Exclude completed production lines
          AND pl.prod_line_id NOT IN (
              -- Exclude production lines that have any completed tasks
              SELECT DISTINCT ta.prod_line_id 
              FROM task_assignments ta 
              WHERE ta.status = 'completed'
          )
          GROUP BY pl.prod_line_id
          ORDER BY pl.date_created DESC";

$result = $db->conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Process materials data
        $materials = [];
        if ($row['materials']) {
            foreach (explode(',', $row['materials']) as $material_json) {
                $material = json_decode($material_json, true);
                if ($material && !empty($material['name'])) {
                    $materials[] = $material;
                }
            }
        }
?>
        <tr class="border-b border-gray-200 hover:bg-gray-50">
            <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['production_code']); ?></td>
            <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['product_name']); ?></td>
            <td class="px-4 py-2 text-center">
                <button onclick='showMaterialsModal(<?php echo htmlspecialchars(json_encode($materials)); ?>, <?php echo htmlspecialchars(json_encode([
                    "name" => $row['product_name'],
                    "length" => $row['length_m'],
                    "width" => $row['width_m'],
                    "weight" => $row['weight_g'],
                    "quantity" => $row['quantity']
                ])); ?>)' class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md hover:bg-blue-200 transition-colors">
                    View Materials
                </button>
            </td>
            <td class="px-4 py-2 text-center">
                <span class="px-2 py-1 rounded-full text-xs <?php 
                    switch($row['status']) {
                        case 'pending':
                            echo 'bg-yellow-100 text-yellow-800';
                            break;
                        case 'in_progress':
                            echo 'bg-blue-100 text-blue-800';
                            break;
                        case 'completed':
                            echo 'bg-green-100 text-green-800';
                            break;
                        default:
                            echo 'bg-gray-100 text-gray-800';
                    }
                ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                </span>
            </td>
            <td class="px-4 py-2 text-center">
                <div class="flex justify-center space-x-2">
                    <button onclick="assignTask('<?php echo $row['prod_line_id']; ?>')" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm">
                        Assign Task
                    </button>
                    <button onclick="viewAssignedMembers(<?php echo $row['prod_line_id']; ?>)"
                        class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm">
                        View Members
                    </button>
                </div>
            </td>
        </tr>
<?php
    }
} else {
    echo "<tr><td colspan='5' class='px-4 py-2 text-center text-gray-500'>No production items found</td></tr>";
}
?> 