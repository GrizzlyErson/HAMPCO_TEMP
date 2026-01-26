<?php 
include "components/header.php";
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Customer & Sales Analytics</h1>
        <p class="text-gray-600 mt-1">Monitor sales performance, customer metrics, and revenue trends</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Revenue Card -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-2" id="totalRevenue">₱0.00</h3>
                    <p class="text-green-600 text-xs mt-2" id="revenueChange">+0% vs last month</p>
                </div>
                <div class="bg-blue-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Orders Card -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Orders</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-2" id="totalOrders">0</h3>
                    <p class="text-green-600 text-xs mt-2" id="ordersChange">+0% vs last month</p>
                </div>
                <div class="bg-green-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Customers Card -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Customers</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-2" id="totalCustomers">0</h3>
                    <p class="text-green-600 text-xs mt-2" id="customersChange">+0% vs last month</p>
                </div>
                <div class="bg-purple-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M13 16H9m4-4H9m6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Avg Order Value Card -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Avg Order Value</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-2" id="avgOrderValue">₱0.00</h3>
                    <p class="text-green-600 text-xs mt-2" id="avgChange">+0% vs last month</p>
                </div>
                <div class="bg-orange-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales Trend Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800">Sales Trend (30 Days)</h2>
                <select id="salesTrendPeriod" class="text-sm border border-gray-300 rounded px-3 py-1">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
            <canvas id="salesTrendChart" height="100"></canvas>
        </div>

        <!-- Order Status Distribution -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Order Status Distribution</h2>
            <div class="flex gap-6">
                <div class="flex-1">
                    <canvas id="orderStatusChart"></canvas>
                </div>
                <div class="flex-1">
                    <div id="orderStatusLegend" class="space-y-3">
                        <!-- Status legend will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CORE ANALYTICS SECTION -->
    <!-- Row 1: Stock & Inventory Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Critical Stock Alerts -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">🚨 Critical Stock Alerts</h2>
            <div id="criticalStockAlerts" class="space-y-3">
                <!-- Will be populated dynamically -->
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <p class="text-sm text-gray-600">Raw Fiber (Balete)</p>
                    <p class="font-bold text-red-700">450g / 2000g</p>
                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                        <div class="bg-red-600 h-2 rounded-full" style="width: 22.5%"></div>
                    </div>
                    <p class="text-xs text-red-600 mt-1">⚠️ Below critical threshold - Reorder immediately</p>
                </div>
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                    <p class="text-sm text-gray-600">Finished Cloth (Piña Seda)</p>
                    <p class="font-bold text-yellow-700">8.5m / 20m</p>
                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                        <div class="bg-yellow-500 h-2 rounded-full" style="width: 42.5%"></div>
                    </div>
                    <p class="text-xs text-yellow-700 mt-1">⚠️ Running low - Schedule restock</p>
                </div>
                <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded">
                    <p class="text-sm text-gray-600">Raw Fiber (Libacao)</p>
                    <p class="font-bold text-green-700">1850g / 2000g</p>
                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: 92.5%"></div>
                    </div>
                    <p class="text-xs text-green-700 mt-1">✓ Healthy stock level</p>
                </div>
            </div>
        </div>

        <!-- Inventory Composition -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">📊 Inventory Composition</h2>
            <div class="flex gap-6">
                <div class="flex-1">
                    <canvas id="inventoryCompositionChart"></canvas>
                </div>
                <div class="flex-1 space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Raw Fiber (Grams)</p>
                        <p class="text-2xl font-bold text-blue-600">5,200g</p>
                        <p class="text-xs text-gray-500">62% of total inventory</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Finished Cloth (Meters)</p>
                        <p class="text-2xl font-bold text-purple-600">18.5m</p>
                        <p class="text-xs text-gray-500">38% of total inventory</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Wastage & Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Material Wastage Analytics -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">♻️ Wastage Analytics</h2>
            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <p class="font-semibold text-gray-800">Knotters (Thread Production)</p>
                        <p class="text-sm text-gray-600">Last 30 days</p>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs text-gray-600">Fiber Issued</p>
                            <p class="text-lg font-bold text-blue-600">2,500g</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Thread Returned</p>
                            <p class="text-lg font-bold text-green-600">2,350g</p>
                        </div>
                    </div>
                    <div class="mt-3 p-2 bg-red-50 rounded">
                        <p class="text-xs text-red-700"><strong>Wastage: 150g (6%)</strong> - Within acceptable range</p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <p class="font-semibold text-gray-800">Weavers (Cloth Production)</p>
                        <p class="text-sm text-gray-600">Last 30 days</p>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs text-gray-600">Thread Issued</p>
                            <p class="text-lg font-bold text-blue-600">2,350g</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Cloth Produced</p>
                            <p class="text-lg font-bold text-green-600">14.2m</p>
                        </div>
                    </div>
                    <div class="mt-3 p-2 bg-green-50 rounded">
                        <p class="text-xs text-green-700"><strong>Efficiency: 94%</strong> - Excellent conversion ratio</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Member Productivity Leaderboard -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">🏆 Top Performing Members</h2>
            <div id="topPerformersList" class="space-y-3">
                <!-- Placeholder data -->
                <div class="flex items-center justify-between bg-blue-50 p-3 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-800">Maria Santos</p>
                        <p class="text-xs text-gray-600">Knotter • Active</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-blue-600">98%</p>
                        <p class="text-xs text-gray-600">45/45 tasks</p>
                    </div>
                </div>
                <div class="flex items-center justify-between bg-green-50 p-3 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-800">Juan dela Cruz</p>
                        <p class="text-xs text-gray-600">Weaver • Active</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-green-600">96%</p>
                        <p class="text-xs text-gray-600">43/45 tasks</p>
                    </div>
                </div>
                <div class="flex items-center justify-between bg-purple-50 p-3 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-800">Rosa Reyes</p>
                        <p class="text-xs text-gray-600">Knotter • Active</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-purple-600">92%</p>
                        <p class="text-xs text-gray-600">41/45 tasks</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Task Progress & Order Fulfillment -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Active Task Progress -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">⏳ Active Production Tasks</h2>
            <div id="taskProgressList" class="space-y-4">
                <!-- Placeholder tasks -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <p class="font-semibold text-gray-800">Order #2024-001: 3kg Piña Seda Cloth</p>
                        <p class="text-xs font-bold text-blue-600">65% Complete</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: 65%"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Deadline: Mar 15, 2026 (45 days remaining)</p>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <p class="font-semibold text-gray-800">Order #2024-002: 2kg Warped Silk</p>
                        <p class="text-xs font-bold text-yellow-600">35% Complete</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-yellow-500 h-3 rounded-full" style="width: 35%"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Deadline: Mar 20, 2026 (50 days remaining)</p>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <p class="font-semibold text-gray-800">Order #2024-003: 1.5kg Knotted Liniwan</p>
                        <p class="text-xs font-bold text-green-600">90% Complete</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-green-600 h-3 rounded-full" style="width: 90%"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Deadline: Feb 28, 2026 (25 days remaining)</p>
                </div>
            </div>
        </div>

        <!-- Order Fulfillment Timeline -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">📦 Order Fulfillment Timeline</h2>
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                        <div class="h-16 w-1 bg-green-600 mt-2"></div>
                    </div>
                    <div class="pt-2">
                        <p class="font-semibold text-gray-800">Order Recorded</p>
                        <p class="text-xs text-gray-600">Jan 26, 2026</p>
                        <p class="text-sm text-gray-700 mt-1">Customer places order</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                        <div class="h-16 w-1 bg-green-600 mt-2"></div>
                    </div>
                    <div class="pt-2">
                        <p class="font-semibold text-gray-800">Production Started</p>
                        <p class="text-xs text-gray-600">Jan 30, 2026 (4 days)</p>
                        <p class="text-sm text-gray-700 mt-1">Raw materials assigned</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 bg-yellow-500 text-white rounded-full flex items-center justify-center font-bold">3</div>
                        <div class="h-16 w-1 bg-yellow-500 mt-2"></div>
                    </div>
                    <div class="pt-2">
                        <p class="font-semibold text-gray-800">In Production</p>
                        <p class="text-xs text-gray-600">Today (65% complete)</p>
                        <p class="text-sm text-gray-700 mt-1">Currently being processed</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">4</div>
                    </div>
                    <div class="pt-2">
                        <p class="font-semibold text-gray-800">Shipment Scheduled</p>
                        <p class="text-xs text-gray-600">Est. Feb 15, 2026</p>
                        <p class="text-sm text-gray-700 mt-1">Awaiting completion</p>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm font-semibold text-blue-900">Average Fulfillment Time</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">45 days</p>
                    <p class="text-xs text-blue-700 mt-1">From order to shipment (based on recent orders)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Top 5 Products</h2>
            <canvas id="topProductsChart"></canvas>
        </div>

        <!-- Top Customers -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Top 5 Customers</h2>
            <div id="topCustomersList" class="space-y-3">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Customer Satisfaction -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Performance Metrics</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Conversion Rate</span>
                        <span class="text-sm font-bold text-gray-800" id="conversionRate">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="conversionRateBar" class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Return Rate</span>
                        <span class="text-sm font-bold text-gray-800" id="returnRate">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="returnRateBar" class="bg-red-600 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Customer Retention</span>
                        <span class="text-sm font-bold text-gray-800" id="retentionRate">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="retentionRateBar" class="bg-green-600 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Work Overview -->
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Member Work Overview</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Member Productivity by Role -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Productivity by Role</h3>
                <div id="memberProductivityList" class="space-y-3">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>

            <!-- Active Member Tasks -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Top Performers</h3>
                <div id="topPerformersList" class="space-y-3">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-800">Recent Orders</h2>
            <a href="orders.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Products</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Date</th>
                    </tr>
                </thead>
                <tbody id="recentOrdersBody" class="divide-y divide-gray-200">
                    <!-- Will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Customers Table -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-800">Top Customers</h2>
            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Total Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Total Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Last Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody id="topCustomersTableBody" class="divide-y divide-gray-200">
                    <!-- Will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Best Sellers Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-800">Best Selling Products</h2>
            <a href="products.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Units Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">Avg Rating</th>
                    </tr>
                </thead>
                <tbody id="bestSellersBody" class="divide-y divide-gray-200">
                    <!-- Will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "components/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let charts = {};

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    initializeInventoryChart();
    
    document.getElementById('salesTrendPeriod').addEventListener('change', function() {
        updateSalesTrendChart(this.value);
    });
});

async function loadDashboardData() {
    try {
        const response = await fetch('backend/end-points/get_ecommerce_analytics.php');
        const data = await response.json();
        
        if (data.success) {
            updateKPIs(data.kpis);
            renderSalesTrendChart(data.salesTrend);
            renderOrderStatusChart(data.orderStatus);
            renderTopProductsChart(data.topProducts);
            renderTopCustomersList(data.topCustomers);
            updatePerformanceMetrics(data.metrics);
            renderRecentOrders(data.recentOrders);
            renderTopCustomersTable(data.topCustomersTable);
            renderBestSellers(data.bestSellers);
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }

    // Load member work statistics
    try {
        const memberResponse = await fetch('backend/end-points/get_member_work_stats.php');
        const memberData = await memberResponse.json();
        
        if (memberData.success) {
            renderMemberProductivity(memberData.productivity);
            renderTopPerformers(memberData.stats);
        }
    } catch (error) {
        console.error('Error loading member work data:', error);
    }
}

function updateKPIs(kpis) {
    document.getElementById('totalRevenue').textContent = formatCurrency(kpis.totalRevenue);
    document.getElementById('revenueChange').textContent = `${kpis.revenueChange >= 0 ? '+' : ''}${kpis.revenueChange.toFixed(1)}% vs last month`;
    document.getElementById('totalOrders').textContent = kpis.totalOrders;
    document.getElementById('ordersChange').textContent = `${kpis.ordersChange >= 0 ? '+' : ''}${kpis.ordersChange.toFixed(1)}% vs last month`;
    document.getElementById('totalCustomers').textContent = kpis.totalCustomers;
    document.getElementById('customersChange').textContent = `${kpis.customersChange >= 0 ? '+' : ''}${kpis.customersChange.toFixed(1)}% vs last month`;
    document.getElementById('avgOrderValue').textContent = formatCurrency(kpis.avgOrderValue);
    document.getElementById('avgChange').textContent = `${kpis.avgChange >= 0 ? '+' : ''}${kpis.avgChange.toFixed(1)}% vs last month`;
}

function updatePerformanceMetrics(metrics) {
    const conversionRate = (metrics.conversionRate || 0);
    const returnRate = (metrics.returnRate || 0);
    const retentionRate = (metrics.retentionRate || 0);
    
    document.getElementById('conversionRate').textContent = conversionRate.toFixed(1) + '%';
    document.getElementById('conversionRateBar').style.width = conversionRate + '%';
    
    document.getElementById('returnRate').textContent = returnRate.toFixed(1) + '%';
    document.getElementById('returnRateBar').style.width = returnRate + '%';
    
    document.getElementById('retentionRate').textContent = retentionRate.toFixed(1) + '%';
    document.getElementById('retentionRateBar').style.width = retentionRate + '%';
}

function renderSalesTrendChart(data) {
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    
    if (charts.salesTrend) charts.salesTrend.destroy();
    
    charts.salesTrend = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Sales',
                    data: data.sales,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function renderOrderStatusChart(data) {
    const ctx = document.getElementById('orderStatusChart').getContext('2d');
    
    if (charts.orderStatus) charts.orderStatus.destroy();
    
    const colors = {
        'Pending': '#f59e0b',
        'Processing': '#3b82f6',
        'Shipped': '#8b5cf6',
        'Delivered': '#10b981',
        'Cancelled': '#ef4444'
    };
    
    charts.orderStatus = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.counts,
                backgroundColor: data.labels.map(label => colors[label] || '#6b7280'),
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
    
    // Update legend
    const legend = document.getElementById('orderStatusLegend');
    legend.innerHTML = data.labels.map((label, i) => `
        <div class="flex items-center">
            <div class="w-3 h-3 rounded-full" style="background-color: ${colors[label] || '#6b7280'}"></div>
            <span class="ml-2 text-sm text-gray-700">${label}: ${data.counts[i]}</span>
        </div>
    `).join('');
}

function renderTopProductsChart(data) {
    const ctx = document.getElementById('topProductsChart').getContext('2d');
    
    if (charts.topProducts) charts.topProducts.destroy();
    
    charts.topProducts = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Units Sold',
                data: data.sales,
                backgroundColor: '#6366f1',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
}

function initializeInventoryChart() {
    const ctx = document.getElementById('inventoryCompositionChart');
    if (!ctx) return;
    
    if (window.charts && window.charts.inventoryComposition) {
        window.charts.inventoryComposition.destroy();
    }
    
    if (!window.charts) window.charts = {};
    
    window.charts.inventoryComposition = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Raw Fiber (Grams)', 'Finished Cloth (Meters)'],
            datasets: [{
                data: [62, 38],
                backgroundColor: ['#3b82f6', '#a855f7'],
                borderColor: ['#1e40af', '#6d28d9'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 12 }
                    }
                }
            }
        }
    });
}

function renderTopCustomersList(data) {
    const list = document.getElementById('topCustomersList');
    list.innerHTML = data.map(customer => `
        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
            <div>
                <p class="font-medium text-gray-800">${customer.name}</p>
                <p class="text-xs text-gray-600">${customer.orders} orders</p>
            </div>
            <p class="font-bold text-blue-600">${formatCurrency(customer.spent)}</p>
        </div>
    `).join('');
}

function renderRecentOrders(orders) {
    const tbody = document.getElementById('recentOrdersBody');
    tbody.innerHTML = orders.map(order => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-medium text-gray-800">#${order.id}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${order.customer}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${order.items} items</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-800">${formatCurrency(order.total)}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-semibold ${getStatusColor(order.status)}">
                    ${order.status}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-700">${formatDate(order.date)}</td>
        </tr>
    `).join('');
}

function renderTopCustomersTable(customers) {
    const tbody = document.getElementById('topCustomersTableBody');
    tbody.innerHTML = customers.map(customer => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-medium text-gray-800">${customer.name}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${customer.email}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${customer.orders}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-800">${formatCurrency(customer.spent)}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${formatDate(customer.lastOrder)}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-semibold ${customer.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${customer.active ? 'Active' : 'Inactive'}
                </span>
            </td>
        </tr>
    `).join('');
}

function renderBestSellers(products) {
    const tbody = document.getElementById('bestSellersBody');
    tbody.innerHTML = products.map(product => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-medium text-gray-800">${product.name}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${product.category}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-800">${product.sold}</td>
            <td class="px-6 py-4 text-sm font-medium text-green-600">${formatCurrency(product.revenue)}</td>
            <td class="px-6 py-4 text-sm">
                <span class="text-yellow-500">★</span> ${product.rating.toFixed(1)}
            </td>
        </tr>
    `).join('');
}

async function updateSalesTrendChart(period) {
    try {
        const response = await fetch(`backend/end-points/get_ecommerce_analytics.php?period=${period}`);
        const data = await response.json();
        if (data.success) {
            renderSalesTrendChart(data.salesTrend);
        }
    } catch (error) {
        console.error('Error updating chart:', error);
    }
}

function formatCurrency(value) {
    return '₱' + parseFloat(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getStatusColor(status) {
    const colors = {
        'Pending': 'bg-yellow-100 text-yellow-800',
        'Processing': 'bg-blue-100 text-blue-800',
        'Shipped': 'bg-purple-100 text-purple-800',
        'Delivered': 'bg-green-100 text-green-800',
        'Accepted': 'bg-green-100 text-green-800',
        'Cancelled': 'bg-red-100 text-red-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function renderMemberProductivity(productivity) {
    const container = document.getElementById('memberProductivityList');
    if (!container) return;
    
    container.innerHTML = productivity.map(role => `
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between items-center mb-2">
                <p class="font-semibold text-gray-800">${role.role}</p>
                <span class="text-sm text-gray-600">${role.memberCount} members</span>
            </div>
            <div class="text-sm text-gray-700 mb-3">
                <p>${role.completedTasks} of ${role.totalTasks} tasks completed</p>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full" style="width: ${role.completionRate}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-2 text-right">${role.completionRate.toFixed(1)}% completion</p>
        </div>
    `).join('');
}

function renderTopPerformers(members) {
    const container = document.getElementById('topPerformersList');
    if (!container) return;
    
    container.innerHTML = members.slice(0, 5).map(member => `
        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
            <div>
                <p class="font-semibold text-gray-800">${member.name}</p>
                <p class="text-xs text-gray-600">${member.role} • ${member.status}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-blue-600">${member.completionRate.toFixed(0)}%</p>
                <p class="text-xs text-gray-600">${member.completedTasks}/${member.totalTasks}</p>
            </div>
        </div>
    `).join('');
}

</script>

