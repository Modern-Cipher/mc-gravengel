<?php require APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/login.css?v=1">

<section class="login-hero">
  <div class="container login-wrap">
    <div class="login-card">
      <div class="login-brand">
        <h1>Change Password</h1>
        <p>For security, please set a new password.</p>
      </div>
      <?php if (!empty($data['flash'])): ?>
        <div class="login-flash"><?php echo htmlspecialchars($data['flash']); ?></div>
      <?php endif; ?>
      <form method="post" action="<?php echo URLROOT; ?>/auth/doChangePassword">
        <label for="new_password">New Password</label>
        <input id="new_password" name="new_password" type="password" required minlength="8" />
        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" name="confirm_password" type="password" required minlength="8" />
        <button class="cta-button" type="submit">Save</button>
      </form>
    </div>
  </div>
</section>

<?php require APPROOT . '/views/includes/footer.php'; ?>
