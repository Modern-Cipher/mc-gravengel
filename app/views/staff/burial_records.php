<?php require APPROOT . '/views/includes/staff_header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4@5/bootstrap-4.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<style>
  .records-wrap { margin-top:.25rem }
  .search-input { max-width:300px; padding:.38rem .6rem; }
  .action-btn{
    display:inline-flex; align-items:center; justify-content:center;
    gap:.45rem; white-space:nowrap; min-width:130px;
    padding:.45rem .75rem; line-height:1.1;
  }
  .action-btn i{ font-size:15px }
  .action-btn .btn-text{ display:inline }
  @media (max-width: 576px){
    .search-input{ max-width:46vw }
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

  /* Print preview modal table */
  #reportModal .table th, #reportModal .table td { font-size:.92rem; }
</style>
<style>
  /* Hardening para sa clickability ng icons */
  td.actions { position: relative; z-index: 1; }
  td.actions i { pointer-events: auto; }
</style>


<div class="records-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Burial Records</h2>

    <div class="d-flex align-items-center">
      <input id="tableSearch" type="search" class="form-control form-control-sm search-input"
             placeholder="Search name, burial id, plot, email…">

      <button id="addBurialBtn" type="button" class="btn btn-danger btn-sm ms-2 action-btn">
        <i class="fas fa-plus"></i><span class="btn-text">Add Burial</span>
      </button>

      <a class="btn btn-warning btn-sm ms-2 action-btn" title="Archive"
         href="<?= URLROOT ?>/staff/archivedBurials">
        <i class="fas fa-box-archive fa-archive"></i><span class="btn-text">Archive</span>
      </a>

      <button id="genReportBtn" class="btn btn-info btn-sm text-white ms-2 action-btn">
        <i class="fas fa-file-export"></i><span class="btn-text">Generate Report</span>
      </button>
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
          <?php
          $fmtHuman = function($dt){ return $dt ? date("D-F d, Y \\a\\t h:i A", strtotime($dt)) : ''; };
          ?>
          <?php foreach ($data['records'] as $r): ?>
            <?php
              $yearsTxt = '—';
              if (!empty($r->rental_date) && !empty($r->expiry_date)) {
                try{ $yearsTxt = (new DateTime($r->rental_date))->diff(new DateTime($r->expiry_date))->y.' yr'; }
                catch(Exception $e){ $yearsTxt = '5 yr'; }
              } elseif (!empty($r->expiry_date)) { $yearsTxt = '5 yr'; }
              $expiryLabel = $fmtHuman($r->expiry_date ?? null);
              $email = $r->interment_email ?? '';
              $emailDisp = $email !== '' ? htmlspecialchars($email) : '<span class="text-muted">—</span>';
            ?>
            <tr data-burial-id="<?= htmlspecialchars($r->burial_id) ?>">
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
                <i class="fas fa-eye i-view"           data-bs-toggle="tooltip" title="View"    data-action="view"></i>
                <i class="fas fa-print i-print"        data-bs-toggle="tooltip" title="Print"   data-action="print"></i>
                <i class="fas fa-qrcode i-qr"          data-bs-toggle="tooltip" title="QR"      data-action="qr"></i>
                <i class="fas fa-box-archive fa-archive i-archive" data-bs-toggle="tooltip" title="Archive" data-action="archive"></i>
                <i class="fas fa-trash-alt i-del"      data-bs-toggle="tooltip" title="Delete"  data-action="delete"></i>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center py-4 text-muted">No burial records found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Print Picker -->
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

<!-- Report Modal -->
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

<!-- View Modal -->
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

<!-- Edit Modal -->
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

<!-- QR Modal -->
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

<!-- Terms (Add Burial gating) -->
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


<script>
(function(){
  // ===== Helpers =====
  const $  = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const byId = (id) => document.getElementById(id);

  // Lazy Bootstrap modal getter (safe kahit maagang tumakbo ang script)
  function modal(id){
    const el = byId(id);
    if (!el) return null;
    try { return bootstrap.Modal.getOrCreateInstance(el); }
    catch(e){ return null; } // bootstrap not ready yet? handler can retry on next click
  }

  // SweetAlert maroon theme
  const themedSwal = Swal.mixin({
    customClass: { confirmButton: 'btn btn-danger mx-2', cancelButton: 'btn btn-secondary mx-2' },
    buttonsStyling: false
  });
  const toast = (title,icon='success') =>
    themedSwal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:1700,icon,title});

  // Init tooltips (best-effort)
  try { $$('[data-bs-toggle="tooltip"]').forEach(el => { try{ new bootstrap.Tooltip(el);}catch(_){}}); } catch(_){}

  // ===== State =====
  const table = byId('burialTable');
  let currentBurialId = '';

  // ===== Utils =====
  const pad = n => String(n).padStart(2,'0');
  const fromDbToInput = (val) => val ? val.substring(0,16).replace(' ','T') : '';

  function calculateAge(dateBornStr){
    if (!dateBornStr) return '';
    const b = new Date(dateBornStr), t = new Date();
    let a = t.getFullYear() - b.getFullYear();
    const m = t.getMonth() - b.getMonth();
    if (m < 0 || (m === 0 && t.getDate() < b.getDate())) a--;
    return a >= 0 ? a : 0;
  }
  function calculateExpiry(rentalDateStr){
    if (!rentalDateStr) return { date:'', display:'' };
    const start = new Date(rentalDateStr);
    if (isNaN(start)) return { date:'', display:'' };
    const exp = new Date(start); exp.setFullYear(exp.getFullYear() + 5);
    return {
      date: `${exp.getFullYear()}-${pad(exp.getMonth()+1)}-${pad(exp.getDate())}T${pad(exp.getHours())}:${pad(exp.getMinutes())}`,
      display: exp.toLocaleString('en-US',{year:'numeric',month:'long',day:'numeric',hour:'numeric',minute:'numeric',hour12:true})
    };
  }

  // ===== Search filter =====
  const searchEl = byId('tableSearch');
  if (searchEl && table){
    searchEl.addEventListener('input', (ev)=>{
      const term = (ev.target.value || '').toLowerCase();
      $$('#burialTable tbody tr').forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none';
      });
    });
  }

  // ===== Add Burial gating (Terms & Conditions) =====
  const addBurialBtn = byId('addBurialBtn');
  const agreeCheck   = byId('agreeCheck');
  const proceedBtn   = byId('proceedBtn');
  if (addBurialBtn && agreeCheck && proceedBtn){
    addBurialBtn.addEventListener('click', ()=>{
      agreeCheck.checked = false; proceedBtn.disabled = true;
      const m = modal('termsModal'); if (m) m.show();
    });
    agreeCheck.addEventListener('change', ()=>{ proceedBtn.disabled = !agreeCheck.checked; });
    proceedBtn.addEventListener('click', ()=>{
      const m = modal('termsModal'); if (m) m.hide();
      window.location = "<?= URLROOT ?>/staff/addBurial";
    });
  }

  // ===== Report (visible rows) =====
  const genReportBtn = byId('genReportBtn');
  const reportBody   = byId('reportBody');
  const reportPrint  = byId('reportPrintBtn');

  if (genReportBtn && reportBody){
    genReportBtn.addEventListener('click', ()=>{
      const rows = (table ? Array.from(table.querySelectorAll('tbody tr')) : [])
        .filter(tr => tr.style.display !== 'none');

      const head = `
        <thead><tr>
          <th>Plot</th><th>Burial ID</th><th>Name</th>
          <th>Grave Level & Type</th><th>Age</th><th>Sex</th>
          <th>Rental</th><th>IRH Email</th>
        </tr></thead>`;

      const body = rows.map(tr=>{
        const tds = tr.querySelectorAll('td,th');
        const get = (i) => (tds[i]?.innerText || '').trim();
        return `<tr>
          <td>${get(0)}</td><td>${get(1)}</td><td>${get(2)}</td>
          <td>${get(3)}</td><td>${get(4)}</td><td>${get(5)}</td>
          <td>${get(6)}</td><td>${get(7)}</td>
        </tr>`;
      }).join('');

      reportBody.innerHTML = `<table class="table table-bordered">${head}<tbody>${body}</tbody></table>`;
      const rm = modal('reportModal'); if (rm) rm.show();
    });
  }

  if (reportPrint && reportBody){
    reportPrint.addEventListener('click', ()=>{
      const w = window.open('', '_blank');
      const html = `
        <html><head><title>Burial Records Report</title>
          <style>
            body{font-family:Arial, Helvetica, sans-serif;padding:16px}
            h3{margin:0 0 10px 0}
            table{width:100%;border-collapse:collapse}
            th,td{border:1px solid #aaa;padding:6px 8px;font-size:12px}
            th{background:#eee}
            @media print { @page{ size: A4 landscape; margin:12mm } }
          </style>
        </head><body>
          <h3>Burial Records</h3>${reportBody.innerHTML}
        </body></html>`;
      w.document.write(html); w.document.close(); w.focus(); w.print(); w.close();
    });
  }

  // ===== Global delegation for action icons =====
  document.addEventListener('click', async (ev)=>{
    const icon = ev.target.closest('td.actions i[data-action]');
    if (!icon) return;

    const tr = icon.closest('tr');
    if (!tr) return;
    currentBurialId = tr.getAttribute('data-burial-id') || tr.dataset.burialId || '';

    const action = icon.dataset.action;
    if (!currentBurialId && action !== 'print') return;

    if (action === 'view')    return handleView(currentBurialId);
    if (action === 'print')   { const m = modal('printPicker'); if (m) m.show(); return; }
    if (action === 'qr')      return showQr(currentBurialId);
    if (action === 'archive') return confirmArchive(currentBurialId, tr);
    if (action === 'delete')  return confirmDelete(currentBurialId, tr);
  });

  // Print picker buttons -> staff routes
  const printPicker = byId('printPicker');
  if (printPicker){
    printPicker.addEventListener('click', (ev)=>{
      const btn = ev.target.closest('button[data-print]'); if(!btn) return;
      const m = modal('printPicker'); if (m) m.hide();
      const what = btn.dataset.print;
      let baseUrl = '';
      if (what==='form')     baseUrl='<?= URLROOT ?>/staff/printBurialForm/'+encodeURIComponent(currentBurialId);
      if (what==='contract') baseUrl='<?= URLROOT ?>/staff/printContract/'+encodeURIComponent(currentBurialId);
      if (what==='qr')       baseUrl='<?= URLROOT ?>/staff/printQrTicket/'+encodeURIComponent(currentBurialId);
      if (baseUrl) window.open(baseUrl + '?autoprint=1', '_blank');
    });
  }

  // ===== View =====
  async function handleView(id){
    try{
      const r = await fetch('<?= URLROOT ?>/staff/getBurialDetails/'+encodeURIComponent(id), {credentials:'same-origin'});
      const d = await r.json();
      if (!d) return toast('Could not load record','error');

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
      const viewBody = byId('viewBody'); if (viewBody) viewBody.innerHTML = html;
      const vm = modal('viewModal'); if (vm) vm.show();
    }catch(_){
      toast('Could not load record','error');
    }
  }

  // View → Edit button
  const viewToEditBtn = byId('viewToEditBtn');
  if (viewToEditBtn){
    viewToEditBtn.addEventListener('click', ()=>{
      const vm = modal('viewModal'); if (vm) vm.hide();
      if (currentBurialId) loadEdit(currentBurialId);
    });
  }

  // ===== Edit loader & saver =====
  async function loadEdit(id){
    try{
      const r = await fetch('<?= URLROOT ?>/staff/getBurialDetails/'+encodeURIComponent(id), {credentials:'same-origin'});
      const d = await r.json();
      if(!d) return toast('Could not load record','error');

      const setVal = (id, v) => { const el = byId(id); if (el) el.value = v ?? ''; };
      setVal('e_burial_id', d.burial_id);
      setVal('e_burial_id_display', d.burial_id);
      setVal('e_plot_id', d.plot_id || '');
      setVal('e_plot_label', [d.block_title, d.plot_number].filter(Boolean).join(' - '));
      setVal('e_deceased_first_name', d.deceased_first_name);
      setVal('e_deceased_middle_name', d.deceased_middle_name);
      setVal('e_deceased_last_name', d.deceased_last_name);
      setVal('e_deceased_suffix', d.deceased_suffix);
      setVal('e_date_born', d.date_born ? d.date_born.substring(0,10) : '');
      setVal('e_date_died', d.date_died ? d.date_died.substring(0,10) : '');
      setVal('e_age', calculateAge(byId('e_date_born')?.value) || d.age || '');
      setVal('e_sex', d.sex);
      setVal('e_grave_level', d.grave_level);
      setVal('e_grave_type', d.grave_type);
      setVal('e_cause_of_death', d.cause_of_death);
      setVal('e_interment_full_name', d.interment_full_name);
      setVal('e_interment_relationship', d.interment_relationship);
      setVal('e_interment_contact_number', (d.interment_contact_number||'').replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3'));
      setVal('e_interment_email', d.interment_email);
      setVal('e_interment_address', d.interment_address);
      const curAddr = byId('e_current_address_display'); if (curAddr) curAddr.textContent = d.interment_address || 'No address saved.';
      setVal('e_payment_amount', (d.payment_amount ?? '0'));
      setVal('e_rental_date', fromDbToInput(d.rental_date || ''));
      const exp = calculateExpiry(byId('e_rental_date')?.value);
      setVal('e_expiry_date', exp.date);
      setVal('e_expiry_date_display', exp.display || (d.expiry_date ? new Date(d.expiry_date).toLocaleString('en-US',{year:'numeric',month:'long',day:'numeric',hour:'numeric',minute:'numeric',hour12:true}) : ''));

      // requirements
      (function(reqsString){
        const s = (reqsString || '').replace(/&#039;/g, "'");
        const reqs = s.split(',').map(x=>x.trim()).filter(Boolean);
        $$('.e_req').forEach(ch => ch.checked = reqs.includes(ch.value));
      })(d.requirements);

      const em = modal('editModal'); if (em) em.show();
    }catch(_){
      toast('Could not load record','error');
    }
  }

  const saveBtn = byId('saveEdit');
  if (saveBtn){
    saveBtn.addEventListener('click', async ()=>{
      const form = byId('editForm'); if(!form) return;
      let ok = true;
      ['e_deceased_first_name','e_deceased_last_name','e_date_died','e_interment_full_name','e_interment_relationship','e_payment_amount']
        .forEach(id => { const el = byId(id); if (!el || !el.value.trim()) { ok=false; el && el.classList.add('is-invalid'); } else { el.classList.remove('is-invalid'); } });

      const emailEl = byId('e_interment_email');
      const email = (emailEl?.value || '').trim();
      if (email && (email.length > 150 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))) {
        emailEl?.classList.add('is-invalid'); ok=false; toast('Please enter a valid email (max 150 chars).','error');
      } else { emailEl?.classList.remove('is-invalid'); }

      const phoneEl = byId('e_interment_contact_number');
      const isPH = v => /^09\d{2}\s\d{3}\s\d{4}$/.test((v||'').trim());
      if (phoneEl && phoneEl.value && !isPH(phoneEl.value)){ phoneEl.classList.add('is-invalid'); ok=false; toast('Please enter a valid PH mobile number.','error'); }
      else { phoneEl?.classList.remove('is-invalid'); }

      // requirements hidden
      const reqHidden = byId('e_requirements');
      if (reqHidden) reqHidden.value = $$('.e_req:checked').map(c=>c.value).join(', ');

      // bake address (only if may laman)
      (function bakeAddressForEdit(){
        const prov = byId('e_addr_province'), city = byId('e_addr_city'), brgy = byId('e_addr_brgy');
        const parts = [
          (byId('e_addr_line')?.value || '').trim(),
          (brgy && brgy.value) ? 'Brgy. ' + brgy.selectedOptions[0].text : '',
          (city && city.value) ? city.selectedOptions[0].text : '',
          (prov && prov.value) ? prov.selectedOptions[0].text : '',
          (byId('e_addr_zip')?.value || '').trim()
        ].filter(Boolean);
        const newAddress = parts.join(', ');
        const hidden = byId('e_interment_address');
        if (hidden && newAddress) hidden.value = newAddress;
      })();

      if (!ok) return;

      const formData = new FormData(form);
      formData.set('age', byId('e_age')?.value || '');
      try{
        const r = await fetch('<?= URLROOT ?>/staff/updateBurial', {
          method:'POST', credentials:'same-origin', body: new URLSearchParams(formData)
        });
        const resp = await r.json();
        if(resp && resp.ok){
          const em = modal('editModal'); if (em) em.hide();
          toast('Saved successfully','success');
          setTimeout(()=>location.reload(), 800);
        }else{
          themedSwal.fire({icon:'error',title:'Update Failed',text:(resp && resp.message) ? resp.message : 'An unknown error occurred.'});
        }
      }catch(_){
        themedSwal.fire({icon:'error',title:'Update Failed',text:'Network or server error.'});
      }
    });
  }

  // ===== QR =====
  function showQr(id){
    const img = byId('qrImg'), meta = byId('qrMeta');
    if (img) img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='+encodeURIComponent(id);
    if (meta) meta.innerText = id;
    const qm = modal('qrModal'); if (qm) qm.show();
  }

  // ===== Archive / Delete =====
  async function confirmArchive(id, row){
    const ans = await themedSwal.fire({icon:'question', title:'Archive this record?', text:'It will be moved to Archived Burials. You can restore it anytime.', showCancelButton:true, confirmButtonText:'Archive'});
    if(!ans.isConfirmed) return;
    try{
      const r = await fetch('<?= URLROOT ?>/staff/archiveBurial/'+encodeURIComponent(id), {
        method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'
      });
      const res = await r.json();
      if(res.ok){ row.style.transition='opacity .2s'; row.style.opacity='0'; setTimeout(()=>row.remove(), 200); toast('Archived','success'); }
      else { themedSwal.fire({icon:'error',title:'Archive failed',text:res.message||'Please try again.'}); }
    }catch(_){
      themedSwal.fire({icon:'error',title:'Archive failed',text:'Network or server error.'});
    }
  }

  async function confirmDelete(id, row){
    const ans = await themedSwal.fire({icon:'warning', title:'Delete record?', text:'This action cannot be undone and will free up the plot.', showCancelButton:true, confirmButtonText:'Delete'});
    if(!ans.isConfirmed) return;
    try{
      const r = await fetch('<?= URLROOT ?>/staff/deleteBurial/'+encodeURIComponent(id), {
        method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'
      });
      const res = await r.json();
      if(res.ok){ row.style.transition='opacity .2s'; row.style.opacity='0'; setTimeout(()=>row.remove(), 200); toast('Deleted','success'); }
      else { themedSwal.fire({icon:'error',title:'Delete failed',text:res.message||'Please try again.'}); }
    }catch(_){
      themedSwal.fire({icon:'error',title:'Delete failed',text:'Network or server error.'});
    }
  }

  // ===== Field reactions (safe binds) =====
  const born = byId('e_date_born');
  const rent = byId('e_rental_date');
  if (born) born.addEventListener('change', () => { const ageEl = byId('e_age'); if (ageEl) ageEl.value = calculateAge(born.value); });
  if (rent)  rent.addEventListener('change', () => {
    const {date, display} = calculateExpiry(rent.value);
    const expDisplay = byId('e_expiry_date_display'), expHidden = byId('e_expiry_date');
    if (expDisplay) expDisplay.value = display;
    if (expHidden)  expHidden.value  = date;
  });

  // Phone mask
  const phoneEl = byId('e_interment_contact_number');
  if (phoneEl){
    phoneEl.addEventListener('input', ev=>{
      let d = ev.target.value.replace(/\D/g,'').slice(0,11);
      ev.target.value = d.length<=4?d : d.length<=7? d.slice(0,4)+' '+d.slice(4) : d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7);
    });
  }

  // ===== PSGC cascading (optional; keeps your original feature) =====
  (function initPSGC(){
    const api='https://psgc.gitlab.io/api';
    const prov=byId('e_addr_province'), city=byId('e_addr_city'), brgy=byId('e_addr_brgy');
    if (!prov || !city || !brgy) return;
    function fill(sel,list,ph='Select'){
      sel.innerHTML=`<option value="">${ph}</option>`;
      list.forEach(x=>{const o=document.createElement('option');o.value=x.code||x.id||x.name;o.textContent=x.name;sel.appendChild(o);});
    }
    fetch(`${api}/provinces/`).then(r=>r.json()).then(list=>{
      list.sort((a,b)=>a.name.localeCompare(b.name)); fill(prov,list);
    }).catch(()=>{});
    prov.addEventListener('change',()=>{
      city.disabled=true; brgy.disabled=true; fill(city,[]); fill(brgy,[]);
      const c=prov.value; if(!c) return;
      fetch(`${api}/provinces/${c}/cities-municipalities/`).then(r=>r.json()).then(list=>{
        list.sort((a,b)=>a.name.localeCompare(b.name)); fill(city,list); city.disabled=false;
      }).catch(()=>{});
    });
    city.addEventListener('change',()=>{
      brgy.disabled=true; fill(brgy,[]);
      const c=city.value; if(!c) return;
      fetch(`${api}/cities-municipalities/${c}/barangays/`).then(r=>r.json()).then(list=>{
        list.sort((a,b)=>a.name.localeCompare(b.name)); fill(brgy,list); brgy.disabled=false;
      }).catch(()=>{});
    });
  })();

})();
</script>



<?php require APPROOT . '/views/includes/staff_footer.php'; ?>
