<?php 
$fetch_all_materials = $db->fetch_all_product();

if ($fetch_all_materials->num_rows > 0) {
    while ($row = $fetch_all_materials->fetch_assoc()) {
?>
   <tr class="border-b border-gray-200 hover:bg-gray-50">
    <td class="py-3 px-6 text-left">
        <img src="../upload/<?=$row['prod_image']?>" class="w-16 h-16 object-cover rounded">
    </td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_id']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_name']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_stocks']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_price']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['category_name']); ?></td>
    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['prod_description']); ?></td>
    <td class="py-3 px-6 flex space-x-2">
        <!-- Update Button -->
       <button class="updateRmBtn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow"
            data-id="<?php echo htmlspecialchars($row['prod_id']); ?>" 
            data-name="<?php echo htmlspecialchars($row['prod_name']); ?>"
            data-description="<?php echo htmlspecialchars($row['prod_description']); ?>"
            data-price="<?php echo htmlspecialchars($row['prod_price']); ?>"
            data-category-id="<?php echo htmlspecialchars($row['prod_category_id']); ?>">
            <span class="material-icons text-sm mr-1">edit</span> Update
        </button>


        <!-- Delete Button -->
        <button class="deleteRmBtn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow"
            data-prod_id="<?php echo htmlspecialchars($row['prod_id']); ?>" 
            data-prod_name="<?php echo htmlspecialchars($row['prod_name']); ?>">
            <span class="material-icons text-sm mr-1">delete</span> Remove
        </button>

        <!-- Stock In Button -->
        <button class="stockInRmBtn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow"
            data-id="<?php echo htmlspecialchars($row['prod_id']); ?>" 
            data-prod_name="<?php echo htmlspecialchars($row['prod_name']); ?>">
            <span class="material-icons text-sm mr-1">arrow_upward</span> Stock In
        </button>
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
                        if (response.status === 200) {
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
                                response.message, 
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

    

    $('.stockInRmBtn').click(function () {
        selectedId = $(this).data('id');
        console.log(selectedId);
        
        $("#prod_id").val(selectedId);
        $('#stockInRmModal').fadeIn();
    });


     $('.closeStockInRmModal').click(function () {
        selectedId = $(this).data('id');
        $('#stockInRmModal').fadeOut();
    });

    


    $("#frmProdStockin").submit(function (e) {
            e.preventDefault();

            $('.spinner').show();
            $('#btnRawStockin').prop('disabled', true);
        
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
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        $('.spinner').hide();
                        $('#btnProdStockin').prop('disabled', false);
                        alertify.error(response.message);
                    }
                },
                complete: function () {
                    $("#btnProdStockin").prop("disabled", false).text("Submit");
                }
            });
        });





   $('.updateRmBtn').click(function () {
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

    $('#closeUpdateProductModal').click(function () {
        $('#UpdateRawMaterialsModal').fadeOut();
    });







  $(document).ready(function() {
      $('#frmUpdateProduct').on('submit', function(e) {
          e.preventDefault();
          var category = $('#productCategory').val();
          if (category === null) {
              alertify.error("Please select a category.");
              return; 
          }
           
          $('.spinner').show();
          $('#frmUpdateProduct').prop('disabled', true);
          var formData = new FormData(this);
          formData.append('requestType', 'UpdateProduct'); 
  
          // Perform the AJAX request
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
                    $('#frmUpdateProduct').prop('disabled', false);
                    location.reload();
                  }
              },
              error: function(xhr, status, error) {
                  alert('Error: ' + error);
              }
          });
      });
  });










    $('#submitDeleteRawMaterials').click(function () {
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
});
</script>