<?php require_once "components/header.php";?>


<!-- Raw Materials Label -->
<div class="mb-4">
    <h3 class="text-lg font-semibold text-gray-700">Raw Materials</h3>
</div>

<!-- Table of members -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
    <!-- Search bar and Add button -->
    <div class="mb-4 flex justify-between items-center">
        <input type="text" id="searchInput" placeholder="Search ..." class="w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        <div class="flex gap-2">
            <button id="scrollToProcessed" class="bg-yellow-500 text-white px-4 py-2 rounded-md shadow hover:bg-yellow-600 transition flex items-center gap-2">
                <span class="material-icons">arrow_downward</span>
                Processed Materials
            </button>
            <button id="scrollToFinished" class="bg-green-500 text-white px-4 py-2 rounded-md shadow hover:bg-green-600 transition flex items-center gap-2">
                <span class="material-icons">arrow_downward</span>
                Finished Products
            </button>
            <button id="openAddRawMaterialsModal" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition flex items-center gap-2">
                <span class="material-icons">add</span>
                Add Raw Materials
            </button>
        </div>
    </div>

    <table class="min-w-full table-auto" id="productionTable">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Raw Materials Name</th>
                <th class="py-3 px-6 text-left">Category</th>
                <th class="py-3 px-6 text-left">Weight (grams)</th>
                <th class="py-3 px-6 text-left">Unit Cost (₱)</th>
                <th class="py-3 px-6 text-left">Total Value (₱)</th>
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
<div id="processed-materials-section" class="mt-8 mb-4">
    <h3 class="text-lg font-semibold text-gray-700">Processed Materials</h3>
</div>

<!-- Processed Materials Table -->
<div class="overflow-x-auto bg-white rounded-md shadow-md p-4">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Processed Materials Name</th>
                <th class="py-3 px-6 text-left">Weight (grams)</th>
                <th class="py-3 px-6 text-left">Unit Cost (₱)</th>
                <th class="py-3 px-6 text-left">Date Updated</th>
                <th class="py-3 px-6 text-left">Action</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
            <?php
            // Add unit_cost column if it doesn't exist
            $check_column = mysqli_query($db->conn, "SHOW COLUMNS FROM processed_materials LIKE 'unit_cost'");
            if (mysqli_num_rows($check_column) == 0) {
                mysqli_query($db->conn, "ALTER TABLE processed_materials ADD COLUMN unit_cost DECIMAL(10,2) DEFAULT 0.00");
            }
            
            // Query to get processed materials (excluding final products)
            $processed_query = "SELECT 
                id,
                processed_materials_name,
                weight,
                unit_cost,
                updated_at
            FROM processed_materials 
            WHERE processed_materials_name IN ('Knotted Bastos', 'Knotted Liniwan', 'Warped Silk')
                AND status = 'Available'
            ORDER BY updated_at DESC";

            $processed_result = mysqli_query($db->conn, $processed_query);

            if ($processed_result && mysqli_num_rows($processed_result) > 0) {
                while ($row = mysqli_fetch_assoc($processed_result)) {
                    $unit_cost = isset($row['unit_cost']) ? floatval($row['unit_cost']) : 0.00;
                    echo "<tr class='border-b border-gray-200 hover:bg-gray-50'>";
                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['processed_materials_name']) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . number_format($row['weight'], 3) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>₱ " . number_format($unit_cost, 2) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . date('Y-m-d H:i', strtotime($row['updated_at'])) . "</td>";
                    echo "<td class='py-3 px-6 flex space-x-2'>";
                    echo "<button 
                        type='button'
                        class='updateProcessedBtn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow'
                        data-id='" . htmlspecialchars($row['id']) . "' 
                        data-name='" . htmlspecialchars($row['processed_materials_name']) . "'
                        data-unit_cost='" . htmlspecialchars($unit_cost) . "'
                    >";
                    echo "<span class='material-icons text-sm mr-1'>edit</span> Update";
                    echo "</button>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='py-3 px-6 text-center'>No processed materials found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Finished Products Label -->
<div id="finished-products-section" class="mt-8 mb-4">
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
                <th class="py-3 px-6 text-left">Unit Cost (₱)</th>
                <th class="py-3 px-6 text-left">Date Updated</th>
                <th class="py-3 px-6 text-left">Action</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
            <?php
            // Add unit_cost column if it doesn't exist
            $check_column_finished = mysqli_query($db->conn, "SHOW COLUMNS FROM finished_products LIKE 'unit_cost'");
            if (mysqli_num_rows($check_column_finished) == 0) {
                mysqli_query($db->conn, "ALTER TABLE finished_products ADD COLUMN unit_cost DECIMAL(10,2) DEFAULT 0.00");
            }
            
            // Query to get finished products
            $finished_query = "SELECT 
                id,
                product_name,
                length_m,
                width_m,
                quantity,
                unit_cost,
                updated_at
            FROM finished_products
            WHERE product_name IN ('Piña Seda', 'Pure Piña Cloth')
            ORDER BY updated_at DESC";

            $finished_result = mysqli_query($db->conn, $finished_query);

            if ($finished_result && mysqli_num_rows($finished_result) > 0) {
                while ($row = mysqli_fetch_assoc($finished_result)) {
                    $unit_cost = isset($row['unit_cost']) ? floatval($row['unit_cost']) : 0.00;
                    echo "<tr class='border-b border-gray-200 hover:bg-gray-50'>";
                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['product_name']) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . number_format($row['length_m'], 3) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . number_format($row['width_m'], 3) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['quantity']) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>₱ " . number_format($unit_cost, 2) . "</td>";
                    echo "<td class='py-3 px-6 text-left'>" . date('Y-m-d H:i', strtotime($row['updated_at'])) . "</td>";
                    echo "<td class='py-3 px-6 flex space-x-2'>";
                    echo "<button 
                        type='button'
                        class='updateFinishedBtn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-full text-xs flex items-center shadow'
                        data-id='" . htmlspecialchars($row['id']) . "' 
                        data-name='" . htmlspecialchars($row['product_name']) . "'
                        data-unit_cost='" . htmlspecialchars($unit_cost) . "'
                    >";
                    echo "<span class='material-icons text-sm mr-1'>edit</span> Update";
                    echo "</button>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='py-3 px-6 text-center'>No finished Piña products found</td></tr>";
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
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost (₱)</label>
                    <input type="number" name="unit_cost" id="unit_cost" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter unit cost per gram" min="0" step="0.01">
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
            var unitCost = $(this).data('unit_cost');
            
            // Debug log the data
            console.log('Update button clicked with data:', {
                id: selectedId,
                name: rmName,
                description: rmDescription,
                quantity: rmQuantity,
                unit: rmUnit,
                status: rmStatus,
                supplier_name: supplierName,
                unit_cost: unitCost
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
            // Handle "Not available" as empty string
            if (rmDescription === 'Not available' || rmDescription === '') {
                rmDescription = '';
            }
            
            if (rmName === 'Silk') {
                rmDescriptionSelect.val('');
                rmDescriptionSelect.prop('disabled', true);
                rmDescriptionSelect.prop('required', false);
                $('#update-category-warning').removeClass('hidden');
            } else {
                // Enable and set category immediately
                rmDescriptionSelect.prop('disabled', false);
                rmDescriptionSelect.prop('required', true);
                
                // Only set value if category exists and is not empty
                if (rmDescription && rmDescription !== 'Not available') {
                    rmDescriptionSelect.val(rmDescription);
                } else {
                    rmDescriptionSelect.val('');
                }
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
                if (rmDescription && rmDescription !== 'Not available' && !rmDescriptionSelect.find('option[value="' + rmDescription + '"]').length) {
                    rmDescriptionSelect.append(new Option(rmDescription, rmDescription));
                }
            }

            // Set other form values
            $('#rm_quantity').val(rmQuantity);
            $('#rm_unit').val(rmUnit || 'gram');
            
            // Normalize status value to match select options
            var normalizedStatus = rmStatus;
            if (rmStatus && typeof rmStatus === 'string') {
                normalizedStatus = rmStatus.charAt(0).toUpperCase() + rmStatus.slice(1).toLowerCase();
                // Handle "Not available" vs "Not Available"
                if (normalizedStatus.toLowerCase() === 'not available') {
                    normalizedStatus = 'Not Available';
                } else if (normalizedStatus.toLowerCase() === 'available') {
                    normalizedStatus = 'Available';
                }
            }
            $('#rm_status').val(normalizedStatus);
            $('#supplier_name').val(supplierName || '');
            // Ensure unit_cost is properly set - handle 0 as a valid value
            var unitCostValue = unitCost !== null && unitCost !== undefined && unitCost !== '' ? unitCost : '0';
            $('#update_unit_cost').val(unitCostValue);

            // Debug log the form values after setting
            console.log('Form values after setting:', {
                id: $('#rmid').val(),
                name: $('#rm_name').val(),
                description: $('#category').val(),
                quantity: $('#rm_quantity').val(),
                unit: $('#rm_unit').val(),
                status: $('#rm_status').val(),
                supplier_name: $('#supplier_name').val(),
                unit_cost: $('#update_unit_cost').val()
            });
            
            // Show the modal - remove hidden class and fade in
            $('#UpdateRawMaterialsModal').removeClass('hidden').fadeIn();
        });

        // Close update modal handler
        $(document).on('click', '#UpdateRawMaterialsModal .closeModal, #UpdateRawMaterialsModal .modalCancel', function() {
            $('#UpdateRawMaterialsModal').fadeOut(function() {
                $(this).addClass('hidden');
            });
        });

        $(document).on('click', '#UpdateRawMaterialsModal', function(e) {
            if (e.target === this) {
                $(this).fadeOut(function() {
                    $(this).addClass('hidden');
                });
            }
        });

        // Update form submission
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            var categoryValue = $('#category').val();
            // Treat "Not available" as empty string
            if (categoryValue === 'Not available' || categoryValue === '') {
                categoryValue = '';
            }
            
            // Get unit_cost and ensure it's a valid number
            var unitCostValue = $('#update_unit_cost').val();
            // Convert to number, default to 0 if empty or invalid
            // Important: preserve 0 as a valid value
            if (unitCostValue === '' || unitCostValue === null || unitCostValue === undefined) {
                unitCostValue = '0';
            } else {
                var parsedValue = parseFloat(unitCostValue);
                unitCostValue = isNaN(parsedValue) ? '0' : parsedValue.toString();
            }
            
            var formData = {
                requestType: 'UpdateRawMaterials',
                rm_id: $('#rmid').val(),
                rm_name: $('#rm_name').val(),
                category: categoryValue,
                rm_quantity: $('#rm_quantity').val(),
                rm_unit: $('#rm_unit').val(),
                rm_status: $('#rm_status').val(),
                supplier_name: $('#supplier_name').val() || '',
                unit_cost: unitCostValue
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
            console.log('Unit cost value being sent:', formData.unit_cost, 'Type:', typeof formData.unit_cost);

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
                                // Force a hard reload to ensure updated data is displayed
                                window.location.reload(true);
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
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost (₱)</label>
                    <input type="number" name="unit_cost" id="update_unit_cost" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter unit cost per gram" min="0" step="0.01">
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
    $('#scrollToFinished').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('#finished-products-section').offset().top
        }, 800);
    });

    $('#scrollToProcessed').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('#processed-materials-section').offset().top
        }, 800);
    });
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

<!-- Update Processed Materials Modal -->
<div id="UpdateProcessedModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg p-8 w-full max-w-md mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Update Processed Material</h2>
            <button type="button" class="closeProcessedModal text-gray-400 hover:text-gray-600">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="updateProcessedForm" class="space-y-6">
            <input type="hidden" name="processed_id" id="processed_id">
            <div class="space-y-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Processed Material Name</label>
                    <input type="text" name="processed_name" id="processed_name" class="w-full border border-gray-300 rounded-md px-4 py-2 bg-gray-100 cursor-not-allowed" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost (₱) <span class="text-red-500">*</span></label>
                    <input type="number" name="processed_unit_cost" id="processed_unit_cost" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter unit cost" min="0" step="0.01" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" class="closeProcessedModal px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
                <button type="submit" id="submitUpdateProcessed" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Finished Products Modal -->
<div id="UpdateFinishedModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg p-8 w-full max-w-md mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Update Finished Product</h2>
            <button type="button" class="closeFinishedModal text-gray-400 hover:text-gray-600">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="updateFinishedForm" class="space-y-6">
            <input type="hidden" name="finished_id" id="finished_id">
            <div class="space-y-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                    <input type="text" name="finished_name" id="finished_name" class="w-full border border-gray-300 rounded-md px-4 py-2 bg-gray-100 cursor-not-allowed" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost (₱) <span class="text-red-500">*</span></label>
                    <input type="number" name="finished_unit_cost" id="finished_unit_cost" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter unit cost" min="0" step="0.01" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" class="closeFinishedModal px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
                <button type="submit" id="submitUpdateFinished" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Processed Materials Update Button Handler
    $(document).on('click', '.updateProcessedBtn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var unitCost = $(this).data('unit_cost') || '0';
        
        $('#processed_id').val(id);
        $('#processed_name').val(name);
        $('#processed_unit_cost').val(unitCost);
        
        $('#UpdateProcessedModal').removeClass('hidden').fadeIn();
    });

    // Processed Materials Update Form Submission
    $('#updateProcessedForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            requestType: 'UpdateProcessedMaterial',
            processed_id: $('#processed_id').val(),
            processed_unit_cost: $('#processed_unit_cost').val() || '0'
        };

        if (!formData.processed_unit_cost || formData.processed_unit_cost === '0') {
            Swal.fire({
                title: 'Error!',
                text: 'Please enter a valid unit cost',
                icon: 'error'
            });
            return false;
        }

        var submitBtn = $('#submitUpdateProcessed');
        submitBtn.prop('disabled', true).html('Updating...');

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
                            text: result.message || 'Processed material updated successfully',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload(true);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Failed to update processed material',
                            icon: 'error'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Server error occurred',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, status, error) {
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

    // Finished Products Update Button Handler
    $(document).on('click', '.updateFinishedBtn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var unitCost = $(this).data('unit_cost') || '0';
        
        $('#finished_id').val(id);
        $('#finished_name').val(name);
        $('#finished_unit_cost').val(unitCost);
        
        $('#UpdateFinishedModal').removeClass('hidden').fadeIn();
    });

    // Finished Products Update Form Submission
    $('#updateFinishedForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            requestType: 'UpdateFinishedProduct',
            finished_id: $('#finished_id').val(),
            finished_unit_cost: $('#finished_unit_cost').val() || '0'
        };

        if (!formData.finished_unit_cost || formData.finished_unit_cost === '0') {
            Swal.fire({
                title: 'Error!',
                text: 'Please enter a valid unit cost',
                icon: 'error'
            });
            return false;
        }

        var submitBtn = $('#submitUpdateFinished');
        submitBtn.prop('disabled', true).html('Updating...');

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
                            text: result.message || 'Finished product updated successfully',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload(true);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Failed to update finished product',
                            icon: 'error'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Server error occurred',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, status, error) {
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

    // Close modals
    $(document).on('click', '.closeProcessedModal, #UpdateProcessedModal', function(e) {
        if (e.target === this || $(e.target).hasClass('closeProcessedModal')) {
            $('#UpdateProcessedModal').fadeOut(function() {
                $(this).addClass('hidden');
            });
        }
    });

    $(document).on('click', '.closeFinishedModal, #UpdateFinishedModal', function(e) {
        if (e.target === this || $(e.target).hasClass('closeFinishedModal')) {
            $('#UpdateFinishedModal').fadeOut(function() {
                $(this).addClass('hidden');
            });
        }
    });
});
</script>