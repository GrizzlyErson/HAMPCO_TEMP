/**
 * Task Status Dashboard Widget
 * Displays real-time task status, urgent tasks, and notifications
 */

class TaskStatusWidget {
    constructor(options = {}) {
        this.refreshInterval = options.refreshInterval || 30000; // 30 seconds
        this.containerSelector = options.containerSelector || '#taskStatusWidget';
        this.container = null;
        this.init();
    }

    init() {
        this.container = document.querySelector(this.containerSelector);
        if (!this.container) {
            console.warn('Task Status Widget container not found:', this.containerSelector);
            return;
        }

        // Initial load
        this.loadTaskStatus();

        // Set up periodic refresh
        setInterval(() => this.loadTaskStatus(), this.refreshInterval);

        // Set up event listener for real-time updates
        document.addEventListener('taskStatusUpdated', () => this.loadTaskStatus());
    }

    async loadTaskStatus() {
        try {
            const response = await fetch('backend/end-points/task_status_notification.php?action=get-dashboard-widget');
            const data = await response.json();

            if (data.success) {
                this.renderWidget(data);
            } else {
                console.error('Error loading task status:', data.error);
            }
        } catch (error) {
            console.error('Error fetching task status:', error);
        }
    }

    renderWidget(data) {
        const { stats, recent_updates } = data;

        let html = `
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Current Task Status</h3>
                    <span class="text-xs text-gray-500">Last updated: ${new Date().toLocaleTimeString()}</span>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                    <!-- Total Tasks -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-600">${stats.total_tasks || 0}</div>
                        <div class="text-xs text-gray-600 uppercase">Total Tasks</div>
                    </div>

                    <!-- Pending -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-yellow-600">${stats.pending || 0}</div>
                        <div class="text-xs text-gray-600 uppercase">Pending</div>
                    </div>

                    <!-- In Progress -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-600">${stats.in_progress || 0}</div>
                        <div class="text-xs text-gray-600 uppercase">In Progress</div>
                    </div>

                    <!-- Submitted -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-purple-600">${stats.submitted || 0}</div>
                        <div class="text-xs text-gray-600 uppercase">Submitted</div>
                    </div>

                    <!-- Due Soon -->
                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-orange-600">${stats.due_soon || 0}</div>
                        <div class="text-xs text-gray-600 uppercase">Due Soon</div>
                    </div>

                    <!-- Overdue -->
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-red-600">${stats.overdue || 0}</div>
                        <div class="text-xs text-gray-600 uppercase">Overdue</div>
                    </div>
                </div>

                <!-- Recent Updates Table -->
                <div class="border-t pt-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Recent Task Updates</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Production ID</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Product</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Member</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Role</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${recent_updates.length > 0 ? recent_updates.map(task => `
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 font-mono text-xs">${task.production_id}</td>
                                        <td class="px-3 py-2">${task.product_name}</td>
                                        <td class="px-3 py-2">${task.member_name}</td>
                                        <td class="px-3 py-2 capitalize">${task.role}</td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-1 text-xs rounded-full ${this.getStatusBadgeClass(task.status)}">
                                                ${task.status_label}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-xs text-gray-600">${this.formatDate(task.updated_at)}</td>
                                    </tr>
                                `).join('') : `
                                    <tr>
                                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">No recent updates</td>
                                    </tr>
                                `}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        this.container.innerHTML = html;
    }

    getStatusBadgeClass(status) {
        const classes = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'in_progress': 'bg-blue-100 text-blue-800',
            'submitted': 'bg-purple-100 text-purple-800',
            'completed': 'bg-green-100 text-green-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        
        return date.toLocaleDateString();
    }

    static async getUrgentTasks() {
        try {
            const response = await fetch('backend/end-points/task_status_notification.php?action=get-urgent-tasks');
            const data = await response.json();
            return data.success ? data.tasks : [];
        } catch (error) {
            console.error('Error fetching urgent tasks:', error);
            return [];
        }
    }

    static async getTaskSummary() {
        try {
            const response = await fetch('backend/end-points/task_status_notification.php?action=get-summary');
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (error) {
            console.error('Error fetching task summary:', error);
            return null;
        }
    }

    static async getTaskById(taskId) {
        try {
            const response = await fetch(`backend/end-points/task_status_notification.php?action=get-task-by-id&task_id=${taskId}`);
            const data = await response.json();
            return data.success ? data.task : null;
        } catch (error) {
            console.error('Error fetching task details:', error);
            return null;
        }
    }

    static async logStatusChange(taskId, oldStatus, newStatus, notes = '') {
        try {
            const formData = new FormData();
            formData.append('action', 'log-status-change');
            formData.append('task_id', taskId);
            formData.append('old_status', oldStatus);
            formData.append('new_status', newStatus);
            formData.append('notes', notes);

            const response = await fetch('backend/end-points/task_status_notification.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            // Trigger update event
            if (data.success) {
                document.dispatchEvent(new Event('taskStatusUpdated'));
            }

            return data.success;
        } catch (error) {
            console.error('Error logging status change:', error);
            return false;
        }
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const widget = new TaskStatusWidget({
        containerSelector: '#taskStatusWidget',
        refreshInterval: 30000 // 30 seconds
    });

    // Expose globally for use in other scripts
    window.taskStatusWidget = widget;
});

// Function for updating notification count
async function updateUrgentTaskNotifications() {
    try {
        const urgent = await TaskStatusWidget.getUrgentTasks();
        const urgentCount = urgent.length;

        // Update notification badge if it exists
        const badge = document.querySelector('[data-urgent-tasks-badge]');
        if (badge) {
            badge.textContent = urgentCount;
            badge.style.display = urgentCount > 0 ? 'inline-block' : 'none';
        }

        // Show alert if there are urgent tasks
        if (urgentCount > 0) {
            console.warn(`⚠️ ${urgentCount} urgent task(s) require attention`);
        }
    } catch (error) {
        console.error('Error updating urgent task notifications:', error);
    }
}

// Update urgent task notifications initially and periodically
updateUrgentTaskNotifications();
setInterval(updateUrgentTaskNotifications, 60000); // Every 60 seconds