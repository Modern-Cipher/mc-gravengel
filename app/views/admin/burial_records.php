<?php require APPROOT . '/views/includes/admin_header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4@5/bootstrap-4.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<style>
  .records-wrap { margin-top:.25rem }
  .action-btn{
    display:inline-flex; align-items:center; justify-content:center;
    gap:.45rem; white-space:nowrap; min-width:130px;
    padding:.45rem .75rem; line-height:1.1;
  }
  .action-btn i{ font-size:15px }
  .action-btn .btn-text{ display:inline }
  @media (max-width: 768px){ /* Adjusted breakpoint for better responsiveness */
    .action-btn{ min-width:auto; padding:.42rem .58rem }
    .action-btn .btn-text{ display:none }
  }
  .table thead th {
    background: var(--maroon, #800000); color:#fff; border-bottom:1px solid #5f0c0e;
    white-space:nowrap; font-weight:600;
  }
  .table tbody td, .table tbody th { border-color:#ececec; }
  .badge-soft{
    background:#f6f7f9;border:1px solid #e9ecef;color:#6c757d;font-weight:600
  }
  .actions i {
    font-size: 19px; cursor: pointer; margin: 0 .32rem;
    color: var(--maroon, #800000) !important;
    opacity: .8; transition: opacity .2s, transform .2s;
  }
  .actions i:hover { transform: translateY(-1px); opacity: 1; }
  .modal-header.bg-danger { background:var(--maroon, #800000) !important }
  .expiry-badge{
    display:inline-block; font-size:.72rem; line-height:1; padding:.18rem .45rem;
    border-radius:999px; background:#f1f3f5; color:#6c757d; border:1px solid #e6e8ea;
  }
  #termsModal .modal-body{ max-height:60vh; overflow:auto; }
  #editModal .modal-body{ max-height:80vh; overflow-y:auto; }
  #editModal .form-label .text-danger{ font-weight:bold; }
</style>
<div class="records-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h2 class="mb-0">Burial Records</h2>
    <div class="d-flex align-items-center gap-2">
      <button id="addBurialBtn" type="button" class="btn btn-danger btn-sm action-btn">
        <i class="fas fa-plus"></i><span class="btn-text">Add Burial</span>
      </button>
      <a class="btn btn-warning btn-sm action-btn" title="Archive" href="<?= URLROOT ?>/admin/archivedBurials">
        <i class="fas fa-box-archive fa-archive"></i><span class="btn-text">Archive</span>
      </a>
      <button id="genReportBtn" class="btn btn-info btn-sm text-white action-btn">
        <i class="fas fa-file-export"></i><span class="btn-text">Generate Report</span>
      </button>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5 col-md-12">
                <label for="tableSearch" class="form-label mb-1"><small>Search</small></label>
                <input type="search" id="tableSearch" class="form-control form-control-sm" placeholder="Search name, burial id, plot, email…">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label for="dateFrom" class="form-label mb-1"><small>Rental Date From</small></label>
                <input type="date" id="dateFrom" class="form-control form-control-sm">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label for="dateTo" class="form-label mb-1"><small>Rental Date To</small></label>
                <input type="date" id="dateTo" class="form-control form-control-sm">
            </div>
            <div class="col-lg-3 col-md-6">
                <button id="resetFiltersBtn" class="btn btn-secondary btn-sm w-100">
                    <i class="fas fa-sync-alt me-1"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>
  </div>
  <div class="card p-3">
    <div class="table-responsive">
      <table id="burialTable" class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Plot</th>
            <th>Burial ID</th>
            <th>Name</th>
            <th>Grave Level &amp; Type</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Rental</th>
            <th>IRH Email</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($data['records'])): ?>
          <?php $fmtHuman = function($dt){ return $dt ? date("M d, Y, h:i A", strtotime($dt)) : ''; }; ?>
          <?php foreach ($data['records'] as $r): ?>
            <?php
              $yearsTxt = '—';
              if (!empty($r->rental_date) && !empty($r->expiry_date)) {
                try{ $yearsTxt = (new DateTime($r->rental_date))->diff(new DateTime($r->expiry_date))->y.' yr'; } catch(Exception $e){ $yearsTxt = '5 yr'; }
              } elseif (!empty($r->expiry_date)) { $yearsTxt = '5 yr'; }
              $expiryLabel = $fmtHuman($r->expiry_date ?? null);
              $email = $r->interment_email ?? '';
              $emailDisp = $email !== '' ? htmlspecialchars($email) : '<span class="text-muted">—</span>';
              $rentalDateAttr = !empty($r->rental_date) ? date('Y-m-d', strtotime($r->rental_date)) : '';
            ?>
            <tr data-burial-id="<?= htmlspecialchars($r->burial_id) ?>" data-rental-date="<?= $rentalDateAttr ?>">
              <td><span class="badge badge-soft"><?= htmlspecialchars($r->plot_number ?? '') ?></span></td>
              <td class="fw-semibold"><?= htmlspecialchars($r->burial_id) ?></td>
              <td><?= htmlspecialchars(trim(($r->deceased_first_name ?? '').' '.($r->deceased_last_name ?? ''))) ?></td>
              <td><?= htmlspecialchars(trim(($r->grave_level ?? '').' / '.($r->grave_type ?? ''))) ?></td>
              <td><?= htmlspecialchars($r->age ?? '') ?></td>
              <td><?= htmlspecialchars($r->sex ?? '') ?></td>
              <td>
                <?= htmlspecialchars($yearsTxt) ?>
                <?php if ($expiryLabel): ?>
                  <span class="expiry-badge ms-2">expires <?= htmlspecialchars($expiryLabel) ?></span>
                <?php endif; ?>
              </td>
              <td title="<?= htmlspecialchars($email) ?>"><?= $emailDisp ?></td>
              <td class="text-center actions">
                <i class="fas fa-eye i-view" data-bs-toggle="tooltip" title="View" data-action="view"></i>
                <i class="fas fa-print i-print" data-bs-toggle="tooltip" title="Print" data-action="print"></i>
                <i class="fas fa-qrcode i-qr" data-bs-toggle="tooltip" title="QR" data-action="qr"></i>
                <i class="fas fa-box-archive fa-archive i-archive" data-bs-toggle="tooltip" title="Archive" data-action="archive"></i>
                <!-- <i class="fas fa-trash-alt i-del" data-bs-toggle="tooltip" title="Delete" data-action="delete"></i> -->
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr id="no-records-row"><td colspan="9" class="text-center py-4 text-muted">No burial records found.</td></tr>
        <?php endif; ?>
        <tr id="no-results-row" style="display: none;"><td colspan="9" class="text-center py-4 text-muted">No matching records found for the selected filters.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="printPicker" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title mb-0">Print</h6>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="d-grid gap-2">
          <button class="btn btn-outline-secondary" data-print="form"><i class="fas fa-file-lines me-2"></i>Burial Form</button>
          <button class="btn btn-outline-secondary" data-print="contract"><i class="fas fa-scroll me-2"></i>Official Contract</button>
          <button class="btn btn-outline-secondary" data-print="qr"><i class="fas fa-qrcode me-2"></i>QR Ticket</button>
        </div>
      </div>
      <div class="modal-footer py-2"><button class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h6 class="modal-title mb-0">Printable Report (visible rows)</h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="reportBody" class="table-responsive"></div>
      </div>
      <div class="modal-footer py-2">
        <button id="reportPrintBtn" class="btn btn-danger btn-sm">
          <i class="fas fa-print me-2"></i>Print
        </button>
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h6 class="modal-title mb-0">Burial Details</h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="viewBody" class="table-responsive"></div>
      </div>
      <div class="modal-footer py-2">
        <button id="viewToEditBtn" class="btn btn-danger btn-sm me-auto">
            <i class="fas fa-pen-to-square me-2"></i>Edit Record
        </button>
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h6 class="modal-title mb-0">Edit Burial Record</h6>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editForm" class="row g-3">
          <input type="hidden" name="burial_id" id="e_burial_id">
          <input type="hidden" name="plot_id" id="e_plot_id">

          <div class="col-12"><hr class="my-2"></div>
          <div class="col-md-4">
              <label class="form-label">Plot</label>
              <input type="text" class="form-control form-control-sm" id="e_plot_label" disabled>
          </div>
          <div class="col-md-8">
              <label class="form-label">Burial ID</label>
              <input type="text" class="form-control form-control-sm" id="e_burial_id_display" disabled>
          </div>
          <div class="col-12"><hr class="my-2"></div>
          
          <div class="col-md-3">
            <label class="form-label">First Name <span class="text-danger">*</span></label>
            <input type="text" id="e_deceased_first_name" name="deceased_first_name" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Middle Name</label>
            <input type="text" id="e_deceased_middle_name" name="deceased_middle_name" class="form-control form-control-sm">
          </div>
          <div class="col-md-3">
            <label class="form-label">Last Name <span class="text-danger">*</span></label>
            <input type="text" id="e_deceased_last_name" name="deceased_last_name" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
              <label class="form-label">Suffix</label>
              <div class="d-flex gap-2">
                  <select id="e_deceased_suffix" name="deceased_suffix" class="form-select form-select-sm">
                    <option value="">(None)</option>
                    <option>Jr.</option><option>Sr.</option>
                    <option>I</option><option>II</option><option>III</option><option>IV</option><option>V</option><option>VI</option>
                  </select>
              </div>
          </div>
          
          <div class="col-12"><hr class="my-2"></div>

          <div class="col-md-3">
            <label class="form-label">Date Born</label>
            <input type="date" id="e_date_born" name="date_born" class="form-control form-control-sm">
          </div>
          <div class="col-md-3">
            <label class="form-label">Date Died <span class="text-danger">*</span></label>
            <input type="date" id="e_date_died" name="date_died" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Age</label>
            <input type="number" min="0" id="e_age" name="age" class="form-control form-control-sm" inputmode="numeric" placeholder="(auto)" readonly>
          </div>
          <div class="col-md-3">
            <label class="form-label">Sex</label>
            <select id="e_sex" name="sex" class="form-select form-select-sm">
              <option value="">Select</option><option>male</option><option>female</option><option>other</option>
            </select>
          </div>
          
          <div class="col-12"><hr class="my-2"></div>
          
          <div class="col-md-6">
            <label class="form-label">Grave Level</label>
            <select id="e_grave_level" name="grave_level" class="form-select form-select-sm">
              <option value="">Select</option>
              <option>A</option><option>B</option><option>C</option><option>D</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Grave Type</label>
            <select id="e_grave_type" name="grave_type" class="form-select form-select-sm">
              <option value="">Select</option>
              <option>Apartment</option><option>Crypt</option><option>Columbarium</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Cause of Death</label>
            <input type="text" id="e_cause_of_death" name="cause_of_death" class="form-control form-control-sm">
          </div>

          <div class="col-12"><hr class="my-2"></div>
          
          <div class="col-md-6">
            <label class="form-label">IRH Full Name <span class="text-danger">*</span></label>
            <input type="text" id="e_interment_full_name" name="interment_full_name" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Relationship <span class="text-danger">*</span></label>
            <select id="e_interment_relationship" name="interment_relationship" class="form-select form-select-sm" required>
              <option value="">Select</option>
              <option>Spouse</option><option>Parent</option><option>Child</option>
              <option>Sibling</option><option>Relative</option><option>Other</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Contact Number <small class="text-muted">(09XX XXX XXXX)</small></label>
            <input type="text" id="e_interment_contact_number" name="interment_contact_number" class="form-control form-control-sm" maxlength="13" inputmode="numeric" placeholder="0912 345 6789">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email <small class="text-muted">(max 150 chars)</small></label>
            <input type="email" id="e_interment_email" name="interment_email" class="form-control form-control-sm" maxlength="150" placeholder="name@example.com">
          </div>
          
          <div class="col-12">
            <label class="form-label">Current Saved Address</label>
            <div id="e_current_address_display" class="form-control form-control-sm" style="height:auto; background-color:#e9ecef;"></div>
            <small class="text-muted mt-1 d-block">To change the address, use the fields below. If left blank, the current address will be kept.</small>
          </div>
          <div class="col-md-3">
            <label class="form-label">Province</label>
            <select id="e_addr_province" class="form-select form-select-sm"><option value="">Select</option></select>
          </div>
          <div class="col-md-3">
            <label class="form-label">City/Municipality</label>
            <select id="e_addr_city" class="form-select form-select-sm" disabled><option value="">Select</option></select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Barangay</label>
            <select id="e_addr_brgy" class="form-select form-select-sm" disabled><option value="">Select</option></select>
          </div>
          <div class="col-md-3">
            <label class="form-label">ZIP</label>
            <input type="text" id="e_addr_zip" class="form-control form-control-sm" maxlength="4" inputmode="numeric" placeholder="e.g. 3004">
          </div>
          <div class="col-12">
            <label class="form-label">House/Lot &amp; Street / Subdivision</label>
            <input type="text" id="e_addr_line" class="form-control form-control-sm" placeholder="House no., Street, Subdivision">
          </div>
          <input type="hidden" id="e_interment_address" name="interment_address">
          <div class="col-12"><hr class="my-2"></div>

          <div class="col-md-4">
            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
            <input type="number" min="0" step="0.01" id="e_payment_amount" name="payment_amount" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Rental Date &amp; Time</label>
            <input type="datetime-local" id="e_rental_date" name="rental_date" class="form-control form-control-sm">
          </div>
          <div class="col-md-4">
            <label class="form-label">Expiry (auto +5 yrs)</label>
            <input type="text" id="e_expiry_date_display" class="form-control form-control-sm" disabled>
            <input type="hidden" id="e_expiry_date" name="expiry_date">
          </div>
          
          <div class="col-12"><hr class="my-2"></div>
          
          <div class="col-12">
            <label class="form-label">Requirements (Check all that apply)</label>
            <div class="row">
              <div class="col-md-6">
                <label class="form-check d-block"><input class="form-check-input e_req" type="checkbox" value="Death Certificate with registry number"> <span class="form-check-label">Death Certificate with registry number</span></label>
                <label class="form-check d-block"><input class="form-check-input e_req" type="checkbox" value="Barangay Indigency for Burial Assistance"> <span class="form-check-label">Barangay Indigency for Burial Assistance</span></label>
                <label class="form-check d-block"><input class="form-check-input e_req" type="checkbox" value="Voter's ID"> <span class="form-check-label">Voter's ID</span></label>
              </div>
              <div class="col-md-6">
                <label class="form-check d-block"><input class="form-check-input e_req" type="checkbox" value="Cedula"> <span class="form-check-label">Cedula</span></label>
                <label class="form-check d-block"><input class="form-check-input e_req" type="checkbox" value="Sulat Kahilingan"> <span class="form-check-label">Sulat Kahilingan</span></label>
              </div>
            </div>
            <input type="hidden" id="e_requirements" name="requirements">
          </div>

        </form>
      </div>
      <div class="modal-footer py-2">
        <button class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button id="saveEdit" class="btn btn-light btn-sm text-danger border">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title mb-0">QR Code</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body text-center">
        <img id="qrImg" src="" style="width:220px;height:220px" alt="QR">
        <div class="small mt-2" id="qrMeta"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Terms &amp; Conditions</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Terms &amp; Conditions as a whole.</strong> By proceeding to add a new burial record, you hereby agree to the following terms:</p>
        <ol style="list-style-position: inside; padding-left: 0;">
            <li class="mb-2"><strong>Accuracy of Information</strong><br>You affirm that all burial details, including the deceased’s identity, burial plot allocation, and rental duration, are accurate and verified to the best of your knowledge. Providing false or misleading data may result in disciplinary or legal action.</li>
            <li class="mb-2"><strong>Authorization</strong><br>You confirm that you are authorized to register or modify burial records in this system either as an official cemetery staff member or as a duly appointed representative of the family or estate of the deceased.</li>
            <li class="mb-2"><strong>Data Privacy</strong><br>All personal information entered into this system is confidential and subject to the [Cemetery’s Privacy Policy]. Data collected will be used solely for burial registry purposes and will not be shared without proper authorization or legal mandate.</li>
            <li class="mb-2"><strong>Burial Plot Assignment</strong><br>Once a burial plot is recorded and confirmed, it is considered provisionally reserved and cannot be reassigned unless a formal cancellation or reassignment request is submitted and approved.</li>
            <li class="mb-2"><strong>Rental Duration Compliance</strong><br>The rental duration selected must comply with the cemetery’s policies and regulations. Renewal, extension, or repurchase of rights beyond the stated duration requires formal application and payment.</li>
            <li class="mb-2"><strong>Modification &amp; Deletion</strong><br>Modification or deletion of burial records can only be done with valid administrative clearance and appropriate documentation. Unauthorized modifications are strictly prohibited.</li>
            <li class="mb-2"><strong>Audit &amp; Logs</strong><br>All interactions with this system are logged for audit purposes. Misuse or fraudulent use of the system will be subject to internal investigation and may be reported to authorities.</li>
            <li class="mb-2"><strong>Acceptance</strong><br>By clicking "I Agree" and proceeding to add a burial, you acknowledge that you have read, understood, and accepted the terms and conditions outlined above.</li>
        </ol>
        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="agreeCheck">
          <label class="form-check-label" for="agreeCheck">I agree to the Terms &amp; Conditions</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="proceedBtn" disabled>Proceed</button>
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
<script>
(() => {
  // --- BAGONG CODE: SWEETALERT MAROON THEME ---
  const themedSwal = Swal.mixin({
      customClass: {
          confirmButton: 'btn btn-danger mx-2', // Gagamitin nito ang maroon style na nasa taas (.btn-danger)
          cancelButton: 'btn btn-secondary mx-2'
      },
      buttonsStyling: false
  });
  // --- END NG BAGONG CODE ---

  [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].forEach(el => new bootstrap.Tooltip(el));

  const table = document.getElementById('burialTable');
  const printPicker = new bootstrap.Modal('#printPicker');
  const reportModal = new bootstrap.Modal('#reportModal');
  const viewModal   = new bootstrap.Modal('#viewModal');
  const editModal   = new bootstrap.Modal('#editModal');
  const qrModal     = new bootstrap.Modal('#qrModal');
  const viewToEditBtn = document.getElementById('viewToEditBtn');
  const e = id => document.getElementById(id);
  let currentBurialId = '';


// --- [START] NEW FILTERING AND PRINTING LOGIC ---
  const searchInput = e('tableSearch');
  const dateFromInput = e('dateFrom');
  const dateToInput = e('dateTo');
  const resetBtn = e('resetFiltersBtn');
  const genReportBtn = e('genReportBtn');
  const noResultsRow = e('no-results-row');
  const noRecordsRow = e('no-records-row');

  const debounce = (fn, ms = 300) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
  
  function applyFilters() {
      const searchTerm = searchInput.value.toLowerCase();
      const fromDate = dateFromInput.value;
      const toDate = dateToInput.value;
      let visibleRowCount = 0;

      table.querySelectorAll('tbody tr:not(#no-results-row):not(#no-records-row)').forEach(tr => {
          const textContent = tr.innerText.toLowerCase();
          const rentalDate = tr.dataset.rentalDate;
          const textMatch = searchTerm === '' || textContent.includes(searchTerm);
          
          let dateMatch = true;
          if (fromDate && (!rentalDate || rentalDate < fromDate)) { dateMatch = false; }
          if (toDate && (!rentalDate || rentalDate > toDate)) { dateMatch = false; }

          if (textMatch && dateMatch) {
              tr.style.display = '';
              visibleRowCount++;
          } else {
              tr.style.display = 'none';
          }
      });
      
      const hasActiveFilters = searchTerm || fromDate || toDate;
      if (noResultsRow) noResultsRow.style.display = (visibleRowCount === 0 && hasActiveFilters) ? '' : 'none';
      if (noRecordsRow) noRecordsRow.style.display = (visibleRowCount === 0 && !hasActiveFilters) ? '' : 'none';
  }

  searchInput.addEventListener('input', debounce(applyFilters));
  dateFromInput.addEventListener('change', applyFilters);
  dateToInput.addEventListener('change', applyFilters);

  resetBtn.addEventListener('click', () => {
      searchInput.value = '';
      dateFromInput.value = '';
      dateToInput.value = '';
      applyFilters();
  });


  const pad = n => String(n).padStart(2,'0');
  
  const fromDbToInput = (val) => {
    if(!val) return '';
    return val.substring(0,16).replace(' ','T');
  };

  const calculateAge = (dateBornStr) => {
    if (!dateBornStr) return '';
    const birthDate = new Date(dateBornStr);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age >= 0 ? age : 0;
  };

  const calculateExpiry = (rentalDateStr) => {
    if (!rentalDateStr) return { date: '', display: '' };
    const start = new Date(rentalDateStr);
    if (isNaN(start.getTime())) return { date: '', display: '' };
    const exp = new Date(start);
    exp.setFullYear(exp.getFullYear() + 5);
    const hiddenValue = `${exp.getFullYear()}-${pad(exp.getMonth()+1)}-${pad(exp.getDate())}T${pad(exp.getHours())}:${pad(exp.getMinutes())}`;
    const displayValue = exp.toLocaleString('en-US',{year:'numeric',month:'long',day:'numeric',hour:'numeric',minute:'numeric',hour12:true});
    return { date: hiddenValue, display: displayValue };
  };

  function collectReqs(){
    const vals = Array.from(document.querySelectorAll('.e_req:checked')).map(c=>c.value);
    e('e_requirements').value = vals.join(', ');
  }

  function setReqs(reqsString){
    const decodedString = (reqsString || '').replace(/&#039;/g, "'");
    const reqs = decodedString.split(',').map(s => s.trim()).filter(Boolean);
    document.querySelectorAll('.e_req').forEach(ch => {
        ch.checked = reqs.includes(ch.value);
    });
  }
  
  e('e_date_born').addEventListener('change', function() {
    e('e_age').value = calculateAge(this.value);
  });
  e('e_rental_date').addEventListener('change', function() {
    const { date, display } = calculateExpiry(this.value);
    e('e_expiry_date_display').value = display;
    e('e_expiry_date').value = date;
  });

  const ePhone = e('e_interment_contact_number');
  ePhone.addEventListener('input', ev=>{
    let d = ev.target.value.replace(/\D/g,'').slice(0,11);
    ev.target.value = d.length<=4?d : d.length<=7? d.slice(0,4)+' '+d.slice(4) : d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7);
  });
  const isPH = v => /^09\d{2}\s\d{3}\s\d{4}$/.test((v||'').trim());

  const tosModal = new bootstrap.Modal(e('termsModal'));
  e('addBurialBtn').addEventListener('click', () => {
    e('agreeCheck').checked = false; e('proceedBtn').disabled = true; tosModal.show();
  });
  e('agreeCheck').addEventListener('change', () => { e('proceedBtn').disabled = !e('agreeCheck').checked; });
  e('proceedBtn').addEventListener('click', () => { tosModal.hide(); window.location = "<?= URLROOT ?>/admin/addBurial"; });

  e('tableSearch').addEventListener('input', (ev) => {
    const term = ev.target.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(tr => tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none');
  });

  const api='https://psgc.gitlab.io/api';
  const e_selProv=e('e_addr_province'), e_selCity=e('e_addr_city'), e_selBrgy=e('e_addr_brgy');
  
  function fill(sel,list,ph='Select'){ sel.innerHTML=`<option value="">${ph}</option>`; list.forEach(x=>{const o=document.createElement('option');o.value=x.code||x.id||x.name;o.textContent=x.name;sel.appendChild(o);}); }
  
  fetch(`${api}/provinces/`).then(r=>r.json()).then(list=>{ list.sort((a,b)=>a.name.localeCompare(b.name)); fill(e_selProv,list); });
  
  e_selProv.addEventListener('change',()=>{ e_selCity.disabled=true; e_selBrgy.disabled=true; fill(e_selCity,[]); fill(e_selBrgy,[]); const c=e_selProv.value; if(!c)return; fetch(`${api}/provinces/${c}/cities-municipalities/`).then(r=>r.json()).then(list=>{ list.sort((a,b)=>a.name.localeCompare(b.name)); fill(e_selCity,list); e_selCity.disabled=false; }); });
  
  e_selCity.addEventListener('change',()=>{ e_selBrgy.disabled=true; fill(e_selBrgy,[]); const c=e_selCity.value; if(!c)return; fetch(`${api}/cities-municipalities/${c}/barangays/`).then(r=>r.json()).then(list=>{ list.sort((a,b)=>a.name.localeCompare(b.name)); fill(e_selBrgy,list); e_selBrgy.disabled=false; }); });
  
  function bakeAddressForEdit(){
      const parts = [ 
          e('e_addr_line').value.trim(), 
          e('e_addr_brgy').value ? 'Brgy. '+e('e_addr_brgy').selectedOptions[0].text : '', 
          e('e_addr_city').value ? e('e_addr_city').selectedOptions[0].text : '', 
          e('e_addr_province').value ? e('e_addr_province').selectedOptions[0].text : '', 
          e('e_addr_zip').value.trim() 
      ].filter(Boolean);
      
      const newAddress = parts.join(', ');
      if (newAddress) {
          e('e_interment_address').value = newAddress;
      }
  }

  e('genReportBtn').addEventListener('click', () => {
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(tr => tr.style.display !== 'none');
    const head = `
      <thead><tr>
        <th>Plot</th><th>Burial ID</th><th>Name</th>
        <th>Grave Level & Type</th><th>Age</th><th>Sex</th>
        <th>Rental</th><th>IRH Email</th>
      </tr></thead>`;
    const body = rows.map(tr=>{
      const tds = tr.querySelectorAll('td,th');
      const plot   = tds[0].innerText.trim();
      const bid    = tds[1].innerText.trim();
      const name   = tds[2].innerText.trim();
      const grave  = tds[3].innerText.trim();
      const age    = tds[4].innerText.trim();
      const sex    = tds[5].innerText.trim();
      const rental = tds[6].innerText.trim();
      const email  = tds[7].innerText.trim();
      return `<tr>
        <td>${plot}</td><td>${bid}</td><td>${name}</td>
        <td>${grave}</td><td>${age}</td><td>${sex}</td>
        <td>${rental}</td><td>${email}</td>
      </tr>`;
    }).join('');
    e('reportBody').innerHTML = `<table class="table table-bordered">${head}<tbody>${body}</tbody></table>`;
    reportModal.show();
  });

  e('reportPrintBtn').addEventListener('click', () => {
    const w = window.open('', '_blank');
    const html = `
      <html>
        <head>
          <title>Burial Records Report</title>
          <style>
            body{font-family:Arial, Helvetica, sans-serif;padding:16px}
            h3{margin:0 0 10px 0}
            table{width:100%;border-collapse:collapse}
            th,td{border:1px solid #aaa;padding:6px 8px;font-size:12px}
            th{background:#eee}
            @media print { @page{ size: A4 landscape; margin:12mm } }
          </style>
        </head>
        <body>
          <h3>Burial Records</h3>
          ${e('reportBody').innerHTML}
        </body>
      </html>`;
    w.document.write(html);
    w.document.close();
    w.focus();
    w.print();
    w.close();
  });

  table.addEventListener('click', async (ev) => {
    const icon = ev.target.closest('i[data-action]'); if (!icon) return;
    const tr = icon.closest('tr');
    currentBurialId = tr.dataset.burialId;

    if (icon.dataset.action === 'view')    return handleView(currentBurialId);
    if (icon.dataset.action === 'print')   return printPicker.show();
    if (icon.dataset.action === 'qr')      return showQr(currentBurialId);
    if (icon.dataset.action === 'archive') return confirmArchive(currentBurialId, tr);
    if (icon.dataset.action === 'delete')  return confirmDelete(currentBurialId, tr);
  });

  if (viewToEditBtn) {
    viewToEditBtn.addEventListener('click', () => { viewModal.hide(); loadEdit(currentBurialId); });
  }

  document.getElementById('printPicker').addEventListener('click', (ev)=>{
    const btn = ev.target.closest('button[data-print]'); if(!btn) return;
    printPicker.hide();
    const what = btn.dataset.print;
    let baseUrl = '';
    if (what==='form')     baseUrl='<?= URLROOT ?>/admin/printBurialForm/'+encodeURIComponent(currentBurialId);
    if (what==='contract') baseUrl='<?= URLROOT ?>/admin/printContract/'+encodeURIComponent(currentBurialId);
    if (what==='qr')       baseUrl='<?= URLROOT ?>/admin/printQrTicket/'+encodeURIComponent(currentBurialId);
    
    if (baseUrl) {
      window.open(baseUrl + '?autoprint=1', '_blank');
    }
  });

  async function handleView(id){
    const d = await fetch('<?= URLROOT ?>/admin/getBurialDetails/'+encodeURIComponent(id), {credentials:'same-origin'})
      .then(r=>r.json()).catch(()=>null);
    if(!d){ return toast('Could not load record','error'); }

    const neatPlot = [d.block_title, d.plot_number].filter(Boolean).join(' - ') || '';
    const row=(k,v)=>`<tr><th class="text-nowrap pe-3">${k}</th><td>${v??''}</td></tr>`;
    const html = `
      <table class="table table-sm mb-0">
        ${row('Burial ID', d.burial_id)}
        ${row('Plot', neatPlot)}
        ${row('Deceased', [d.deceased_first_name,d.deceased_middle_name,d.deceased_last_name,d.deceased_suffix].filter(Boolean).join(' '))}
        ${row('Date Born', d.date_born||'')}
        ${row('Date Died', d.date_died||'')}
        ${row('Age / Sex', (d.age||'')+' '+(d.sex||''))}
        ${row('Cause of Death', d.cause_of_death||'')}
        ${row('Grave', (d.grave_level||'-')+' / '+(d.grave_type||'-'))}
        ${row('IRH', d.interment_full_name||'')}
        ${row('Relationship', d.interment_relationship||'')}
        ${row('Contact', d.interment_contact_number||'')}
        ${row('Email', d.interment_email||'')}
        ${row('Address', d.interment_address||'')}
        ${row('Payment Amount', '₱ ' + Number(d.payment_amount||0).toLocaleString('en-US',{minimumFractionDigits: 2}))}
        ${row('Rental', d.rental_date||'')}
        ${row('Expiry', d.expiry_date||'')}
        ${row('Requirements', (d.requirements || '').replace(/&#039;/g, "'").replace(/, /g, '<br>'))}
      </table>`;
    e('viewBody').innerHTML = html;
    viewModal.show();
  }

  async function loadEdit(id){
    const d = await fetch('<?= URLROOT ?>/admin/getBurialDetails/'+encodeURIComponent(id), {credentials:'same-origin'})
      .then(r=>r.json()).catch(()=>null);
    if(!d){ return toast('Could not load record','error'); }

    e('e_burial_id').value = d.burial_id;
    e('e_burial_id_display').value = d.burial_id;
    e('e_plot_id').value = d.plot_id || '';
    e('e_plot_label').value = [d.block_title, d.plot_number].filter(Boolean).join(' - ');
    e('e_deceased_first_name').value = d.deceased_first_name||'';
    e('e_deceased_middle_name').value = d.deceased_middle_name||'';
    e('e_deceased_last_name').value = d.deceased_last_name||'';
    e('e_deceased_suffix').value = d.deceased_suffix||'';
    e('e_date_born').value = d.date_born ? d.date_born.substring(0, 10) : '';
    e('e_date_died').value = d.date_died ? d.date_died.substring(0, 10) : '';
    e('e_age').value = calculateAge(e('e_date_born').value) || d.age || '';
    e('e_sex').value = d.sex||'';
    e('e_grave_level').value = d.grave_level||'';
    e('e_grave_type').value = d.grave_type||'';
    e('e_cause_of_death').value = d.cause_of_death||'';
    e('e_interment_full_name').value = d.interment_full_name||'';
    e('e_interment_relationship').value = d.interment_relationship||'';
    e('e_interment_contact_number').value = d.interment_contact_number ? d.interment_contact_number.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3') : '';
    e('e_interment_email').value = d.interment_email || '';
    e('e_interment_address').value = d.interment_address||'';
    e('e_current_address_display').textContent = d.interment_address || 'No address saved.';
    e('e_payment_amount').value = d.payment_amount ?? '0';
    e('e_rental_date').value = fromDbToInput(d.rental_date || '');
    const { date: expDateVal, display: expDateDisp } = calculateExpiry(e('e_rental_date').value);
    e('e_expiry_date').value = expDateVal;
    e('e_expiry_date_display').value = expDateDisp || (d.expiry_date ? new Date(d.expiry_date).toLocaleString('en-US',{ year:'numeric',month:'long',day:'numeric',hour:'numeric',minute:'numeric',hour12:true}) : '');
    setReqs(d.requirements);
    editModal.show();
  }

  document.getElementById('saveEdit').addEventListener('click', async ()=>{
    const form = document.getElementById('editForm');
    let formOk = true;
    ['e_deceased_first_name','e_deceased_last_name','e_date_died','e_interment_full_name','e_interment_relationship','e_payment_amount']
      .forEach(id => {
        const el = e(id);
        if (!el.value.trim()) { formOk=false; el.classList.add('is-invalid'); }
        else { el.classList.remove('is-invalid'); }
      });
    const emailEl = e('e_interment_email');
    const email = (emailEl.value||'').trim();
    if (email && (email.length > 150 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))) {
      emailEl.classList.add('is-invalid'); formOk = false;
      toast('Please enter a valid email (max 150 chars).','error');
    } else { emailEl.classList.remove('is-invalid'); }
    if (ePhone.value && !isPH(ePhone.value)){ ePhone.classList.add('is-invalid'); formOk=false; toast('Please enter a valid PH mobile number.','error'); }
    else { ePhone.classList.remove('is-invalid'); }
    if (!formOk) return;
    collectReqs();
    bakeAddressForEdit(); 
    const formData = new FormData(form);
    formData.set('age', e('e_age').value);
    const resp = await fetch('<?= URLROOT ?>/admin/updateBurial', {
      method:'POST',
      credentials:'same-origin',
      body: new URLSearchParams(formData)
    }).then(r=>r.json()).catch(()=>({ok:false, message:'Network or server error.'}));
    if(resp && resp.ok){
      editModal.hide();
      toast('Saved successfully','success');
      setTimeout(()=>location.reload(), 800);
    }else{
      themedSwal.fire({icon:'error',title:'Update Failed',text: (resp && resp.message) ? resp.message : 'An unknown error occurred.'});
    }
  });

  function showQr(id){
    const url='https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='+encodeURIComponent(id);
    e('qrImg').src=url; e('qrMeta').innerText=id; qrModal.show();
  }

  async function confirmArchive(id, row){
    const ans = await themedSwal.fire({icon:'question', title:'Archive this record?', text:'It will be moved to Archived Burials. You can restore it anytime.', showCancelButton:true, confirmButtonText:'Archive'});
    if(!ans.isConfirmed) return;
    const res = await fetch('<?= URLROOT ?>/admin/archiveBurial/'+encodeURIComponent(id), {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'}).then(r=>r.json()).catch(()=>({ok:false}));
    if(res.ok){ row.style.transition='opacity .2s'; row.style.opacity='0'; setTimeout(()=>row.remove(), 200); toast('Archived','success'); }
    else{ themedSwal.fire({icon:'error',title:'Archive failed',text:res.message||'Please try again.'}); }
  }

  async function confirmDelete(id, row){
    const ans = await themedSwal.fire({icon:'warning', title:'Delete record?', text:'This action cannot be undone and will free up the plot.', showCancelButton:true, confirmButtonText:'Delete'});
    if(!ans.isConfirmed) return;
    const res = await fetch('<?= URLROOT ?>/admin/deleteBurial/'+encodeURIComponent(id), {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'}).then(r=>r.json()).catch(()=>({ok:false}));
    if(res.ok){ row.style.transition='opacity .2s'; row.style.opacity='0'; setTimeout(()=>row.remove(), 200); toast('Deleted','success'); }
    else{ themedSwal.fire({icon:'error',title:'Delete failed',text:res.message||'Please try again.'}); }
  }

  function toast(title,icon='success'){
    themedSwal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:1700,icon,title});
  }
})();
</script>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>