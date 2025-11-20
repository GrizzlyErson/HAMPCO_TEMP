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
  <script type="text/javascript" src="app.js" defer></script>

<div class="hampco-sidebar-isolation">
<nav id="sidebar">
    <ul>
      <li>
        <span class="logo"><img src="../img/logo.png" alt="HAMPCO" class="navbar-logo" style="height: 30px; width: auto;"></span>
        
        <button onclick=toggleSidebar() id="toggle-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8ede9ff"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
        </button>
       
        </li>
       <div id="profcard" style="margin-bottom: 20px;">


       
      
          <div class="max-w-sm mx-auto shadow-lg rounded-lg overflow-hidden border border-gray-200" style="background-color: #b0e6afff;">
          <div class="flex items-center px-1 py-4">
            <div class="ml-4">
              <h2 class="font-semibold text-gray-800">
              <span class="fluid-name"><?= ucfirst($On_Session[0]['fullname']) ?></span></h2>
              <p class="text-sm text-gray-600">ROLE: <?= ucfirst($On_Session[0]['role']) ?></p>
            </div>
            <img class="ml-2 w-16 h-16 object-cover rounded-full border-2 border-indigo-500" src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile Picture">
            
          </div>
        </div>
        </div>

      
      
      <li>
        <a href="member_dashboard.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M520-640v-160q0-17 11.5-28.5T560-840h240q17 0 28.5 11.5T840-800v160q0 17-11.5 28.5T800-600H560q-17 0-28.5-11.5T520-640ZM120-480v-320q0-17 11.5-28.5T160-840h240q17 0 28.5 11.5T440-800v320q0 17-11.5 28.5T400-440H160q-17 0-28.5-11.5T120-480Zm400 320v-320q0-17 11.5-28.5T560-520h240q17 0 28.5 11.5T840-480v320q0 17-11.5 28.5T800-120H560q-17 0-28.5-11.5T520-160Zm-400 0v-160q0-17 11.5-28.5T160-360h240q17 0 28.5 11.5T440-320v160q0 17-11.5 28.5T400-120H160q-17 0-28.5-11.5T120-160Zm80-360h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/></svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="production.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M160-120q-33 0-56.5-23.5T80-200v-360q0-33 23.5-56.5T160-640h200v-200q0-33 23.5-56.5T440-920h80q33 0 56.5 23.5T600-840v200h200q33 0 56.5 23.5T880-560v360q0 33-23.5 56.5T800-120H160Zm0-80h640v-360H600v120q0 17-11.5 28.5T560-400h-160q-17 0-28.5-11.5T360-440v-120H160v360Zm240-400h160v-200h-160v200Zm-80 240h320v-80H320v80Z"/></svg>
          <span>Production Line</span>
        </a>
      </li>
      <li>
        <a href="settings.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M560-440v-120H400v-280h160v-120h80v120h160v280H640v120h-80ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0 0v-560 560Z"/></svg>
          <span>Settings</span>
        </a>
      </li>
      <li style="font-color:#ffffff;!important;">
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

    <script>


    </script>