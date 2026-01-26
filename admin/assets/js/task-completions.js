document.addEventListener('DOMContentLoaded', function() {
    // Get the Member Task Requests tab and content elements
    const memberTaskRequestsTab = document.getElementById('memberTaskRequestsTab');
    const memberTaskRequestsContent = document.getElementById('memberTaskRequestsContent');

    if (memberTaskRequestsTab && memberTaskRequestsContent) {
        // Add click event for Member Task Requests tab
        memberTaskRequestsTab.addEventListener('click', () => {
            loadTaskCompletions();
        });

        // Initial load if Member Task Requests tab is active
        if (!memberTaskRequestsContent.classList.contains('hidden')) {
            loadTaskCompletions();
        }
    }
});

function loadTaskCompletions() {
    fetch('backend/end-points/get_task_completions.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#taskCompletionTable tbody');
            if (!data || data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No completion requests found</td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = data.map(task => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${task.production_id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.member_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.role}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.product_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.weight}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.date_started}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.date_submitted || 'Not submitted'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(task.status)}">
                            ${task.status.charAt(0).toUpperCase() + task.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ${task.status === 'submitted' ? `
                            <button onclick="confirmTaskCompletion('${task.production_id}')"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                                Confirm Completion
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
                    <td colspan="9" class="px-6 py-4 text-center text-red-500">Error loading completion requests. Please try again.</td>
                </tr>
            `;
        });
}

function getStatusClass(status) {
    switch (status.toLowerCase()) {
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'submitted':
            return 'bg-yellow-100 text-yellow-800';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function confirmTaskCompletion(productionId) {
    console.log('confirmTaskCompletion called with productionId:', productionId);
    
    // First, fetch the task details to determine the type of inputs needed
    const url = `backend/end-points/get_task_details.php?production_id=${encodeURIComponent(productionId)}`;
    console.log('Fetching from:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(taskData => {
            console.log('Task data received:', taskData);
            
            if (!taskData.success) {
                throw new Error(taskData.message || 'Failed to load task details');
            }
            
            const task = taskData.data;
            const productName = task.product_name;
            
            // Determine fields based on product name
            const isWeightBased = ['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'].includes(productName);
            const isDimensionsBased = ['Piña Seda', 'Pure Piña Cloth'].includes(productName);

            let inputFields = '';
            if (isWeightBased) {
                inputFields = `
                    <div class="mb-4 text-left">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actual Weight (grams)</label>
                        <input type="number" id="actual_weight" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter actual weight" step="0.01" min="0" value="${task.weight_g || ''}">
                    </div>
                `;
            } else if (isDimensionsBased) {
                inputFields = `
                    <div class="mb-4 text-left">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actual Length (meters)</label>
                        <input type="number" id="actual_length" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter actual length" step="0.01" min="0" value="${task.length_m || ''}">
                    </div>
                    <div class="mb-4 text-left">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actual Width (inches)</label>
                        <input type="number" id="actual_width" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter actual width" step="0.01" min="0" value="${task.width_m || ''}">
                    </div>
                `;
            }

            Swal.fire({
                title: 'Confirm Task Completion',
                html: `
                    <div class="text-sm text-gray-600 mb-4">
                        Please verify the actual product measurements before confirming completion.
                    </div>
                    <div class="bg-gray-50 p-3 rounded mb-4 text-left">
                        <p><strong>Production ID:</strong> ${productionId}</p>
                        <p><strong>Product:</strong> ${productName}</p>
                        <p><strong>Member:</strong> ${task.member_name}</p>
                    </div>
                    ${inputFields}
                    <div class="mb-4 text-left">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Notes (optional)</label>
                        <textarea id="admin_notes" class="swal2-textarea" style="margin: 0; width: 100%;" placeholder="Any notes or observations..." rows="3"></textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirm Completion',
                preConfirm: () => {
                    let measurements = {};
                    const notes = document.getElementById('admin_notes').value;

                    if (isWeightBased) {
                        const weight = document.getElementById('actual_weight').value;
                        if (!weight) {
                            Swal.showValidationMessage('Please enter the actual weight');
                            return false;
                        }
                        measurements.actual_weight = weight;
                    } else if (isDimensionsBased) {
                        const length = document.getElementById('actual_length').value;
                        const width = document.getElementById('actual_width').value;
                        if (!length || !width) {
                            Swal.showValidationMessage('Please enter both length and width');
                            return false;
                        }
                        measurements.actual_length = length;
                        measurements.actual_width = width;
                    }

                    return { measurements, notes };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitTaskCompletion(productionId, result.value);
                }
            });
        })
        .catch(error => {
            console.error('Error loading task details:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load task details. Please try again.'
            });
        });
}

function submitTaskCompletion(productionId, data) {
    const formData = new FormData();
    formData.append('production_id', productionId);
    
    if (data.measurements) {
        formData.append('measurements', JSON.stringify(data.measurements));
    }
    if (data.notes) {
        formData.append('admin_notes', data.notes);
    }

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
                text: 'Task completion has been confirmed and product added to inventory.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                loadTaskCompletions();
            });
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