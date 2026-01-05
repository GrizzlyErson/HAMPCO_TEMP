// Function to load completed tasks
async function loadCompletedTasks() {
    try {
        const response = await fetch('backend/end-points/get_completed_tasks.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to load completed tasks');
        }

        const tableBody = document.querySelector('#completedTasksTable tbody');
        if (!tableBody) return;

        if (!data.data || data.data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-gray-500 py-4">
                        No completed tasks found
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = data.data.map(task => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm">${task.product_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">${task.member_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">${task.role}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">${task.measurements}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">${task.weight_g}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">${task.quantity}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">${task.completed_date}</td>
            </tr>
        `).join('');

    } catch (error) {
        console.error('Error:', error);
        const tableBody = document.querySelector('#completedTasksTable tbody');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-red-500 py-4">
                        Error loading completed tasks. Please try again.
                    </td>
                </tr>
            `;
        }
    }
}

// Load completed tasks when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadCompletedTasks();
    
    // Refresh completed tasks every 5 minutes
    setInterval(loadCompletedTasks, 300000);
});