// public/js/staff_profile.js
document.addEventListener('DOMContentLoaded', () => {
  const MAROON = getComputedStyle(document.documentElement).getPropertyValue('--maroon').trim() || '#800000';

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

  const actList          = document.getElementById('my-activity-list');
  const actCount         = document.getElementById('act-count');
  const printActivityBtn = document.getElementById('print-activity-btn');

  let originalAvatarSrc = modalAvatarImg && modalAvatarImg.tagName === 'IMG' ? modalAvatarImg.src : (mainAvatarImg?.src || null);
  let savedOnce = false;

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

  modalEl?.addEventListener('hidden.bs.modal', () => {
    if (savedOnce) return;
    if (originalAvatarSrc) {
      if (modalAvatarImg && modalAvatarImg.tagName === 'IMG') modalAvatarImg.src = originalAvatarSrc;
      if (mainAvatarImg) mainAvatarImg.src = originalAvatarSrc;
      imageInput.value = '';
    }
  });

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

  async function loadRecentActivity(){
    if (!actList) return;
    try{
      const res  = await fetch(`${window.URLROOT}/staff/myActivity`);
      const json = await toJSON(res);
      const rows = Array.isArray(json?.rows) ? json.rows : [];
      actList.innerHTML = '';
      if (!rows.length){
        actList.innerHTML = `<li class="activity-item text-muted">No activity recorded.</li>`;
        actCount.textContent = '0 records';
        return;
      }
      rows.forEach(r=>{
        const li = document.createElement('li');
        li.className = 'activity-item';
        const safeText = (r.action_text || r.kind || 'Activity').replace(/</g, "&lt;").replace(/>/g, "&gt;");
        li.innerHTML = `${safeText}<br><small class="text-muted">${formatDT(r.ts)}</small>`;
        actList.appendChild(li);
      });
      actCount.textContent = `Showing last ${rows.length} record(s)`;
    }catch(err){
      actList.innerHTML = `<li class="activity-item text-danger">Failed to load activity.</li>`;
      actCount.textContent = '';
      console.error("Activity Load Error:", err);
    }
  }
  loadRecentActivity();

  function formatDT(s){
    if (!s) return '—';
    try {
      const d = new Date(s.replace(' ', 'T'));
      return d.toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: '2-digit', hour12: true
      });
    } catch {
      return s;
    }
  }

  printActivityBtn?.addEventListener('click', async () => {
    waitDlg('Preparing Report', 'Please wait while we generate your activity report...');
    try {
      const printUrl = `${window.URLROOT}/staff/printMyActivity`;
      const res = await fetch(printUrl);
      if (!res.ok) {
        throw new Error(`Server responded with status: ${res.status}`);
      }
      const reportHtml = await res.text();
      Swal.close();
      const iframe = document.createElement('iframe');
      iframe.style.cssText = 'position:absolute;width:0;height:0;border:0;';
      document.body.appendChild(iframe);
      const doc = iframe.contentWindow.document;
      doc.open();
      doc.write(reportHtml);
      doc.close();
      iframe.onload = () => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(() => document.body.removeChild(iframe), 1000);
      };
    } catch (err) {
      Swal.close();
      console.error('Print Error:', err);
      errDlg('Failed to generate the print report. Please check the network connection and try again.');
    }
  });

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