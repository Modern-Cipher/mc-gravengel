<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password | Gravengel</title>
  <script>window.URLROOT = '<?= URLROOT ?>';</script>
  <link rel="stylesheet" href="<?= URLROOT ?>/public/css/login.css?v=<?= time() ?>">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<header class="login-topbar">
  <div class="topbar-inner">
    <a href="<?= URLROOT ?>/" class="topbar-brand" aria-label="Home">
      <img src="<?= URLROOT ?>/public/img/gravengel.png" alt="Gravengel">
      <span class="brand-title">PLARIDEL PUBLIC CEMETERY</span>
    </a>
    <button type="button" class="topbar-back-btn">Back</button>
  </div>
</header>

<section class="login-hero">
  <div class="login-wrap">
    <div class="login-card">
      <h2 class="welcome-title" style="margin-bottom:10px">Forgot Password</h2>
      <p style="color:#fff;margin:0 0 12px">Enter your email to receive a reset link.</p>

      <form id="forgotForm" method="post" action="<?= URLROOT ?>/auth/requestReset" novalidate>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="Enter your email" required>
        <button class="cta-button" type="submit">Send Reset Link</button>
      </form>

      <p class="login-caption">Smart Records. Sacred Grounds.</p>
    </div>
  </div>
</section>

<script src="<?= URLROOT ?>/public/js/login.js?v=<?= time() ?>"></script>
</body>
</html>