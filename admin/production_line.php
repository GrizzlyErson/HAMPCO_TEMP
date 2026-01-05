<?php 
include "components/header.php";
// Add custom CSS for tables
echo '<link rel="stylesheet" href="css/table-styles.css">';





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
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Hide all tab contents
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Remove active styling from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('border-indigo-500', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Add active styling to clicked button
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-indigo-500', 'text-indigo-600');
            
            // Show the corresponding content
            const contentId = this.id.replace('Tab', 'Content');
            const content = document.getElementById(contentId);
            if (content) {
                content.classList.remove('hidden');
                // Trigger any data loading if needed
                if (contentId === 'memberTaskRequestsContent') {
                    loadTaskRequests();
                    loadTaskCompletions();
                } else if (contentId === 'completedTasksContent') {
                    loadCompletedTasks();
                } else if (contentId === 'rawMaterialsContent') {
                    loadRawMaterials();
                }
            }
        });
    });

    // Initialize the first tab as active
    const monitoringTab = document.getElementById('monitoringTab');
    if (monitoringTab) {
        monitoringTab.click();
    }
});

// Filter production items in the search
function filterProductionItems() {
    const searchInput = document.getElementById('searchInput');
    const table = document.querySelector('table tbody');
    const rows = table.querySelectorAll('tr');
    const searchTerm = searchInput.value.toLowerCase();

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;
        
        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(searchTerm)) {
                match = true;
            }
        });
        
        row.style.display = match ? '' : 'none';
    });
}

// Show materials modal
function showMaterialsModal(materialsData, productData) {
    if (typeof materialsData === 'string') {
        try {
            materialsData = JSON.parse(materialsData);
        } catch (e) {
            console.error('Error parsing materials data:', e);
            return;
        }
    }
    
    if (typeof productData === 'string') {
        try {
            productData = JSON.parse(productData);
        } catch (e) {
            console.error('Error parsing product data:', e);
            return;
        }
    }
    
    // Create modal HTML
    const modalContent = `
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-[1001] p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Required Raw Materials</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4">
                    <p class="mb-4 text-sm text-gray-600"><strong>Product:</strong> ${productData.name || 'N/A'}</p>
                    ${materialsData.success ? `
                        <div class="space-y-2">
                            ${materialsData.materials && materialsData.materials.length > 0 ? 
                                materialsData.materials.map(material => `
                                    <div class="flex justify-between p-2 bg-gray-50 rounded">
                                        <span class="text-sm text-gray-700">
                                            ${material.name}
                                            ${material.category ? ` (${material.category})` : ''}
                                        </span>
                                        <span class="text-sm font-medium text-gray-800">
                                            ${material.amount} ${material.unit}
                                        </span>
                                    </div>
                                `).join('')
                                : '<p class="text-gray-500">No materials information available</p>'
                            }
                        </div>
                    ` : `<p class="text-red-600">${materialsData.message || 'Error loading materials'}</p>`}
                </div>
            </div>
        </div>
    `;
    
    // Insert modal into DOM
    document.body.insertAdjacentHTML('beforeend', modalContent);
}

// Assign task function
function assignTask(prodLineId, productName, quantity) {
    // Show the create task modal and populate it
    const modal = document.getElementById('createTaskModal');
    const form = document.getElementById('createTaskForm');
    
    if (modal) {
        // Set product name
        document.getElementById('product_name').value = productName;
        
        // Load materials and members
        loadModalDataForCreateTask();
        
        // Store the production line ID for later use
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'prod_line_id';
        hiddenInput.value = prodLineId;
        form.appendChild(hiddenInput);
        
        // Show modal
        modal.classList.remove('hidden');
    }
}

// Delete product function
function deleteProduct(prodLineId) {
    if (confirm('Are you sure you want to delete this production line item?')) {
        const formData = new FormData();
        formData.append('prod_line_id', prodLineId);
        
        fetch('backend/end-points/delete_production_line.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Production line item deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the item');
        });
    }
}

// Task request functions
function loadTaskRequests() {
    fetch('backend/end-points/get_task_requests.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#taskApprovalTable tbody');
            if (!tableBody) return;
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No requests found</td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = data.map(request => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono">${request.request_id}</td>
                    <td class="px-6 py-4 text-sm">${request.member_name}</td>
                    <td class="px-6 py-4 text-sm">${request.role}</td>
                    <td class="px-6 py-4 text-sm">${request.product_type}</td>
                    <td class="px-6 py-4 text-sm">${request.weight_g || '-'}</td>
                    <td class="px-6 py-4 text-sm">${request.quantity || '1'}</td>
                    <td class="px-6 py-4 text-sm">${request.request_date}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(request.status)}">
                            ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        ${request.status === 'pending' ? `
                            <div class="flex space-x-2">
                                <button onclick="handleTaskRequest(${request.request_id}, 'approve')"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                                    Approve
                                </button>
                                <button onclick="handleTaskRequest(${request.request_id}, 'decline')"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                                    Decline
                                </button>
                            </div>
                        ` : '-'}
                    </td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading task requests:', error);
            const tableBody = document.querySelector('#taskApprovalTable tbody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-red-500">Error loading requests. Please try again.</td>
                    </tr>
                `;
            }
        });
}

function loadTaskCompletions() {
    fetch('backend/end-points/get_task_completions.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#taskCompletionTable tbody');
            if (!tableBody) return;
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No completion requests found</td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = data.map(task => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono">${task.prod_line_id}</td>
                    <td class="px-6 py-4 text-sm">${task.product_name}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(task.status)}">
                            ${task.status.charAt(0).toUpperCase() + task.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">${task.date_created}</td>
                    <td class="px-6 py-4 text-sm">${task.member_name}</td>
                    <td class="px-6 py-4 text-sm">
                        ${task.status === 'submitted' ? `
                            <button onclick="confirmTaskCompletion(${task.prod_line_id})"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm transition-colors">
                                Confirm Task Completion
                            </button>
                        ` : '-'}
                    </td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading task completions:', error);
            const tableBody = document.querySelector('#taskCompletionTable tbody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-red-500">Error loading completion requests. Please try again.</td>
                    </tr>
                `;
            }
        });
}

function getStatusClass(status) {
    switch (status ? status.toLowerCase() : '') {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'declined':
            return 'bg-red-100 text-red-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'submitted':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function handleTaskRequest(requestId, action) {
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', action);

    fetch('backend/end-points/handle_task_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Task request ${action}ed successfully`);
            loadTaskRequests();
        } else {
            throw new Error(data.message || 'Failed to process request');
        }
    })
    .catch(error => {
        console.error('Error handling task request:', error);
        alert('Error: ' + error.message);
    });
}

function confirmTaskCompletion(prodLineId) {
    if (confirm('Are you sure you want to confirm this task as completed?')) {
        const formData = new FormData();
        formData.append('prod_line_id', prodLineId);

        fetch('backend/end-points/confirm_task_completion.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Task completion has been confirmed');
                loadTaskCompletions();
                loadTaskRequests();
            } else {
                throw new Error(data.message || 'Failed to confirm task completion');
            }
        })
        .catch(error => {
            console.error('Error confirming task completion:', error);
            alert('Error: ' + error.message);
        });
    }
}

// Load completed tasks
function loadCompletedTasks() {
    fetch('backend/end-points/get_completed_tasks.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#completedTasksTable tbody');
            if (!tableBody) return;

            if (!data.success || !data.data || data.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No completed tasks found</td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = data.data.map(task => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.product_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.member_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.role}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.measurements || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.weight_g || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.quantity || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${task.completed_date}</td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading completed tasks:', error);
            const tableBody = document.querySelector('#completedTasksTable tbody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-red-500">Error loading completed tasks. Please try again.</td>
                    </tr>
                `;
            }
        });
}

// Load raw materials
function loadRawMaterials() {
    // Load raw materials table
    fetch('backend/end-points/list_raw_material.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#rawMaterialsTable tbody');
            if (!tableBody) return;

            if (!data || data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No raw materials found</td>
                    </tr>
                `;
            } else {
                tableBody.innerHTML = data.map(material => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">${material.raw_materials_name || '-'}</td>
                        <td class="px-6 py-4 text-sm">${material.category || '-'}</td>
                        <td class="px-6 py-4 text-sm">${material.weight || '-'}</td>
                        <td class="px-6 py-4 text-sm">₱${parseFloat(material.unit_cost || 0).toFixed(2)}</td>
                        <td class="px-6 py-4 text-sm">₱${parseFloat((material.weight || 0) * (material.unit_cost || 0)).toFixed(2)}</td>
                        <td class="px-6 py-4 text-sm">${material.supplier_name || '-'}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full ${material.status === 'Available' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${material.status || '-'}
                            </span>
                        </td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error loading raw materials:', error);
            const tableBody = document.querySelector('#rawMaterialsTable tbody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-red-500">Error loading raw materials. Please try again.</td>
                    </tr>
                `;
            }
        });

    // Load raw materials summary
    fetch('backend/end-points/get_materials_summary.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const availableList = document.getElementById('availableRawMaterialsList');
                const processedList = document.getElementById('processedMaterialsList');
                const finishedList = document.getElementById('finishedProductsList');

                if (availableList && data.raw_materials) {
                    availableList.innerHTML = data.raw_materials.length > 0 ?
                        data.raw_materials.map(m => `
                            <div class="flex justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm">${m.name}</span>
                                <span class="text-sm font-medium">${m.quantity}g</span>
                            </div>
                        `).join('') : '<div class="text-sm text-gray-500">No raw materials</div>';
                }

                if (processedList && data.processed_materials) {
                    processedList.innerHTML = data.processed_materials.length > 0 ?
                        data.processed_materials.map(m => `
                            <div class="flex justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm">${m.name}</span>
                                <span class="text-sm font-medium">${m.quantity}g</span>
                            </div>
                        `).join('') : '<div class="text-sm text-gray-500">No processed materials</div>';
                }

                if (finishedList && data.finished_products) {
                    finishedList.innerHTML = data.finished_products.length > 0 ?
                        data.finished_products.map(p => `
                            <div class="flex justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm">${p.name}</span>
                                <span class="text-sm font-medium">${p.quantity}g</span>
                            </div>
                        `).join('') : '<div class="text-sm text-gray-500">No finished products</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading materials summary:', error);
        });
}

// Modal handling
document.addEventListener('DOMContentLoaded', function() {
    const createTaskBtn = document.getElementById('createTaskBtn');
    const createTaskModal = document.getElementById('createTaskModal');
    const cancelCreateTask = document.getElementById('cancelCreateTask');
    const createTaskForm = document.getElementById('createTaskForm');
    const productNameSelect = document.getElementById('product_name');

    if (createTaskBtn && createTaskModal) {
        createTaskBtn.addEventListener('click', function() {
            if (createTaskForm) {
                createTaskForm.reset();
            }
            createTaskModal.classList.remove('hidden');
        });

        if (cancelCreateTask) {
            cancelCreateTask.addEventListener('click', function() {
                createTaskModal.classList.add('hidden');
            });
        }

        createTaskModal.addEventListener('click', function(e) {
            if (e.target === createTaskModal) {
                createTaskModal.classList.add('hidden');
            }
        });
    }

    if (productNameSelect) {
        productNameSelect.addEventListener('change', function() {
            updateMaterialsList(this.value);
            loadModalDataForCreateTask();
        });
    }

    if (createTaskForm) {
        createTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Add form submission logic here
            console.log('Form submitted');
        });
    }
});

































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

                <!-- Row 3: Deadline -->
                <div>
                    <label for="deadline" class="block text-xs font-medium text-gray-700 mb-1">Deadline</label>
                    <input type="datetime-local" id="deadline" name="deadline" min="" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                </div>

                <!-- Row 5: Assigned To -->
                <div>
                    <label for="assigned_to" class="block text-xs font-medium text-gray-700 mb-1">Assigned To</label>
                    <select id="assigned_to" name="assigned_to" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                        <option value="">Select a member</option>
                    </select>
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
            <button id="memberTaskRequestsTab" class="tab-button border-transparent text-gray-500 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700">
                Member Task Requests
            </button>
            <button id="completedTasksTab" class="tab-button border-transparent text-gray-500 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700">
                Completed Tasks
            </button>
            <button id="rawMaterialsTab" class="tab-button border-transparent text-gray-500 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700">
                Raw Materials
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
                                    $productName = $item['product_name'];
                                    $quantity = $item['quantity'];
                                    $length = $item['length_m'];
                                    $width = $item['width_m'];
                                    $weight = $item['weight_g'];
                                    
                                    $materials = [];
                                    if ($productName === 'Piña Seda') {
                                        if ($length !== null) {
                                            $requiredKnottedBastos = 15 * floatval($length) * intval($quantity);
                                            $materials[] = ['name' => 'Knotted Bastos', 'category' => null, 'amount' => round($requiredKnottedBastos, 2), 'unit' => 'g'];
                                            $requiredWarpedSilk = 7 * floatval($length) * intval($quantity);
                                            $materials[] = ['name' => 'Warped Silk', 'category' => null, 'amount' => round($requiredWarpedSilk, 2), 'unit' => 'g'];
                                        }
                                    } else if ($productName === 'Pure Piña Cloth') {
                                        if ($length !== null) {
                                            $requiredKnottedLiniwan = 22 * floatval($length) * intval($quantity);
                                            $materials[] = ['name' => 'Knotted Liniwan', 'category' => null, 'amount' => round($requiredKnottedLiniwan, 2), 'unit' => 'g'];
                                        }
                                    } else if ($productName === 'Knotted Liniwan' && $weight !== null) {
                                        $requiredPinaLoose = 1.22 * floatval($weight) * intval($quantity);
                                        $materials[] = ['name' => 'Piña Loose', 'category' => 'Liniwan/Washout', 'amount' => round($requiredPinaLoose, 2), 'unit' => 'g'];
                                    } else if ($productName === 'Knotted Bastos' && $weight !== null) {
                                        $requiredPinaLoose = 1.22 * floatval($weight) * intval($quantity);
                                        $materials[] = ['name' => 'Piña Loose', 'category' => 'Bastos', 'amount' => round($requiredPinaLoose, 2), 'unit' => 'g'];
                                    } else if ($productName === 'Warped Silk' && $weight !== null) {
                                        $requiredSilk = 1.2 * floatval($weight) * intval($quantity);
                                        $materials[] = ['name' => 'Silk', 'category' => null, 'amount' => round($requiredSilk, 2), 'unit' => 'g'];
                                    }
                                    
                                    echo htmlspecialchars(json_encode(['success' => true, 'materials' => $materials], JSON_HEX_APOS | JSON_HEX_QUOT)); 
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated later -->
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No requests found</td>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member's Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated later -->
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No completion requests found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Completed Tasks Tab Content -->
<div id="completedTasksContent" class="tab-content hidden">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Completed Tasks</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="completedTasksTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Measurements</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (g)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated later -->
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No completed tasks found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Raw Materials Tab Content -->
<div id="rawMaterialsContent" class="tab-content hidden">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Available Raw Materials -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Available Raw Materials</h3>
            <div id="availableRawMaterialsList" class="space-y-2">
                <div class="text-sm text-gray-500">Loading...</div>
            </div>
        </div>

        <!-- Processed Materials -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Processed Materials</h3>
            <div id="processedMaterialsList" class="space-y-2">
                <div class="text-sm text-gray-500">Loading...</div>
            </div>
        </div>

        <!-- Finished Products -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Finished Products</h3>
            <div id="finishedProductsList" class="space-y-2">
                <div class="text-sm text-gray-500">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Detailed Raw Materials Table -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Raw Materials Inventory</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="rawMaterialsTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (g)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost (₱)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value (₱)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated later -->
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No raw materials found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>









<?php include "components/footer.php"; ?>

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
    const selectedProduct = document.getElementById('product_name').value;
    const assignedToSelect = document.getElementById('assigned_to');
    
    // Clear existing options
    assignedToSelect.innerHTML = '<option value="">Loading...</option>';

    if (!selectedProduct) {
        assignedToSelect.innerHTML = '<option value="">Select a product first</option>';
        return;
    }

    // Map product to the required role
    const productRoleMap = {
        'Piña Seda': 'Weaver',
        'Pure Piña Cloth': 'Weaver',
        'Knotted Liniwan': 'Knotter',
        'Knotted Bastos': 'Knotter',
        'Warped Silk': 'Warper'
    };

    const requiredRole = productRoleMap[selectedProduct];

    if (!requiredRole) {
        assignedToSelect.innerHTML = '<option value="">No members for this product</option>';
        return;
    }
    
    // Fetch members with the specific role
    fetch(`backend/end-points/get_members_by_role.php?role=${requiredRole}`)
        .then(response => response.json())
        .then(members => {
            assignedToSelect.innerHTML = '<option value="">Select a member</option>';
            if (Array.isArray(members) && members.length > 0) {
                members.forEach(member => {
                    if (member.work_status === 'Available') {
                        const option = document.createElement('option');
                        option.value = member.id;
                        option.textContent = `${member.fullname} (${member.role})`;
                        assignedToSelect.appendChild(option);
                    }
                });
            } else {
                assignedToSelect.innerHTML = `<option value="">No available ${requiredRole.toLowerCase()}s found</option>`;
            }
        })
        .catch(error => {
            console.error('Error fetching members:', error);
            assignedToSelect.innerHTML = '<option value="">Error loading members</option>';
        });
}
</script>
</body>
</html>
