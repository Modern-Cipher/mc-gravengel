const $ = sel => document.querySelector(sel);
const $$ = sel => document.querySelectorAll(sel);

// Back button and brand logo navigation
$$('.topbar-back-btn, .topbar-brand').forEach(element => {
    element.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${window.URLROOT}/`;
    });
});

// Toggle password visibility
$$('.toggle-pass').forEach(button => {
    button.addEventListener('click', () => {
        const passwordInput = button.previousElementSibling;
        if (passwordInput && passwordInput.type) {
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }
    });
});

// --- LOGIN FORM SUBMISSION WITH VALIDATION ---
const loginForm = $('#login-form');
loginForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const identifier = $('#identifier').value.trim();
    const password = $('#password').value.trim();

    // BAGONG VALIDATION: Check for empty fields
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
        return; // Stop submission
    }

    const submitButton = loginForm.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.innerHTML = 'Logging in...';
    submitButton.disabled = true;

    try {
        const response = await fetch(`${window.URLROOT}/auth/doLogin`, {
            method: 'POST',
            body: new FormData(loginForm)
        });
        const result = await response.json();

        if (!result.ok) {
            Swal.fire({ icon: 'error', title: 'Login Failed', text: result.msg || 'Invalid credentials.' });
        } else {
            Swal.fire({ icon: 'success', title: 'Logged In!', timer: 800, showConfirmButton: false });
            setTimeout(() => { window.location.href = result.redirect; }, 800);
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Request Error', text: 'An unexpected error occurred.' });
    } finally {
        submitButton.innerHTML = originalButtonText;
        submitButton.disabled = false;
    }
});

// --- FORGOT PASSWORD FORM SUBMISSION WITH VALIDATION ---
const forgotForm = $('#forgotForm'); // Using ID selector
forgotForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = $('#email').value.trim();

    // BAGONG VALIDATION: Check for empty email field
    if (!email) {
        Swal.fire({
            icon: 'error',
            title: 'Email Required',
            text: 'Please enter your email address to receive a reset link.',
            background: '#3a414e', // Dark theme
            color: '#ffffff'       // White text
        });
        return; // Stop submission
    }

    const submitButton = forgotForm.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.innerHTML = 'Sending...';
    submitButton.disabled = true;

    try {
        const response = await fetch(`${window.URLROOT}/auth/requestReset`, {
            method: 'POST',
            body: new FormData(forgotForm)
        });
        const result = await response.json();

        const swalConfig = {
            background: '#3a414e',
            color: '#ffffff'
        };

        if (!result.ok) {
            Swal.fire({ ...swalConfig, icon: 'error', title: 'Error', text: result.msg });
        } else {
            await Swal.fire({ ...swalConfig, icon: 'success', title: 'Success', text: result.msg });
            window.location.href = `${window.URLROOT}/auth/login`;
        }
    } catch (error) {
        Swal.fire({
            background: '#3a414e',
            color: '#ffffff',
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred.'
        });
    } finally {
        submitButton.innerHTML = originalButtonText;
        submitButton.disabled = false;
    }
});


// --- RESET PASSWORD FORM (no changes needed, validation already exists) ---
const resetPasswordForm = $('#reset-password-form');
resetPasswordForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const newPassword = $('#new_password').value;
    const confirmPassword = $('#confirm_password').value;
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
        const response = await fetch(`${window.URLROOT}/auth/doResetPassword`, {
            method: 'POST',
            body: new FormData(resetPasswordForm)
        });
        const result = await response.json();

        if (!result.ok) {
            Swal.fire('Error', result.msg, 'error');
        } else {
            await Swal.fire('Success', result.msg, 'success');
            window.location.href = `${window.URLROOT}/auth/login`;
        }
    } catch (error) {
        Swal.fire('Error', 'An unexpected error occurred.', 'error');
    } finally {
        submitButton.innerHTML = 'Set New Password';
        submitButton.disabled = false;
    }
});