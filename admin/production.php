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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (g)</th>
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

                tableBody.innerHTML = data.map(request => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${request.request_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.member_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.role}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.product_type}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.weight_g || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.quantity || '1'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.request_date}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(request.status)}">
                                ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                `).join('');
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${task.prod_line_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${task.product_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(task.status)}">
                                ${task.status.charAt(0).toUpperCase() + task.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${task.date_created}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${task.member_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            ${task.status === 'submitted' ? `
                                <button onclick="confirmTaskCompletion(${task.prod_line_id})"
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

    function confirmTaskCompletion(prodLineId) {
        Swal.fire({
            title: 'Confirm Task Completion',
            text: 'Are you sure you want to confirm this task as completed?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#EF4444',
            confirmButtonText: 'Yes, confirm completion',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('prod_line_id', prodLineId);

                fetch('backend/end-points/confirm_task_completion.php', {
                    method: 'POST',
                    body: formData
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
        });
    }

    // Load task requests when the tab is shown
    document.getElementById('memberTaskRequestsTab').addEventListener('click', loadTaskRequests);

    // Initial load if the tab is active
    if (document.getElementById('memberTaskRequestsContent').classList.contains('show')) {
        loadTaskRequests();
    }
    </script>
</div> 