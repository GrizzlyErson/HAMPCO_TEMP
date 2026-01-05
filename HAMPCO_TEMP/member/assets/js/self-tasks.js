// Function to submit a self-assigned task
function submitSelfTask(productionId) {
    Swal.fire({
        title: 'Submit Task',
        text: 'Are you sure you want to submit this task?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, submit it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('backend/end-points/update_self_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    production_id: productionId,
                    action: 'submit'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Task has been submitted successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to submit task');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to submit task. Please try again.'
                });
            });
        }
    });
}

// Function to load self-assigned tasks
function loadSelfAssignedTasks() {
    const tableBody = document.getElementById('selfAssignedTasksBody');
    
    // Show loading state
    tableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading tasks...</td></tr>';

    fetch('backend/end-points/get_self_tasks.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tasks) {
                if (data.tasks.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No tasks available</td></tr>';
                    return;
                }

                const rows = data.tasks.map(task => {
                    return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.production_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.product_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.weight_g}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.status}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <button onclick="viewMaterials('${task.product_name}', ${task.weight_g})" 
                                class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-1 rounded text-sm">
                                View Materials
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.date_created}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.date_submitted || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            ${task.status === 'in_progress' ? `
                                <button onclick="submitSelfTask('${task.production_id}')" 
                                    class="bg-[#4F46E5] hover:bg-[#4338CA] text-white text-sm py-2 px-4 rounded">
                                    Submit
                                </button>
                            ` : ''}
                            ${task.status !== 'submitted' ? `
                                <button onclick="deleteSelfTask('${task.production_id}')" 
                                    class="bg-[#EF4444] hover:bg-[#DC2626] text-white text-sm py-2 px-4 rounded">
                                    Delete
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                    `;
                }).join('');

                tableBody.innerHTML = rows;
            } else {
                tableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Error loading tasks</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Failed to load tasks</td></tr>';
        });
}

// Function to delete a self-assigned task
function deleteSelfTask(productionId) {
    Swal.fire({
        title: 'Delete Task',
        text: 'Are you sure you want to delete this task? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('backend/end-points/update_self_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    production_id: productionId,
                    action: 'delete'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Task has been deleted successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        loadSelfAssignedTasks();
                    });
                } else {
                    throw new Error(data.message || 'Failed to delete task');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to delete task. Please try again.'
                });
            });
        }
    });
}

// Load self-assigned tasks when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const createdTab = document.getElementById('created-tab');
    const createdContent = document.getElementById('created');

    // Load tasks when Task Created tab is shown
    if (createdTab) {
        createdTab.addEventListener('click', loadSelfAssignedTasks);
    }

    // Initial load if Task Created tab is active
    if (createdContent && !createdContent.classList.contains('hidden')) {
        loadSelfAssignedTasks();
    }
}); 