<?php 
include "components/header.php";
// Add custom CSS for tables
echo '<link rel="stylesheet" href="css/table-styles.css">';
?>
<style>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
<?php
$check_pl_updated = mysqli_query($db->conn, "SHOW COLUMNS FROM production_line LIKE 'updated_at'");
if (mysqli_num_rows($check_pl_updated) == 0) {
    mysqli_query($db->conn, "ALTER TABLE production_line ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
}

// Check and add updated_at column to task_assignments if it doesn't exist
$check_ta_updated = mysqli_query($db->conn, "SHOW COLUMNS FROM task_assignments LIKE 'updated_at'");
if (mysqli_num_rows($check_ta_updated) == 0) {
    mysqli_query($db->conn, "ALTER TABLE task_assignments ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
}

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

<script>
    const productionItemsData = <?php echo json_encode($production_items); ?>;
</script>

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

// Function to open task completion verification modal
function openTaskCompletionVerificationModal(prodLineId, productName, expectedLength, expectedWidth, expectedWeight) {
    const modal = document.getElementById('taskCompletionVerificationModal');
    const form = document.getElementById('taskCompletionForm');
    
    // Set product line ID
    document.getElementById('taskCompletionProdLineId').value = prodLineId;
    
    // Set product name
    document.getElementById('taskCompletionProductName').textContent = productName;
    
    // Reset form
    form.reset();
    
    // Determine product type and show appropriate fields
    const isKnottedProduct = ['Knotted Liniwan', 'Knotted Bastos'].includes(productName);
    const isWarpedSilk = productName === 'Warped Silk';
    const isDimensionsProduct = ['Piña Seda', 'Pure Piña Cloth'].includes(productName);
    
    const dimensionsSection = document.getElementById('taskCompletionDimensionsSection');
    const weightSection = document.getElementById('taskCompletionWeightSection');
    
    if (isKnottedProduct || isWarpedSilk) {
        // Show weight section
        dimensionsSection.classList.add('hidden');
        weightSection.classList.remove('hidden');
        document.getElementById('taskCompletionExpectedWeight').textContent = expectedWeight ? expectedWeight + ' g' : '-';
        document.getElementById('taskCompletionActualWeight').required = true;
        document.getElementById('taskCompletionActualLength').required = false;
        document.getElementById('taskCompletionActualWidth').required = false;
    } else {
        // Show dimensions section
        dimensionsSection.classList.remove('hidden');
        weightSection.classList.add('hidden');
        document.getElementById('taskCompletionExpectedLength').textContent = expectedLength ? expectedLength + ' m' : '-';
        document.getElementById('taskCompletionExpectedWidth').textContent = expectedWidth ? expectedWidth + ' in' : '-';
        document.getElementById('taskCompletionActualWeight').required = false;
        document.getElementById('taskCompletionActualLength').required = true;
        document.getElementById('taskCompletionActualWidth').required = true;
    }
    
    // Show modal
    modal.classList.remove('hidden');
}

// Function to confirm task completion
function confirmTaskCompletion(prodLineId, productName) {
    if (['Piña Seda', 'Pure Piña Cloth'].includes(productName)) {
        // For fabrics, ask for both Length and Width
        Swal.fire({
            title: 'Verify Product Dimensions',
            html: `
                <div class="text-left">
                    <p class="mb-4 text-sm text-gray-600">Please verify the actual dimensions for <b>${productName}</b> before adding to inventory.</p>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actual Length (meters)</label>
                        <input id="swal-length" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter verified length" type="number" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actual Width (inches)</label>
                        <input id="swal-width" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter verified width" type="number" step="0.01" min="0">
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Verify & Add to Inventory',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const length = document.getElementById('swal-length').value;
                const width = document.getElementById('swal-width').value;
                if (!length || !width) {
                    Swal.showValidationMessage('Please enter both verified length and width');
                }
                return { length: length, width: width };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitConfirmation(prodLineId, null, result.value.length, result.value.width);
            }
        });
    } else {
        // For weight-based products
        Swal.fire({
            title: 'Verify Product Weight',
            html: `
                <div class="text-left">
                    <p class="mb-4 text-sm text-gray-600">Please weigh the product <b>${productName}</b> and enter the actual weight in grams. This will be used for wastage calculation and inventory.</p>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actual Weight (grams)</label>
                        <input id="swal-weight" class="swal2-input" style="margin: 0; width: 100%;" placeholder="Enter verified weight" type="number" step="0.01" min="0">
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Verify & Add to Inventory',
            preConfirm: () => {
                const weight = document.getElementById('swal-weight').value;
                if (!weight) {
                    Swal.showValidationMessage('Please enter the verified weight!');
                }
                return weight;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitConfirmation(prodLineId, result.value, null, null);
            }
        });
    }
}

function submitConfirmation(prodLineId, actualOutput, actualLength, actualWidth) {
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

    let body = `production_id=${prodLineId}`;
    if (actualOutput) body += `&actual_output=${actualOutput}`;
    if (actualLength) body += `&actual_length=${actualLength}`;
    if (actualWidth) body += `&actual_width=${actualWidth}`;

    fetch('backend/end-points/confirm_task_completion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: body
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
                // Refresh the tasks table with force refresh to ensure DOM updates
                refreshTaskAssignments(true);
                if (typeof loadTaskCompletions === 'function') {
                    loadTaskCompletions();
                }
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

// Smart cache for real-time updates to avoid unnecessary DOM updates
const dataCache = {
    taskAssignments: null,
    taskRequests: null,
    
    hasTaskAssignmentsChanged(newData) {
        if (!this.taskAssignments) return true;
        return JSON.stringify(this.taskAssignments) !== JSON.stringify(newData);
    },
    
    hasTaskRequestsChanged(newData) {
        if (!this.taskRequests) return true;
        return JSON.stringify(this.taskRequests) !== JSON.stringify(newData);
    },
    
    updateTaskAssignments(data) {
        this.taskAssignments = JSON.parse(JSON.stringify(data));
    },
    
    updateTaskRequests(data) {
        this.taskRequests = JSON.parse(JSON.stringify(data));
    }
};

// Function to refresh task assignments
function refreshTaskAssignments(forceRefresh = false) {
    fetch('backend/end-points/get_task_assignments.php')
        .then(response => response.json())
        .then(response => {
            if (!response.success) {
                throw new Error(response.message);
            }
            
            // Check if data has changed before updating DOM (unless forceRefresh is true)
            if (!forceRefresh && !dataCache.hasTaskAssignmentsChanged(response.data)) {
                console.log('Task assignments data unchanged, skipping DOM update');
                return;
            }
            
            dataCache.updateTaskAssignments(response.data);
            
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

                // Determine display status based on assignment priority
                let displayStatus;
                let isDeclined = false;
                let isReassigned = false;
                let isInProgress = false;
                let isSubmitted = false;
                let isPending = false;
                let isCompleted = false;

                if (item.assignments && item.assignments.length > 0) {
                    // Check if any assignment is declined or reassigned
                    for (const assignment of item.assignments) {
                        if (assignment && (assignment.decline_status === 'pending' || assignment.decline_status === 'responded')) {
                            isDeclined = true;
                            break; // Found a declined assignment, no need to check further
                        }
                        if (assignment && assignment.task_status === 'declined') {
                            isDeclined = true;
                            break; // Also check if task_status is explicitly 'declined'
                        }
                        if (assignment && assignment.task_status === 'completed') {
                            isCompleted = true;
                        }
                        if (assignment && assignment.task_status === 'reassigned') {
                            isReassigned = true;
                        }
                        if (assignment && assignment.task_status === 'in_progress') {
                            isInProgress = true;
                        }
                        if (assignment && assignment.task_status === 'submitted') {
                            isSubmitted = true;
                        }
                        if (assignment && assignment.task_status === 'pending') {
                            isPending = true;
                        }
                    }

                    if (isDeclined) {
                        displayStatus = 'declined';
                    } else if (isCompleted) {
                        displayStatus = 'completed';
                    } else if (isReassigned) {
                        displayStatus = 'reassigned';
                    } else if (isSubmitted) {
                        displayStatus = 'submitted';
                    } else if (isInProgress) {
                        displayStatus = 'in_progress';
                    } else if (isPending) {
                        displayStatus = 'pending';
                    } else {
                        displayStatus = item.status; // Fallback to production line status
                    }
                } else {
                    displayStatus = item.status; // No assignments, use production line status
                }

                const statusClass = displayStatus === 'completed' ? 'bg-green-100 text-green-800' :
                                  displayStatus === 'submitted' ? 'bg-purple-100 text-purple-800' :
                                  displayStatus === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                  displayStatus === 'declined' ? 'bg-red-100 text-red-800' :
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

                    const fullItemData = productionItemsData.find(p => p.raw_id == item.raw_id);
                    let unitDisplay = '-';
                    if (fullItemData) {
                        const isDimensionsProduct = ['Piña Seda', 'Pure Piña Cloth'].includes(fullItemData.product_name);
                        const isWeightProduct = ['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'].includes(fullItemData.product_name);

                        if (isDimensionsProduct) {
                            const length = fullItemData.length_m ? `${fullItemData.length_m}m` : 'N/A';
                            const width = fullItemData.width_m ? `${fullItemData.width_m}in` : 'N/A';
                            const quantity = fullItemData.quantity ? `${fullItemData.quantity}pc(s)` : 'N/A';
                            unitDisplay = `L:${length}<br>W:${width}<br>Q:${quantity}`;
                        } else if (isWeightProduct) {
                            unitDisplay = `${fullItemData.weight_g} g`;
                        } else {
                            unitDisplay = fullItemData.quantity ? `${fullItemData.quantity} pc(s)` : (fullItemData.weight_g ? `${fullItemData.weight_g} g` : '-');
                        }
                    }

                    // Check if task has a decline reason
                                    let declinedAssignment = null;
                                    for (const assignment of item.assignments) {
                                        if (assignment && (assignment.decline_status === 'pending' || assignment.decline_status === 'responded' || assignment.task_status === 'declined')) {
                                            declinedAssignment = assignment;
                                            break;
                                        }
                                    }
                    
                                    const hasDecline = declinedAssignment !== null;
                                    const declineReason = declinedAssignment?.decline_reason ? declinedAssignment.decline_reason : 'No reason provided';
                                    const declinedTaskId = declinedAssignment?.task_id || 0;
                                    const declinedMemberName = declinedAssignment?.member_name || '';
                                    const declinedRole = declinedAssignment?.role || '';                    
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${item.prod_line_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.product_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${unitDisplay}</td>
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
                            ${item.status !== 'completed' ? `
                                <div class="flex flex-col items-center space-y-2">
                                    <button onclick="openTaskCompletionVerificationModal('${item.raw_id}', '${item.product_name.replace(/'/g, "\\'")}'${fullItemData ? `, '${fullItemData.length_m || ''}', '${fullItemData.width_m || ''}', '${fullItemData.weight_g || ''}'` : ', , , '})" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-md transition-colors w-full text-xs ${displayStatus !== 'submitted' ? 'opacity-50 cursor-not-allowed' : ''}"
                                        ${displayStatus !== 'submitted' ? 'disabled' : ''}>
                                        Confirm
                                    </button>
                                    ${hasDecline ? `
                                        <button onclick="openDeclineReasonModal('${declineReason.replace(/'/g, "\\'")}')"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md transition-colors w-full text-xs">
                                            View Decline Reason
                                        </button>
                                        <button onclick="openReassignModal('${item.raw_id}', '${item.product_name}', ${declinedTaskId}, '${declinedMemberName}', '${declinedRole}')"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md transition-colors w-full text-xs">
                                            Reassign
                                        </button>
                                    ` : ''}
                                </div>
                            ` : ''}
                        </td>
                    `;
                    inProgressTableBody.appendChild(row);
                }
            });

            if (inProgressTasksFound === 0) {
                inProgressTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No in-progress tasks assigned yet.
                        </td>
                    </tr>
                `;
            }
            if (completedTasksFound === 0) {
                completedTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
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
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-red-600">
                            Error loading in-progress tasks: ${error.message}
                        </td>
                    </tr>
                `;
            }
            if (completedTableBody) {
                completedTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-red-600">
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
            // Check if data has changed before updating DOM
            if (!dataCache.hasTaskRequestsChanged(data)) {
                console.log('Task requests data unchanged, skipping DOM update');
                return;
            }
            
            dataCache.updateTaskRequests(data);
            
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
                
                let measurement = (request.weight_g && parseFloat(request.weight_g) > 0) ? `${request.weight_g} g` : '-';
                if (request.role && request.role.toLowerCase() === 'weaver') {
                    if (request.length_m && request.width_in) {
                        measurement = `${request.length_m}m x ${request.width_in}in`;
                    }
                }

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${request.production_id || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.member_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.role}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${request.product_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${measurement}</td>
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

// Function to manually refresh all tables
function manualRefreshAllTables() {
    console.log('Manual refresh triggered');
    refreshTaskAssignments();
    refreshTaskApprovalRequests();
    Swal.fire({
        icon: 'success',
        title: 'Refreshing...',
        text: 'Tables are being updated',
        timer: 1500,
        showConfirmButton: false
    });
}

// Real-time update controller
const updateController = {
    intervals: {},
    startRealTimeUpdates() {
        // Update every 5 seconds for real-time experience
        this.intervals.tasks = setInterval(refreshTaskAssignments, 5000);
        this.intervals.requests = setInterval(refreshTaskApprovalRequests, 5000);
    },
    stopRealTimeUpdates() {
        Object.values(this.intervals).forEach(interval => clearInterval(interval));
    }
};

// Call refreshTaskAssignments and refreshTaskApprovalRequests initially and set up periodic updates
document.addEventListener('DOMContentLoaded', function() {
    refreshTaskAssignments();
    refreshTaskApprovalRequests();
    // Start real-time updates
    updateController.startRealTimeUpdates();
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
    // Real-time member list updates every 5 seconds
    updateController.intervals.members = setInterval(() => {
        renderMemberList('knotter', 'knotterList');
        renderMemberList('warper', 'warperList');
        renderMemberList('weaver', 'weaverList');
    }, 5000);
    refreshTaskAssignments();
    
    // Get the active tab from URL or localStorage
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || localStorage.getItem('activeTab') || 'monitoring';
    const status = urlParams.get('status');
    const search = urlParams.get('search');
    
    // Tabs
    const monitoringTab = document.getElementById('monitoringTab');
    const tasksTab = document.getElementById('tasksTab');
    const workforceTab = document.getElementById('workforceTab');
    const monitoringContent = document.getElementById('monitoringContent');
    const wasteTab = document.getElementById('wasteTab');
    const tasksContent = document.getElementById('tasksContent');
    const workforceContent = document.getElementById('workforceContent');
    const memberTaskRequestsTab = document.getElementById('memberTaskRequestsTab');
    const memberTaskRequestsContent = document.getElementById('memberTaskRequestsContent');
    const wasteContent = document.getElementById('wasteContent');

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

    if (monitoringTab && monitoringContent && tasksTab && tasksContent && workforceTab && workforceContent && memberTaskRequestsTab && memberTaskRequestsContent && wasteTab && wasteContent) {
        // Set initial active tab
        switch(activeTab) {
            case 'tasks':
                switchTab(tasksTab, tasksContent, monitoringTab, monitoringContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
                break;
            case 'workforce':
                switchTab(workforceTab, workforceContent, monitoringTab, monitoringContent, tasksTab, tasksContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
                break;
            case 'memberTaskRequests':
                switchTab(memberTaskRequestsTab, memberTaskRequestsContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, wasteTab, wasteContent);
                break;
            case 'waste':
                switchTab(wasteTab, wasteContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
                loadWasteReport();
                break;
            default: // 'monitoring'
                switchTab(monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
        }

        monitoringTab.addEventListener('click', () => {
            switchTab(monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
            updateURL('monitoring');
        });
        tasksTab.addEventListener('click', () => {
            switchTab(tasksTab, tasksContent, monitoringTab, monitoringContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
            updateURL('tasks');
        });
        workforceTab.addEventListener('click', () => {
            switchTab(workforceTab, workforceContent, monitoringTab, monitoringContent, tasksTab, tasksContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
            updateURL('workforce');
        });
        memberTaskRequestsTab.addEventListener('click', () => {
            switchTab(memberTaskRequestsTab, memberTaskRequestsContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, wasteTab, wasteContent);
            updateURL('memberTaskRequests');
        });
        wasteTab.addEventListener('click', () => {
            switchTab(wasteTab, wasteContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
            updateURL('waste');
            loadWasteReport();
        });
    }

    // Handle URL params for filtering
    if (activeTab === 'tasks') {
        if(status) {
            const statusFilter = document.getElementById('statusFilter');
            if(statusFilter) statusFilter.value = status;
        }
        if (search) {
            // there are two inputs with same ID. querySelector to the rescue.
            const searchInput = document.querySelector('#tasksContent #searchInput');
            if (searchInput) {
                searchInput.value = search;
            }
        }
        if(status || search) {
            setTimeout(filterTasks, 100); // setTimeout to allow table to render
        }
    } else if (activeTab === 'monitoring' && search) {
        const searchInput = document.querySelector('#monitoringContent #searchInput');
        if(searchInput) {
            searchInput.value = search;
        }
        filterProductionItems();
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const newTab = urlParams.get('tab') || 'monitoring';
        switch(newTab) {
            case 'tasks':
                switchTab(tasksTab, tasksContent, monitoringTab, monitoringContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
                break;
            case 'workforce':
                switchTab(workforceTab, workforceContent, monitoringTab, monitoringContent, tasksTab, tasksContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
                break;
            case 'memberTaskRequests':
                switchTab(memberTaskRequestsTab, memberTaskRequestsContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, wasteTab, wasteContent);
                break;
            case 'waste':
                switchTab(wasteTab, wasteContent, monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent);
                loadWasteReport();
                break;
            default:
                switchTab(monitoringTab, monitoringContent, tasksTab, tasksContent, workforceTab, workforceContent, memberTaskRequestsTab, memberTaskRequestsContent, wasteTab, wasteContent);
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
    });

    // Update the form submission to handle quantity
    // Note: Form submission is handled in the second DOMContentLoaded event listener below

    // Handle Task Assignment Form Submission (Assign Tasks Modal)
    const taskAssignmentForm = document.getElementById('taskAssignmentForm');
    if (taskAssignmentForm) {
        taskAssignmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('backend/end-points/assign_tasks.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Tasks assigned successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        document.getElementById('taskAssignmentModal').classList.add('hidden');
                        location.reload();
                    });
                } else {
                    if (data.message && data.message.includes('task limit')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Task Limit Reached',
                            text: data.message
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while assigning tasks.'
                });
            });
        });
    }
});

function loadWasteReport() {
    const tableBody = document.querySelector('#wasteTable tbody');
    tableBody.innerHTML = '<tr><td colspan="10" class="px-6 py-4 text-center text-gray-500">Loading data...</td></tr>';

    fetch('backend/end-points/get_material_waste_report.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                tableBody.innerHTML = data.data.map(item => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${item.prod_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.product}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.member}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.duration}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.actual_output}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">${item.wastage}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.wastage_rate}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${item.labor_cost.toFixed(2)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${item.material_cost.toFixed(2)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">₱${item.total_cost.toFixed(2)}</td>
                    </tr>
                `).join('');
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                            No completed production records found.
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading waste report:', error);
            tableBody.innerHTML = `
                <tr><td colspan="10" class="px-6 py-4 text-center text-red-500">Error loading data.</td></tr>
            `;
        });
}

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
    // Real-time workforce updates every 5 seconds
    updateController.intervals.workforce = setInterval(updateWorkforceManagement, 5000);
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
    const tasksStatusFilter = document.getElementById('tasksStatusFilter');
    const tasksSearchInput = document.getElementById('tasksSearchInput');

    if (tasksStatusFilter) {
        tasksStatusFilter.addEventListener('change', function() {
            filterTasks();
        });
    }

    if (tasksSearchInput) {
        tasksSearchInput.addEventListener('input', function() {
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
    const statusFilter = document.getElementById('tasksStatusFilter')?.value || 'all';
    const searchTerm = (document.getElementById('tasksSearchInput')?.value || '').toLowerCase().trim();
    
    const inProgressRows = document.querySelectorAll('#inProgressTasksTable tbody tr');
    const completedRows = document.querySelectorAll('#completedTasksTable tbody tr');

    inProgressRows.forEach(row => {
        const statusElement = row.querySelector('td:nth-child(4) span');
        const status = statusElement ? statusElement.textContent.trim().toLowerCase() : '';
        const productionId = (row.querySelector('td:nth-child(1)')?.textContent || '').trim().toLowerCase();
        const productName = (row.querySelector('td:nth-child(2)')?.textContent || '').trim().toLowerCase();
        const memberNames = (row.querySelector('td:nth-child(6)')?.textContent || '').trim().toLowerCase();
        
        const statusMatch = statusFilter === 'all' || status.includes(statusFilter);
        const searchMatch = !searchTerm || 
                          productionId.includes(searchTerm) || 
                          productName.includes(searchTerm) || 
                          memberNames.includes(searchTerm);

        row.style.display = (statusMatch && searchMatch) ? '' : 'none';
    });

    completedRows.forEach(row => {
        const productionId = (row.querySelector('td:nth-child(1)')?.textContent || '').trim().toLowerCase();
        const productName = (row.querySelector('td:nth-child(2)')?.textContent || '').trim().toLowerCase();
        const memberNames = (row.querySelector('td:nth-child(5)')?.textContent || '').trim().toLowerCase();
        
        const searchMatch = !searchTerm || 
                          productionId.includes(searchTerm) || 
                          productName.includes(searchTerm) || 
                          memberNames.includes(searchTerm);
        
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
    // Real-time updates handled by updateController
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
                                <button onclick="confirmTaskCompletion('${task.production_id}', '${task.product_name.replace(/'/g, "\\'")}')"
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

// Function to open the Edit Product Modal
function editProduct(prodLineId) {
    const editProductModal = document.getElementById('editProductModal');
    const editProductForm = document.getElementById('editProductForm');
    
    // Clear previous form data and reset visibility
    editProductForm.reset();
    document.getElementById('edit_prod_line_id').value = prodLineId;
    document.getElementById('editDimensionFields').classList.add('hidden');
    document.getElementById('editWeightField').classList.add('hidden');
    document.getElementById('editQuantityField').classList.add('hidden'); // Ensure it's hidden by default

    // Show loading state
    Swal.fire({
        title: 'Loading...',
        text: 'Fetching product details',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch product details
    fetch(`backend/end-points/get_production_item_details.php?prod_line_id=${prodLineId}`)
        .then(response => response.json())
        .then(data => {
            Swal.close(); // Close loading swal
            if (data.success) {
                const item = data.data;
                document.getElementById('edit_product_name').value = item.product_name;
                document.getElementById('edit_length').value = item.length_m;
                document.getElementById('edit_width').value = item.width_m;
                document.getElementById('edit_weight').value = item.weight_g;
                document.getElementById('edit_quantity').value = item.quantity;

                // Trigger change event to update fields visibility
                updateEditFormFieldsVisibility(item.product_name);
                
                editProductModal.classList.remove('hidden');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to fetch product details.'
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error fetching product details:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while fetching product details.'
            });
        });
}

// Helper function to update visibility of fields in edit modal
function updateEditFormFieldsVisibility(selectedProduct) {
    const editDimensionFields = document.getElementById('editDimensionFields');
    const editWeightField = document.getElementById('editWeightField');
    const editQuantityField = document.getElementById('editQuantityField');

    editDimensionFields.classList.add('hidden');
    editWeightField.classList.add('hidden');
    editQuantityField.classList.add('hidden'); // Default hidden

    if (['Piña Seda', 'Pure Piña Cloth'].includes(selectedProduct)) {
        editDimensionFields.classList.remove('hidden');
        editQuantityField.classList.remove('hidden'); // Quantity for dimension products
    } else if (['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'].includes(selectedProduct)) {
        editWeightField.classList.remove('hidden');
        // Quantity remains hidden for weight-based products, as quantity is implicitly 1
    } else {
        // For other products, show quantity by default
        editQuantityField.classList.remove('hidden');
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

                <!-- Row 4: Available Materials & Required Materials -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Materials Reference</label>
                    <div id="materialsList" class="p-3 border rounded-md max-h-56 overflow-y-auto bg-gray-50 text-xs">
                        <div class="text-sm text-gray-500">Select a product to view required and available materials</div>
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

<!-- Task Confirmation Modal -->
<div id="confirmTaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[1001] p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
            <h3 class="text-xl font-semibold text-gray-800">Confirm Task Details</h3>
            <p class="text-sm text-gray-600 mt-1">Please review the task details before creating</p>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <div class="space-y-4">
                <!-- Product Name -->
                <div class="border-b pb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <p id="confirmProductName" class="text-base text-gray-900 font-semibold bg-gray-50 p-3 rounded-md">-</p>
                </div>
                
                <!-- Assigned Member -->
                <div class="border-b pb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                    <p id="confirmAssignedTo" class="text-base text-gray-900 bg-gray-50 p-3 rounded-md">-</p>
                </div>
                
                <!-- Dimensions/Weight Section -->
                <div id="confirmDimensionsSection" class="border-b pb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Length (m)</label>
                            <p id="confirmLength" class="text-base text-gray-900 bg-gray-50 p-3 rounded-md">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Width (in)</label>
                            <p id="confirmWidth" class="text-base text-gray-900 bg-gray-50 p-3 rounded-md">-</p>
                        </div>
                    </div>
                </div>
                
                <!-- Weight Section (for Knotted/Warped) -->
                <div id="confirmWeightSection" class="border-b pb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight (g)</label>
                    <p id="confirmWeight" class="text-base text-gray-900 bg-gray-50 p-3 rounded-md">-</p>
                </div>
                
                <!-- Quantity -->
                <div class="border-b pb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <p id="confirmQuantity" class="text-base text-gray-900 bg-gray-50 p-3 rounded-md">-</p>
                </div>
                
                <!-- Deadline -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                    <p id="confirmDeadline" class="text-base text-gray-900 bg-gray-50 p-3 rounded-md">-</p>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <button type="button" id="editTaskDetailsBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Edit Details
            </button>
            <button type="button" id="confirmCreateTaskBtn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Confirm & Create Task
            </button>
        </div>
    </div>
</div>

<!-- Task Completion Verification Modal -->
<div id="taskCompletionVerificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[1001] p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
            <h3 class="text-xl font-semibold text-gray-800">Verify Product Measurements</h3>
            <p class="text-sm text-gray-600 mt-1">Please verify the actual measurements/weight received from the member</p>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <form id="taskCompletionForm" class="space-y-4">
                <input type="hidden" id="taskCompletionProdLineId" name="prod_line_id">
                
                <!-- Product Name Display -->
                <div class="border-b pb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <p id="taskCompletionProductName" class="text-base text-gray-900 font-semibold bg-gray-50 p-3 rounded-md">-</p>
                </div>
                
                <!-- Expected vs Actual Dimensions Section -->
                <div id="taskCompletionDimensionsSection" class="border-b pb-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Expected Dimensions</h4>
                    <div class="grid grid-cols-2 gap-4 mb-4 bg-blue-50 p-3 rounded-md">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Expected Length (m)</label>
                            <p id="taskCompletionExpectedLength" class="text-sm text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Expected Width (in)</label>
                            <p id="taskCompletionExpectedWidth" class="text-sm text-gray-900">-</p>
                        </div>
                    </div>
                    
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Actual Dimensions (Enter Verified Values)</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="taskCompletionActualLength" class="block text-xs font-medium text-gray-700 mb-1">Actual Length (m) *</label>
                            <input type="number" id="taskCompletionActualLength" name="actual_length" step="0.01" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-2" required>
                        </div>
                        <div>
                            <label for="taskCompletionActualWidth" class="block text-xs font-medium text-gray-700 mb-1">Actual Width (in) *</label>
                            <input type="number" id="taskCompletionActualWidth" name="actual_width" step="0.01" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-2" required>
                        </div>
                    </div>
                </div>
                
                <!-- Expected vs Actual Weight Section -->
                <div id="taskCompletionWeightSection" class="border-b pb-4 hidden">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Expected Weight</h4>
                    <div class="bg-blue-50 p-3 rounded-md mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Expected Weight (g)</label>
                        <p id="taskCompletionExpectedWeight" class="text-sm text-gray-900">-</p>
                    </div>
                    
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Actual Weight (Enter Verified Value)</h4>
                    <div>
                        <label for="taskCompletionActualWeight" class="block text-xs font-medium text-gray-700 mb-1">Actual Weight (g) *</label>
                        <input type="number" id="taskCompletionActualWeight" name="actual_weight" step="0.01" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-2" required>
                    </div>
                </div>
                
                <!-- Variance Notes -->
                <div>
                    <label for="taskCompletionNotes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea id="taskCompletionNotes" name="notes" rows="3" placeholder="Add any notes about measurements discrepancies or observations..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-2"></textarea>
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <button type="button" id="cancelTaskCompletionBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Cancel
            </button>
            <button type="button" id="submitTaskCompletionBtn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Verify & Add to Inventory
            </button>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[1000] p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Edit Production Item</h3>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto flex-1">
            <form id="editProductForm" class="space-y-4">
                <input type="hidden" id="edit_prod_line_id" name="prod_line_id">
                
                <!-- Row 1: Product Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label for="edit_product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <select id="edit_product_name" name="product_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
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
                    <div id="editDimensionFields" class="col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_length" class="block text-xs font-medium text-gray-700 mb-1">Length (m)</label>
                            <input type="number" id="edit_length" name="length" step="0.001" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                        </div>
                        <div>
                            <label for="edit_width" class="block text-xs font-medium text-gray-700 mb-1">Width (in)</label>
                            <input type="number" id="edit_width" name="width" step="0.001" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                        </div>
                    </div>
                    
                    <!-- Weight Field -->
                    <div id="editWeightField" class="hidden">
                        <label for="edit_weight" class="block text-xs font-medium text-gray-700 mb-1">Weight (g)</label>
                        <input type="number" id="edit_weight" name="weight" step="0.001" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                    </div>
                    
                    <!-- Quantity -->
                    <div id="editQuantityField">
                        <label for="edit_quantity" class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" id="edit_quantity" name="quantity" min="1" value="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" id="cancelEditProduct" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Decline Reason Modal -->
<div id="declineReasonModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[1000] p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Task Decline Reason</h3>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <p class="text-sm text-gray-700" id="declineReasonText">Loading...</p>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button type="button" id="closeDeclineReasonModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Reassign Task Modal -->
<div id="reassignTaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[1000] p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Reassign Task</h3>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <form id="reassignTaskForm" class="space-y-4">
                <input type="hidden" id="reassign_task_id" name="task_id">
                <input type="hidden" id="reassign_prod_line_id" name="prod_line_id">
                
                <!-- Product Info -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                    <div id="reassign_product_name" class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">-</div>
                </div>
                
                <!-- Current Member -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Member</label>
                    <div id="reassign_current_member" class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">-</div>
                </div>
                
                <!-- New Member Selection -->
                <div>
                    <label for="reassign_member" class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                    <select id="reassign_member" name="new_member_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                        <option value="">Select a member</option>
                    </select>
                </div>
                
                <!-- Deadline -->
                <div>
                    <label for="reassign_deadline" class="block text-sm font-medium text-gray-700 mb-1">New Deadline</label>
                    <input type="datetime-local" id="reassign_deadline" name="deadline" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <button type="button" id="cancelReassignTask" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                Cancel
            </button>
            <button type="button" id="submitReassignTask" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700">
                Reassign Task
            </button>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200">
        <div class="flex justify-between items-center">
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
                <button id="wasteTab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Material Waste & Cost
                </button>
            </nav>
            <!-- Live Update Indicator with Manual Refresh -->
            <div class="flex items-center space-x-4">
                <button onclick="manualRefreshAllTables()" title="Manually refresh all tables" class="py-2 px-3 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-md transition-colors flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
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
                                                                                    <a href="#" onclick="editProduct('<?php echo $item['raw_id']; ?>'); return false;" class="block px-4 py-2 text-sm text-gray-800 hover:bg-indigo-50 hover:text-indigo-700 transition-colors duration-150" role="menuitem">Edit</a>
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
                        <input type="text" id="tasksSearchInput" placeholder="Search tasks..." class="pl-10 pr-4 py-2 w-full rounded-md border border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="w-48 z-10">
                        <label for="tasksStatusFilter" class="sr-only">Filter by Status</label>
                        <select id="tasksStatusFilter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="declined">Declined</option>
                            <option value="completed">Completed</option>
                            <option value="reassigned">Reassigned</option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Unit</th>
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

<!-- Material Waste & Cost Tab Content -->
<div id="wasteContent" class="tab-content hidden">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Production Waste & Cost Analysis</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="wasteTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Output</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Wastage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wastage Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Labor Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Material Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr><td colspan="10" class="px-6 py-4 text-center text-gray-500">Loading data...</td></tr>
                </tbody>
            </table>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Weight / Size</th>
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
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-semibold text-gray-800">Task Completion Confirmations</h3>
                <div class="flex items-center gap-2">
                    <span id="autoRefreshStatus" class="text-xs text-green-600 font-medium flex items-center gap-1">
                        <span class="inline-block w-2 h-2 bg-green-600 rounded-full animate-pulse"></span>
                        Real-time (3s)
                    </span>
                    <span id="lastUpdateTime" class="text-xs text-gray-500">Updated just now</span>
                </div>
            </div>
            <button id="refreshTaskCompletionBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
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
<div id="taskAssignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-[1000]">
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
// Function to calculate required materials based on form inputs
function calculateRequiredMaterials(productName) {
    const length = parseFloat(document.getElementById('length')?.value) || 0;
    const width = parseFloat(document.getElementById('width')?.value) || 0;
    const quantity = parseFloat(document.getElementById('quantity')?.value) || 1;
    const weight = parseFloat(document.getElementById('weight')?.value) || 0;
    
    const calculations = {
        'Piña Seda': {
            title: 'PIÑA SEDA MATERIAL CALCULATION',
            baseRequirements: {
                description: 'For 1 meter, 30 inches width, quantity = 1:',
                materials: [
                    { name: 'Knotted Liniwan', perUnit: 15, unit: 'g per meter' },
                    { name: 'Warped Silk', perUnit: 7, unit: 'g per meter' }
                ]
            },
            calculated: function() {
                if (length <= 0) return null;
                return {
                    description: `For ${length} meter(s), 30 inches width, quantity = ${quantity}:`,
                    materials: [
                        { 
                            name: 'Knotted Liniwan', 
                            total: (length * 15 * quantity).toFixed(2), 
                            unit: 'g',
                            formula: `${length}m × 15g × ${quantity} = ${(length * 15 * quantity).toFixed(2)}g`
                        },
                        { 
                            name: 'Warped Silk', 
                            total: (length * 7 * quantity).toFixed(2), 
                            unit: 'g',
                            formula: `${length}m × 7g × ${quantity} = ${(length * 7 * quantity).toFixed(2)}g`
                        }
                    ]
                };
            }
        },
        'Pure Piña Cloth': {
            title: 'PURE PIÑA CLOTH MATERIAL CALCULATION',
            baseRequirements: {
                description: 'For 1 meter, 30 inches width, quantity = 1:',
                materials: [
                    { name: 'Knotted Liniwan', perUnit: 15, unit: 'g per meter' },
                    { name: 'Warped Silk', perUnit: 7, unit: 'g per meter' }
                ]
            },
            calculated: function() {
                if (length <= 0) return null;
                return {
                    description: `For ${length} meter(s), 30 inches width, quantity = ${quantity}:`,
                    materials: [
                        { 
                            name: 'Knotted Liniwan', 
                            total: (length * 15 * quantity).toFixed(2), 
                            unit: 'g',
                            formula: `${length}m × 15g × ${quantity} = ${(length * 15 * quantity).toFixed(2)}g`
                        },
                        { 
                            name: 'Warped Silk', 
                            total: (length * 7 * quantity).toFixed(2), 
                            unit: 'g',
                            formula: `${length}m × 7g × ${quantity} = ${(length * 7 * quantity).toFixed(2)}g`
                        }
                    ]
                };
            }
        },
        'Knotted Liniwan': {
            title: 'KNOTTED LINIWAN MATERIAL CALCULATION',
            baseRequirements: {
                description: 'Raw materials needed (per meter):',
                materials: [
                    { name: 'Raw Loose Liniwan', perUnit: 15, unit: 'g per meter' }
                ]
            },
            calculated: function() {
                if (weight <= 0) return null;
                // Standard is 15g per meter, so calculate meters equivalent
                const meterEquivalent = (weight / 15).toFixed(3);
                return {
                    description: `For ${weight}g of product (≈ ${meterEquivalent} meter equivalent):`,
                    materials: [
                        { 
                            name: 'Raw Loose Liniwan', 
                            total: weight.toFixed(2), 
                            unit: 'g',
                            formula: `${weight}g (standard for ${meterEquivalent}m)`
                        }
                    ]
                };
            }
        },
        'Knotted Bastos': {
            title: 'KNOTTED BASTOS MATERIAL CALCULATION',
            baseRequirements: {
                description: 'Raw materials needed (per meter):',
                materials: [
                    { name: 'Raw Loose Bastos', perUnit: 15, unit: 'g per meter' }
                ]
            },
            calculated: function() {
                if (weight <= 0) return null;
                // Standard is 15g per meter, so calculate meters equivalent
                const meterEquivalent = (weight / 15).toFixed(3);
                return {
                    description: `For ${weight}g of product (≈ ${meterEquivalent} meter equivalent):`,
                    materials: [
                        { 
                            name: 'Raw Loose Bastos', 
                            total: weight.toFixed(2), 
                            unit: 'g',
                            formula: `${weight}g (standard for ${meterEquivalent}m)`
                        }
                    ]
                };
            }
        },
        'Warped Silk': {
            title: 'WARPED SILK MATERIAL CALCULATION',
            baseRequirements: {
                description: 'Standard processed material:',
                materials: [
                    { name: 'Warped Silk', perUnit: 32, unit: 'g per meter' }
                ]
            },
            calculated: function() {
                if (weight <= 0) return null;
                const meterEquivalent = (weight / 32).toFixed(3);
                return {
                    description: `For ${weight}g of product (≈ ${meterEquivalent} meter equivalent):`,
                    materials: [
                        { 
                            name: 'Warped Silk', 
                            total: weight.toFixed(2), 
                            unit: 'g',
                            formula: `${weight}g (standard for ${meterEquivalent}m)`
                        }
                    ]
                };
            }
        }
    };
    
    return calculations[productName] || null;
}

// Function to get required materials formula for a product
function getRequiredMaterialsInfo(productName) {
    const materialsInfo = {
        'Piña Seda': {
            title: 'PIÑA SEDA REQUIREMENTS',
            description: 'For 1 meter, 30 inches width, quantity = 1:',
            materials: [
                { name: 'Knotted Liniwan', amount: '15g' },
                { name: 'Warped Silk', amount: '7g' }
            ]
        },
        'Pure Piña Cloth': {
            title: 'PURE PIÑA CLOTH REQUIREMENTS',
            description: 'For 1 meter, 30 inches width, quantity = 1:',
            materials: [
                { name: 'Knotted Liniwan', amount: '15g' },
                { name: 'Warped Silk', amount: '7g' }
            ]
        },
        'Knotted Liniwan': {
            title: 'KNOTTED LINIWAN REQUIREMENTS',
            description: 'Raw materials needed:',
            materials: [
                { name: 'Raw Loose Liniwan', amount: '1 meter ≈ 15g (standard)' }
            ]
        },
        'Knotted Bastos': {
            title: 'KNOTTED BASTOS REQUIREMENTS',
            description: 'Raw materials needed:',
            materials: [
                { name: 'Raw Loose Bastos', amount: '1 meter ≈ 15g (standard)' }
            ]
        },
        'Warped Silk': {
            title: 'WARPED SILK REQUIREMENTS',
            description: 'Standard processed material:',
            materials: [
                { name: 'Warped Silk', amount: '32 grams per 1 meter' }
            ]
        }
    };
    
    return materialsInfo[productName] || null;
}

// Function to fetch and display available materials
function updateMaterialsList(productName) {
    const materialsListDiv = document.getElementById('materialsList');
    materialsListDiv.innerHTML = '<div class="text-sm text-gray-500">Loading materials...</div>'; // Loading indicator

    let url = 'backend/end-points/get_available_materials.php';
    if (productName && productName !== '') {
        url += `?product_name=${encodeURIComponent(productName)}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            materialsListDiv.innerHTML = ''; // Clear previous content
            
            // Add calculated materials info
            const calculations = calculateRequiredMaterials(productName);
            if (calculations) {
                const calculatedSection = document.createElement('div');
                calculatedSection.className = 'mb-4 pb-4 border-b';
                
                let calculatedHTML = `
                    <div class="text-xs font-bold text-green-700 mb-2">${calculations.title}</div>
                `;
                
                // Show calculated totals if values are entered
                const calculated = calculations.calculated();
                if (calculated) {
                    calculatedHTML += `
                        <div class="text-xs text-gray-600 mb-2 bg-green-50 p-2 rounded">${calculated.description}</div>
                        <div class="space-y-1 bg-green-50 p-2 rounded">
                    `;
                    
                    calculated.materials.forEach(material => {
                        calculatedHTML += `
                            <div class="text-xs text-gray-700">
                                <div class="font-semibold text-green-700">• ${material.name}: <span class="text-lg">${material.total} ${material.unit}</span></div>
                                <div class="text-xs text-gray-600 ml-3">Formula: ${material.formula}</div>
                            </div>
                        `;
                    });
                    
                    calculatedHTML += '</div>';
                } else {
                    // Show base requirements if no values entered yet
                    const base = calculations.baseRequirements;
                    calculatedHTML += `
                        <div class="text-xs text-gray-600 mb-2">${base.description}</div>
                        <div class="space-y-1">
                    `;
                    
                    base.materials.forEach(material => {
                        calculatedHTML += `
                            <div class="text-xs text-gray-700 flex justify-between">
                                <span>• ${material.name}</span>
                                <span class="font-semibold text-green-600">${material.perUnit} ${material.unit}</span>
                            </div>
                        `;
                    });
                    
                    calculatedHTML += '</div>';
                }
                
                calculatedSection.innerHTML = calculatedHTML;
                materialsListDiv.appendChild(calculatedSection);
            }
            
            // Add available materials header
            if (data.success && data.materials && data.materials.length > 0) {
                const availableHeader = document.createElement('div');
                availableHeader.className = 'text-xs font-bold text-blue-700 mb-2';
                availableHeader.textContent = 'AVAILABLE MATERIALS IN STOCK';
                materialsListDiv.appendChild(availableHeader);
                
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
                        <span class="text-xs text-gray-700">${materialNameDisplay}</span>
                        <span class="text-xs font-medium ${textColorClass}">
                            ${availabilityText}
                        </span>
                    `;
                    materialsListDiv.appendChild(materialItem);
                });
            } else {
                if (calculations) {
                    // Only show error if we have calculated materials but no available materials
                    const noMaterialsNote = document.createElement('div');
                    noMaterialsNote.className = 'text-xs text-gray-500 italic mt-2';
                    noMaterialsNote.textContent = 'No stock information available';
                    materialsListDiv.appendChild(noMaterialsNote);
                } else {
                    materialsListDiv.innerHTML = `<div class="text-sm text-gray-500">${data.message || 'No materials information available for this product.'}</div>`;
                }
            }
        })
        .catch(error => {
            console.error('Error loading materials:', error);
            materialsListDiv.innerHTML = 
                '<div class="text-sm text-red-600">Error loading materials. Please try again.</div>';
        });
}

// Function to open decline reason modal
function openDeclineReasonModal(reason) {
    const modal = document.getElementById('declineReasonModal');
    document.getElementById('declineReasonText').textContent = reason;
    modal.classList.remove('hidden');
}

// Function to close decline reason modal
document.addEventListener('DOMContentLoaded', function() {
    const closeDeclineBtn = document.getElementById('closeDeclineReasonModal');
    if (closeDeclineBtn) {
        closeDeclineBtn.addEventListener('click', function() {
            document.getElementById('declineReasonModal').classList.add('hidden');
        });
    }
    
    // Close modal when clicking outside
    document.getElementById('declineReasonModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});

// Function to open reassign modal
function openReassignModal(prodLineId, productName, taskId, currentMember, currentRole) {
    const modal = document.getElementById('reassignTaskModal');
    
    // Set the form data
    document.getElementById('reassign_task_id').value = taskId;
    document.getElementById('reassign_prod_line_id').value = prodLineId;
    document.getElementById('reassign_product_name').textContent = productName;
    document.getElementById('reassign_current_member').textContent = `${currentMember} (${currentRole})`;
    
    // Load available members for this role
    loadReassignMembers(currentRole);
    
    // Set default deadline to 7 days from now
    const now = new Date();
    const defaultDeadline = new Date(now.setDate(now.getDate() + 7)).toISOString().slice(0, 16);
    document.getElementById('reassign_deadline').value = defaultDeadline;
    
    // Show modal
    modal.classList.remove('hidden');
}

// Function to load available members for reassignment
function loadReassignMembers(role) {
    fetch(`backend/end-points/get_members_by_role.php?role=${role}`)
        .then(response => response.json())
        .then(members => {
            const select = document.getElementById('reassign_member');
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            if (members && members.length > 0) {
                // Sort members so available ones are at the top
                members.sort((a, b) => {
                    if (a.work_status === 'Available' && b.work_status !== 'Available') return -1;
                    if (a.work_status !== 'Available' && b.work_status === 'Available') return 1;
                    return a.fullname.localeCompare(b.fullname);
                });

                members.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.id;
                    option.textContent = `${member.fullname} - ${member.work_status}`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading members:', error);
            const select = document.getElementById('reassign_member');
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Error loading members';
            option.disabled = true;
            select.appendChild(option);
        });
}

// Function to submit reassign task form
function submitReassignTask() {
    const taskId = document.getElementById('reassign_task_id').value;
    const prodLineId = document.getElementById('reassign_prod_line_id').value;
    const newMemberId = document.getElementById('reassign_member').value;
    const deadline = document.getElementById('reassign_deadline').value;
    
    if (!newMemberId) {
        alert('Please select a member to reassign the task to');
        return;
    }
    
    if (!deadline) {
        alert('Please set a new deadline');
        return;
    }
    
    // Submit the reassignment
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('prod_line_id', prodLineId);
    formData.append('new_member_id', newMemberId);
    formData.append('deadline', deadline);
    
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
                timer: 1500
            }).then(() => {
                document.getElementById('reassignTaskModal').classList.add('hidden');
                refreshTaskAssignments();
            });
        } else {
            alert('Error: ' + (data.message || 'Failed to reassign task'));
            if (data.message && data.message.includes('task limit')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Task Limit Reached',
                    text: data.message
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to reassign task'
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while reassigning the task');
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while reassigning the task'
        });
    });
}

// Function to fetch and display available materials and members
function loadModalData() {
    // Load initial materials list (empty or for default selected product)
    const initialSelectedProduct = document.getElementById('product_name').value;
    updateMaterialsList(initialSelectedProduct);
    
    // Load initial members (all available members if no product selected yet)
    updateAvailableMembers('');
}

// Function to update available members based on selected product
function updateAvailableMembers(productName) {
    let url = 'backend/end-points/get_members_by_role.php?role=all';
    
    // Map product names to required roles
    if (productName) {
        const productRoleMap = {
            'Piña Seda': 'weaver',
            'Pure Piña Cloth': 'weaver',
            'Knotted Liniwan': 'knotter',
            'Knotted Bastos': 'knotter',
            'Warped Silk': 'warper'
        };
        
        if (productRoleMap[productName]) {
            url = 'backend/end-points/get_members_by_role.php?role=' + productRoleMap[productName];
        }
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
                // Sort members so available ones are at the top
                members.sort((a, b) => {
                    if (a.work_status === 'Available' && b.work_status !== 'Available') return -1;
                    if (a.work_status !== 'Available' && b.work_status === 'Available') return 1;
                    return a.fullname.localeCompare(b.fullname);
                });

                members.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.id;
                    option.textContent = `${member.fullname} (${member.role}) - ${member.work_status}`;
                    assignedToSelect.appendChild(option);
                });
                
                // Auto-select the first available member if any
                const availableMembers = members.filter(member => member.work_status === 'Available');
                if (availableMembers.length > 0) {
                    assignedToSelect.value = availableMembers[0].id;
                }
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No members available';
                option.disabled = true;
                assignedToSelect.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Error loading members:', error);
            const assignedToSelect = document.getElementById('assigned_to');
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Error loading members';
            option.disabled = true;
            assignedToSelect.appendChild(option);
        });
}


// Function to set minimum date for deadline (today)
function setMinDeadlineDate() {
    const now = new Date();
    // Format: YYYY-MM-DDTHH:MM
    const minDate = now.toISOString().slice(0, 16);
    document.getElementById('deadline').min = minDate;
    // Set default deadline to 7 days from now
    const defaultDeadline = new Date(now.setDate(now.getDate() + 7)).toISOString().slice(0, 16);
    document.getElementById('deadline').value = defaultDeadline;
}

// Function to show task confirmation modal
function showTaskConfirmation(productName, memberName, length, width, weight, quantity, deadline, selectedProduct) {
    const confirmTaskModal = document.getElementById('confirmTaskModal');
    const createTaskModal = document.getElementById('createTaskModal');
    
    // Populate confirmation modal fields
    document.getElementById('confirmProductName').textContent = productName;
    document.getElementById('confirmAssignedTo').textContent = memberName;
    
    // Format deadline to readable format
    const deadlineDate = new Date(deadline);
    const formattedDeadline = deadlineDate.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    document.getElementById('confirmDeadline').textContent = formattedDeadline;
    
    // Handle different product types
    const isKnottedProduct = ['Knotted Liniwan', 'Knotted Bastos'].includes(selectedProduct);
    const isWarpedSilk = selectedProduct === 'Warped Silk';
    const isDimensionsProduct = ['Piña Seda', 'Pure Piña Cloth'].includes(selectedProduct);
    
    const confirmDimensionsSection = document.getElementById('confirmDimensionsSection');
    const confirmWeightSection = document.getElementById('confirmWeightSection');
    
    if (isKnottedProduct || isWarpedSilk) {
        // Show weight, hide dimensions
        confirmDimensionsSection.classList.add('hidden');
        confirmWeightSection.classList.remove('hidden');
        document.getElementById('confirmWeight').textContent = weight && weight !== '-' ? weight + ' g' : '-';
        document.getElementById('confirmQuantity').textContent = '1';
    } else if (isDimensionsProduct) {
        // Show dimensions and quantity, hide weight
        confirmDimensionsSection.classList.remove('hidden');
        confirmWeightSection.classList.add('hidden');
        document.getElementById('confirmLength').textContent = length && length !== '-' ? length + ' m' : '-';
        document.getElementById('confirmWidth').textContent = width && width !== '-' ? width + ' in' : '-';
        document.getElementById('confirmQuantity').textContent = quantity && quantity !== '-' ? quantity : '1';
    } else {
        // Show dimensions and quantity
        confirmDimensionsSection.classList.remove('hidden');
        confirmWeightSection.classList.add('hidden');
        document.getElementById('confirmLength').textContent = length && length !== '-' ? length + ' m' : '-';
        document.getElementById('confirmWidth').textContent = width && width !== '-' ? width + ' in' : '-';
        document.getElementById('confirmQuantity').textContent = quantity && quantity !== '-' ? quantity : '1';
    }
    
    // Hide create task modal and show confirmation modal
    createTaskModal.classList.add('hidden');
    confirmTaskModal.classList.remove('hidden');
}

// Initialize the create task form
// Initialize search functionality for assigned tasks
document.addEventListener('DOMContentLoaded', function() {
    // Store form data for confirmation (declare here for scope with confirmation handlers)
    let pendingTaskFormData = null;

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
        loadModalData();
        setMinDeadlineDate();
    });

    // Hide modal
    cancelCreateTask.addEventListener('click', function() {
        createTaskModal.classList.add('hidden');
    });

    // Toggle fields based on product type
    productNameSelect.addEventListener('change', function() {
        const selectedProduct = this.value;
        
        // Update available members based on selected product
        updateAvailableMembers(selectedProduct);
        
        // Reset all fields
        dimensionFields.classList.add('hidden');
        weightField.classList.add('hidden');
        
        // Show/hide fields based on product type
        if (['Piña Seda', 'Pure Piña Cloth'].includes(selectedProduct)) {
            dimensionFields.classList.remove('hidden');
            weightField.classList.add('hidden');
        } else if (['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'].includes(selectedProduct)) {
            dimensionFields.classList.add('hidden');
            weightField.classList.remove('hidden');
        }

        // Update materials list based on selected product
        updateMaterialsList(selectedProduct);
    });

    // Add event listeners to recalculate materials when dimensions/weight change
    const lengthInput = document.getElementById('length');
    const widthInput = document.getElementById('width');
    const quantityInput = document.getElementById('quantity');
    const weightInput = document.getElementById('weight');
    
    const onMaterialsFieldChange = function() {
        const selectedProduct = productNameSelect.value;
        if (selectedProduct) {
            updateMaterialsList(selectedProduct);
        }
    };
    
    if (lengthInput) lengthInput.addEventListener('input', onMaterialsFieldChange);
    if (widthInput) widthInput.addEventListener('input', onMaterialsFieldChange);
    if (quantityInput) quantityInput.addEventListener('input', onMaterialsFieldChange);
    if (weightInput) weightInput.addEventListener('input', onMaterialsFieldChange);

    // Handle Create Task Form Submission
    document.getElementById('createTaskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const selectedProduct = formData.get('product_name');
        const assignedTo = formData.get('assigned_to');
        const deadline = formData.get('deadline');
        
        // Validate required fields
        if (!selectedProduct) {
            alert('Please select a product');
            return;
        }
        
        if (!assignedTo) {
            alert('Please assign this task to a member');
            return;
        }
        
        if (!deadline) {
            alert('Please set a deadline for this task');
            return;
        }
        
        // Set quantity to 1 for Knotted products and Warped Silk
        if (selectedProduct === 'Knotted Liniwan' || selectedProduct === 'Knotted Bastos' || selectedProduct === 'Warped Silk') {
            formData.set('quantity', '1');
        }
        
        // Get member name from the select option text
        const assignedToSelect = document.getElementById('assigned_to');
        const selectedOption = assignedToSelect.options[assignedToSelect.selectedIndex];
        const memberName = selectedOption ? selectedOption.text : 'Unknown';
        
        // Store the form data for confirmation
        pendingTaskFormData = formData;
        
        // Populate confirmation modal with form details
        showTaskConfirmation(
            selectedProduct,
            memberName,
            formData.get('length') || '-',
            formData.get('width') || '-',
            formData.get('weight') || '-',
            formData.get('quantity') || '1',
            deadline,
            selectedProduct
        );
    });

    // Handle reassign task modal
    const cancelReassignTask = document.getElementById('cancelReassignTask');
    const submitReassignTaskBtn = document.getElementById('submitReassignTask');
    const reassignTaskModal = document.getElementById('reassignTaskModal');

    // Hide reassign modal
    cancelReassignTask.addEventListener('click', function() {
        reassignTaskModal.classList.add('hidden');
    });

    // Submit reassign form
    submitReassignTaskBtn.addEventListener('click', function() {
        submitReassignTask();
    });

    // Handle Edit Product Modal
    const cancelEditProductBtn = document.getElementById('cancelEditProduct');
    const editProductNameSelect = document.getElementById('edit_product_name');
    const editProductForm = document.getElementById('editProductForm');
    const editProductModal = document.getElementById('editProductModal');

    // Hide edit modal
    if (cancelEditProductBtn) {
        cancelEditProductBtn.addEventListener('click', function() {
            editProductModal.classList.add('hidden');
        });
    }

    // Toggle fields based on product type in edit modal
    if (editProductNameSelect) {
        editProductNameSelect.addEventListener('change', function() {
            updateEditFormFieldsVisibility(this.value);
        });
    }

    // Handle Edit Product Form submission
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const selectedProduct = formData.get('product_name');
            
            // Client-side validation (basic example)
            if (!selectedProduct) {
                Swal.fire('Error', 'Please select a product', 'error');
                return;
            }
            
            // Set quantity to 1 for Knotted products and Warped Silk if not explicitly set
            if (['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'].includes(selectedProduct)) {
                if (!formData.get('weight')) {
                    Swal.fire('Error', 'Weight is required for this product type.', 'error');
                    return;
                }
                formData.set('quantity', '1'); // Quantity is implicitly 1 for these
            } else if (['Piña Seda', 'Pure Piña Cloth'].includes(selectedProduct)) {
                if (!formData.get('length') || !formData.get('width') || !formData.get('quantity')) {
                    Swal.fire('Error', 'Length, Width, and Quantity are required for this product type.', 'error');
                    return;
                }
            } else {
                 if (!formData.get('quantity')) {
                    Swal.fire('Error', 'Quantity is required for this product type.', 'error');
                    return;
                }
            }


            // Show loading state
            Swal.fire({
                title: 'Saving...',
                text: 'Updating product details',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('backend/end-points/update_production_item.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Production item updated successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        editProductModal.classList.add('hidden');
                        // Since production_items are defined in PHP, a full reload is needed to update the monitoring table.
                        // Ideally, we'd have a refreshProductionItems() function to call here.
                        location.reload(); 
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update production item.'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error updating production item:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the production item.'
                });
            });
        });
    }

    // Handle confirmation modal buttons
    const confirmTaskModal = document.getElementById('confirmTaskModal');
    const editTaskDetailsBtn = document.getElementById('editTaskDetailsBtn');
    const confirmCreateTaskBtn = document.getElementById('confirmCreateTaskBtn');

    // Edit Details Button - goes back to create task modal
    editTaskDetailsBtn.addEventListener('click', function() {
        confirmTaskModal.classList.add('hidden');
        createTaskModal.classList.remove('hidden');
    });

    // Confirm Create Task Button - submits the form
    confirmCreateTaskBtn.addEventListener('click', function() {
        if (!pendingTaskFormData) {
            alert('Form data is missing. Please try again.');
            return;
        }

        fetch('backend/end-points/create_production_item.php', {
            method: 'POST',
            body: pendingTaskFormData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Task created and assigned successfully!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    document.getElementById('createTaskModal').classList.add('hidden');
                    confirmTaskModal.classList.add('hidden');
                    document.getElementById('createTaskForm').reset();
                    pendingTaskFormData = null;
                    location.reload();
                });
            } else {
                if (data.message && data.message.includes('task limit')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Task Limit Reached',
                        text: data.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to create task'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while creating the task. Please check the console for details.'
            });
        });
    });

    // Handle task completion verification modal buttons
    const taskCompletionVerificationModal = document.getElementById('taskCompletionVerificationModal');
    const cancelTaskCompletionBtn = document.getElementById('cancelTaskCompletionBtn');
    const submitTaskCompletionBtn = document.getElementById('submitTaskCompletionBtn');
    const taskCompletionForm = document.getElementById('taskCompletionForm');

    // Close modal
    cancelTaskCompletionBtn.addEventListener('click', function() {
        taskCompletionVerificationModal.classList.add('hidden');
        taskCompletionForm.reset();
    });

    // Submit task completion
    submitTaskCompletionBtn.addEventListener('click', function() {
        // Validate form fields
        const prodLineId = document.getElementById('taskCompletionProdLineId').value;
        const productName = document.getElementById('taskCompletionProductName').textContent;
        const isKnottedProduct = ['Knotted Liniwan', 'Knotted Bastos'].includes(productName);
        const isWarpedSilk = productName === 'Warped Silk';
        
        let actualLength, actualWidth, actualWeight;
        
        if (isKnottedProduct || isWarpedSilk) {
            actualWeight = document.getElementById('taskCompletionActualWeight').value;
            if (!actualWeight) {
                alert('Please enter the actual weight');
                return;
            }
        } else {
            actualLength = document.getElementById('taskCompletionActualLength').value;
            actualWidth = document.getElementById('taskCompletionActualWidth').value;
            if (!actualLength || !actualWidth) {
                alert('Please enter both actual length and width');
                return;
            }
        }

        // Submit the confirmation
        submitConfirmation(prodLineId, actualWeight, actualLength, actualWidth);
        
        // Close modal
        taskCompletionVerificationModal.classList.add('hidden');
        taskCompletionForm.reset();
    });

    // Handle form submission
    // Note: Form submission is handled in the DOMContentLoaded event listener above
    
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
});
</script>
</body>
</html>