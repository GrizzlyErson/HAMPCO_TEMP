<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">









<!-- Modal Structure -->
<div id="checkoutModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50" style="display:none;">
  <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
    <h2 class="text-xl font-semibold mb-4">Confirm Checkout</h2>
    <p class="mb-6">Are you sure you want to proceed with checkout?</p>

    <!-- Payment Method Section -->
    <div class="mb-6">
    <label for="paymentMethod" class="block text-sm font-medium text-gray-700">Select Payment Method</label>
    <select id="paymentMethod" name="paymentMethod" class="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="cod" data-ename="cod">Cash on Delivery</option>
        <?php 
            $getAllEwallet = $db->getAllEwallet(); 
            foreach ($getAllEwallet as $mop):
                echo '<option value="'.$mop['e_id'].'" data-img="'.$mop['e_img'].'" data-ename="'.$mop['e_wallet_name'].'">';
                echo $mop['e_wallet_name'];
                echo '</option>';
            endforeach; 
        ?>
    </select>
</div>


    <!-- Payment Method Instructions -->
    <div id="paymentDetails" class="hidden">
    <!-- QR Code Image (for Gcash and Bank Transfer) -->
    <div id="qrCode" class="mb-4 hidden">
        <label class="block text-sm font-medium text-gray-700">Scan QR Code for Payment</label>
        <img src="" alt="QR Code" class="w-40 h-40 mt-2 mx-auto" />
    </div>

    <!-- Proof of Payment Upload Section -->
    <div id="proofOfPaymentSection" class="mt-4">
        <label for="proofOfPayment" class="block text-sm font-medium text-gray-700">Upload Proof of Payment</label>
        <input type="file" id="proofOfPayment" name="proofOfPayment" class="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" accept="image/*" />

    </div>
    </div>

    <!-- Modal Footer -->
    <div class="flex justify-end mt-6">
      <button class="closeModal mr-4 px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
      <button id="btnConfirmCheckout" class=" px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Confirm</button>
    </div>
    <div class="loadingSpinner" style="display:none;">
        <div class=" absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
          <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
     </div>
     
  </div>
</div>




</body>
</html>