// Function to handle task acceptance
async function acceptTask(taskId) {
    try {
        // First confirm with the user
        const confirmResult = await Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to accept this task?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, accept it!'
        });

        if (!confirmResult.isConfirmed) {
            return;
        }

        console.log('Accepting task:', taskId); // Debug log

        const response = await fetch('backend/end-points/update_task_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                task_id: taskId,
                action: 'accept'
            })
        });

        console.log('Response status:', response.status); // Debug log
        const responseText = await response.text();
        console.log('Raw response:', responseText); // Debug log

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid JSON response from server: ' + responseText);
        }

        console.log('Parsed response:', data); // Debug log
        
        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }
        
        if (!data.success) {
            throw new Error(data.message || 'Server indicated failure');
        }

        if (!data.task) {
            throw new Error('No task data received from server');
        }
        
        // Remove the task from New Tasks table
        const taskRow = document.querySelector(`button[onclick="acceptTask(${taskId})"]`);
        if (!taskRow) {
            console.error('Accept button not found');
            throw new Error('Could not find the task row to update');
        }
        const originalRow = taskRow.closest('tr');
        if (!originalRow) {
            console.error('Task row not found');
            throw new Error('Could not find the task row to update');
        }
        originalRow.remove();

        // Add task to My Assigned Tasks table
        const assignedTasksTable = document.querySelector('#assignedTasksTable tbody');
        if (!assignedTasksTable) {
            console.error('Assigned tasks table not found');
            throw new Error('Could not find the assigned tasks table');
        }

        // Remove "No assigned tasks" row if it exists
        const noTasksRow = assignedTasksTable.querySelector('tr td[colspan]');
        if (noTasksRow) {
            noTasksRow.closest('tr').remove();
        }

        // Check if we're in knotter view
        const tableHeaders = document.querySelectorAll('#newTasksTable th');
        const isKnotter = Array.from(tableHeaders).some(th => th.textContent.includes('Weight (g)'));
        console.log('Is knotter view:', isKnotter); // Debug log
        
        // Create new row with the correct structure
        const newRow = document.createElement('tr');
        if (isKnotter) {
            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.display_id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.product_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.weight_g}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.status}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.date_started || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button onclick="submitTask('${data.task.display_id}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md">Submit</button>
                </td>
            `;
        } else {
            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.display_id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.product_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.length_m}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.width_m}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.weight_g}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${['Knotted Liniwan', 'Knotted Bastos'].includes(data.task.product_name) ? '-' : data.task.quantity}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.status}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.task.date_started || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button onclick="submitTask('${data.task.display_id}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md">Submit</button>
                </td>
            `;
        }
        
        assignedTasksTable.appendChild(newRow);

        // Check if New Tasks table is empty
        const newTasksTable = document.querySelector('#newTasksTable tbody');
        if (newTasksTable && !newTasksTable.querySelector('tr')) {
            const noTasksRow = document.createElement('tr');
            noTasksRow.innerHTML = `
                <td colspan="${isKnotter ? '7' : '9'}" class="px-6 py-4 text-center text-gray-500">No new tasks available</td>
            `;
            newTasksTable.appendChild(noTasksRow);
        }

        // Show success message
        await Swal.fire({
            icon: 'success',
            title: 'Task Accepted',
            text: data.message,
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error('Error accepting task:', error);
        console.log('Error details:', error.stack);
        
        // Show detailed error message
        await Swal.fire({
            icon: 'error',
            title: 'Error Accepting Task',
            html: `
                <p>${error.message}</p>
                <pre class="mt-2 text-sm text-left text-red-600">${error.stack}</pre>
            `,
            confirmButtonText: 'OK'
        });
    }
}

// Function to handle task decline
async function declineTask(taskId) {
    try {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, decline it!'
        });

        if (result.isConfirmed) {
            const response = await fetch('backend/end-points/update_task_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    task_id: taskId,
                    action: 'decline'
                })
            });

            const data = await response.json();
            console.log('Server response:', data); // Debug log
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }

            if (data.success) {
                // Remove the task from New Tasks table
                const taskRow = document.querySelector(`button[onclick="declineTask(${taskId})"]`).closest('tr');
                if (!taskRow) {
                    console.error('Task row not found');
                    return;
                }
                taskRow.remove();

                // Check if table is empty
                const tasksTable = document.querySelector('#newTasksTable tbody');
                if (tasksTable && !tasksTable.querySelector('tr')) {
                    const noTasksRow = document.createElement('tr');
                    noTasksRow.innerHTML = `
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No new tasks available</td>
                    `;
                    tasksTable.appendChild(noTasksRow);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Task Declined',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message || 'Failed to decline task');
            }
        }
    } catch (error) {
        console.error('Error declining task:', error);
        console.log('Error details:', error.stack);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to decline task. Please try again.',
        });
    }
}

// Function to handle task submission
function submitTask(productionId) {
    // Show confirmation dialog
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
            // Send request to update task status
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
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Task has been submitted successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Refresh the page to show updated status
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to submit task');
                }
            })
            .catch(error => {
                console.error('Error submitting task:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to submit task. Please try again.'
                });
            });
        }
    });
}
