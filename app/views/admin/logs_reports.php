<?php require APPROOT . '/views/includes/admin_header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<style>
  :root {
    --g-maroon: #800000;
    --g-maroon-dark: #6a0000;
  }
  .table-wrapper {
    overflow: auto; /* Enables horizontal scrolling */
    max-height: 60vh;
  }
  .logs-toolbar i, .logs-page .nav-tabs .nav-link i, .action-icon {
    color: var(--g-maroon);
  }
  .logs-page .nav-tabs .nav-link { color: #495057; }
  .logs-page .nav-tabs .nav-link.active {
    background-color: var(--g-maroon);
    color: #fff;
    border-color: var(--g-maroon) var(--g-maroon) #fff;
  }
  .logs-page .nav-tabs .nav-link.active i { color: #fff; }
  .logs-page .btn-primary {
    background-color: var(--g-maroon);
    border-color: var(--g-maroon);
  }
  .logs-page .btn-primary:hover {
    background-color: var(--g-maroon-dark);
    border-color: var(--g-maroon-dark);
  }
  .table thead th {
    background: var(--g-maroon) !important;
    color: #fff !important;
    position: sticky; top: 0; z-index: 1;
  }
  
  /* [UPDATED] More compact and responsive table styling */
  .table-compact th, .table-compact td {
    font-size: 0.85rem;
    padding: .4rem .5rem; /* Reduced padding */
    vertical-align: middle;
    white-space: nowrap;  /* KEY CHANGE: Prevents text from wrapping */
  }

  .filters-row .form-label {
    font-size: 0.8rem;
    margin-bottom: .25rem;
    color: #6c757d;
  }
  .action-icon { cursor: pointer; font-size: 1.1rem; }
  .action-icon:hover { color: var(--g-maroon-dark); transform: scale(1.1); }
  
  /* Modal styling */
  .detail-modal-label {
    font-weight: 600;
    color: #555;
  }
</style>

<div class="container-fluid py-3 logs-page">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 logs-toolbar">
    <h3 class="mb-0">Logs & Reports</h3>
    <button id="btn-print" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Generate a printable report of the current view">
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

  <div class="tab-content border border-top-0 p-3 rounded-bottom bg-white shadow-sm">
    <div class="tab-pane fade show active" id="activity" role="tabpanel">
      <div class="row g-3 align-items-end mb-3 filters-row">
        <div class="col-md-3"><label class="form-label">Date From</label><input type="date" id="act-from" class="form-control form-control-sm"></div>
        <div class="col-md-3"><label class="form-label">Date To</label><input type="date" id="act-to" class="form-control form-control-sm"></div>
        <div class="col-md-4"><label class="form-label">Search</label><input type="text" id="act-search" class="form-control form-control-sm" placeholder="Search username, action, or details..."></div>
        <div class="col-md-2 d-flex"><button id="act-reset" class="btn btn-secondary btn-sm w-100"><i class="fa-solid fa-rotate me-1"></i> Reset</button></div>
      </div>
      <div class="table-wrapper">
        <table class="table table-hover table-compact align-middle" id="tbl-activity">
          <thead><tr><th>User</th><th>Action Type</th><th style="min-width:400px">Details</th><th>Timestamp</th><th class="text-center">Action</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <div class="tab-pane fade" id="transactions" role="tabpanel">
      <div class="row g-3 align-items-end mb-3 filters-row">
        <div class="col-md-3"><label class="form-label">Rental Date From</label><input type="date" id="trx-from" class="form-control form-control-sm"></div>
        <div class="col-md-3"><label class="form-label">Rental Date To</label><input type="date" id="trx-to" class="form-control form-control-sm"></div>
        <div class="col-md-4"><label class="form-label">Search</label><input type="text" id="trx-search" class="form-control form-control-sm" placeholder="Search transaction, name, plot, etc."></div>
        <div class="col-md-2 d-flex"><button id="trx-reset" class="btn btn-secondary btn-sm w-100"><i class="fa-solid fa-rotate me-1"></i> Reset</button></div>
      </div>
      <div class="table-wrapper">
        <table class="table table-hover table-compact align-middle" id="tbl-transactions">
          <thead>
            <tr>
              <th>Transaction ID</th><th>Burial ID</th><th>Plot</th>
              <th style="min-width:210px">IRH</th><th>Contact</th><th>Email</th>
              <th class="text-end">Payment</th>
              <th>Rental Date</th><th>Expiry Date</th><th style="min-width:210px">Deceased</th>
              <th>Grave</th><th>Created By</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="activityDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--g-maroon); color: #fff;">
        <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i>Activity Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="activityDetailsBody">
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="transactionDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--g-maroon); color: #fff;">
        <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Transaction Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="transactionDetailsBody">
        </div>
    </div>
  </div>
</div>
<style>
    .scroll-spacer-dummy {
   
    height: 1200px; 
    opacity: 0;             
    visibility: hidden;    
    pointer-events: none;  
    padding: 0;
    margin: 0;
    width: 100%;
}
</style>
<div class="row">
    <div class="col-12">
        <div class="scroll-spacer-dummy">
            </div>
    </div>
</div>

<script>window.URLROOT = "<?= URLROOT ?>";</script>
<script src="<?= URLROOT ?>/public/js/logs_reports.js?v=<?= time() ?>"></script>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>