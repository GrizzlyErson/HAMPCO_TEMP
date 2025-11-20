

<?php include "header.php"?>



<div class="bg-gray-100 flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url('assets/image/banner.jpg');">


  <!-- Login Area -->
  <div class="mt-3 mb-3 w-full max-w-md bg-white p-8 rounded-lg shadow-lg">

<!-- Spinner overlay (initially hidden) -->
    <div id="spinner" class="spinner absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50 " style="display:none;">
      <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Customer</h2>
    
    <form id="FrmLogin_Customer" class="space-y-6">
      <!-- Email -->
      <div class="relative">
        <input type="email" id="email" name="email" placeholder=" " required
          class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
        <label for="email"
          class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 start-1">
          Email
        </label>
      </div>

      <!-- Password -->
      <div class="relative">
        <input type="password" id="password" name="password" placeholder=" " required
          class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
        <label for="password"
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
      <button type="submit" id="btnLogin"
        class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-75">
        Sign In
      </button>
    </form>


    <p class="mt-6 text-center text-sm text-gray-600">
      Don't have an account? <a href="signup_customer" class="text-indigo-600 hover:text-indigo-500">Sign up</a>
    </p>
  </div>
</div>

<?php include "footer.php";?>