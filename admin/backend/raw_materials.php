<?php include "components/header.php";?>

<!-- Top bar with user profile -->
<div class="flex justify-between items-center bg-white p-4 mb-6 rounded-md shadow-md">
    <h2 class="text-lg font-semibold text-gray-700">Inventory</h2>
    <div class="w-10 h-10 ">
    </div>
</div>

<!-- Raw Materials Label -->
<div class="mb-4">
    <h3 class="text-lg font-semibold text-gray-700">Raw Materials</h3>
</div>

<!-- Table of members -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
    <!-- Search bar and Add button -->
    <div class="mb-4 flex justify-between items-center">
        <input type="text" id="searchInput" placeholder="Search ..." class="w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        <button id="openAddRawMaterialsModal" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition flex items-center gap-2">
            <span class="material-icons">add</span>
            Add Raw Materials
        </button>
    </div>

    <table class="min-w-full table-auto" id="productionTable">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Raw Materials Name</th>
                <th class="py-3 px-6 text-left">Category</th>
                <th class="py-3 px-6 text-left">Weight (grams)</th>
                <th class="py-3 px-6 text-left">Supplier Name</th>
                <th class="py-3 px-6 text-left">Status</th>
                <th class="py-3 px-6 text-left">Action</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
           <?php include "backend/end-points/list_raw_material.php";?>
        </tbody>
    </table>
</div>

<!-- Processed Materials Label -->
<div class="mt-8 mb-4">
    <h3 class="text-lg font-semibold text-gray-700">Processed Materials</h3>
</div>

<!-- Processed Materials Table -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Processed Materials Name</th>
                <th class="py-3 px-6 text-left">Weight (grams)</th>
                <th class="py-3 px-6 text-left">Date Updated</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
            <?php
            // Query to get processed materials (excluding final products)
            $processed_query = "SELECT 
                processed_materials_name,
                weight,
                updated_at
            FROM processed_materials 
            WHERE processed_materials_name IN ('Knotted Bastos', 'Knotted Liniwan', 'Warped Silk')
                AND status = 'Available'
            ORDER BY updated_at DESC";

            $processed_result = mysqli_query($db->conn, $processed_query);

            if ($processed_result && mysqli_num_rows($processed_result) > 0) {
                while ($row = mysqli_fetch_assoc($processed_result)) {
                    echo "<tr class='border-b border-gray-200 hover:bg-gray-50'>";
                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['processed_materials_name']) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . number_format($row['weight'], 3) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . date('Y-m-d H:i', strtotime($row['updated_at'])) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' class='py-3 px-6 text-center'>No processed materials found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Finished Products Label -->
<div class="mt-8 mb-4">
    <h3 class="text-lg font-semibold text-gray-700">Finished Piña Products</h3>
</div>

<!-- Finished Products Table -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Product Name</th>
                <th class="py-3 px-6 text-left">Length (m)</th>
                <th class="py-3 px-6 text-left">Width (m)</th>
                <th class="py-3 px-6 text-left">Quantity</th>
                <th class="py-3 px-6 text-left">Date Updated</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
            <?php
            // Query to get finished products
            $finished_query = "SELECT 
                product_name,
                length_m,
                width_m,
                quantity,
                updated_at
            FROM finished_products
            WHERE product_name IN ('Piña Seda', 'Pure Piña Cloth')
            ORDER BY updated_at DESC";

            $finished_result = mysqli_query($db->conn, $finished_query);

            if ($finished_result && mysqli_num_rows($finished_result) > 0) {
                while ($row = mysqli_fetch_assoc($finished_result)) {
                    echo "<tr class='border-b border-gray-200 hover:bg-gray-50'>";
                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['product_name']) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . number_format($row['length_m'], 3) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . number_format($row['width_m'], 3) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['quantity']) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . date('Y-m-d H:i', strtotime($row['updated_at'])) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='py-3 px-6 text-center'>No finished Piña products found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include "components/footer.php";?>

<script src="assets/js/app.js"></script>






<!-- Modal -->
<div id="RawMaterialsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg p-8 w-full max-w-md mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Add Raw Materials</h2>
            <button type="button" class="closeModal text-gray-400 hover:text-gray-600">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="AddRawMaterialsForm" class="space-y-6">
            <div class="space-y-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raw Materials Name</label>
                    <select name="rm_name" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="" disabled selected>Select material name</option>
                        <option value="Piña Loose">Piña Loose</option>
                        <option value="Silk">Silk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" id="category" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="" disabled>Select category</option>
                        <option value="Liniwan/Washout">Liniwan/Washout</option>
                        <option value="Bastos">Bastos</option>
                    </select>
                    <p class="mt-1 text-sm text-red-600 hidden" id="category-warning">Category is not available for Silk material</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity (grams)</label>
                    <input type="number" name="rm_qty" id="rm_qty" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter quantity in grams" required>
                    <input type="hidden" name="rm_unit" value="gram">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier Name <span class="text-gray-500 text-xs">(optional)</span></label>
                    <input type="text" name="supplier_name" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter supplier name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="rm_status" id="rm_status" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select status</option>
                        <option value="Available">Available</option>
                        <option value="Not Available">Not Available</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" class="closeModal px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
                <button type="submit" id="submitAddRawMaterials" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Add Material
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        let selectedId = "";

        // Function to handle category dropdown based on material selection
        function handleCategoryDropdown(materialSelect, categorySelect, warningId) {
            var selectedValue = $(materialSelect).val();
            console.log('Selected material:', selectedValue);
            
            if (selectedValue === 'Silk') {
                $(categorySelect).val('');
                $(categorySelect).prop('disabled', true);
                $(categorySelect).addClass('bg-gray-100 cursor-not-allowed opacity-60');
                $(warningId).removeClass('hidden');
            } else {
                $(categorySelect).prop('disabled', false);
                $(categorySelect).removeClass('bg-gray-100 cursor-not-allowed opacity-60');
                $(warningId).addClass('hidden');
            }
        }

        // Handle category dropdown in Add form
        $('select[name="rm_name"]').on('change', function() {
            handleCategoryDropdown(this, 'select[name="category"]', '#category-warning');
        });

        // Handle category dropdown in Update form
        $('#rm_name').on('change', function() {
            handleCategoryDropdown(this, '#category', '#update-category-warning');
        });

        // Update button click handler
        $(document).on('click', '.updateRmBtn', function() {
            console.log('Update button clicked');
            var selectedId = $(this).data('id');
            
            // Get all the data attributes
            var rmName = $(this).data('rm_name');
            var rmDescription = $(this).data('category');
            var rmQuantity = $(this).data('rm_quantity');
            var rmUnit = $(this).data('rm_unit');
            var rmStatus = $(this).data('rm_status');
            var supplierName = $(this).data('supplier_name');
            
            // Debug log the data
            console.log('Update button clicked with data:', {
                id: selectedId,
                name: rmName,
                description: rmDescription,
                quantity: rmQuantity,
                unit: rmUnit,
                status: rmStatus,
                supplier_name: supplierName
            });

            // Reset form and clear any previous values
            $('#updateForm')[0].reset();

            // Set the form values
            $('#rmid').val(selectedId);
            
            // Set material name and trigger change event
            var rmNameSelect = $('#rm_name');
            rmNameSelect.val(rmName);
            rmNameSelect.trigger('change');

            // Set category if not Silk
            var rmDescriptionSelect = $('#category');
            if (rmName === 'Silk') {
                rmDescriptionSelect.val('');
                rmDescriptionSelect.prop('disabled', true);
                rmDescriptionSelect.prop('required', false);
                $('#update-category-warning').removeClass('hidden');
            } else {
                // Enable and set category immediately
                    rmDescriptionSelect.prop('disabled', false);
                    rmDescriptionSelect.prop('required', true);
                    rmDescriptionSelect.val(rmDescription);
                    $('#update-category-warning').addClass('hidden');
                    
                    // Debug log for category setting
                    console.log('Setting category value:', {
                        description: rmDescription,
                        currentValue: rmDescriptionSelect.val(),
                        options: rmDescriptionSelect.find('option').map(function() {
                            return $(this).val();
                        }).get()
                    });

                // If category is not in the options, add it
                if (rmDescription && !rmDescriptionSelect.find('option[value="' + rmDescription + '"]').length) {
                    rmDescriptionSelect.append(new Option(rmDescription, rmDescription));
                }
            }

            // Set other form values
            $('#rm_quantity').val(rmQuantity);
            $('#rm_unit').val(rmUnit || 'gram');
            $('#rm_status').val(rmStatus);
            $('#supplier_name').val(supplierName || '');

            // Debug log the form values after setting
            console.log('Form values after setting:', {
                id: $('#rmid').val(),
                name: $('#rm_name').val(),
                description: $('#category').val(),
                quantity: $('#rm_quantity').val(),
                unit: $('#rm_unit').val(),
                status: $('#rm_status').val(),
                supplier_name: $('#supplier_name').val()
            });
            
            // Show the modal
            $('#UpdateRawMaterialsModal').fadeIn();
        });

        // Close update modal handler
        $('#UpdateRawMaterialsModal .closeModal, #UpdateRawMaterialsModal .modalCancel').click(function() {
            $('#UpdateRawMaterialsModal').fadeOut();
        });

        $('#UpdateRawMaterialsModal').click(function(e) {
            if (e.target === this) {
                $(this).fadeOut();
            }
        });

        // Update form submission
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            var formData = {
                requestType: 'UpdateRawMaterials',
                rm_id: $('#rmid').val(),
                rm_name: $('#rm_name').val(),
                category: $('#category').val(),
                rm_quantity: $('#rm_quantity').val(),
                rm_unit: $('#rm_unit').val(),
                rm_status: $('#rm_status').val(),
                supplier_name: $('#supplier_name').val() || ''
            };

            // For Silk material, ensure category is empty
            if (formData.rm_name === 'Silk') {
                formData.category = '';
                $('#category').prop('required', false);
            }

            // Basic validation
            if (!formData.rm_name || !formData.rm_quantity || !formData.rm_status) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill in all required fields',
                    icon: 'error'
                });
                return false;
            }

            // Category validation for non-Silk materials
            if (formData.rm_name !== 'Silk' && !formData.category) {
                    Swal.fire({
                    title: 'Error!',
                    text: 'Category is required for non-Silk materials',
                        icon: 'error'
                });
                return false;
            }

            // Debug log
            console.log('Sending update data:', formData);

            // Disable submit button
            var submitBtn = $('#submitUpdateRawMaterials');
            submitBtn.prop('disabled', true).html('Updating...');

            // Submit form data
            $.ajax({
                type: "POST",
                url: "backend/end-points/controller.php",
                data: formData,
                success: function(response) {
                    try {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: result.message || 'Raw material updated successfully',
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: result.message || 'Failed to update raw material',
                                icon: 'error'
                            });
                        }
                    } catch (e) {
                        console.error('Error:', e);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Server error occurred',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Server error: ' + error,
                        icon: 'error'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('Update');
                }
            });
        });

        // Add form submission
        $('#AddRawMaterialsForm').submit(function(e) {
            e.preventDefault();
            
            // Get form values directly from form elements
            var formData = new FormData(this);
            formData.append('requestType', 'AddRawMaterials');

            // Convert FormData to object for validation
            var formObject = {};
            formData.forEach(function(value, key) {
                formObject[key] = value;
            });

            // Debug form data
            console.log('Form data:', formObject);

            // Validate required fields
            var errors = [];
            if (!formObject.rm_name) errors.push('Material name is required');
            if (formObject.rm_name !== 'Silk' && !formObject.category) errors.push('Category is required');
            if (!formObject.rm_qty) errors.push('Quantity is required');
            if (!formObject.rm_status) errors.push('Status is required');

            if (errors.length > 0) {
                console.log('Validation errors:', errors);
                console.log('Status value:', $('#rm_status').val());
                errors.forEach(function(error) {
                    alertify.error(error);
                });
                return false;
            }

            // For Silk material, ensure category is empty
            if (formObject.rm_name === 'Silk') {
                formObject.category = '';
            }

            // Disable submit button and show loading state
            var submitBtn = $('#submitAddRawMaterials');
            submitBtn.prop('disabled', true).html('Adding...');

            // Submit form data
            $.ajax({
                type: "POST",
                url: "backend/end-points/controller.php",
                data: formObject,
                success: function(response) {
                    try {
                        console.log('Raw server response:', response);
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        console.log('Parsed response:', result);
                        
                        if (result.status === 'success') {
                            alertify.success(result.message);
                            $('#RawMaterialsModal').fadeOut();
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            alertify.error(result.message || 'Failed to add raw material');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        console.error('Raw response:', response);
                        alertify.error('Server error: ' + (response.message || 'Unknown error occurred'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    console.error('XHR:', xhr);
                    alertify.error('Server error: ' + error);
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html('Add Material');
                }
            });
        });

        // Initialize select elements
        $('select').each(function() {
            $(this).on('change', function() {
                console.log($(this).attr('name') + ' changed to:', $(this).val());
            });
        });

        // Function to reset Add form to initial state
        function resetAddForm() {
            var form = $('#AddRawMaterialsForm')[0];
            form.reset();
            
            // Reset select elements to their first option
            $('select[name="rm_name"]').val('').trigger('change');
            $('select[name="category"]').val('').trigger('change');
            $('select[name="rm_status"]').val('').trigger('change');
            
            // Reset category field state
            $('#category').prop('disabled', false)
                         .removeClass('bg-gray-100 cursor-not-allowed opacity-60');
            $('#category-warning').addClass('hidden');
            
            // Clear any validation messages
            $('.error-message').remove();
        }

        // Reset form when opening modal
        $('#openAddRawMaterialsModal').click(function() {
            resetAddForm();
            $('#RawMaterialsModal').fadeIn();
        });

        // Close modal handlers
        $('.closeModal').click(function() {
            $('#RawMaterialsModal').fadeOut();
            resetAddForm();
        });

        $('#RawMaterialsModal').click(function(e) {
            if (e.target === this) {
                $(this).fadeOut();
                resetAddForm();
            }
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



<div id="UpdateRawMaterialsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg p-8 w-full max-w-md mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Update Raw Material</h2>
            <button type="button" class="closeModal text-gray-400 hover:text-gray-600">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="updateForm" class="space-y-6">
            <input type="hidden" name="rm_id" id="rmid">
            <div class="space-y-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raw Materials Name</label>
                    <select name="rm_name" id="rm_name" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="" disabled>Select material name</option>
                        <option value="Piña Loose">Piña Loose</option>
                        <option value="Silk">Silk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" id="category" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="" disabled>Select category</option>
                        <option value="Liniwan/Washout">Liniwan/Washout</option>
                        <option value="Bastos">Bastos</option>
                    </select>
                    <p class="mt-1 text-sm text-red-600 hidden" id="update-category-warning">Category is not available for Silk material</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity (grams)</label>
                    <input type="number" name="rm_quantity" id="rm_quantity" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter quantity in grams" required>
                    <input type="hidden" name="rm_unit" id="rm_unit" value="gram">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier Name <span class="text-gray-500 text-xs">(optional)</span></label>
                    <input type="text" name="supplier_name" id="supplier_name" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter supplier name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="rm_status" id="rm_status" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select status</option>
                        <option value="Available">Available</option>
                        <option value="Not Available">Not Available</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" class="closeModal px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
                <button type="submit" id="submitUpdateRawMaterials" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Function to handle category dropdown based on material selection
    function handleCategoryDropdown(materialSelect, categorySelect, warningId) {
        var selectedValue = $(materialSelect).val();
        console.log('Selected material:', selectedValue);
        
        if (selectedValue === 'Silk') {
            $(categorySelect).val('');
            $(categorySelect).prop('disabled', true);
            $(categorySelect).addClass('bg-gray-100 cursor-not-allowed opacity-60');
            $(warningId).removeClass('hidden');
        } else {
            $(categorySelect).prop('disabled', false);
            $(categorySelect).removeClass('bg-gray-100 cursor-not-allowed opacity-60');
            $(warningId).addClass('hidden');
        }
    }

    // Handle category dropdown in Add form
    $('select[name="rm_name"]').on('change', function() {
        handleCategoryDropdown(this, 'select[name="category"]', '#category-warning');
    });

    // Handle category dropdown in Update form
    $('#rm_name').on('change', function() {
        handleCategoryDropdown(this, '#category', '#update-category-warning');
    });

    // Update button click handler
    $(document).on('click', '.updateRmBtn', function() {
        var selectedId = $(this).data('id');
        var rmName = $(this).data('rm_name');
        var rmDescription = $(this).data('category');
        var rmQuantity = $(this).data('rm_quantity');
        var rmUnit = $(this).data('rm_unit');
        var rmStatus = $(this).data('rm_status');
        var supplierName = $(this).data('supplier_name');
        
        // Reset form
        $('#updateForm')[0].reset();

        // Set form values
        $('#rmid').val(selectedId);
        $('#rm_name').val(rmName);
        $('#rm_quantity').val(rmQuantity);
        $('#rm_unit').val(rmUnit || 'gram');
        $('#rm_status').val(rmStatus);
        $('#supplier_name').val(supplierName || '');

        // Handle category based on material type
        var categorySelect = $('#category');
        if (rmName === 'Silk') {
            categorySelect.val('');
            categorySelect.prop('disabled', true);
            $('#update-category-warning').removeClass('hidden');
        } else {
            categorySelect.prop('disabled', false);
            
            // Clear existing options except the first (disabled) one
            categorySelect.find('option:not(:first)').remove();
        
            // Add standard options
            categorySelect.append(new Option('Liniwan/Washout', 'Liniwan/Washout'));
            categorySelect.append(new Option('Bastos', 'Bastos'));

            // Set the value
            categorySelect.val(rmDescription);
            $('#update-category-warning').addClass('hidden');
        }

        // Show modal
        $('#UpdateRawMaterialsModal').fadeIn();
    });

    // Close update modal handler
    $('#UpdateRawMaterialsModal .closeModal, #UpdateRawMaterialsModal .modalCancel').click(function() {
        $('#UpdateRawMaterialsModal').fadeOut();
    });

    $('#UpdateRawMaterialsModal').click(function(e) {
        if (e.target === this) {
            $(this).fadeOut();
        }
    });

    // Update form submission
    $('#updateForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form values directly from form elements
        var formData = new FormData(this);
        formData.append('requestType', 'UpdateRawMaterials');
        
        // Convert FormData to object for validation
        var data = {};
        formData.forEach(function(value, key) {
            data[key] = value;
        });

        // Handle category based on material type
        if (data.rm_name === 'Silk') {
            formData.set('category', '');
        }

        // Ensure proper status value
        if (data.rm_status) {
            formData.set('rm_status', data.rm_status);

        }

        // Basic validation
        if (!data.rm_name || !data.rm_quantity || !data.rm_status) {
            Swal.fire({
                title: 'Error!',
                text: 'Please fill in all required fields',
                icon: 'error'
            });
            return false;
        }

        // Category validation for non-Silk materials
        if (data.rm_name !== 'Silk' && !data.category) {
                Swal.fire({
                title: 'Error!',
                text: 'Category is required for non-Silk materials',
                    icon: 'error'
            });
            return false;
        }

        // Convert FormData back to regular object for AJAX
        var sendData = {};
        formData.forEach(function(value, key) {
            sendData[key] = value;
        });

        // Disable submit button
        var submitBtn = $('#submitUpdateRawMaterials');
        submitBtn.prop('disabled', true).html('Updating...');

        // Submit form data
        $.ajax({
            type: "POST",
            url: "backend/end-points/controller.php",
            data: sendData,
            success: function(response) {
                try {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: result.message || 'Raw material updated successfully',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Failed to update raw material',
                            icon: 'error'
                        });
                    }
                } catch (e) {
                    console.error('Error:', e);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Server error occurred',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Server error: ' + error,
                    icon: 'error'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Update');
            }
        });
    });

    // Add form submission and other handlers...
});
</script>

<!-- Modal Structure -->
<div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6">
        <h2 class="text-xl font-semibold mb-4" id="modalTitle">Delete Raw Material</h2>
        <p id="modalContent" class="mb-4">Are you sure you want to delete this raw material?</p>
        <div class="flex justify-end space-x-2">
            <button id="modalCancel" class="bg-gray-500 hover:bg-gray-600 text-white py-1 px-3 rounded transition-colors duration-200">
                Cancel
            </button>
            <button id="modalConfirm" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded transition-colors duration-200">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add delete button handler
    $('.deleteRmBtn').click(function(e) {
        e.preventDefault();
        var rmId = $(this).data('id');
        var rmName = $(this).data('rm_name');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete ' + rmName + '? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "backend/end-points/controller.php",
                    data: {
                        requestType: 'DeleteRawMaterials',
                        rm_id: rmId
                    },
                    success: function(response) {
                        try {
                            var result = typeof response === 'string' ? JSON.parse(response) : response;
                            if (result.status === 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    result.message || 'Raw material has been deleted.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    result.message || 'Failed to delete raw material.',
                                    'error'
                                );
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            Swal.fire(
                                'Error!',
                                'Server error occurred while deleting.',
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', error);
                        Swal.fire(
                            'Error!',
                            'Server error: ' + error,
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
