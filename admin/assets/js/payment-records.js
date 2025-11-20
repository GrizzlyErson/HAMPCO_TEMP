// Load payment records when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadPaymentRecords();

    // Add event listeners for filter changes
    const statusFilter = document.querySelector('select[name="status-filter"]');
    const roleFilter = document.querySelector('select[name="role-filter"]');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', loadPaymentRecords);
    }
    if (roleFilter) {
        roleFilter.addEventListener('change', loadPaymentRecords);
    }
});

async function loadPaymentRecords() {
    try {
        const statusFilter = document.querySelector('select[name="status-filter"]')?.value || 'all';
        const roleFilter = document.querySelector('select[name="role-filter"]')?.value || 'all';
        
        const response = await fetch(`backend/end-points/get_payment_records.php?status=${statusFilter}&role=${roleFilter}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to load payment records');
        }

        updatePaymentTable(data.data);
        updatePaymentSummary(data.data);

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('paymentRecordsTableBody').innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-red-500 py-4">
                    Error loading payment records. Please try again.
                </td>
            </tr>
        `;
    }
}

function updatePaymentTable(records) {
    const tableBody = document.getElementById('paymentRecordsTableBody');
    
    if (!records || records.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-gray-500 py-4">
                    No payment records found
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = records.map(record => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm">${record.member_name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">${record.product_name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">${record.measurements}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">${record.weight_g}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">${record.quantity}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">₱${record.unit_rate}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">₱${record.total}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(record.payment_status)}">
                    ${record.payment_status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">${record.date_paid}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                ${getActionButtons(record)}
            </td>
        </tr>
    `).join('');
}

function getStatusClass(status) {
    switch (status) {
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'Paid':
            return 'bg-green-100 text-green-800';
        case 'Adjusted':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getActionButtons(record) {
    if (record.payment_status === 'Paid') {
        return '-';
    }

    return `
        <div class="flex space-x-2">
            ${record.payment_status !== 'Paid' ? `
                <button onclick="processPayment(${record.id})"
                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                    Pay
                </button>
            ` : ''}
            ${record.payment_status === 'Pending' ? `
                <button onclick="adjustPayment(${record.id})"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                    Adjust
                </button>
            ` : ''}
        </div>
    `;
}

function updatePaymentSummary(records) {
    // Calculate summary statistics
    const summary = records.reduce((acc, record) => {
        acc.totalPayments += parseFloat(record.total_amount);
        if (record.payment_status === 'Pending') {
            acc.pendingPayments += parseFloat(record.total_amount);
        }
        if (record.payment_status === 'Paid') {
            acc.completedPayments += parseFloat(record.total_amount);
        }
        if (!acc.members.has(record.member_id)) {
            acc.members.add(record.member_id);
        }
        return acc;
    }, {
        totalPayments: 0,
        pendingPayments: 0,
        completedPayments: 0,
        members: new Set()
    });

    // Update summary displays
    document.getElementById('totalPayments').textContent = `₱${summary.totalPayments.toFixed(2)}`;
    document.getElementById('pendingPayments').textContent = `₱${summary.pendingPayments.toFixed(2)}`;
    document.getElementById('completedPayments').textContent = `₱${summary.completedPayments.toFixed(2)}`;
    document.getElementById('totalMembers').textContent = summary.members.size;
}

async function processPayment(paymentId) {
    try {
        const result = await Swal.fire({
            title: 'Process Payment',
            text: 'Are you sure you want to mark this payment as paid?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#EF4444',
            confirmButtonText: 'Yes, process payment'
        });

        if (result.isConfirmed) {
            const response = await fetch('backend/end-points/process_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ payment_id: paymentId })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Payment has been processed successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadPaymentRecords();
            } else {
                throw new Error(data.message || 'Failed to process payment');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to process payment. Please try again.'
        });
    }
}

async function adjustPayment(paymentId) {
    try {
        const { value: adjustmentReason } = await Swal.fire({
            title: 'Adjust Payment',
            input: 'textarea',
            inputLabel: 'Adjustment Reason',
            inputPlaceholder: 'Enter the reason for adjustment...',
            showCancelButton: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to provide a reason for the adjustment!';
                }
            }
        });

        if (adjustmentReason) {
            const response = await fetch('backend/end-points/adjust_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    reason: adjustmentReason
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Payment has been marked for adjustment.',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadPaymentRecords();
            } else {
                throw new Error(data.message || 'Failed to adjust payment');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to adjust payment. Please try again.'
        });
    }
}