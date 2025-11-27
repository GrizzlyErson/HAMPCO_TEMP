<?php include "components/header.php";?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Manage Products</h1>
                        
                        <!-- Notification Bell Icon -->
                    <button class="relative focus:outline-none" title="Notifications">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <!-- Example: Notification dot -->
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full ring-2 ring-white bg-red-500"></span>
                    </button>
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

<!-- Update Product Modal -->
<div id="UpdateRawMaterialsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="display:none;">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 id="modalTitle" class="text-xl font-semibold mb-4">Update Product</h2>
        <form id="frmUpdateProduct">
            <input type="hidden" name="rm_id" id="rmid"> 

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" id="rm_name" name="rm_name" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Description</label>
                <input type="text" id="rm_description" name="rm_description" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Price</label>
                <input type="text" id="rm_price" name="rm_price" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-4">
                <label for="productCategory" class="block text-sm font-medium">Choose a Category</label>
                <select id="productCategory" name="rm_product_Category" class="w-full border rounded p-2" required>
                    <option value="" disabled selected>Select a Category</option>
                    <?php foreach ($db->fetch_all_category() as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="productImage" class="block text-sm font-medium">Product Image</label>
                <input type="file" id="productImage" name="rm_product_image" class="w-full border rounded p-2" accept="image/*">
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" id="closeUpdateProductModal" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" id="submitUpdateProduct" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
            </div>
      
        </form>
    </div>
</div>

<!-- Stock In Modal -->
<div id="stockInRmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display:none;">
    <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 sm:mx-0 max-h-[90vh] overflow-y-auto">
        <!-- Spinner -->
        <div id="spinner" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center rounded-2xl z-50" style="display:none;">
            <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <h2 id="modalTitle" class="text-2xl font-bold text-gray-800 text-center">Stock In</h2>
        <p id="stockInProductLabel" class="text-sm text-gray-500 text-center mb-6"></p>

        <form id="frmProdStockin" method="POST" class="space-y-4">
            <div>
                <label for="rm_quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input 
                    type="number" 
                    name="rm_quantity" 
                    id="rm_quantity" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    placeholder="Enter quantity"
                    required
                >
                <input hidden type="text" id="prod_id" name="prod_id">
            </div>

            <div class="flex justify-end pt-4 space-x-3">
                <button type="button" class="closeStockInRmModal px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button id="btnProdStockin" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Product Materials Modal -->
<div id="manageMaterialsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display:none;">
    <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Manage Materials for <span id="manageMaterialsProdName"></span></h2>
            <button type="button" id="closeManaageMaterialsModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        <input type="hidden" id="manageMaterialsProdId">
        
        <!-- Existing Materials List -->
        <div class="mb-4">
            <h3 class="font-semibold text-gray-700 mb-2">Current Materials:</h3>
            <div id="materialsList" class="space-y-2">
                <p class="text-gray-500">Loading...</p>
            </div>
        </div>
        
        <!-- Add New Material Form -->
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-700 mb-2">Add Material:</h3>
            <form id="frmAddProductMaterial" class="space-y-3">
                <div>
                    <label for="materialType" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select id="materialType" name="material_type" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        <option value="">-- Select Type --</option>
                        <option value="raw">Raw Material</option>
                        <option value="processed">Processed Material</option>
                    </select>
                </div>
                <div>
                    <label for="materialName" class="block text-sm font-medium text-gray-700 mb-1">Material Name</label>
                    <input type="text" id="materialName" name="material_name" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="e.g., Piña Cloth" required>
                </div>
                <div>
                    <label for="materialQty" class="block text-sm font-medium text-gray-700 mb-1">Qty per Unit</label>
                    <input type="number" id="materialQty" name="material_qty" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="e.g., 1.5" step="0.001" required>
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Add Material</button>
            </form>
        </div>
    </div>
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
        let selectedProductId = null;
        // Add Product button click
        $('#AddProduct').on('click', function(){
            $('#AddProductModal').fadeIn();
        });

        // Close modal button
        $('#closeAddProductModal').on('click', function(){
            $('#AddProductModal').fadeOut();
        });

        // Close modal when clicking outside
        $('#AddProductModal').on('click', function(e){
            if (e.target === this) {
                $('#AddProductModal').fadeOut();
            }
        });

        // Add Product form submission
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
                dataType: "json",
                success: function (response) {
                    if (response && response.status === 'success') {
                        alertify.success(response.message || 'Product added successfully');
                        $('#AddProductForm')[0].reset();
                        $('#AddProductModal').fadeOut();
                        setTimeout(function () {
                            location.reload();
                        }, 800);
                    } else {
                        alertify.error((response && response.message) ? response.message : 'Failed to add product');
                    }
                },
                error: function(xhr, status, err) {
                    console.error('AddProduct error', xhr.responseText, status, err);
                    alertify.error('Server error while adding product');
                }
            });
        });

        // Search functionality
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#productionTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Delete Product Button
        $(document).on('click', '.deleteRmBtn', function(e) {
            e.preventDefault();
            var prod_id = $(this).data('prod_id');
        
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'No, cancel!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "backend/end-points/controller.php",
                        type: 'POST',
                        data: { prod_id: prod_id, requestType: 'DeleteProduct' },
                        dataType: 'json', 
                        success: function(response) {
                            if (response && response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success').then(() => {
                                    location.reload(); 
                                });
                            } else {
                                Swal.fire('Error!', (response && response.message) ? response.message : 'Failed to delete product', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'There was a problem with the request.', 'error');
                        }
                    });
                }
            });
        });

        // Stock In Button
        $(document).on('click', '.stockInRmBtn', function () {
            selectedProductId = $(this).data('id');
            const prodName = $(this).data('prod_name') || '';
            $("#prod_id").val(selectedProductId);
            $("#stockInProductLabel").text(prodName ? `Updating stock for ${prodName}` : '');
            $('#stockInRmModal').fadeIn();
        });

        function closeStockInModal() {
            $('#stockInRmModal').fadeOut();
            $('#frmProdStockin')[0].reset();
            $('#stockInProductLabel').text('');
        }

        // Close Stock In Modal
        $(document).on('click', '.closeStockInRmModal', function () {
            closeStockInModal();
        });

        // Stock In Form Submit
        $("#frmProdStockin").submit(function (e) {
            e.preventDefault();
            const spinner = $('#spinner');
            spinner.show();
        
            var formData = new FormData(this); 
            formData.append('requestType', 'ProdStockin');
            $.ajax({
                type: "POST",
                url: "backend/end-points/controller.php",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json", 
                beforeSend: function () {
                    $("#btnProdStockin").prop("disabled", true).text("Processing...");
                },
                success: function (response) {
                    if (response.status === "success") {
                        alertify.success(response.message);
                        closeStockInModal();
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        alertify.error(response.message || 'Unable to process stock in.');
                    }
                },
                error: function () {
                    alertify.error('Server error while processing stock in.');
                },
                complete: function () {
                    $("#btnProdStockin").prop("disabled", false).text("Submit");
                    spinner.hide();
                }
            });
        });

        // Update Product Button
        $(document).on('click', '.updateRmBtn', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const price = $(this).data('price');
            const categoryId = $(this).data('category-id');

            $('#modalTitle').text('Update Product Details');
            $('#rmid').val(id);
            $('#rm_name').val(name);
            $('#rm_description').val(description);
            $('#rm_price').val(price);
            $('#productCategory').val(categoryId);

            $('#UpdateRawMaterialsModal').fadeIn();
        });

        // Close Update Modal
        $(document).on('click', '#closeUpdateProductModal', function () {
            $('#UpdateRawMaterialsModal').fadeOut();
        });

        // Update Product Form Submit
        $('#frmUpdateProduct').on('submit', function(e) {
            e.preventDefault();
            var category = $('#productCategory').val();
            if (category === null) {
                alertify.error("Please select a category.");
                return; 
            }
           
            var formData = new FormData(this);
            formData.append('requestType', 'UpdateProduct'); 

            $.ajax({
                type: "POST",
                url: "backend/end-points/controller.php",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    if(response && response.status === 'success'){
                        alertify.success(response.message || 'Product updated successfully');
                        $('#UpdateRawMaterialsModal').fadeOut();
                        $('#frmUpdateProduct')[0].reset();
                        setTimeout(function() {
                            location.reload();
                        }, 800);
                    } else {
                        alertify.error(response.message || 'Failed to update product');
                    }
                },
                error: function(xhr, status, error) {
                    alertify.error('Error: ' + error);
                }
            });
        });

        // Manage Materials Button
        $(document).on('click', '.manageMaterialsBtn', function() {
            var prodId = $(this).data('id');
            var prodName = $(this).data('prod_name');
            
            $('#manageMaterialsProdId').val(prodId);
            $('#manageMaterialsProdName').text(prodName);
            
            loadProductMaterials(prodId);
            $('#manageMaterialsModal').fadeIn();
        });

        // Close Materials Modal
        $(document).on('click', '#closeManaageMaterialsModal', function() {
            $('#manageMaterialsModal').fadeOut();
        });

        // Load Product Materials
        function loadProductMaterials(prodName) {
            $.ajax({
                type: "POST",
                url: "backend/end-points/controller.php",
                data: {
                    requestType: "GetProductMaterials",
                    product_name: prodName
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success' && response.materials) {
                        let html = '';
                        response.materials.forEach(function(mat) {
                            html += `
                                <div class="flex gap-2 items-center mb-2 p-2 bg-gray-100 rounded">
                                    <span class="flex-1">${mat.material_type} - ${mat.material_name} (${mat.material_qty} per unit)</span>
                                    <button type="button" class="removeMaterialBtn bg-red-500 text-white px-2 py-1 rounded text-sm" data-mat-id="${mat.id}">Remove</button>
                                </div>
                            `;
                        });
                        $('#materialsList').html(html);
                    } else {
                        $('#materialsList').html('<p class="text-gray-500">No materials assigned yet</p>');
                    }
                }
            });
        }

        // Add Material Form Submit
        $('#frmAddProductMaterial').submit(function(e) {
            e.preventDefault();
            
            var prodId = $('#manageMaterialsProdId').val();
            var matType = $('#materialType').val();
            var matName = $('#materialName').val();
            var matQty = $('#materialQty').val();
            
            if (!matType || !matName || !matQty) {
                alertify.error('Please fill in all fields');
                return;
            }
            
            $.ajax({
                type: "POST",
                url: "backend/end-points/controller.php",
                data: {
                    requestType: "AddProductMaterial",
                    product_id: prodId,
                    material_type: matType,
                    material_name: matName,
                    material_qty: matQty
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        alertify.success('Material added successfully');
                        document.getElementById('frmAddProductMaterial').reset();
                        loadProductMaterials($('#manageMaterialsProdName').text());
                    } else {
                        alertify.error(response.message || 'Failed to add material');
                    }
                },
                error: function() {
                    alertify.error('Server error');
                }
            });
        });

        // Remove Material Button
        $(document).on('click', '.removeMaterialBtn', function() {
            var matId = $(this).data('mat-id');
            if (confirm('Remove this material?')) {
                $.ajax({
                    type: "POST",
                    url: "backend/end-points/controller.php",
                    data: {
                        requestType: "RemoveProductMaterial",
                        material_id: matId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === 'success') {
                            alertify.success('Material removed');
                            loadProductMaterials($('#manageMaterialsProdName').text());
                        } else {
                            alertify.error(response.message || 'Failed to remove material');
                        }
                    }
                });
            }
        });

        // Actions dropdown behavior
        $(document).on('click', '.actionToggle', function(e) {
            e.stopPropagation();
            const menu = $(this).siblings('.actionMenu');
            $('.actionMenu').not(menu).addClass('hidden');
            menu.toggleClass('hidden');
        });

        $(document).on('click', '.actionMenu button', function() {
            $(this).closest('.actionMenu').addClass('hidden');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.actionToggle').length && !$(e.target).closest('.actionMenu').length) {
                $('.actionMenu').addClass('hidden');
            }
        });
    });
</script>




