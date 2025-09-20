<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password · Gravengel</title>
  <script>window.URLROOT = '<?= URLROOT ?>';</script>
  <link rel="stylesheet" href="<?= URLROOT ?>/public/css/login.css?v=1">
  <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <script defer src="<?= URLROOT ?>/public/js/login.js?v=1"></script>
</head>
<body>

<header class="login-topbar">
  <div class="topbar-inner">
    <a href="<?= URLROOT ?>/public/" class="topbar-brand" aria-label="Home">
      <img src="<?= URLROOT ?>/public/img/gravengel.png" alt="Gravengel">
      <span class="brand-title">PLARIDEL PUBLIC CEMETERY</span>
    </a>
    <button type="button" id="back-btn" class="topbar-back-btn">Back</button>
  </div>
</header>


<section class="login-hero">
  <div class="login-wrap">
    <div class="login-card">
      <h2 class="welcome-title" style="margin-bottom:10px">Reset Password</h2>
      <p style="color:#fff;margin:0 0 12px">Enter your new password below.</p>

      <form id="reset-password-form" method="post" novalidate>
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['user']->id) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($data['token']) ?>">

        <label for="new-password">New Password</label>
        <input id="new-password" name="new_password" type="password" placeholder="Enter new password" required>

        <label for="confirm-password">Confirm New Password</label>
        <input id="confirm-password" name="confirm_password" type="password" placeholder="Confirm new password" required>

        <button class="cta-button" type="submit">Reset Password</button>
      </form>

      <p class="login-caption">Smart Records. Sacred Grounds.</p>
    </div>
  </div>
</section>
</body>
</html>