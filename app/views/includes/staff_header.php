<?php
// app/views/includes/staff_header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($data['title'] ?? 'Staff Panel') ?> | Gravengel</title>

  <!-- Bootstrap CSS + FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Optional: FullCalendar (used on staff dashboard only, safe to preload) -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.13/index.global.min.js"></script>
<!-- Required JS for staff pages -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


  <!-- Staff styles -->
  <link rel="stylesheet" href="<?= URLROOT ?>/css/staff.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= URLROOT ?>/css/staff_profile.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= URLROOT ?>/css/staff_map-styles.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= URLROOT ?>/css/staff_theme.css?v=<?= time() ?>">
  <?php if (!empty($data['title']) && $data['title'] === 'Burial Records'): ?>
    <link rel="stylesheet" href="<?= URLROOT ?>/css/staff_burial-records.css?v=<?= time() ?>">
  <?php endif; ?>

  <script>window.URLROOT = '<?= URLROOT ?>';</script>

  <style>
    :root{ --footer-h:64px; --header-h:56px; }
    .main-content{ padding-bottom: calc(var(--footer-h) + 24px) !important; min-height: calc(100vh - var(--header-h)); }
    .app-footer{ width:100%; }
    @media print{ .app-footer{ display:none !important; } .main-content{ padding-bottom:0 !important; } }

    /* notif dropdown */
    .top-navbar{ position:relative; z-index:1100; overflow:visible!important; }
    .top-navbar-right{ position:relative; overflow:visible!important; }
    .main-wrapper, body{ overflow:visible!important; }
    .top-navbar-right .nav-link{ display:inline-flex; align-items:center; gap:.4rem; padding:.25rem .4rem; color:#fff!important; text-decoration:none; }
    .top-navbar-right .nav-link:hover{ color:#fff!important; opacity:.95; }
    .top-navbar-right .fas.fa-bell{ font-size:18px; }
    #notifCount{ font-size:.65rem; min-width:1.1rem; display:none; }
    .dropdown-menu.show{ display:block!important; }
    .top-navbar-right .dropdown-menu{ right:0; left:auto; min-width:320px; max-width:360px; z-index:2000; background:#fff; color:#212529; border:1px solid rgba(0,0,0,.1); box-shadow:0 6px 24px rgba(0,0,0,.12); }
    .dropdown-menu.notif-floating{ z-index:2000; }
    .notif-item{ display:flex; gap:.5rem; padding:.5rem .75rem; }
    .notif-item + .notif-item{ border-top:1px solid #f1f1f1; }
    .notif-item i{ font-size:16px; margin-top:2px; }
    .notif-empty{ padding:.75rem; color:#6c757d; text-align:center; }
  </style>
</head>

<body data-must-change-pwd="<?= htmlspecialchars($_SESSION['user']['must_change_pwd'] ?? '0') ?>">

  <!-- Top Navbar -->
  <nav class="top-navbar">
    <div class="top-navbar-brand">
      <button class="sidebar-toggle me-3"><i class="fas fa-bars"></i></button>
      <img src="<?= URLROOT ?>/img/gravengel.png" alt="Logo">
      <span>PLARIDEL PUBLIC CEMETERY</span>
    </div>

    <div class="top-navbar-right">
      <!-- Notifications dropdown (static; JS fills & relocates) -->
      <div class="dropdown" id="notifRoot">
        <a class="nav-link position-relative" href="#" id="notifBell" role="button">
          <i class="fas fa-bell notification-icon"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifCount">0</span>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0 shadow" aria-labelledby="notifBell">
          <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
            <strong>Notifications</strong>
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-light" id="notifTabToday" data-tab="today">Today</button>
              <button type="button" class="btn btn-outline-secondary" id="notifTabHistory" data-tab="history">History</button>
            </div>
          </div>
          <div id="notifList" style="max-height:360px; overflow:auto;">
            <div class="notif-empty">Loading…</div>
          </div>
          <div class="p-2 border-top d-flex justify-content-end">
            <button class="btn btn-sm btn-outline-secondary" id="notifMarkRead">Mark all read</button>
          </div>
        </div>
      </div>
    </div>
  </nav>
  <!-- /Top Navbar -->

  <div class="main-wrapper">
    <aside id="sidebar">
      <div class="sidebar-header">
        <img src="<?= URLROOT ?>/img/ggs.png" alt="Gravengel System Logo">
      </div>

      <ul class="sidebar-nav main-nav">
        <?php if (!empty($_SESSION['user']['role'])): ?>
          <li>
            <a href="<?= URLROOT ?>/staff/dashboard"
               class="<?= (!empty($data['title']) && $data['title']==='Dashboard') ? 'active' : '' ?>">
               <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
            </a>
          </li>
          <li>
            <a href="<?= URLROOT ?>/staff/burialRecords"
               class="<?= (!empty($data['title']) && $data['title']==='Burial Records') ? 'active' : '' ?>">
               <i class="fas fa-book-dead fa-fw me-2"></i> Burial Records
            </a>
          </li>
          <li>
            <a href="<?= URLROOT ?>/staff/renewals"
               class="<?= (!empty($data['title']) && $data['title']==='Renewals') ? 'active' : '' ?>">
               <i class="fas fa-file-invoice-dollar fa-fw me-2"></i> Renewals
            </a>
          </li>
          <li>
            <a href="<?= URLROOT ?>/staff/cemeteryMap"
               class="<?= (!empty($data['title']) && $data['title']==='Cemetery Map') ? 'active' : '' ?>">
               <i class="fas fa-map-marked-alt fa-fw me-2"></i> Cemetery Map
            </a>
          </li>

          <!-- Logs & Reports (shown on Staff) -->
          <li>
            <a href="<?= URLROOT ?>/staff/logsReports"
               class="<?= (!empty($data['title']) && $data['title']==='Logs & Reports') ? 'active' : '' ?>">
               <i class="fas fa-file-alt fa-fw me-2"></i> Logs & Reports
            </a>
          </li>

          <!-- No "User Accounts" menu on Staff -->
          <li>
            <a href="<?= URLROOT ?>/staff/contact_us"
               class="<?= (!empty($data['title']) && $data['title']==='Contact Us') ? 'active' : '' ?>">
               <i class="fas fa-address-book fa-fw me-2"></i> Contact Us
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <div class="sidebar-footer">
        <ul class="sidebar-nav">
          <li>
            <a href="<?= URLROOT ?>/staff/profile"
               class="<?= (!empty($data['title']) && $data['title']==='My Profile') ? 'active' : '' ?>">
               <i class="fas fa-user-circle fa-fw me-2"></i> My Profile
            </a>
          </li>
          <li>
            <a href="<?= URLROOT ?>/auth/logout">
              <i class="fas fa-sign-out-alt fa-fw me-2"></i> Log Out
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="container-fluid p-4">
        <!-- ⬇️ page content starts here; include staff_footer.php after content -->
