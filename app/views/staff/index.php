<?php require APPROOT . '/views/includes/staff_header.php'; ?>

<style>
/* Calendar badge styling */
.fc .exp-pill{
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.18rem .5rem; border-radius:999px;
  background:#800000; color:#fff; font-weight:700; font-size:.78rem; line-height:1;
  box-shadow:0 1px 2px rgba(0,0,0,.15);
}
.fc .exp-time{
  display:inline-block; padding:.1rem .4rem; border-radius:6px;
  background:#a75a5a; color:#fff; font-weight:700; font-size:.74rem;
}
.fc .exp-holder{
  display:block; margin-top:.15rem; font-size:.72rem; color:#5a5a5a; font-weight:600;
}
</style>

<div class="main-content-header mb-4">
    <h1>Hello, <?php echo htmlspecialchars(explode(' ', $_SESSION['user']['name'])[0] ?? 'Staff'); ?>!</h1>
</div>

<div class="row">
    <div class="col-lg-7 mb-4 mb-lg-0">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-church"></i></div>
                    <div class="card-value" id="card-active">0</div>
                    <div class="card-title">Active Burial</div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="card-value" id="card-expired">0</div>
                    <div class="card-title">Expired Rental</div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="card-value" id="card-today">0</div>
                    <div class="card-title">Today's Transaction</div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-users"></i></div>
                    <div class="card-value" id="card-staff">0</div>
                    <div class="card-title">Staff Accounts</div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Rental Status Overview</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="rentalChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daily Transaction Totals (Last 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="financialChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div id="calendar-container"></div>
    </div>
</div>

<?php require APPROOT . '/views/includes/staff_footer.php'; ?>