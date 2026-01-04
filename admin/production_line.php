<?php 
include "components/header.php";
// Add custom CSS for tables
echo '<link rel="stylesheet" href="css/table-styles.css">';

// Fetch assigned tasks
$tasks_query = "SELECT 
    pl.prod_line_id,
    pl.product_name,
    pl.length_m,
    pl.width_m,
    pl.weight_g,
    pl.quantity,
    pl.date_created,
    pl.status,
    ta.id as task_id,
    ta.member_id,
    ta.role,
    ta.status as task_status,
    ta.deadline,
    um.fullname as member_name
FROM production_line pl
LEFT JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
LEFT JOIN user_member um ON ta.member_id = um.id
WHERE ta.status != 'completed' OR ta.status IS NULL
ORDER BY pl.date_created DESC";
$tasks_result = mysqli_query($db->conn, $tasks_query);

// Initialize RawMaterialCalculator
require_once 'backend/raw_material_calculator.php';
$materialCalculator = new RawMaterialCalculator($db);

// Get production line data
$production_query = "SELECT pl.*, 
    (SELECT COUNT(*) FROM task_assignments ta WHERE ta.prod_line_id = pl.prod_line_id) as has_assignments
    FROM production_line pl
    WHERE pl.status != 'completed'
    AND pl.prod_line_id NOT IN (
        -- Exclude production lines that have any completed tasks
        SELECT DISTINCT ta.prod_line_id 
        FROM task_assignments ta 
        WHERE ta.status = 'completed'
    )
    ORDER BY pl.date_created DESC";
$production_result = mysqli_query($db->conn, $production_query);
$production_items = [];
while ($row = mysqli_fetch_assoc($production_result)) {
    $production_items[] = [
        'display_id' => 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT),
        'raw_id' => $row['prod_line_id'], // Add the raw ID for delete function
        'product_name' => $row['product_name'],
        'length_m' => $row['length_m'],
        'width_m' => $row['width_m'],
        'weight_g' => $row['weight_g'],
        'quantity' => $row['quantity'],
        'date_created' => date('Y-m-d H:i', strtotime($row['date_created'])),
        'status' => $row['status'],
        'has_assignments' => (int)$row['has_assignments'] > 0
    ];
}
?>

<!-- Materials Modal -->
<div id="materialsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-[1000] hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Raw Materials Information</h2>
            <button id="closeMaterialsModal" class="text-gray-400 hover:text-gray-600">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div id="materialsContent" class="max-h-[70vh] overflow-y-auto"></div>
    </div>
</div>

<script>


function renderMemberList(role, listId) {
    fetch('backend/end-points/get_members_by_role.php?role=' + role)
        .then(response => response.json())
        .then(members => {
            const list = document.getElementById(listId);
            if (!list) {
                console.error('List element not found:', listId);
                return;
            }
            
            list.innerHTML = '';
            let found = false;
            
            if (Array.isArray(members) && members.length > 0) {
                members.forEach(member => {
                    // Only show available members
                    if (member.work_status === 'Available') {
                        found = true;
                        const name = member.fullname;
                        const status = member.work_status;
                        const badgeClass = (status === 'Work In Progress' || status === 'Occupied (Pending)') ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                        const li = document.createElement('li');
                        li.className = 'flex items-center justify-between py-2';
                        li.innerHTML = `
                            <span class="font-medium">${name}</span>
                            <span class="ml-2 px-2 py-1 rounded-full text-xs font-semibold ${badgeClass}">${status}</span>
                        `;
                        list.appendChild(li);
                    }
                });
            }
            
            if (!found) {
                list.innerHTML = '<li class="text-gray-400">No members found.</li>';
            }
        })
        .catch(error => {
            console.error('Error loading members:', error);
            const list = document.getElementById(listId);
            if (list) {
                list.innerHTML = '<li class="text-gray-400">Error loading members.</li>';
            }
        });
}

function fetchProductionLineData() {
    // This function is no longer needed as data is fetched in PHP
}

// Function to confirm task completion
function confirmTaskCompletion(prodLineId) {
    Swal.fire({
        title: 'Confirm Task Completion',
        text: 'Are you sure you want to mark this task as completed? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#EF4444',
        confirmButtonText: 'Yes, complete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Marking task as completed',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('backend/end-points/confirm_task_completion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `prod_line_id=${prodLineId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Task has been marked as completed.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Refresh the tasks table
                        refreshTaskAssignments();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to mark task as completed'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while marking the task as completed'
                });
            });
        }
    });
}

// Function to refresh task assignments
function refreshTaskAssignments() {
    fetch('backend/end-points/get_task_assignments.php')
        .then(response => response.json())
        .then(response => {
            if (!response.success) {
                throw new Error(response.message);
            }
            
            const inProgressTableBody = document.querySelector('#inProgressTasksTable tbody');
            const completedTableBody = document.querySelector('#completedTasksTable tbody');
            
            if (!inProgressTableBody || !completedTableBody) return;
            
            inProgressTableBody.innerHTML = '';
            completedTableBody.innerHTML = '';

            let inProgressTasksFound = 0;
            let completedTasksFound = 0;
            
            response.data.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                const assignedMembersHtml = item.assignments.map(assignment => {
                    if (!assignment.member_name) return '';
                    return `
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="font-medium">${assignment.member_name}</span>
                            <span class="text-gray-500">(${assignment.role})</span>
                        </div>
                    `;
                }).join('');

                const taskStatuses = item.assignments.map(a => a.task_status);
                let displayStatus = item.status;
                if (taskStatuses.includes('in_progress')) {
                    displayStatus = 'in_progress';
                } else if (taskStatuses.includes('submitted')) {
                    displayStatus = 'submitted';
                } else if (taskStatuses.includes('pending')) {
                    displayStatus = 'pending';
                }

                const statusClass = displayStatus === 'completed' ? 'bg-green-100 text-green-800' :
                                  displayStatus === 'submitted' ? 'bg-purple-100 text-purple-800' :
                                  displayStatus === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                  'bg-yellow-100 text-yellow-800';

                if (displayStatus === 'completed') {
                    completedTasksFound++;
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${item.prod_line_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.product_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                ${displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.date_created}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            ${assignedMembersHtml || 'No members assigned'}
                        </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            ${item.assignments[0]?.completion_date ? item.assignments[0].completion_date : '-'}
                        </td>
                    `;
                    completedTableBody.appendChild(row);
                } else {
                    inProgressTasksFound++;
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${item.prod_line_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.product_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                ${displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.date_created}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${assignedMembersHtml || 'No members assigned'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex flex-col items-center space-y-2">
                                ${item.assignments.map(assignment => {
                                    if (assignment.decline_status && assignment.decline_status !== null) {
                                        return `
                                            <button onclick="reassignDeclinedTask('${item.raw_id}', '${assignment.task_id}', '${item.product_name}', '${assignment.decline_id}')" 
                                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition-colors w-full">
                                                Reassign Declined Task
                                            </button>
                                        `;
                                    } else if (assignment.task_status === 'submitted') {
                                        return `
                                            <button onclick="confirmTaskCompletion('${item.raw_id}')" 
                                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition-colors w-full">
                                                Confirm Task Completion
                                            </button>
                                        `;
                                    } else if (assignment.task_status === 'pending') {
                                        return `
                                            <button onclick="openReassignModal('${item.raw_id}', '${assignment.task_id}', '${item.product_name}', '${assignment.member_id}')" 
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors w-full">
                                                Reassign
                                            </button>
                                        `;
                                    }
                                    return '';
                                }).join('')}
                                ${item.assignments.filter(a => a.decline_status || a.task_status === 'submitted' || a.task_status === 'pending').length === 0 ? '-' : ''}
                            </div>
                        </td>
                    `;
                    inProgressTableBody.appendChild(row);
                }
            });

            if (inProgressTasksFound === 0) {
                inProgressTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No in-progress tasks assigned yet.
                        </td>
                    </tr>
                `;
            }
            if (completedTasksFound === 0) {
                completedTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No completed tasks found.
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching task assignments:', error);
            const inProgressTableBody = document.querySelector('#inProgressTasksTable tbody');
            const completedTableBody = document.querySelector('#completedTasksTable tbody');
            if (inProgressTableBody) {
                inProgressTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-red-600">
                            Error loading in-progress tasks: ${error.message}
                        </td>
                    </tr>
                `;
            }
            if (completedTableBody) {
                completedTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-red-600">
                            Error loading completed tasks: ${error.message}
                        </td>
                    </tr>
                `;
            }
        });
}

// Function to refresh task approval requests
function refreshTaskApprovalRequests() {
    const tableBody = document.querySelector('#taskApprovalTable tbody');
    if (!tableBody) return;

    fetch('backend/end-points/get_task_requests.php')
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No requests found</td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = data.map(request => {
                const statusClass = request.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                  request.status === 'approved' ? 'bg-green-100 text-green-800' :
                                  'bg-red-100 text-red-800';

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${request.production_id || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.member_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.role}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.product_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.weight_g || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.date_created}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full font-medium ${statusClass}">
                                ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            ${request.status === 'pending' ? `
                                <div class="flex flex-col space-y-2">
                                    <button onclick="handleTaskRequest(${request.request_id}, 'approve')"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md w-full">
                                        Approve
                                    </button>
                                    <button onclick="handleTaskRequest(${request.request_id}, 'reject')"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md w-full">
                                        Reject
                                    </button>
                                </div>
                            ` : '-'}
                        </td>
                    </tr>
                `;
            }).join('');
        })
        .catch(error => {
            console.error('Error fetching task approval requests:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-sm text-red-600">
                        Error loading data: ${error.message}
                    </td>
                </tr>
            `;
        });
}

// Function to handle task request approval/rejection
function handleTaskRequest(requestId, action) {
    // Show loading state
    Swal.fire({
        title: 'Processing...',
        text: `${action === 'approve' ? 'Approving' : 'Rejecting'} task request`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Send request to backend
    fetch('backend/end-points/handle_task_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `request_id=${requestId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: `Task request has been ${action}ed.`,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Refresh the task requests table
                refreshTaskApprovalRequests();
            });
        } else {
            throw new Error(data.message || `Failed to ${action} task request`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || `Failed to ${action} task request. Please try again.`
        });
    });
}

// Call refreshTaskAssignments and refreshTaskApprovalRequests initially and set up periodic updates
document.addEventListener('DOMContentLoaded', function() {
    refreshTaskAssignments();
    refreshTaskApprovalRequests();
    // Update every 30 seconds
    setInterval(refreshTaskAssignments, 30000);
    setInterval(refreshTaskApprovalRequests, 30000);
});

function showMaterialsModal(materials, product) {
    const modal = document.getElementById('materialsModal');
    const content = document.getElementById('materialsContent');
    const modalTitle = modal.querySelector('h2');
    
    if (!modal || !content) {
        console.error('Modal elements not found');
        return;
    }

    let html = '';

    try {
        const isDimensionsProduct = ['Piña Seda', 'Pure Piña Cloth'].includes(product.name);
        const isKnottedProduct = ['Knotted Liniwan', 'Knotted Bastos'].includes(product.name);
        const isWarpedSilk = product.name === 'Warped Silk';

        // Set modal title based on product type
        modalTitle.textContent = isDimensionsProduct ? 'Processed Materials Information' : 'Raw Materials Information';

        // Product Information Section
        html += `
            <div class="mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold mb-2">Product Information</h3>
                <div class="grid grid-cols-1 gap-2">`;

        // Always show product name
        html += `<div><strong>Product:</strong> ${product.name}</div>`;

        // Show specific details based on product type
        if (isKnottedProduct || isWarpedSilk) {
            // For Knotted products and Warped Silk, only show weight
            html += `<div><strong>Weight:</strong> ${product.weight} g</div>`;
        } else if (isDimensionsProduct) {
            // For dimension-based products
            html += `
                <div><strong>Length:</strong> ${product.length} m</div>
                <div><strong>Width:</strong> ${product.width} in</div>
                <div><strong>Quantity:</strong> ${product.quantity} unit(s)</div>`;
        } else {
            // For other products
            html += `
                <div><strong>Weight:</strong> ${product.weight} g</div>
                <div><strong>Quantity:</strong> ${product.quantity} unit(s)</div>`;
        }

        html += `</div></div>`;

        // Materials Section - title changes based on product type
        html += `
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">${isDimensionsProduct ? 'Processed Materials Required' : 'Raw Materials Required'}</h3>`;

        if (materials && materials.success && Array.isArray(materials.materials) && materials.materials.length > 0) {
            html += `<div class="space-y-4">`;
            materials.materials.forEach(material => {
                html += `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">${material.name}${material.category ? ` (${material.category})` : ''}</span>
                            <span class="text-lg font-semibold">${material.amount} ${material.unit}</span>
                        </div>
                    </div>`;
            });
            html += `</div>`;
        } else {
            html += `
                <div class="text-gray-500 italic">
                    ${materials.error || 'No materials information available.'}
                </div>`;
        }
        html += `</div>`;

        content.innerHTML = html;
        modal.classList.remove('hidden');
    } catch (error) {
        console.error('Error rendering materials modal:', error);
        content.innerHTML = `
            <div class="text-red-600">
                Error displaying materials information: ${error.message}
            </div>`;
    }
}

// Close modal handler
document.getElementById('closeMaterialsModal')?.addEventListener('click', () => {
    document.getElementById('materialsModal')?.classList.add('hidden');
});

function completeTask(taskId) {
    if (confirm('Are you sure you want to mark this task as completed? This action cannot be undone.')) {
        fetch('backend/end-points/update_task_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'task_id=' + taskId + '&status=completed'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Task marked as completed successfully!');
                // Refresh the assigned tasks table to remove completed tasks
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the task.');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    renderMemberList('knotter', 'knotterList');
    renderMemberList('warper', 'warperList');
    renderMemberList('weaver', 'weaverList');
    setInterval(() => {
        renderMemberList('knotter', 'knotterList');
        renderMemberList('warper', 'warperList');
        renderMemberList('weaver', 'weaverList');
    }, 30000);
    refreshTaskAssignments();
    
    // Get the active tab from URL or localStorage
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || localStorage.getItem('activeTab') || 'monitoring';
    
    // Tabs
    const monitoringTab = document.getElementById('monitoringTab');
    const tasksTab = document.getElementById('tasksTab');
    const workforceTab = document.getElementById('workforceTab');
    const monitoringContent = document.getElementById('monitoringContent');
    const tasksContent = document.getElementById('tasksContent');
    const workforceContent = document.getElementById('workforceContent');
    const memberTaskRequestsTab = document.getElementById('memberTaskRequestsTab');
    const memberTaskRequestsContent = document.getElementById('memberTaskRequestsContent');

    function switchTab(activeTab, activeContent, ...inactive) {
        if (activeTab) {
            activeTab.classList.add('border-indigo-500', 'text-indigo-600');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
        }
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }
        for (let i = 0; i < inactive.length; i += 2) {
            const tab = inactive[i];
            const content = inactive[i+1];
            if (tab) {
                tab.classList.remove('border-indigo-500', 'text-indigo-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            }
            if (content) {
                content.classList.add('hidden');
            }
        }
    }

    // Function to update URL without reloading
    function updateURL(tabName) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
        localStorage.setItem('activeTab', tabName);
    }

    if (monitoringTab && monitoringContent && tasksTab && tasksContent && workforceTab && workforceContent && memberTaskRequestsTab && memberTaskRequestsContent) {
        // Set initial active tab
        switch(activeTab) {
            case 'tasks':
                switchTab(tasksTab, tasksContent, monitoringTab, monitoringContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
                break;
            case 'workforce':
                switchTab(workforceTab, workforceContent, monitoringTab, monitoringContent, tasksTab, tasksContent, memberTaskRequestsTab, memberTaskRequestsContent);
                break;
            case 'memberTaskRequests':
                switchTab(memberTaskRequestsTab, memberTaskRequestsContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent);
                break;
            default: // 'monitoring'
                switchTab(monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
        }

        monitoringTab.addEventListener('click', () => {
            switchTab(monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
            updateURL('monitoring');
        });
        tasksTab.addEventListener('click', () => {
            switchTab(tasksTab, tasksContent, monitoringTab, monitoringContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
            updateURL('tasks');
        });
        workforceTab.addEventListener('click', () => {
            switchTab(workforceTab, workforceContent, monitoringTab, monitoringContent, tasksTab, tasksContent, memberTaskRequestsTab, memberTaskRequestsContent);
            updateURL('workforce');
        });
        memberTaskRequestsTab.addEventListener('click', () => {
            switchTab(memberTaskRequestsTab, memberTaskRequestsContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent);
            updateURL('memberTaskRequests');
        });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const newTab = urlParams.get('tab') || 'monitoring';
        switch(newTab) {
            case 'tasks':
                switchTab(tasksTab, tasksContent, monitoringTab, monitoringContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
                break;
            case 'workforce':
                switchTab(workforceTab, workforceContent, monitoringTab, monitoringContent, tasksTab, tasksContent, memberTaskRequestsTab, memberTaskRequestsContent);
                break;
            case 'memberTaskRequests':
                switchTab(memberTaskRequestsTab, memberTaskRequestsContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent);
                break;
            default:
                switchTab(monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
        }
    });

    document.getElementById('createTaskBtn').addEventListener('click', function() {
        document.getElementById('createTaskModal').classList.remove('hidden');
    });

    document.getElementById('cancelCreateTask').addEventListener('click', function() {
        document.getElementById('createTaskModal').classList.add('hidden');
    });

    // Update the product type selection event listener
    document.getElementById('product_name').addEventListener('change', function() {
        const selectedProduct = this.value;
        const dimensionFields = document.getElementById('dimensionFields');
        const weightField = document.getElementById('weightField');
        const quantityField = document.getElementById('quantityField');
        
        if (selectedProduct === 'Knotted Liniwan' || selectedProduct === 'Knotted Bastos' || selectedProduct === 'Warped Silk') {
            dimensionFields.classList.add('hidden');
            weightField.classList.remove('hidden');
            quantityField.classList.add('hidden');
        } else {
            dimensionFields.classList.remove('hidden');
            weightField.classList.add('hidden');
            quantityField.classList.remove('hidden');
        }
        
        // Update materials list when product changes
        updateMaterialsList(selectedProduct);
        
        // Update member list when product changes
        loadModalDataForCreateTask();
    });

    // Update the form submission to handle quantity
    document.getElementById('createTaskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const selectedProduct = formData.get('product_name');
        const assignedTo = formData.get('assigned_to');
        
        // Set quantity to 1 for Knotted products and Warped Silk
        if (selectedProduct === 'Knotted Liniwan' || selectedProduct === 'Knotted Bastos' || selectedProduct === 'Warped Silk') {
            formData.set('quantity', '1');
        }
        
        fetch('backend/end-points/create_production_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message with SweetAlert
                const message = assignedTo ? 'Task created and assigned successfully!' : 'Task created successfully!';
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    document.getElementById('createTaskModal').classList.add('hidden');
                    const form = document.getElementById('createTaskForm');
                    if (form) form.reset();
                    
                    // If a member was assigned, switch to Assigned Tasks tab
                    if (assignedTo) {
                        const tasksTab = document.getElementById('tasksTab');
                        const tasksContent = document.getElementById('tasksContent');
                        const monitoringTab = document.getElementById('monitoringTab');
                        const monitoringContent = document.getElementById('monitoringContent');
                        const workforceTab = document.getElementById('workforceTab');
                        const workforceContent = document.getElementById('workforceContent');
                        const memberTaskRequestsTab = document.getElementById('memberTaskRequestsTab');
                        const memberTaskRequestsContent = document.getElementById('memberTaskRequestsContent');
                        
                        if (tasksTab && tasksContent && monitoringTab && monitoringContent) {
                            switchTab(tasksTab, tasksContent, monitoringTab, monitoringContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
                        }
                    }
                    
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to create task'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while creating the task. Please check the console for details.'
            });
        });
    });

    // Fetch initial production line data when page loads
    // The production line data is now fetched in PHP, so no need to fetch here
});

function updateWorkforceManagement() {
    fetch('backend/end-points/get_members_availability.php')
        .then(response => response.json())
        .then(data => {
            // Update the summary numbers directly using the data object
            ['knotter', 'warper', 'weaver'].forEach(role => {
                const pluralRole = role + 's'; // e.g., 'knotters'
                if (data[pluralRole]) {
                    document.getElementById(`${role}Total`).textContent = data[pluralRole].total;
                    document.getElementById(`${role}Active`).textContent = data[pluralRole].available;
                    document.getElementById(`${role}Inactive`).textContent = data[pluralRole].unavailable;
                } else {
                    // If data for a specific role is missing, set counts to 0
                    document.getElementById(`${role}Total`).textContent = '0';
                    document.getElementById(`${role}Active`).textContent = '0';
                    document.getElementById(`${role}Inactive`).textContent = '0';
                }
            });
            // The member lists are now updated by renderMemberList(), so this function
            // should not attempt to update them. The previous code block for updating
            // member lists will be removed.
        })
        .catch(error => {
            console.error('Error fetching workforce data:', error);
            // On error, set counts to 0
            ['knotter', 'warper', 'weaver'].forEach(role => {
                document.getElementById(`${role}Total`).textContent = '0';
                document.getElementById(`${role}Active`).textContent = '0';
                document.getElementById(`${role}Inactive`).textContent = '0';
            });
        });
}
// Call updateWorkforceManagement initially and set up periodic updates
document.addEventListener('DOMContentLoaded', function() {
    updateWorkforceManagement();
    // Update every 30 seconds
    setInterval(updateWorkforceManagement, 30000);
});

// Add this function for delete confirmation and handling
function deleteProduct(prodLineId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will delete the production item and all associated tasks. This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('backend/end-points/delete_production_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    prod_line_id: prodLineId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Production item has been deleted.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Refresh the page to show updated data
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to delete production item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'An error occurred while deleting the production item'
                });
            });
        }
    });
}

// Add event listeners for filtering and search
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterTasks();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTasks();
        });
    }
});

// Function to filter production line items
function filterProductionItems() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#monitoringContent table tbody tr');
    
    rows.forEach(row => {
        // Skip the header row if it's included in the selection
        if (row.querySelector('th')) return;
        
        const productName = row.cells[1].textContent.toLowerCase();
        const productionId = row.cells[0].textContent.toLowerCase();
        
        if (productName.includes(searchTerm) || productionId.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Function to filter tasks
function filterTasks() {
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    const searchTerm = (document.getElementById('searchInput')?.value || '').toLowerCase();
    
    const inProgressRows = document.querySelectorAll('#inProgressTasksTable tbody tr');
    const completedRows = document.querySelectorAll('#completedTasksTable tbody tr');

    inProgressRows.forEach(row => {
        const statusElement = row.querySelector('td:nth-child(3) span');
        const status = statusElement ? statusElement.textContent.trim().toLowerCase() : '';
        const productName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        
        const statusMatch = statusFilter === 'all' || status.includes(statusFilter);
        const searchMatch = productName.includes(searchTerm) || row.querySelector('td:nth-child(1)').textContent.toLowerCase().includes(searchTerm);

        row.style.display = (statusMatch && searchMatch) ? '' : 'none';
    });

    completedRows.forEach(row => {
        const productName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const searchMatch = productName.includes(searchTerm) || row.querySelector('td:nth-child(1)').textContent.toLowerCase().includes(searchTerm);
        
        // Completed tasks should only be filtered by search term, not status filter
        // unless statusFilter explicitly asks for 'completed'
        const statusExplicitlyCompleted = statusFilter === 'all' || statusFilter === 'completed';

        row.style.display = (searchMatch && statusExplicitlyCompleted) ? '' : 'none';
    });
}

// Call refreshTaskAssignments initially and set up periodic updates
document.addEventListener('DOMContentLoaded', function() {
    refreshTaskAssignments();
    // Update every 30 seconds
    setInterval(refreshTaskAssignments, 30000);
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
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                        ${task.status === 'in_progress' ? `
                            <div class="flex flex-col items-center space-y-2">
                                <button onclick="confirmTaskCompletion('${task.production_id}')"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm transition-colors w-full">
                                    Confirm Completion
                                </button>
                            </div>
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
        case 'in_progress':
            return 'bg-blue-100 text-blue-800';
        case 'pending':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
</script>

<!-- Top bar with user profile -->
<div class="flex justify-between items-center bg-white p-4 mb-6 rounded-md shadow-md">
    <h2 class="text-lg font-semibold text-gray-700">Production Line</h2>
    <button id="createTaskBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
            Create a Task
    </button>
</div>

<!-- Create Task Modal -->
<div id="createTaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[1000] p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Create New Task</h3>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto flex-1">
            <form id="createTaskForm" class="space-y-4">
                <!-- Row 1: Product Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <select id="product_name" name="product_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            <option value="">Select a product</option>
                            <option value="Piña Seda">Piña Seda</option>
                            <option value="Pure Piña Cloth">Pure Piña Cloth</option>
                            <option value="Knotted Liniwan">Knotted Liniwan</option>
                            <option value="Knotted Bastos">Knotted Bastos</option>
                            <option value="Warped Silk">Warped Silk</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Dimensions / Weight -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Length and Width Fields -->
                    <div id="dimensionFields" class="col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label for="length" class="block text-xs font-medium text-gray-700 mb-1">Length (m)</label>
                            <input type="number" id="length" name="length" step="0.001" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                        </div>
                        <div>
                            <label for="width" class="block text-xs font-medium text-gray-700 mb-1">Width (in)</label>
                            <input type="number" id="width" name="width" step="0.001" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                        </div>
                    </div>
                    
                    <!-- Weight Field -->
                    <div id="weightField" class="hidden">
                        <label for="weight" class="block text-xs font-medium text-gray-700 mb-1">Weight (g)</label>
                        <input type="number" id="weight" name="weight" step="0.001" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                    </div>
                    
                    <!-- Quantity -->
                    <div id="quantityField">
                        <label for="quantity" class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="1" value="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                    </div>
                </div>

                <!-- Row 3: Deadline and Assign To -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="deadline" class="block text-xs font-medium text-gray-700 mb-1">Deadline</label>
                        <input type="datetime-local" id="deadline" name="deadline" min="" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                    </div>
                    <div>
                        <label for="assigned_to" class="block text-xs font-medium text-gray-700 mb-1">Assign To</label>
                        <select id="assigned_to" name="assigned_to" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            <option value="">Select a member</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                    </div>
                </div>

                <!-- Row 4: Available Materials -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Available Materials</label>
                    <div id="materialsList" class="p-2 border rounded-md max-h-32 overflow-y-auto bg-gray-50">
                        <div class="text-sm text-gray-500">Select a product to view materials</div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" id="cancelCreateTask" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="monitoringTab" class="tab-button border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Production Line Monitoring
            </button>
            <button id="tasksTab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Assigned Tasks
            </button>
            <button id="memberTaskRequestsTab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Member Task Requests
            </button>
            <button id="workforceTab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Workforce Management
            </button>
        </nav>
    </div>
</div>

<!-- Production Line Monitoring Tab Content -->
<div id="monitoringContent" class="tab-content">
    <!-- Search bar -->
    <div class="mb-4">
        <div class="relative">
    <input type="text" id="searchInput" placeholder="Search products by name or ID..." class="w-full p-2 pl-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" onkeyup="filterProductionItems()">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
        </svg>
    </div>
</div>
    </div>

    <!-- Production Line List Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Production ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Product Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Length (m)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Width (in)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Weight (g)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Quantity</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-800 uppercase">Raw Materials</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-800 uppercase">Date Added</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-800 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($production_items)): ?>
                    <?php foreach ($production_items as $item): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-4 py-2"><?php echo $item['display_id']; ?></td>
                            <td class="px-4 py-2"><?php echo $item['product_name']; ?></td>
                            <td class="px-4 py-2"><?php 
                                if ($item['product_name'] === 'Knotted Liniwan' || $item['product_name'] === 'Knotted Bastos' || $item['product_name'] === 'Warped Silk') {
                                    echo '-';
                                } else {
                                    echo $item['length_m'] ?: '-';
                                }
                            ?></td>
                            <td class="px-4 py-2"><?php 
                                if ($item['product_name'] === 'Knotted Liniwan' || $item['product_name'] === 'Knotted Bastos' || $item['product_name'] === 'Warped Silk') {
                                    echo '-';
                                } else {
                                    echo $item['width_m'] ?: '-';
                                }
                            ?></td>
                            <td class="px-4 py-2"><?php 
                                if ($item['product_name'] === 'Piña Seda' || $item['product_name'] === 'Pure Piña Cloth') {
                                    echo '-';
                                } else {
                                    echo $item['weight_g'] ?: '-';
                                }
                            ?></td>
                            <td class="px-4 py-2"><?php 
                                if ($item['product_name'] === 'Knotted Liniwan' || $item['product_name'] === 'Knotted Bastos' || $item['product_name'] === 'Warped Silk') {
                                    echo '-';
                                } else {
                                    echo $item['quantity'];
                                }
                            ?></td>
                            <td class="px-4 py-2 text-center">
                                <button onclick='showMaterialsModal(<?php 
                                    $calculatedMaterials = $materialCalculator->calculateMaterialsNeeded(
                                        $item['product_name'],
                                        $item['quantity'],
                                        $item['length_m'],
                                        $item['width_m'],
                                        $item['weight_g']
                                    );
                                    echo htmlspecialchars(json_encode($calculatedMaterials, JSON_HEX_APOS | JSON_HEX_QUOT)); 
                                ?>, <?php 
                                    $isKnottedProduct = in_array($item['product_name'], ['Knotted Liniwan', 'Knotted Bastos']);
                                    $isDimensionsProduct = in_array($item['product_name'], ['Piña Seda', 'Pure Piña Cloth']);
                                    
                                    $productData = [
                                        'name' => $item['product_name'],
                                        'weight' => $item['weight_g']
                                    ];
                                    
                                    if ($isDimensionsProduct) {
                                        $productData['length'] = $item['length_m'];
                                        $productData['width'] = $item['width_m'];
                                        $productData['quantity'] = $item['quantity'];
                                    } elseif (!$isKnottedProduct) {
                                        $productData['quantity'] = $item['quantity'];
                                    }
                                    
                                    echo htmlspecialchars(json_encode($productData, JSON_HEX_APOS | JSON_HEX_QUOT)); 
                                ?>)' 
                                    class="bg-blue-100 text-blue-700 px-4 py-2 rounded-md hover:bg-blue-200 transition-colors">
                                    View Materials
                                </button>
                            </td>
                            <td class="px-4 py-2 text-center"><?php echo $item['date_created']; ?></td>
                            <td class="px-4 py-2 text-center relative">
                                <div class="relative inline-block text-left">
                                    <button type="button" class="inline-flex justify-center w-full rounded-md px-4 py-2 bg-indigo-500 text-sm font-semibold text-white hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200" id="options-menu-<?php echo $item['raw_id']; ?>" aria-haspopup="true" aria-expanded="true">
                                        Actions
                                        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <div id="dropdown-menu-<?php echo $item['raw_id']; ?>" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-10" role="menu" aria-orientation="vertical" aria-labelledby="options-menu-<?php echo $item['raw_id']; ?>">
                                                                                <div class="py-1" role="none">
                                                                                    <a href="#" onclick="assignTask('<?php echo $item['raw_id']; ?>', '<?php echo htmlspecialchars($item['product_name'], ENT_QUOTES); ?>', <?php echo $item['quantity']; ?>); return false;" class="block px-4 py-2 text-sm text-gray-800 hover:bg-indigo-50 hover:text-indigo-700 transition-colors duration-150 <?php echo $item['has_assignments'] ? 'opacity-50 cursor-not-allowed' : ''; ?>" role="menuitem" <?php echo $item['has_assignments'] ? 'disabled' : ''; ?>>Assign Tasks</a>
                                                                                    <a href="#" onclick="deleteProduct('<?php echo $item['raw_id']; ?>'); return false;" class="block px-4 py-2 text-sm text-gray-800 hover:bg-indigo-50 hover:text-indigo-700 transition-colors duration-150" role="menuitem">Delete</a>
                                                                                </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-4 py-2 text-center text-gray-500">No production items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Completed Tasks Table -->
    <div class="bg-green-50 rounded-lg shadow-sm overflow-hidden p-6">
        <h2 class="text-xl font-semibold text-green-800 mb-4">Production Line Overview (Completed Tasks)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Product Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Member's Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Role</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Measurements</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Weight (g)</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Quantity</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-800 uppercase">Completed Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $completed_query = "SELECT 
                        pl.prod_line_id, 
                        pl.product_name, 
                        pl.length_m, 
                        pl.width_m, 
                        pl.weight_g, 
                        pl.quantity, 
                        ta.updated_at as completed_date, 
                        um.fullname as member_name, 
                        ta.role
                        FROM production_line pl
                        JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
                        JOIN user_member um ON ta.member_id = um.id
                        WHERE ta.status = 'completed'
                    
                    UNION ALL
                    
                    SELECT 
                        mst.production_id as prod_line_id,
                        mst.product_name,
                        NULL as length_m,
                        NULL as width_m,
                        mst.weight_g,
                        NULL as quantity,
                        mst.date_submitted as completed_date,
                        um.fullname as member_name,
                        um.role
                    FROM member_self_tasks mst
                    JOIN user_member um ON mst.member_id = um.id
                    WHERE mst.status = 'completed'
                    
                    ORDER BY completed_date DESC";
                    $completed_result = mysqli_query($db->conn, $completed_query);
                    if ($completed_result && mysqli_num_rows($completed_result) > 0):
                        while($row = mysqli_fetch_assoc($completed_result)):
                            $display_id = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
                            $is_knotted_product = in_array($row['product_name'], ['Knotted Liniwan', 'Knotted Bastos']);
                            $is_warped_silk = $row['product_name'] === 'Warped Silk';
                            $is_self_assigned = $row['length_m'] === NULL; // Check if this is a self-assigned task
                            
                            if ($is_self_assigned) {
                                // For self-assigned tasks, measurements are always '-'
                                $measurements = '-';
                                $weight = $row['weight_g'] ? $row['weight_g'] : '-';
                                $quantity = '-';
                            } else {
                                // For regular assigned tasks
                            $length = $row['length_m'] ? $row['length_m'] . 'm' : '-';
                            $width = $row['width_m'] ? $row['width_m'] . 'in' : '-';
                            $measurements = ($is_knotted_product || $is_warped_silk) ? '-' : (($length !== '-' && $width !== '-') ? $length . ' x ' . $width : '-');
                            $weight = $row['weight_g'] ? $row['weight_g'] : '-';
                                $quantity = ($is_knotted_product || $is_warped_silk) ? '-' : $row['quantity'];
                            }
                            
                            $completed_date = $row['completed_date'] ? date('Y-m-d H:i', strtotime($row['completed_date'])) : '-';
                    ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-4 py-2"><?php echo $row['product_name']; ?></td>
                                <td class="px-4 py-2"><?php echo $row['member_name']; ?></td>
                                <td class="px-4 py-2"><?php echo ucfirst($row['role']); ?></td>
                                <td class="px-4 py-2"><?php echo $measurements; ?></td>
                                <td class="px-4 py-2"><?php echo $weight; ?></td>
                                <td class="px-4 py-2"><?php echo $quantity; ?></td>
                                <td class="px-4 py-2"><?php echo $completed_date; ?></td>
                            </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-center text-gray-500">No completed tasks found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assigned Tasks Tab Content -->
<div id="tasksContent" class="tab-content hidden">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex flex-col space-y-4 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h3 class="text-xl font-semibold text-gray-800">In Progress Tasks</h3>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative w-64 z-10">
                        <input type="text" id="searchInput" placeholder="Search tasks..." class="pl-10 pr-4 py-2 w-full rounded-md border border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="w-48 z-10">
                        <label for="statusFilter" class="sr-only">Filter by Status</label>
                        <select id="statusFilter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="inProgressTasksTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Production ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Assigned Members</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
        <div class="flex flex-col space-y-4 mb-6">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Completed Tasks</h3>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="completedTasksTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Production ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Assigned Members</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Completion Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Workforce Management Tab Content -->
<div id="workforceContent" class="tab-content hidden">
    <div class="bg-white p-6">
        <h2 class="text-xl font-semibold mb-6">Workforce Management</h2>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Knotters Card -->
            <div class="bg-indigo-50 rounded-lg p-6">
                <h3 class="text-center text-xl text-indigo-700 mb-4">Knotters</h3>
                <div class="text-center text-4xl font-bold mb-4" id="knotterTotal">0</div>
                <div class="flex justify-center gap-4">
                    <div class="text-green-600">
                        <span id="knotterActive">0</span> Available
                    </div>
                    <div class="text-red-600">
                        <span id="knotterInactive">0</span> Unavailable
                    </div>
                </div>
            </div>

            <!-- Warpers Card -->
            <div class="bg-blue-50 rounded-lg p-6">
                <h3 class="text-center text-xl text-blue-700 mb-4">Warpers</h3>
                <div class="text-center text-4xl font-bold mb-4" id="warperTotal">0</div>
                <div class="flex justify-center gap-4">
                    <div class="text-green-600">
                        <span id="warperActive">0</span> Available
                    </div>
                    <div class="text-red-600">
                        <span id="warperInactive">0</span> Unavailable
                    </div>
                </div>
            </div>

            <!-- Weavers Card -->
            <div class="bg-green-50 rounded-lg p-6">
                <h3 class="text-center text-xl text-green-700 mb-4">Weavers</h3>
                <div class="text-center text-4xl font-bold mb-4" id="weaverTotal">0</div>
                <div class="flex justify-center gap-4">
                    <div class="text-green-600">
                        <span id="weaverActive">0</span> Available
                    </div>
                    <div class="text-red-600">
                        <span id="weaverInactive">0</span> Unavailable
                    </div>
                </div>
            </div>
        </div>

        <!-- Member Lists -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Knotters List -->
            <div>
                <h3 class="text-lg font-semibold text-indigo-700 mb-4">Knotters</h3>
                <ul id="knotterList" class="space-y-2">
                    <!-- Members will be populated here -->
                </ul>
            </div>

            <!-- Warpers List -->
            <div>
                <h3 class="text-lg font-semibold text-blue-700 mb-4">Warpers</h3>
                <ul id="warperList" class="space-y-2">
                    <!-- Members will be populated here -->
                </ul>
            </div>

            <!-- Weavers List -->
            <div>
                <h3 class="text-lg font-semibold text-green-700 mb-4">Weavers</h3>
                <ul id="weaverList" class="space-y-2">
                    <!-- Members will be populated here -->
                </ul>
            </div>
        </div>
    </div>
</div>

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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Production ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Member Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Weight (g)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated later -->
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No requests found</td>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (g)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated later -->
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No completion requests found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reassign Task Modal -->
<div id="reassignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-[1000]">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Reassign Task</h3>
            
            <form id="reassignForm" class="space-y-4" novalidate>
                <input type="hidden" id="reassign_prod_line_id" name="prod_line_id">
                <input type="hidden" id="reassign_task_id" name="task_id">
                <input type="hidden" id="reassign_product_name" name="product_name">
                <input type="hidden" id="reassign_current_member" name="current_member_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Select New Member</label>
                    <select id="reassign_member_select" name="new_member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Loading members...</option>
                    </select>
                    <div class="text-xs text-red-500 hidden" id="reassignMemberError">Please select a member</div>
                </div>

                <div>
                    <label for="reassign_deadline" class="block text-sm font-medium text-gray-700">New Deadline</label>
                    <input type="datetime-local" id="reassign_deadline" name="deadline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <div class="text-xs text-red-500 hidden" id="reassignDeadlineError">Please set a deadline</div>
                </div>

                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" id="cancelReassignBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">
                        Reassign Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Assignment Modal -->
<div id="taskAssignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-[1000]">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Assign Tasks</h3>
            
            <form id="taskAssignmentForm" class="space-y-4" novalidate>
                <input type="hidden" id="identifier" name="identifier">
                <input type="hidden" id="prod_line_id" name="prod_line_id">
                <input type="hidden" id="product_details" name="product_details">
                <input type="hidden" id="is_reassignment" name="is_reassignment" value="false">
                <input type="hidden" id="original_task_id" name="original_task_id">
                <input type="hidden" id="decline_notification_id" name="decline_notification_id">
                
                <!-- Knotter Section -->
                <div id="knotterSection" class="space-y-2 hidden">
                    <label class="block text-sm font-medium text-gray-700">Knotter</label>
                    <select name="knotter_id[]" class="knotter-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Knotter</option>
                    </select>
                    <div class="text-sm text-gray-500">
                        Deadline:
                    </div>
                    <input type="datetime-local" name="deadline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <div class="text-xs text-red-500 hidden" id="knotterError">Please select a knotter and deadline</div>
                </div>

                <!-- Warper Section -->
                <div id="warperSection" class="space-y-2 hidden">
                    <label class="block text-sm font-medium text-gray-700">Warper</label>
                    <select name="warper_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Warper</option>
                    </select>
                    <div class="text-sm text-gray-500">
                        Deadline:
                    </div>
                    <input type="datetime-local" name="warper_deadline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <div class="text-xs text-red-500 hidden" id="warperError">Please select a warper and deadline</div>
                </div>

                <!-- Weaver Section -->
                <div id="weaverSection" class="space-y-2 hidden">
                    <label class="block text-sm font-medium text-gray-700">Weaver</label>
                    <select name="weaver_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Weaver</option>
                    </select>
                    <div class="text-sm text-gray-500">
                        Deadline:
                    </div>
                    <input type="datetime-local" name="weaver_deadline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <div class="text-xs text-red-500 hidden" id="weaverError">Please select a weaver and deadline</div>
                </div>

                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" id="cancelTaskBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">
                        Assign Tasks
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "components/footer.php"; ?>

<style>
    /* Action dropdown menu */
    [id^="dropdown-menu-"] {
        z-index: 99999 !important;
        position: absolute !important;
        transform: translateZ(99999px);
        will-change: transform;
    }
    
    /* Task dropdown menu */
    [id^="task-dropdown-"] {
        z-index: 9999 !important;
        position: absolute !important;
        transform: translateZ(9999px);
        will-change: transform;
    }
    
    /* Dropdown toggle buttons */
    [id^="options-menu-"],
    [id^="task-options-"] {
        position: relative;
        z-index: 1;
    }
    
    /* Ensure dropdown stays on top of other elements */
    .relative {
        position: relative;
    }
    
    /* Make sure the dropdown container has a higher stacking context */
    .dropdown-container {
        position: relative;
        z-index: 1;
    }
    
    /* Ensure dropdown items are clickable */
    [role="menu"] {
        z-index: 99999 !important;
    }
</style>

<!-- Load scripts after the DOM is ready -->
<script src="assets/js/app.js"></script>
<script src="assets/js/task-completions.js"></script>

<script>
// Function to fetch and display available materials
function updateMaterialsList(productName) {
    const materialsListDiv = document.getElementById('materialsList');
    
    if (!productName || productName === '') {
        materialsListDiv.innerHTML = '<div class="text-sm text-gray-500">Select a product to view materials</div>';
        return;
    }
    
    materialsListDiv.innerHTML = '<div class="text-sm text-gray-500">Loading materials...</div>'; // Loading indicator

    let url = 'backend/end-points/get_available_materials.php';
    url += `?product_name=${encodeURIComponent(productName)}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            materialsListDiv.innerHTML = ''; // Clear previous content

            if (!data.success) {
                console.error('API Error:', data.message);
                materialsListDiv.innerHTML = `<div class="text-sm text-red-600">Error: ${data.message || 'Failed to load materials'}</div>`;
                return;
            }

            if (data.materials && data.materials.length > 0) {
                data.materials.forEach(material => {
                    const materialItem = document.createElement('div');
                    materialItem.className = 'flex items-center justify-between py-1';
                    
                    const isAvailable = material.available_quantity > 0;
                    const textColorClass = isAvailable ? 'text-green-600' : 'text-red-600';
                    const availabilityText = isAvailable ? 
                        `${material.available_quantity} ${material.unit} available` : 
                        `0 ${material.unit} available (Required)`;

                    let materialNameDisplay = material.name;
                    if (material.category) {
                        materialNameDisplay += ` (${material.category})`;
                    }

                    materialItem.innerHTML = `
                        <span class="text-sm text-gray-700">${materialNameDisplay}</span>
                        <span class="text-sm font-medium ${textColorClass}">
                            ${availabilityText}
                        </span>
                    `;
                    materialsListDiv.appendChild(materialItem);
                });
            } else {
                materialsListDiv.innerHTML = `<div class="text-sm text-gray-500">${data.message || 'No materials information available for this product.'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error loading materials:', error);
            materialsListDiv.innerHTML = 
                `<div class="text-sm text-red-600">Error loading materials: ${error.message}</div>`;
        });
}

// Function to fetch and display available materials and members
// Function to fetch and display available members for the Create Task modal
function loadModalDataForCreateTask() {
    // Get the currently selected product
    const selectedProduct = document.getElementById('product_name').value;
    
    // Load assignable members filtered by product
    let url = 'backend/end-points/get_members_by_role.php?role=all';
    if (selectedProduct) {
        url += `&product_name=${encodeURIComponent(selectedProduct)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(members => {
            const assignedToSelect = document.getElementById('assigned_to');
            // Clear existing options except the first one
            while (assignedToSelect.options.length > 1) {
                assignedToSelect.remove(1);
            }
            
            if (members && members.length > 0) {
                members.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.id;
                    option.textContent = `${member.fullname} (${member.role})`;
                    assignedToSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No members available';
                option.disabled = true;
                assignedToSelect.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Error loading members for create task modal:', error);
            const assignedToSelect = document.getElementById('assigned_to');
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Error loading members';
            option.disabled = true;
            assignedToSelect.appendChild(option);
        });

    // Load initial materials list (empty or for default selected product)
    const initialSelectedProduct = document.getElementById('product_name').value;
    updateMaterialsList(initialSelectedProduct);
} 

// Function to fetch and display available members for the Task Assignment modal
function loadAssignmentModalData(productName = null) {
    // Get product name from form if not provided
    if (!productName) {
        const productDetailsInput = document.getElementById('product_details');
        if (productDetailsInput) {
            productName = productDetailsInput.value;
        }
    }

    let url = 'backend/end-points/get_members_by_role.php?role=all';
    if (productName) {
        url += '&product_name=' + encodeURIComponent(productName);
    }

    fetch(url)
        .then(response => response.json())
        .then(members => {
            // Get all role-specific select elements in the taskAssignmentForm
            const knotterSelect = document.querySelector('#taskAssignmentForm select[name="knotter_id[]"]');
            const warperSelect = document.querySelector('#taskAssignmentForm select[name="warper_id"]');
            const weaverSelect = document.querySelector('#taskAssignmentForm select[name="weaver_id"]');
            
            // Clear existing options, keeping the first "Select" option
            [knotterSelect, warperSelect, weaverSelect].forEach(select => {
                if (select) {
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                }
            });
            
            if (members && members.length > 0) {
                members.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.id;
                    option.textContent = `${member.fullname} (${member.role}) ${member.work_status === 'Available' ? '' : `(${member.work_status})`}`;
                    // Only add to the correct role's select field
                    if (member.role.toLowerCase() === 'knotter' && knotterSelect) {
                        knotterSelect.appendChild(option.cloneNode(true));
                    } else if (member.role.toLowerCase() === 'warper' && warperSelect) {
                        warperSelect.appendChild(option.cloneNode(true));
                    } else if (member.role.toLowerCase() === 'weaver' && weaverSelect) {
                        weaverSelect.appendChild(option.cloneNode(true));
                    }
                });
            } else {
                // If no members, add a disabled option to all selects
                [knotterSelect, warperSelect, weaverSelect].forEach(select => {
                    if (select) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No members available';
                        option.disabled = true;
                        select.appendChild(option);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading members:', error);
            // On error, add a disabled error option to all selects
            ['knotter', 'warper', 'weaver'].forEach(role => {
                const select = document.querySelector(`#taskAssignmentForm select[name="${role}_id[]"]`); // Adjust for knotter[]
                if (select) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Error loading members';
                    option.disabled = true;
                    select.appendChild(option);
                }
            });
        });
} 

// Function to set minimum date for deadline (today)
function setMinDeadlineDate(inputElement) {
    const now = new Date();
    // Format: YYYY-MM-DDTHH:MM
    const minDate = now.toISOString().slice(0, 16);
    if (inputElement) {
        inputElement.min = minDate;
        if (!inputElement.value) { // Only set default if no value exists
            const defaultDeadline = new Date(now.setDate(now.getDate() + 7)).toISOString().slice(0, 16);
            inputElement.value = defaultDeadline;
        }
    }
}

// Function to open reassign modal for pending tasks
function openReassignModal(prodLineId, taskId, productName, currentMemberId) {
    const modal = document.getElementById('reassignModal');
    
    if (!modal) {
        console.error('Reassign modal not found');
        return;
    }
    
    // Set the form data
    document.getElementById('reassign_prod_line_id').value = prodLineId;
    document.getElementById('reassign_task_id').value = taskId;
    document.getElementById('reassign_product_name').value = productName;
    document.getElementById('reassign_current_member').value = currentMemberId;
    
    // Load members filtered by product
    let url = 'backend/end-points/get_members_by_role.php?role=all';
    url += `&product_name=${encodeURIComponent(productName)}`;
    
    const memberSelect = document.getElementById('reassign_member_select');
    memberSelect.innerHTML = '<option value="">Select a member</option>';
    
    fetch(url)
        .then(response => response.json())
        .then(members => {
            if (members && members.length > 0) {
                members.forEach(member => {
                    if (member.id != currentMemberId) { // Don't show current member
                        const option = document.createElement('option');
                        option.value = member.id;
                        option.textContent = `${member.fullname} (${member.role})`;
                        memberSelect.appendChild(option);
                    }
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No other members available';
                option.disabled = true;
                memberSelect.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Error loading members:', error);
            memberSelect.innerHTML = '<option value="">Error loading members</option>';
        });
    
    // Set deadline input
    const deadlineInput = document.getElementById('reassign_deadline');
    setMinDeadlineDate(deadlineInput);
    
    // Show the modal
    modal.classList.remove('hidden');
}

// Initialize the create task form
// Initialize search functionality for assigned tasks
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search and filter event listeners
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterTasks);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTasks);
    }
    
    // Initial filter call to ensure everything is in sync
    filterTasks();
    
    // Handle dropdown menus
    document.querySelectorAll('[id^="options-menu-"]').forEach(button => {
        button.addEventListener('click', function() {
            const dropdownId = this.id.replace('options-menu-', 'dropdown-menu-');
            const dropdownMenu = document.getElementById(dropdownId);
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('hidden');
                this.setAttribute('aria-expanded', dropdownMenu.classList.contains('hidden') ? 'false' : 'true');
            }
        });
    });

    // Handle create task modal
    const createTaskBtn = document.getElementById('createTaskBtn');
    const createTaskModal = document.getElementById('createTaskModal');
    const cancelCreateTask = document.getElementById('cancelCreateTask');
    const createTaskForm = document.getElementById('createTaskForm');
    const productNameSelect = document.getElementById('product_name');
    const dimensionFields = document.getElementById('dimensionFields');
    const weightField = document.getElementById('weightField');
    const quantityField = document.getElementById('quantityField');

    // Show modal and load data
    createTaskBtn.addEventListener('click', function() {
        createTaskModal.classList.remove('hidden');
        loadModalDataForCreateTask();
        setMinDeadlineDate();
    });

    // Hide modal
    cancelCreateTask.addEventListener('click', function() {
        createTaskModal.classList.add('hidden');
    });

    // Toggle fields based on product type
    productNameSelect.addEventListener('change', function() {
        const selectedProduct = this.value;
        
        // Reset all fields
        dimensionFields.classList.add('hidden');
        weightField.classList.add('hidden');
        
        // Show/hide fields based on product type
        if (['Piña Seda', 'Pure Piña Cloth'].includes(selectedProduct)) {
            dimensionFields.classList.remove('hidden');
            weightField.classList.add('hidden');
        } else if (['Knotted Liniwan', 'Knotted Bastos'].includes(selectedProduct)) {
            dimensionFields.classList.add('hidden');
            weightField.classList.remove('hidden');
        } else if (selectedProduct === 'Warped Silk') {
            dimensionFields.classList.add('hidden');
            weightField.classList.add('hidden');
        }

        // Update materials list based on selected product
        updateMaterialsList(selectedProduct);
    });

    // Handle form submission
    createTaskForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        const formData = new FormData(this);
        const productName = formData.get('product_name');
        const quantity = formData.get('quantity');
        const deadline = formData.get('deadline');
        
        if (!productName) {
            alert('Please select a product');
            return;
        }
        
        if (!quantity || quantity < 1) {
            alert('Please enter a valid quantity');
            return;
        }
        
        // Submit the form
        fetch('backend/end-points/create_task.php', {
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
                    text: 'Task created successfully!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            } else {
                alert('Error: ' + (data.message || 'Failed to create task'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the task');
        });
    });

    // Close the dropdown menu if the user clicks outside of it
    window.addEventListener('click', function(event) {
        document.querySelectorAll('[id^="dropdown-menu-"]').forEach(dropdownMenu => {
            const buttonId = dropdownMenu.id.replace('dropdown-menu-', 'options-menu-');
            const button = document.getElementById(buttonId);

            if (dropdownMenu && button && !dropdownMenu.contains(event.target) && !button.contains(event.target) && !dropdownMenu.classList.contains('hidden')) {
                dropdownMenu.classList.add('hidden');
                button.setAttribute('aria-expanded', 'false');
            }
        });
    });

    // Handle Reassign Modal
    const reassignModal = document.getElementById('reassignModal');
    const reassignForm = document.getElementById('reassignForm');
    const cancelReassignBtn = document.getElementById('cancelReassignBtn');

    if (cancelReassignBtn && reassignModal) {
        cancelReassignBtn.addEventListener('click', function() {
            reassignModal.classList.add('hidden');
            if (reassignForm) reassignForm.reset();
        });
    }

    if (reassignModal) {
        reassignModal.addEventListener('click', function(e) {
            if (e.target === reassignModal) {
                reassignModal.classList.add('hidden');
                if (reassignForm) reassignForm.reset();
            }
        });
    }

    if (reassignForm) {
        reassignForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newMemberId = document.getElementById('reassign_member_select').value;
            const deadline = document.getElementById('reassign_deadline').value;
            
            if (!newMemberId || !deadline) {
                if (!newMemberId) {
                    document.getElementById('reassignMemberError').classList.remove('hidden');
                }
                if (!deadline) {
                    document.getElementById('reassignDeadlineError').classList.remove('hidden');
                }
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('backend/end-points/reassign_task.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Task reassigned successfully!',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        reassignModal.classList.add('hidden');
                        reassignForm.reset();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to reassign task'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while reassigning the task'
                });
            });
        });
    }
});
</script>
</body>
</html>
