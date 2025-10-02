<?php require APPROOT . '/views/includes/admin_header.php'; ?>

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

  @media (max-width:576px){
    .search-input{ max-width:46vw }
    .action-btn{ min-width:auto; padding:.42rem .58rem }
    .action-btn .btn-text{ display:none }
  }

  .table thead th{
    background:#7b1113; color:#fff; border-bottom:1px solid #5f0c0e;
    white-space:nowrap; font-weight:600;
  }
  .table tbody td, .table tbody th { border-color:#ececec; }
  .badge-soft{
    background:#f6f7f9;border:1px solid #e9ecef;color:#6c757d;font-weight:600
  }

  .actions i{ font-size:19px; cursor:pointer; margin:0 .32rem; }
  .actions i:hover{ transform:translateY(-1px) }
  .actions .i-view  { color:#0d6efd }
  .actions .i-print { color:#0dcaf0 }
  .actions .i-qr    { color:#6c757d }

  .btn-maroon{ background:#7b1113; border-color:#7b1113; color:#fff; }
  .btn-maroon:hover,.btn-maroon:focus{ background:#5f0c0e; border-color:#5f0c0e; color:#fff; }

  .expiry-badge{
    display:inline-block;font-size:.72rem;line-height:1;padding:.18rem .45rem;border-radius:999px;
    background:#f1f3f5;color:#6c757d;border:1px solid #e6e8ea;vertical-align:baseline;
  }
</style>

<div class="records-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Archived Burials</h2>

    <div class="d-flex align-items-center">
      <input id="tableSearch" type="search" class="form-control form-control-sm search-input"
             placeholder="Search name, burial id, plot, email…">

      <a href="<?= URLROOT ?>/admin/burialRecords"
         class="btn btn-secondary btn-sm ms-2 action-btn">
        <i class="fas fa-arrow-left"></i><span class="btn-text">Back to Records</span>
      </a>
    </div>
  </div>

  <div class="card p-3">
    <div class="table-responsive">
      <table id="archTable" class="table table-hover align-middle mb-0">
        <thead>
        <tr>
          <th>Plot</th>
          <th>Burial ID</th>
          <th>Name</th>
          <th>Grave Level &amp; Type</th>
          <th>Age</th>
          <th>Sex</th>
          <th>Rental</th>
          <th>IRH Email</th> <!-- NEW -->
          <th class="text-center">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($data['records'])): ?>
          <?php
          $fmtHuman = function($dt){ return $dt ? date("D-F d, Y \\a\\t h:i A", strtotime($dt)) : ''; };
          foreach ($data['records'] as $r):
            $yearsTxt = '—';
            if (!empty($r->rental_date) && !empty($r->expiry_date)) {
              try{
                $d1 = new DateTime($r->rental_date);
                $d2 = new DateTime($r->expiry_date);
                $years = $d1->diff($d2)->y;
                $yearsTxt = $years.' yr';
              }catch(Exception $e){
                $yearsTxt = '5 yr';
              }
            } elseif (!empty($r->expiry_date)) {
              $yearsTxt = '5 yr';
            }
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
              <i class="fas fa-eye i-view"    data-bs-toggle="tooltip" title="View"  data-action="view"></i>
              <i class="fas fa-print i-print" data-bs-toggle="tooltip" title="Print" data-action="print"></i>
              <i class="fas fa-qrcode i-qr"   data-bs-toggle="tooltip" title="QR"    data-action="qr"></i>
              <button type="button" class="btn btn-sm btn-maroon ms-1" data-action="restore">Restore</button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center py-4 text-muted">No archived records.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- View details modal -->
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

<!-- QR modal -->
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

<script>
(() => {
  [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].forEach(el => new bootstrap.Tooltip(el));

  const table      = document.getElementById('archTable');
  const viewModal  = new bootstrap.Modal('#viewModal');
  const printModal = new bootstrap.Modal('#printPicker');
  const qrModal    = new bootstrap.Modal('#qrModal');

  let currentBurialId = '';

  document.getElementById('tableSearch').addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(tr => {
      tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
  });

  table.addEventListener('click', async (e) => {
    const control = e.target.closest('[data-action]');
    if (!control) return;

    const tr = control.closest('tr');
    currentBurialId = tr.dataset.burialId;

    switch (control.dataset.action) {
      case 'view':    return handleView(currentBurialId);
      case 'print':   return printModal.show();
      case 'qr':      return showQr(currentBurialId);
      case 'restore': return confirmRestore(currentBurialId, tr);
    }
  });

  document.getElementById('printPicker').addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-print]');
    if(!btn) return;
    printModal.hide();
    let url = '';
    if (btn.dataset.print==='form')     url='<?= URLROOT ?>/admin/printBurialForm/'+encodeURIComponent(currentBurialId);
    if (btn.dataset.print==='contract') url='<?= URLROOT ?>/admin/printContract/'+encodeURIComponent(currentBurialId);
    if (btn.dataset.print==='qr')       url='<?= URLROOT ?>/admin/printQrTicket/'+encodeURIComponent(currentBurialId);
    window.open(url, '_blank');
  });

  async function handleView(id){
    const d = await fetch('<?= URLROOT ?>/admin/getBurialDetails/'+encodeURIComponent(id))
      .then(r=>r.json()).catch(()=>null);
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
        ${row('Email', d.interment_email||'')}          <!-- NEW -->
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
    const url='https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='+encodeURIComponent(id);
    document.getElementById('qrImg').src=url;
    document.getElementById('qrMeta').innerText=id;
    qrModal.show();
  }

  async function confirmRestore(id, row){
    const ans = await Swal.fire({
      icon:'question',
      title:'Restore this record?',
      text:'It will return to the active Burial Records.',
      showCancelButton:true,
      confirmButtonText:'Restore',
      confirmButtonColor:'#7b1113'
    });
    if(!ans.isConfirmed) return;

    const res = await fetch('<?= URLROOT ?>/admin/restoreBurial/'+encodeURIComponent(id), {
      method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.json()).catch(()=>({ok:false}));

    if(res.ok){
      row.style.transition='opacity .2s'; row.style.opacity='0';
      setTimeout(()=>row.remove(), 200);
      toast('Restored','success');
    }else{
      Swal.fire({icon:'error',title:'Restore failed',text:res.message||'Please try again.'});
    }
  }

  function toast(title,icon='success'){
    Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:1700,icon,title});
  }
})();
</script>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>
