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

    <table class="w-full border-collapse" id="taskTable">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Raw Name</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Name</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Account Type</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Activity</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Quantity</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Changes</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Date</th>
            </tr>
        </thead>
        <tbody id="rawStockLogsTableBody" class="divide-y divide-gray-200">
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
