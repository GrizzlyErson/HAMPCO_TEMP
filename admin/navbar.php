<link rel="stylesheet" href="navbar.css">
 <link rel="icon" href="../img/logo.png" type="image/x-icon">
  <script type="text/javascript" src="app.js" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="backend/header.min.css" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.13.1/css/alertify.css" integrity="sha512-MpdEaY2YQ3EokN6lCD6bnWMl5Gwk7RjBbpKLovlrH6X+DRokrPRAF3zQJl1hZUiLXfo2e9MrOt+udOnHCAmi5w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.13.1/alertify.min.js" integrity="sha512-JnjG+Wt53GspUQXQhc+c4j8SBERsgJAoHeehagKHlxQN+MtCCmFDghX9/AcbkkNRZptyZU4zC8utK59M5L45Iw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="https://cdn.lineicons.com/5.0/lineicons.css" rel="stylesheet" />

  <link rel="stylesheet" href="navbar.css">

<div class="hampco-sidebar-isolation">
<nav id="sidebar" class="close">
    <ul>
      <li>
        <span class="logo"><img src="../img/logo.png" alt="HAMPCO" class="navbar-logo" style="height: 30px; width: auto;"></span>
        <button onclick=toggleSidebar() id="toggle-btn">
          <svg id="icon-open" class="hidden" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
          <svg id="icon-close" class="" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m647-480-155-156-11-11.5T468-628q11-11 28-11t28 11L708-448q6 6 8.5 13t2.5 15q0 8-2.5 15t-8.5 13L524-228q-11 11-27.5 11.5T468-204q-11-11-11-28t11-28l179-180Zm-264 0-155-156-11-11.5T204-628q11-11 28-11t28 11L444-448q6 6 8.5 13t2.5 15q0 8-2.5 15t-8.5 13L260-228q-11 11-27.5 11.5T204-204q-11-11-11-28t11-28l179-180Z"/></svg>
        </button>
      </li>
      
      <li class="active">
        <a href="admin_dashboard.php">
          <i class="fas fa-solid fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="member.php">
          <i class="fas fa-solid fa-users"></i>
          <span>Members</span>
        </a>
      </li>
      <li>
        <a href="workers.php">
          <i class="fas fa-solid fa-chart-line"></i>
          <span>Activity Monitoring</span>
        </a>
      </li>
      <li>
        <a href="customer_overview.php">
          <i class="fas fa-solid fa-chart-bar"></i>
          <span>Sales Forecasting</span>
        </a>
      </li>
      <li>
        <a href="orders.php">
          <i class="fas fa-solid fa-shopping-cart"></i>
          <span>Orders</span>
        </a>
      </li>
      <li>
        <a href="production_line.php">
          <i class="fas fa-solid fa-cogs"></i>
          <span>Production Line</span>
        </a>
      </li>
      <li>
        <a href="payment.php">
          <i class="fas fa-solid fa-money-bill-alt"></i>
          <span>Payments</span>
        </a>
      </li>
      
      <li>
        <button onclick=toggleSubMenu(this) class="dropdown-btn">
          <i class="fas fa-solid fa-warehouse"></i>
          <span>Inventory</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li><a href="products.php"><i class="fas fa-solid fa-box-open mr-2"></i>Products</a></li>
            <li><a href="raw_materials.php"><i class="fas fa-solid fa-boxes mr-2"></i>Materials</a></li>
            <li><a href="raw_stock_logs.php"><i class="fas fa-solid fa-clipboard-list mr-2"></i>Raw Logs</a></li>
          </div>
        </ul>
      </li>
      <li>
        <a href="logout.php">
          <i class="fas fa-solid fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </nav>
</div>


<script type="text/javascript" src="app.js" defer></script>

<script>
        // Set active menu item based on current page
        function setActiveMenuItem() {
            const currentPage = window.location.pathname.split('/').pop() || 'admin_dashboard.php';
            const menuItems = document.querySelectorAll('nav#sidebar ul li');
            
            menuItems.forEach(item => {
                const link = item.querySelector('a');
                if (link) {
                    const href = link.getAttribute('href');
                    if (href === currentPage || (currentPage === '' && href === 'admin_dashboard.php')) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                }
            });
        }

        // Call on page load
        document.addEventListener('DOMContentLoaded', setActiveMenuItem);
        // Also call immediately in case DOM is already loaded
        setActiveMenuItem();

        function updateNotifications() {
            Promise.all([
                fetch('backend/end-points/get_unverified_members.php').then(r => r.json()),
                fetch('backend/end-points/notifications.php?action=get').then(r => r.json())
            ])
            .then(([memberData, notifData]) => {
                const notificationBell = document.querySelector('button[title="Notifications"]');
                const notificationDot = notificationBell.querySelector('span');
                const unverifiedList = document.getElementById('unverifiedMembersList');
                const hasNotifications = (memberData && memberData.length > 0) || (notifData.notifications && notifData.notifications.length > 0);
                    
                    // Update notification dot visibility
                    notificationDot.classList.toggle('hidden', !hasNotifications);
                    
                    // Update member verification notifications
                    if (memberData && memberData.length > 0) {
                        unverifiedList.innerHTML = memberData.map(member => `
                            <li class="p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-800">${member.member_fullname}</h4>
                                        <p class="text-sm text-gray-600">Role: ${member.member_role}</p>
                                        <p class="text-sm text-gray-600">Contact: ${member.member_phone}</p>
                                    </div>
                                    <span class="px-2 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs">Pending</span>
                                </div>
                            </li>
                        `).join('');
                    } else {
                        unverifiedList.innerHTML = '<li class="p-3 text-gray-500 text-center">No pending verifications</li>';
                    }

                    // Update order notifications
                    const ordersList = document.getElementById('orderNotificationsList');
                    if (notifData.notifications && notifData.notifications.length > 0) {
                        ordersList.innerHTML = notifData.notifications.map(notif => `
                            <li class="p-3 ${notif.is_read ? 'bg-gray-50' : 'bg-blue-50'} rounded-lg border ${notif.is_read ? 'border-gray-100' : 'border-blue-100'}" data-id="${notif.id}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-800">${notif.message}</h4>
                                        <p class="text-sm text-gray-600">${new Date(notif.created_at).toLocaleString()}</p>
                                    </div>
                                    ${!notif.is_read ? `
                                        <button onclick="markNotificationRead(${notif.id})" class="px-2 py-1 text-sm text-blue-600 hover:text-blue-800">
                                            Mark read
                                        </button>
                                    ` : ''}
                                </div>
                            </li>
                        `).join('');
                    } else {
                        ordersList.innerHTML = '<li class="p-3 text-gray-500 text-center">No new order notifications</li>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                });
        }

        // Initial check for notifications
        updateNotifications();

        // Check for new notifications every 30 seconds
        setInterval(updateNotifications, 30000);

        document.querySelector('button[title="Notifications"]').addEventListener('click', function() {
            document.getElementById('notificationModal').classList.remove('hidden');
            updateNotifications(); // Refresh notifications when opening modal
        });

        document.getElementById('closeNotificationModal').addEventListener('click', function() {
            document.getElementById('notificationModal').classList.add('hidden');
        });

        // Function to mark a single notification as read
        function markNotificationRead(notificationId) {
            fetch('backend/end-points/notifications.php?action=mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotifications();
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }

        // Handle mark all as read button
        document.getElementById('markAllRead').addEventListener('click', function() {
            fetch('backend/end-points/notifications.php?action=mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotifications();
                    alertify.success('All notifications marked as read');
                }
            })
            .catch(error => console.error('Error marking all notifications as read:', error));
        });

        // Play notification sound for new orders
        function playNotificationSound() {
            const audio = new Audio('../assets/notification.mp3');
            audio.play().catch(e => console.log('Audio playback prevented:', e));
        }

        // Check for new notifications and play sound if there are new ones
        let previousNotificationCount = 0;
        function checkNewNotifications() {
            fetch('backend/end-points/notifications.php?action=get')
                .then(response => response.json())
                .then(data => {
                    const currentCount = data.notifications.filter(n => !n.is_read).length;
                    if (currentCount > previousNotificationCount) {
                        playNotificationSound();
                        if (Notification.permission === "granted") {
                            new Notification("New Order Received", {
                                body: "You have a new order waiting for review",
                                icon: "../assets/image/logo.png"
                            });
                        }
                    }
                    previousNotificationCount = currentCount;
                });
        }

        // Request notification permission
        if ("Notification" in window) {
            Notification.requestPermission();
        }

        // Check for new notifications every 30 seconds
        setInterval(checkNewNotifications, 30000);
    </script>