<?php 
require_once "../function/database.php";
include "components/header.php";

$db = new Database();

// Get member's current availability status and role
$stmt = $db->conn->prepare("SELECT availability_status, role FROM user_member WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$current_status = $member['availability_status'] ?? 'available';
$member_role = strtolower($member['role']);

// Get production tasks directly using the database connection
$member_id = $_SESSION['id'];

// Get new tasks (tasks specifically assigned to this member but not yet accepted)
$new_tasks_query = "SELECT DISTINCT
    pl.prod_line_id,
    pl.product_name,
    pl.length_m,
    pl.width_m,
    pl.weight_g,
    pl.quantity,
    pl.status as prod_status,
    MIN(ta.status) as task_status,
    MIN(ta.deadline) as deadline,
    MIN(ta.id) as task_id
    FROM production_line pl
    JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
    WHERE ta.member_id = ? 
    AND ta.status = 'pending'
    AND pl.status NOT IN ('completed', 'submitted')
    AND NOT EXISTS (
        SELECT 1 
        FROM task_assignments ta2 
        WHERE ta2.prod_line_id = pl.prod_line_id 
        AND ta2.member_id = ta.member_id 
        AND ta2.status IN ('in_progress', 'completed', 'submitted', 'declined')
    )
    GROUP BY pl.prod_line_id
    ORDER BY pl.date_created DESC";

$stmt = $db->conn->prepare($new_tasks_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$new_tasks_result = $stmt->get_result();
$new_tasks = [];

while ($row = $new_tasks_result->fetch_assoc()) {
    $row['display_id'] = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
    $row['status'] = $row['task_status'] ?? 'pending';
    $new_tasks[] = $row;
}

// Get assigned tasks (tasks that have been accepted/started)
$assigned_tasks_query = "SELECT 
    pl.prod_line_id,
    pl.product_name,
    pl.length_m,
    pl.width_m,
    pl.weight_g,
    pl.quantity,
    ta.status,
    ta.created_at as date_started,
    CASE 
        WHEN ta.status = 'completed' OR ta.status = 'submitted' THEN ta.updated_at 
        ELSE NULL 
    END as date_submitted
    FROM production_line pl
    JOIN task_assignments ta ON pl.prod_line_id = ta.prod_line_id
    WHERE ta.member_id = ? 
    AND ta.status NOT IN ('pending', 'completed')  -- Exclude pending and completed tasks
    AND pl.status NOT IN ('completed', 'submitted')  -- Exclude tasks from completed/submitted production lines
    ORDER BY ta.created_at DESC";

$stmt = $db->conn->prepare($assigned_tasks_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$assigned_tasks_result = $stmt->get_result();
$assigned_tasks = [];

while ($row = $assigned_tasks_result->fetch_assoc()) {
    $row['display_id'] = 'PL' . str_pad($row['prod_line_id'], 4, '0', STR_PAD_LEFT);
    $assigned_tasks[] = $row;
}
?>

<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Production</h2>
        <div class="flex space-x-4">
            <!-- Availability Toggle Buttons -->
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

    <!-- Nav tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button class="border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" id="assigned-tab" role="tab" aria-selected="true">
                Assigned
            </button>
            <button class="text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm" id="created-tab" role="tab" aria-selected="false">
                Task Created
            </button>
            <button class="text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm" id="earnings-tab" role="tab" aria-selected="false">
                My Earnings
            </button>
        </nav>
    </div>

    <!-- Tab content -->
    <div class="tab-content">
        <!-- Assigned Tab -->
        <div class="tab-pane show active" id="assigned" role="tabpanel">
            <!-- New Tasks Available Table -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">New Tasks Available</h3>
                <div class="overflow-x-auto">
                    <table id="newTasksTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <?php if ($member_role === 'knotter'): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (g)</th>
                                <?php elseif ($member_role !== 'warper'): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Length (m)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Width (in)</th>
                                <?php endif; ?>
                                <?php if ($member_role === 'weaver'): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <?php endif; ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($new_tasks as $task): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($task['display_id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($task['product_name']); ?></td>
                                <?php if ($member_role === 'knotter'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['weight_g']; ?></td>
                                <?php elseif ($member_role !== 'warper'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['length_m']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['width_m']; ?></td>
                                <?php endif; ?>
                                <?php if ($member_role === 'weaver'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo isset($task['quantity']) && $task['quantity'] > 0 ? $task['quantity'] : 1; ?></td>
                                <?php endif; ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo ucfirst($task['status']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['deadline'] ? date('Y-m-d', strtotime($task['deadline'])) : '-'; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <button onclick="acceptTask(<?php echo $task['task_id']; ?>)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md">Accept</button>
                                    <button onclick="declineTask(<?php echo $task['task_id']; ?>)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md ml-2">Decline</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($new_tasks)): ?>
                            <tr>
                                <td colspan="<?php echo $member_role === 'knotter' ? '7' : ($member_role === 'weaver' ? '7' : '8'); ?>" class="px-6 py-4 text-center text-gray-500">No new tasks available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- My Assigned Tasks Table -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">My Assigned Tasks</h3>
                <div class="overflow-x-auto">
                    <table id="assignedTasksTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <?php if ($member_role === 'knotter'): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (g)</th>
                                <?php elseif ($member_role !== 'warper'): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Length (m)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Width (in)</th>
                                <?php endif; ?>
                                <?php if ($member_role !== 'warper' && $member_role !== 'knotter'): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <?php endif; ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Started</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($assigned_tasks as $task): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($task['display_id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($task['product_name']); ?></td>
                                <?php if ($member_role === 'knotter'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['weight_g']; ?></td>
                                <?php elseif ($member_role !== 'warper'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['length_m']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['width_m']; ?></td>
                                <?php endif; ?>
                                <?php if ($member_role !== 'warper' && $member_role !== 'knotter'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['quantity']; ?></td>
                                <?php endif; ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo ucfirst($task['status']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['date_started'] ? date('Y-m-d', strtotime($task['date_started'])) : '-'; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $task['date_submitted'] ? date('Y-m-d', strtotime($task['date_submitted'])) : '-'; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($task['status'] === 'in_progress'): ?>
                                    <button class="submit-task-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md" 
                                            data-prod-id="<?php echo $task['prod_line_id']; ?>"
                                            data-prod-name="<?php echo htmlspecialchars($task['product_name']); ?>">
                                        Submit
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assigned_tasks)): ?>
                            <tr>
                                <td colspan="<?php echo $member_role === 'knotter' ? '7' : ($member_role === 'weaver' ? '8' : '9'); ?>" class="px-6 py-4 text-center text-gray-500">No assigned tasks</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Task Created Tab -->
        <div class="tab-pane hidden" id="created" role="tabpanel">
            <div class="flex justify-end mb-4">
                <button id="createTaskBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                    <span class="material-icons mr-2">add</span>
                    Create Task
                </button>
            </div>

            <?php if (in_array($member_role, ['knotter', 'warper'])): ?>
            <!-- Self-Assigned Tasks Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Self-Assigned Tasks</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (g)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raw Materials</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="selfAssignedTasksBody">
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No tasks available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- My Earnings Tab -->
        <div class="tab-pane hidden" id="earnings" role="tabpanel">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">My Earnings Overview</h3>
                    <div class="flex space-x-2">
                        <select id="earningsDateFilter" class="border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="all">All Time</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                </div>

                <!-- Earnings Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total Earnings -->
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <span class="material-icons text-green-600">payments</span>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm">Total Earnings</h2>
                                <p id="totalEarnings" class="text-2xl font-semibold text-gray-800">₱0.00</p>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Tasks -->
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <span class="material-icons text-blue-600">task_alt</span>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm">Completed Tasks</h2>
                                <p id="completedTasksCount" class="text-2xl font-semibold text-gray-800">0</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Payments -->
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <span class="material-icons text-yellow-600">pending</span>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm">Pending Payments</h2>
                                <p id="pendingPayments" class="text-2xl font-semibold text-gray-800">₱0.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Summary Table -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Balance Summary</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="balanceSummaryTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <?php if (in_array($member_role, ['knotter', 'warper'])): ?>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight(g)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (₱)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Paid</th>
                                    <?php else: ?>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Measurement</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (₱)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Paid</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="balanceSummaryTableBody">
                                <!-- Data will be loaded here -->
                                <tr>
                                    <td colspan="<?php echo in_array($member_role, ["knotter", "warper"]) ? "6" : "7"; ?>" class="px-6 py-4 text-center text-gray-500">Loading balance summary...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div id="createTaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Create New Task</h3>
            <form id="createTaskForm">
                <?php if ($member_role === 'knotter'): ?>
                <!-- Fields for Knotter -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="productName">
                        Product Name
                    </label>
                    <select id="productName" name="productName" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Select a product</option>
                        <option value="Knotted Liniwan">Knotted Liniwan</option>
                        <option value="Knotted Bastos">Knotted Bastos</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="weight">
                        Weight (g)
                    </label>
                    <input type="number" id="weight" name="weight" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <?php elseif ($member_role === 'warper'): ?>
                <!-- Fields for Warper -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="productName">
                        Product Name
                    </label>
                    <input type="text" id="productName" name="productName" value="Warped Silk" readonly class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="weight">
                        Weight (g)
                    </label>
                    <input type="number" id="weight" name="weight" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <?php else: ?>
                <!-- Fields for Weaver -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="productName">
                        Product Name
                    </label>
                    <select id="productName" name="productName" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Select a product</option>
                        <option value="Piña Seda">Piña Seda</option>
                        <option value="Pure Piña Cloth">Pure Piña Cloth</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="length">
                        Length (m)
                    </label>
                    <input type="number" id="length" name="length" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="width">
                        Width (in)
                    </label>
                    <input type="number" id="width" name="width" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="quantity">
                        Quantity
                    </label>
                    <input type="number" id="quantity" name="quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <?php endif; ?>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" id="cancelTaskBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="assets/js/self-tasks.js"></script>
<script>
$(document).ready(function() {
    // Handle tab switching
    $('#assigned-tab, #created-tab, #earnings-tab').click(function(e) {
        e.preventDefault();
        
        // Remove active classes from all tabs
        $('#assigned-tab, #created-tab, #earnings-tab').removeClass('border-blue-500 text-blue-600').addClass('text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent');
        
        // Hide all tab panes
        $('.tab-pane').addClass('hidden').removeClass('show active');
        
        // Add active class to clicked tab
        $(this).removeClass('text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent').addClass('border-blue-500 text-blue-600');
        
        // Show corresponding tab pane
        const tabId = $(this).attr('id').replace('-tab', '');
        $(`#${tabId}`).removeClass('hidden').addClass('show active');

        // Store the active tab ID in localStorage
        localStorage.setItem('activeProductionTab', tabId);

        // Load earnings data if switching to the earnings tab
        if (tabId === 'earnings') {
            loadEarningsData();
        }
    });

    // On page load, check for saved tab state
    $(document).ready(function() {
        const savedTab = localStorage.getItem('activeProductionTab');
        if (savedTab) {
            // Trigger click on the saved tab
            $(`#${savedTab}-tab`).click();
        }
    });

    // Create Task Modal Functionality
    const createTaskBtn = document.getElementById('createTaskBtn');
    const createTaskModal = document.getElementById('createTaskModal');
    const cancelTaskBtn = document.getElementById('cancelTaskBtn');
    const createTaskForm = document.getElementById('createTaskForm');
    const productNameSelect = document.getElementById('productName');
    const memberRole = '<?php echo strtolower($member_role); ?>';

    // Show modal
    createTaskBtn.addEventListener('click', () => {
        createTaskModal.classList.remove('hidden');
    });

    // Hide modal
    cancelTaskBtn.addEventListener('click', () => {
        createTaskModal.classList.add('hidden');
        if (memberRole !== 'warper') {
            createTaskForm.reset();
        }
    });

    // Close modal when clicking outside
    createTaskModal.addEventListener('click', (e) => {
        if (e.target === createTaskModal) {
            createTaskModal.classList.add('hidden');
            if (memberRole !== 'warper') {
                createTaskForm.reset();
            }
        }
    });

    // Function to load self-assigned tasks
    function loadSelfAssignedTasks() {
        const tableBody = $('#selfAssignedTasksBody');
        
        // Show loading state
        tableBody.html('<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading tasks...</td></tr>');

        fetch('backend/end-points/get_self_tasks.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.tasks) {
                    if (data.tasks.length === 0) {
                        tableBody.html('<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No tasks available</td></tr>');
                        return;
                    }

                    const rows = data.tasks.map(task => {
                        const isApproved = task.approval_status === 'approved';
                        const startButtonClass = isApproved ? 
                            'bg-[#4F46E5] hover:bg-[#4338CA]' : 
                            'bg-gray-400 cursor-not-allowed';
                        
                        return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.production_id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.product_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.weight_g}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs rounded-full font-medium ${
                                    task.status === 'completed' ? 'bg-green-100 text-green-800' :
                                    task.status === 'submitted' ? 'bg-purple-100 text-purple-800' :
                                    task.status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                    'bg-yellow-100 text-yellow-800'
                                }">
                                    ${task.status ? task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ') : '-'}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button onclick="viewMaterials('${task.product_name}', ${task.weight_g})" 
                                    class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-1 rounded text-sm">
                                    View Materials
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.date_created}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.date_submitted || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                ${task.status === 'pending' ? `
                                    <button onclick="${isApproved ? `startTask('${task.production_id}')` : 'void(0)'}" 
                                        class="${startButtonClass} text-white text-sm py-2 px-4 rounded"
                                        ${!isApproved ? 'disabled' : ''}>
                                        Start
                                    </button>
                                ` : task.status === 'in_progress' ? `
                                    <button onclick="submitSelfTask('${task.production_id}')" 
                                    class="bg-[#4F46E5] hover:bg-[#4338CA] text-white text-sm py-2 px-4 rounded">
                                    Submit
                                </button>
                            ` : ''}
                                ${task.status !== 'submitted' && task.status !== 'completed' ? `
                                    <button onclick="deleteTask('${task.production_id}')" class="bg-[#EF4444] hover:bg-[#DC2626] text-white text-sm py-2 px-4 rounded">Delete</button>
                                ` : ''}
                            </td>
                        </tr>
                    `}).join('');

                    tableBody.html(rows);
                } else {
                    tableBody.html('<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Error loading tasks</td></tr>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.html('<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Failed to load tasks</td></tr>');
            });
    }

    // Handle form submission for creating new task
    createTaskForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Get form data
        const formData = {
            product_name: document.getElementById('productName').value,
            weight: parseFloat(document.getElementById('weight').value)
        };

        // Validate form data
        if (!formData.product_name || !formData.weight) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please fill in all required fields'
            });
            return;
        }

        // Show loading state
        const createBtn = createTaskForm.querySelector('button[type="submit"]');
        const originalBtnText = createBtn.innerHTML;
        createBtn.disabled = true;
        createBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';

        // Send request to create task
        fetch('backend/end-points/create_self_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and reset form
                createTaskModal.classList.add('hidden');
                createTaskForm.reset();

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Task created successfully',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    loadSelfAssignedTasks();
                });
            } else {
                throw new Error(data.message || 'Failed to create task');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to create task. Please try again.'
            });
        })
        .finally(() => {
            // Reset button state
            createBtn.disabled = false;
            createBtn.innerHTML = originalBtnText;
        });
    });

    // Load self-assigned tasks when Task Created tab is shown
    $('#created-tab').on('click', function() {
        loadSelfAssignedTasks();
    });

    // Initial load if Task Created tab is active
    if ($('#created-tab').attr('aria-selected') === 'true') {
        loadSelfAssignedTasks();
    }

    // Handle submit button clicks using jQuery delegation
    $('#assignedTasksTable').on('click', '.submit-task-btn', function(e) {
        e.preventDefault();
        const $button = $(this);
        const prodId = $button.data('prod-id');
        const prodName = $button.data('prod-name');
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Submit Task',
            text: `Are you sure you want to submit the task for ${prodName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, submit it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable button and show loading state
                $button.prop('disabled', true)
                       .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...')
                       .addClass('opacity-50');
                
                // Send request to backend
                $.ajax({
                    url: './backend/end-points/submit_task.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        prod_line_id: prodId
                    }),
                    beforeSend: function() {
                        // Disable button and show loading state
                        $button.prop('disabled', true)
                               .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...')
                               .addClass('opacity-50');
                    },
                    success: function(response) {
                        try {
                            // Parse response if it's a string
                            if (typeof response === 'string') {
                                response = JSON.parse(response);
                            }
                            
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message || 'Product has been submitted to the admin.',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    // Refresh the page to update the status
                                    window.location.reload();
                                });
                            } else {
                                console.error('Server error:', response.message); // Debug log
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to submit task'
                                });
                                // Reset button state
                                $button.prop('disabled', false)
                                       .text('Submit')
                                       .removeClass('opacity-50');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid response from server. Please try again.'
                            });
                            // Reset button state
                            $button.prop('disabled', false)
                                   .text('Submit')
                                   .removeClass('opacity-50');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Log the full error details
                        console.error('AJAX error:', {
                            status: status,
                            error: error,
                            response: xhr.responseText,
                            xhr: xhr
                        });
                        
                        let errorMessage = 'An error occurred while submitting the task.';
                        
                        // Try to extract error message from response if possible
                        try {
                            if (xhr.responseText) {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing error response:', e);
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage + ' Please try again.'
                        });
                        
                        // Reset button state
                        $button.prop('disabled', false)
                               .text('Submit')
                               .removeClass('opacity-50');
                    }
                });
            }
        });
    });

    // Availability buttons functionality
    const availableBtn = document.getElementById('availableBtn');
    const unavailableBtn = document.getElementById('unavailableBtn');

    function updateAvailabilityStatus(status) {
        // Show loading state
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
                // Update button styles
                if (availableBtn) {
                    availableBtn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    availableBtn.classList.add('bg-green-500', 'text-white');
                }
                if (unavailableBtn) {
                    unavailableBtn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    unavailableBtn.classList.add('bg-red-500', 'text-white');
                }
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Status Updated',
                    text: `Your status has been set to ${status}`,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: data.message || 'Failed to update status'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating status'
            });
        })
        .finally(() => {
            // Re-enable buttons
            if (availableBtn) availableBtn.disabled = false;
            if (unavailableBtn) unavailableBtn.disabled = false;
        });
    }

    if (availableBtn && unavailableBtn) {
        availableBtn.addEventListener('click', () => updateAvailabilityStatus('available'));
        unavailableBtn.addEventListener('click', () => updateAvailabilityStatus('unavailable'));
    }
});

// Task handling functions
function acceptTask(taskId) {
    Swal.fire({
        title: 'Accept Task',
        text: 'Are you sure you want to accept this task?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, accept it!'
    }).then((result) => {
        if (result.isConfirmed) {
    fetch('backend/end-points/update_task_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            task_id: taskId,
                    action: 'accept'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Task Accepted',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
            window.location.reload();
                    });
        } else {
                    throw new Error(data.message || 'Failed to accept task');
        }
    })
    .catch(error => {
        console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to accept task. Please try again.'
                });
            });
        }
    });
}

function declineTask(taskId) {
    Swal.fire({
        title: 'Decline Task',
        text: 'Are you sure you want to decline this task?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, decline it!'
    }).then((result) => {
        if (result.isConfirmed) {
    fetch('backend/end-points/update_task_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            task_id: taskId,
                    action: 'decline'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Task Declined',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
            window.location.reload();
                    });
        } else {
                    throw new Error(data.message || 'Failed to decline task');
        }
    })
    .catch(error => {
        console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to decline task. Please try again.'
                });
            });
        }
    });
}

function submitTask(prodLineId, $button) {
    // Show confirmation dialog first
    Swal.fire({
        title: 'Submit Task',
        text: 'Are you sure you want to submit this task?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, submit it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Disable the button and show loading state
            $button.prop('disabled', true)
                   .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...')
                   .addClass('opacity-50');

    // Remove 'PL' prefix and leading zeros
    const numericId = prodLineId.replace('PL', '').replace(/^0+/, '');
    
            $.ajax({
                url: 'backend/end-points/submit_task.php',
        method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
            prod_line_id: numericId
                }),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Your task has been submitted to the admin for confirmation',
                            showConfirmButton: true
                        }).then(() => {
            // Refresh the table to update the status
            window.location.reload();
                        });
        } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to submit task'
                        });
                        // Reset button state
                        $button.prop('disabled', false)
                               .text('Submit')
                               .removeClass('opacity-50');
        }
                },
                error: function(xhr, status, error) {
        console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while submitting the task. Please try again.'
                    });
                    // Reset button state
                    $button.prop('disabled', false)
                           .text('Submit')
                           .removeClass('opacity-50');
                }
            });
            }
        });
    }

    // Function to load earnings data
    function loadEarningsData() {
        const dateFilter = document.getElementById('earningsDateFilter').value;
        
        fetch('backend/end-points/get_member_earnings.php?filter=' + dateFilter)
            .then(response => response.json())
            .then(data => {
                // Update statistics
                document.getElementById('totalEarnings').textContent = '₱' + data.total_earnings.toFixed(2);
                document.getElementById('completedTasksCount').textContent = data.completed_tasks;
                document.getElementById('pendingPayments').textContent = '₱' + data.pending_payments.toFixed(2);

                // Update table
                const tableBody = document.getElementById('earningsTableBody');
                if (!data.earnings || data.earnings.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No earnings history found</td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = data.earnings.map(earning => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">${earning.task_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${earning.product_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${earning.completion_date}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">₱${earning.amount.toFixed(2)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs rounded-full ${
                                earning.payment_status === 'paid' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-yellow-100 text-yellow-800'
                            }">
                                ${earning.payment_status.charAt(0).toUpperCase() + earning.payment_status.slice(1)}
                            </span>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(error => {
                console.error('Error loading earnings data:', error);
                document.getElementById('earningsTableBody').innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-red-500">Error loading earnings data. Please try again.</td>
                    </tr>
                `;
            });
    }

function loadBalanceSummary() {
    const dateFilter = document.getElementById('earningsDateFilter').value;
    const tableBody = document.getElementById('balanceSummaryTableBody');
    
    // Show loading state
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                Loading balance summary...
            </td>
        </tr>
    `;

    // Debug log
    console.log('Loading balance summary with filter:', dateFilter);

    fetch('backend/end-points/get_balance_summary.php?filter=' + dateFilter)
        .then(async response => {
            if (!response.ok) {
                const text = await response.text();
                console.error('Response not OK:', text);
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            console.log('Member role:', data.member_role);

            console.log('Full response:', data); // Debug log
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load balance summary');
            }

            if (!data.data || data.data.length === 0) {
                const colSpan = data.member_role === 'weaver' ? '8' : '6';
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="${colSpan}" class="px-6 py-4 text-center text-gray-500">
                            No balance records found
                        </td>
                    </tr>
                `;
                return;
            }

            // Debug log the first record
            if (data.data.length > 0) {
                console.log('First record:', data.data[0]);
            }

            // Update the table with the fetched data
                        // Debug log the data
            console.log('Data to render:', data);
            
            tableBody.innerHTML = data.data.map(item => {
                console.log('Processing item:', item);
                
                if (item.member_role === 'weaver') {
                    return `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">${item.product_name || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">${item.measurements || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">${item.quantity || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">₱${parseFloat(item.unit_rate || 0).toFixed(2)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">₱${parseFloat(item.total_amount || 0).toFixed(2)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full ${
                                    item.payment_status === 'Paid' ? 'bg-green-100 text-green-800' :
                                    item.payment_status === 'Adjusted' ? 'bg-blue-100 text-blue-800' :
                                    'bg-yellow-100 text-yellow-800'
                                }">
                                    ${item.payment_status}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">${item.date_paid || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                ${item.payment_status === 'Pending' ? `
                                    <div class="flex space-x-2">
                                        <button onclick="processPayment(${item.id})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm transition-colors">Pay</button>
                                        <button onclick="adjustPayment(${item.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-sm transition-colors">Adjust</button>
                                    </div>
                                ` : '-'}
                            </td>
                        </tr>
                    `;
                } else {
                    return `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">${item.product_name || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">${item.weight_g || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">₱${parseFloat(item.unit_rate).toFixed(2)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">₱${parseFloat(item.total_amount).toFixed(2)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full ${
                                    item.payment_status === 'Paid' ? 'bg-green-100 text-green-800' : 
                                    item.payment_status === 'Adjusted' ? 'bg-blue-100 text-blue-800' :
                                    'bg-yellow-100 text-yellow-800'
                                }">
                                    ${item.payment_status}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">${item.date_paid || '-'}</td>
                        </tr>
                    `;
                }
            }).join('');

            // Update the earnings summary
            if (data.summary) {
                document.getElementById('totalEarnings').textContent = '₱' + parseFloat(data.summary.total_earnings || 0).toFixed(2);
                document.getElementById('completedTasksCount').textContent = data.summary.total_tasks || 0;
                document.getElementById('pendingPayments').textContent = '₱' + parseFloat(data.summary.pending_payments || 0).toFixed(2);
            }
        })
        .catch(error => {
            console.error('Error loading balance summary:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="${'<?php echo in_array($member_role, ["knotter", "warper"]) ? "7" : "8"; ?>'}" class="px-6 py-4 text-center text-red-500">
                        Error loading balance summary. Please try again.
                    </td>
                </tr>
            `;
        });
}

// Add event listener for date filter
document.getElementById('earningsDateFilter').addEventListener('change', loadBalanceSummary);

// Function to check if we're on the earnings tab
function isEarningsTabActive() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || '';
    return activeTab === 'earnings' || document.getElementById('earnings').classList.contains('show');
}

// Load balance summary when the tab is shown
document.getElementById('earnings-tab').addEventListener('click', () => {
    // Update URL without refreshing
    const newUrl = new URL(window.location);
    newUrl.searchParams.set('tab', 'earnings');
    window.history.pushState({}, '', newUrl);
    loadBalanceSummary();
});

// Add event listeners for other tabs to update URL
document.getElementById('assigned-tab').addEventListener('click', () => {
    const newUrl = new URL(window.location);
    newUrl.searchParams.set('tab', 'assigned');
    window.history.pushState({}, '', newUrl);
});

document.getElementById('created-tab').addEventListener('click', () => {
    const newUrl = new URL(window.location);
    newUrl.searchParams.set('tab', 'created');
    window.history.pushState({}, '', newUrl);
});

// Handle browser back/forward buttons
window.addEventListener('popstate', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'assigned';
    
    // Hide all tabs
    document.querySelectorAll('.tab-pane').forEach(tab => {
        tab.classList.add('hidden');
        tab.classList.remove('show', 'active');
    });
    
    // Show active tab
    const activeTabElement = document.getElementById(activeTab);
    if (activeTabElement) {
        activeTabElement.classList.remove('hidden');
        activeTabElement.classList.add('show', 'active');
    }
    
    // If earnings tab is active, load the data
    if (activeTab === 'earnings') {
        loadBalanceSummary();
    }
});

// Initial load based on URL or default tab
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'assigned';
    
    // Hide all tabs
    document.querySelectorAll('.tab-pane').forEach(tab => {
        tab.classList.add('hidden');
        tab.classList.remove('show', 'active');
    });
    
    // Show active tab
    const activeTabElement = document.getElementById(activeTab);
    if (activeTabElement) {
        activeTabElement.classList.remove('hidden');
        activeTabElement.classList.add('show', 'active');
        
        // Update tab buttons
        document.querySelectorAll('[role="tab"]').forEach(tab => {
            tab.classList.remove('border-blue-500', 'text-blue-600');
            tab.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
        });
        
        document.getElementById(`${activeTab}-tab`).classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
        document.getElementById(`${activeTab}-tab`).classList.add('border-blue-500', 'text-blue-600');
    }
    
    // If earnings tab is active, load the data
    if (activeTab === 'earnings') {
        loadBalanceSummary();
    }
});



// Task action functions
function startTask(productionId) {
    Swal.fire({
        title: 'Start Task',
        text: 'Are you sure you want to start this task?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, start it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateTaskStatus(productionId, 'start');
        }
    });
}

function submitTask(productionId) {
    Swal.fire({
        title: 'Submit Task',
        text: 'Are you sure you want to submit this task?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, submit it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateTaskStatus(productionId, 'submit');
        }
    });
}

function deleteTask(productionId) {
    Swal.fire({
        title: 'Delete Task',
        text: 'Are you sure you want to delete this task? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateTaskStatus(productionId, 'delete');
        }
    });
}

function updateTaskStatus(productionId, action) {
    fetch('backend/end-points/update_self_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            production_id: productionId,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                loadSelfAssignedTasks();
            });
        } else {
            throw new Error(data.message || `Failed to ${action} task`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || `Failed to ${action} task. Please try again.`
        });
    });
}

// Add the viewMaterials function
function viewMaterials(productName, weight) {
    fetch(`backend/end-points/get_raw_materials_info.php?product_name=${encodeURIComponent(productName)}&weight=${weight}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const materialsList = data.data.materials.map(material => 
                    `<div class="flex justify-between items-center text-gray-700">
                        <span>${material.name}</span>
                        <span>${material.amount} g</span>
                    </div>`
                ).join('');

                Swal.fire({
                    title: 'Raw Materials Information',
                    html: `
                        <div class="text-left">
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Product Information</h3>
                                <div class="text-gray-700">
                                    <div class="mb-2">
                                        <span class="font-medium">Product:</span> ${data.data.product}
                                    </div>
                                    <div>
                                        <span class="font-medium">Weight:</span> ${data.data.weight} g
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Raw Materials Required</h3>
                                <div class="space-y-3">
                                    ${materialsList}
                                </div>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#6366F1',
                    customClass: {
                        container: 'raw-materials-modal',
                        popup: 'rounded-lg',
                        title: 'text-xl font-semibold text-gray-900 pb-4',
                        htmlContainer: 'px-6 pb-6',
                        confirmButton: 'bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500'
                    }
                });
            } else {
                throw new Error(data.message || 'Failed to get materials information');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to get materials information. Please try again.'
            });
        });
}
</script>

<?php include "components/footer.php"; ?>