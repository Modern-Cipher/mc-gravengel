<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= isset($data['title']) ? htmlspecialchars($data['title']) : 'Gravengel'; ?></title>

  <script>window.URLROOT = '<?= URLROOT ?>';</script>

  <link rel="stylesheet" href="<?= URLROOT ?>/public/css/login.css?v=14">
  <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/gsap@3/dist/gsap.min.js"></script>
  <script defer src="<?= URLROOT ?>/public/js/login.js?v=14"></script>
</head>
<body class="is-preload">

  <div class="preload-overlay" aria-hidden="true">
    <img src="<?= URLROOT ?>/public/img/G.png" alt="">
  </div>

  <header class="login-topbar">
    <div class="topbar-inner">

      <!-- Make brand clickable to landing page -->
      <a href="<?= URLROOT ?>/public/" class="topbar-brand" aria-label="Home">
        <img src="<?= URLROOT ?>/public/img/gravengel.png" alt="Gravengel">
        <span class="brand-title">PLARIDEL PUBLIC CEMETERY</span>
      </a>

      <!-- Back button (JS forces same landing target) -->
      <button type="button" id="back-btn" class="topbar-back-btn">Back</button>
    </div>
  </header>
