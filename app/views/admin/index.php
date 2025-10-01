<?php require APPROOT . '/views/includes/admin_header.php'; ?>

<style>
  /* card numbers (same layout, just a touch larger & consistent) */
  .dashboard-card .card-value { font-size: 42px; line-height: 1; }

  /* calendar container uses available height nicely */
  #calendar-container {
    background: #fff; border: 1px solid #e5e5e5; border-radius: 10px;
    padding: 10px; box-shadow: 0 1px 2px rgba(0,0,0,.03);
  }

  /* Big readable badges inside calendar cells */
  .fc .exp-pill {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: 4px 10px; border-radius: 999px;
    background: #800000; color: #fff; font-weight: 600; font-size: 12px;
    letter-spacing: .2px; box-shadow: 0 1px 2px rgba(0,0,0,.15);
    white-space: nowrap;
  }
  .fc .exp-pill .exp-time {
    background: rgba(255,255,255,.15);
    padding: 2px 6px; border-radius: 999px; font-weight: 700;
  }
  .fc .exp-holder {
    display: block; font-size: 11px; color: #555; margin-top: 2px;
  }
  /* tighten default day cell so pills fit/look good */
  .fc .fc-daygrid-event { margin: 2px 0; }
</style>

<div class="main-content-header mb-4">
  <h1>Hello, <?php echo htmlspecialchars(explode(' ', $_SESSION['user']['name'])[0] ?? 'Staff'); ?>!</h1>
</div>

<div class="row">
  <div class="col-lg-7">
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
  </div>

  <div class="col-lg-5">
    <div id="calendar-container"></div>
  </div>
</div>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>
