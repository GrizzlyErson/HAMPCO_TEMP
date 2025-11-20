// Function to load member dashboard data
async function loadMemberDashboard() {
    try {
        const response = await fetch('backend/end-points/get_member_dashboard.php');
        const data = await response.json();

        // Update task statistics
        document.getElementById('pendingTasks').textContent = data.taskStats.pending;
        document.getElementById('inProgressTasks').textContent = data.taskStats.in_progress;
        document.getElementById('completedTasks').textContent = data.taskStats.completed;

        // Update recent tasks list
        const tasksList = document.getElementById('recentTasksList');
        tasksList.innerHTML = data.recentTasks.map(task => `
            <tr class="border-b border-gray-100">
                <td class="px-4 py-3">${task.product_name}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs ${getStatusClass(task.status)}">
                        ${task.status}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600">${formatDate(task.deadline)}</td>
            </tr>
        `).join('');

        // Create task progress chart
        const progressChart = new ApexCharts(document.getElementById('taskProgressChart'), {
            series: [{
                name: 'Tasks',
                data: [
                    data.taskStats.pending,
                    data.taskStats.in_progress,
                    data.taskStats.completed
                ]
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                }
            },
            colors: ['#F59E0B', '#3B82F6', '#10B981'],
            xaxis: {
                categories: ['Pending', 'In Progress', 'Completed'],
            }
        });
        progressChart.render();

        // Update current task details
        const currentTaskDetails = document.getElementById('currentTaskDetails');
        if (data.currentTask) {
            currentTaskDetails.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-4">Product Details</h4>
                        <div class="space-y-2">
                            <p class="flex justify-between">
                                <span class="text-gray-600">Product Name:</span>
                                <span class="font-medium">${data.currentTask.product_name}</span>
                            </p>
                            ${data.currentTask.length_m ? `
                                <p class="flex justify-between">
                                    <span class="text-gray-600">Dimensions:</span>
                                    <span class="font-medium">${data.currentTask.length_m}m × ${data.currentTask.width_m}m</span>
                                </p>
                            ` : ''}
                            ${data.currentTask.weight_g ? `
                                <p class="flex justify-between">
                                    <span class="text-gray-600">Weight:</span>
                                    <span class="font-medium">${data.currentTask.weight_g}g</span>
                                </p>
                            ` : ''}
                            <p class="flex justify-between">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-medium">${data.currentTask.quantity}</span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-4">Task Details</h4>
                        <div class="space-y-2">
                            <p class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-1 rounded-full text-xs ${getStatusClass(data.currentTask.status)}">
                                    ${data.currentTask.status}
                                </span>
                            </p>
                            <p class="flex justify-between">
                                <span class="text-gray-600">Deadline:</span>
                                <span class="font-medium">${formatDate(data.currentTask.deadline)}</span>
                            </p>
                            <p class="flex justify-between">
                                <span class="text-gray-600">Assigned Date:</span>
                                <span class="font-medium">${formatDate(data.currentTask.assigned_date)}</span>
                            </p>
                        </div>
                    </div>
                </div>
            `;
        } else {
            currentTaskDetails.innerHTML = `
                <div class="text-center text-gray-500 py-4">
                    No active task assigned
                </div>
            `;
        }

    } catch (error) {
        console.error('Error loading member dashboard:', error);
    }
}

// Helper function to get status class
function getStatusClass(status) {
    switch(status.toLowerCase()) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'in progress':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Helper function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Load dashboard data when the page loads
document.addEventListener('DOMContentLoaded', loadMemberDashboard); 