// Function to load members for a specific role
async function loadMembers(role, selectElement) {
    try {
        const response = await fetch('backend/end-points/get_members_by_role.php?role=' + role);
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
        }
    } catch (error) {
        console.error('Error loading members:', error);
    }
}

// Function to handle task assignment
function assignTask(prodLineId, productName, quantity) {
    // Set the production line ID in the form
    const identifierInput = document.getElementById('identifier');
    const prodLineIdInput = document.getElementById('prod_line_id');
    if (identifierInput) identifierInput.value = prodLineId;
    if (prodLineIdInput) prodLineIdInput.value = prodLineId;

    // Get modal sections
    const knotterSection = document.getElementById('knotterSection');
    const warperSection = document.getElementById('warperSection');
    const weaverSection = document.getElementById('weaverSection');
    const assignBtn = document.querySelector('#taskAssignmentForm button[type="submit"]');

    // Hide all sections and error messages by default
    if (knotterSection) {
        knotterSection.classList.add('hidden');
        const knotterError = document.getElementById('knotterError');
        if (knotterError) knotterError.classList.add('hidden');
    }
    if (warperSection) {
        warperSection.classList.add('hidden');
        const warperError = document.getElementById('warperError');
        if (warperError) warperError.classList.add('hidden');
    }
    if (weaverSection) {
        weaverSection.classList.add('hidden');
        const weaverError = document.getElementById('weaverError');
        if (weaverError) weaverError.classList.add('hidden');
    }
    if (assignBtn) assignBtn.disabled = false;

    // Get select elements
    const knotterSelects = document.querySelectorAll('.knotter-select');
    const warperSelect = document.querySelector('select[name="warper_id"]');
    const weaverSelect = document.querySelector('select[name="weaver_id"]');

    // Reset form fields
    const form = document.getElementById('taskAssignmentForm');
    if (form) form.reset();

    // Load members for each role
    knotterSelects.forEach(select => loadMembers('knotter', select));
    if (warperSelect) loadMembers('warper', warperSelect);
    if (weaverSelect) loadMembers('weaver', weaverSelect);

    // Show appropriate sections based on product type
    if (productName === 'Knotted Liniwan' || productName === 'Knotted Bastos') {
        // Only show knotter section for knotted products
        if (knotterSection) {
            knotterSection.classList.remove('hidden');
            // Make knotter fields required
            const knotterDeadline = knotterSection.querySelector('input[name="deadline"]');
            const knotterSelect = knotterSection.querySelector('select[name="knotter_id[]"]');
            
            if (knotterDeadline) knotterDeadline.required = true;
            if (knotterSelect) knotterSelect.required = true;
        }
    } else if (productName === 'Warped Silk') {
        // Only show warper section for Warped Silk
        if (warperSection) {
            warperSection.classList.remove('hidden');
            // Make warper fields required
            const warperDeadline = warperSection.querySelector('input[name="warper_deadline"]');
            const warperSelect = warperSection.querySelector('select[name="warper_id"]');
            
            if (warperDeadline) warperDeadline.required = true;
            if (warperSelect) warperSelect.required = true;
        }
    } else if (productName === 'Piña Seda' || productName === 'Pure Piña Cloth') {
        // Only show weaver section for Piña Seda and Pure Piña Cloth
        if (weaverSection) {
            weaverSection.classList.remove('hidden');
            // Make weaver fields required
            const weaverDeadline = weaverSection.querySelector('input[name="weaver_deadline"]');
            const weaverSelect = weaverSection.querySelector('select[name="weaver_id"]');
            
            if (weaverDeadline) weaverDeadline.required = true;
            if (weaverSelect) weaverSelect.required = true;
        }
    }

    // Show the modal
    const modal = document.getElementById('taskAssignmentModal');
    if (modal) modal.classList.remove('hidden');
}

// Add event listeners for modal close buttons
document.addEventListener('DOMContentLoaded', function() {
    // Task Assignment Modal
    const taskModal = document.getElementById('taskAssignmentModal');
    const cancelTaskBtn = document.getElementById('cancelTaskBtn');
    const taskForm = document.getElementById('taskAssignmentForm');

    if (taskModal) {
        // Close modal when clicking outside
        taskModal.addEventListener('click', function(e) {
            if (e.target === taskModal) {
                taskModal.classList.add('hidden');
                if (taskForm) taskForm.reset();
                // Hide all error messages
                const errorMessages = taskModal.querySelectorAll('.text-red-500');
                errorMessages.forEach(msg => msg.classList.add('hidden'));
            }
        });
    }
    
    if (cancelTaskBtn && taskModal) {
        cancelTaskBtn.addEventListener('click', function() {
            taskModal.classList.add('hidden');
            if (taskForm) taskForm.reset();
            // Hide all error messages
            const errorMessages = taskModal.querySelectorAll('.text-red-500');
            errorMessages.forEach(msg => msg.classList.add('hidden'));
        });
    }

    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide all error messages first
            const errorMessages = taskForm.querySelectorAll('.text-red-500');
            errorMessages.forEach(msg => msg.classList.add('hidden'));

            // Get visible sections
            const visibleSections = Array.from(taskForm.querySelectorAll('.space-y-2')).filter(
                section => !section.classList.contains('hidden')
            );

            // Validate each visible section
            let isValid = true;
            visibleSections.forEach(section => {
                const select = section.querySelector('select');
                const deadline = section.querySelector('input[type="datetime-local"]');
                const error = section.querySelector('.text-red-500');

                if (select && deadline) {
                    if (!select.value || !deadline.value) {
                        isValid = false;
                        if (error) error.classList.remove('hidden');
                    }
                }
            });

            if (!isValid) {
                return;
            }

            const submitBtn = taskForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const formData = new FormData(taskForm);

            fetch('backend/end-points/assign_tasks.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
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
                        // Refresh the page or update the UI as needed
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
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while assigning tasks'
                });
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }

    // Product Creation Modal
    const createBtn = document.getElementById('createProductBtn');
    const productModal = document.getElementById('createProductModal');
    const productForm = document.getElementById('productForm');
    const cancelCreateBtn = document.getElementById('cancelCreateBtn');

    if (createBtn && productModal) {
    createBtn.addEventListener('click', function() {
            productModal.classList.remove('hidden');
        });
    }
    
    if (cancelCreateBtn && productModal) {
        cancelCreateBtn.addEventListener('click', function() {
            productModal.classList.add('hidden');
            if (productForm) productForm.reset();
        });
    }
    
    if (productModal) {
        // Close modal when clicking outside
        productModal.addEventListener('click', function(e) {
            if (e.target === productModal) {
                productModal.classList.add('hidden');
                if (productForm) productForm.reset();
            }
    });
    }
    
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
            const submitBtn = productForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            
            const formData = new FormData(productForm);
            
            fetch('backend/end-points/create_production_item.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Product created successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        if (productModal) productModal.classList.add('hidden');
                        if (productForm) productForm.reset();
                        // Refresh the page to show the new product
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to create product'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while creating the product'
                });
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }

    const productNameSelect = document.getElementById('productNameSelect');
    const lengthField = document.getElementById('lengthField');
    const widthField = document.getElementById('widthField');
    const weightField = document.getElementById('weightField');
    // By default, show all fields
    function updateProductFields() {
        const selected = productNameSelect ? productNameSelect.value : '';
        if (selected === 'Piña Seda' || selected === 'Pure Piña Cloth') {
            if (lengthField) lengthField.style.display = '';
            if (widthField) widthField.style.display = '';
            if (weightField) weightField.style.display = 'none';
        } else if (selected === 'Piña Liniwan' || selected === 'Piña Bastos') {
            if (lengthField) lengthField.style.display = 'none';
            if (widthField) widthField.style.display = 'none';
            if (weightField) weightField.style.display = '';
        } else {
            if (lengthField) lengthField.style.display = '';
            if (widthField) widthField.style.display = '';
            if (weightField) weightField.style.display = '';
        }
    }
    if (productNameSelect) {
        productNameSelect.addEventListener('change', updateProductFields);
        updateProductFields();
        }
});
