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

  @media (max-width:576px){
    .search-input{ max-width:46vw }
    .action-btn{ min-width:auto; padding:.42rem .58rem }
    .action-btn .btn-text{ display:none }
  }

  .table thead th{
    background: var(--maroon, #800000); color:#fff; border-bottom:1px solid #6a0000;
    white-space:nowrap; font-weight:600;
  }
  .table tbody td, .table tbody th { border-color:#ececec; }
  .badge-soft{
    background:#f6f7f9;border:1px solid #e9ecef;color:#6c757d;font-weight:600
  }

  .actions i{ font-size:19px; cursor:pointer; margin:0 .32rem; transition:.2s; }
  .actions i:hover{ transform:translateY(-1px); }

  /* maroon icons */
  .actions .i-view,
  .actions .i-print,
  .actions .i-qr {
    color: var(--maroon, #800000);
  }
  .actions .i-view:hover,
  .actions .i-print:hover,
  .actions .i-qr:hover {
    color: #6a0000;
  }

  .btn-maroon{ background:#800000; border-color:#800000; color:#fff; }
  .btn-maroon:hover,.btn-maroon:focus{ background:#6a0000; border-color:#6a0000; color:#fff; }

  .expiry-badge{
    display:inline-block;font-size:.72rem;line-height:1;padding:.18rem .45rem;border-radius:999px;
    background:#f1f3f5;color:#6c757d;border:1px solid #e6e8ea;vertical-align:baseline;
  }

  /* make sure icons are clickable above table overflow */
  td.actions{ position:relative; z-index:1; }
</style>

<div class="records-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Archived Burials</h2>

    <div class="d-flex align-items-center">
      <input id="tableSearch" type="search" class="form-control form-control-sm search-input"
             placeholder="Search name, burial id, plot, email…">

      <a href="<?= URLROOT ?>/staff/burialRecords"
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
          <th>IRH Email</th>
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
                $yearsTxt = $d1->diff($d2)->y . ' yr';
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
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
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
(function(){
  // Helpers
  const $  = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const byId = (id) => document.getElementById(id);
  const modal = (id) => { const el = byId(id); if(!el) return null; try{ return bootstrap.Modal.getOrCreateInstance(el); }catch(e){ return null; } };

  // Maroon themed Swal
  const themedSwal = Swal.mixin({
    customClass: { confirmButton: 'btn btn-maroon mx-2', cancelButton: 'btn btn-secondary mx-2' },
    buttonsStyling: false
  });
  const toast = (title,icon='success') => themedSwal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:1700,icon,title});

  // Tooltips
  try { $$('[data-bs-toggle="tooltip"]').forEach(el => { try{ new bootstrap.Tooltip(el);}catch(_){}}); } catch(_){}

  // DOM
  const table = byId('archTable');
  let currentBurialId = '';

  // Search
  const search = byId('tableSearch');
  if (search && table){
    search.addEventListener('input', (e)=>{
      const term = (e.target.value || '').toLowerCase();
      table.querySelectorAll('tbody tr').forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none';
      });
    });
  }

  // Delegation for actions
  document.addEventListener('click', async (e)=>{
    const ctl = e.target.closest('[data-action]');
    if (!ctl) return;

    const tr = ctl.closest('tr'); if(!tr) return;
    currentBurialId = tr.getAttribute('data-burial-id') || tr.dataset.burialId || '';
    const act = ctl.dataset.action;

    if (act === 'view')   return handleView(currentBurialId);
    if (act === 'print')  { const m = modal('printPicker'); if (m) m.show(); return; }
    if (act === 'qr')     return showQr(currentBurialId);
    if (act === 'restore')return confirmRestore(currentBurialId, tr);
  });

  // Print picker buttons -> staff routes
  const printPicker = byId('printPicker');
  if (printPicker){
    printPicker.addEventListener('click', (e)=>{
      const btn = e.target.closest('button[data-print]'); if(!btn) return;
      const m = modal('printPicker'); if (m) m.hide();
      let url = '';
      if (btn.dataset.print==='form')     url='<?= URLROOT ?>/staff/printBurialForm/'+encodeURIComponent(currentBurialId);
      if (btn.dataset.print==='contract') url='<?= URLROOT ?>/staff/printContract/'+encodeURIComponent(currentBurialId);
      if (btn.dataset.print==='qr')       url='<?= URLROOT ?>/staff/printQrTicket/'+encodeURIComponent(currentBurialId);
      if (url) window.open(url + '?autoprint=1', '_blank');
    });
  }

  // View
  async function handleView(id){
    try{
      const d = await fetch('<?= URLROOT ?>/staff/getBurialDetails/'+encodeURIComponent(id), {credentials:'same-origin'}).then(r=>r.json());
      if(!d){ return toast('Could not load record','error'); }
      const neatPlot = [d.block_title, d.plot_number].filter(Boolean).join(' - ') || '';
      const row=(k,v)=>`<tr><th class="text-nowrap pe-3">${k}</th><td>${v??''}</td></tr>`;
      const html = `
        <table class="table table-sm mb-0">
          ${row('Burial ID', d.burial_id)}
          ${row('Plot', neatPlot)}
          ${row('Deceased', [d.deceased_first_name,d.deceased_middle_name,d.deceased_last_name,d.deceased_suffix].filter(Boolean).join(' '))}
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
          ${row('Requirements', (d.requirements||'').replace(/&#039;/g,"'"))}
        </table>`;
      const vb = byId('viewBody'); if (vb) vb.innerHTML = html;
      const vm = modal('viewModal'); if (vm) vm.show();
    }catch(_){
      toast('Could not load record','error');
    }
  }

  // QR
  function showQr(id){
    const img = byId('qrImg'), meta = byId('qrMeta');
    if (img) img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='+encodeURIComponent(id);
    if (meta) meta.innerText = id;
    const m = modal('qrModal'); if (m) m.show();
  }

  // Restore
  async function confirmRestore(id, row){
    const ans = await themedSwal.fire({
      icon:'question',
      title:'Restore this record?',
      text:'It will return to the active Burial Records.',
      showCancelButton:true,
      confirmButtonText:'Restore'
    });
    if(!ans.isConfirmed) return;

    try{
      const res = await fetch('<?= URLROOT ?>/staff/restoreBurial/'+encodeURIComponent(id), {
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest'},
        credentials:'same-origin'
      }).then(r=>r.json());
      if(res && res.ok){
        row.style.transition='opacity .2s'; row.style.opacity='0';
        setTimeout(()=>row.remove(), 200);
        toast('Restored','success');
      }else{
        themedSwal.fire({icon:'error',title:'Restore failed',text:(res && res.message) || 'Please try again.'});
      }
    }catch(_){
      themedSwal.fire({icon:'error',title:'Restore failed',text:'Network or server error.'});
    }
  }
})();
</script>

<?php require APPROOT . '/views/includes/staff_footer.php'; ?>
