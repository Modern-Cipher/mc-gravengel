(() => {
    const $ = sel => document.querySelector(sel);

    const HOME_URL = `${window.URLROOT}/`;

    document.querySelectorAll('.topbar-back-btn, .topbar-brand').forEach(element => {
        element.addEventListener('click', e => {
            e.preventDefault();
            document.body.classList.add('fade-out');
            setTimeout(() => {
                window.location.href = HOME_URL;
            }, 400);
        });
    });

    const passwordInput = $('#password');
    const toggleButton = $('.toggle-pass');

    toggleButton?.addEventListener('click', () => {
        if (!passwordInput) return;
        passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
    });

    const loginForm = $('#login-form');
    loginForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);

        const submitButton = loginForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = 'Logging in...';
        submitButton.disabled = true;

        try {
            const response = await fetch(`${window.URLROOT}/auth/doLogin`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: result.msg || 'Invalid credentials. Please try again.'
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Logged In!',
                    timer: 800,
                    showConfirmButton: false,
                    heightAuto: false 
                });
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 800);
            }

        } catch (error) {
            console.error("Network or Server Error:", error);
            Swal.fire({
                icon: 'error',
                title: 'Request Error',
                text: 'An unexpected error occurred. Please try again.'
            });
        } finally {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });
    
    const forgotForm = $('form[action*="requestReset"]');
    forgotForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(forgotForm);

        const submitButton = forgotForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = 'Sending...';
        submitButton.disabled = true;

        try {
            const response = await fetch(`${window.URLROOT}/auth/requestReset`, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (!result.ok) {
                Swal.fire('Error', result.msg, 'error');
            } else {
                Swal.fire('Success', result.msg, 'success').then(() => {
                    window.location.href = `${window.URLROOT}/auth/login`;
                });
            }
        } catch (error) {
            Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
        } finally {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });

    // NEW: Handle reset password form submission
    const resetPasswordForm = $('#reset-password-form');
    resetPasswordForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newPassword = $('#new-password').value;
        const confirmPassword = $('#confirm-password').value;

        if (newPassword.length < 6) {
            Swal.fire('Error', 'New password must be at least 6 characters.', 'error');
            return;
        }
        if (newPassword !== confirmPassword) {
            Swal.fire('Error', 'Passwords do not match.', 'error');
            return;
        }
        
        const formData = new FormData(resetPasswordForm);
        const submitButton = resetPasswordForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = 'Resetting...';
        submitButton.disabled = true;

        try {
            const response = await fetch(`${window.URLROOT}/auth/doResetPassword`, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (!result.ok) {
                Swal.fire('Error', result.msg, 'error');
            } else {
                Swal.fire('Success', result.msg, 'success').then(() => {
                    window.location.href = `${window.URLROOT}/auth/login`;
                });
            }
        } catch (error) {
            Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
        } finally {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });

})();