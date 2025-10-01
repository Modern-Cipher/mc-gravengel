<?php require APPROOT . '/views/includes/admin_header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4@5/bootstrap-4.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<style>
  .records-wrap { margin-top:.25rem }
  .search-input { max-width:300px; padding:.38rem .6rem; }

  /* ===== Toolbar buttons: wide + no wrap; switch to icon-only on mobile ===== */
  .action-btn{
    display:inline-flex; align-items:center; justify-content:center;
    gap:.45rem;
    white-space:nowrap;
    min-width:130px;
    padding:.45rem .75rem;
    line-height:1.1;
  }
  .action-btn i{ font-size:15px }
  .action-btn .btn-text{ display:inline }

  @media (max-width: 576px){
    .search-input{ max-width:46vw }
    .action-btn{ min-width:auto; padding:.42rem .58rem }
    .action-btn .btn-text{ display:none }   /* icon-only on mobile */
  }

  /* ==== TABLE HEADER — MAROON ==== */
  .table thead th {
    background:#7b1113; color:#fff; border-bottom:1px solid #5f0c0e;
    white-space:nowrap; font-weight:600;
  }
  .table tbody td, .table tbody th { border-color:#ececec; }

  .badge-soft{
    background:#f6f7f9;border:1px solid #e9ecef;color:#6c757d;font-weight:600
  }

  /* row action icons */
  .actions i { font-size:19px; cursor:pointer; margin:0 .32rem; }
  .actions i:hover { transform:translateY(-1px) }
  .actions .i-view    { color:#0d6efd }
  .actions .i-print   { color:#0dcaf0 }
  .actions .i-qr      { color:#6c757d }
  .actions .i-edit    { color:#ffc107 }
  .actions .i-archive { color:#fd7e14 } /* orange */
  .actions .i-del     { color:#dc3545 }

  .modal-header.bg-danger { background:#a51e1e !important }

  /* small expiry pill badge */
  .expiry-badge{
    display:inline-block;
    font-size:.72rem;
    line-height:1;
    padding:.18rem .45rem;
    border-radius:999px;
    background:#f1f3f5;
    color:#6c757d;
    border:1px solid #e6e8ea;
    vertical-align:baseline;
  }

  /* TOS modal body scroll */
  #termsModal .modal-body{ max-height:60vh; overflow:auto; }
</style>

<div class="records-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Burial Records</h2>

    <div class="d-flex align-items-center">
      <input id="tableSearch" type="search" class="form-control form-control-sm search-input"
             placeholder="Search name, burial id, plot…">

      <!-- Add Burial now opens TOS modal first -->
      <button id="addBurialBtn" type="button"
              class="btn btn-danger btn-sm ms-2 action-btn">
        <i class="fas fa-plus"></i><span class="btn-text">Add Burial</span>
      </button>

      <!-- ARCHIVE button goes to Archive page -->
      <a class="btn btn-warning btn-sm ms-2 action-btn" title="Archive"
         href="<?= URLROOT ?>/admin/archivedBurials">
        <!-- use both classes for compatibility (fa-box-archive in FA6, fa-archive in FA5) -->
        <i class="fas fa-box-archive fa-archive"></i><span class="btn-text">Archive</span>
      </a>

      <button class="btn btn-info btn-sm text-white ms-2 action-btn">
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
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($data['records'])): ?>
          <?php
          $fmtHuman = function($dt){
            return $dt ? date("D-F d, Y \\a\\t h:i A", strtotime($dt)) : '';
          };
          ?>
          <?php foreach ($data['records'] as $r): ?>
            <?php
              $yearsTxt = '—';
              if (!empty($r->rental_date) && !empty($r->expiry_date)) {
                try{
                  $d1 = new DateTime($r->rental_date);
                  $d2 = new DateTime($r->expiry_date);
                  $years = $d1->diff($d2)->y;
                  $yearsTxt = $years . ' yr';
                }catch(Exception $e){
                  $yearsTxt = '5 yr';
                }
              } elseif (!empty($r->expiry_date)) {
                $yearsTxt = '5 yr';
              }
              $expiryLabel = $fmtHuman($r->expiry_date ?? null);
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
              <td class="text-center actions">
                <i class="fas fa-eye i-view"           data-bs-toggle="tooltip" title="View"    data-action="view"></i>
                <i class="fas fa-print i-print"        data-bs-toggle="tooltip" title="Print"   data-action="print"></i>
                <i class="fas fa-qrcode i-qr"          data-bs-toggle="tooltip" title="QR"      data-action="qr"></i>
                <i class="fas fa-pen-to-square i-edit" data-bs-toggle="tooltip" title="Edit"    data-action="edit"></i>
                <!-- archive icon (box) + fallback archive icon -->
                <i class="fas fa-box-archive fa-archive i-archive" data-bs-toggle="tooltip" title="Archive" data-action="archive"></i>
                <i class="fas fa-trash-alt i-del"      data-bs-toggle="tooltip" title="Delete"  data-action="delete"></i>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center py-4 text-muted">No burial records found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Print picker -->
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

<!-- View details -->
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
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit -->
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
          <div class="col-md-3"><label class="form-label">Plot</label><input type="text" class="form-control form-control-sm" id="e_plot_label" disabled></div>
          <div class="col-md-3"><label class="form-label">First Name</label><input type="text" class="form-control form-control-sm" name="deceased_first_name" id="e_fn"></div>
          <div class="col-md-3"><label class="form-label">Last Name</label><input type="text" class="form-control form-control-sm" name="deceased_last_name" id="e_ln"></div>
          <div class="col-md-3"><label class="form-label">Age</label><input type="number" class="form-control form-control-sm" name="age" id="e_age"></div>
          <div class="col-md-3"><label class="form-label">Sex</label>
            <select class="form-select form-select-sm" name="sex" id="e_sex">
              <option value="">Select</option><option>male</option><option>female</option><option>other</option>
            </select>
          </div>
          <div class="col-md-9"><label class="form-label">IRH Full Name</label><input type="text" class="form-control form-control-sm" name="interment_full_name" id="e_irh"></div>
          <div class="col-md-4"><label class="form-label">Relationship</label><input type="text" class="form-control form-control-sm" name="interment_relationship" id="e_rel"></div>
          <div class="col-md-4"><label class="form-label">Contact</label><input type="text" class="form-control form-control-sm" name="interment_contact_number" id="e_contact"></div>
          <div class="col-md-4"><label class="form-label">Grave Level / Type</label>
            <div class="d-flex gap-2">
              <input type="text" class="form-control form-control-sm" name="grave_level" id="e_lvl">
              <input type="text" class="form-control form-control-sm" name="grave_type" id="e_type">
            </div>
          </div>
          <div class="col-12"><label class="form-label">Address</label><input type="text" class="form-control form-control-sm" name="interment_address" id="e_addr"></div>
          <div class="col-12"><label class="form-label">Cause of Death</label><input type="text" class="form-control form-control-sm" name="cause_of_death" id="e_cod"></div>
        </form>
      </div>
      <div class="modal-footer py-2">
        <button class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button id="saveEdit" class="btn btn-light btn-sm text-danger border">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- QR -->
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

<!-- ===== Terms & Conditions modal (shown before Add Burial) ===== -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Terms &amp; Conditions</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Terms &amp; Conditions as a whole.</strong> By proceeding to add a new burial record, you hereby agree to the following terms:</p>
        <ol class="mb-3">
          <li><strong>Accuracy of Information</strong> You affirm that all burial details, including the deceased’s identity, burial plot allocation, and rental duration, are accurate and verified to the best of your knowledge. Providing false or misleading data may result in disciplinary or legal action.</li>
          <li><strong>Authorization</strong> You confirm that you are authorized to register or modify burial records in this system either as an official cemetery staff member or as a duly appointed representative of the family or estate of the deceased.</li>
          <li><strong>Data Privacy</strong> All personal information entered into this system is confidential and subject to the Cemetery’s Privacy Policy. Data collected will be used solely for burial registry purposes and will not be shared without proper authorization or legal mandate.</li>
          <li><strong>Burial Plot Assignment</strong> Once a burial plot is recorded and confirmed, it is considered provisionally reserved and cannot be reassigned unless a formal cancellation or reassignment request is submitted and approved.</li>
          <li><strong>Rental Duration Compliance</strong> The rental duration selected must comply with the cemetery’s policies and regulations. Renewal, extension, or repurchase of rights beyond the stated duration requires formal application and payment.</li>
          <li><strong>Modification &amp; Deletion</strong> Modification or deletion of burial records can only be done with valid administrative clearance and appropriate documentation. Unauthorized modifications are strictly prohibited.</li>
          <li><strong>Audit &amp; Logs</strong> All interactions with this system are logged for audit purposes. Misuse or fraudulent use of the system will be subject to internal investigation and may be reported to authorities.</li>
          <li><strong>Acceptance</strong> By clicking “I Agree” and proceeding to add a burial, you acknowledge that you have read, understood, and accepted the terms and conditions outlined above.</li>
        </ol>
        <div class="form-check">
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
(() => {
  [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].forEach(el => new bootstrap.Tooltip(el));

  const table = document.getElementById('burialTable');
  const printPicker = new bootstrap.Modal('#printPicker');
  const viewModal   = new bootstrap.Modal('#viewModal');
  const editModal   = new bootstrap.Modal('#editModal');
  const qrModal     = new bootstrap.Modal('#qrModal');

  // TOS modal wiring
  const tosModal = new bootstrap.Modal(document.getElementById('termsModal'));
  const addBtn   = document.getElementById('addBurialBtn');
  const agreeCb  = document.getElementById('agreeCheck');
  const proceed  = document.getElementById('proceedBtn');

  addBtn.addEventListener('click', () => {
    agreeCb.checked = false;
    proceed.disabled = true;
    tosModal.show();
  });
  agreeCb.addEventListener('change', () => { proceed.disabled = !agreeCb.checked; });
  proceed.addEventListener('click', () => {
    tosModal.hide();
    window.location = "<?= URLROOT ?>/admin/addBurial";
  });

  let currentBurialId = '';

  document.getElementById('tableSearch').addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(tr => {
      tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
  });

  table.addEventListener('click', async (e) => {
    const icon = e.target.closest('i[data-action]');
    if (!icon) return;
    const tr = icon.closest('tr');
    currentBurialId = tr.dataset.burialId;

    switch (icon.dataset.action) {
      case 'view':    return handleView(currentBurialId);
      case 'print':   return printPicker.show();
      case 'qr':      return showQr(currentBurialId);
      case 'edit':    return loadEdit(currentBurialId);
      case 'archive': return confirmArchive(currentBurialId, tr);
      case 'delete':  return confirmDelete(currentBurialId, tr);
    }
  });

  document.getElementById('printPicker').addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-print]');
    if(!btn) return;
    printPicker.hide();
    const what = btn.dataset.print;
    let url = '';
    if (what==='form')     url='<?= URLROOT ?>/admin/printBurialForm/'+encodeURIComponent(currentBurialId);
    if (what==='contract') url='<?= URLROOT ?>/admin/printContract/'+encodeURIComponent(currentBurialId);
    if (what==='qr')       url='<?= URLROOT ?>/admin/printQrTicket/'+encodeURIComponent(currentBurialId);
    window.open(url, '_blank');
  });

  async function handleView(id){
    const d = await fetch('<?= URLROOT ?>/admin/getBurialDetails/'+encodeURIComponent(id)).then(r=>r.json()).catch(()=>null);
    if(!d){ toast('Could not load record','error'); return; }
    const row=(k,v)=>`<tr><th class="text-nowrap pe-3">${k}</th><td>${v??''}</td></tr>`;
    const html = `
      <table class="table table-sm mb-0">
        ${row('Burial ID', d.burial_id)}
        ${row('Plot', d.plot_number||'')}
        ${row('Deceased', [d.deceased_first_name,d.deceased_middle_name,d.deceased_last_name].filter(Boolean).join(' '))}
        ${row('Age / Sex', (d.age||'')+' '+(d.sex||''))}
        ${row('IRH', d.interment_full_name||'')}
        ${row('Relationship', d.interment_relationship||'')}
        ${row('Contact', d.interment_contact_number||'')}
        ${row('Address', d.interment_address||'')}
        ${row('Grave', (d.grave_level||'-')+' / '+(d.grave_type||'-'))}
        ${row('Cause of Death', d.cause_of_death||'')}
        ${row('Rental', d.rental_date||'')}
        ${row('Expiry', d.expiry_date||'')}
        ${row('Requirements', d.requirements||'')}
      </table>`;
    document.getElementById('viewBody').innerHTML = html;
    viewModal.show();
  }

  async function loadEdit(id){
    const d = await fetch('<?= URLROOT ?>/admin/getBurialDetails/'+encodeURIComponent(id)).then(r=>r.json()).catch(()=>null);
    if(!d){ toast('Could not load record','error'); return; }
    e('e_burial_id').value = d.burial_id;
    e('e_plot_label').value = d.plot_number||'';
    e('e_fn').value = d.deceased_first_name||'';
    e('e_ln').value = d.deceased_last_name||'';
    e('e_age').value = d.age||'';
    e('e_sex').value = d.sex||'';
    e('e_irh').value = d.interment_full_name||'';
    e('e_rel').value = d.interment_relationship||'';
    e('e_contact').value = d.interment_contact_number||'';
    e('e_addr').value = d.interment_address||'';
    e('e_lvl').value = d.grave_level||'';
    e('e_type').value = d.grave_type||'';
    e('e_cod').value = d.cause_of_death||'';
    editModal.show();
  }

  document.getElementById('saveEdit').addEventListener('click', async ()=>{
    const form = document.getElementById('editForm');
    const payload = new URLSearchParams(new FormData(form)).toString();
    const resp = await fetch('<?= URLROOT ?>/admin/updateBurial', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:payload
    }).then(r=>r.json()).catch(()=>({ok:false}));
    if(resp.ok){ editModal.hide(); toast('Saved','success'); setTimeout(()=>location.reload(),500); }
    else{ toast(resp.message||'Save failed','error'); }
  });

  function showQr(id){
    const url='https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='+encodeURIComponent(id);
    document.getElementById('qrImg').src=url;
    document.getElementById('qrMeta').innerText=id;
    qrModal.show();
  }

  async function confirmArchive(id, row){
    const ans = await Swal.fire({
      icon:'question',
      title:'Archive this record?',
      text:'It will be moved to Archived Burials. You can restore it anytime.',
      showCancelButton:true,
      confirmButtonText:'Archive',
      confirmButtonColor:'#fd7e14'
    });
    if(!ans.isConfirmed) return;

    const res = await fetch('<?= URLROOT ?>/admin/archiveBurial/'+encodeURIComponent(id), {
      method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.json()).catch(()=>({ok:false}));

    if(res.ok){
      row.style.transition='opacity .2s'; row.style.opacity='0';
      setTimeout(()=>row.remove(), 200);
      toast('Archived','success');
    }else{
      Swal.fire({icon:'error',title:'Archive failed',text:res.message||'Please try again.'});
    }
  }

  async function confirmDelete(id, row){
    const ans = await Swal.fire({
      icon:'warning', title:'Delete record?', text:'This cannot be undone.',
      showCancelButton:true, confirmButtonText:'Delete', confirmButtonColor:'#dc3545'
    });
    if(!ans.isConfirmed) return;

    const res = await fetch('<?= URLROOT ?>/admin/deleteBurial/'+encodeURIComponent(id), {
      method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.json()).catch(()=>({ok:false}));

    if(res.ok){
      row.style.transition='opacity .2s'; row.style.opacity='0';
      setTimeout(()=>row.remove(), 200);
      toast('Deleted','success');
    }else{
      Swal.fire({icon:'error',title:'Delete failed',text:res.message||'Please try again.'});
    }
  }

  function toast(title,icon='success'){
    Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:1700,icon,title});
  }
  function e(id){ return document.getElementById(id); }
})();
</script>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>
