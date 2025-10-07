// public/js/profile.js — ADMIN (single save, preview rollback, today's activity)
document.addEventListener('DOMContentLoaded', () => {
  const MAROON = getComputedStyle(document.documentElement).getPropertyValue('--maroon') || '#7b1d1d';

  const profileForm        = document.getElementById('profileForm');
  const saveBtn            = document.getElementById('edit-save-btn');

  const changePasswordForm = document.getElementById('changePasswordForm');
  const passwordUpdateBtn  = document.getElementById('password-update-btn');

  const imageInput         = document.getElementById('imageUploadInput');
  const modalAvatarWrap    = document.getElementById('modal-avatar-wrapper');
  let   modalAvatarImg     = document.getElementById('modal-profile-image');
  const mainAvatarImg      = document.getElementById('main-avatar');

  const modalEl            = document.getElementById('editProfileModal');
  const bsModal            = modalEl ? new bootstrap.Modal(modalEl) : null;

  const actList            = document.getElementById('my-activity-list');
  const actCount           = document.getElementById('act-count');
  const resetRecentBtn     = document.getElementById('reset-recent-btn');

  let originalAvatarSrc = modalAvatarImg && modalAvatarImg.tagName === 'IMG' ? modalAvatarImg.src : (mainAvatarImg?.src || null);
  let savedOnce = false;

  // sweetalert helpers
  const waitDlg = (t, h) => Swal.fire({title:t, html:h, allowOutsideClick:false, showConfirmButton:false, willOpen:()=>Swal.showLoading(), heightAuto:false});
  const okDlg   = (t, m) => Swal.fire({icon:'success', title:t||'Success', html:m||'Done', confirmButtonColor: MAROON});
  const errDlg  = (m)   => Swal.fire({icon:'error', title:'Error', html:m||'Something went wrong.', confirmButtonColor: MAROON});

  const toJSON = async (resp) => {
    const ct = resp.headers.get('content-type') || '';
    if (ct.includes('application/json')) return await resp.json();
    const txt = await resp.text(); try { return JSON.parse(txt); } catch { return { ok: resp.ok }; }
  };

  const withBtn = (btn, label, task) => {
    const old = btn.innerHTML; btn.disabled = true; btn.innerHTML = label;
    return Promise.resolve(task()).finally(()=>{ btn.disabled = false; btn.innerHTML = old; });
  };

  // instant preview (no upload yet)
  imageInput?.addEventListener('change', function(){
    const f = this.files?.[0]; if (!f) return;
    const rd = new FileReader();
    rd.onload = (ev) => {
      if (modalAvatarImg && modalAvatarImg.tagName === 'IMG') {
        modalAvatarImg.src = ev.target.result;
      } else if (modalAvatarWrap) {
        modalAvatarWrap.innerHTML = `<img src="${ev.target.result}" id="modal-profile-image" alt="Profile Image" class="profile-avatar">`;
        modalAvatarImg = document.getElementById('modal-profile-image');
      }
    };
    rd.readAsDataURL(f);
  });

  // if user closes modal without saving, restore original preview
  modalEl?.addEventListener('hidden.bs.modal', () => {
    if (savedOnce) return; // keep new one after save
    if (originalAvatarSrc) {
      if (modalAvatarImg && modalAvatarImg.tagName === 'IMG') modalAvatarImg.src = originalAvatarSrc;
      if (mainAvatarImg) mainAvatarImg.src = originalAvatarSrc;
      imageInput.value = ''; // discard chosen file
    }
  });

  // SAVE (details + image together)
  saveBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!profileForm) return;

    const fd = new FormData(profileForm);
    
    withBtn(saveBtn, `<span class="spinner-border spinner-border-sm"></span> Saving...`, async () => {
      waitDlg('Saving changes...', 'Please wait');
      try{
        const res  = await fetch(`${window.URLROOT}/users/updateProfile`, { method:'POST', body: fd });
        const json = await toJSON(res);
        Swal.close();

        if (json?.success){
          if (json.filepath && mainAvatarImg) mainAvatarImg.src = json.filepath;
          originalAvatarSrc = mainAvatarImg?.src || originalAvatarSrc;
          savedOnce = true;
          await okDlg('Profile updated!', json?.message || 'Your changes have been saved.');
          location.reload();
        } else {
          errDlg(json?.message || 'Failed to save profile.');
        }
      }catch(err){
        Swal.close();
        console.error("Save Error:", err);
        errDlg('A network or server error occurred while saving.');
      }
    });
  });

  // Change password
  changePasswordForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const np = changePasswordForm.new_password.value.trim();
    const cp = changePasswordForm.confirm_password.value.trim();
    if (np.length < 6) return errDlg('New password must be at least 6 characters.');
    if (np !== cp)     return errDlg('New password and confirm do not match.');

    withBtn(passwordUpdateBtn, `<span class="spinner-border spinner-border-sm"></span> Updating...`, async () => {
      waitDlg('Updating password...', 'Please wait');
      try{
        const fd = new FormData(changePasswordForm);
        const res  = await fetch(`${window.URLROOT}/users/changePassword`, { method:'POST', body: fd });
        const json = await toJSON(res);
        Swal.close();
        if (json?.success){
          okDlg('Password changed!', json?.message || 'Use your new password next login.');
          changePasswordForm.reset();
        } else {
          errDlg(json?.message || 'Unable to change password.');
        }
      }catch(err){
        Swal.close();
        console.error("Password Change Error:", err);
        errDlg('A network or server error occurred while changing password.');
      }
    });
  });

  // Recent activity for TODAY only
  async function loadToday(){
    if (!actList) return;
    try{
      const res  = await fetch(`${window.URLROOT}/admin/myActivity?scope=today&limit=50`);
      const json = await toJSON(res);
      const rows = Array.isArray(json?.rows) ? json.rows : [];
      actList.innerHTML = '';

      if (!rows.length){
        actList.innerHTML = `<li class="activity-item text-muted">No activity recorded today.</li>`;
        actCount.textContent = '0 records';
        return;
      }
      rows.forEach(r=>{
        const li = document.createElement('li');
        li.className = 'activity-item';
        li.innerHTML = `${(r.action_text || r.kind || 'Activity')}<br><small class="text-muted">${formatDT(r.ts)}</small>`;
        actList.appendChild(li);
      });
      actCount.textContent = `${rows.length} record(s)`;
    }catch(err){
      actList.innerHTML = `<li class="activity-item text-danger">Failed to load activity.</li>`;
      actCount.textContent = '';
      console.error("Activity Load Error:", err);
    }
  }
  loadToday();

  function formatDT(s){
    if (!s) return '—';
    try {
      const d = new Date(s.replace(' ', 'T'));
      return d.toLocaleString('en-US', {
        month: 'short', day: 'numeric',
        hour: 'numeric', minute: '2-digit', hour12: true
      });
    } catch {
      return s;
    }
  }

  // Reset visible list (session-only)
  resetRecentBtn?.addEventListener('click', async () => {
    const q = await Swal.fire({
      icon: 'question',
      title: 'Reload Recent Activity?',
      html: 'This will just refresh the list of today\'s activities.',
      showCancelButton: true,
      confirmButtonText: 'Reload',
      cancelButtonText: 'Cancel',
      confirmButtonColor: MAROON
    });
    if (!q.isConfirmed) return;
    
    // The backend endpoint is just a placeholder. The real action is reloading the data.
    loadToday();
  });

  // Zoom main avatar
  document.querySelector('.profile-avatar-container.zoomable')?.addEventListener('click', function(){
    const img = this.querySelector('img.profile-avatar');
    if (!img) return;
    Swal.fire({
      title: 'Profile Picture',
      imageUrl: img.src,
      imageHeight: 420,
      showCloseButton: true,
      confirmButtonText: 'Close',
      confirmButtonColor: '#6c757d',
      heightAuto: false
    });
  });
});