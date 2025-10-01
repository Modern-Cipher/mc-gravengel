document.addEventListener('DOMContentLoaded', function() {
    const addStaffForm     = document.getElementById('addStaffForm');
    const userSearchInput  = document.getElementById('user-search');
    const userTableBody    = document.querySelector('#user-table tbody');
    const editUserModal    = document.getElementById('editUserModal');
    const editUserForm     = document.getElementById('editUserForm');

    // Regex for validation (retain your rules)
    const usernameRegex = /^[a-zA-Z0-9]+$/;
    const emailRegex    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex    = /^09\d{2} \d{3} \d{4}$/;

    // ---------- helpers ----------
    const val = (x) => (x === undefined || x === null ? '' : String(x));

    function getFormObject(form) {
        const fd = new FormData(form);
        return Object.fromEntries(fd.entries());
    }

    function validate(data) {
        if (!data.first_name || !data.last_name || !data.username || !data.email || !data.phone || !data.staff_id || !data.designation) {
            return 'Please fill in all required fields.';
        }
        if (!usernameRegex.test(data.username)) return 'Username must only contain letters and numbers.';
        if (!emailRegex.test(data.email)) return 'Please enter a valid email address.';
        if (!phoneRegex.test(data.phone)) return 'Please enter a valid phone number in the format 0912 345 6789.';
        return '';
    }

    function diffObjects(current, original) {
        const changed = {};
        Object.keys(current).forEach(k => {
            if (val(current[k]).trim() !== val(original[k]).trim()) {
                changed[k] = current[k];
            }
        });
        return changed;
    }

    // ---------- submit handler (add / edit) ----------
    function handleFormSubmit(e, form, url, isEdit = false) {
        e.preventDefault();

        const data = getFormObject(form);

        // EDIT: check changes first
        if (isEdit) {
            // snapshot from modal open
            let original = {};
            try { original = JSON.parse(form.dataset.original || '{}'); } catch { original = {}; }

            const changed = diffObjects(data, original);

            // Remove id from diff (we'll always send it separately)
            delete changed.id;
            delete changed.user_id;

            if (Object.keys(changed).length === 0) {
                Swal.fire('No changes', 'Nothing to save.', 'info');
                return;
            }

            // Validate merged values (original + changed)
            const toValidate = { ...original, ...changed };
            const err = validate(toValidate);
            if (err) { Swal.fire('Error', err, 'error'); return; }

            const fd = new FormData();
            // ID from hidden input
            const id = form.querySelector('#edit-user-id')?.value || original.user_id || original.id || '';
            fd.append('id', id);
            for (const [k, v] of Object.entries(changed)) fd.append(k, v);

            Swal.fire({
                title: 'Saving changes...',
                html: 'Please wait...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading(),
            });

            fetch(url, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(result => {
                    Swal.close();
                    if (result.success) {
                        const msg = result.message || 'User details have been updated.';
                        Swal.fire('Saved!', msg, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', result.message || 'An unexpected error occurred. Please try again.', 'error');
                    }
                })
                .catch(err => {
                    Swal.close();
                    Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
                    console.error('updateStaff error:', err);
                });

            return;
        }

        // ADD path (full form submit)
        const err = validate(data);
        if (err) { Swal.fire('Error', err, 'error'); return; }

        Swal.fire({
            title: 'Creating account...',
            html: 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => Swal.showLoading(),
        });

        fetch(url, { method: 'POST', body: new FormData(form) })
            .then(r => r.json())
            .then(result => {
                Swal.close();
                if (result.success) {
                    Swal.fire({
                        title: 'Account Created!',
                        html: `
                            <p>Staff account for ${result.user?.full_name || 'user'} has been created successfully.</p>
                            <p>Temporary Password: <strong>${result.temp_password || ''}</strong></p>
                            <button id="copy-temp-password" class="btn btn-sm btn-outline-secondary mt-2">
                                <i class="fas fa-copy"></i> Copy Password
                            </button>
                        `,
                        icon: 'success',
                        showConfirmButton: true,
                        allowOutsideClick: false,
                    }).then(() => window.location.reload());

                    // copy-to-clipboard
                    setTimeout(() => {
                        const btn = document.getElementById('copy-temp-password');
                        if (btn) {
                            btn.addEventListener('click', () => {
                                navigator.clipboard.writeText(result.temp_password || '').then(() => {
                                    btn.innerHTML = '<i class="fas fa-check"></i> Copied';
                                });
                            });
                        }
                    }, 0);
                } else {
                    Swal.fire('Error', result.message || 'An unexpected error occurred. Please try again.', 'error');
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
                console.error('addStaff error:', err);
            });
    }

    // ---------- wire up ----------
    if (addStaffForm)  addStaffForm.addEventListener('submit', (e) => handleFormSubmit(e, addStaffForm, `${window.URLROOT}/admin/addStaff`));
    if (editUserForm)  editUserForm.addEventListener('submit', (e) => handleFormSubmit(e, editUserForm, `${window.URLROOT}/admin/updateStaff`, true));

    // Populate Edit Modal + snapshot original for diff
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function(event) {
            const button   = event.relatedTarget;
            const userData = JSON.parse(button.dataset.user || '{}');

            this.querySelector('#edit-user-id').value       = val(userData.id);
            this.querySelector('#edit-first_name').value    = val(userData.first_name);
            this.querySelector('#edit-last_name').value     = val(userData.last_name);
            this.querySelector('#edit-username').value      = val(userData.username);
            this.querySelector('#edit-email').value         = val(userData.email);
            this.querySelector('#edit-phone').value         = val(userData.phone || '');
            this.querySelector('#edit-staff_id').value      = val(userData.staff_id || '');
            this.querySelector('#edit-designation').value   = val(userData.designation || '');

            // snapshot for diff
            const original = {
                user_id:     val(userData.id),
                first_name:  val(userData.first_name),
                last_name:   val(userData.last_name),
                username:    val(userData.username),
                email:       val(userData.email),
                phone:       val(userData.phone || ''),
                staff_id:    val(userData.staff_id || ''),
                designation: val(userData.designation || '')
            };
            editUserForm.dataset.original = JSON.stringify(original);
        });
    }

    // Dynamic search
    if (userSearchInput && userTableBody) {
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
