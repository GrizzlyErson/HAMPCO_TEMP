<?php include "components/header.php";?>

<!-- Top bar with user profile -->
<div class="flex justify-between items-center bg-white p-4 mb-6 rounded-md shadow-md">
    <h2 class="text-lg font-semibold text-gray-700">Raw Logs</h2>
    <div class="w-10 h-10 ">
    </div>
</div>


<!-- Table of members -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
    <!-- Search bar -->
    <div class="mb-4">
    <input type="text" id="searchInput" placeholder="Search..." class="w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
</div>

    <table class="min-w-full table-auto" id="taskTable">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Raw Name</th>
                <th class="py-3 px-6 text-left">Name</th>
                <th class="py-3 px-6 text-left">Account Type</th>
                <th class="py-3 px-6 text-left">Activity</th>
                <th class="py-3 px-6 text-left">Quantity</th>
                <th class="py-3 px-6 text-left">Changes</th>
                <th class="py-3 px-6 text-left">Date</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
            <?php 
            include "backend/end-points/list_stock_logs.php";
            ?>
        </tbody>
    </table>
</div>

<?php include "components/footer.php";?>

<script src="assets/js/app.js"></script>

<script>
// jQuery search functionality
$(document).ready(function() {
    $("#searchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#taskTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>
