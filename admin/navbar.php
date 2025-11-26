<link rel="stylesheet" href="navbar.css">
 <link rel="icon" href="../img/logo.png" type="image/x-icon">
  <script type="text/javascript" src="app.js" defer></script>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
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
          <svg id="icon-open" class="hidden" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m647-480-155-156-11-11.5T468-628q11-11 28-11t28 11L708-448q6 6 8.5 13t2.5 15q0 8-2.5 15t-8.5 13L524-228q-11 11-27.5 11.5T468-204q-11-11-11-28t11-28l179-180Zm-264 0-155-156-11-11.5T204-628q11-11 28-11t28 11L444-448q6 6 8.5 13t2.5 15q0 8-2.5 15t-8.5 13L260-228q-11 11-27.5 11.5T204-204q-11-11-11-28t11-28l179-180Z"/></svg>
          <svg id="icon-close" class="" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
        </button>
      </li>
      
      <li class="active">
        <a href="admin_dashboard.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M200-200h160v-280H200v280Zm240 0h160v-560H440v560Zm240 0h160v-360H680v360ZM80-80v-720q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v720H80Z"/></svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="member.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M0-240v-63q0-43 44-70t116-27q13 0 25 .5t23 1.5q-14 21-21 44t-7 48v66H0Zm240 0v-66q0-66 44-105.5T280-450q75 0 120 39.5t45 105.5v66H240Zm540 0v-66q0-25-7-48t-21-44q11-1 23-1.5t25-.5q72 0 116 27t44 70v63H780Zm-360-80h100v-70q0-29-21-49t-29-20q-8 0-29 20t-21 49v70Zm-420 0h100v-70q0-29-21-49t-29-20q-8 0-29 20t-21 49v70Zm540 0h100v-70q0-29-21-49t-29-20q-8 0-29 20t-21 49v70ZM480-440q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42Zm-240-80q-33 0-56.5-23.5T160-600q0-33 23.5-56.5T240-680q33 0 56.5 23.5T320-600q0 33-23.5 56.5T240-520Zm480 0q-33 0-56.5-23.5T640-600q0-33 23.5-56.5T720-680q33 0 56.5 23.5T800-600q0 33-23.5 56.5T720-520Z"/></svg>
          <span>Members</span>
        </a>
      </li>
      <li>
        <a href="workers.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M840-680v400q0 33-23.5 56.5T760-200H200q-33 0-56.5-23.5T120-280v-400q0-33 23.5-56.5T200-760h560q33 0 56.5 23.5T840-680Zm-60 0H180v80h600v-80Zm-600 400h600v-320H180v320Zm300-160q17 0 28.5-11.5T520-440q0-17-11.5-28.5T480-480q-17 0-28.5 11.5T440-440q0 17 11.5 28.5T480-400Z"/></svg>
          <span>Activity Monitoring</span>
        </a>
      </li>
      <li>
      <li>
        <a href="orders.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M320-240h320v-80H320v80Zm0-120h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h480q33 0 56.5 23.5T800-800v640q0 33-23.5 56.5T720-80H240Zm0-80h480v-640H240v640Zm0 0v-640 640Z"/></svg>
          <span>Orders</span>
        </a>
      </li>
      <li>
        <a href="production_line.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560H120Zm80-80h560v-480H200v480Zm0 0v-480 480Zm160-40h240v-80H360v80Zm120-120q17 0 28.5-11.5T520-480q0-17-11.5-28.5T480-520q-17 0-28.5 11.5T440-480q0 17 11.5 28.5T480-440Z"/></svg>
          <span>Production Line</span>
        </a>
      </li>
      <li>
        <a href="payment.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M840-680v400q0 33-23.5 56.5T760-200H200q-33 0-56.5-23.5T120-280v-400q0-33 23.5-56.5T200-760h560q33 0 56.5 23.5T840-680Zm-60 0H180v80h600v-80Zm-600 400h600v-320H180v320Zm300-160q17 0 28.5-11.5T520-440q0-17-11.5-28.5T480-480q-17 0-28.5 11.5T440-440q0 17 11.5 28.5T480-400Z"/></svg>
          <span>Payments</span>
        </a>
      </li>
      <li>
        <button onclick=toggleSubMenu(this) class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M440-183v-274H266v-60h294v334h-120Zm0-354v-60h200v60H440Zm80-120q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM80-80v-720q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v720H80Zm60-60h720v-660H140v660Zm0 0v-660 660Z"/></svg>
          <span>Inventory</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li><a href="products.php">Products</a></li>
            <li><a href="raw_materials.php">Materials</a></li>
            <li><a href="raw_stock_logs.php">Raw Logs</a></li>
          </div>
        </ul>
      </li>
      <li>
        <a href="logout.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h280v80H200Zm440-160-55-58 102-102H360v-80h327L585-622l55-58 200 200-200 200Z"/></svg>
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
                fetch('backend/get_unverified_members.php').then(r => r.json()),
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