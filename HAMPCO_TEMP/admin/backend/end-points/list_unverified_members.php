<?php 
$fetch_unverified_members = $db->fetch_members_by_status(0);

if ($fetch_unverified_members === false) {
    echo '<tr><td colspan="8" class="py-3 px-6 text-center text-red-500">Error fetching unverified members. Please try again later.</td></tr>';
} else if ($fetch_unverified_members->num_rows > 0) {
    while ($row = $fetch_unverified_members->fetch_assoc()) {
?>
    <tr class="border-b border-gray-200 hover:bg-gray-50">
        <td class="py-3 px-4 text-left truncate max-w-24"><?php echo htmlspecialchars($row['umid_number']); ?></td>
        <td class="py-3 px-4 text-left truncate max-w-32"><?php echo htmlspecialchars($row['umfullname']); ?></td>
        <td class="py-3 px-4 text-left truncate max-w-40"><?php echo htmlspecialchars($row['umemail']); ?></td>
        <td class="py-3 px-4 text-left truncate max-w-24"><?php echo htmlspecialchars($row['umphone']); ?></td>
        <td class="py-3 px-4 text-left truncate max-w-20"><?php echo htmlspecialchars($row['umrole']); ?></td>
        <td class="py-3 px-4 text-left truncate max-w-16"><?php echo htmlspecialchars($row['umsex']); ?></td>
        <td class="py-3 px-4 text-left">Waiting For Verification</td>
        <td class="py-3 px-4 flex flex-col gap-2">
            <button 
                class="verifyBtn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-full text-xs flex items-center justify-center shadow w-full"
                data-id="<?php echo htmlspecialchars($row['umid']); ?>" 
                data-name="<?php echo htmlspecialchars($row['umfullname']); ?>">
                <span class="material-icons text-sm mr-1">check_circle</span> Verify
            </button>
            <button 
                class="declineBtn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-full text-xs flex items-center justify-center shadow w-full"
                data-id="<?php echo htmlspecialchars($row['umid']); ?>" 
                data-name="<?php echo htmlspecialchars($row['umfullname']); ?>">
                <span class="material-icons text-sm mr-1">cancel</span> Decline
            </button>
        </td>
    </tr>
<?php
    }
} else {
?>
    <tr>
        <td colspan="8" class="py-3 px-6 text-center">No unverified members found.</td>
    </tr>
<?php
}
?> 