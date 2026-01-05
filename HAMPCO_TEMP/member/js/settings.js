document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.settings-tab');
    const contents = document.querySelectorAll('.settings-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const tabName = this.getAttribute('data-tab');

            tabs.forEach(t => {
                t.classList.remove('text-white', 'font-medium', 'bg-yellow-500');
                t.classList.add('hover:bg-gray-50', 'text-gray-700');
                t.style.backgroundColor = 'transparent';
            });

            this.classList.add('text-white', 'font-medium', 'bg-yellow-500');
            this.classList.remove('hover:bg-gray-50', 'text-gray-700');
            this.style.backgroundColor = '#D4AF37';


            contents.forEach(content => content.classList.add('hidden'));
            document.getElementById(tabName + '-tab').classList.remove('hidden');
        });
    });

    const handleFormSubmit = async (form, action, body) => {
        try {
            const response = await fetch('backend/end-points/update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&${body}`
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Server error: ${response.status} ${response.statusText}. ${errorText}`);
            }
            
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000
                }).then(() => {
                    if (action === 'update_password') {
                        form.reset();
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'An unknown error occurred.'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'An error occurred while processing your request.'
            });
        }
    };

    document.getElementById('profileForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fullname = document.getElementById('fullname').value;
        const role = document.getElementById('role').value;
        const body = `fullname=${encodeURIComponent(fullname)}&role=${encodeURIComponent(role)}`;
        handleFormSubmit(this, 'update_profile', body);
    });

    document.getElementById('passwordForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            Swal.fire({
                icon: 'warning',
                title: 'Mismatch',
                text: 'New passwords do not match'
            });
            return;
        }

        if (newPassword.length < 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Weak Password',
                text: 'Password must be at least 6 characters'
            });
            return;
        }

        const body = `currentPassword=${encodeURIComponent(currentPassword)}&newPassword=${encodeURIComponent(newPassword)}`;
        handleFormSubmit(this, 'update_password', body);
    });

    document.getElementById('contactForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const phone = document.getElementById('phone').value;
        const body = `phone=${encodeURIComponent(phone)}`;
        handleFormSubmit(this, 'update_contact', body);
    });

    // Cancel buttons
    document.getElementById('cancelProfile').addEventListener('click', function () {
        document.getElementById('profileForm').reset();
    });

    document.getElementById('cancelPassword').addEventListener('click', function () {
        document.getElementById('passwordForm').reset();
    });

    document.getElementById('cancelContact').addEventListener('click', function () {
        document.getElementById('contactForm').reset();
    });

    // Password strength indicator
    document.getElementById('newPassword').addEventListener('input', function () {
        const password = this.value;
        const strengthBars = document.querySelectorAll('#passwordStrength > div:last-child > div');
        let strength = 0;

        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password) && /[!@#$%^&*]/.test(password)) strength++;

        const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];

        strengthBars.forEach((bar, index) => {
            if (index < strength) {
                bar.classList.remove('bg-gray-300');
                bar.classList.add(colors[strength - 1]);
            } else {
                bar.classList.add('bg-gray-300');
                bar.classList.remove(...colors);
            }
        });
    });
});