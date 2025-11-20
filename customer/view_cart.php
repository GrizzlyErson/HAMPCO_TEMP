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
    <button class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition duration-300 font-semibold shadow-md">
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
});
</script>

<?php include "component/footer.php"; ?>
