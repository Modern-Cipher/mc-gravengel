<?php require APPROOT . '/views/includes/staff_header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4@5/bootstrap-4.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<style>
  .records-wrap { margin-top:.25rem }
  .search-input { max-width:300px; padding:.38rem .6rem; }
  .action-btn{ display:inline-flex; align-items:center; justify-content:center; gap:.45rem; white-space:nowrap; min-width:130px; padding:.45rem .75rem; line-height:1.1; }
  .action-btn i{ font-size:15px } .action-btn .btn-text{ display:inline }
  @media (max-width:576px){ .search-input{ max-width:46vw } .action-btn{ min-width:auto; padding:.42rem .58rem } .action-btn .btn-text{ display:none } }
  .table thead th{ background:#7b1113; color:#fff; border-bottom:1px solid #5f0c0e; white-space:nowrap; font-weight:600; }
  .table tbody td, .table tbody th { border-color:#ececec; }
  .badge-soft{ background:#f6f7f9;border:1px solid #e9ecef;color:#6c757d;font-weight:600 }
  .actions i{ font-size:19px; cursor:pointer; margin:0 .32rem; }
  .actions i:hover{ transform:translateY(-1px) }
  .actions .i-view { color:#0d6efd } .actions .i-print{ color:#0dcaf0 } .actions .i-qr{ color:#6c757d } .actions .i-archive{ color:#fd7e14 } .actions .i-del{ color:#dc3545 }
  .expiry-badge{ display:inline-block; font-size:.72rem; line-height:1; padding:.18rem .45rem; border-radius:999px; background:#f1f3f5; color:#6c757d; border:1px solid #e6e8ea; vertical-align:baseline; }
  #termsModal .modal-body{ max-height:60vh; overflow:auto; }
</style>

<div class="records-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Burial Records</h2>

    <div class="d-flex align-items-center">
      <input id="tableSearch" type="search" class="form-control form-control-sm search-input"
             placeholder="Search name, burial id, plot, email…">

      <!-- STAFF: Add Burial opens TOS modal first -->
      <button id="addBurialBtn" type="button" class="btn btn-danger btn-sm ms-2 action-btn">
        <i class="fas fa-plus"></i><span class="btn-text">Add Burial</span>
      </button>

      <a class="btn btn-warning btn-sm ms-2 action-btn" title="Archive" href="<?= URLROOT ?>/staff/archivedBurials">
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
            <th>IRH Email</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($data['records'])): ?>
          <?php $fmtHuman = fn($dt)=> $dt ? date("D-F d, Y \\a\\t h:i A", strtotime($dt)) : ''; ?>
          <?php foreach ($data['records'] as $r): ?>
            <?php
              $yearsTxt = '—';
              if (!empty($r->rental_date) && !empty($r->expiry_date)) {
                try{ $d1=new DateTime($r->rental_date); $d2=new DateTime($r->expiry_date); $yearsTxt=$d1->diff($d2)->y.' yr'; }
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
                <?php if ($expiryLabel): ?><span class="expiry-badge ms-2">expires <?= htmlspecialchars($expiryLabel) ?></span><?php endif; ?>
              </td>
              <td title="<?= htmlspecialchars($email) ?>"><?= $emailDisp ?></td>
              <td class="text-center actions">
                <i class="fas fa-eye i-view"           data-bs-toggle="tooltip" title="View"    data-action="view"></i>
                <i class="fas fa-print i-print"        data-bs-toggle="tooltip" title="Print"   data-action="print"></i>
                <i class="fas fa-qrcode i-qr"          data-bs-toggle="tooltip" title="QR"      data-action="qr"></i>
                <i class="fas fa-box-archive fa-archive i-archive" data-bs-toggle="tooltip" title="Archive" data-action="archive"></i>
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

<!-- Print picker -->
<div class="modal fade" id="printPicker" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title mb-0">Print</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
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

<!-- Terms & Conditions (same content as admin) -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Terms &amp; Conditions</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Terms &amp; Conditions as a whole.</strong> By proceeding to add a new burial record, you hereby agree to the following terms:</p>
        <ol class="mb-3">
          <li><strong>Accuracy of Information</strong> …</li>
          <li><strong>Authorization</strong> …</li>
          <li><strong>Data Privacy</strong> …</li>
          <li><strong>Burial Plot Assignment</strong> …</li>
          <li><strong>Rental Duration Compliance</strong> …</li>
          <li><strong>Modification &amp; Deletion</strong> …</li>
          <li><strong>Audit &amp; Logs</strong> …</li>
          <li><strong>Acceptance</strong> …</li>
        </ol>
        <div class="form-check"><input class="form-check-input" type="checkbox" id="agreeCheck"><label class="form-check-label" for="agreeCheck">I agree to the Terms &amp; Conditions</label></div>
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

  // Search
  document.getElementById('tableSearch').addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(tr => {
      tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
  });

  let currentBurialId = '';
  table.addEventListener('click', async (e) => {
    const icon = e.target.closest('[data-action]');
    if (!icon) return;
    const tr = icon.closest('tr');
    currentBurialId = tr.dataset.burialId;

    switch (icon.dataset.action) {
      case 'view':  return handleView(currentBurialId);
      case 'print': return printPicker.show();
      case 'qr':    return showQr(currentBurialId);
      case 'archive': return confirmArchive(currentBurialId, tr);
    }
  });

  // Print routing — still uses admin printers (single source)
  document.getElementById('printPicker').addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-print]'); if(!btn) return;
    printPicker.hide();
    let url = '';
    if (btn.dataset.print==='form')     url='<?= URLROOT ?>/admin/printBurialForm/'+encodeURIComponent(currentBurialId);
    if (btn.dataset.print==='contract') url='<?= URLROOT ?>/admin/printContract/'+encodeURIComponent(currentBurialId);
    if (btn.dataset.print==='qr')       url='<?= URLROOT ?>/admin/printQrTicket/'+encodeURIComponent(currentBurialId);
    window.open(url, '_blank');
  });

  async function handleView(id){
    // READ from admin endpoint (shared)
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
        ${row('Email', d.interment_email||'')}
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

  function showQr(id){
    Swal.fire({
      title:'QR Code',
      html:`<img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(id)}" alt="QR"> <div class="small mt-2">${id}</div>`,
      showConfirmButton:true
    });
  }

  async function confirmArchive(id, row){
    const ans = await Swal.fire({ icon:'question', title:'Archive this record?', text:'It will be moved to Archived Burials.', showCancelButton:true, confirmButtonText:'Archive', confirmButtonColor:'#fd7e14' });
    if(!ans.isConfirmed) return;

    // WRITE via staff endpoint so updated_by = staff
    const res = await fetch('<?= URLROOT ?>/staff/archiveBurial', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
      body: new URLSearchParams({burial_id:id}).toString()
    }).then(r=>r.json()).catch(()=>({ok:false}));

    if(res.ok){ row.style.transition='opacity .2s'; row.style.opacity='0'; setTimeout(()=>row.remove(),200); toast('Archived','success'); }
    else{ Swal.fire({icon:'error',title:'Archive failed',text:res.msg||'Please try again.'}); }
  }

  function toast(title,icon='success'){ Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:1700,icon,title}); }

  // ===== TOS wiring (IMPORTANT: go to /staff/addBurial) =====
  const tosModal = new bootstrap.Modal(document.getElementById('termsModal'));
  document.getElementById('addBurialBtn').addEventListener('click', ()=>{ document.getElementById('agreeCheck').checked=false; document.getElementById('proceedBtn').disabled=true; tosModal.show(); });
  document.getElementById('agreeCheck').addEventListener('change', (e)=>{ document.getElementById('proceedBtn').disabled = !e.target.checked; });
  document.getElementById('proceedBtn').addEventListener('click', ()=>{ tosModal.hide(); window.location.href = '<?= URLROOT ?>/staff/addBurial'; });
})();
</script>

<?php require APPROOT . '/views/includes/staff_footer.php'; ?>
