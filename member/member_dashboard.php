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
                            <!-- Notification Bell Icon -->
                            <button class="relative focus:outline-none" title="Notifications">
                                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <!-- Example: Notification dot -->
                                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full ring-2 ring-white bg-red-500"></span>
                            </button>
                        </div>
                        </div>
                    </div>

                    <!-- Notification Modal -->
                    <div id="notificationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;">
                        <div style="width: 100%; max-width: 500px; background-color: white; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; max-height: 600px; margin: 20px;">
                            <!-- Modal Header -->
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #e5e7eb;">
                                <h3 style="font-size: 18px; font-weight: 600; color: #1f2937;">Notifications</h3>
                                <button id="closeNotificationModal" style="background: none; border: none; cursor: pointer; color: #6b7280; padding: 0; font-size: 20px;">
                                    ✕
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div style="flex: 1; overflow-y: auto; padding: 20px;">
                                <!-- Assigned Tasks Section -->
                                <div style="margin-bottom: 24px;">
                                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">New Task Assignments</h4>
                                    <ul id="assignedTasksList" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                        <li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>
                                    </ul>
                                </div>

                                <!-- Task Approval Notifications Section -->
                                <div style="margin-bottom: 24px;">
                                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Task Approval Status</h4>
                                    <ul id="taskApprovalList" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                        <li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>
                                    </ul>
                                </div>

                                <!-- Admin Messages Section -->
                                <div>
                                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Admin Messages</h4>
                                    <ul id="adminMessagesList" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                        <li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">No new messages</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div style="padding: 16px; border-top: 1px solid #e5e7eb; display: flex; gap: 8px;">
                                <button id="markAllRead" style="flex: 1; background-color: #2563eb; color: white; font-weight: 600; padding: 10px 16px; border-radius: 8px; border: none; cursor: pointer; transition: background-color 0.3s;">
                                    Mark All as Read
                                </button>
                                <button id="closeNotificationBtn" style="flex: 1; background-color: #e5e7eb; color: #374151; font-weight: 600; padding: 10px 16px; border-radius: 8px; border: none; cursor: pointer; transition: background-color 0.3s;">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Task Decline Reason Modal -->
                    <div id="declineResponseModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 10000; justify-content: center; align-items: center;">
                        <div style="width: 100%; max-width: 480px; background-color: white; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); display: flex; flex-direction: column; max-height: 420px; margin: 20px;">
                            <div style="padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">Task Decline Reason</h3>
                                    <p id="declineResponseTaskInfo" style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;"></p>
                                </div>
                                <button id="closeDeclineResponseModal" style="background: none; border: none; cursor: pointer; color: #6b7280; font-size: 20px; line-height: 1;">✕</button>
                            </div>
                            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                <p id="declineReasonContent" style="font-size: 14px; color: #111827; margin-bottom: 16px;">Loading reason...</p>
                            </div>
                            <div style="padding: 16px; border-top: 1px solid #e5e7eb; display: flex; gap: 8px;">
                                <button id="closeDeclineReasonBtn" style="flex: 1; background-color: #e5e7eb; color: #374151; font-weight: 600; padding: 10px 16px; border-radius: 8px; border: none; cursor: pointer; transition: background-color 0.3s;">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <!-- Content Row -->

                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4" style="max-height: 420px;">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">Recent Tasks</h6>
                                    <div class="btn-group" role="group" aria-label="Recent task filters">
                                        <button type="button" class="btn btn-light btn-sm recent-task-tab active" data-task-tab="assigned">Assigned Tasks</button>
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
                        <div class="col-xl-4 col-lg-5">
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
            if (assignedData) {
                if (assignedData.success === false) {
                    console.error('Backend reported error for assigned tasks:', assignedData.message);
                } else {
                    // Update card counts (these elements are removed, but the code still exists)
                    // They were commented out in the previous revert.
                    // This is to avoid errors if these elements are expected.
                    const pendingCountElement = document.getElementById('pendingTasksCount');
                    if (pendingCountElement) pendingCountElement.innerHTML = assignedData.pending_tasks ? assignedData.pending_tasks.length : 0;
                    
                    const inProgressCountElement = document.getElementById('inProgressTasksCount');
                    if (inProgressCountElement) inProgressCountElement.innerHTML = assignedData.in_progress_tasks ? assignedData.in_progress_tasks.length : 0;
                    
                    const completedCountElement = document.getElementById('completedTasksCount');
                    if (completedCountElement) completedCountElement.innerHTML = assignedData.completed_tasks ? assignedData.completed_tasks.length : 0;
                    

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
            }
            recentTasksState.assigned = assigned.slice(0, 8);

            const created = [];
            if (createdData) {
                if (createdData.success === false) {
                    console.error('Backend reported error for created tasks:', createdData.message);
                } else if (createdData.success && Array.isArray(createdData.tasks)) {
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

    const escapeHtml = (value = '') => {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const declineResponseModal = document.getElementById('declineResponseModal');
    const declineReasonContent = document.getElementById('declineReasonContent');
    const declineResponseTaskInfo = document.getElementById('declineResponseTaskInfo');
    const closeDeclineResponseModalBtn = document.getElementById('closeDeclineResponseModal');
    const closeDeclineReasonBtn = document.getElementById('closeDeclineReasonBtn');
    let activeDeclineId = null;

    function showDeclineReasonModal(details) {
        if (!declineResponseModal || !declineReasonContent) return;
        activeDeclineId = details.id; // Store for potential future use if needed
        if (declineResponseTaskInfo) {
            declineResponseTaskInfo.textContent = `${details.production} • ${details.productName} (${details.memberName})`;
        }
        declineReasonContent.innerHTML = escapeHtml(details.reason || 'No reason provided.');
        declineResponseModal.style.display = 'flex';
    }

    function hideDeclineReasonModal() {
        if (declineResponseModal) {
            declineResponseModal.style.display = 'none';
        }
        activeDeclineId = null;
        if (declineReasonContent) {
            declineReasonContent.innerHTML = 'Loading reason...';
        }
    }

    if (closeDeclineResponseModalBtn) {
        closeDeclineResponseModalBtn.addEventListener('click', hideDeclineReasonModal);
    }
    if (closeDeclineReasonBtn) {
        closeDeclineReasonBtn.addEventListener('click', hideDeclineReasonModal);
    }
    if (declineResponseModal) {
        declineResponseModal.addEventListener('click', function(e) {
            if (e.target === declineResponseModal) {
                hideDeclineReasonModal();
            }
        });
    }

    function updateNotifications() {
        console.log('Updating member notifications...');
        
        const assignedTasksList = document.getElementById('assignedTasksList');
        const taskApprovalList = document.getElementById('taskApprovalList');
        const adminMessagesList = document.getElementById('adminMessagesList');

        // Show loading states
        if (assignedTasksList) assignedTasksList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>';
        if (taskApprovalList) taskApprovalList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>';
        if (adminMessagesList) adminMessagesList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>';

        Promise.all([
            // Fetch new task assignments
            fetch('backend/end-points/get_assignments.php?status=pending')
                .then(r => r.json())
                .catch(e => {
                    console.error('Error fetching new task assignments:', e);
                    return { success: false, assignments: [] };
                }),
            // Fetch task approval status updates (assuming a new endpoint `get_task_approval_notifications.php`)
            fetch('backend/end-points/get_task_approval_notifications.php')
                .then(r => r.json())
                .catch(e => {
                    console.error('Error fetching task approval notifications:', e);
                    return { success: false, notifications: [] };
                }),
            // Fetch admin messages (assuming a new endpoint `get_admin_messages_for_member.php`)
            fetch('backend/end-points/get_admin_messages_for_member.php')
                .then(r => r.json())
                .catch(e => {
                    console.error('Error fetching admin messages:', e);
                    return { success: false, messages: [] };
                })
        ])
        .then(([assignedTasksData, taskApprovalData, adminMessagesData]) => {
            console.log('Member Notification data:', assignedTasksData, taskApprovalData, adminMessagesData);

            const notificationBell = document.querySelector('button[title="Notifications"]');
            if (!notificationBell) {
                console.error('Notification bell button not found!');
                return;
            }
            const notificationDot = notificationBell.querySelector('span');
            
            const assignedCount = (assignedTasksData && assignedTasksData.success && Array.isArray(assignedTasksData.assignments)) ? assignedTasksData.assignments.length : 0;
            const approvalCount = (taskApprovalData && taskApprovalData.success && Array.isArray(taskApprovalData.notifications)) ? taskApprovalData.notifications.length : 0;
            const messagesCount = (adminMessagesData && adminMessagesData.success && Array.isArray(adminMessagesData.messages)) ? adminMessagesData.messages.length : 0;

            const hasNotifications = assignedCount > 0 || approvalCount > 0 || messagesCount > 0;
            
            if (notificationDot) {
                notificationDot.style.display = hasNotifications ? 'block' : 'none';
            }

            // Render Assigned Tasks
            if (assignedTasksList) {
                if (assignedCount > 0) {
                    assignedTasksList.innerHTML = assignedTasksData.assignments.map(task => `
                        <li style="padding: 12px; background-color: #e8f5e9; border-radius: 6px; border: 1px solid #c8e6c9; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;" 
                            class="notification-item assigned-task-notification" 
                            data-task-id="${task.id}" 
                            data-prod-id="${task.prod_line_id}"
                            onmouseover="this.style.backgroundColor='#a5d6a7'" 
                            onmouseout="this.style.backgroundColor='#e8f5e9'">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <h4 style="font-weight: 600; color: #1b5e20; margin: 0; font-size: 14px;">New Assignment: ${escapeHtml(task.product_name)}</h4>
                                    <p style="font-size: 12px; color: #388e3c; margin: 4px 0 0 0;">Production ID: ${escapeHtml(task.prod_line_id)}</p>
                                    <p style="font-size: 12px; color: #388e3c; margin: 4px 0 0 0;">Role: ${escapeHtml(task.role)}</p>
                                    <p style="font-size: 12px; color: #388e3c; margin: 4px 0 0 0;">Deadline: ${new Date(task.deadline).toLocaleDateString()}</p>
                                </div>
                                <span style="padding: 4px 8px; background-color: #66bb6a; color: white; border-radius: 9999px; font-size: 12px; white-space: nowrap; margin-left: 8px;">New</span>
                            </div>
                        </li>
                    `).join('');

                    assignedTasksList.querySelectorAll('.assigned-task-notification').forEach(item => {
                        item.addEventListener('click', function() {
                            const taskId = this.dataset.taskId;
                            const prodId = this.dataset.prodId;
                            // Mark as read and redirect to production page for this task
                            markNotificationRead(taskId, 'assigned_task'); // Assuming notification_id is task.id and type
                            window.location.href = `production.php?tab=assigned&prod_id=${prodId}`;
                        });
                    });

                } else {
                    assignedTasksList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">No new task assignments</li>';
                }
            }

            // Render Task Approval Status Updates
            if (taskApprovalList) {
                if (approvalCount > 0) {
                    taskApprovalList.innerHTML = taskApprovalData.notifications.map(notif => {
                        const isApproved = notif.status === 'approved';
                        const bgColor = isApproved ? '#e0f2f7' : '#ffebee';
                        const borderColor = isApproved ? '#b2ebf2' : '#ffcdd2';
                        const textColor = isApproved ? '#006064' : '#c62828';
                        const badgeBg = isApproved ? '#4dd0e1' : '#ef5350';
                        const badgeText = isApproved ? 'Approved' : 'Rejected';

                        let declineReasonHtml = '';
                        if (notif.reason && !isApproved) {
                             declineReasonHtml = `
                                <button class="view-decline-reason-btn" 
                                    data-id="${notif.id}" 
                                    data-prod="${encodeURIComponent(notif.production_id || '')}" 
                                    data-product="${encodeURIComponent(notif.product_name || '')}" 
                                    data-member="${encodeURIComponent(notif.member_name || '')}"
                                    data-reason="${encodeURIComponent(notif.reason || '')}"
                                    style="align-self: flex-start; margin-top: 8px; padding: 6px 10px; background-color: #dc2626; color: white; border: none; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                    View Reason
                                </button>`;
                        }

                        return `
                            <li style="padding: 12px; background-color: ${bgColor}; border-radius: 6px; border: 1px solid ${borderColor}; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;" 
                                class="notification-item approval-notification" 
                                data-notification-id="${notif.id}"
                                onmouseover="this.style.backgroundColor='${isApproved ? '#80deea' : '#ef9a9a'}'" 
                                onmouseout="this.style.backgroundColor='${bgColor}'">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div style="flex: 1;">
                                        <h4 style="font-weight: 600; color: ${textColor}; margin: 0; font-size: 14px;">Task ${badgeText}: ${escapeHtml(notif.product_name)}</h4>
                                        <p style="font-size: 12px; color: ${textColor}; margin: 4px 0 0 0;">Production ID: ${escapeHtml(notif.production_id)}</p>
                                        <p style="font-size: 12px; color: ${textColor}; margin: 4px 0 0 0;">Submitted: ${new Date(notif.submitted_at).toLocaleDateString()}</p>
                                    </div>
                                    <span style="padding: 4px 8px; background-color: ${badgeBg}; color: white; border-radius: 9999px; font-size: 12px; white-space: nowrap; margin-left: 8px;">${badgeText}</span>
                                </div>
                                ${declineReasonHtml}
                            </li>
                        `;
                    }).join('');

                    taskApprovalList.querySelectorAll('.approval-notification').forEach(item => {
                        item.addEventListener('click', function() {
                            const notificationId = this.dataset.notificationId;
                            markNotificationRead(notificationId, 'task_approval');
                        });
                    });

                    taskApprovalList.querySelectorAll('.view-decline-reason-btn').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation(); // Prevent parent li click
                            const declineId = this.dataset.id;
                            const production = decodeURIComponent(this.dataset.prod || '');
                            const productName = decodeURIComponent(this.dataset.product || '');
                            const memberName = decodeURIComponent(this.dataset.member || '');
                            const reason = decodeURIComponent(this.dataset.reason || '');
                            showDeclineReasonModal({
                                id: declineId,
                                production,
                                productName,
                                memberName,
                                reason
                            });
                        });
                    });

                } else {
                    taskApprovalList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">No new task approval updates</li>';
                }
            }

            // Render Admin Messages
            if (adminMessagesList) {
                if (messagesCount > 0) {
                    adminMessagesList.innerHTML = adminMessagesData.messages.map(message => `
                        <li style="padding: 12px; background-color: #e3f2fd; border-radius: 6px; border: 1px solid #90caf9; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;" 
                            class="notification-item admin-message-notification" 
                            data-message-id="${message.id}"
                            onmouseover="this.style.backgroundColor='#64b5f6'" 
                            onmouseout="this.style.backgroundColor='#e3f2fd'">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <h4 style="font-weight: 600; color: #1976d2; margin: 0; font-size: 14px;">Admin Message: ${escapeHtml(message.title)}</h4>
                                    <p style="font-size: 12px; color: #2196f3; margin: 4px 0 0 0;">${escapeHtml(message.content)}</p>
                                    <p style="font-size: 11px; color: #2196f3; margin: 4px 0 0 0;">Sent: ${new Date(message.sent_at).toLocaleString()}</p>
                                </div>
                                <span style="padding: 4px 8px; background-color: #42a5f5; color: white; border-radius: 9999px; font-size: 12px; white-space: nowrap; margin-left: 8px;">Message</span>
                            </div>
                        </li>
                    `).join('');

                    adminMessagesList.querySelectorAll('.admin-message-notification').forEach(item => {
                        item.addEventListener('click', function() {
                            const messageId = this.dataset.messageId;
                            markNotificationRead(messageId, 'admin_message');
                            // Optionally, display full message in a modal
                        });
                    });

                } else {
                    adminMessagesList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">No new admin messages</li>';
                }
            }

        })
        .catch(error => {
            console.error('Error updating notifications:', error);
            if (assignedTasksList) assignedTasksList.innerHTML = '<li style="padding: 12px; color: #dc2626; text-align: center; font-size: 14px;">Error loading new task assignments</li>';
            if (taskApprovalList) taskApprovalList.innerHTML = '<li style="padding: 12px; color: #dc2626; text-align: center; font-size: 14px;">Error loading task approval notifications</li>';
            if (adminMessagesList) adminMessagesList.innerHTML = '<li style="padding: 12px; color: #dc2626; text-align: center; font-size: 14px;">Error loading admin messages</li>';
        });
    }

    // Initial check for notifications
    setTimeout(() => {
        updateNotifications();
    }, 500);

    // Check for new notifications every 30 seconds
    setInterval(updateNotifications, 30000);

    const notificationBell = document.querySelector('button[title="Notifications"]');
    if (notificationBell) {
        notificationBell.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = document.getElementById('notificationModal');
            if (modal) {
                const currentDisplay = modal.style.display;
                const isHidden = currentDisplay === 'none' || currentDisplay === '';
                modal.style.display = isHidden ? 'flex' : 'none';
                if (isHidden) {
                    updateNotifications(); // Refresh notifications when opening modal
                }
            }
        });
    } else {
        console.error('Notification bell button not found!');
    }

    const closeBtn = document.getElementById('closeNotificationModal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = document.getElementById('notificationModal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }

    const closeNotificationBtn = document.getElementById('closeNotificationBtn');
    if (closeNotificationBtn) {
        closeNotificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = document.getElementById('notificationModal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Prevent modal from showing on page load by ensuring display is none
    const modal = document.getElementById('notificationModal');
    if (modal && modal.style.display !== 'none') {
        modal.style.display = 'none';
    }

    // Close modal when clicking outside of it
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }

    // Function to mark a single notification as read
    window.markNotificationRead = function(notificationId, type) {
        console.log('Marking notification as read:', notificationId, type);
        fetch('backend/end-points/member_notifications.php?action=mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notificationId, type: type })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Mark read response:', data);
            if (data.success) {
                updateNotifications();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    };

    // Handle mark all as read button
    const markAllReadBtn = document.getElementById('markAllRead');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            console.log('Marking all member notifications as read');
            fetch('backend/end-points/member_notifications.php?action=mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Mark all read response:', data);
                if (data.success) {
                    updateNotifications();
                    if (typeof alertify !== 'undefined') {
                        alertify.success('All notifications marked as read');
                    }
                }
            })
            .catch(error => console.error('Error marking all notifications as read:', error));
        });
    }
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