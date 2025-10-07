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

  // FIX: Maroon Themed SweetAlert2 Mixin
  const SwalMaroon = Swal.mixin({
    customClass: { 
      // Tiyakin na ang 'btn-maroon' CSS class ay defined mo sa CSS mo
      confirmButton: 'btn btn-maroon me-2', 
      cancelButton: 'btn btn-outline-secondary' 
    },
    buttonsStyling: false
  });

  const val = (x) => (x === undefined || x === null ? '' : String(x));
  const getFormObject = (form) => Object.fromEntries(new FormData(form).entries());

  function validate(data, opts = { requireStaffId: true }) {
    const required = ['first_name','last_name','username','email','phone','designation'];
    if (opts.requireStaffId) required.push('staff_id');
    for (const k of required) if (!data[k] || val(data[k]).trim() === '') return 'Please fill in all required fields.';
    if (!usernameRegex.test(data.username)) return 'Username must only contain letters and numbers.';
    if (!emailRegex.test(data.email)) return 'Please enter a valid email address.';
    if (!phoneRegex.test(data.phone)) return 'Please enter a valid phone number in the format 0912 345 6789.';
    return '';
  }

  const diffObjects = (current, original) => {
    const changed = {};
    Object.keys(current).forEach((k) => { 
      if (k !== 'id' && k !== 'user_id' && val(current[k]).trim() !== '' && val(current[k]).trim() !== val(original[k]).trim()) {
        changed[k] = current[k]; 
      }
    });
    return changed;
  };

  function handleFormSubmit(e, form, url, isEdit = false) {
    e.preventDefault();
    const data = getFormObject(form);

    if (isEdit) {
      let original = {};
      try { original = JSON.parse(form.dataset.original || '{}'); } catch { original = {}; }

      const changed = diffObjects(data, original);
      const id = form.querySelector('#edit-user-id')?.value || original.user_id || original.id || ''; 
      
      if (!id) { SwalMaroon.fire('Error', 'Missing user ID for update.', 'error'); return; }

      if (Object.keys(changed).length === 0) { SwalMaroon.fire('No changes','Nothing to save.','info'); return; }

      const err = validate({ ...original, ...changed }, { requireStaffId: true });
      if (err) { SwalMaroon.fire('Error', err, 'error'); return; }

      const fd = new FormData();
      fd.append('id', id); 
      for (const [k,v] of Object.entries(changed)) fd.append(k, v);

      SwalMaroon.fire({ title:'Saving changes...', html:'Please wait...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });

      fetch(url, { method:'POST', body:fd })
        .then(r=>r.json()).then(result=>{
          Swal.close();
          if (result.success) SwalMaroon.fire('Saved!', result.message || 'User details have been updated.', 'success').then(()=>window.location.reload());
          else SwalMaroon.fire('Error', result.message || 'An unexpected error occurred. Please try again.', 'error');
        }).catch(err=>{
          Swal.close(); SwalMaroon.fire('Error', 'An unexpected error occurred. Please try again.', 'error'); console.error('updateStaff error:', err);
        });
      return;
    }

    // ADD STAFF LOGIC
    const err = validate(data, { requireStaffId:false });
    if (err) { SwalMaroon.fire('Error', err, 'error'); return; }

    SwalMaroon.fire({ title:'Creating account...', html:'Please wait...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });

    fetch(url, { method:'POST', body:new FormData(form) })
      .then(r=>r.json()).then(result=>{
        Swal.close();
        if (result.success) {
          SwalMaroon.fire({
            title:'Account Created!',
            html:`<p>Staff account for ${result.user?.full_name || 'user'} has been created successfully.</p>
                  <p>Temporary Password: <strong>${result.temp_password || ''}</strong></p>
                  <button id="copy-temp-password" class="btn btn-sm btn-maroon mt-2"><i class="fas fa-copy"></i> Copy Password</button>`, // FIX: Ginawang btn-maroon
            icon:'success', showConfirmButton:true, allowOutsideClick:false
          }).then(()=>window.location.reload());

          setTimeout(()=>{
            const btn=document.getElementById('copy-temp-password');
            if(btn){ btn.addEventListener('click',()=>{ navigator.clipboard.writeText(result.temp_password || '').then(()=>{
              btn.innerHTML='<i class="fas fa-check"></i> Copied'; btn.classList.remove('btn-maroon'); btn.classList.add('btn-success');
            }); }); }
          },0);
        } else {
          SwalMaroon.fire('Error', result.message || 'An unexpected error occurred. Please try again.', 'error');
        }
      }).catch(err=>{
        Swal.close(); SwalMaroon.fire('Error','An unexpected error occurred. Please try again.','error'); console.error('addStaff error:', err);
      });
  }

  // --- Initializers ---
  if (addStaffForm) addStaffForm.addEventListener('submit', (e)=>handleFormSubmit(e, addStaffForm, `${window.URLROOT}/admin/addStaff`));
  if (editUserForm) editUserForm.addEventListener('submit', (e)=>handleFormSubmit(e, editUserForm, `${window.URLROOT}/admin/updateStaff`, true));

  // --- Edit Modal Setup ---
  if (editUserModal) editUserModal.addEventListener('show.bs.modal', function (event) {
    const userData = JSON.parse(event.relatedTarget.dataset.user || '{}');
    this.querySelector('#edit-user-id').value       = val(userData.id);
    this.querySelector('#edit-first_name').value    = val(userData.first_name);
    this.querySelector('#edit-last_name').value     = val(userData.last_name);
    this.querySelector('#edit-username').value      = val(userData.username);
    this.querySelector('#edit-email').value         = val(userData.email);
    this.querySelector('#edit-phone').value         = val(userData.phone || '');
    this.querySelector('#edit-staff_id').value      = val(userData.staff_id || '');
    this.querySelector('#edit-designation').value   = val(userData.designation || '');
    const original = {
      user_id:val(userData.id), first_name:val(userData.first_name), last_name:val(userData.last_name),
      username:val(userData.username), email:val(userData.email), phone:val(userData.phone || ''),
      staff_id:val(userData.staff_id || ''), designation:val(userData.designation || '')
    };
    editUserForm.dataset.original = JSON.stringify(original);
  });

  // --- Search Filter ---
  if (userSearchInput && userTableBody) {
    userSearchInput.addEventListener('input', function () {
      const q = this.value.toLowerCase();
      userTableBody.querySelectorAll('tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }

  // --- Reset Password (send email via /admin/resetPassword) ---
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.reset-pwd-btn'); if (!btn) return;
    const userId = btn.dataset.userId; const email = btn.dataset.email;

    const { isConfirmed } = await SwalMaroon.fire({
      title:'Send reset email?', html:`A password reset link will be emailed to <b>${email}</b>.`,
      icon:'question', showCancelButton:true, confirmButtonText:'Send', cancelButtonText:'Cancel'
    });
    if (!isConfirmed) return;

    SwalMaroon.fire({ title:'Sending email...', html:'Please wait...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });
    
    try {
      const fd = new FormData(); fd.append('user_id', userId); fd.append('email', email);
      const res = await fetch(`${window.URLROOT}/admin/resetPassword`, { method:'POST', body:fd });
      const data = await res.json();
      Swal.close();
      if (data.success) SwalMaroon.fire('Sent!', data.message || 'Reset email sent successfully.', 'success');
      else SwalMaroon.fire('Error', data.message || 'Failed to send reset email.', 'error');
    } catch (err) { Swal.close(); console.error(err); SwalMaroon.fire('Error','Unexpected error while sending reset email.','error'); }
  });

  // --- Activate/Deactivate toggle (setUserActive) ---
  document.addEventListener('change', async (e) => {
    const el = e.target; if (!el.classList.contains('user-toggle')) return;
    const userId = el.dataset.userId; 
    const isChecked = el.checked; 
    const nextState = isChecked ? 1 : 0; 
    const actionTxt = nextState ? 'Activate' : 'Deactivate';

    const { isConfirmed } = await SwalMaroon.fire({
      title:`${actionTxt} account?`, text:`Are you sure you want to ${actionTxt.toLowerCase()} this account?`,
      icon:'warning', showCancelButton:true, confirmButtonText:actionTxt, cancelButtonText:'Cancel'
    });
    
    if (!isConfirmed) { 
      el.checked = !isChecked; 
      return; 
    }

    SwalMaroon.fire({ title:'Updating status...', html:'Please wait...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });
    
    try {
      const fd = new FormData(); fd.append('id', userId); fd.append('is_active', nextState);
      const res = await fetch(`${window.URLROOT}/admin/setUserActive`, { method:'POST', body:fd });
      const data = await res.json();
      
      Swal.close();

      if (data.success) {
        const badge = el.closest('td').querySelector('.badge');
        if (badge) { 
          // FIX: Tiyakin na gumagamit ng 'bg-maroon'
          badge.className = `ms-2 badge ${nextState ? 'bg-maroon' : 'bg-secondary'}`; 
          badge.textContent = nextState ? 'Active' : 'Inactive'; 
        }
        SwalMaroon.fire(nextState?'Activated':'Deactivated', data.message || `Account ${nextState?'activated':'deactivated'}.`, 'success');
      } else { 
        el.checked = !isChecked; 
        SwalMaroon.fire('Error', data.message || 'Could not update status.', 'error'); 
      }
    } catch (err) { 
      el.checked = !isChecked; 
      Swal.close();
      console.error(err); 
      SwalMaroon.fire('Error','Unexpected error while updating status.','error'); 
    }
  });
});