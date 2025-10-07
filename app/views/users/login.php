<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login · Gravengel</title>
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
      <h2 class="welcome-title">Welcome to</h2>
      <div class="login-brand">
        <img src="<?= URLROOT ?>/public/img/ggs.png" alt="GRAVENGEL">
      </div>

      <?php if (!empty($data['flash'])): ?>
        <div class="login-flash"><?= htmlspecialchars($data['flash']) ?></div>
      <?php endif; ?>

      <form id="login-form" method="post" action="<?= URLROOT ?>/auth/doLogin" novalidate>
        <label for="identifier">Username or Email</label>
        <input id="identifier" name="identifier" type="text" placeholder="Enter username or email" required>

        <label for="password">Password</label>
        <div class="password-field">
          <input id="password" name="password" type="password" placeholder="Enter password" required>
          <button type="button" class="toggle-pass" aria-label="Show/Hide">👁</button>
        </div>

        <div class="login-actions">
          <a class="forgot-link" href="<?= URLROOT ?>/auth/forgot">Forgot Password</a>
        </div>

        <button class="cta-button" type="submit">Login</button>
      </form>

      <p class="login-caption">Smart Records. Sacred Grounds.</p>
    </div>
  </div>
</section>

<script src="<?= URLROOT ?>/public/js/login.js?v=<?= time() ?>"></script>
</body>
</html>