<?php
include "component/header.php";
$userID = $_SESSION['customer_id'];
?>

<!-- Cart Container -->
<div class="max-w-4xl mx-auto px-4 py-8">
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Your Shopping Cart</h2>

  <!-- Cart items wrapper -->
  <div class="cart-items space-y-6">
    <!-- Cart items will be dynamically loaded here -->
  </div>

  <!-- Summary Section -->
  <div class="mt-8 border-t pt-6 flex flex-col sm:flex-row sm:justify-between sm:items-center">
    <div class="mb-4 sm:mb-0">
      <span class="text-lg font-medium text-gray-700">Total:</span>
      <span class="text-2xl font-extrabold text-gray-900 total-price ml-2">₱ 0.00</span>
    </div>
      <button id="checkoutBtn" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition duration-300 font-semibold shadow-md">
        Proceed to Checkout
      </button>
  </div>
</div>

<script>

// Increase quantity
$('.cart-items').on('click', 'button:contains("+")', function () {
  const cartItem = $(this).closest('[data-cart-id]');
  const cartId = cartItem.data('cart-id');

  // Get current qty and stock
  const qtyInput = cartItem.find('input[type="text"]');
  const currentQty = parseInt(qtyInput.val());
  const stockLimit = parseInt(qtyInput.data('stock'));

  if (currentQty >= stockLimit) {
    alertify.error('You have reached the maximum available stock for this item.');
    return; // stop incrementing
  }

  $.ajax({
    url: 'backend/end-points/controller.php',
    type: 'POST',
    data: { cart_id: cartId, requestType: 'IncreaseQty' },
    success: function(response) {
      loadCart();
    },
    error: function() {
      alert('Failed to update quantity. Please try again.');
    }
  });
});


// Decrease quantity
$('.cart-items').on('click', 'button:contains("−")', function () {
  const cartItem = $(this).closest('[data-cart-id]');
  const cartId = cartItem.data('cart-id');

  $.ajax({
    url: 'backend/end-points/controller.php',
    type: 'POST',
    data: { cart_id: cartId, requestType: 'DecreaseQty' },
    success: function(response) {
      loadCart();
    },
    error: function() {
      alert('Failed to update quantity. Please try again.');
    }
  });
});

function loadCart() {
  $.ajax({
    url: "backend/end-points/get_cart.php",
    type: 'GET',
    dataType: 'json',
    success: function (data) {
      const cartContainer = $('.cart-items');
      let total = 0;

      cartContainer.empty();

      if(data.length === 0){
        cartContainer.append(`
          <p class="text-center text-gray-500">Your cart is currently empty.</p>
        `);
        $('.total-price').text('₱ 0.00');
        return;
      }

      data.forEach(item => {
        const qty = parseInt(item.cart_Qty);
        const price = parseFloat(item.prod_price);
        const itemTotal = qty * price;
        total += itemTotal;

        cartContainer.append(`
          <div data-cart-id="${item.cart_id}" class="flex flex-col sm:flex-row sm:items-center justify-between border rounded-lg p-4 shadow-sm hover:shadow-md transition duration-200 bg-white">
            <div class="flex items-center gap-4 flex-1">
              <img src="../upload/${item.prod_image}" alt="${item.prod_name}" class="w-24 h-24 rounded-md object-cover border" />
              <div>
                <h3 class="text-lg font-semibold text-gray-800">${item.prod_name}</h3>
                <p class="text-sm text-gray-500 mt-1">${item.prod_description || ''}</p>
              </div>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center gap-6">
              <div class="flex items-center border rounded-md overflow-hidden">
                <button class="px-3 py-1 text-gray-600 hover:bg-gray-100 transition duration-150">−</button>
                <input 
                type="text" 
                value="${qty}" 
                data-stock="${item.prod_stocks}" 
                class="w-12 text-center border-x outline-none bg-gray-50" 
                readonly 
                />

                <button class="px-3 py-1 text-gray-600 hover:bg-gray-100 transition duration-150">+</button>
              </div>
              <div class="text-right min-w-[100px]">
                <p class="text-lg font-bold text-gray-700">₱ ${itemTotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}</p>
              </div>
              <button class="remove-item-btn text-red-600 hover:text-red-800 ml-4 font-semibold" title="Remove item">
                &times;
              </button>
            </div>
          </div>
        `);
      });

      $('.total-price').text(`₱ ${total.toLocaleString(undefined, { minimumFractionDigits: 2 })}`);
    },
    error: function (xhr, status, error) {
      console.error('AJAX error:', error);
      $('.cart-items').html('<p class="text-red-600 text-center">Failed to load cart items. Please try again.</p>');
    }
  });
}

$(document).ready(function () {
  loadCart();

  // Delegate event to dynamically added remove buttons
  $('.cart-items').on('click', '.remove-item-btn', function () {
    const cartItem = $(this).closest('[data-cart-id]');
    const cartId = cartItem.data('cart-id');

    if (!confirm('Are you sure you want to remove this item from your cart?')) {
      return;
    }

    $.ajax({
      url: 'backend/end-points/controller.php',
      type: 'POST',
      data: { cart_id: cartId,requestType:'RemoveCart' },
      success: function(response) {
        loadCart(); 
      },
      error: function(xhr, status, error) {
        alert('Failed to remove item. Please try again.');
      }
    });
  });

  // ======== CHECKOUT MODAL SETUP ========
  function createAndShowCheckoutModal() {
    // Check if modal already exists
    if ($('#checkoutModal').length > 0) {
      showCheckoutModal();
      return;
    }

    // Create modal HTML
    const modalHTML = `
    <div id="checkoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
      <div style="background: white; border-radius: 10px; width: 90%; max-width: 600px; max-height: 85vh; overflow-y: auto; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px;">
          <h2 style="margin: 0; font-size: 24px; font-weight: bold; color: #111827;">Confirm Your Order</h2>
          <button type="button" id="closeCheckoutModal" style="background: none; border: none; font-size: 28px; color: #6b7280; cursor: pointer; padding: 0; width: 30px; height: 30px;">×</button>
        </div>

        <form id="checkoutForm" style="margin: 0;">
          
          <!-- Customer Info Section -->
          <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600; color: #374151;">Customer Information</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
              <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px;">Full Name *</label>
                <input type="text" id="modalName" name="name" placeholder="John Doe" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: Arial;" required>
              </div>
              <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px;">Contact Number *</label>
                <input type="text" id="modalContact" name="contact" placeholder="09XXXXXXXXX" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: Arial;" required>
              </div>
            </div>
            
            <div>
              <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px;">Delivery Address *</label>
              <textarea id="modalAddress" name="address" placeholder="Enter your complete address" rows="3" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: Arial; resize: vertical;" required></textarea>
            </div>
          </div>

          <!-- Payment Section -->
          <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600; color: #374151;">Payment Information</h3>
            
            <div style="margin-bottom: 15px;">
              <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px;">Payment Method *</label>
              <select id="modalPaymentMethod" name="payment_method" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: Arial;" required>
                <option value="">-- Select Payment Method --</option>
                <option value="COD">Cash on Delivery (COD)</option>
                <option value="GCash">GCash</option>
              </select>
            </div>

            <div id="paymentProofWrapper" style="display: none; margin-bottom: 15px;">
              <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px;">Payment Proof *</label>
              <input type="file" id="modalPaymentProof" name="payment_proof" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; box-sizing: border-box; font-family: Arial;">
              <small style="display: block; color: #6b7280; margin-top: 5px; font-size: 12px;">Upload a screenshot of your GCash payment</small>
            </div>
          </div>

          <!-- Notes Section -->
          <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600; color: #374151;">Additional Notes</h3>
            
            <div>
              <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px;">Order Notes (Optional)</label>
              <textarea id="modalNotes" name="notes" placeholder="Add any special instructions or details about your order..." rows="3" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: Arial; resize: vertical;"></textarea>
            </div>
          </div>

          <!-- Order Summary Section -->
          <div style="background-color: #dbeafe; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #3b82f6;">
            <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #374151;">Order Summary</h3>
            <div style="display: flex; justify-content: space-between; font-size: 16px; color: #374151;">
              <span>Total Amount:</span>
              <span style="font-weight: bold; color: #1d4ed8; font-size: 18px;" id="modalTotalAmount">₱ 0.00</span>
            </div>
            <input type="hidden" id="modalTotalValue" name="totalAmount" value="0">
          </div>

          <!-- Buttons Section -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 25px;">
            <button type="button" id="cancelCheckoutBtn" style="padding: 12px; background-color: #d1d5db; color: #111827; border: none; border-radius: 6px; font-weight: 500; font-size: 14px; cursor: pointer; transition: background-color 0.2s;">Cancel</button>
            <button type="submit" id="completeOrderBtn" style="padding: 12px; background-color: #10b981; color: white; border: none; border-radius: 6px; font-weight: 500; font-size: 14px; cursor: pointer; transition: background-color 0.2s;">Complete Order</button>
          </div>
        </form>
      </div>
    </div>
    `;

    $('body').append(modalHTML);

    // Bind event handlers
    bindCheckoutModalEvents();
  }

  function showCheckoutModal() {
    const totalPrice = $('.total-price').text().replace('₱ ', '').trim();
    $('#modalTotalValue').val(totalPrice);
    $('#modalTotalAmount').text($('.total-price').text());
    
    const modal = $('#checkoutModal');
    modal.css('display', 'flex');
    $('#modalName').focus();
  }

  function hideCheckoutModal() {
    $('#checkoutModal').css('display', 'none');
  }

  function bindCheckoutModalEvents() {
    // Close modal
    $(document).on('click', '#closeCheckoutModal, #cancelCheckoutBtn', function(e) {
      e.preventDefault();
      hideCheckoutModal();
    });

    // Payment method change - toggle proof field
    $(document).on('change', '#modalPaymentMethod', function() {
      if ($(this).val() === 'GCash') {
        $('#paymentProofWrapper').css('display', 'block');
      } else {
        $('#paymentProofWrapper').css('display', 'none');
        $('#modalPaymentProof').val('');
      }
    });

    // Submit form - checkout
    $(document).on('submit', '#checkoutForm', function(e) {
      e.preventDefault();
      
      // Validate all required fields before submission
      const name = $('#modalName').val().trim();
      const contact = $('#modalContact').val().trim();
      const address = $('#modalAddress').val().trim();
      const paymentMethod = $('#modalPaymentMethod').val();
      const paymentProof = $('#modalPaymentProof').val();

      if (!name) {
        alertify.error('Please enter your full name');
        $('#modalName').focus();
        return;
      }
      if (!contact) {
        alertify.error('Please enter your contact number');
        $('#modalContact').focus();
        return;
      }
      if (!address) {
        alertify.error('Please enter your delivery address');
        $('#modalAddress').focus();
        return;
      }
      if (!paymentMethod) {
        alertify.error('Please select a payment method');
        $('#modalPaymentMethod').focus();
        return;
      }
      if (paymentMethod === 'GCash' && !paymentProof) {
        alertify.error('Please upload payment proof for GCash');
        $('#modalPaymentProof').focus();
        return;
      }

      $('#completeOrderBtn').prop('disabled', true).text('Processing...');

      const formData = new FormData();
      formData.append('requestType', 'Checkout');
      formData.append('user_id', <?php echo intval($userID); ?>);
      formData.append('full_name', name);
      formData.append('contact_number', contact);
      formData.append('delivery_address', address);
      formData.append('payment_method', paymentMethod);
      formData.append('notes', $('#modalNotes').val());
      formData.append('totalAmount', $('#modalTotalValue').val());
      
      if (paymentProof) {
        const fileInput = $('#modalPaymentProof')[0];
        if (fileInput.files.length > 0) {
          formData.append('payment_proof', fileInput.files[0]);
        }
      }

      $.ajax({
        url: 'backend/end-points/controller.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
          if (res.status === 'success') {
            alertify.success(res.message || 'Order placed successfully!');
            hideCheckoutModal();
            $('#checkoutForm')[0].reset();
            loadCart();
          } else {
            alertify.error(res.message || 'Checkout failed. Please try again.');
          }
        },
        error: function(xhr, status, err) {
          console.error('Checkout error:', err);
          alertify.error('Checkout request failed. Please try again.');
        },
        complete: function() {
          $('#completeOrderBtn').prop('disabled', false).text('Complete Order');
        }
      });
    });
  }

  // Open checkout modal when button clicked
  $('#checkoutBtn').on('click', function() {
    createAndShowCheckoutModal();
  });
});
</script>

<?php include "component/footer.php"; ?>
