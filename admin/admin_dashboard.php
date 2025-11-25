<?php
require_once "components/header.php";

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="mobile-fix.css">
  <style>
    @keyframes countPulse {
      0% {
        transform: scale(1);
        opacity: 1;
      }
      50% {
        transform: scale(1.15);
        opacity: 0.7;
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }
    .count-animate {
      animation: countPulse 0.6s ease-in-out;
    }
  </style>
</head>
<body class="hampco-admin-sidebar-layout">

  <main>
    <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
                        <i class="fa-solid fa-cart-plus"></i>
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
                                <!-- Unverified Members Section -->
                                <div style="margin-bottom: 24px;">
                                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Pending Verifications</h4>
                                    <ul id="unverifiedMembersList" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                        <li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>
                                    </ul>
                                </div>

                                <!-- Order Notifications Section -->
                                <div style="margin-bottom: 24px;">
                                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Order Notifications</h4>
                                    <ul id="orderNotificationsList" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                        <li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>
                                    </ul>
                                </div>

                                <!-- Declined Tasks Section -->
                                <div>
                                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Declined Assignments</h4>
                                    <ul id="declinedTasksList" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                        <li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">No declined assignments</li>
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

                    <!-- Decline Response Modal -->
                    <div id="declineResponseModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 10000; justify-content: center; align-items: center;">
                        <div style="width: 100%; max-width: 480px; background-color: white; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); display: flex; flex-direction: column; max-height: 420px; margin: 20px;">
                            <div style="padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">Decline Explanation</h3>
                                    <p id="declineResponseTaskInfo" style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;"></p>
                                </div>
                                <button id="closeDeclineResponseModal" style="background: none; border: none; cursor: pointer; color: #6b7280; font-size: 20px; line-height: 1;">✕</button>
                            </div>
                            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                <label for="declineResponseInput" style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Details for member</label>
                                <textarea id="declineResponseInput" rows="5" style="flex: 1; resize: vertical; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-size: 14px; color: #111827;" placeholder="Explain why the task was declined or provide next steps..."></textarea>
                            </div>
                            <div style="padding: 16px; border-top: 1px solid #e5e7eb; display: flex; gap: 8px;">
                                <button id="sendDeclineResponseBtn" style="flex: 1; background-color: #16a34a; color: white; font-weight: 600; padding: 10px 16px; border-radius: 8px; border: none; cursor: pointer; transition: background-color 0.3s;">Send</button>
                                <button id="cancelDeclineResponseBtn" style="flex: 1; background-color: #e5e7eb; color: #374151; font-weight: 600; padding: 10px 16px; border-radius: 8px; border: none; cursor: pointer; transition: background-color 0.3s;">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Total Customers</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php require_once "backend/count_customer.php";?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Total Members</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php require_once "backend/count_member.php";?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Production Items
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">0</div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Active Tasks</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeTasksCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    

                    <!-- Content Row -->

                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4" style="height: 415px; display: flex; flex-direction: column;">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">Current Task Details & Status</h6>
                                </div>
                                <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
                                    <div class="table-responsive" style="flex: 1; overflow-y: auto; overflow-x: auto;">
                                    <table class="table table-sm mb-0" id="recentTasksTable">
                                        <thead class="sticky-top bg-light">
                                        <tr>
                                            <th scope="col" style="font-size: 11px; white-space: nowrap;">Production ID</th>
                                            <th scope="col" style="font-size: 11px; white-space: nowrap;">Product</th>
                                            <th scope="col" style="font-size: 11px; white-space: nowrap;">Member</th>
                                            <th scope="col" style="font-size: 11px; white-space: nowrap;">Status</th>
                                            <th scope="col" style="font-size: 11px; white-space: nowrap;">Created Date</th>
                                            <th scope="col" style="font-size: 11px; white-space: nowrap;">Progress</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">Loading task data...</td>
                                        </tr>
                                        </tbody>
                                    </table>
                        </div>

                                </div>
                            </div>
                        </div>
                        

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4" style="height: 415px; display: flex; flex-direction: column;">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">Task Completion by Role</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="production_line.php">View Production Line</a>
                                            <a class="dropdown-item" href="production_line.php?tab=tasks">View Task Details</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px;">
                                    <div style="width: 100%; display: flex; justify-content: center; align-items: center; margin-bottom: 20px; height: 180px;">
                                        <svg width="160" height="160" viewBox="0 0 160 160" style="transform: rotate(-90deg);">
                                            <!-- Weavers segment -->
                                            <circle cx="80" cy="80" r="60" fill="none" stroke="#4e73df" stroke-width="20" stroke-dasharray="113.1 376.99" stroke-linecap="round" />
                                            <!-- Knotters segment -->
                                            <circle cx="80" cy="80" r="60" fill="none" stroke="#1cc88a" stroke-width="20" stroke-dasharray="131.49 376.99" stroke-dashoffset="-113.1" stroke-linecap="round" />
                                            <!-- Warpers segment -->
                                            <circle cx="80" cy="80" r="60" fill="none" stroke="#36b9cc" stroke-width="20" stroke-dasharray="132.4 376.99" stroke-dashoffset="-244.59" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <div style="width: 100%; text-align: center;">
                                        <div style="margin-bottom: 10px;">
                                            <div style="display: flex; align-items: center; justify-content: center;">
                                                <span style="display: inline-block; width: 10px; height: 10px; background-color: #4e73df; border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="font-size: 14px; font-weight: bold; margin-right: 12px;">Weavers</span>
                                                <span id="weaverPercentage" style="font-size: 16px; font-weight: bold; color: #4e73df;">0%</span>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 10px;">
                                            <div style="display: flex; align-items: center; justify-content: center;">
                                                <span style="display: inline-block; width: 10px; height: 10px; background-color: #1cc88a; border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="font-size: 14px; font-weight: bold; margin-right: 12px;">Knotters</span>
                                                <span id="knotterPercentage" style="font-size: 16px; font-weight: bold; color: #1cc88a;">0%</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div style="display: flex; align-items: center; justify-content: center;">
                                                <span style="display: inline-block; width: 10px; height: 10px; background-color: #36b9cc; border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="font-size: 14px; font-weight: bold; margin-right: 12px;">Warpers</span>
                                                <span id="warperPercentage" style="font-size: 16px; font-weight: bold; color: #36b9cc;">0%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Workforce Summary Row -->
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">👥 Active Members by Role</h6>
                                </div>
                                <div class="card-body">
                                    <div style="display: flex; gap: 40px; justify-content: space-around; flex-wrap: wrap;">
                                        <!-- Knotters -->
                                        <div style="text-align: center; flex: 1; min-width: 150px;">
                                            <div style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; background-color: #e7f3ff; border-radius: 50%; margin: 0 auto 15px;">
                                                <span style="font-size: 32px; font-weight: bold; color: #1cc88a;" id="activeKnottersCount">0</span>
                                            </div>
                                            <p style="margin: 0; font-size: 14px; font-weight: 600; color: #1f2937;">Knotters</p>
                                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">Active on tasks</p>
                                        </div>

                                        <!-- Weavers -->
                                        <div style="text-align: center; flex: 1; min-width: 150px;">
                                            <div style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; background-color: #eff6ff; border-radius: 50%; margin: 0 auto 15px;">
                                                <span style="font-size: 32px; font-weight: bold; color: #4e73df;" id="activeWeaversCount">0</span>
                                            </div>
                                            <p style="margin: 0; font-size: 14px; font-weight: 600; color: #1f2937;">Weavers</p>
                                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">Active on tasks</p>
                                        </div>

                                        <!-- Warpers -->
                                        <div style="text-align: center; flex: 1; min-width: 150px;">
                                            <div style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; background-color: #ecfdf5; border-radius: 50%; margin: 0 auto 15px;">
                                                <span style="font-size: 32px; font-weight: bold; color: #36b9cc;" id="activeWarpersCount">0</span>
                                            </div>
                                            <p style="margin: 0; font-size: 14px; font-weight: 600; color: #1f2937;">Warpers</p>
                                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">Active on tasks</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Content Column -->
                        <div class="col-lg-6 mb-4">

                            <!-- Color System -->


                        </div>
                    </div>

                    <!-- Task Analytics Row -->
                    <div class="row">
                        <!-- Task Status Overview -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-success">
                                    <h6 class="m-0 font-weight-bold text-light">📊 Task Status Overview</h6>
                                </div>
                                <div class="card-body">
                                    <div id="taskStatusContainer" class="space-y-3">
                                        <div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="small font-weight-bold">Pending Tasks <span id="pendingCount" class="text-primary">0</span></span>
                                                <span id="pendingPercent" class="small font-weight-bold text-primary">0%</span>
                                            </div>
                                            <div class="progress">
                                                <div id="pendingBar" class="progress-bar bg-warning" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="small font-weight-bold">In Progress <span id="inProgressCount" class="text-info">0</span></span>
                                                <span id="inProgressPercent" class="small font-weight-bold text-info">0%</span>
                                            </div>
                                            <div class="progress">
                                                <div id="inProgressBar" class="progress-bar bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="small font-weight-bold">Submitted <span id="submittedCount" class="text-secondary">0</span></span>
                                                <span id="submittedPercent" class="small font-weight-bold text-secondary">0%</span>
                                            </div>
                                            <div class="progress">
                                                <div id="submittedBar" class="progress-bar bg-secondary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="small font-weight-bold">Completed <span id="completedCount" class="text-success">0</span></span>
                                                <span id="completedPercent" class="small font-weight-bold text-success">0%</span>
                                            </div>
                                            <div class="progress">
                                                <div id="completedBar" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
    

  </main>


<!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard script loaded');

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
            const declineResponseInput = document.getElementById('declineResponseInput');
            const declineResponseTaskInfo = document.getElementById('declineResponseTaskInfo');
            const closeDeclineResponseModalBtn = document.getElementById('closeDeclineResponseModal');
            const cancelDeclineResponseBtn = document.getElementById('cancelDeclineResponseBtn');
            const sendDeclineResponseBtn = document.getElementById('sendDeclineResponseBtn');
            let activeDeclineId = null;

            function showDeclineResponseModal(details) {
                if (!declineResponseModal || !declineResponseInput) return;
                activeDeclineId = details.id;
                if (declineResponseTaskInfo) {
                    declineResponseTaskInfo.textContent = `${details.production} • ${details.productName} (${details.memberName})`;
                }
                declineResponseInput.value = '';
                declineResponseModal.style.display = 'flex';
                setTimeout(() => {
                    declineResponseInput.focus();
                }, 50);
            }

            function hideDeclineResponseModal() {
                if (declineResponseModal) {
                    declineResponseModal.style.display = 'none';
                }
                activeDeclineId = null;
                if (declineResponseInput) {
                    declineResponseInput.value = '';
                }
            }

            if (closeDeclineResponseModalBtn) {
                closeDeclineResponseModalBtn.addEventListener('click', hideDeclineResponseModal);
            }
            if (cancelDeclineResponseBtn) {
                cancelDeclineResponseBtn.addEventListener('click', hideDeclineResponseModal);
            }
            if (declineResponseModal) {
                declineResponseModal.addEventListener('click', function(e) {
                    if (e.target === declineResponseModal) {
                        hideDeclineResponseModal();
                    }
                });
            }
            if (sendDeclineResponseBtn && declineResponseInput) {
                sendDeclineResponseBtn.addEventListener('click', function() {
                    if (!activeDeclineId) {
                        alert('Select a declined assignment to respond to.');
                        return;
                    }
                    const message = declineResponseInput.value.trim();
                    if (!message) {
                        alert('Please provide details before sending.');
                        return;
                    }
                    const originalText = sendDeclineResponseBtn.textContent;
                    sendDeclineResponseBtn.disabled = true;
                    sendDeclineResponseBtn.textContent = 'Sending...';

                    fetch('backend/end-points/task_declines.php?action=respond', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            decline_id: activeDeclineId,
                            message
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.error || data.message || 'Failed to send explanation');
                        }
                        if (typeof alertify !== 'undefined') {
                            alertify.success('Decline explanation sent');
                        }
                        hideDeclineResponseModal();
                        updateNotifications();
                    })
                    .catch(err => {
                        console.error('Error sending decline explanation:', err);
                        if (typeof alertify !== 'undefined') {
                            alertify.error(err.message || 'Failed to send explanation');
                        } else {
                            alert(err.message || 'Failed to send explanation');
                        }
                    })
                    .finally(() => {
                        sendDeclineResponseBtn.disabled = false;
                        sendDeclineResponseBtn.textContent = originalText;
                    });
                });
            }

            function updateNotifications() {
                console.log('Updating notifications...');
                
                // Show loading state
                const unverifiedList = document.getElementById('unverifiedMembersList');
                const ordersList = document.getElementById('orderNotificationsList');
                const declinedList = document.getElementById('declinedTasksList');
                if (unverifiedList) unverifiedList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>';
                if (ordersList) ordersList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>';
                if (declinedList) declinedList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">Loading...</li>';
                
                Promise.all([
                    fetch('backend/end-points/get_unverified_members.php')
                        .then(r => {
                            console.log('Get unverified members response status:', r.status);
                            return r.json();
                        })
                        .catch(e => {
                            console.error('Error fetching unverified members:', e);
                            return [];
                        }),
                    fetch('backend/end-points/notifications.php?action=get')
                        .then(r => {
                            console.log('Get notifications response status:', r.status);
                            return r.json();
                        })
                        .catch(e => {
                            console.error('Error fetching notifications:', e);
                            return { notifications: [] };
                        }),
                    fetch('backend/end-points/task_declines.php?action=list&status=pending')
                        .then(r => {
                            console.log('Get declined tasks response status:', r.status);
                            return r.json();
                        })
                        .catch(e => {
                            console.error('Error fetching declined tasks:', e);
                            return { declines: [] };
                        })
                ])
                .then(([memberData, notifData, declineData]) => {
                    console.log('Notification data:', memberData, notifData, declineData);
                    
                    const notificationBell = document.querySelector('button[title="Notifications"]');
                    if (!notificationBell) {
                        console.error('Notification bell not found!');
                        return;
                    }
                    
                    const notificationDot = notificationBell.querySelector('span');
                    const unverifiedList = document.getElementById('unverifiedMembersList');
                    const ordersList = document.getElementById('orderNotificationsList');
                    const declinedList = document.getElementById('declinedTasksList');
                    
                    // Check if we have notifications
                    const memberCount = Array.isArray(memberData) ? memberData.length : 0;
                    const notificationCount = (notifData && notifData.notifications) ? notifData.notifications.length : 0;
                    const declineItems = (declineData && declineData.success && Array.isArray(declineData.declines)) ? declineData.declines : [];
                    const declineCount = declineItems.length;
                    const hasNotifications = memberCount > 0 || notificationCount > 0 || declineCount > 0;
                    
                    console.log('Has notifications:', hasNotifications, '(members:', memberCount, 'orders:', notificationCount, 'declines:', declineCount, ')');
                    
                    // Update notification dot visibility
                    if (notificationDot) {
                        notificationDot.style.display = hasNotifications ? 'block' : 'none';
                    }
                    
                    // Update member verification notifications
                    if (unverifiedList) {
                        if (Array.isArray(memberData) && memberData.length > 0) {
                            unverifiedList.innerHTML = memberData.map(member => `
                                <li style="padding: 12px; background-color: #fffbeb; border-radius: 6px; border: 1px solid #fef3c7; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;" class="notification-item" data-member-id="${member.member_id}" onmouseover="this.style.backgroundColor='#fef08a'" onmouseout="this.style.backgroundColor='#fffbeb'">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <div style="flex: 1;">
                                            <h4 style="font-weight: 600; color: #1f2937; margin: 0; font-size: 14px;">${member.member_fullname}</h4>
                                            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">Role: ${member.member_role}</p>
                                            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">Contact: ${member.member_phone}</p>
                                        </div>
                                        <span style="padding: 4px 8px; background-color: #fcd34d; color: #92400e; border-radius: 9999px; font-size: 12px; white-space: nowrap; margin-left: 8px;">Pending</span>
                                    </div>
                                </li>
                            `).join('');
                            
                            // Add click handlers for member verification notifications
                            unverifiedList.querySelectorAll('.notification-item').forEach(item => {
                                item.addEventListener('click', function() {
                                    window.location.href = 'member.php';
                                });
                            });
                        } else {
                            unverifiedList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">No pending verifications</li>';
                        }
                    }

                    // Update order notifications
                    if (ordersList) {
                        if (notifData && notifData.notifications && notifData.notifications.length > 0) {
                            ordersList.innerHTML = notifData.notifications.map(notif => `
                                <li style="padding: 12px; background-color: #fef3c7; border-radius: 6px; border: 1px solid #fde68a; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;" class="order-notification-item" data-order-id="${notif.id}" onmouseover="this.style.backgroundColor='#fde68a'" onmouseout="this.style.backgroundColor='#fef3c7'">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                                        <div style="flex: 1;">
                                            <h4 style="font-weight: 600; color: #1f2937; margin: 0; font-size: 14px;">Order #${notif.id}</h4>
                                            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">Customer: ${notif.customer_name}</p>
                                            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">Total: ₱${parseFloat(notif.total_amount).toFixed(2)}</p>
                                            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">Method: ${notif.payment_method}</p>
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 6px; white-space: nowrap;">
                                            <button onclick="acceptOrderFromNotif(${notif.id})" style="padding: 4px 8px; background-color: #10b981; color: white; border-radius: 4px; font-size: 11px; border: none; cursor: pointer; transition: background-color 0.3s;">✓ Accept</button>
                                            <button onclick="declineOrderFromNotif(${notif.id})" style="padding: 4px 8px; background-color: #ef4444; color: white; border-radius: 4px; font-size: 11px; border: none; cursor: pointer; transition: background-color 0.3s;">✕ Decline</button>
                                        </div>
                                    </div>
                                </li>
                            `).join('');
                        } else {
                            ordersList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">No new order notifications</li>';
                        }
                    }

                    // Update declined task notifications
                    if (declinedList) {
                        if (declineCount > 0) {
                            declinedList.innerHTML = declineItems.map(decline => `
                                <li style="padding: 12px; background-color: #fee2e2; border-radius: 6px; border: 1px solid #fecaca; margin-bottom: 8px; display: flex; flex-direction: column; gap: 8px;">
                                    <div>
                                        <h4 style="font-weight: 600; color: #991b1b; margin: 0; font-size: 14px;">${escapeHtml(decline.production_code)} • ${escapeHtml(decline.product_name)}</h4>
                                        <p style="font-size: 12px; color: #7f1d1d; margin: 4px 0 0 0;">Member: ${escapeHtml(decline.member_name)}</p>
                                        ${decline.member_reason ? `<p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">Reason: ${escapeHtml(decline.member_reason)}</p>` : ''}
                                        <p style="font-size: 11px; color: #6b7280; margin: 4px 0 0 0;">Declined: ${new Date(decline.declined_at).toLocaleString()}</p>
                                    </div>
                                    <button class="respond-decline-btn" 
                                        data-id="${decline.id}" 
                                        data-prod="${encodeURIComponent(decline.production_code || '')}" 
                                        data-product="${encodeURIComponent(decline.product_name || '')}" 
                                        data-member="${encodeURIComponent(decline.member_name || '')}"
                                        style="align-self: flex-start; padding: 6px 10px; background-color: #dc2626; color: white; border: none; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                        Add Explanation
                                    </button>
                                </li>
                            `).join('');

                            declinedList.querySelectorAll('.respond-decline-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const declineId = parseInt(this.getAttribute('data-id'), 10);
                                    const production = decodeURIComponent(this.getAttribute('data-prod') || '');
                                    const productName = decodeURIComponent(this.getAttribute('data-product') || '');
                                    const memberName = decodeURIComponent(this.getAttribute('data-member') || '');
                                    showDeclineResponseModal({
                                        id: declineId,
                                        production,
                                        productName,
                                        memberName
                                    });
                                });
                            });
                        } else {
                            declinedList.innerHTML = '<li style="padding: 12px; color: #9ca3af; text-align: center; font-size: 14px;">No declined assignments</li>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating notifications:', error);
                    if (unverifiedList) {
                        unverifiedList.innerHTML = '<li style="padding: 12px; color: #dc2626; text-align: center; font-size: 14px;">Error loading notifications</li>';
                    }
                    if (ordersList) {
                        ordersList.innerHTML = '<li style="padding: 12px; color: #dc2626; text-align: center; font-size: 14px;">Error loading order notifications</li>';
                    }
                    const declinedList = document.getElementById('declinedTasksList');
                    if (declinedList) {
                        declinedList.innerHTML = '<li style="padding: 12px; color: #dc2626; text-align: center; font-size: 14px;">Error loading declined assignments</li>';
                    }
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
            window.markNotificationRead = function(notificationId) {
                console.log('Marking notification as read:', notificationId);
                fetch('backend/end-points/notifications.php?action=mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ notification_id: notificationId })
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
                    console.log('Marking all notifications as read');
                    fetch('backend/end-points/notifications.php?action=mark-read', {
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

            // Accept order from notification
            window.acceptOrderFromNotif = function(orderId) {
                if (confirm(`Accept order #${orderId}?`)) {
                    updateOrderStatus(orderId, 'Accepted', null);
                }
            };

            // Decline order from notification
            window.declineOrderFromNotif = function(orderId) {
                if (confirm(`Decline order #${orderId}?`)) {
                    updateOrderStatus(orderId, 'Declined', null);
                }
            };
        });
    </script>

    <!-- Recent Tasks Data Loading Script -->
    <script>
    // Load recent tasks data - combines assigned tasks and member task requests
    function loadRecentTasks() {
        Promise.all([
            fetch('backend/end-points/get_task_assignments.php').then(r => r.json()),
            fetch('backend/end-points/get_task_requests.php').then(r => r.json())
        ])
        .then(([assignedTasksData, memberRequestsData]) => {
            console.log('Assigned tasks data:', assignedTasksData);
            console.log('Member requests data:', memberRequestsData);
            
            const tableBody = document.querySelector('#recentTasksTable tbody');
            let allTasks = [];

            // Process assigned tasks
            if (assignedTasksData && assignedTasksData.success && assignedTasksData.data) {
                assignedTasksData.data.forEach(item => {
                    item.assignments.forEach(assignment => {
                        allTasks.push({
                            production_id: item.prod_line_id,
                            product_name: item.product_name,
                            member_name: assignment.member_name || 'N/A',
                            role: assignment.role || item.status,
                            status: assignment.task_status || item.status,
                            date_created: item.date_created,
                            type: 'assigned'
                        });
                    });
                });
            }

            // Process member task requests and self-tasks
            if (Array.isArray(memberRequestsData)) {
                memberRequestsData.forEach(request => {
                    allTasks.push({
                        production_id: request.production_id || 'N/A',
                        product_name: request.product_name,
                        member_name: request.member_name,
                        role: request.role,
                        status: request.status,
                        date_created: request.date_created,
                        type: request.type === 'self_task' ? 'Member Created' : 'Request'
                    });
                });
            }

            // Sort by date_created (most recent first)
            allTasks.sort((a, b) => new Date(b.date_created) - new Date(a.date_created));

            // Limit to 8 most recent tasks
            const recentTasks = allTasks.slice(0, 8);
            
            if (recentTasks.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No active tasks</td></tr>';
                return;
            }
            
            tableBody.innerHTML = recentTasks.map(task => {
                // Determine badge colors based on status
                let badgeBgColor = '#6c757d';
                let badgeTextColor = '#ffffff';

                if (task.status === 'declined') {
                    badgeBgColor = '#dc3545'; // Red badge
                    badgeTextColor = '#ffffff';
                } else if (task.status === 'pending') {
                    badgeBgColor = '#ffc107'; // Yellow badge
                    badgeTextColor = '#000000';
                } else if (task.status === 'submitted') {
                    badgeBgColor = '#0d6efd'; // Blue badge
                    badgeTextColor = '#ffffff';
                } else if (task.status === 'in_progress') {
                    badgeBgColor = '#ff6a07ff'; // Yellow badge
                    badgeTextColor = '#000000';
                } else if (task.status === 'completed') {
                    badgeBgColor = '#28a745'; // Green badge
                    badgeTextColor = '#ffffff';
                } else if (task.status === 'approved') {
                    badgeBgColor = '#28a745'; // Green badge
                    badgeTextColor = '#ffffff';
                }
                
                const statusLabel = task.status.charAt(0).toUpperCase() + task.status.slice(1);
                
                return `
                    <tr style="height: 40px; vertical-align: middle;">
                        <td style="font-size: 13px; font-weight: 500; padding: 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80px;">${task.production_id}</td>
                        <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">${task.product_name}</td>
                        <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80px;" title="${task.member_name}">${task.member_name}</td>
                        <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap;">
                            <span style="display: inline-block; background-color: ${badgeBgColor}; color: ${badgeTextColor}; font-size: 12px; padding: 4px 8px; border-radius: 4px;">${statusLabel}</span>
                        </td>
                        <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; color: #666;">
                            ${task.date_created ? new Date(task.date_created).toLocaleDateString() : '-'}
                        </td>
                        <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; color: #999;">
                            <span style="display: inline-block; background-color: #e9ecef; color: #495057; font-size: 12px; padding: 4px 8px; border-radius: 4px;">${typeof task.type === 'string' ? task.type : 'Assigned'}</span>
                        </td>
                    </tr>
                `;
            }).join('');
            
            // Update active tasks count
            updateActiveTasksCount(allTasks);
        })
        .catch(error => {
            console.error('Error loading recent tasks:', error);
            const tableBody = document.querySelector('#recentTasksTable tbody');
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">Error loading tasks</td></tr>';
        });
    }

    // Update the active tasks count in the card
    function updateActiveTasksCount(allTasks) {
        const countElement = document.getElementById('activeTasksCount');
        if (!countElement) return;
        
        // Count tasks with status 'in_progress' or 'pending'
        const activeCount = allTasks.filter(task => 
            task.status === 'in_progress' || task.status === 'pending'
        ).length;
        
        countElement.textContent = activeCount;
    }

    // Load member created tasks for the admin dashboard
    function loadMemberCreatedTasks() {
        const tableBody = document.getElementById('memberTasksTableBody');
        if (!tableBody) return;

        fetch('backend/end-points/get_all_self_tasks.php')
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success && data.tasks && Array.isArray(data.tasks)) {
                    if (data.tasks.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No member created tasks</td></tr>';
                        return;
                    }

                    tableBody.innerHTML = data.tasks.map(task => {
                        const statusBadgeClass = task.status === 'completed' ? 'badge-success' :
                                                task.status === 'submitted' ? 'badge-warning' :
                                                task.status === 'in_progress' ? 'badge-info' :
                                                'badge-secondary';

                        const approvalBadgeClass = task.approval_status === 'approved' ? 'badge-success' :
                                                 task.approval_status === 'rejected' ? 'badge-danger' :
                                                 'badge-warning';

                        const statusLabel = task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ');
                        const approvalLabel = task.approval_status.charAt(0).toUpperCase() + task.approval_status.slice(1);

                        return `
                            <tr style="height: 40px; vertical-align: middle;">
                                <td style="font-size: 13px; font-weight: 500; padding: 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">${task.production_id || 'N/A'}</td>
                                <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px;">${task.product_name}</td>
                                <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;" title="${task.member_name}">${task.member_name}</td>
                                <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70px;">
                                    <span class="badge badge-light" style="font-size: 11px; padding: 3px 6px;">${task.role}</span>
                                </td>
                                <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; text-align: center;">${task.weight_g}</td>
                                <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap;">
                                    <span class="badge badge-sm ${statusBadgeClass}" style="display: inline-block; font-size: 11px; padding: 3px 6px;">${statusLabel}</span>
                                </td>
                                <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap;">
                                    <span class="badge badge-sm ${approvalBadgeClass}" style="display: inline-block; font-size: 11px; padding: 3px 6px;">${approvalLabel}</span>
                                </td>
                                <td style="font-size: 13px; padding: 6px 10px; white-space: nowrap; color: #666;">
                                    ${task.date_created ? new Date(task.date_created).toLocaleDateString() : '-'}
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No member created tasks</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading member created tasks:', error);
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-3">Error loading tasks</td></tr>';
            });
    }

    // Helper function to format time ago
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Now';
        if (minutes < 60) return `${minutes}m`;
        if (hours < 24) return `${hours}h`;
        if (days < 7) return `${days}d`;
        
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    // Load active members by role from member list
    function loadActiveMembersByRole() {
        fetch('backend/end-points/get_workforce_members.php')
            .then(response => response.json())
            .then(data => {
                var summary = {
                    knotter: 0,
                    warper: 0,
                    weaver: 0
                };
                
                // Count members with 'available' status for each role
                data.forEach(member => {
                    const role = member.role.toLowerCase();
                    if (['knotter', 'warper', 'weaver'].indexOf(role) !== -1) {
                        if (member.availability_status === 'available') {
                            summary[role]++;
                        }
                    }
                });
                
                // Update the cards with available members only
                document.getElementById('activeKnottersCount').textContent = summary.knotter;
                document.getElementById('activeWeaversCount').textContent = summary.weaver;
                document.getElementById('activeWarpersCount').textContent = summary.warper;
            })
            .catch(error => {
                console.error('Error loading active members by role:', error);
            });
    }

    // Load tasks on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadRecentTasks();
        loadMemberCreatedTasks();
        loadTaskCompletionChart();
        loadTaskStatusOverview();
        loadActiveMembersByRole();
        // Refresh every 30 seconds
        setInterval(loadRecentTasks, 30000);
        setInterval(loadMemberCreatedTasks, 30000);
        setInterval(loadTaskCompletionChart, 60000);
        setInterval(loadTaskStatusOverview, 60000);
        setInterval(loadActiveMembersByRole, 60000);
    });

    // Load Task Status Overview with Progress Bars
    function loadTaskStatusOverview() {
        fetch('backend/end-points/get_current_task_status.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.summary) {
                    const summary = data.summary;
                    const total = summary.total_active_tasks;
                    
                    // Calculate percentages
                    const pendingPercent = total > 0 ? Math.round((summary.pending_tasks / total) * 100) : 0;
                    const inProgressPercent = total > 0 ? Math.round((summary.in_progress_tasks / total) * 100) : 0;
                    const submittedPercent = total > 0 ? Math.round((summary.submitted_tasks / total) * 100) : 0;
                    const completedPercent = total > 0 ? Math.round((summary.completed_tasks || 0 / total) * 100) : 0;
                    
                    // Update counts
                    document.getElementById('pendingCount').textContent = summary.pending_tasks;
                    document.getElementById('inProgressCount').textContent = summary.in_progress_tasks;
                    document.getElementById('submittedCount').textContent = summary.submitted_tasks;
                    document.getElementById('completedCount').textContent = summary.completed_tasks || 0;
                    
                    // Update percentages
                    document.getElementById('pendingPercent').textContent = pendingPercent + '%';
                    document.getElementById('inProgressPercent').textContent = inProgressPercent + '%';
                    document.getElementById('submittedPercent').textContent = submittedPercent + '%';
                    document.getElementById('completedPercent').textContent = completedPercent + '%';
                    
                    // Update progress bars
                    document.getElementById('pendingBar').style.width = pendingPercent + '%';
                    document.getElementById('inProgressBar').style.width = inProgressPercent + '%';
                    document.getElementById('submittedBar').style.width = submittedPercent + '%';
                    document.getElementById('completedBar').style.width = completedPercent + '%';
                    
                    // Update deadline status with animation
                    function animateCountUpdate(elementId, newValue) {
                        const element = document.getElementById(elementId);
                        if (element) {
                            element.classList.remove('count-animate');
                            // Trigger reflow to restart animation
                            void element.offsetWidth;
                            element.textContent = newValue;
                            element.classList.add('count-animate');
                        }
                    }
                    
                    animateCountUpdate('overdueCount', summary.overdue_tasks);
                    animateCountUpdate('dueCount', summary.urgent_tasks);
                    animateCountUpdate('onTrackCount', total - summary.overdue_tasks - summary.urgent_tasks);
                    animateCountUpdate('completedTaskCount', summary.completed_tasks || 0);
                }
            })
            .catch(error => console.error('Error loading task status overview:', error));
    }

    // Task Completion Chart by Role
    let taskCompletionChartInstance = null;

    function loadTaskCompletionChart() {
        fetch('backend/end-points/get_task_completion_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    
                    // Update percentage displays
                    document.getElementById('weaverPercentage').textContent = stats.weaver.percentage + '%';
                    document.getElementById('knotterPercentage').textContent = stats.knotter.percentage + '%';
                    document.getElementById('warperPercentage').textContent = stats.warper.percentage + '%';

                    // Calculate SVG doughnut chart segments
                    const weaverPercent = stats.weaver.percentage;
                    const knotterPercent = stats.knotter.percentage;
                    const warperPercent = stats.warper.percentage;
                    
                    // Circumference of the circle (radius 60, so 2*pi*60 = 376.99)
                    const circumference = 376.99;
                    
                    // Calculate dasharray for each segment
                    const weaverDash = (weaverPercent / 100) * circumference;
                    const knotterDash = (knotterPercent / 100) * circumference;
                    const warperDash = (warperPercent / 100) * circumference;
                    
                    // Update SVG circles
                    const circles = document.querySelectorAll('circle[stroke]');
                    if (circles.length >= 3) {
                        // Weavers (blue)
                        circles[0].setAttribute('stroke-dasharray', weaverDash + ' ' + circumference);
                        circles[0].setAttribute('stroke-dashoffset', '0');
                        
                        // Knotters (green)
                        circles[1].setAttribute('stroke-dasharray', knotterDash + ' ' + circumference);
                        circles[1].setAttribute('stroke-dashoffset', '-' + weaverDash);
                        
                        // Warpers (cyan)
                        circles[2].setAttribute('stroke-dasharray', warperDash + ' ' + circumference);
                        circles[2].setAttribute('stroke-dashoffset', '-' + (weaverDash + knotterDash));
                    }
                }
            })
            .catch(error => console.error('Error loading task completion chart:', error));
    }
    </script>

</body>
</html>