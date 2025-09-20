<?php require APPROOT . '/views/includes/staff_header.php'; ?>

<div class="main-content-header mb-4">
    <h1>Hello, <?php echo htmlspecialchars(explode(' ', $_SESSION['user']['name'])[0] ?? 'Staff'); ?>!</h1>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="ri-cross-line"></i></i></div>
                    <div class="card-value">256</div>
                    <div class="card-title">Active Burial</div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="card-value">0</div>
                    <div class="card-title">Expired Rental</div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="card-value">2</div>
                    <div class="card-title">Today's Transaction</div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-users"></i></div>
                    <div class="card-value">3</div>
                    <div class="card-title">Staff Accounts</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div id="calendar-container">
            </div>
    </div>
</div>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>