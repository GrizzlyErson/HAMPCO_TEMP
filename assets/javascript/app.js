$(document).ready(function() {
    // Check for autofill parameters in URL
    const urlParams = new URLSearchParams(window.location.search);
    const autoFillId = urlParams.get('id');
    if (autoFillId) {
        $('#id_number').val(autoFillId);
        // Focus on password field if ID is autofilled
        $('#password').focus();
    }
});

$("#frmLogin_Admin").submit(function (e) { 
    e.preventDefault();
    $('.spinner').show();
    var formData = $(this).serializeArray(); 
    formData.push({ name: 'requestType', value: 'Login_Admin' });
    var serializedData = $.param(formData);

    $.ajax({
        type: "POST",
        url: "backend/end-points/controller.php",
        data: serializedData,
        dataType: "json", 
        success: function (response) {
            if (response.status === 'success') {
                alertify.success(response.message);  
                setTimeout(function () {
                    window.location.href = "admin/admin_dashboard"; 
                }, 1000);
            } else {
                alertify.error(response.message); 
                $('.spinner').hide();
            }
        }
    });
});



$("#FrmLogin_Member").submit(function (e) { 
    e.preventDefault();
    $('.spinner').show();
    var formData = $(this).serializeArray(); 
    formData.push({ name: 'requestType', value: 'LoginMember' });
    var serializedData = $.param(formData);

    $.ajax({
        type: "POST",
        url: "backend/end-points/controller.php",
        data: serializedData,
        dataType: "json", 
        success: function (response) {
            if (response.status === 'success') {
                alertify.success(response.message);  
                setTimeout(function () {
                    window.location.href = "member/member_dashboard"; 
                }, 1000);
            } else {
                alertify.error(response.message); 
                $('.spinner').hide();
            }
        }
    });
});













$("#FrmLogin_Customer").submit(function (e) { 
    e.preventDefault();
    $('.spinner').show();
    var formData = $(this).serializeArray(); 
    formData.push({ name: 'requestType', value: 'LoginCustomer' });
    var serializedData = $.param(formData);

    $.ajax({
        type: "POST",
        url: "backend/end-points/controller.php",
        data: serializedData,
        dataType: "json", 
        success: function (response) {
            if (response.status === 'success') {
                alertify.success(response.message);  
                setTimeout(function () {
                    window.location.href = "customer/customer_home_page"; 
                }, 1000);
            } else {
                alertify.error(response.message); 
                $('.spinner').hide();
            }
        }
    });
});



$("#FrmRegister_Member").submit(function (e) { 
    e.preventDefault();
    $('.spinner').show();

    var fname = $("#first-name").val().trim();
    var lname = $("#last-name").val().trim();
    var email = $("#email").val().trim();
    var phone = $("#phone").val().trim();
    var role = $("#role").val();
    var sex = $("#sex").val();
    var password = $("#password").val();
    var confirmPassword = $("#confirm-password").val();

    // Basic validation
    if (fname === '') {
        alertify.error("First Name is required.");
        $('.spinner').hide(); 
        return;
    }
    if (lname === '') {
        alertify.error("Last Name is required.");
        $('.spinner').hide();
        return;
    }
    if (email === '') {
        alertify.error("Email is required.");
        $('.spinner').hide();
        return;
    }
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alertify.error("Invalid email format.");
        $('.spinner').hide();
        return;
    }
    if (phone === '') {
        alertify.error("Phone number is required.");
        $('.spinner').hide();
        return;
    }
    if (phone.length < 7) {
        alertify.error("Phone number is too short.");
        $('.spinner').hide();
        return;
    }
    if (!role) {
        alertify.error("Please select a role.");
        $('.spinner').hide();
        return;
    }
    if (!sex) {
        alertify.error("Please select sex.");
        $('.spinner').hide();
        return;
    }
    if (password === '') {
        alertify.error("Password is required.");
        $('.spinner').hide();
        return;
    }
    if (password.length < 6) {
        alertify.error("Password must be at least 6 characters.");
        $('.spinner').hide();
        return;
    }
    if (confirmPassword === '') {
        alertify.error("Confirm Password is required.");
        $('.spinner').hide();
        return;
    }
    if (password !== confirmPassword) {
        alertify.error("Passwords do not match.");
        $('.spinner').hide();
        return;
    }

    // All validations passed
    var formData = $(this).serializeArray(); 
    formData.push({ name: 'requestType', value: 'RegisterMember' });
    var serializedData = $.param(formData);

    $.ajax({
        type: "POST",
        url: "backend/end-points/controller.php",
        data: serializedData,
        dataType: "json",
        success: function (response) {
            $('.spinner').hide();
            if (response.status === 'success') {
                // Extract member ID from the success message
                const memberIdMatch = response.message.match(/Member ID is: ([A-Z]+-\d{4}-\d{3})/);
                if (memberIdMatch && memberIdMatch[1]) {
                    const memberId = memberIdMatch[1];
                    
                    // Store credentials for quick login
                    sessionStorage.setItem('tempMemberId', memberId);
                    sessionStorage.setItem('tempPassword', password);
                    
                    // Show the popup with member ID
                    $('#member-id').text(memberId);
                    $('#success-popup').fadeIn(300);
                    
                    // Handle popup buttons
                    $('#login-now').off('click').on('click', function() {
                        const memberId = $('#member-id').text();
                        window.location.href = "login_member?id=" + encodeURIComponent(memberId);
                    });
                    
                    $('#close-popup').off('click').on('click', function() {
                        $('#success-popup').fadeOut(300);
                        window.location.href = "login_member";
                    });
                } else {
                    alertify.success(response.message);
                    setTimeout(function () {
                        window.location.href = "login_member";
                    }, 3000);
                }
            } else {
                alertify.error(response.message);
            }
        },
        error: function(xhr, status, error) {
            $('.spinner').hide();
            alertify.error("An error occurred. Please try again.");
            console.error(xhr, status, error);
        }
    });
});
















$("#FrmRegister_Customer").submit(function (e) { 
    e.preventDefault();
    $('.spinner').show();

    var fullname = $("#fullname").val();
    var email = $("#email").val().trim();
    var phone = $("#phone").val().trim();
    var password = $("#password").val();
    var confirmPassword = $("#confirm-password").val();

    // Basic validation
    if (fullname === '') {
        alertify.error("Customer Name is required.");
        $('.spinner').hide(); 
        return;
    }
    
    if (email === '') {
        alertify.error("Email is required.");
        $('.spinner').hide();
        return;
    }
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alertify.error("Invalid email format.");
        $('.spinner').hide();
        return;
    }
    if (phone === '') {
        alertify.error("Phone number is required.");
        $('.spinner').hide();
        return;
    }
    if (phone.length < 7) {
        alertify.error("Phone number is too short.");
        $('.spinner').hide();
        return;
    }
    

    if (password === '') {
        alertify.error("Password is required.");
        $('.spinner').hide();
        return;
    }
    if (password.length < 6) {
        alertify.error("Password must be at least 6 characters.");
        $('.spinner').hide();
        return;
    }
    if (confirmPassword === '') {
        alertify.error("Confirm Password is required.");
        $('.spinner').hide();
        return;
    }
    if (password !== confirmPassword) {
        alertify.error("Passwords do not match.");
        $('.spinner').hide();
        return;
    }

    // All validations passed
    var formData = $(this).serializeArray(); 
    formData.push({ name: 'requestType', value: 'RegisterCustomer' });
    var serializedData = $.param(formData);

    $.ajax({
        type: "POST",
        url: "backend/end-points/controller.php",
        data: serializedData,
        dataType: "json", 
        success: function (response) {
            if (response.status === 'success') {
                alertify.success(response.message);  
                setTimeout(function () {
                    window.location.href = "login_customer"; 
                }, 1000);
            } else {
                alertify.error(response.message); 
                $('.spinner').hide();
            }
        }
    });
});









