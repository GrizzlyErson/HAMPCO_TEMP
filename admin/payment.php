<?php
require_once "components/header.php";
?>

    <h1 class="text-3xl font-bold text-gray-800 mb-8">Payment Management</h1>

    <!-- Search and Filter Section -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                    placeholder="Search members..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    id="memberSearch">
            </div>
            <div class="flex gap-4">
                <select name="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Paid">Paid</option>
                    <option value="Adjusted">Adjusted</option>
                </select>
                <select name="role-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Roles</option>
                    <option value="knotter">Knotter</option>
                    <option value="warper">Warper</option>
                    <option value="weaver">Weaver</option>
                </select>
                <div class="relative inline-block text-left">
                    <div>
                        <button type="button" id="exportDropdownBtn" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500">
                            Export
                            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div id="exportDropdownMenu" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                        <div class="py-1" role="none">
                            <button id="exportExcelBtn" class="text-gray-700 block w-full text-left px-4 py-2 text-sm" role="menuitem" tabindex="-1">Export as Excel</button>
                            <button id="exportPdfBtn" class="text-gray-700 block w-full text-left px-4 py-2 text-sm" role="menuitem" tabindex="-1">Export as PDF</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-600 text-sm">Total Payments</h2>
                    <p id="totalPayments" class="text-2xl font-semibold text-gray-800">₱0.00</p>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-600 text-sm">Pending Payments</h2>
                    <p id="pendingPayments" class="text-2xl font-semibold text-gray-800">₱0.00</p>
                </div>
            </div>
        </div>

        <!-- Completed Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-600 text-sm">Completed Payments</h2>
                    <p id="completedPayments" class="text-2xl font-semibold text-gray-800">₱0.00</p>
                </div>
            </div>
        </div>

        <!-- Total Members -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-600 text-sm">Total Members</h2>
                    <p id="totalMembers" class="text-2xl font-semibold text-gray-800">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Records Table -->
    <div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Payment Records</h2>
        <table id="paymentTable" class="w-full border-collapse">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Member</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Product</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Measure</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Wt(g)</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Qty</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Rate</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Total</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Paid Date</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="paymentRecordsTableBody" class="divide-y divide-gray-200">
                <!-- Payment records will be loaded here -->
            </tbody>
        </table>
    </div>


<!-- Export libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

<!-- Include SweetAlert2 for better alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Include the payment records JavaScript -->
<script src="assets/js/payment-records.js"></script>

<script>
$(document).ready(function() {
    $("#memberSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#paymentRecordsTableBody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $('#exportDropdownBtn').on('click', function(e) {
        e.stopPropagation();
        $('#exportDropdownMenu').toggleClass('hidden');
    });

    $(document).on('click', function(event) {
        if (!$(event.target).closest('.relative').length) {
            $('#exportDropdownMenu').addClass('hidden');
        }
    });

    $('#exportExcelBtn').on('click', function() {
        const table = document.getElementById('paymentTable');
        const wb = XLSX.utils.table_to_book(table, { sheet: "Payment Records" });
        XLSX.writeFile(wb, 'payment_records.xlsx');
        $('#exportDropdownMenu').addClass('hidden');
    });

    $('#exportPdfBtn').on('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.autoTable({
            html: '#paymentTable',
            startY: 20,
            theme: 'striped',
            headStyles: { fillColor: [22, 160, 133] },
            styles: {
                halign: 'center',
                valign: 'middle',
                fontSize: 8,
            },
            didDrawPage: function (data) {
                // Header
                doc.setFontSize(20);
                doc.setTextColor(40);
                doc.text("Payment Records", data.settings.margin.left, 15);
            }
        });

        doc.save('payment_records.pdf');
        $('#exportDropdownMenu').addClass('hidden');
    });
});
</script>

<?php
require_once "components/footer.php";
?>