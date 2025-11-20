<?php
require_once dirname(dirname(__DIR__)) . '/function/config.php';
include "component/header.php";

$userID = $_SESSION['customer_id'];
$product_id = $_GET['product_id'];

$product_info = $db->fetch_product_info($product_id); 
foreach ($product_info as $product):
    $prod_price = $product['prod_price']; 
endforeach;
?>

<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    <div class="text-gray-500 text-sm mb-4">
        <a href="#" class="hover:underline">Home</a> &gt;
        <a href="#" class="hover:underline"></a> 
        <a href="#" class="hover:underline"><?=$product['prod_name']?></a>
    </div>

    <!-- Main Product Container -->
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="md:flex gap-8">
            
            <!-- Images Section -->
            <div class="md:w-1/2 space-y-4">
                <img src="../upload/<?=$product['prod_image']?>" alt="Product Image" class="w-full rounded-lg">
              
            </div>

            <!-- Product Details Section -->
            <div class="md:w-1/2">
                <div class="mb-4">
                    <h2 class="text-2xl font-semibold"><?=$product['prod_name']?></h2>
                    <p class="text-red-500 text-xl font-semibold">PHP <?=number_format($product['prod_price'], 2);?></p>
                </div>
                <div class="mb-4">
                    <h3 class="text-gray-700 font-semibold mb-2">Description</h3>
                    <div class="flex space-x-2">
                        <p><?=$product['prod_description']?></p>
                    </div>
                </div>


                <!-- Cart and Wishlist Buttons -->
                <div class="flex gap-4 mt-6">
                    <button 
                        class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition btnAddToCart"
                        data-product_id="<?=$product_id?>"
                        data-user_id="<?=$userID?>"
                    >
                        Add to Cart
                    </button>
                    
                </div>
            </div>
        </div>
    </div>







</div>









<script>
    
$('.btnAddToCart').click(function() {
        let cart_user_id = $(this).data('user_id');
        let cart_prod_id = $(this).attr('data-product_id'); 

    console.log('test');
        $.ajax({
            type: "POST",
            url: "backend/end-points/controller.php",
            data: { 
                cart_user_id: cart_user_id,
                cart_prod_id: cart_prod_id,
                requestType: "AddToCart" 
            },
            dataType: 'json', 
            success: function(response) {
                console.log(response);
                
                if(response.status == "Added To Cart!") {
                    alertify.success('Item successfully added to the cart!');
                } else if(response.status == "Cart Updated!") {
                    alertify.success('Cart updated successfully!');
                } else {
                    alertify.error(response.status);
                }
            },
            error: function() {
                alertify.error('Error occurred during the request!');
            }
        });
    });
</script>

<?php include "component/footer.php"; ?>
