// public/js/profile.js
document.addEventListener('DOMContentLoaded', () => {
  const editDetailsForm    = document.getElementById('editDetailsForm');
  const changePasswordForm = document.getElementById('changePasswordForm');
  const imageUploadForm    = document.getElementById('imageUploadForm');
  const modalAvatarWrapper = document.getElementById('modal-avatar-wrapper');

  const editSaveBtn        = document.getElementById('edit-save-btn');
  const passwordUpdateBtn  = document.getElementById('password-update-btn');
  const uploadImageBtn     = document.getElementById('upload-image-btn');

  const waitToast = (t, h) => Swal.fire({title:t, html:h, showConfirmButton:false, allowOutsideClick:false, willOpen:()=>Swal.showLoading(), heightAuto:false});
  const okToast  = (t, m) => Swal.fire(t||'Success', m||'Done!', 'success');
  const errToast = (m)   => Swal.fire('Error', m||'An unexpected error occurred. Please try again.', 'error');

  const smartJson = async (resp) => {
    const ct = resp.headers.get('content-type') || '';
    if (ct.includes('application/json')) return await resp.json();
    const text = await resp.text();
    try { return JSON.parse(text); } catch { return { redirected: resp.redirected, ok: resp.ok }; }
  };

  const withBtn = (btn, label, fn) => {
    if (!btn) return fn();
    const o = btn.innerHTML; btn.innerHTML=label; btn.disabled=true;
    return Promise.resolve(fn()).finally(()=>{ btn.innerHTML=o; btn.disabled=false; });
  };

  // ----- Edit Details -----
  if (editSaveBtn && editDetailsForm) {
    editSaveBtn.addEventListener('click', (e) => {
      e.preventDefault();
      withBtn(editSaveBtn, `<span class="spinner-border spinner-border-sm"></span> Saving...`, async () => {
        waitToast('Saving changes...', 'Please wait...');
        try {
          const resp = await fetch(`${window.URLROOT}/users/updateDetails`, { method:'POST', body: new FormData(editDetailsForm) });
          const res  = await smartJson(resp);
          Swal.close();
          if (res?.success) okToast('Success!', res?.message || 'Saved.').then(()=>location.reload());
          else if (res?.redirect) location.href = res.redirect;
          else errToast(res?.message || 'Failed to save.');
        } catch (err) {
          Swal.close(); errToast();
        }
      });
    });
  }

  // ----- Change Password -----
  if (passwordUpdateBtn && changePasswordForm) {
    passwordUpdateBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const np = changePasswordForm.querySelector('#new_password, #new-password')?.value || '';
      const cp = changePasswordForm.querySelector('#confirm_password, #confirm-password')?.value || '';
      if (np.length < 6) return errToast('New password must be at least 6 characters.');
      if (np !== cp)    return errToast('Passwords do not match.');

      withBtn(passwordUpdateBtn, `<span class="spinner-border spinner-border-sm"></span> Saving...`, async () => {
        waitToast('Updating password...', 'Please wait...');
        try {
          const resp = await fetch(`${window.URLROOT}/users/changePassword`, { method:'POST', body: new FormData(changePasswordForm) });
          const res  = await smartJson(resp);
          Swal.close();
          if (res?.success) okToast('Success!', res?.message || 'Password changed.'); // no reload
          else if (res?.redirect) location.href = res.redirect;
          else errToast(res?.message || 'Failed to change password.');
        } catch (err) {
          Swal.close(); errToast();
        }
      });
    });
  }

  // ----- Upload Image -----
  if (uploadImageBtn && imageUploadForm) {
    uploadImageBtn.addEventListener('click', (e) => {
      e.preventDefault();
      withBtn(uploadImageBtn, `<span class="spinner-border spinner-border-sm"></span> Uploading...`, async () => {
        waitToast('Uploading image...', 'Please wait...');
        try {
          const resp = await fetch(`${window.URLROOT}/users/uploadImage`, { method:'POST', body: new FormData(imageUploadForm) });
          const res  = await smartJson(resp);
          Swal.close();
          if (res?.success) {
            okToast('Success!', res?.message || 'Image uploaded.');
            // live preview update
            if (res?.filepath) {
              document.querySelector('.profile-avatar-container.zoomable img.profile-avatar')?.setAttribute('src', res.filepath);
              const modalImg = document.getElementById('modal-profile-image');
              if (modalImg && modalImg.tagName === 'IMG') modalImg.src = res.filepath;
              else if (modalAvatarWrapper) {
                modalAvatarWrapper.innerHTML = `<img src="${res.filepath}" id="modal-profile-image" alt="Profile Image" class="profile-avatar">`;
              }
            }
          } else if (res?.redirect) location.href = res.redirect;
          else errToast(res?.message || 'Failed to upload image.');
        } catch (err) {
          Swal.close(); errToast();
        }
      });
    });
  }

  // ----- Client preview -----
  document.getElementById('imageUploadInput')?.addEventListener('change', function () {
    const f = this.files?.[0]; if (!f) return;
    const reader = new FileReader();
    reader.onload = (e) => {
      const modalImg = document.getElementById('modal-profile-image');
      if (modalImg && modalImg.tagName === 'IMG') modalImg.src = e.target.result;
      else if (modalAvatarWrapper) {
        modalAvatarWrapper.innerHTML = `<img src="${e.target.result}" id="modal-profile-image" alt="Profile Image" class="profile-avatar">`;
      }
    };
    reader.readAsDataURL(f);
  });

  // ----- Zoom avatar -----
  document.querySelector('.profile-avatar-container.zoomable')
    ?.addEventListener('click', function () {
      const img = this.querySelector('img.profile-avatar'); if (!img) return;
      Swal.fire({ title:'Profile Picture', imageUrl: img.src, imageAlt:'Profile Picture', imageHeight:400, showCloseButton:true, confirmButtonText:'Close', confirmButtonColor:'#6c757d', heightAuto:false });
    });
});
