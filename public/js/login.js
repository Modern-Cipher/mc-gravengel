/* ------------------ Helpers ------------------ */
const $  = sel => document.querySelector(sel);
const $$ = sel => document.querySelectorAll(sel);

/* ------------------ Topbar nav ------------------ */
$$('.topbar-back-btn, .topbar-brand').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();
    window.location.href = `${window.URLROOT}/`;
  });
});

/* ------------------ Toggle password ------------------ */
$$('.toggle-pass').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.previousElementSibling;
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
  });
});

/* ------------------ LOGIN ------------------ */
const loginForm = $('#login-form');
loginForm?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const identifier = $('#identifier')?.value.trim() || '';
  const password   = $('#password')?.value.trim() || '';

  if (!identifier || !password) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'warning',
      title: 'Please fill in all fields.',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
    return;
  }

  const submitButton = loginForm.querySelector('button[type="submit"]');
  const originalText = submitButton.innerHTML;
  submitButton.innerHTML = 'Logging in...';
  submitButton.disabled = true;

  try {
    const fd = new FormData(loginForm);
    // Normalize remember_me to 1/0
    fd.set('remember_me', $('#remember_me')?.checked ? '1' : '0');

    const res = await fetch(`${window.URLROOT}/auth/doLogin`, { method: 'POST', body: fd });

    // Try to parse JSON regardless of status
    let result = null;
    try {
      result = await res.json();
    } catch {
      return Swal.fire({ icon: 'error', title: 'Login Error', text: 'Invalid server response.' });
    }

    // Handle blocked due to another active session (controller returns 423 + {ok:false,msg})
    if (res.status === 423 || (result && result.ok === false && /active.*device/i.test(result.msg || ''))) {
      return Swal.fire({
        icon: 'error',
        title: 'Login blocked',
        text: result.msg || 'This account is already active on another device.'
      });
    }

    // Generic login failure
    if (!res.ok || !result || result.ok !== true) {
      return Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: (result && result.msg) || 'Invalid credentials.'
      });
    }

    // If another session was closed (should be 0 in strict mode, but keep UX)
    if (result.closed_sessions && Number(result.closed_sessions) > 0) {
      await Swal.fire({
        icon: 'warning',
        title: 'Other session signed out',
        text: 'We signed out another active session for this account.',
        confirmButtonText: 'OK'
      });
    }

    // Must-change-password flow: ask BEFORE redirect
    if (result.prompt_change_pwd) {
      const sw = await Swal.fire({
        icon: 'info',
        title: 'Change password now?',
        html: 'For your security, please update your password.<br>You can do it now or later.',
        showCancelButton: true,
        confirmButtonText: 'Change now',
        cancelButtonText: 'Maybe later',
        reverseButtons: true
      });

      if (sw.isConfirmed) {
        window.location.href = result.profile_redirect || `${window.URLROOT}/admin/profile`;
        return;
      } else {
        window.location.href = result.redirect_dashboard || `${window.URLROOT}/admin/dashboard`;
        return;
      }
    }

    // Normal success
    await Swal.fire({ icon: 'success', title: 'Logged In!', timer: 700, showConfirmButton: false });
    window.location.href = result.redirect_dashboard || `${window.URLROOT}/admin/dashboard`;

  } catch (err) {
    console.error(err);
    Swal.fire({ icon: 'error', title: 'Request Error', text: 'An unexpected error occurred.' });
  } finally {
    submitButton.innerHTML = originalText;
    submitButton.disabled = false;
  }
});

/* ------------------ FORGOT PASSWORD ------------------ */
const forgotForm = $('#forgotForm');
forgotForm?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const email = $('#email')?.value.trim() || '';
  if (!email) {
    Swal.fire({
      icon: 'error',
      title: 'Email Required',
      text: 'Please enter your email address to receive a reset link.',
      background: '#3a414e',
      color: '#ffffff'
    });
    return;
  }

  const submitButton = forgotForm.querySelector('button[type="submit"]');
  const originalText = submitButton.innerHTML;
  submitButton.innerHTML = 'Sending...';
  submitButton.disabled = true;

  try {
    const res = await fetch(`${window.URLROOT}/auth/requestReset`, {
      method: 'POST',
      body: new FormData(forgotForm)
    });
    const result = await res.json();
    const swalConfig = { background: '#3a414e', color: '#ffffff' };

    if (!result.ok) {
      Swal.fire({ ...swalConfig, icon: 'error', title: 'Error', text: result.msg || 'Failed to send reset link.' });
    } else {
      await Swal.fire({ ...swalConfig, icon: 'success', title: 'Success', text: result.msg });
      window.location.href = `${window.URLROOT}/auth/login`;
    }
  } catch (err) {
    Swal.fire({
      background: '#3a414e',
      color: '#ffffff',
      icon: 'error',
      title: 'Error',
      text: 'An unexpected error occurred.'
    });
  } finally {
    submitButton.innerHTML = originalText;
    submitButton.disabled = false;
  }
});

/* ------------------ RESET PASSWORD ------------------ */
const resetPasswordForm = $('#reset-password-form');
resetPasswordForm?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const newPassword = $('#new_password')?.value || '';
  const confirmPassword = $('#confirm_password')?.value || '';
  const submitButton = resetPasswordForm.querySelector('button[type="submit"]');

  if (newPassword.length < 6) {
    return Swal.fire('Error', 'New password must be at least 6 characters.', 'error');
  }
  if (newPassword !== confirmPassword) {
    return Swal.fire('Error', 'Passwords do not match.', 'error');
  }

  submitButton.innerHTML = 'Resetting...';
  submitButton.disabled = true;

  try {
    const res = await fetch(`${window.URLROOT}/auth/doResetPassword`, {
      method: 'POST',
      body: new FormData(resetPasswordForm)
    });
    const result = await res.json();

    if (!result.ok) {
      Swal.fire('Error', result.msg || 'Failed to reset password.', 'error');
    } else {
      await Swal.fire('Success', result.msg || 'Password reset successfully!', 'success');
      window.location.href = `${window.URLROOT}/auth/login`;
    }
  } catch (err) {
    Swal.fire('Error', 'An unexpected error occurred.', 'error');
  } finally {
    submitButton.innerHTML = 'Set New Password';
    submitButton.disabled = false;
  }
});
