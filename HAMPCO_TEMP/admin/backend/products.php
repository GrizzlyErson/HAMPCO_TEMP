<?php include "components/header.php";?>

<!-- Top bar with user profile -->
<div class="flex justify-between items-center bg-white p-4 mb-6 rounded-md shadow-md">
    <h2 class="text-lg font-semibold text-gray-700">Manage Product</h2>
    <div class="w-10 h-10 ">
    </div>
</div>

<button id="AddProduct" class="mb-3 bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition flex items-center gap-2">
        <span class="material-icons">add</span>
        Add Products
    </button>
<!-- Table of members -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
    <!-- Search bar -->
    <div class="mb-4">
    <input type="text" id="searchInput" placeholder="Search ..." class="w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
</div>

    <table class="min-w-full table-auto" id="productionTable">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Image</th>
                <th class="py-3 px-6 text-left">Product ID</th>
                <th class="py-3 px-6 text-left">Product Name</th>
                <th class="py-3 px-6 text-left">Stocks</th>
                <th class="py-3 px-6 text-left">Price</th>
                <th class="py-3 px-6 text-left">Category</th>
                <th class="py-3 px-6 text-left">Description</th>
                <th class="py-3 px-6 text-left"></th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
           <?php
           
           include "backend/end-points/list_products.php";
           
           ?>
        </tbody>
    </table>
</div>

<?php include "components/footer.php";?>

<script src="assets/js/app.js"></script>






<!-- Modal -->
<div id="AddProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center " style="display:none;">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4">Add Product</h2>
        <form id="AddProductForm">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="rm_name" class="w-full border rounded p-2" placeholder="" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Description</label>
                <input type="text" name="rm_description" id="rm_description" class="w-full border rounded p-2" placeholder="" >
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Price</label>
                <input type="text" name="rm_price" id="rm_price" class="w-full border rounded p-2" placeholder="" required>
            </div>


             <div class="mb-4">
                    <label for="productCategory" class="block text-sm font-medium text-gray-700">Choose a Category</label>
                    <select id="productCategory" name="rm_product_Category" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="" disabled selected>Select a Category</option>
                        <?php $fetch_all_category = $db->fetch_all_category();
                            if ($fetch_all_category): 
                                foreach ($fetch_all_category as $category): ?>
                                    <option value="<?=$category['category_id']?>"><?=$category['category_name']?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No record found.</option>
                            <?php endif; ?>
                    </select>
                </div>
           
           
            <div class="mb-4">
                <label for="productImage" class="block text-gray-700">Product Image</label>
                <input type="file" id="productImage" name="rm_product_image" class="w-full p-2 border border-gray-300 rounded-md" accept="image/*" required>
            </div>


            <div class="flex justify-end gap-2">
                <button type="button" id="closeAddProductModal" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" id="submitAddRawMaterials" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add</button>
            </div>
        </form>
    </div>
</div>












<script>
    $(document).ready(function(){
        $('#AddProduct').on('click', function(){
            $('#AddProductModal').fadeIn();
        });

        $('#closeAddProductModal').on('click', function(){
            $('#AddProductModal').fadeOut();
        });

        $('#AddProductForm').on('submit', function(e){
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('requestType', 'AddProduct');

            $.ajax({
                type: "POST",
                url: "backend/end-points/controller.php",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "text",  // expecting plain text response
                success: function (response) {
                    if (response.trim() === 'success') {  // check the string directly
                        alertify.success('Product added successfully');  
                        setTimeout(function () {
                            location.reload(); 
                        }, 1000);
                    } else {
                        alertify.error('Failed to add product');
                        $('.spinner').hide();
                    }
                }
            });
        });









        $(document).ready(function() {
                $('#frmAddProduct').on('submit', function(e) {
                    e.preventDefault();
                    var category = $('#productCategory').val();
                    if (category === null) {
                        alert("Please select a category.");
                        return; 
                    }
                    var productImage = $('#productImage').val();
                    if (productImage === "") {
                        alert("Please upload an image.");
                        return; 
                    }
                    $('.spinner').show();
                    $('#frmAddProduct').prop('disabled', true);
                    var formData = new FormData(this);
                    formData.append('requestType', 'AddProduct'); 
                    $.ajax({
                        type: "POST",
                        url: "backend/end-points/controller.php",
                        data: formData,
                        contentType: false,
                        processData: false, 
                        success: function(response) {
                            console.log(response)
                            if(response==200){
                                $('#AddproductModal').hide();
                                $('.spinner').hide();
                                $('#frmAddProduct').prop('disabled', false);
                                location.reload();
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Error: ' + error);
                        }
                    });
                });
            });





    });
</script>








<script>
// jQuery search functionality
$(document).ready(function() {
    $("#searchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#productionTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>
