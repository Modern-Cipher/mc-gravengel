document.addEventListener('DOMContentLoaded', function() {
    const addStaffForm = document.getElementById('addStaffForm');
    const userSearchInput = document.getElementById('user-search');
    const userTableBody = document.querySelector('#user-table tbody');
    const editUserModal = document.getElementById('editUserModal');
    const editUserForm = document.getElementById('editUserForm');

    // Regex for validation
    const usernameRegex = /^[a-zA-Z0-9]+$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^09\d{2} \d{3} \d{4}$/;

    // Function to handle form submission (add/edit)
    function handleFormSubmit(e, form, url, isEdit = false) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Simple validation
        if (!data.first_name || !data.last_name || !data.username || !data.email || !data.phone || !data.staff_id || !data.designation) {
            Swal.fire('Error', 'Please fill in all required fields.', 'error');
            return;
        }

        // Regex validation
        if (!usernameRegex.test(data.username)) {
            Swal.fire('Error', 'Username must only contain letters and numbers.', 'error');
            return;
        }

        if (!emailRegex.test(data.email)) {
            Swal.fire('Error', 'Please enter a valid email address.', 'error');
            return;
        }

        if (!phoneRegex.test(data.phone)) {
            Swal.fire('Error', 'Please enter a valid phone number in the format 0912 345 6789.', 'error');
            return;
        }

        // Show loading alert
        Swal.fire({
            title: isEdit ? 'Saving changes...' : 'Creating account...',
            html: 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => Swal.showLoading(),
        });

        // Submit the form data via fetch
        fetch(url, {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(result => {
            Swal.close();
            if (result.success) {
                if (isEdit) {
                    Swal.fire('Saved!', 'User details have been updated.', 'success').then(() => window.location.reload());
                } else {
                    Swal.fire({
                        title: 'Account Created!',
                        html: `
                            <p>Staff account for ${result.user.full_name} has been created successfully.</p>
                            <p>Temporary Password: <strong>${result.temp_password}</strong></p>
                            <button id="copy-temp-password" class="btn btn-sm btn-outline-secondary mt-2">
                                <i class="fas fa-copy"></i> Copy Password
                            </button>
                        `,
                        icon: 'success',
                        showConfirmButton: true,
                        allowOutsideClick: false,
                    }).then(() => window.location.reload());
                }
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
            console.error('Form submission error:', error);
        });
    }

    if (addStaffForm) {
        addStaffForm.addEventListener('submit', (e) => handleFormSubmit(e, addStaffForm, `${window.URLROOT}/admin/addStaff`));
    }
    
    if (editUserForm) {
        editUserForm.addEventListener('submit', (e) => handleFormSubmit(e, editUserForm, `${window.URLROOT}/admin/updateStaff`, true));
    }

    // Populate Edit Modal
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userData = JSON.parse(button.dataset.user);
            
            this.querySelector('#edit-user-id').value = userData.id;
            this.querySelector('#edit-first_name').value = userData.first_name;
            this.querySelector('#edit-last_name').value = userData.last_name;
            this.querySelector('#edit-username').value = userData.username;
            this.querySelector('#edit-email').value = userData.email;
            this.querySelector('#edit-phone').value = userData.phone || '';
            this.querySelector('#edit-staff_id').value = userData.staff_id || '';
            this.querySelector('#edit-designation').value = userData.designation || '';
        });
    }

    // Dynamic search functionality
    if (userSearchInput) {
        userSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = userTableBody.querySelectorAll('tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});