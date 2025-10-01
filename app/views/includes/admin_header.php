<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Admin Panel'); ?> · Gravengel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.13/index.global.min.js"></script>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/map-styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/admin_theme.css?v=<?php echo time(); ?>">

    <?php if (isset($data['title']) && $data['title'] === 'Burial Records'): ?>
        <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/burial-records.css?v=<?php echo time(); ?>">
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>window.URLROOT = '<?php echo URLROOT; ?>';</script>
    <style>
  :root{
    /* will be set by JS below, default fallback */
    --footer-h: 64px;
    --header-h: 56px; /* adjust if your top navbar height is different */
  }

  /* Make sure the app layout reserves space for the footer */
  .main-content{
    /* room so the footer never overlaps last rows */
    padding-bottom: calc(var(--footer-h) + 24px) !important;
    min-height: calc(100vh - var(--header-h));
  }

  /* Optional: keep footer visually at the bottom even on short pages */
  .app-footer{
    width: 100%;
  }

  /* Printing: footers don’t matter on paper */
  @media print{
    .app-footer{ display:none !important; }
    .main-content{ padding-bottom:0 !important; }
  }
</style>

</head>
<body data-must-change-pwd="<?php echo htmlspecialchars($_SESSION['user']['must_change_pwd'] ?? '0'); ?>">

    <nav class="top-navbar">
        <div class="top-navbar-brand">
            <button class="sidebar-toggle me-3"><i class="fas fa-bars"></i></button>
            <img src="<?php echo URLROOT; ?>/img/gravengel.png" alt="Logo">
            <span>PLARIDEL PUBLIC CEMETERY</span>
        </div>
        <div class="top-navbar-right">
            <i class="fas fa-bell notification-icon"></i>
        </div>
    </nav>

    <div class="main-wrapper">
        <aside id="sidebar">
            <div class="sidebar-header">
                <img src="<?php echo URLROOT; ?>/img/ggs.png" alt="Gravengel System Logo">
            </div>
            <ul class="sidebar-nav main-nav">
                <?php if (isset($_SESSION['user']['role'])): ?>
                    <li>
                        <a href="<?php echo URLROOT; ?>/admin/dashboard"
                           class="<?php echo (!empty($data['title']) && $data['title']==='Dashboard') ? 'active' : ''; ?>">
                           <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo URLROOT; ?>/admin/burialRecords"
                           class="<?php echo (!empty($data['title']) && $data['title']==='Burial Records') ? 'active' : ''; ?>">
                           <i class="fas fa-book-dead fa-fw me-2"></i> Burial Records
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo URLROOT; ?>/admin/cemeteryMap"
                           class="<?php echo (!empty($data['title']) && $data['title']==='Cemetery Map') ? 'active' : ''; ?>">
                           <i class="fas fa-map-marked-alt fa-fw me-2"></i> Cemetery Map
                        </a>
                    </li>

                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li>
                            <!-- UPDATED: link to Logs & Reports page -->
                            <a href="<?php echo URLROOT; ?>/admin/logsReports"
                               class="<?php echo (!empty($data['title']) && $data['title']==='Logs & Reports') ? 'active' : ''; ?>">
                               <i class="fas fa-file-alt fa-fw me-2"></i> Logs & Reports
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo URLROOT; ?>/admin/userAccounts"
                               class="<?php echo (!empty($data['title']) && $data['title']==='User Account') ? 'active' : ''; ?>">
                               <i class="fas fa-user-cog fa-fw me-2"></i> User Account
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- CONTACT US with icon + active -->
                    <li>
                        <a href="<?php echo URLROOT; ?>/admin/contact_us"
                           class="<?php echo (!empty($data['title']) && $data['title']==='Contact Us') ? 'active' : ''; ?>">
                           <i class="fas fa-address-book fa-fw me-2"></i> Contact Us
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="sidebar-footer">
                <ul class="sidebar-nav">
                    <li>
                        <a href="<?php echo URLROOT; ?>/admin/profile"
                           class="<?php echo (!empty($data['title']) && $data['title']==='My Profile') ? 'active' : ''; ?>">
                           <i class="fas fa-user-circle fa-fw me-2"></i> My Profile
                        </a>
                    </li>
                    <li><a href="<?php echo URLROOT; ?>/auth/logout"><i class="fas fa-sign-out-alt fa-fw me-2"></i> Log Out</a></li>
                </ul>
            </div>
        </aside>

        <main class="main-content">
            <div class="container-fluid p-4">
<script>
  // Measure footer and set --footer-h so content gets correct bottom padding.
  (function() {
    function syncFooterSpace(){
      var f = document.querySelector('.app-footer');
      var h = f ? Math.ceil(f.getBoundingClientRect().height) : 0;
      document.documentElement.style.setProperty('--footer-h', h + 'px');
      // If you want to be extra safe, directly pad main-content too
      var main = document.querySelector('.main-content');
      if (main) main.style.paddingBottom = (h + 24) + 'px';
    }
    window.addEventListener('load', syncFooterSpace, { once:true });
    window.addEventListener('resize', syncFooterSpace);
    // In case tabs or dynamic content change footer height:
    const ro = new ResizeObserver(syncFooterSpace);
    var f = document.querySelector('.app-footer'); if (f) ro.observe(f);
  })();
</script>
