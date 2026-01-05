<?php 
$fetch_all_non_verify_member = $db->fetch_all_non_verify_member();

if ($fetch_all_non_verify_member->num_rows > 0) {
    while ($row = $fetch_all_non_verify_member->fetch_assoc()) {


        if($row['umstatus']==1){
            $status="Verified";
        }else{
             $status="Waiting For Verification";
        }

?>
    <tr class="border-b border-gray-200 hover:bg-gray-50">
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['umid_number']); ?></td>
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['umfullname']); ?></td>
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['umemail']); ?></td>
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['umphone']); ?></td>
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['umrole']); ?></td>
        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['umsex']); ?></td>
        <td class="py-3 px-6 text-left"><?=$status?></td>
        <td class="py-3 px-6 flex space-x-2">
            <button 
                class="verifyBtn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow <?= $row['umstatus'] == 1 ? 'opacity-50 cursor-not-allowed' : '' ?>"
                data-id="<?php echo htmlspecialchars($row['umid']); ?>" 
                data-name="<?php echo htmlspecialchars($row['umfullname']); ?>"
                <?= $row['umstatus'] == 1 ? 'disabled' : '' ?>>
                <span class="material-icons text-sm mr-1">check_circle</span> Verify
            </button>
            <button 
                class="declineBtn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow <?= $row['umstatus'] == 1 ? 'opacity-50 cursor-not-allowed' : '' ?>"
                data-id="<?php echo htmlspecialchars($row['umid']); ?>" 
                data-name="<?php echo htmlspecialchars($row['umfullname']); ?>"
                <?= $row['umstatus'] == 1 ? 'disabled' : '' ?>>
                <span class="material-icons text-sm mr-1">cancel</span> Decline
            </button>
        </td>

    </tr>
<?php
    }
} else {
?>
    <tr>
        <td colspan="8" class="py-3 px-6 text-center">No members found.</td>
    </tr>
<?php
}
?>

<!-- Modal Structure -->
<div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" style="display:none;">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6">
        <h2 class="text-xl font-semibold mb-4" id="modalTitle">Action</h2>
        <p id="modalContent" class="mb-4">Are you sure you want to proceed?</p>
        <div class="flex justify-end space-x-2">
            <button id="modalCancel" class="bg-gray-500 hover:bg-gray-600 text-white py-1 px-3 rounded">Cancel</button>
            <button id="modalConfirm" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded">Confirm</button>
        </div>
    </div>
</div>

<!-- Include jQuery if it's not yet included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let actionType = '';
    let userId = '';

    $('.verifyBtn').click(function() {
        userId = $(this).data('id');
        const userName = $(this).data('name');
        actionType = 'verify';
        $('#modalTitle').text('Verify Member');
        $('#modalContent').text(`Are you sure you want to verify ${userName}?`);
        $('#actionModal').fadeIn();
    });

    $('.declineBtn').click(function() {
        userId = $(this).data('id');
        const userName = $(this).data('name');
        actionType = 'decline';
        $('#modalTitle').text('Decline Member');
        $('#modalContent').text(`Are you sure you want to decline ${userName}?`);
        $('#actionModal').fadeIn();
    });

    // These handlers should NOT be inside the click handlers above
    $('#modalCancel').click(function() {
        $('#actionModal').fadeOut();
    });

    $('#modalConfirm').click(function() {
        console.log(`User ID: ${userId}, Action: ${actionType}`);
        $.ajax({
            type: "POST",
            url: "backend/end-points/controller.php",
            data: {
                requestType: "MemberVerification",
                actionType: actionType,
                userId: userId
            },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    alertify.success(response.message);  
                    setTimeout(function () {
                        window.location.href = "member"; 
                    }, 1000);
                } else {
                    alertify.error(response.message); 
                    $('.spinner').hide();
                }
            }
        });
        $('#actionModal').fadeOut();
    });
});
</script>
