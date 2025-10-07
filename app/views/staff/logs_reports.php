<?php
// Staff header/footer (same layout styling as ibang staff pages)
if (file_exists(APPROOT . '/views/includes/staff_header.php')) {
    require APPROOT . '/views/includes/staff_header.php';
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
  :root {
    --g-maroon: #7b1d1d;
    --g-maroon-dark: #6a1818;
  }

  /* layout & scroll container */
  .logs-page { }
  .table-wrapper { overflow: auto; max-height: 55vh; }

  /* maroon theme */
  .logs-toolbar h4 .fa-solid,
  .logs-page .nav-tabs .nav-link i { color: var(--g-maroon); }
  .logs-page .nav-tabs .nav-link { color: #495057; }
  .logs-page .nav-tabs .nav-link.active {
    background-color: var(--g-maroon);
    color:#fff;
    border-color: var(--g-maroon) var(--g-maroon) #fff;
  }
  .logs-page .nav-tabs .nav-link.active i { color:#fff; }
  .logs-page .btn-primary { background-color: var(--g-maroon); border-color: var(--g-maroon); }
  .logs-page .btn-primary:hover,.logs-page .btn-primary:focus { background-color: var(--g-maroon-dark); border-color: var(--g-maroon-dark); }
  .logs-toolbar .btn-outline-primary { color: var(--g-maroon); border-color: var(--g-maroon); }
  .logs-toolbar .btn-outline-primary:hover { color:#fff; background-color: var(--g-maroon); border-color: var(--g-maroon); }
  .table .btn-outline-primary { color: var(--g-maroon); border-color: var(--g-maroon); }
  .table .btn-outline-primary:hover { color:#fff; background-color: var(--g-maroon); border-color: var(--g-maroon); }

  /* tables */
  .table thead th{
    background: var(--g-maroon) !important;
    color:#fff !important;
    border-color: var(--g-maroon-dark) !important;
    position: sticky; top:0; z-index:1;
  }
  .table-compact th,.table-compact td{
    font-size:13px; padding:.5rem .6rem; vertical-align:middle; white-space:nowrap;
  }
  .col-actions{ width:64px; text-align:center; }
  .filters-row .form-label{ font-size:12px; margin-bottom:.25rem; }

  /* print */
  @media print{
    body{ background:#fff !important; }
    .top-navbar,#sidebar,.sidebar-toggle,.logs-toolbar,
    .nav-tabs,.filters-row,.col-actions,.btn,[data-bs-toggle="tooltip"]{
      display:none !important;
    }
    .main-content,.container-fluid,.logs-page,.tab-content,.table-wrapper{
      padding:0 !important; margin:0 !important; box-shadow:none !important; border:0 !important;
      overflow:visible !important; height:auto !important; max-height:none !important;
    }
    .table thead th,.table td{ border-color:#bbb !important; }
    .table-compact th,.table-compact td{ font-size:10pt; }
  }
</style>

<div class="container-fluid py-3 logs-page">
  <div class="d-flex justify-content-between align-items-center mb-3 logs-toolbar">
    <h4 class="mb-0">Logs & Reports (Staff)</h4>
    <button id="btn-print" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Print current tab">
      <i class="fa-solid fa-print me-1"></i>Generate Report
    </button>
  </div>

  <ul class="nav nav-tabs" id="logsTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
        <i class="fa-solid fa-user-clock me-2"></i>Activity Logs
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button" role="tab">
        <i class="fa-solid fa-receipt me-2"></i>Transaction Reports
      </button>
    </li>
  </ul>

  <div class="tab-content border border-top-0 p-3 rounded-bottom bg-white">
    <!-- Activity -->
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
        <div class="col-md-3 d-flex justify-content-start align-items-end">
          <button id="act-filter" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-filter me-1"></i> Apply
          </button>
          <button id="act-reset" class="btn btn-outline-secondary btn-sm ms-2">
            <i class="fa-solid fa-rotate me-1"></i> Reset
          </button>
        </div>
      </div>

      <div class="table-wrapper">
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
      <div class="d-flex justify-content-end pt-2">
        <small class="text-muted" id="act-count"></small>
      </div>
    </div>

    <!-- Transactions -->
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
          <input type="text" id="trx-search" class="form-control form-control-sm" placeholder="Search transaction / name / plot / etc.">
        </div>
        <div class="col-md-3 d-flex justify-content-start align-items-end">
          <button id="trx-filter" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-filter me-1"></i> Apply
          </button>
          <button id="trx-reset" class="btn btn-outline-secondary btn-sm ms-2">
            <i class="fa-solid fa-rotate me-1"></i> Reset
          </button>
        </div>
      </div>

      <div class="table-wrapper">
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
              <th>Email</th>
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
      <div class="d-flex justify-content-end pt-2">
        <small class="text-muted" id="trx-count"></small>
      </div>
    </div>
  </div>
</div>

<script>window.URLROOT = "<?= URLROOT ?>";</script>
<script src="<?= URLROOT ?>/public/js/staff_logs_reports.js?v=<?= time() ?>"></script>

<?php
if (file_exists(APPROOT . '/views/includes/staff_footer.php')) {
    require APPROOT . '/views/includes/staff_footer.php';
}
?>
