<?php 
$fetch_all_materials = $db->fetch_all_product();

if ($fetch_all_materials->num_rows > 0) {
    while ($row = $fetch_all_materials->fetch_assoc()) {
?>
   <tr class="border-b border-gray-200 hover:bg-gray-50">
    <td class="py-3 px-6 text-left">
        <?php 
        $image_src = 'https://via.placeholder.com/64?text=No+Image';
        if (!empty($row['prod_image'])) {
            // Path is relative to where the page is loaded from (admin/products.php)
            $image_src = '../upload/' . htmlspecialchars($row['prod_image']);
        }
        ?>
        <img src="<?php echo $image_src; ?>" 
             alt="Product Image" 
             class="w-16 h-16 object-cover rounded" 
             onerror="this.src='https://via.placeholder.com/64?text=No+Image'">
    </td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_id']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_name']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_stocks']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_price']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['category_name']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_description']); ?></td>
    <td class="py-3 px-6">
        <!-- Dropdown Menu -->
        <div class="relative group">
            <button class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded text-xs flex items-center shadow">
                <span class="material-icons text-sm mr-1">more_vert</span> Actions
            </button>
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden group-hover:block z-10">
                <!-- Update Option -->
                <button class="updateRmBtn w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center text-gray-800 text-sm border-b"
                    data-id="<?php echo htmlspecialchars($row['prod_id']); ?>" 
                    data-name="<?php echo htmlspecialchars($row['prod_name']); ?>"
                    data-description="<?php echo htmlspecialchars($row['prod_description']); ?>"
                    data-price="<?php echo htmlspecialchars($row['prod_price']); ?>"
                    data-category-id="<?php echo htmlspecialchars($row['prod_category_id']); ?>">
                    <span class="material-icons text-sm mr-2">edit</span> Update
                </button>
                
                <!-- Stock In Option -->
                <button class="stockInRmBtn w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center text-gray-800 text-sm border-b"
                    data-id="<?php echo htmlspecialchars($row['prod_id']); ?>" 
                    data-prod_name="<?php echo htmlspecialchars($row['prod_name']); ?>">
                    <span class="material-icons text-sm mr-2">arrow_upward</span> Stock In
                </button>

                <!-- Manage Materials Option -->
                <button class="manageMaterialsBtn w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center text-gray-800 text-sm border-b"
                    data-id="<?php echo htmlspecialchars($row['prod_id']); ?>" 
                    data-prod_name="<?php echo htmlspecialchars($row['prod_name']); ?>">
                    <span class="material-icons text-sm mr-2">inventory_2</span> Materials
                </button>

                <!-- Delete Option -->
                <button class="deleteRmBtn w-full text-left px-4 py-2 hover:bg-red-50 flex items-center text-red-600 text-sm"
                    data-prod_id="<?php echo htmlspecialchars($row['prod_id']); ?>" 
                    data-prod_name="<?php echo htmlspecialchars($row['prod_name']); ?>">
                    <span class="material-icons text-sm mr-2">delete</span> Remove
                </button>
            </div>
        </div>
    </td>
</tr>

<?php
}
} else {
?>
    <tr>
        <td colspan="8" class="py-3 px-6 text-center">No Product found.</td>
    </tr>
<?php
}
?>



















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






<!-- Modal Structure -->
<div id="stockInRmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 " style="display:none;">
    <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 sm:mx-0 max-h-[90vh] overflow-y-auto">
        <!-- Spinner -->
        <div id="spinner" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center rounded-2xl z-50 " style="display:none;">
            <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <h2 id="modalTitle" class="text-2xl font-bold text-gray-800 mb-6 text-center">Stock In</h2>

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



<script>
$(document).ready(function () {

// $('.togglerDeleteProduct').click(function (e) {
    $(document).on('click', '.deleteRmBtn', function(e) {
        e.preventDefault();
        var prod_id = $(this).data('prod_id');
        console.log(prod_id);
    
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
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            ).then(() => {
                                 location.reload(); 
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                (response && response.message) ? response.message : 'Failed to delete product', 
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'There was a problem with the request.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    

    $(document).on('click', '.stockInRmBtn', function () {
        selectedId = $(this).data('id');
        console.log(selectedId);
        
        $("#prod_id").val(selectedId);
        $('#stockInRmModal').fadeIn();
    });


    $(document).on('click', '.closeStockInRmModal', function () {
        selectedId = $(this).data('id');
        $('#stockInRmModal').fadeOut();
    });

    


    $("#frmProdStockin").submit(function (e) {
            e.preventDefault();

            $('.spinner').show();
            $('#btnProdStockin').prop('disabled', true);
        
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
                    console.log(response); 
                    
                    if (response.status ==="success") {
                        alertify.success(response.message);
                        $('#frmProdStockin')[0].reset();
                        $('#rm_quantity').val('');
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        $('.spinner').hide();
                        $('#btnProdStockin').prop('disabled', false);
                        alertify.error(response.message);
                    }
                },
                complete: function () {
                    $("#btnProdStockin").prop("disabled", false).text("Submit");
                    $('.spinner').hide();
                }
            });
        });





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

    $(document).on('click', '#closeUpdateProductModal', function () {
        $('#UpdateRawMaterialsModal').fadeOut();
    });







  $('#frmUpdateProduct').on('submit', function(e) {
      e.preventDefault();
      var category = $('#productCategory').val();
      if (category === null) {
          alertify.error("Please select a category.");
          return; 
      }
       
      var formData = new FormData(this);
      formData.append('requestType', 'UpdateProduct'); 

      // Perform the AJAX request
      $.ajax({
          type: "POST",
          url: "backend/end-points/controller.php",
          data: formData,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(response) {
            console.log(response)
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










    $(document).on('click', '#submitDeleteRawMaterials', function () {
        $.ajax({
            type: "POST",
            url: "backend/end-points/controller.php",
            data: {
                requestType: "deleteRawMaterial",
                rmid: selectedId
            },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    alertify.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alertify.error(response.message);
                }
            }
        });

        $('#UpdateRawMaterialsModal').fadeOut();
    });

    // Manage Materials
    $(document).on('click', '.manageMaterialsBtn', function() {
        var prodId = $(this).data('id');
        var prodName = $(this).data('prod_name');
        
        $('#manageMaterialsProdId').val(prodId);
        $('#manageMaterialsProdName').text(prodName);
        
        // Load existing materials for this product
        loadProductMaterials(prodId);
        $('#manageMaterialsModal').fadeIn();
    });

    $(document).on('click', '#closeManaageMaterialsModal', function() {
        $('#manageMaterialsModal').fadeOut();
    });

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
});
</script>