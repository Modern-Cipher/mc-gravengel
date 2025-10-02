<?php
// Use the same admin header include you already have in your project.
if (file_exists(APPROOT . '/views/includes/admin_header.php')) {
    require APPROOT . '/views/includes/admin_header.php';
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
  :root{
    --g-maroon:#7b1d1d;
    --footer-h:120px;
  }
  .logs-page.avoid-footer-overlap{
    /* Always keep space for the footer + a little breathing room */
    padding-bottom: calc(var(--footer-h) + 24px);
  }

  /* page should scroll, no inner scrollbars */
  .logs-page { padding-bottom: 140px; }

  /* compact, tight tables (shared by both tabs) */
  .table-compact th,
  .table-compact td {
    font-size: 12px;
    padding: .45rem .55rem; /* tighter rows */
    vertical-align: middle;
    white-space: nowrap;     /* prevent wrap; page can scroll horizontally */
  }
  .table-nowrap { overflow-x: auto; }
  .table-nowrap table { min-width: 1200px; }

  /* maroon headers w/ white text */
  .table thead th {
    background: var(--g-maroon) !important;
    color: #fff !important;
    border-color: #6a1818 !important;
    position: sticky;
    top: 0;                  /* nice while printing big lists to screen */
    z-index: 1;
  }

  /* “Actions” column is small and centered */
  .col-actions { width: 64px; text-align: center; }

  /* filters in a single visual row */
  .filters-row .form-label { font-size: 12px; margin-bottom: .25rem; }

  /* page header + print button */
  .logs-toolbar .btn { font-size: 12px; }

  /* print: hide UI, keep data only, remove action cols */
  @media print {
    body { background: #fff !important; }
    .top-navbar, #sidebar, .sidebar-toggle, .logs-toolbar,
    .nav-tabs, .filters-row, .col-actions,
    .btn, [data-bs-toggle="tooltip"] {
      display: none !important;
    }
    .main-content, .container-fluid, .logs-page,
    .tab-content, .table-responsive {
      padding: 0 !important; margin: 0 !important;
      box-shadow: none !important; border: 0 !important;
    }
    .table thead th, .table td { border-color: #bbb !important; }
    .table-compact th, .table-compact td { font-size: 11px; }
  }
</style>

<div class="container-fluid py-3 logs-page avoid-footer-overlap">

  <div class="d-flex justify-content-between align-items-center mb-3 logs-toolbar">
    <h4 class="mb-0"><i class="fa-solid fa-clipboard-list"></i> Logs & Reports</h4>
    <button id="btn-print" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Print current tab">
      <i class="fa-solid fa-print"></i> Generate Report
    </button>
  </div>

  <ul class="nav nav-tabs" id="logsTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
        <i class="fa-solid fa-user-clock"></i> Activity Logs
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button" role="tab">
        <i class="fa-solid fa-receipt"></i> Transaction Reports
      </button>
    </li>
  </ul>

  <div class="tab-content border border-top-0 p-3 rounded-bottom bg-white">
    <!-- Activity Logs -->
    <div class="tab-pane fade show active" id="activity" role="tabpanel" aria-labelledby="activity-tab">
      <div class="row g-2 align-items-end mb-3 filters-row">
        <div class="col-md-3">
          <label class="form-label">Date From</label>
          <input type="date" id="act-from" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
          <label class="form-label">Date To</label>
          <input type="date" id="act-to" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" id="act-search" class="form-control form-control-sm" placeholder="Search staff ID / username / action">
        </div>
        <div class="col-md-3 d-grid d-md-block">
          <button id="act-filter" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Apply</button>
          <button id="act-reset" class="btn btn-outline-secondary btn-sm ms-md-2 mt-2 mt-md-0"><i class="fa-solid fa-rotate"></i> Reset</button>
        </div>
      </div>

      <div class="table-responsive table-nowrap">
        <table class="table table-hover table-compact align-middle" id="tbl-activity">
          <thead>
            <tr>
              <th>Staff ID</th>
              <th>Username</th>
              <th>Time Stamp</th>
              <th style="min-width:420px">Action Taken</th>
              <th class="col-actions"><i class="fa-solid fa-gear" data-bs-toggle="tooltip" title="Actions"></i></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="d-flex justify-content-end">
        <small class="text-muted" id="act-count"></small>
      </div>
    </div>

    <!-- Transaction Reports -->
    <div class="tab-pane fade" id="transactions" role="tabpanel" aria-labelledby="transactions-tab">
      <div class="row g-2 align-items-end mb-3 filters-row">
        <div class="col-md-3">
          <label class="form-label">Rental Date From</label>
          <input type="date" id="trx-from" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
          <label class="form-label">Rental Date To</label>
          <input type="date" id="trx-to" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" id="trx-search" class="form-control form-control-sm" placeholder="Search transaction / name / plot / contact / email">
        </div>
        <div class="col-md-3 d-grid d-md-block">
          <button id="trx-filter" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Apply</button>
          <button id="trx-reset" class="btn btn-outline-secondary btn-sm ms-md-2 mt-2 mt-md-0"><i class="fa-solid fa-rotate"></i> Reset</button>
        </div>
      </div>

      <div class="table-responsive table-nowrap">
        <table class="table table-hover table-compact align-middle" id="tbl-transactions">
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>Burial ID</th>
              <th>Block</th>
              <th>Plot</th>
              <th style="min-width:210px">Interment Right</th>
              <th style="min-width:260px">Address</th>
              <th>Contact</th>
              <th>Email</th> <!-- NEW -->
              <th class="text-end">Payment</th>
              <th>Rental Date &amp; Time</th>
              <th>Expiry Date</th>
              <th style="min-width:210px">Deceased</th>
              <th>Grave</th>
              <th>Created By</th>
              <th>Created At</th>
              <th class="col-actions"><i class="fa-solid fa-gear" data-bs-toggle="tooltip" title="Actions"></i></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="d-flex justify-content-end">
        <small class="text-muted" id="trx-count"></small>
      </div>
    </div>
  </div>
</div>
<script>
  (function(){
    function setFooterSpace(){
      var f = document.querySelector('footer, .site-footer, .app-footer');
      var h = f ? f.getBoundingClientRect().height : 120; // fallback
      document.documentElement.style.setProperty('--footer-h', h + 'px');
    }
    setFooterSpace();
    window.addEventListener('resize', setFooterSpace, {passive:true});
  })();
</script>

<script>window.URLROOT = "<?= URLROOT ?>";</script>
<script src="<?= URLROOT ?>/public/js/logs_reports.js?v=<?= time() ?>"></script>

<?php
// Close with the same admin footer if you have one
if (file_exists(APPROOT . '/views/includes/admin_footer.php')) {
    require APPROOT . '/views/includes/admin_footer.php';
}
?>
