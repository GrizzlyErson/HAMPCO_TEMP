// Function to load dashboard data
async function loadDashboardData() {
    try {
        const response = await fetch('backend/end-points/get_dashboard_data.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        // Update counters
        document.getElementById('totalCustomers').textContent = data.totalCustomers || 0;
        document.getElementById('totalMembers').textContent = data.totalMembers || 0;
        document.getElementById('activeMembers').textContent = `${data.activeMembers || 0} Active`;
        document.getElementById('pendingMembers').textContent = `${data.pendingMembers || 0} Pending`;
        document.getElementById('totalProducts').textContent = data.totalProducts || 0;
        document.getElementById('activeTasks').textContent = data.activeTasks || 0;
        document.getElementById('pendingTasks').textContent = `${data.pendingTasks || 0} Pending`;
        document.getElementById('inProgressTasks').textContent = `${data.inProgressTasks || 0} In Progress`;

        // Update member distribution
        document.getElementById('totalKnotters').textContent = data.memberDistribution.knotters || 0;
        document.getElementById('totalWarpers').textContent = data.memberDistribution.warpers || 0;
        document.getElementById('totalWeavers').textContent = data.memberDistribution.weavers || 0;

        // Create member distribution chart
        if (document.getElementById('memberDistributionChart')) {
            const memberChart = new ApexCharts(document.getElementById('memberDistributionChart'), {
                series: [
                    data.memberDistribution.knotters || 0,
                    data.memberDistribution.warpers || 0,
                    data.memberDistribution.weavers || 0
                ],
                chart: {
                    type: 'donut',
                    height: 250
                },
                labels: ['Knotters', 'Warpers', 'Weavers'],
                colors: ['#3B82F6', '#10B981', '#F59E0B'],
                legend: {
                    position: 'bottom'
                }
            });
            memberChart.render();
        }

        // Update recent tasks list
        const tasksList = document.getElementById('recentTasksList');
        if (tasksList) {
            if (data.recentTasks && data.recentTasks.length > 0) {
                tasksList.innerHTML = data.recentTasks.map(task => `
                    <tr class="border-b border-gray-100">
                        <td class="px-4 py-3">${task.product_name}</td>
                        <td class="px-4 py-3">${task.member_name}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs ${getStatusClass(task.status)}">
                                ${task.status}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } else {
                tasksList.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-center text-gray-500">
                            No recent tasks found
                        </td>
                    </tr>
                `;
            }
        }

        // Update raw materials overview
        const materialsOverview = document.getElementById('rawMaterialsOverview');
        if (materialsOverview) {
            if (data.rawMaterials && data.rawMaterials.length > 0) {
                materialsOverview.innerHTML = data.rawMaterials.map(material => `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700">${material.name}</h4>
                        <p class="text-sm text-gray-500 mt-1">${material.category}</p>
                        <div class="mt-2 flex justify-between items-center">
                            <span class="text-sm text-gray-600">Stock:</span>
                            <span class="font-semibold ${material.stock < material.min_stock ? 'text-red-600' : 'text-green-600'}">
                                ${material.stock}g
                            </span>
                        </div>
                        ${material.stock < material.min_stock ? `
                            <div class="mt-2 text-xs text-red-600">
                                Low stock warning
                            </div>
                        ` : ''}
                    </div>
                `).join('');
            } else {
                materialsOverview.innerHTML = `
                    <div class="col-span-3 text-center text-gray-500 py-4">
                        No raw materials data available
                    </div>
                `;
            }
        }

    } catch (error) {
        console.error('Error loading dashboard data:', error);
        // Show error message to user
        const errorMessage = document.createElement('div');
        errorMessage.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
        errorMessage.innerHTML = `
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"> Failed to load dashboard data.</span>
        `;
        document.querySelector('.grid').appendChild(errorMessage);
    }
}

// Helper function to get status class
function getStatusClass(status) {
    switch(status?.toLowerCase()) {
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

// Load dashboard data when the page loads
document.addEventListener('DOMContentLoaded', loadDashboardData); 