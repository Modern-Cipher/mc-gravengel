<?php
// app/views/includes/admin_header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($data['title'] ?? 'Admin Panel'); ?> · Gravengel</title>

  <!-- Bootstrap + FontAwesome 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Optional: FullCalendar (ginagamit sa dashboard) -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.13/index.global.min.js"></script>

  <!-- Your styles -->
  <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/admin.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/profile.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/map-styles.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/admin_theme.css?v=<?php echo time(); ?>">
  <?php if (!empty($data['title']) && $data['title']==='Burial Records'): ?>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/burial-records.css?v=<?php echo time(); ?>">
  <?php endif; ?>

  <script>window.URLROOT = '<?php echo URLROOT; ?>';</script>

  <style>
    :root{
      --footer-h: 64px;      /* updated by JS sa baba */
      --header-h: 56px;
    }

    /* ===== Layout footer spacing ===== */
    .main-content{
      padding-bottom: calc(var(--footer-h) + 24px) !important;
      min-height: calc(100vh - var(--header-h));
    }
    .app-footer{ width:100%; }
    @media print{
      .app-footer{ display:none !important; }
      .main-content{ padding-bottom:0 !important; }
    }

    /* ===== NOTIFICATION DROPDOWN ===== */
    .top-navbar{ position:relative; z-index:1100; overflow:visible !important; }
    .top-navbar-right{ position:relative; overflow:visible !important; }
    .main-wrapper, body{ overflow:visible !important; }

    .top-navbar-right .nav-link{
      display:inline-flex; align-items:center; gap:.4rem;
      padding:.25rem .4rem; color:#fff !important; text-decoration:none;
    }
    .top-navbar-right .nav-link:hover{ color:#fff !important; opacity:.95; }
    .top-navbar-right .fas.fa-bell{ font-size:18px; }

    #notifCount{ font-size:.65rem; min-width:1.1rem; display:none; } /* JS shows when unread > 0 */

    /* ensure visible even if themes mess with dropdowns */
    .dropdown-menu.show{ display:block !important; }

    .top-navbar-right .dropdown-menu{
      right:0; left:auto;
      min-width:320px; max-width:360px;
      z-index:2000; /* mas mataas kaysa navbar */
      background:#fff; color:#212529;
      border:1px solid rgba(0,0,0,.1);
      box-shadow:0 6px 24px rgba(0,0,0,.12);
    }
    /* kapag nailipat sa <body>, panatilihin ang mataas na z-index */
    .dropdown-menu.notif-floating{ z-index:2000; }

    .notif-item{ display:flex; gap:.5rem; padding:.5rem .75rem; }
    .notif-item + .notif-item{ border-top:1px solid #f1f1f1; }
    .notif-item i{ font-size:16px; margin-top:2px; }
    .notif-empty{ padding:.75rem; color:#6c757d; text-align:center; }
  </style>
</head>

<body data-must-change-pwd="<?php echo htmlspecialchars($_SESSION['user']['must_change_pwd'] ?? '0'); ?>">

  <!-- ===== Top Navbar ===== -->
  <nav class="top-navbar">
    <div class="top-navbar-brand">
      <button class="sidebar-toggle me-3"><i class="fas fa-bars"></i></button>
      <img src="<?php echo URLROOT; ?>/img/gravengel.png" alt="Logo">
      <span>PLARIDEL PUBLIC CEMETERY</span>
    </div>

    <div class="top-navbar-right">
      <!-- 🔔 Notifications dropdown (static markup; JS will fill & move) -->
      <div class="dropdown" id="notifRoot">
        <a class="nav-link position-relative" href="#" id="notifBell" role="button">
          <i class="fas fa-bell notification-icon"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifCount">0</span>
        </a>

        <!-- NOTE: ililipat ito ng JS sa <body> para hindi ma-clip -->
        <div class="dropdown-menu dropdown-menu-end p-0 shadow" aria-labelledby="notifBell">
          <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
            <strong>Notifications</strong>
            <div class="btn-group btn-group-sm" role="group" aria-label="notif tabs">
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
      <!-- /🔔 -->
    </div>
  </nav>
  <!-- ===== /Top Navbar ===== -->

  <div class="main-wrapper">
    <aside id="sidebar">
      <div class="sidebar-header">
        <img src="<?php echo URLROOT; ?>/img/ggs.png" alt="Gravengel System Logo">
      </div>

      <ul class="sidebar-nav main-nav">
        <?php if (!empty($_SESSION['user']['role'])): ?>
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

          <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
            <li>
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
          <li>
            <a href="<?php echo URLROOT; ?>/auth/logout">
              <i class="fas fa-sign-out-alt fa-fw me-2"></i> Log Out
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="container-fluid p-4">

        <!-- ===== Footer spacing sync ===== -->
        <script>
          (function () {
            function syncFooterSpace(){
              var f = document.querySelector('.app-footer');
              var h = f ? Math.ceil(f.getBoundingClientRect().height) : 0;
              document.documentElement.style.setProperty('--footer-h', h + 'px');
              var main = document.querySelector('.main-content');
              if (main) main.style.paddingBottom = (h + 24) + 'px';
            }
            window.addEventListener('load', syncFooterSpace, { once:true });
            window.addEventListener('resize', syncFooterSpace);
            var f = document.querySelector('.app-footer');
            if (window.ResizeObserver && f){
              const ro = new ResizeObserver(syncFooterSpace);
              ro.observe(f);
            }
          })();
        </script>

        <!-- ===== Scripts ===== -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Notifications client (calls /notifications/*) -->
        <script src="<?php echo URLROOT; ?>/js/notifications.js?v=<?php echo time(); ?>"></script>

        <!-- Dropdown lifter: moves notif dropdown to <body> & positions it below header -->
        <script>
          (function(){
            var bell = document.getElementById('notifBell');
            var root = document.getElementById('notifRoot');
            if(!bell || !root) return;

            var menu = root.querySelector('.dropdown-menu');
            if(!menu) return;

            // move menu to body to avoid clipping, and keep it on top
            document.body.appendChild(menu);
            menu.classList.add('notif-floating');

            var open = false;
            var OFFSET = 12; // ibaba nang kaunti para hindi matakpan ng header

            function placeMenu(){
              // tell notifications.js "opening" (para mag-seed/markRead)
              try{ bell.dispatchEvent(new Event('show.bs.dropdown', {bubbles:true})); }catch(e){}
              var r = bell.getBoundingClientRect();
              menu.style.position = 'fixed';
              menu.style.top  = (r.bottom + OFFSET) + 'px';
              menu.style.right= (window.innerWidth - r.right) + 'px';
              menu.classList.add('show');
              open = true;
            }
            function hideMenu(){
              menu.classList.remove('show');
              open = false;
            }

            bell.addEventListener('click', function(ev){
              ev.preventDefault();
              open ? hideMenu() : placeMenu();
            });

            document.addEventListener('click', function(ev){
              if(!open) return;
              if (ev.target === bell || bell.contains(ev.target)) return;
              if (!menu.contains(ev.target)) hideMenu();
            });

            window.addEventListener('resize', function(){ if(open) placeMenu(); });
            window.addEventListener('scroll', function(){ if(open) placeMenu(); }, {passive:true});

            // initialize Bootstrap dropdown (safe kahit tawagin once lang)
            try{ new bootstrap.Dropdown(bell); }catch(e){}
          })();
        </script>
