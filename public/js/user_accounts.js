// public/js/user_accounts.js
document.addEventListener('DOMContentLoaded', function () {
  const addStaffForm = document.getElementById('addStaffForm');
  const userSearchInput = document.getElementById('user-search');
  const userTableBody = document.querySelector('#user-table tbody');
  const editUserModal = document.getElementById('editUserModal');
  const editUserForm = document.getElementById('editUserForm');

  const usernameRegex = /^[a-zA-Z0-9]+$/;
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phoneRegex = /^09\d{2} \d{3} \d{4}$/; 

  const SwalMaroon = Swal.mixin({
    customClass: { 
      confirmButton: 'btn btn-maroon me-2', 
      cancelButton: 'btn btn-outline-secondary' 
    },
    buttonsStyling: false
  });

  const val = (x) => (x === undefined || x === null ? '' : String(x));
  const getFormObject = (form) => Object.fromEntries(new FormData(form).entries());
  
  const validateInput = (inputEl, regex) => {
    if (regex.test(inputEl.value)) {
      inputEl.classList.remove('is-invalid');
    } else {
      inputEl.classList.add('is-invalid');
    }
  };

  const formatPhoneNumber = (inputEl) => {
    let value = inputEl.value.replace(/\D/g, '');
    if (value.startsWith('9')) { value = '0' + value; }
    
    let formattedValue = '';
    if (value.length > 0) { formattedValue = value.substring(0, 4); }
    if (value.length > 4) { formattedValue += ' ' + value.substring(4, 7); }
    if (value.length > 7) { formattedValue += ' ' + value.substring(7, 11); }
    
    inputEl.value = formattedValue;
    validateInput(inputEl, phoneRegex);
  };
  
  document.querySelectorAll('input[name="phone"], input[id="edit-phone"]').forEach(phoneInput => {
      phoneInput.addEventListener('input', () => formatPhoneNumber(phoneInput));
  });

  document.querySelectorAll('input[name="email"], input[id="edit-email"]').forEach(emailInput => {
      emailInput.addEventListener('input', () => validateInput(emailInput, emailRegex));
  });

  addStaffForm?.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = getFormObject(this);

    if (Array.from(this.querySelectorAll('[required]')).some(el => !el.value.trim())) {
        return SwalMaroon.fire('Invalid Input', 'Please fill in all required fields.', 'error');
    }
    if (!usernameRegex.test(data.username)) return SwalMaroon.fire('Invalid Input', 'Username must only contain letters and numbers.', 'error');
    if (!emailRegex.test(data.email)) return SwalMaroon.fire('Invalid Input', 'Please enter a valid email address.', 'error');
    if (!phoneRegex.test(data.phone)) return SwalMaroon.fire('Invalid Input', 'Please enter a valid phone number in the format 0912 345 6789.', 'error');

    SwalMaroon.fire({ title:'Creating account...', html:'Please wait...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });

    fetch(`${window.URLROOT}/admin/addStaff`, { method:'POST', body:new FormData(this) })
      .then(r => r.json()).then(result => {
        Swal.close();
        if (result.success) {
          SwalMaroon.fire({
            title:'Account Created!',
            html:`<p>Staff account for <strong>${result.user?.full_name || 'user'}</strong> has been created.</p>
                  <p class='mt-3'>Temporary Password:</p>
                  <div class='input-group mt-1'>
                    <input type='text' class='form-control' value='${result.temp_password || ''}' readonly>
                    <button class='btn btn-outline-secondary' id='copy-temp-pw'><i class='fas fa-copy'></i></button>
                  </div>`,
            icon:'success',
            allowOutsideClick:false
          }).then(() => window.location.reload());
          
          document.getElementById('copy-temp-pw')?.addEventListener('click', (e) => {
              navigator.clipboard.writeText(result.temp_password || '');
              e.target.innerHTML = "<i class='fas fa-check'></i>";
          });
        } else {
          SwalMaroon.fire('Error', result.message || 'An unexpected error occurred.', 'error');
        }
      }).catch(err => {
        Swal.close(); 
        SwalMaroon.fire('Network Error', 'Could not connect to the server.', 'error');
        console.error('addStaff error:', err);
      });
  });
  
  editUserModal?.addEventListener('show.bs.modal', function (event) {
    const userData = JSON.parse(event.relatedTarget.dataset.user || '{}');
    this.querySelector('#edit-user-id').value       = val(userData.id);
    this.querySelector('#edit-first_name').value    = val(userData.first_name);
    this.querySelector('#edit-last_name').value     = val(userData.last_name);
    this.querySelector('#edit-username').value      = val(userData.username);
    this.querySelector('#edit-email').value         = val(userData.email);
    this.querySelector('#edit-phone').value         = val(userData.phone || '');
    this.querySelector('#edit-staff_id').value      = val(userData.staff_id || '');
    this.querySelector('#edit-designation').value   = val(userData.designation || '');
    this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  });
  
  editUserForm?.addEventListener('submit', function(e) {
      e.preventDefault();
      const data = getFormObject(this);
      if (!emailRegex.test(data.email)) return SwalMaroon.fire('Invalid Input', 'Please enter a valid email address.', 'error');
      if (!phoneRegex.test(data.phone)) return SwalMaroon.fire('Invalid Input', 'Please enter a valid phone number in the format 0912 345 6789.', 'error');
      
      SwalMaroon.fire({ title:'Saving changes...', html:'Please wait...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });

      fetch(`${window.URLROOT}/admin/updateStaff`, { method:'POST', body: new FormData(this) })
        .then(r => r.json()).then(result => {
          Swal.close();
          if (result.success) {
            SwalMaroon.fire('Saved!', result.message, 'success').then(() => window.location.reload());
          } else {
            SwalMaroon.fire('Error', result.message, 'error');
          }
        }).catch(err => {
            Swal.close();
            SwalMaroon.fire('Network Error', 'Could not connect to the server.', 'error');
            console.error('updateStaff error:', err);
        });
  });

  userSearchInput?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    userTableBody.querySelectorAll('tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });

  // --- [FIXED & COMPLETE] Event Delegation for Actions ---
  document.addEventListener('click', async (e) => {
    // RESET PASSWORD
    const resetBtn = e.target.closest('.reset-pwd-btn');
    if (resetBtn) {
        const userId = resetBtn.dataset.userId;
        const email = resetBtn.dataset.email;
        const { isConfirmed } = await SwalMaroon.fire({
            title: 'Send Reset Link?',
            html: `A password reset link will be emailed to <strong>${email}</strong>.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send It',
        });
        if (!isConfirmed) return;

        SwalMaroon.fire({ title:'Sending...', html:'Please wait...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });
        const fd = new FormData(); 
        fd.append('user_id', userId); 
        fd.append('email', email);
        
        fetch(`${window.URLROOT}/admin/resetPassword`, { method:'POST', body:fd })
          .then(r => r.json()).then(data => {
            Swal.close();
            if (data.success) SwalMaroon.fire('Email Sent!', data.message, 'success');
            else SwalMaroon.fire('Error', data.message, 'error');
          }).catch(err => {
            Swal.close();
            SwalMaroon.fire('Network Error', 'Could not connect to the server.', 'error');
          });
    }
  });
  
  document.addEventListener('change', async (e) => {
    // TOGGLE USER STATUS
    const toggle = e.target.closest('.user-toggle');
    if (toggle) {
        const userId = toggle.dataset.userId;
        const isChecked = toggle.checked;
        const nextState = isChecked ? 1 : 0;
        const actionTxt = nextState ? 'Activate' : 'Deactivate';

        const { isConfirmed } = await SwalMaroon.fire({
            title: `${actionTxt} Account?`,
            text: `Are you sure you want to ${actionTxt.toLowerCase()} this user account?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `Yes, ${actionTxt}`,
        });
        
        if (!isConfirmed) { toggle.checked = !isChecked; return; }

        SwalMaroon.fire({ title:'Updating...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });
        const fd = new FormData(); 
        fd.append('id', userId); 
        fd.append('is_active', nextState);
        
        fetch(`${window.URLROOT}/admin/setUserActive`, { method:'POST', body:fd })
          .then(r => r.json()).then(data => {
            Swal.close();
            if (data.success) {
                const badge = toggle.closest('td').querySelector('.badge');
                if (badge) { 
                    badge.className = `ms-2 badge ${nextState ? 'bg-maroon' : 'bg-secondary'}`; 
                    badge.textContent = nextState ? 'Active' : 'Inactive'; 
                }
                SwalMaroon.fire(nextState ? 'Activated' : 'Deactivated', data.message, 'success');
            } else { 
                toggle.checked = !isChecked; 
                SwalMaroon.fire('Error', data.message, 'error'); 
            }
          }).catch(err => {
            toggle.checked = !isChecked;
            Swal.close();
            SwalMaroon.fire('Network Error', 'Could not update status.', 'error');
          });
    }
  });
});