<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/hampco_memberFix/admin/backend/class.php';
$db = new global_class();

$fetch_all_materials = $db->fetch_all_materials();

if ($fetch_all_materials->num_rows > 0) {
    while ($row = $fetch_all_materials->fetch_assoc()) {
?>
    <tr class="border-b border-gray-200 hover:bg-gray-50">
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['raw_materials_name']); ?></td>
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['category']); ?></td>
        <td class="py-3 px-6 text-left"><?php echo number_format($row['rm_quantity'], 0); ?></td>
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['supplier_name'] ?? '-'); ?></td>
        <td class="py-3 px-6 text-left" style="color: <?php echo strtolower($row['rm_status']) == 'available' ? 'green' : (strtolower($row['rm_status']) == 'not available' ? 'red' : 'orange'); ?>">
            <?php echo htmlspecialchars(ucfirst($row['rm_status'])); ?>
        </td>
        <td class="py-3 px-6 flex space-x-2">
            <button 
                type="button"
                class="updateRmBtn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow"
                data-id="<?php echo htmlspecialchars($row['rmid']); ?>" 
                data-rm_name="<?php echo htmlspecialchars($row['raw_materials_name']); ?>"
                data-category="<?php echo htmlspecialchars($row['category']); ?>"
                data-rm_quantity="<?php echo htmlspecialchars($row['rm_quantity']); ?>"
                data-rm_unit="<?php echo htmlspecialchars($row['rm_unit']); ?>"
                data-rm_status="<?php echo htmlspecialchars(ucfirst(strtolower($row['rm_status']))); ?>"
                data-supplier_name="<?php echo htmlspecialchars($row['supplier_name'] ?? ''); ?>"
            >
                <span class="material-icons text-sm mr-1">edit</span> Update
            </button>
            <button 
                type="button"
                class="deleteRmBtn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow"
                data-id="<?php echo htmlspecialchars($row['rmid']); ?>" 
                data-rm_name="<?php echo htmlspecialchars($row['raw_materials_name']); ?>"
            >
                <span class="material-icons text-sm mr-1">delete</span> Remove
            </button>
        </td>
    </tr>
<?php
    }
} else {
?>
    <tr>
        <td colspan="6" class="py-3 px-6 text-center">No raw materials found.</td>
    </tr>
<?php
}
?>
