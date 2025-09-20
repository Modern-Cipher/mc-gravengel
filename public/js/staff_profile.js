
document.addEventListener('DOMContentLoaded', () => {
  const editDetailsForm   = document.getElementById('editDetailsForm');
  const changePasswordForm= document.getElementById('changePasswordForm');
  const imageUploadForm   = document.getElementById('imageUploadForm');
  const modalAvatarWrapper= document.getElementById('modal-avatar-wrapper');

  const editSaveBtn       = document.getElementById('edit-save-btn');
  const passwordUpdateBtn = document.getElementById('password-update-btn');
  const uploadImageBtn    = document.getElementById('upload-image-btn');

  // ---------- helpers ----------
  const getUserId = (form) =>
    document.getElementById('edit-user-id')?.value ||
    form?.querySelector('input[name="id"]')?.value ||
    '';

  const withLoadingBtn = (btn, labelWhenLoading, fn) => {
    if (!btn) return fn(); // fallback kung walang trigger button
    const original = btn.innerHTML;
    btn.innerHTML = labelWhenLoading;
    btn.disabled = true;
    return Promise.resolve()
      .then(fn)
      .finally(() => {
        btn.innerHTML = original;
        btn.disabled = false;
      });
  };

  const parseSmart = async (resp) => {
    const ct = resp.headers.get('content-type') || '';
    if (ct.includes('application/json')) return await resp.json();
    // may mga backend na nagre-redirect / nagbabalik ng HTML
    const text = await resp.text();
    try { return JSON.parse(text); } catch { return { redirected: resp.redirected, ok: resp.ok, raw: text }; }
  };

  const showLoading = (title='Please wait...', html='Processing...') =>
    Swal.fire({ title, html, allowOutsideClick:false, showConfirmButton:false, willOpen:()=>Swal.showLoading(), heightAuto:false });

  const toastError = (msg='An unexpected error occurred. Please try again.') =>
    Swal.fire('Error', msg, 'error');

  const toastSuccess = (title='Success!', msg='Done!') =>
    Swal.fire(title, msg, 'success');

  // ---------- generic handler ----------
  async function handleFormSubmission({ form, triggerBtn, url, successMessage, reloadOnSuccess=true, validate }) {
    if (!form) return;

    // optional validations (e.g., require user id)
    if (typeof validate === 'function') {
      const v = validate();
      if (v !== true) { toastError(v || 'Validation failed.'); return; }
    }

    await withLoadingBtn(triggerBtn, `
      <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...
    `, async () => {
      showLoading('Saving changes...', 'Please wait...');
      try {
        const resp = await fetch(url, { method:'POST', body:new FormData(form) });
        if (!resp.ok) { Swal.close(); return toastError('Server error while saving.'); }

        const result = await parseSmart(resp);

        Swal.close();
        // expected JSON: { success: boolean, message?: string, redirect?: string }
        if (result?.success) {
          await toastSuccess('Success!', successMessage || 'Saved.');
          if (reloadOnSuccess) {
            if (result.redirect) { window.location.href = result.redirect; }
            else { window.location.reload(); }
          }
        } else if (result?.redirected) {
          // fallback: kung HTML redirect ang bumalik
          window.location.reload();
        } else {
          toastError(result?.message || 'Failed to save changes.');
        }
      } catch (err) {
        console.error('Submission Error:', err);
        Swal.close();
        toastError();
      }
    });
  }

  // ---------- Edit Details ----------
  if (editSaveBtn && editDetailsForm) {
    editSaveBtn.addEventListener('click', (e) => {
      e.preventDefault();
      handleFormSubmission({
        form: editDetailsForm,
        triggerBtn: editSaveBtn,
        url: `${window.URLROOT}/users/updateDetails`,
        successMessage: 'Profile updated successfully!',
        reloadOnSuccess: true,
        validate: () => {
          const id = getUserId(editDetailsForm);
          if (!id) return 'User ID is missing. Cannot save changes.';
          return true;
        }
      });
    });
  }

  // ---------- Change Password ----------
  if (passwordUpdateBtn && changePasswordForm) {
    passwordUpdateBtn.addEventListener('click', (e) => {
      e.preventDefault();

      // light client-side validation
      const newPass = changePasswordForm.querySelector('#new_password, #new-password')?.value || '';
      const confPass= changePasswordForm.querySelector('#confirm_password, #confirm-password')?.value || '';
      if (newPass.length < 6) return toastError('New password must be at least 6 characters.');
      if (newPass !== confPass) return toastError('Passwords do not match.');

      handleFormSubmission({
        form: changePasswordForm,
        triggerBtn: passwordUpdateBtn,
        url: `${window.URLROOT}/users/changePassword`,
        successMessage: 'Password changed successfully!',
        reloadOnSuccess: false
      });
    });
  }

  // ---------- Upload Image ----------
  if (uploadImageBtn && imageUploadForm) {
    uploadImageBtn.addEventListener('click', async (e) => {
      e.preventDefault();

      const id = getUserId(imageUploadForm);
      if (!id) return toastError('User ID is missing. Cannot upload image.');

      await withLoadingBtn(uploadImageBtn, `
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...
      `, async () => {
        showLoading('Uploading image...', 'Please wait...');
        try {
          const resp = await fetch(`${window.URLROOT}/users/uploadImage`, { method:'POST', body:new FormData(imageUploadForm) });
          if (!resp.ok) { Swal.close(); return toastError('Server error while uploading.'); }

          const result = await parseSmart(resp);

          Swal.close();
          if (result?.success) {
            await toastSuccess('Success!', result?.message || 'Image uploaded successfully!');
            // live update ng avatar (page-safe)
            const profileAvatar = document.querySelector('.profile-avatar-container.zoomable img.profile-avatar');
            if (profileAvatar && result?.filepath) profileAvatar.src = result.filepath;

            const modalProfileImage = document.getElementById('modal-profile-image');
            if (modalProfileImage) {
              if (modalProfileImage.tagName === 'IMG' && result?.filepath) {
                modalProfileImage.src = result.filepath;
              } else if (modalAvatarWrapper && result?.filepath) {
                modalAvatarWrapper.innerHTML =
                  `<img src="${result.filepath}" id="modal-profile-image" alt="Profile Image" class="profile-avatar">`;
              }
            }
          } else if (result?.redirected) {
            window.location.reload();
          } else {
            toastError(result?.message || 'Failed to upload image.');
          }
        } catch (err) {
          console.error('Image Upload Error:', err);
          Swal.close();
          toastError();
        }
      });
    });
  }

  // ---------- Image Preview (client-side) ----------
  const imageUploadInput = document.getElementById('imageUploadInput');
  if (imageUploadInput) {
    imageUploadInput.addEventListener('change', function () {
      const file = this.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (e) {
        const modalProfileImage = document.getElementById('modal-profile-image');
        if (modalProfileImage && modalProfileImage.tagName === 'IMG') {
          modalProfileImage.src = e.target.result;
        } else if (modalAvatarWrapper) {
          modalAvatarWrapper.innerHTML =
            `<img src="${e.target.result}" id="modal-profile-image" alt="Profile Image" class="profile-avatar">`;
        }
      };
      reader.readAsDataURL(file);
    });
  }

  // ---------- Zoom-in (main avatar) ----------
  const mainAvatarContainer = document.querySelector('.profile-avatar-container.zoomable');
  mainAvatarContainer?.addEventListener('click', function () {
    const image = this.querySelector('img.profile-avatar');
    if (!image) return;
    Swal.fire({
      title: 'Profile Picture',
      imageUrl: image.src,
      imageAlt: 'Profile Picture',
      imageHeight: 400,
      showCloseButton: true,
      confirmButtonText: 'Close',
      confirmButtonColor: '#6c757d',
      heightAuto: false
    });
  });
});
