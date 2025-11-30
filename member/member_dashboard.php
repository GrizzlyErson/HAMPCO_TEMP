<?php
require_once "../function/database.php";
require_once "components/header.php";

$db = new Database();
$current_status = 'available';

try {
    $stmt = $db->conn->prepare("SELECT availability_status FROM user_member WHERE id = ?");
    if ($stmt) {
        $member_id = $_SESSION['id'] ?? 0;
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $current_status = $row['availability_status'] ?? 'available';
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Failed to fetch availability status: " . $e->getMessage());
}
?>

<body class="hampco-admin-sidebar-layout">


    <!-- Begin Page Content -->
        <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                        <h1 class="h3 mb-0 text-gray-800">DASHBOARD  </h1>
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-cart-plus"></i>
                            <div class="bg-white rounded-lg shadow-sm p-2 flex space-x-2">
                                <button id="availableBtn" class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 <?php echo $current_status === 'available' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                                    Available
                                </button>
                                <button id="unavailableBtn" class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 <?php echo $current_status === 'unavailable' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                                    Unavailable
                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <!-- Content Row -->

                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-8">
                            <div class="card shadow mb-4" style="max-height: 420px;">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">Recent Tasks</h6>
                                    <div class="btn-group" role="group" aria-label="Recent task filters">
                                        <button type="button" class="btn btn-light btn-sm recent-task-tab active" data-task-tab="assigned">Assigned Tasks</button>
                                        <button type="button" class="btn btn-outline-light btn-sm recent-task-tab" data-task-tab="created">Task Created</button>
                                    </div>
                                </div>
                                <div>
                                    <div class="table-responsive" style="overflow-y: auto; max-height: 320px;">
                                    <table class="table mb-0" id="recentTasksTable">
                                        <thead>
                                        <tr>
                                            <th scope="col">Task ID</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Type</th>
                                        </tr>
                                        </thead>
                                        <tbody id="recentTasksBody">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Loading...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                        </div>

                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-4">
                            <div class="card shadow mb-4" >
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">Task Progress</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body" style="height: 350px;">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="taskProgressChart"></canvas>
                                    </div>
                                    <!-- Custom HTML Legend -->
                                    <div id="taskProgressLegend" class="flex justify-center flex-wrap gap-x-4 gap-y-2 mt-4"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Content Column -->
                        <div class="col-lg-12 mb-4">

                            <!-- Task Created Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">📝 My Tasks Created</h6>
                                </div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0" id="taskCreatedTable">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th scope="col" style="font-size: 12px;">Production ID</th>
                                                    <th scope="col" style="font-size: 12px;">Product Name</th>
                                                    <th scope="col" style="font-size: 12px;">Weight (g)</th>
                                                    <th scope="col" style="font-size: 12px;">Status</th>
                                                    <th scope="col" style="font-size: 12px;">Raw Materials</th>
                                                    <th scope="col" style="font-size: 12px;">Date Created</th>
                                                    <th scope="col" style="font-size: 12px;">Date Submitted</th>
                                                    <th scope="col" style="font-size: 12px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="taskCreatedTableBody">
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-3">Loading tasks...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                        
                    </div>

                </div>





    
<script>
document.addEventListener('DOMContentLoaded', function() {
    const availableBtn = document.getElementById('availableBtn');
    const unavailableBtn = document.getElementById('unavailableBtn');
    const recentTasksBody = document.getElementById('recentTasksBody');
    const recentTaskTabs = document.querySelectorAll('.recent-task-tab');
    let activeTaskTab = 'assigned';
    const recentTasksState = {
        assigned: [],
        created: []
    };

    function updateAvailabilityStatus(status) {
        if (availableBtn) availableBtn.disabled = true;
        if (unavailableBtn) unavailableBtn.disabled = true;

        fetch('backend/end-points/update_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (status === 'available') {
                    if (availableBtn) {
                        availableBtn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                        availableBtn.classList.add('bg-green-500', 'text-white');
                    }
                    if (unavailableBtn) {
                        unavailableBtn.classList.remove('bg-red-500', 'text-white');
                        unavailableBtn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    }
                } else {
                    if (unavailableBtn) {
                        unavailableBtn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                        unavailableBtn.classList.add('bg-red-500', 'text-white');
                    }
                    if (availableBtn) {
                        availableBtn.classList.remove('bg-green-500', 'text-white');
                        availableBtn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Status Updated',
                    text: `Your status has been set to ${status}`,
                    timer: 1800,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: data.message || 'Failed to update status'
                });
            }
        })
        .catch(error => {
            console.error('Error updating availability:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating status'
            });
        })
        .finally(() => {
            if (availableBtn) availableBtn.disabled = false;
            if (unavailableBtn) unavailableBtn.disabled = false;
        });
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(value);
        return isNaN(date) ? '—' : date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatStatus(status) {
        if (!status) return 'Unknown';
        return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
    }

    function buildStatusBadge(status) {
        const normalized = status.toLowerCase();
        let badgeClass = 'badge-secondary';
        if (normalized.includes('pending')) badgeClass = 'badge-warning';
        else if (normalized.includes('progress')) badgeClass = 'badge-info';
        else if (normalized.includes('completed') || normalized.includes('approved')) badgeClass = 'badge-success';
        else if (normalized.includes('declined') || normalized.includes('rejected')) badgeClass = 'badge-danger';
        return `<span class="badge ${badgeClass}">${formatStatus(status)}</span>`;
    }

    function renderRecentTasks(tab) {
        if (!recentTasksBody) return;
        const tasks = recentTasksState[tab] || [];
        if (tasks.length === 0) {
            recentTasksBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        No ${tab === 'assigned' ? 'assigned' : 'created'} tasks to display.
                    </td>
                </tr>
            `;
            return;
        }

        recentTasksBody.innerHTML = tasks.map(task => `
            <tr>
                <td>${task.display_id || '-'}</td>
                <td>${task.product_name || 'N/A'}</td>
                <td>${buildStatusBadge(task.status)}</td>
                <td>${formatDate(task.date)}</td>
                <td><span class="badge badge-light">${task.type}</span></td>
            </tr>
        `).join('');
    }

    function setActiveTaskTab(tab) {
        activeTaskTab = tab;
        recentTaskTabs.forEach(btn => {
            if (btn.dataset.taskTab === tab) {
                btn.classList.add('active', 'btn-light');
                btn.classList.remove('btn-outline-light');
            } else {
                btn.classList.remove('active', 'btn-light');
                btn.classList.add('btn-outline-light');
            }
        });
        renderRecentTasks(tab);
    }

    function fetchRecentTasks() {
        if (!recentTasksBody) return;
        recentTasksBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-3">Loading tasks...</td>
            </tr>
        `;

        Promise.all([
            fetch('backend/end-points/get_production_tasks.php')
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .catch(err => {
                    console.error('Error fetching production tasks:', err);
                    return { success: false };
                }),
            fetch('backend/end-points/get_created_tasks.php')
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .catch(err => {
                    console.error('Error fetching created tasks:', err);
                    return { success: false };
                })
        ])
        .then(([assignedData, createdData]) => {
            console.log('Assigned tasks data:', assignedData);
            console.log('Created tasks data:', createdData);

            const assigned = [];
            if (assignedData && assignedData.success) {
                // Update card counts
                document.getElementById('pendingTasksCount').innerHTML = assignedData.pending_tasks ? assignedData.pending_tasks.length : 0;
                document.getElementById('inProgressTasksCount').innerHTML = assignedData.in_progress_tasks ? assignedData.in_progress_tasks.length : 0;
                document.getElementById('completedTasksCount').innerHTML = assignedData.completed_tasks ? assignedData.completed_tasks.length : 0;

                // Populate assigned tasks for the table (combining pending, in-progress, completed for display)
                const allAssignedTasks = [
                    ...(assignedData.pending_tasks || []),
                    ...(assignedData.in_progress_tasks || []),
                    ...(assignedData.completed_tasks || [])
                ];

                allAssignedTasks.forEach(task => {
                    assigned.push({
                        display_id: task.display_id || `PL${String(task.prod_line_id).padStart(4, '0')}`,
                        product_name: task.product_name,
                        status: task.status || task.task_status || 'pending',
                        date: task.deadline || task.date_started,
                        type: 'Assigned Task'
                    });
                });
            }
            recentTasksState.assigned = assigned.slice(0, 8);

            const created = [];
            if (createdData && createdData.success && Array.isArray(createdData.tasks)) {
                createdData.tasks.forEach(task => {
                    created.push({
                        display_id: task.display_id || `PL${String(task.prod_line_id).padStart(4, '0')}`,
                        product_name: task.product_name,
                        status: task.status || 'Created',
                        date: task.date_added || task.date_submitted,
                        type: 'Created'
                    });
                });
            }
            recentTasksState.created = created.slice(0, 8);

            console.log('State assigned:', recentTasksState.assigned);
            // --- Chart.js Task Progress Chart ---
            const ctx = document.getElementById('taskProgressChart');
            if (ctx) {
                // Destroy existing chart if it exists
                if (window.taskProgressDoughnutChart) {
                    window.taskProgressDoughnutChart.destroy();
                }

                const pendingCount = assignedData.pending_tasks ? assignedData.pending_tasks.length : 0;
                const completedCount = assignedData.completed_tasks ? assignedData.completed_tasks.length : 0;
                const inProgressCount = assignedData.in_progress_tasks ? assignedData.in_progress_tasks.length : 0;

                window.taskProgressDoughnutChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending', 'In Progress', 'Completed'],
                        datasets: [{
                            data: [pendingCount, inProgressCount, completedCount],
                            backgroundColor: ['#fd7e14', '#17a2b8', '#28a745'], // Orange for pending, Blue for in-progress, Green for completed
                            hoverBackgroundColor: ['#e66b00', '#138496', '#218838'],
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        tooltips: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            caretPadding: 10,
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    const dataset = data.datasets[tooltipItem.datasetIndex];
                                    const total = dataset.data.reduce((previousValue, currentValue) => previousValue + currentValue);
                                    const currentValue = dataset.data[tooltipItem.index];
                                    const percentage = parseFloat(((currentValue / total) * 100).toFixed(1));
                                    const label = data.labels[tooltipItem.index];
                                    return label + ': ' + currentValue + ' (' + percentage + '%)';
                                }
                            }
                        },
                        legend: {
                            display: false, // Disable Chart.js's internal legend
                        },
                        cutoutPercentage: 70,
                    },
                });
                // --- Custom HTML Legend Generation ---
                const taskProgressLegendContainer = document.getElementById('taskProgressLegend');
                if (taskProgressLegendContainer) {
                    taskProgressLegendContainer.innerHTML = ''; // Clear previous legend items
                    const chartData = window.taskProgressDoughnutChart.data;
                    const total = chartData.datasets[0].data.reduce((sum, val) => sum + val, 0);

                    chartData.labels.forEach((label, index) => {
                        const color = chartData.datasets[0].backgroundColor[index];
                        const value = chartData.datasets[0].data[index];
                        const percentage = total > 0 ? parseFloat(((value / total) * 100).toFixed(1)) : 0;

                        const legendItem = document.createElement('div');
                        legendItem.className = 'flex items-center space-x-2 text-sm text-gray-700';
                        legendItem.innerHTML = `
                            <span class="w-3 h-3 rounded-full" style="background-color: ${color};"></span>
                            <span>${label}: ${value} (${percentage}%)</span>
                        `;
                        taskProgressLegendContainer.appendChild(legendItem);
                    });
                }
            }

            renderRecentTasks(activeTaskTab);
        })
        .catch(error => {
            console.error('Error loading recent tasks:', error);
            recentTasksBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger py-3">
                        Failed to load tasks. Please try again later.
                    </td>
                </tr>
            `;
        });
    }

    if (availableBtn && unavailableBtn) {
        availableBtn.addEventListener('click', () => updateAvailabilityStatus('available'));
        unavailableBtn.addEventListener('click', () => updateAvailabilityStatus('unavailable'));
    }

    recentTaskTabs.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.taskTab;
            if (tab !== activeTaskTab) {
                setActiveTaskTab(tab);
            }
        });
    });

    setActiveTaskTab('assigned');
    fetchRecentTasks();
    loadTasksCreated();
    setInterval(fetchRecentTasks, 60000);
    setInterval(loadTasksCreated, 60000);
});

// Load tasks created by the member
function loadTasksCreated() {
    const tableBody = document.getElementById('taskCreatedTableBody');
    if (!tableBody) return;

    fetch('backend/end-points/get_self_tasks.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success && data.tasks && Array.isArray(data.tasks)) {
                if (data.tasks.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No tasks created yet</td></tr>';
                    return;
                }

                tableBody.innerHTML = data.tasks.map(task => {
                    const statusBadgeClass = task.status === 'completed' ? 'badge-success' :
                                            task.status === 'submitted' ? 'badge-warning' :
                                            task.status === 'in_progress' ? 'badge-info' :
                                            'badge-secondary';

                    const statusLabel = task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ');

                    return `
                        <tr>
                            <td style="font-size: 12px; padding: 6px 10px; white-space: nowrap;">${task.production_id || '-'}</td>
                            <td style="font-size: 12px; padding: 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px;">${task.product_name}</td>
                            <td style="font-size: 12px; padding: 6px 10px; white-space: nowrap; text-align: center;">${task.weight_g}</td>
                            <td style="font-size: 12px; padding: 6px 10px; white-space: nowrap;">
                                <span class="badge ${statusBadgeClass}" style="font-size: 11px; padding: 3px 6px;">${statusLabel}</span>
                            </td>
                            <td style="font-size: 12px; padding: 6px 10px; white-space: nowrap;">
                                <button onclick="viewTaskMaterials('${task.product_name}', ${task.weight_g})" 
                                    class="btn btn-sm btn-info" style="font-size: 11px; padding: 2px 6px;">
                                    View
                                </button>
                            </td>
                            <td style="font-size: 12px; padding: 6px 10px; white-space: nowrap; color: #666;">
                                ${task.date_created ? new Date(task.date_created).toLocaleDateString() : '-'}
                            </td>
                            <td style="font-size: 12px; padding: 6px 10px; white-space: nowrap; color: #666;">
                                ${task.date_submitted ? new Date(task.date_submitted).toLocaleDateString() : '-'}
                            </td>
                            <td style="font-size: 12px; padding: 6px 10px; white-space: nowrap;">
                                <a href="production.php?tab=created" class="btn btn-sm btn-primary" style="font-size: 11px; padding: 2px 6px;">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No tasks created yet</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading created tasks:', error);
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-3">Error loading tasks</td></tr>';
        });
}

// View materials function
function viewTaskMaterials(productName, weight) {
    fetch(`production.php?action=view_materials&product=${encodeURIComponent(productName)}&weight=${weight}`)
        .then(response => response.text())
        .then(html => {
            alert(`Materials for ${productName} (${weight}g)`);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error viewing materials');
        });
}
</script>

<?php require_once "components/footer.php"; ?>