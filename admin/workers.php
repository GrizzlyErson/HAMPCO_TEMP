<?php
include 'components/header.php';
?>

<div class="p-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Worker Management</h1>

        <!-- Filter Section -->
        <div class="bg-gray-50 p-6 rounded-lg mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Filter Workers</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">All Workers</option>
                        <option value="active">Active Workers</option>
                        <option value="lazy">Lazy Workers</option>
                    </select>
                </div>

                <!-- Min Tasks Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Min Tasks Completed</label>
                    <input type="number" id="minTasksFilter" min="0" placeholder="0" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Date Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" id="fromDateFilter" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" id="toDateFilter" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <button onclick="applyFilters()" class="mt-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                Apply Filters
            </button>
            <button onclick="resetFilters()" class="mt-4 ml-2 bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-semibold transition">
                Reset
            </button>
        </div>

        <!-- Workers Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse" id="workersTable">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">ID</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Role</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-700">Tasks Completed</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-700">Days Worked</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-700">Last Active</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody id="workersTableBody" class="divide-y divide-gray-200">
                    <!-- Will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-8 hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M13 16H9m4-4H9m6-4h.01M9 16h.01" />
            </svg>
            <p class="text-gray-500 text-lg">No workers found matching the filters.</p>
        </div>
    </div>
</div>

<script>
// Sample workers data (replace with actual data from backend)
const workersData = [
    {
        id: 1,
        name: 'Maria Santos',
        role: 'Knotter',
        tasksCompleted: 45,
        daysWorked: 60,
        lastActive: '2025-11-25',
        joinDate: '2025-09-01',
        averageTasksPerDay: 0.75,
        status: 'active'
    },
    {
        id: 2,
        name: 'Juan Dela Cruz',
        role: 'Weaver',
        tasksCompleted: 12,
        daysWorked: 55,
        lastActive: '2025-11-20',
        joinDate: '2025-09-05',
        averageTasksPerDay: 0.22,
        status: 'lazy'
    },
    {
        id: 3,
        name: 'Rosa Garcia',
        role: 'Warper',
        tasksCompleted: 38,
        daysWorked: 45,
        lastActive: '2025-11-24',
        joinDate: '2025-09-10',
        averageTasksPerDay: 0.84,
        status: 'active'
    },
    {
        id: 4,
        name: 'Pedro Reyes',
        role: 'Knotter',
        tasksCompleted: 8,
        daysWorked: 50,
        lastActive: '2025-11-18',
        joinDate: '2025-09-15',
        averageTasksPerDay: 0.16,
        status: 'lazy'
    },
    {
        id: 5,
        name: 'Ana Lopez',
        role: 'Weaver',
        tasksCompleted: 52,
        daysWorked: 65,
        lastActive: '2025-11-25',
        joinDate: '2025-08-20',
        averageTasksPerDay: 0.80,
        status: 'active'
    }
];

function renderWorkers(workers) {
    const tbody = document.getElementById('workersTableBody');
    const emptyState = document.getElementById('emptyState');

    if (workers.length === 0) {
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');
    tbody.innerHTML = workers.map(worker => `
        <tr class="hover:bg-gray-50 transition">
            <td class="px-6 py-3 text-gray-800 font-semibold">#${worker.id.toString().padStart(3, '0')}</td>
            <td class="px-6 py-3 text-gray-800">${worker.name}</td>
            <td class="px-6 py-3 text-gray-800">
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    ${worker.role === 'Knotter' ? 'bg-indigo-100 text-indigo-800' : 
                      worker.role === 'Weaver' ? 'bg-blue-100 text-blue-800' : 
                      'bg-green-100 text-green-800'}">
                    ${worker.role}
                </span>
            </td>
            <td class="px-6 py-3 text-center">
                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-semibold text-sm">
                    ${worker.tasksCompleted}
                </span>
            </td>
            <td class="px-6 py-3 text-center text-gray-800">
                <span class="font-semibold">${worker.daysWorked}</span> days
            </td>
            <td class="px-6 py-3 text-center text-gray-800">
                ${formatDate(worker.lastActive)}
            </td>
            <td class="px-6 py-3 text-center">
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    ${worker.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${worker.status === 'active' ? '✓ Active' : '⚠ Lazy'}
                </span>
            </td>
            <td class="px-6 py-3 text-center">
                <button onclick="viewDetails(${worker.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-semibold transition mr-2">
                    View
                </button>
                <button onclick="editWorker(${worker.id})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold transition">
                    Edit
                </button>
            </td>
        </tr>
    `).join('');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const diffTime = Math.abs(today - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays}d ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)}w ago`;
    return dateString;
}

function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const minTasks = parseInt(document.getElementById('minTasksFilter').value) || 0;
    const fromDate = document.getElementById('fromDateFilter').value;
    const toDate = document.getElementById('toDateFilter').value;

    let filtered = workersData.filter(worker => {
        // Status filter
        if (status && worker.status !== status) return false;

        // Tasks filter
        if (worker.tasksCompleted < minTasks) return false;

        // Date range filter
        if (fromDate && new Date(worker.lastActive) < new Date(fromDate)) return false;
        if (toDate && new Date(worker.lastActive) > new Date(toDate)) return false;

        return true;
    });

    renderWorkers(filtered);
}

function resetFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('minTasksFilter').value = '';
    document.getElementById('fromDateFilter').value = '';
    document.getElementById('toDateFilter').value = '';
    renderWorkers(workersData);
}

function viewDetails(workerId) {
    const worker = workersData.find(w => w.id === workerId);
    alert(`Worker Details:\n\nName: ${worker.name}\nRole: ${worker.role}\nTasks: ${worker.tasksCompleted}\nDays Worked: ${worker.daysWorked}\nAvg Tasks/Day: ${worker.averageTasksPerDay.toFixed(2)}`);
}

function editWorker(workerId) {
    alert(`Edit functionality for worker #${workerId}`);
}

// Initial render
renderWorkers(workersData);
</script>