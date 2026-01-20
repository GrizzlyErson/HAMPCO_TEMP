<!-- Member Task Requests Tab Content -->
<div id="memberTaskRequestsContent" class="tab-content hidden">
    <!-- Task Approval Requests Table -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Task Approval Requests</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="taskApprovalTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight / Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated later -->
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No requests found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Task Completion Confirmations Table -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Task Completion Confirmations</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="taskCompletionTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member's Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated later -->
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No completion requests found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add JavaScript for handling task requests -->
    <script>
    function loadTaskRequests() {
        fetch('backend/end-points/get_task_requests.php')
            .then(response => response.json())
            .then(data => {
                const tableBody = document.querySelector('#taskApprovalTable tbody');
                if (!data || data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500">No requests found</td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = data.map(request => {
                    let measurement = (request.weight_g && parseFloat(request.weight_g) > 0) ? `${request.weight_g} g` : '-';
                    if (request.role.toLowerCase() === 'weaver') {
                        if (request.length_m && request.width_in) {
                            measurement = `${request.length_m}m x ${request.width_in}in`;
                        }
                    }

                    return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono">${request.production_id}</td>
                        <td class="px-6 py-4 text-sm">${request.member_name}</td>
                        <td class="px-6 py-4 text-sm">${request.role}</td>
                        <td class="px-6 py-4 text-sm">${request.product_name}</td>
                        <td class="px-6 py-4 text-sm">${measurement}</td>
                        <td class="px-6 py-4 text-sm">${request.quantity || '1'}</td>
                        <td class="px-6 py-4 text-sm">${request.request_date}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(request.status)}">
                                ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            ${request.status === 'pending' ? `
                                <div class="flex space-x-2">
                                    <button onclick="handleTaskRequest(${request.request_id}, 'approve')"
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                                        Approve
                                    </button>
                                    <button onclick="handleTaskRequest(${request.request_id}, 'decline')"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                                        Decline
                                    </button>
                                </div>
                            ` : '-'}
                        </td>
                    </tr>
                `}).join('');
            })
            .catch(error => {
                console.error('Error loading task requests:', error);
                const tableBody = document.querySelector('#taskApprovalTable tbody');
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-red-500">Error loading requests. Please try again.</td>
                    </tr>
                `;
            });
    }

    function getStatusClass(status) {
        switch (status) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'approved':
                return 'bg-green-100 text-green-800';
            case 'declined':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function handleTaskRequest(requestId, action) {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('action', action);

        fetch('backend/end-points/handle_task_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: `Task request ${action}ed successfully`,
                    showConfirmButton: false,
                    timer: 1500
                });
                // Reload the task requests
                loadTaskRequests();
            } else {
                throw new Error(data.message || 'Failed to process request');
            }
        })
        .catch(error => {
            console.error('Error handling task request:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to process request. Please try again.'
            });
        });
    }

    function loadTaskCompletions() {
        fetch('backend/end-points/get_task_completions.php')
            .then(response => response.json())
            .then(data => {
                const tableBody = document.querySelector('#taskCompletionTable tbody');
                if (!data || data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No completion requests found</td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = data.map(task => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono">${task.prod_line_id}</td>
                        <td class="px-6 py-4 text-sm">${task.product_name}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(task.status)}">
                                ${task.status.charAt(0).toUpperCase() + task.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">${task.date_created}</td>
                        <td class="px-6 py-4 text-sm">${task.member_name}</td>
                        <td class="px-6 py-4 text-sm">
                            ${task.status === 'submitted' ? `
                                <button onclick="confirmTaskCompletion(${task.prod_line_id}, '${task.product_name.replace(/'/g, "\\'")}')"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                                    Confirm Task Completion
                                </button>
                            ` : '-'}
                        </td>
                    </tr>
                `).join('');
            })
            .catch(error => {
                console.error('Error loading task completions:', error);
                const tableBody = document.querySelector('#taskCompletionTable tbody');
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-red-500">Error loading completion requests. Please try again.</td>
                    </tr>
                `;
            });
    }

    function confirmTaskCompletion(prodLineId, productName) {
        if (['Piña Seda', 'Pure Piña Cloth'].includes(productName)) {
            // For fabrics, ask for both Length and Width
            Swal.fire({
                title: '
                    <div class="text-left">
                        <p class="mb-4 tes
                            <label class="block text-sm font-medium text-gray-700 mb-1">Actual Length (meters)</label>
                            <input id="swal-length" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter verified length" type="number" step="0.01" min="0">
                        </div>
                        <div class="mb-3"> 
                            <input id="swal-width" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter verified width" type="number" step="0.01" min="0">
                        </div>
                    </div>
                `,i
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#6B7280',
                confirmuttonText: 'Cancel',
                preConfirm: () => {
                    const length = document.!
                        Swal.showValidationMessage('Please enter both verified length and width');
                    }
                    return { length: length, width: width };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitConfirmation(prodLineId, null, result.value.length, result.value.width);
                }
        } else {
            // For weight-based products
            Swal.fire({
                title: 'Verify Product Weight',
                html: `
                    <div class="text-left">
                        <p class="mb-4 text-sm text-gray-600">Please weigh the product <b>${productName}</b> and enter the actual weight in grams. This will be used for wastage calculation and inventory.</p>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Actual Weight (grams)</label>
                            <input id="swal-weight" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter verified weight" type="number" step="0.01" min="0">
                      :y',
                preConfirm: () => {
                    const weight = document.getElementById('swal-weight').value;
                    if (!weight) {
                        Swal.showValidationMessage('Please enter the verified weight!');
                    }
                    return weight;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitConfirmation(prodLineId, result.value, null, null);
                }
            });
        } `&actual_output=${actualOutput}`;
        if (actualLength) body += `&actual_length=${actualLength}`;
        if (actualWidth) body += `&actual_width=${actualWidth}`;

        fetch('backend/end-points/confirm_task_completion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Task completion has been confirmed',
                    showConfirmButton: false,
                    timer: 1500
                });
                // Reload both tables to reflect the changes
                loadTaskCompletions();
                if (typeof loadTaskRequests === 'function') {
                    loadTaskRequests();
                }
            } else {
                throw new Error(data.message || 'Failed to confirm task completion');
            }
        })
        .catch(error => {
            console.error('Error confirming task completion:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to confirm task completion. Please try again.'
            });
        });
    }

    // Load task requests when the tab is shown
    const memberTaskRequestsTabEl = document.getElementById('memberTaskRequestsTab');
    if (memberTaskRequestsTabEl) {
        memberTaskRequestsTabEl.addEventListener('click', loadTaskRequests);
    }

    // Initial load if the tab is active
    const memberTaskRequestsContentEl = document.getElementById('memberTaskRequestsContent');
    if (memberTaskRequestsContentEl && memberTaskRequestsContentEl.classList.contains('show')) {
        loadTaskRequests();
    }
    
    // Load both tables on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTaskRequests();
        loadTaskCompletions();
    });
    </script>
</div> 