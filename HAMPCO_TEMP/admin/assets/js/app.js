// Function to load members for a specific role and product
async function loadMembers(role, selectElement, productName = null) {
    try {
        let url = 'backend/end-points/get_members_by_role.php?role=' + role;
        if (productName) {
            url += '&product_name=' + encodeURIComponent(productName);
        }
        const response = await fetch(url);
        const members = await response.json();

        // Clear existing options except the first one
        while (selectElement.options.length > 1) {
            selectElement.remove(1);
        }
        
        if (Array.isArray(members) && members.length > 0) {
            members.forEach(member => {
                const option = document.createElement('option');
                option.value = member.id;
                option.textContent = member.fullname;
                selectElement.appendChild(option);
            });
        } else {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No available members';
            option.disabled = true;
            selectElement.appendChild(option);
        }
    } catch (error) {
        console.error('Error loading members:', error);
    }
}

// Function to handle task assignment
async function assignTask(prodLineId, productName, quantity) { // Made async
    const form = document.getElementById('taskAssignmentForm');
    const modalTitle = document.querySelector('#taskAssignmentModal h3');
    const identifierInput = document.getElementById('identifier');
    const prodLineIdInput = document.getElementById('prod_line_id');
    const isReassignmentInput = document.getElementById('is_reassignment'); // New hidden input
    const productDetailsInput = document.getElementById('product_details'); // Get the product_details input

    if (identifierInput) identifierInput.value = prodLineId;
    if (prodLineIdInput) prodLineIdInput.value = prodLineId;
    if (productDetailsInput) productDetailsInput.value = productName; // Set product_details

    // Reset form fields and hidden inputs
    if (form) form.reset();
    if (isReassignmentInput) isReassignmentInput.value = '';

    // Get modal sections
    const knotterSection = document.getElementById('knotterSection');
    const warperSection = document.getElementById('warperSection');
    const weaverSection = document.getElementById('weaverSection');
    const assignBtn = document.querySelector('#taskAssignmentForm button[type="submit"]');

    // Hide all sections and error messages by default
    [knotterSection, warperSection, weaverSection].forEach(section => {
        if (section) {
            section.classList.add('hidden');
            const error = section.querySelector('.text-red-500');
            if (error) error.classList.add('hidden');
            // Also reset required attribute for select and deadline
            const select = section.querySelector('select');
            const deadline = section.querySelector('input[type="datetime-local"]');
            if (select) select.required = false;
            if (deadline) deadline.required = false;
        }
    });
    if (assignBtn) assignBtn.disabled = false;

    // Get select elements
    const knotterSelect = document.querySelector('select[name="knotter_id[]"]');
    const warperSelect = document.querySelector('select[name="warper_id"]');
    const weaverSelect = document.querySelector('select[name="weaver_id"]');

    // Load members for each role with product name filtering
    // Determine which role(s) are needed based on product name
    let rolesToLoad = [];
    if (productName === 'Knotted Liniwan' || productName === 'Knotted Bastos') {
        rolesToLoad = [
            loadMembers('knotter', knotterSelect, productName)
        ];
    } else if (productName === 'Warped Silk') {
        rolesToLoad = [
            loadMembers('warper', warperSelect, productName)
        ];
    } else if (productName === 'Piña Seda' || productName === 'Pure Piña Cloth') {
        rolesToLoad = [
            loadMembers('weaver', weaverSelect, productName)
        ];
    } else {
        // Load all roles if product name doesn't match expected values
        rolesToLoad = [
            loadMembers('knotter', knotterSelect, productName),
            loadMembers('warper', warperSelect, productName),
            loadMembers('weaver', weaverSelect, productName)
        ];
    }

    // Using Promise.all to load members concurrently
    await Promise.all(rolesToLoad);

    // Fetch existing assignments for pre-population
    let existingAssignments = [];
    try {
        const response = await fetch(`backend/end-points/get_task_assignments.php?prod_line_id=${prodLineId}`);
        const data = await response.json();
        console.log('Data from get_task_assignments.php:', data); // Log the data
        if (data.success && data.assignments.length > 0) {
            existingAssignments = data.assignments;
        }
    } catch (error) {
        console.error('Error fetching existing assignments:', error);
    }

    let isReassignment = existingAssignments.length > 0;
    if (isReassignmentInput) isReassignmentInput.value = isReassignment ? 'true' : '';
    if (modalTitle) modalTitle.textContent = isReassignment ? 'Reassign Tasks' : 'Assign Tasks';


    // Show appropriate sections and pre-populate fields based on product type
    if (productName === 'Knotted Liniwan' || productName === 'Knotted Bastos') {
        if (knotterSection) {
            knotterSection.classList.remove('hidden');
            const knotterDeadline = knotterSection.querySelector('input[name="deadline"]');
            if (knotterDeadline) knotterDeadline.required = true;
            if (knotterSelect) knotterSelect.required = true;

            const assignment = existingAssignments.find(a => a.role === 'knotter');
            if (assignment) {
                knotterSelect.value = assignment.member_id;
                knotterDeadline.value = assignment.deadline.substring(0, 16); // Format for datetime-local
                const hiddenTaskId = document.createElement('input');
                hiddenTaskId.type = 'hidden';
                hiddenTaskId.name = 'knotter_task_id';
                hiddenTaskId.value = assignment.task_id;
                form.appendChild(hiddenTaskId);
            }
        }
    } else if (productName === 'Warped Silk') {
        if (warperSection) {
            warperSection.classList.remove('hidden');
            const warperDeadline = warperSection.querySelector('input[name="warper_deadline"]');
            if (warperDeadline) warperDeadline.required = true;
            if (warperSelect) warperSelect.required = true;

            const assignment = existingAssignments.find(a => a.role === 'warper');
            if (assignment) {
                warperSelect.value = assignment.member_id;
                warperDeadline.value = assignment.deadline.substring(0, 16); // Format for datetime-local
                const hiddenTaskId = document.createElement('input');
                hiddenTaskId.type = 'hidden';
                hiddenTaskId.name = 'warper_task_id';
                hiddenTaskId.value = assignment.task_id;
                form.appendChild(hiddenTaskId);
            }
        }
    } else if (productName === 'Piña Seda' || productName === 'Pure Piña Cloth') {
        if (weaverSection) {
            weaverSection.classList.remove('hidden');
            const weaverDeadline = weaverSection.querySelector('input[name="weaver_deadline"]');
            if (weaverDeadline) weaverDeadline.required = true;
            if (weaverSelect) weaverSelect.required = true;

            const assignment = existingAssignments.find(a => a.role === 'weaver');
            if (assignment) {
                weaverSelect.value = assignment.member_id;
                weaverDeadline.value = assignment.deadline.substring(0, 16); // Format for datetime-local
                const hiddenTaskId = document.createElement('input');
                hiddenTaskId.type = 'hidden';
                hiddenTaskId.name = 'weaver_task_id';
                hiddenTaskId.value = assignment.task_id;
                form.appendChild(hiddenTaskId);
            }
        }
    }

    // Show the modal
    const modal = document.getElementById('taskAssignmentModal');
    if (modal) modal.classList.remove('hidden');
}

// Function to handle edit product
function editProduct(prodLineId) {
    alert('Edit functionality for production item ' + prodLineId + ' is being developed.');
    // This can be extended later to open an edit modal or redirect to an edit page
}

// Function to load all uncompleted production lines
async function loadProductionLines(selectElement) {
    try {
        const response = await fetch('backend/end-points/get_uncompleted_production_lines.php');
        const data = await response.json();

        // Clear existing options except the first one
        while (selectElement.options.length > 1) {
            selectElement.remove(1);
        }

        if (data.success && Array.isArray(data.production_lines) && data.production_lines.length > 0) {
            data.production_lines.forEach(prod => {
                const option = document.createElement('option');
                option.value = prod.prod_line_id;
                option.textContent = `PL${String(prod.prod_line_id).padStart(4, '0')} - ${prod.product_name}`;
                selectElement.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading production lines:', error);
    }
}


// Add event listeners for modal close buttons
document.addEventListener('DOMContentLoaded', function() {
    // Task Assignment Modal (Existing)
    const taskModal = document.getElementById('taskAssignmentModal');
    const cancelTaskBtn = document.getElementById('cancelTaskBtn');
    const taskForm = document.getElementById('taskAssignmentForm');

    if (taskModal) {
        taskModal.addEventListener('click', function(e) {
            if (e.target === taskModal) {
                taskModal.classList.add('hidden');
                if (taskForm) taskForm.reset();
                const errorMessages = taskModal.querySelectorAll('.text-red-500');
                errorMessages.forEach(msg => msg.classList.add('hidden'));
            }
        });
    }
    
    if (cancelTaskBtn && taskModal) {
        cancelTaskBtn.addEventListener('click', function() {
            taskModal.classList.add('hidden');
            if (taskForm) taskForm.reset();
            const errorMessages = taskModal.querySelectorAll('.text-red-500');
            errorMessages.forEach(msg => msg.classList.add('hidden'));
        });
    }

    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = taskForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const formData = new FormData(taskForm);
            

            // Hide all error messages first
            const errorMessages = taskForm.querySelectorAll('.text-red-500');
            errorMessages.forEach(msg => msg.classList.add('hidden'));

            // Get visible sections and validate
            const visibleSections = Array.from(taskForm.querySelectorAll('.space-y-2')).filter(
                section => !section.classList.contains('hidden')
            );

            let isValid = true;
            visibleSections.forEach(section => {
                const select = section.querySelector('select');
                const deadline = section.querySelector('input[type="datetime-local"]');
                const error = section.querySelector('.text-red-500');

                if (select && deadline) {
                    // Check if a member is selected AND a deadline is set
                    if (!select.value || !deadline.value) {
                        isValid = false;
                        if (error) error.classList.remove('hidden'); // Ensure error message is hidden before showing
                        if (!select.value) error.textContent = 'Please select a member.';
                        else if (!deadline.value) error.textContent = 'Please set a deadline.';
                        error.classList.remove('hidden');
                    } else {
                        // Ensure both are valid if one is filled
                        if (!select.value && deadline.value) {
                            isValid = false;
                            error.textContent = 'Please select a member.';
                            error.classList.remove('hidden');
                        } else if (select.value && !deadline.value) {
                            isValid = false;
                            error.textContent = 'Please set a deadline.';
                            error.classList.remove('hidden');
                        }
                    }
                }
            });

            if (!isValid) {
                if (submitBtn) submitBtn.disabled = false;
                return;
            }

            // Log formData content for debugging
            for (let pair of formData.entries()) {
                console.log(pair[0]+ ': ' + pair[1]); 
            }

            fetch('backend/end-points/assign_tasks.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Tasks assigned successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        if (taskModal) taskModal.classList.add('hidden');
                        if (taskForm) taskForm.reset();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to assign tasks'
                    });
                }
            })
            .catch(error => {
                console.error('Error during fetch or JSON parsing:', error);
                let userMessage = 'An error occurred while assigning tasks. ';
                if (error.message.includes('HTTP error!')) {
                    userMessage += `Server responded with ${error.message.split('status: ')[1]}. Please check server logs.`;
                } else if (error instanceof SyntaxError) {
                    userMessage += 'Received malformed response from server. Check server output.';
                } else {
                    userMessage += `Details: ${error.message}`;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: userMessage
                });
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }

    // Product Creation / Reassignment Modal
    const createBtn = document.getElementById('createProductBtn');
    const productModal = document.getElementById('createProductModal');
    const createProductForm = document.getElementById('createProductForm'); // Changed from productForm
    const cancelCreateBtn = document.getElementById('cancelCreateProduct'); // Changed from cancelCreateBtn
    
    // Elements for product creation
    const productNameSelect = document.getElementById('product_name'); // Changed from productNameSelect
    const dimensionFields = document.getElementById('dimensionFields');
    const weightField = document.getElementById('weightField');
    const quantityField = document.getElementById('quantityField');

    // Elements for reassignment
    const toggleReassign = document.getElementById('toggleReassign');
    const reassignTaskSection = document.getElementById('reassignTaskSection');
    const reassignProdLineSelect = document.getElementById('reassign_prod_line_id');
    const actionTypeInput = document.getElementById('action_type');

    // Reassignment member selects and deadlines
    const reassignKnotterSection = document.getElementById('reassignKnotterSection');
    const reassignWarperSection = document.getElementById('reassignWarperSection');
    const reassignWeaverSection = document.getElementById('reassignWeaverSection');
    const reassignKnotterSelect = document.querySelector('select[name="reassign_knotter_id"]');
    const reassignKnotterDeadline = document.querySelector('input[name="reassign_knotter_deadline"]');
    const reassignWarperSelect = document.querySelector('select[name="reassign_warper_id"]');
    const reassignWarperDeadline = document.querySelector('input[name="reassign_warper_deadline"]');
    const reassignWeaverSelect = document.querySelector('select[name="reassign_weaver_id"]');
    const reassignWeaverDeadline = document.querySelector('input[name="reassign_weaver_deadline"]');


    if (createBtn && productModal) {
        createBtn.addEventListener('click', function() {
            productModal.classList.remove('hidden');
            // Reset to default "create product" view when opening modal
            if (toggleReassign) toggleReassign.checked = false;
            if (actionTypeInput) actionTypeInput.value = 'create_product';
            if (reassignTaskSection) reassignTaskSection.classList.add('hidden');
            if (productNameSelect) {
                productNameSelect.closest('.mb-4')?.classList.remove('hidden');
                updateProductFields(); // Ensure product fields are correctly displayed/hidden based on initial product_name
            }
            if (dimensionFields) dimensionFields.classList.remove('hidden');
            if (weightField) weightField.classList.add('hidden');
            if (quantityField) quantityField.classList.remove('hidden');
        });
    }
    
    if (cancelCreateBtn && productModal) {
        cancelCreateBtn.addEventListener('click', function() {
            productModal.classList.add('hidden');
            if (createProductForm) createProductForm.reset();
            // Reset required attributes and hide error messages
            resetProductFormValidation();
        });
    }
    
    if (productModal) {
        productModal.addEventListener('click', function(e) {
            if (e.target === productModal) {
                productModal.classList.add('hidden');
                if (createProductForm) createProductForm.reset();
                resetProductFormValidation();
            }
        });
    }

    // Function to reset required attributes and hide error messages for product form
    function resetProductFormValidation() {
        const productFormFields = createProductForm.querySelectorAll('select, input');
        productFormFields.forEach(field => {
            field.required = false; // Set all to false, then selectively enable
            field.classList.remove('border-red-500'); // Remove red borders
        });
        const errorMessages = createProductForm.querySelectorAll('.text-red-500');
        errorMessages.forEach(msg => msg.classList.add('hidden'));

        // Re-enable required for 'create product' fields by default
        if (actionTypeInput.value === 'create_product') {
            if (productNameSelect) productNameSelect.required = true;
            if (quantityField && !quantityField.classList.contains('hidden')) {
                const quantityInput = quantityField.querySelector('input');
                if (quantityInput) quantityInput.required = true;
            }
            if (dimensionFields && !dimensionFields.classList.contains('hidden')) {
                const lengthInput = dimensionFields.querySelector('#length');
                const widthInput = dimensionFields.querySelector('#width');
                if (lengthInput) lengthInput.required = true;
                if (widthInput) widthInput.required = true;
            }
            if (weightField && !weightField.classList.contains('hidden')) {
                const weightInput = weightField.querySelector('#weight');
                if (weightInput) weightInput.required = true;
            }
        }
    }


    // Handle toggle between Create Product and Reassign Task
    if (toggleReassign) {
        toggleReassign.addEventListener('change', async function() {
            if (this.checked) {
                actionTypeInput.value = 'reassign_task';
                reassignTaskSection.classList.remove('hidden');
                if (productNameSelect) productNameSelect.closest('.mb-4')?.classList.add('hidden');
                dimensionFields.classList.add('hidden');
                weightField.classList.add('hidden');
                quantityField.classList.add('hidden');
                
                // Load production lines for reassignment
                await loadProductionLines(reassignProdLineSelect);
                // Load members for reassignment selects
                await Promise.all([
                    loadMembers('knotter', reassignKnotterSelect),
                    loadMembers('warper', reassignWarperSelect),
                    loadMembers('weaver', reassignWeaverSelect)
                ]);

                // Reset required for product creation fields
                resetProductFormValidation();
                // Set required for reassign fields (initially only prod line select)
                reassignProdLineSelect.required = true;

            } else {
                actionTypeInput.value = 'create_product';
                reassignTaskSection.classList.add('hidden');
                if (productNameSelect) productNameSelect.closest('.mb-4')?.classList.remove('hidden');
                // Re-evaluate product fields based on selected product name
                updateProductFields(); 
                
                // Reset required for reassign fields
                reassignProdLineSelect.required = false;
                [reassignKnotterSelect, reassignWarperSelect, reassignWeaverSelect].forEach(select => {
                    if (select) select.required = false;
                });
                [reassignKnotterDeadline, reassignWarperDeadline, reassignWeaverDeadline].forEach(input => {
                    if (input) input.required = false;
                });

                // Set required for product creation fields
                resetProductFormValidation();
            }
        });
    }

    // Handle selection of production line for reassignment
    if (reassignProdLineSelect) {
        reassignProdLineSelect.addEventListener('change', async function() {
            const selectedProdLineId = this.value;
            if (selectedProdLineId) {
                try {
                    // Fetch product details for the selected production line
                    const prodResponse = await fetch(`backend/end-points/get_production_item.php?prod_line_id=${selectedProdLineId}`);
                    const prodData = await prodResponse.json();

                    if (!prodData.success) {
                        Swal.fire('Error', prodData.message || 'Failed to fetch product details.', 'error');
                        return;
                    }
                    const product = prodData.product;
                    
                    // Fetch existing assignments for this production line
                    const assignResponse = await fetch(`backend/end-points/get_assignments.php?prod_line_id=${selectedProdLineId}`);
                    const assignData = await assignResponse.json();

                    if (!assignData.success) {
                        Swal.fire('Error', assignData.message || 'Failed to fetch assignments.', 'error');
                        return;
                    }
                    const existingAssignments = assignData.assignments;

                    // Hide all reassignment role sections first
                    [reassignKnotterSection, reassignWarperSection, reassignWeaverSection].forEach(section => {
                        if (section) section.classList.add('hidden');
                        const select = section.querySelector('select');
                        const deadline = section.querySelector('input[type="datetime-local"]');
                        if (select) select.required = false;
                        if (deadline) deadline.required = false;
                    });

                    // Show appropriate reassignment role section based on product type
                    if (product.product_name === 'Knotted Liniwan' || product.product_name === 'Knotted Bastos') {
                        if (reassignKnotterSection) reassignKnotterSection.classList.remove('hidden');
                        if (reassignKnotterSelect) reassignKnotterSelect.required = true;
                        if (reassignKnotterDeadline) reassignKnotterDeadline.required = true;

                        const assignment = existingAssignments.find(a => a.role === 'knotter');
                        if (assignment) {
                            reassignKnotterSelect.value = assignment.member_id;
                            reassignKnotterDeadline.value = assignment.deadline.substring(0, 16);
                        } else {
                            reassignKnotterSelect.value = '';
                            reassignKnotterDeadline.value = '';
                        }
                    } else if (product.product_name === 'Warped Silk') {
                        if (reassignWarperSection) reassignWarperSection.classList.remove('hidden');
                        if (reassignWarperSelect) reassignWarperSelect.required = true;
                        if (reassignWarperDeadline) reassignWarperDeadline.required = true;

                        const assignment = existingAssignments.find(a => a.role === 'warper');
                        if (assignment) {
                            reassignWarperSelect.value = assignment.member_id;
                            reassignWarperDeadline.value = assignment.deadline.substring(0, 16);
                        } else {
                            reassignWarperSelect.value = '';
                            reassignWarperDeadline.value = '';
                        }
                    } else if (product.product_name === 'Piña Seda' || product.product_name === 'Pure Piña Cloth') {
                        if (reassignWeaverSection) reassignWeaverSection.classList.remove('hidden');
                        if (reassignWeaverSelect) reassignWeaverSelect.required = true;
                        if (reassignWeaverDeadline) reassignWeaverDeadline.required = true;

                        const assignment = existingAssignments.find(a => a.role === 'weaver');
                        if (assignment) {
                            reassignWeaverSelect.value = assignment.member_id;
                            reassignWeaverDeadline.value = assignment.deadline.substring(0, 16);
                        } else {
                            reassignWeaverSelect.value = '';
                            reassignWeaverDeadline.value = '';
                        }
                    }
                } catch (error) {
                    console.error('Error handling production line selection:', error);
                    Swal.fire('Error', 'An error occurred while loading product details or assignments.', 'error');
                }
            } else {
                // Reset role sections if no product is selected
                [reassignKnotterSection, reassignWarperSection, reassignWeaverSection].forEach(section => {
                    if (section) section.classList.add('hidden');
                    const select = section.querySelector('select');
                    const deadline = section.querySelector('input[type="datetime-local"]');
                    if (select) select.required = false;
                    if (deadline) deadline.required = false;
                    if (select) select.value = '';
                    if (deadline) deadline.value = '';
                });
            }
        });
    }

    // Original updateProductFields function (modified to work with createProductForm)
    function updateProductFields() {
        // Reset required attributes for all original product creation fields first
        const originalProductFields = [productNameSelect, 
                                        dimensionFields?.querySelector('#length'), 
                                        dimensionFields?.querySelector('#width'), 
                                        weightField?.querySelector('#weight'), 
                                        quantityField?.querySelector('input')];
        originalProductFields.forEach(field => {
            if (field) field.required = false;
        });

        const selected = productNameSelect ? productNameSelect.value : '';
        if (selected === 'Piña Seda' || selected === 'Pure Piña Cloth') {
            if (dimensionFields) dimensionFields.classList.remove('hidden');
            if (weightField) weightField.classList.add('hidden');
            if (quantityField) quantityField.classList.remove('hidden');
            if (dimensionFields?.querySelector('#length')) dimensionFields.querySelector('#length').required = true;
            if (dimensionFields?.querySelector('#width')) dimensionFields.querySelector('#width').required = true;
            if (quantityField?.querySelector('input')) quantityField.querySelector('input').required = true;
        } else if (selected === 'Knotted Liniwan' || selected === 'Knotted Bastos' || selected === 'Warped Silk') {
            if (dimensionFields) dimensionFields.classList.add('hidden');
            if (weightField) weightField.classList.remove('hidden');
            if (quantityField) quantityField.classList.add('hidden');
            if (weightField?.querySelector('#weight')) weightField.querySelector('#weight').required = true;
        } else {
            // Default state or no product selected, all fields hidden or optional
            if (dimensionFields) dimensionFields.classList.add('hidden');
            if (weightField) weightField.classList.add('hidden');
            if (quantityField) quantityField.classList.add('hidden');
        }
        if (productNameSelect) productNameSelect.required = true;
    }

    if (productNameSelect) {
        productNameSelect.addEventListener('change', updateProductFields);
    }

    // Call updateProductFields initially if create product is the default view
    if (actionTypeInput.value === 'create_product') {
        updateProductFields();
    }


    // Modify createProductForm submission handler
    if (createProductForm) {
        createProductForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = createProductForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const formData = new FormData(this);
            const currentActionType = formData.get('action_type');

            // Hide all error messages first
            const errorMessages = createProductForm.querySelectorAll('.text-red-500');
            errorMessages.forEach(msg => msg.classList.add('hidden'));

            let isValid = true;

            if (currentActionType === 'create_product') {
                // Validate product creation fields
                if (productNameSelect && !productNameSelect.value) {
                    isValid = false;
                    productNameSelect.classList.add('border-red-500');
                }
                if (dimensionFields && !dimensionFields.classList.contains('hidden')) {
                    const lengthInput = dimensionFields.querySelector('#length');
                    const widthInput = dimensionFields.querySelector('#width');
                    if (lengthInput && !lengthInput.value) { isValid = false; lengthInput.classList.add('border-red-500'); }
                    if (widthInput && !widthInput.value) { isValid = false; widthInput.classList.add('border-red-500'); }
                }
                if (weightField && !weightField.classList.contains('hidden')) {
                    const weightInput = weightField.querySelector('#weight');
                    if (weightInput && !weightInput.value) { isValid = false; weightInput.classList.add('border-red-500'); }
                }
                if (quantityField && !quantityField.classList.contains('hidden')) {
                    const quantityInput = quantityField.querySelector('input');
                    if (quantityInput && !quantityInput.value) { isValid = false; quantityInput.classList.add('border-red-500'); }
                }
            } else if (currentActionType === 'reassign_task') {
                // Validate reassignment fields
                if (reassignProdLineSelect && !reassignProdLineSelect.value) {
                    isValid = false;
                    reassignProdLineSelect.classList.add('border-red-500');
                }

                // Check visible reassignment role sections
                const reassignSections = [reassignKnotterSection, reassignWarperSection, reassignWeaverSection];
                reassignSections.forEach(section => {
                    if (section && !section.classList.contains('hidden')) {
                        const select = section.querySelector('select');
                        const deadline = section.querySelector('input[type="datetime-local"]');
                        if (select && !select.value) { isValid = false; select.classList.add('border-red-500'); }
                        if (deadline && !deadline.value) { isValid = false; deadline.classList.add('border-red-500'); }
                    }
                });
            }

            if (!isValid) {
                Swal.fire('Validation Error', 'Please fill in all required fields.', 'error');
                if (submitBtn) submitBtn.disabled = false;
                return;
            }

            let endpoint = '';
            let successMessage = '';
            let errorMessage = '';

            if (currentActionType === 'create_product') {
                endpoint = 'backend/end-points/create_production_item.php';
                successMessage = 'Product created successfully!';
                errorMessage = 'Failed to create product';
            } else if (currentActionType === 'reassign_task') {
                endpoint = 'backend/end-points/assign_tasks.php';
                // Add prod_line_id and is_reassignment flag to formData for assign_tasks.php
                formData.append('prod_line_id', formData.get('reassign_prod_line_id'));
                formData.append('is_reassignment', 'true');
                // Also need to get product_name for assign_tasks.php if required
                // For reassign, the product_name for assign_tasks will come from the selected reassign_prod_line_id's product name
                // This might require another fetch or storing the product name when loading the dropdown
                
                // For simplicity, let's just append a dummy product_details for now if assign_tasks.php requires it
                formData.append('product_details', 'Reassigned Task Product'); // Placeholder

                // Map reassign fields to assign_tasks.php expected fields
                if (reassignKnotterSelect && !reassignKnotterSection.classList.contains('hidden')) {
                    formData.append('knotter_id[]', reassignKnotterSelect.value);
                    formData.append('deadline[]', reassignKnotterDeadline.value);
                }
                if (reassignWarperSelect && !reassignWarperSection.classList.contains('hidden')) {
                    formData.append('warper_id', reassignWarperSelect.value);
                    formData.append('warper_deadline', reassignWarperDeadline.value);
                }
                if (reassignWeaverSelect && !reassignWeaverSection.classList.contains('hidden')) {
                    formData.append('weaver_id', reassignWeaverSelect.value);
                    formData.append('weaver_deadline', reassignWeaverDeadline.value);
                }

                successMessage = 'Task reassigned successfully!';
                errorMessage = 'Failed to reassign task';
            }

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || successMessage,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        if (productModal) productModal.classList.add('hidden');
                        if (createProductForm) createProductForm.reset();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || errorMessage
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred. Please try again.'
                });
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    if (productNameSelect) {
        productNameSelect.addEventListener('change', updateProductFields);
    }

    // Call updateProductFields initially if create product is the default view
    if (actionTypeInput.value === 'create_product') {
        updateProductFields();
    }

});

