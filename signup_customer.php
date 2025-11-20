<?php include "header.php" ?>

<div class="bg-gray-100 flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url('assets/image/banner.jpg');">
  
  <!-- Registration Area -->
  <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg relative">
    
     <!-- Spinner overlay (initially hidden) -->
    <div id="spinner" class="spinner absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50 " style="display:none;">
      <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
    </div>
   

    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Signup</h2>
    
    <form id="FrmRegister_Customer" class="space-y-6">
      <!-- Full Name -->
      <div class="relative">
        <input type="text" id="fullname" name="fullname" placeholder=" "
          class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
        <label for="fullname"
          class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 start-1">
          Full Name
        </label>
      </div>

      <!-- Email -->
      <div class="relative">
        <input type="email" id="email" name="email" placeholder=" "
          class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
        <label for="email"
          class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 start-1">
          Email
        </label>
      </div>

      <!-- Phone Number -->
      <div class="relative">
        <input type="tel" id="phone" name="phone" placeholder=" "
          class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
        <label for="phone"
          class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 start-1">
          Phone Number
        </label>
      </div>

      <!-- Password -->
      <div class="relative">
        <input type="password" id="password" name="password" placeholder=" "
          class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
        <label for="password"
          class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 start-1">
          Password
        </label>
      </div>

      <!-- Confirm Password -->
      <div class="relative">
        <input type="password" id="confirm-password" name="confirm-password" placeholder=" "
          class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-800 bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-500 peer" />
        <label for="confirm-password"
          class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 start-1">
          Confirm Password
        </label>
      </div>

      <!-- Submit Button -->
      <button type="submit"
        class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-75">
        Create Account
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
      Already have an account? <a href="login.php" class="text-indigo-600 hover:text-indigo-500" id="btnRegister">Log in</a>
    </p>
  </div>

</div>

<?php include "footer.php";?>