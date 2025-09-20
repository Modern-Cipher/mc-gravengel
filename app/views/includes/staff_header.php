<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Staff Panel'); ?> · Gravengel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.13/index.global.min.js'></script>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/map-styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/admin_theme.css?v=<?php echo time(); ?>">
     <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <script>
        window.URLROOT = '<?php echo URLROOT; ?>';
    </script>
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
                    <li><a href="<?php echo URLROOT; ?>/staff/index" class="active"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-book-dead fa-fw me-2"></i> Burial Records</a></li>
                    <li><a href="<?php echo URLROOT; ?>/staff/cemeteryMap"><i class="fas fa-map-marked-alt fa-fw me-2"></i> Cemetery Map</a></li>
                    <li><a href="#"><i class="fas fa-file-alt fa-fw me-2"></i> Logs & Reports</a></li>
                    <li><a href="#"><i class="fas fa-address-book fa-fw me-2"></i> Contact Us</a></li>
                <?php endif; ?>
            </ul>
            <div class="sidebar-footer">
                <ul class="sidebar-nav">
                    <li><a href="<?php echo URLROOT; ?>/staff/profile"><i class="fas fa-user-circle fa-fw me-2"></i> My Profile</a></li>
                    <li><a href="<?php echo URLROOT; ?>/auth/logout"><i class="fas fa-sign-out-alt fa-fw me-2"></i> Log Out</a></li>
                </ul>
            </div>
        </aside>
        <main class="main-content">
            <div class="container-fluid p-4">