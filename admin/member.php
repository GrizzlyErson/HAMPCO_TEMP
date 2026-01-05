<?php include "components/header.php";?>

<div class="bg-white rounded-md shadow-md p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-700">Members Management</h2>
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Search members..."
                class="w-64 p-2 pl-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto" id="memberTable">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-4 text-left min-w-24">Member ID</th>
                    <th class="py-3 px-4 text-left min-w-32">Full Name</th>
                    <th class="py-3 px-4 text-left min-w-40">Email</th>
                    <th class="py-3 px-4 text-left min-w-24">Phone</th>
                    <th class="py-3 px-4 text-left min-w-20">Role</th>
                    <th class="py-3 px-4 text-left min-w-16">Sex</th>
                    <th class="py-3 px-4 text-left min-w-28">Status</th>
                    <th class="py-3 px-4 text-left min-w-32">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                <tr class="bg-gray-50">
                    <th colspan="8" class="py-3 px-4 text-left text-lg font-semibold text-gray-700">New Members Verification</th>
                </tr>
                <?php include "backend/end-points/list_unverified_members.php";?>
                
                <tr class="bg-gray-50">
                    <th colspan="8" class="py-3 px-4 text-left text-lg font-semibold text-gray-700">Members</th>
                </tr>
                <?php include "backend/end-points/list_verified_members.php";?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Structure -->
<div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6">
        <h2 class="text-xl font-semibold mb-4" id="modalTitle">Action</h2>
        <p id="modalContent" class="mb-4">Are you sure you want to proceed?</p>
        <div class="flex justify-end space-x-2">
            <button id="modalCancel" class="bg-gray-500 hover:bg-gray-600 text-white py-1 px-3 rounded transition-colors duration-200">
                Cancel
            </button>
            <button id="modalConfirm" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded transition-colors duration-200">
                Confirm
            </button>
        </div>
    </div>
</div>

<?php include "components/footer.php";?>

<script>
$(document).ready(function() {
    // Search functionality
    $("#searchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#memberTable tbody tr:has(td)").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    let actionType = '';
    let userId = '';

    // Verify button click handler
    $('.verifyBtn').click(function() {
        if ($(this).hasClass('cursor-not-allowed')) {
            return;
        }
        userId = $(this).data('id');
        const userName = $(this).data('name');
        actionType = 'verify';
        $('#modalTitle').text('Verify Member');
        $('#modalContent').text(`Are you sure you want to verify ${userName}?`);
        $('#actionModal').removeClass('hidden').addClass('flex');
    });

    // Decline/Remove button click handler
    $('.declineBtn, .removeBtn').click(function() {
        if ($(this).hasClass('cursor-not-allowed')) {
            return;
        }
        userId = $(this).data('id');
        const userName = $(this).data('name');
        actionType = $(this).hasClass('declineBtn') ? 'decline' : 'remove';
        const actionText = actionType === 'decline' ? 'decline' : 'remove';
        $('#modalTitle').text(actionType === 'decline' ? 'Decline Member' : 'Remove Member');
        $('#modalContent').text(`Are you sure you want to ${actionText} ${userName}?`);
        $('#actionModal').removeClass('hidden').addClass('flex');
    });

    // Modal cancel button handler
    $('#modalCancel').click(function() {
        $('#actionModal').removeClass('flex').addClass('hidden');
    });

    // Modal confirm button handler
    $('#modalConfirm').click(function() {
        $.ajax({
            type: "POST",
            url: "backend/end-points/controller.php",
            data: {
                requestType: "MemberVerification",
                actionType: actionType,
                userId: userId
            },
            dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while processing your request.',
                    icon: 'error'
                });
            }
        });
        $('#actionModal').removeClass('flex').addClass('hidden');
    });

    // Close modal when clicking outside
    $('#actionModal').click(function(e) {
        if (e.target === this) {
            $(this).removeClass('flex').addClass('hidden');
        }
    });
});
</script>
