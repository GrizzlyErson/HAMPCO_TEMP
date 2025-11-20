<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>HAMPCO || CUSTOMER</title>
  <script src="https://cdn.tailwindcss.com"></script>
  
  <style>
  /* Existing styles */
  
  /* Change the navbar background color */
  nav.bg-slate-800 {
    background-color: #4e7c4e; /* Replace with your desired color */
  }

</style>
</head>
<body class="bg-gray-100">

  <!-- Sticky Navbar -->
  <nav class="bg-slate-800 text-white px-4 py-3 fixed top-0 left-0 right-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto flex items-center justify-between md:justify-normal relative">
      
      <!-- Mobile: Left - Hamburger -->
      <button id="menu-btn" class="md:hidden text-white text-2xl focus:outline-none">
        ☰
      </button>

      <!-- Mobile: Center - Logo -->
      <div class="absolute left-1/2 transform -translate-x-1/2 md:static md:translate-x-0 md:left-0 text-xl font-bold">
        <img src="../img/logo.png" alt="Logo" style="width: 140px; height: auto;" class="inline-block mr-2">
      </div>

      <!-- Mobile: Right - Notification Bell -->
      <div class="md:hidden">
        <div class="relative">
          <button class="text-xl hover:text-sky-400">🔔</button>
          <span class="absolute -top-1 -right-1 bg-red-500 text-xs text-white rounded-full px-1.5 py-0.5">3</span>
        </div>
      </div>

      <!-- Desktop: Center Links -->
      <div id="menu" class="hidden md:flex flex-1 justify-center space-x-6 text-sm font-medium">
        <a href="#" class="hover:text-sky-400">Home</a>
        <a href="#" class="hover:text-sky-400">Products</a>
        <a href="#" class="hover:text-sky-400">Services</a>
        <a href="#" class="hover:text-sky-400">Contact</a>
      </div>

      <!-- Desktop: Right - Bell + Account -->
      <div class="hidden md:flex items-center space-x-4">
        <!-- Notification Bell -->
        <div class="relative">
          <button class="text-xl hover:text-sky-400">🔔</button>
          <span class="absolute -top-1 -right-1 bg-red-500 text-xs text-white rounded-full px-1.5 py-0.5">3</span>
        </div>

        <!-- Account Dropdown -->
        <div class="relative">
          <button id="account-btn" class="bg-sky-400 text-slate-800 px-4 py-2 rounded hover:bg-sky-500 font-semibold">
            Account
          </button>
          <div id="account-menu" class="absolute right-0 mt-2 w-40 bg-white text-slate-800 rounded shadow-lg hidden z-10">
            <a href="#" class="block px-4 py-2 hover:bg-slate-100">Account Settings</a>
            <a href="#" class="block px-4 py-2 hover:bg-slate-100">Orders</a>
            <a href="#" class="block px-4 py-2 hover:bg-slate-100">Logout</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden mt-2 space-y-2 hidden">
      <a href="#" class="block px-2 py-1 hover:bg-slate-700 rounded">Home</a>
      <a href="#" class="block px-2 py-1 hover:bg-slate-700 rounded">Products</a>
      <a href="#" class="block px-2 py-1 hover:bg-slate-700 rounded">Services</a>
      <a href="#" class="block px-2 py-1 hover:bg-slate-700 rounded">Contact</a>
      <button class="w-full bg-sky-400 text-slate-800 px-4 py-2 rounded hover:bg-sky-500 font-semibold">
        Account
      </button>
    </div>
  </nav>

  <script>
    const menuBtn = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const accountBtn = document.getElementById('account-btn');
    const accountMenu = document.getElementById('account-menu');

    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });

    if (accountBtn) {
      accountBtn.addEventListener('click', () => {
        accountMenu.classList.toggle('hidden');
      });

      document.addEventListener('click', (e) => {
        if (!accountBtn.contains(e.target) && !accountMenu.contains(e.target)) {
          accountMenu.classList.add('hidden');
        }
      });
    }
  </script>

</body>
</html>