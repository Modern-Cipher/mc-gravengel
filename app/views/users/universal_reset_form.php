<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($data['title'] ?? 'Reset Password') ?> | Gravengel</title>
  <script>window.URLROOT = '<?= URLROOT ?>';</script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    /* Google Font */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
    
    /* General Styles */
    body {
        font-family: 'Poppins', sans-serif;
        background: url('<?= URLROOT ?>/public/img/bg.png') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }
    
    /* --- HEADER COLOR FIXED --- */
    .topbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        padding: 15px 30px;
        background: #7b1e28; /* EKSATONG KULAY MAROON */
        box-sizing: border-box;
        box-shadow: 0 2px B(0,0,0,0.2);
    }
    .topbar-brand {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #ffffff;
        font-weight: 600;
    }
    .topbar-brand img {
        height: 35px;
        margin-right: 12px;
    }

    /* --- GLASS MORPHISM EFFECT --- */
    .card {
        width: 100%;
        max-width: 450px;
        background: rgba(0, 0, 0, 0.25); /* Mas transparent na background */
        backdrop-filter: blur(20px); /* Mas malakas na blur para sa frosted effect */
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 40px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        color: #fff; /* White text para sa glass effect */
    }

    .card-title {
        font-size: 28px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 10px;
    }
    .card-subtitle {
        font-size: 15px;
        text-align: center;
        color: #e0e0e0;
        margin-bottom: 30px;
    }
    .input-group {
        margin-bottom: 20px;
        position: relative;
    }
    .input-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 14px;
        color: #f0f0f0;
    }
    .input-group input {
        width: 100%;
        padding: 12px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background-color: rgba(0, 0, 0, 0.3); /* Translucent dark input */
        color: #fff; /* White text */
        font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    .input-group input:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.5);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }
    .toggle-pass {
        position: absolute;
        right: 12px;
        top: 38px;
        background: none;
        border: none;
        color: #ccc;
        cursor: pointer;
        font-size: 20px;
    }
    /* Password Validation Checklist */
    #password-strength {
        list-style-type: none;
        padding: 0;
        margin: -10px 0 20px 0;
        font-size: 13px;
    }
    #password-strength li {
        color: #ff8a80; /* Lighter Red para sa dark background */
        transition: color 0.3s ease;
    }
    #password-strength li.valid {
        color: #b9f6ca; /* Lighter Green */
    }
    #password-strength li::before {
        content: '✖';
        margin-right: 8px;
        font-weight: bold;
    }
    #password-strength li.valid::before {
        content: '✔';
    }
    .submit-btn {
        width: 100%;
        padding: 14px;
        background-color: #7b1e28; /* Maroon Button */
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 6px;
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .submit-btn:hover {
        background-color: #9c2a38;
    }
    .submit-btn:disabled {
        background-color: #555;
        cursor: not-allowed;
    }
    .login-caption {
        text-align: center;
        margin-top: 25px;
        font-size: 12px;
        color: #ccc;
    }
  </style>
</head>
<body>

<header class="topbar">
    <a href="<?= URLROOT ?>/" class="topbar-brand" aria-label="Home">
        <img src="<?= URLROOT ?>/public/img/gravengel.png" alt="Gravengel">
        <span>PLARIDEL PUBLIC CEMETERY</span>
    </a>
</header>

<main class="card">
    <h2 class="card-title">Set a New Password</h2>
    <p class="card-subtitle">Hello, <strong><?= htmlspecialchars($data['full_name']) ?></strong>. Your new password must meet the criteria below.</p>

    <form id="reset-password-form" method="post" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($data['token']) ?>">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['user']->id) ?>">

        <div class="input-group">
            <label for="new_password">New Password</label>
            <input id="new_password" name="new_password" type="password" placeholder="Enter new password" required>
            <button type="button" class="toggle-pass" aria-label="Show/Hide">👁</button>
        </div>
        
        <ul id="password-strength">
            <li id="length">At least 8 characters</li>
            <li id="lowercase">An lowercase letter</li>
            <li id="uppercase">An uppercase letter</li>
            <li id="number">A number</li>
            <li id="special">A special character (!@#$%^&*)</li>
        </ul>
        
        <div class="input-group">
            <label for="confirm_password">Confirm New Password</label>
            <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm new password" required>
        </div>
        
        <button class="submit-btn" type="submit">Set New Password</button>
    </form>
    <p class="login-caption">Smart Records. Sacred Grounds.</p>
</main>

<script>
// This script is self-contained and will not affect your other JS files.
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const form = document.getElementById('reset-password-form');
    const strengthChecks = {
        length: document.getElementById('length'),
        lowercase: document.getElementById('lowercase'),
        uppercase: document.getElementById('uppercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };

    const validators = {
        length: val => val.length >= 8,
        lowercase: val => /[a-z]/.test(val),
        uppercase: val => /[A-Z]/.test(val),
        number: val => /[0-9]/.test(val),
        special: val => /[!@#$%^&*]/.test(val)
    };

    newPasswordInput.addEventListener('input', () => {
        const password = newPasswordInput.value;
        for (const [key, validator] of Object.entries(validators)) {
            const isValid = validator(password);
            strengthChecks[key].classList.toggle('valid', isValid);
        }
    });
    
    document.querySelectorAll('.toggle-pass').forEach(button => {
        button.addEventListener('click', () => {
            const passwordInput = button.closest('.input-group').querySelector('input');
            if (passwordInput && passwordInput.type) {
                passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            }
        });
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const submitButton = form.querySelector('.submit-btn');

        let allValid = true;
        for (const validator of Object.values(validators)) {
            if (!validator(password)) {
                allValid = false;
                break;
            }
        }

        if (!allValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Weak Password',
                text: 'Please ensure your password meets all the criteria shown.'
            });
            return;
        }

        if (password !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Passwords Do Not Match',
                text: 'The new password and confirmation password must be the same.'
            });
            return;
        }

        submitButton.textContent = 'Resetting...';
        submitButton.disabled = true;

        try {
            const response = await fetch(`${window.URLROOT}/auth/doResetPassword`, {
                method: 'POST',
                body: new FormData(form)
            });
            const result = await response.json();

            if (!result.ok) {
                Swal.fire({icon: 'error', title: 'Error', text: result.msg});
            } else {
                await Swal.fire({icon: 'success', title: 'Success!', text: result.msg});
                window.location.href = `${window.URLROOT}/auth/login`;
            }
        } catch (error) {
            Swal.fire({icon: 'error', title: 'Error', text: 'An unexpected error occurred.'});
        } finally {
            submitButton.textContent = 'Set New Password';
            submitButton.disabled = false;
        }
    });
});
</script>

</body>
</html>