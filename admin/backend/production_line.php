<?php 
include "components/header.php";

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
<div id="materialsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
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
function updateSummaryPanels() {
    fetch('backend/end-points/list_member.php')
        .then(function(response) { return response.text(); })
        .then(function(html) {
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            var rows = tempDiv.querySelectorAll('tr');
            var summary = {
                knotter: { total: 0, active: 0, inactive: 0 },
                warper: { total: 0, active: 0, inactive: 0 },
                weaver: { total: 0, active: 0, inactive: 0 }
            };
            rows.forEach(function(row) {
                var cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    var role = cells[4] ? cells[4].textContent.trim().toLowerCase() : '';
                    var status = cells[6] ? cells[6].textContent.trim() : '';
                    if (['knotter', 'warper', 'weaver'].indexOf(role) !== -1) {
                        summary[role].total++;
                        if (status === 'Verified' || status === 'Active') summary[role].active++;
                        else summary[role].inactive++;
                    }
                }
            });
            if (document.getElementById('knotterTotal')) document.getElementById('knotterTotal').textContent = summary.knotter.total;
            if (document.getElementById('knotterActive')) document.getElementById('knotterActive').textContent = summary.knotter.active + ' Active';
            if (document.getElementById('knotterInactive')) document.getElementById('knotterInactive').textContent = summary.knotter.inactive + ' Inactive';
            if (document.getElementById('warperTotal')) document.getElementById('warperTotal').textContent = summary.warper.total;
            if (document.getElementById('warperActive')) document.getElementById('warperActive').textContent = summary.warper.active + ' Active';
            if (document.getElementById('warperInactive')) document.getElementById('warperInactive').textContent = summary.warper.inactive + ' Inactive';
            if (document.getElementById('weaverTotal')) document.getElementById('weaverTotal').textContent = summary.weaver.total;
            if (document.getElementById('weaverActive')) document.getElementById('weaverActive').textContent = summary.weaver.active + ' Active';
            if (document.getElementById('weaverInactive')) document.getElementById('weaverInactive').textContent = summary.weaver.inactive + ' Inactive';
        });
}

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
                    found = true;
                    const name = member.fullname;
                    const status = member.work_status;
                    const badgeClass = status === 'Work In Progress' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between py-2';
                    li.innerHTML = `
                        <span class="font-medium">${name}</span>
                        <span class="ml-2 px-2 py-1 rounded-full text-xs font-semibold ${badgeClass}">${status}</span>
                    `;
                    list.appendChild(li);
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
            
            const tableBody = document.querySelector('#assignedTasksTable tbody');
            if (!tableBody) return;
            
            tableBody.innerHTML = '';
            
            if (response.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No tasks assigned yet.
                        </td>
                    </tr>
                `;
                return;
            }
            
            response.data.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                // Create assigned members display without status
                const assignedMembersHtml = item.assignments.map(assignment => {
                    if (!assignment.member_name) return '';
                    return `
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="font-medium">${assignment.member_name}</span>
                            <span class="text-gray-500">(${assignment.role})</span>
                        </div>
                    `;
                }).join('');

                // Get the most relevant status from assignments
                const taskStatuses = item.assignments.map(a => a.task_status);
                let displayStatus = item.status;
                if (taskStatuses.includes('in_progress')) {
                    displayStatus = 'in_progress';
                } else if (taskStatuses.includes('submitted')) {
                    displayStatus = 'submitted';
                } else if (taskStatuses.includes('pending')) {
                    displayStatus = 'pending';
                }

                // Get status class for the status badge
                const statusClass = displayStatus === 'completed' ? 'bg-green-100 text-green-800' :
                                  displayStatus === 'submitted' ? 'bg-purple-100 text-purple-800' :
                                  displayStatus === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                  'bg-yellow-100 text-yellow-800';

                // Determine if all tasks are submitted
                const allSubmitted = item.assignments.every(assignment => 
                    assignment.task_status === 'submitted' || assignment.task_status === 'completed'
                );

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
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        ${item.status !== 'completed' ? `
                            <button onclick="confirmTaskCompletion('${item.raw_id}')" 
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition-colors ${displayStatus !== 'submitted' ? 'opacity-50 cursor-not-allowed' : ''}"
                                ${displayStatus !== 'submitted' ? 'disabled' : ''}>
                                Confirm Task Completion
                            </button>
                        ` : ''}
                    </td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error fetching task assignments:', error);
            const tableBody = document.querySelector('#assignedTasksTable tbody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-red-600">
                            Error loading data: ${error.message}
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
                                <div class="flex space-x-2">
                                    <button onclick="handleTaskRequest(${request.request_id}, 'approve')"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md">
                                        Approve
                                    </button>
                                    <button onclick="handleTaskRequest(${request.request_id}, 'reject')"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md">
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
    updateSummaryPanels();
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

    document.getElementById('createProductBtn').addEventListener('click', function() {
        document.getElementById('createProductModal').classList.remove('hidden');
    });

    document.getElementById('cancelCreateProduct').addEventListener('click', function() {
        document.getElementById('createProductModal').classList.add('hidden');
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
    });

    // Update the form submission to handle quantity
    document.getElementById('createProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const selectedProduct = formData.get('product_name');
        
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
                alert('Product created successfully!');
                document.getElementById('createProductModal').classList.add('hidden');
                this.reset();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the product. Please check the console for details.');
        });
    });

    // Fetch initial production line data when page loads
    // The production line data is now fetched in PHP, so no need to fetch here
});

function updateWorkforceManagement() {
    fetch('backend/end-points/get_members_availability.php')
        .then(response => response.json())
        .then(data => {
            const summary = {
                knotter: { total: 0, active: 0, inactive: 0, members: [] },
                warper: { total: 0, active: 0, inactive: 0, members: [] },
                weaver: { total: 0, active: 0, inactive: 0, members: [] }
            };

            // Process the data
            data.forEach(member => {
                const role = member.role.toLowerCase();
                if (summary[role]) {
                    summary[role].total++;
                    if (member.availability_status === 'available') {
                        summary[role].active++;
                    } else {
                        summary[role].inactive++;
                    }
                    summary[role].members.push(member);
                }
            });

            // Update the summary numbers
            ['knotter', 'warper', 'weaver'].forEach(role => {
                document.getElementById(`${role}Total`).textContent = summary[role].total;
                document.getElementById(`${role}Active`).textContent = summary[role].active;
                document.getElementById(`${role}Inactive`).textContent = summary[role].inactive;

                // Update member lists
                const list = document.getElementById(`${role}List`);
                if (summary[role].members.length > 0) {
                    list.innerHTML = summary[role].members.map(member => `
                        <li class="flex items-center justify-between p-2 bg-white rounded shadow-sm">
                            <span>${member.fullname}</span>
                            <span class="px-2 py-1 text-xs rounded-full ${
                                member.availability_status === 'available' 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-red-100 text-red-800'
                            }">
                                ${member.availability_status === 'available' ? 'Available' : 'Unavailable'}
                            </span>
                        </li>
                    `).join('');
                } else {
                    list.innerHTML = '<li class="text-gray-500">No members found.</li>';
                }
            });
        })
        .catch(error => {
            console.error('Error fetching workforce data:', error);
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

// Function to filter tasks
function filterTasks() {
    const statusFilter = document.getElementById('statusFilter').value;
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#assignedTasksTable tbody tr');

    rows.forEach(row => {
        const status = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase();
        const productName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const statusMatch = statusFilter === 'all' || status.includes(statusFilter);
        const searchMatch = productName.includes(searchTerm);

        row.style.display = statusMatch && searchMatch ? '' : 'none';
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ${task.status === 'in_progress' ? `
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
    <button id="createProductBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
        Create Task
    </button>
</div>

<!-- Add this modal HTML right after the top bar div -->
<div id="createProductModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h3 class="text-lg font-semibold mb-4">Create a Product</h3>
        <form id="createProductForm">
            <div class="mb-4">
                <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                <select id="product_name" name="product_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select a product</option>
                    <option value="Piña Seda">Piña Seda</option>
                    <option value="Pure Piña Cloth">Pure Piña Cloth</option>
                    <option value="Knotted Liniwan">Knotted Liniwan</option>
                    <option value="Knotted Bastos">Knotted Bastos</option>
                    <option value="Warped Silk">Warped Silk</option>
                </select>
            </div>
            
            <!-- Length and Width fields (shown for Piña Seda and Pure Piña Cloth) -->
            <div id="dimensionFields" class="mb-4">
                <div class="mb-4">
                    <label for="length" class="block text-sm font-medium text-gray-700">Length (m)</label>
                    <input type="number" id="length" name="length" step="0.001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="width" class="block text-sm font-medium text-gray-700">Width (in)</label>
                    <input type="number" id="width" name="width" step="0.001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Weight field (shown for Knotted Liniwan and Knotted Bastos) -->
            <div id="weightField" class="mb-4 hidden">
                <label for="weight" class="block text-sm font-medium text-gray-700">Weight (g)</label>
                <input type="number" id="weight" name="weight" step="0.001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <!-- Quantity field (hidden for Knotted products) -->
            <div id="quantityField" class="mb-4">
                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" id="cancelCreateProduct" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Create Product</button>
            </div>
        </form>
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
        <input type="text" id="searchInput" placeholder="Search products..." class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>

    <!-- Production Line List Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Production ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Length (m)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Width (in)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Weight (g)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Raw Materials</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Date Added</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
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
                                    echo rtrim(rtrim(number_format((float)$item['length_m'], 3, '.', ''), '0'), '.') . 'm';
                                }
                            ?></td>
                            <td class="px-4 py-2"><?php 
                                if ($item['product_name'] === 'Knotted Liniwan' || $item['product_name'] === 'Knotted Bastos' || $item['product_name'] === 'Warped Silk') {
                                    echo '-';
                                } else {
                                    echo rtrim(rtrim(number_format((float)$item['width_m'], 3, '.', ''), '0'), '.') . 'in';
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
                            <td class="px-4 py-2 text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick="assignTask('<?php echo $item['raw_id']; ?>', '<?php echo htmlspecialchars($item['product_name'], ENT_QUOTES); ?>', <?php echo $item['quantity']; ?>)"
                                        class="<?php echo $item['has_assignments'] ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-500 hover:bg-indigo-600'; ?> text-white px-4 py-2 rounded"
                                        <?php echo $item['has_assignments'] ? 'disabled' : ''; ?>>
                                        Assign Tasks
                                    </button>
                                    <button onclick="editProduct('<?php echo $item['raw_id']; ?>')"
                                        class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded">Edit</button>
                                    <button onclick="deleteProduct('<?php echo $item['raw_id']; ?>')"
                                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Delete</button>
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
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Member's Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Measurements</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Weight (g)</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Completed Date</th>
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
                            $length = $row['length_m'] ? rtrim(rtrim(number_format((float)$row['length_m'], 3, '.', ''), '0'), '.') . 'm' : '-';
                            $width = $row['width_m'] ? rtrim(rtrim(number_format((float)$row['width_m'], 3, '.', ''), '0'), '.') . 'in' : '-';
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
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Assigned Tasks</h3>
            <div class="flex items-center space-x-4">
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700">Filter by Status</label>
                    <select id="statusFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div>
                    <label for="searchInput" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" id="searchInput" placeholder="Search by product name..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="assignedTasksTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Members</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                        <span id="warperUnavailable">0</span> Unavailable
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
                        <span id="weaverUnavailable">0</span> Unavailable
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (g)</th>
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

<!-- Task Assignment Modal -->
<div id="taskAssignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Assign Tasks</h3>
            
            <form id="taskAssignmentForm" class="space-y-4" novalidate>
                <input type="hidden" id="identifier" name="identifier">
                <input type="hidden" id="prod_line_id" name="prod_line_id">
                <input type="hidden" id="product_details" name="product_details">
                
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

<!-- Load scripts after the DOM is ready -->
<script src="assets/js/app.js"></script>
<script src="assets/js/task-completions.js"></script>
</body>
</html>
