<?php include "header.php"?>

<!-- Background Image -->
<div class="fixed inset-0 z-0">
  <img src="assets/image/banner.jpg" class="w-full h-full object-cover filter blur-sm" alt="background">
  <div class="absolute inset-0 bg-black opacity-50"></div>
</div>

<!-- Single Modal with Tabs -->
<div class="fixed inset-0 z-40 flex items-center justify-center">
  <div class="relative w-full max-w-md mx-4">
    <!-- Close button -->
    <button id="closeModal" class="absolute -right-2 -top-2 bg-white rounded-full p-1 text-gray-500 hover:text-gray-700 z-50 shadow-lg hover:shadow-xl transition-all duration-200">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>

    <!-- Login Container -->
    <div class="w-full bg-white rounded-lg shadow-lg overflow-hidden">
      <!-- Tabs -->
      <div class="flex border-b border-gray-200">
        <button id="customerTab" class="flex-1 py-4 px-6 text-center font-semibold text-indigo-600 bg-white border-b-2 border-indigo-600 focus:outline-none transition-all duration-200">
          Customer
        </button>
        <button id="memberTab" class="flex-1 py-4 px-6 text-center font-semibold text-gray-500 hover:text-gray-700 focus:outline-none transition-all duration-200">
          Member
        </button>
      </div>

      <!-- Login Forms Container -->
      <div class="relative">
        <!-- Customer Form -->
        <div id="customerFormContainer" class="p-8">
          <div id="spinner-customer" class="spinner absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50" style="display:none;">
            <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
          </div>

          <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Customer Login</h2>
      
          <form id="FrmLogin_Customer" class="space-y-6">
            <!-- Email -->
            <div class="relative">
              <input type="email" id="customer_email" name="email" placeholder=" " required
                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
              <label for="customer_email"
                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 start-1">
                Email
              </label>
            </div>

            <!-- Password -->
            <div class="relative">
              <input type="password" id="customer_password" name="password" placeholder=" " required
                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
              <label for="customer_password"
                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 start-1">
                Password
              </label>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
              <label class="flex items-center">
                <input type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
              </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="btnLoginCustomer"
              class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-75">
              Sign In
            </button>
          </form>

          <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account? <a href="signup_customer" class="text-indigo-600 hover:text-indigo-500">Sign up</a>
          </p>
        </div>

        <!-- Member Form Container -->
        <div id="memberFormContainer" class="p-8 hidden opacity-0">
          <div id="spinner-member" class="spinner absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50" style="display:none;">
            <div class="w-10 h-10 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
          </div>

          <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Member Login</h2>
      
          <form id="FrmLogin_Member" class="space-y-6">
            <!-- Member ID -->
            <div class="relative">
              <input type="text" id="id_number" name="id_number" placeholder=" " required
                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-green-500 peer" />
              <label for="id_number"
                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-green-600 start-1">
                Member ID
              </label>
            </div>

            <!-- Password -->
            <div class="relative">
              <input type="password" id="member_password" name="password" placeholder=" " required
                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-green-500 peer" />
              <label for="member_password"
                class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-green-600 start-1">
                Password
              </label>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
              <label class="flex items-center">
                <input type="checkbox" class="h-4 w-4 text-green-600 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
              </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="btnLoginMember"
              class="w-full py-2 px-4 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-75 transition-colors duration-200">
              Sign In
            </button>
          </form>

          <p class="mt-6 text-center text-sm text-gray-600">
            Are you a new member? <a href="signup_member" class="text-green-600 hover:text-green-500">Sign up</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Elements
  const modal = document.querySelector('.fixed');
  const closeModal = document.getElementById('closeModal');
  const customerTab = document.getElementById('customerTab');
  const memberTab = document.getElementById('memberTab');
  const customerForm = document.getElementById('customerFormContainer');
  const memberForm = document.getElementById('memberFormContainer');

  // Function to switch tabs with animation
  function switchTab(activeTab, inactiveTab, showForm, hideForm) {
    // Don't switch if the target form is already visible
    if (!showForm.classList.contains('hidden')) return;

    // Update tab styles for member tab
    if (activeTab === memberTab) {
      activeTab.classList.remove('text-gray-500');
      activeTab.classList.add('text-green-600', 'border-b-2', 'border-green-600', 'bg-white');
      inactiveTab.classList.remove('text-indigo-600', 'border-b-2', 'border-indigo-600', 'bg-white');
    } else {
      // Update tab styles for customer tab
      activeTab.classList.remove('text-gray-500');
      activeTab.classList.add('text-indigo-600', 'border-b-2', 'border-indigo-600', 'bg-white');
      inactiveTab.classList.remove('text-green-600', 'border-b-2', 'border-green-600', 'bg-white');
    }
    inactiveTab.classList.add('text-gray-500');

    // Hide current form
    hideForm.classList.add('opacity-0');
    setTimeout(() => {
      hideForm.classList.add('hidden');
      // Show new form
      showForm.classList.remove('hidden');
      requestAnimationFrame(() => {
        showForm.classList.remove('opacity-0');
      });
    }, 200);
  }

  // Close modal
  closeModal.addEventListener('click', (e) => {
    e.preventDefault();
    modal.classList.add('opacity-0');
    setTimeout(() => {
      window.location.href = 'index.php'; // Redirect to home page
    }, 200);
  });

  // Customer tab click
  customerTab.addEventListener('click', () => {
    switchTab(customerTab, memberTab, customerForm, memberForm);
  });

  // Member tab click
  memberTab.addEventListener('click', () => {
    switchTab(memberTab, customerTab, memberForm, customerForm);
  });

  // Add transition classes for animation
  customerForm.classList.add('transition-opacity', 'duration-200', 'ease-in-out');
  memberForm.classList.add('transition-opacity', 'duration-200', 'ease-in-out', 'opacity-0');
  modal.classList.add('transition-opacity', 'duration-200', 'ease-in-out');
});
</script>

<?php include "footer.php";?>