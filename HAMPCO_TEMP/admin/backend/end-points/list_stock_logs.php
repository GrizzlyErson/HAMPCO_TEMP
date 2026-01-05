<?php 




$user_id=$_SESSION['id'];

$list_stock_logs = $db->list_stock_logs();




if ($list_stock_logs && $list_stock_logs->num_rows > 0): ?>
    <?php while ($logs = $list_stock_logs->fetch_assoc()): ?>


       



        <tr class="border-b border-gray-200 hover:bg-gray-50">
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(ucfirst($logs['rm_name'])); ?></td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(ucfirst($logs['fullname'])); ?></td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(ucfirst($logs['stock_user_type'])); ?></td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($logs['stock_type']); ?></td>
            <td class="py-3 px-6 text-left"><?php echo number_format($logs['stock_outQty'], 2); ?></td>
            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($logs['stock_changes']); ?></td>
            <td class="py-3 px-6 text-left">
                <?php echo htmlspecialchars(date("F j, Y g:i A", strtotime($logs['stock_date']))); ?>
            </td>

          
            
            

        </tr>
                  
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="p-2">No record found.</td>
    </tr>
<?php endif; ?>

