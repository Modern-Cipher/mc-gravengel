<?php require APPROOT . '/views/includes/staff_header.php'; ?>
<style>
  /* --- General Styles --- */
  .table-wrapper { max-height: 400px; overflow-y: auto; }
  .table thead th { position: sticky; top: 0; z-index: 1; background: var(--maroon, #800000); color: #fff; }
  .action-btn { min-width: 150px; }
  .badge { font-size: 0.8em; }
  .copy-btn i { pointer-events: none; }

  /* =========================================
     1. MAROON THEME VARIABLES
     ========================================= */
  :root{
    --g-maroon:#800000;
    --g-maroon-rgb:128,0,0;
    --g-maroon-dark:#6a0000;
    --bs-primary:var(--g-maroon);
    --bs-primary-rgb:var(--g-maroon-rgb);
    --bs-primary-text:#fff;
    --bs-primary-bg-subtle:#fbe6e6;
    --bs-primary-border-subtle:#f3cccc;
    --bs-link-color:var(--g-maroon-dark);
    --bs-link-color-rgb:106,0,0;
    --bs-link-hover-color:var(--g-maroon-dark);
    --bs-btn-focus-shadow-rgb:var(--g-maroon-rgb);
  }

  /* =========================================
     2. MODAL MAROON THEME STYLES
     ========================================= */
  .modal{ padding-top: var(--header-h,56px); }
  .modal-dialog{ width:95vw; max-width:900px; margin-top:1rem; margin-bottom:1rem; }
  .modal-body{ max-height: calc(85vh - 200px); overflow-y:auto; }
  .modal-content{ border:2px solid var(--g-maroon-dark); border-radius:.5rem; }
  .modal-header{ background:var(--g-maroon)!important; color:#fff; border-bottom:2px solid var(--g-maroon-dark); }
  .modal-body hr{ border-top:1px solid var(--bs-primary-border-subtle); }
  .modal-body .input-group-text{
    background:var(--bs-primary-bg-subtle);
    border-color:var(--bs-primary-border-subtle);
    font-weight:500; color:var(--g-maroon-dark);
  }
  .modal-body h6{ color:var(--g-maroon-dark); font-weight:700; }
  .modal-footer{ background:var(--bs-primary-bg-subtle); border-top:1px solid var(--bs-primary-border-subtle); }
  .btn-primary{ background:var(--g-maroon); border-color:var(--g-maroon); }
  .btn-primary:hover{ background:var(--g-maroon-dark); border-color:var(--g-maroon-dark); }
  .modal-footer .btn-secondary{ background:#fff; border-color:var(--g-maroon); color:var(--g-maroon); }
  .modal-footer .btn-secondary:hover{ background:var(--g-maroon); color:#fff; }
</style>

<div class="container-fluid py-4">
  <h2 class="mb-4">Renewals (Staff)</h2>

  <div class="card mb-5">
    <div class="card-header" style="background-color: var(--g-maroon-dark,#6a1818); color:#fff;">
      <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>All Active Rentals</h5>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Plot</th>
              <th>Deceased Name</th>
              <th>IRH Name</th>
              <th>Expiry Date</th>
              <th>Status</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="for-renewal-table">
            <tr><td colspan="6" class="text-center text-muted">Calculating...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Renewal History</h5>
        <a href="<?= URLROOT ?>/staff/printRenewalHistory" target="_blank" class="btn btn-sm btn-secondary">
            <i class="fas fa-print me-1"></i> Generate Report
        </a>
    </div>
    <div class="card-body">
        <div class="table-wrapper">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Payer Name</th>
                        <th>New Expiry</th>
                        <th>Processed By</th>
                        <th>Receipt Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['history'])): ?>
                        <tr><td colspan="7" class="text-center text-muted">No renewal history found.</td></tr>
                    <?php else: foreach($data['history'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item->transaction_id ?? '') ?></td>
                            <td><?= date('M d, Y', strtotime($item->payment_date)) ?></td>
                            <td>₱ <?= number_format($item->payment_amount ?? 0, 2) ?></td>
                            <td><?= htmlspecialchars($item->payer_name ?? 'N/A') ?></td>
                            <td><?= date('M d, Y', strtotime($item->new_expiry_date)) ?></td>
                            <td><?= htmlspecialchars($item->processed_by ?? '') ?></td>
                            <td><?= htmlspecialchars($item->receipt_email_status ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="renewalModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Process Renewal (Staff)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="renewalForm">
          <input type="hidden" id="modal_burial_id" name="burial_id">
          <input type="hidden" id="modal_plot_id"   name="plot_id">

          <h6 class="mb-3">Burial Details</h6>
          <p><strong>Deceased:</strong> <span id="modal_deceased_name"></span></p>
          <p><strong>Current Expiry Date:</strong> <span id="modal_expiry_date"></span></p>
          <hr>

          <h6 class="mb-3">Interment Right Holder (IRH) Details</h6>
          <div class="input-group mb-2">
            <span class="input-group-text" style="width:120px;">IRH Name</span>
            <input type="text" id="modal_irh_name" class="form-control" readonly style="background:#e9ecef;">
            <button class="btn btn-outline-secondary copy-btn" type="button"
              data-copy-source="modal_irh_name" data-copy-target="payer_name" title="Copy to Payer's Name">
              <i class="fas fa-copy"></i> Copy
            </button>
          </div>
          <div class="input-group mb-4">
            <span class="input-group-text" style="width:120px;">IRH Email</span>
            <input type="text" id="modal_irh_email" class="form-control" readonly style="background:#e9ecef;">
            <button class="btn btn-outline-secondary copy-btn" type="button"
              data-copy-source="modal_irh_email" data-copy-target="payer_email" title="Copy to Payer's Email">
              <i class="fas fa-copy"></i> Copy
            </button>
          </div>
          <hr>

          <h6 class="mb-3">Payer's Information</h6>
          <div class="mb-3">
            <label for="payer_name" class="form-label">Payer's Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="payer_name" name="payer_name" required>
          </div>
          <div class="mb-3">
            <label for="payer_email" class="form-label">Payer's Email (for receipt)</label>
            <input type="email" class="form-control" id="payer_email" name="payer_email">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="payment_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" min="0" required value="5000">
            </div>
            <div class="col-md-6 mb-3">
              <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="payment_date" name="payment_date" required>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-danger" id="vacateBtn">Do Not Renew & Vacate Plot</button>
        <div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirmRenewalBtn">Confirm & Renew</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const allBurials = <?= json_encode($data['all_burials'] ?? []) ?>;

  const renewalModal  = new bootstrap.Modal(document.getElementById('renewalModal'));
  const modalEl       = document.getElementById('renewalModal');
  const form          = document.getElementById('renewalForm');
  const paymentDateEl = document.getElementById('payment_date');
  const tableBody     = document.getElementById('for-renewal-table');

  // SweetAlert2 maroon-themed instance
  const themedSwal = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-primary mx-2',
      denyButton:     'btn btn-info mx-2',
      cancelButton:   'btn btn-secondary mx-2'
    },
    buttonsStyling: false
  });

  // helpers
  const esc = (s) => {
    if (s === null || s === undefined) return '';
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  };
  const formatDate = (d) => {
    if (!d) return '';
    const x = new Date(d);
    return isNaN(x) ? '' : x.toLocaleString('en-US',{month:'short',day:'2-digit',year:'numeric'});
  };

  const rowHtml = (item, status) => {
    const deceased = esc(((item.deceased_first_name||'')+' '+(item.deceased_last_name||'')).trim());
    const plotLbl  = esc(((item.block_title||'N/A')+' - '+(item.plot_number||'N/A')).trim());
    return `
      <tr data-burial-id="${esc(item.burial_id)}"
          data-plot-id="${esc(item.plot_id)}"
          data-deceased-name="${deceased}"
          data-expiry-date="${esc(item.expiry_date)}">
        <td>${plotLbl}</td>
        <td>${deceased}</td>
        <td>${esc(item.interment_full_name)}</td>
        <td>${formatDate(item.expiry_date)}</td>
        <td><span class="badge ${status.badge}">${status.text}</span></td>
        <td class="text-center">
          <button class="btn btn-primary btn-sm action-btn process-renewal-btn">Process Renewal</button>
        </td>
      </tr>`;
  };

  // compute statuses + render list
  const render = () => {
    if (!Array.isArray(allBurials)) {
      tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Invalid data from server.</td></tr>';
      return;
    }
    const now = new Date(); now.setHours(0,0,0,0);
    const rows = [];

    allBurials.forEach(item => {
      if (!item.expiry_date) return;
      const exp = new Date(item.expiry_date); exp.setHours(0,0,0,0);
      const diff = Math.round((exp - now) / 86400000);
      let status;
      if (diff < 0)      status = { text:`Expired ${Math.abs(diff)} days ago`, badge:'bg-danger' };
      else if (diff == 0)status = { text:'Expires Today',                 badge:'bg-danger' };
      else if (diff <=30)status = { text:`Expires in ${diff} days`,       badge:'bg-warning text-dark' };
      else               status = { text:`Expires in ${diff} days`,       badge:'bg-secondary' };
      rows.push(rowHtml(item, status));
    });

    if (rows.length) {
      // soonest expiry first
      allBurials.sort((a,b)=> new Date(a.expiry_date) - new Date(b.expiry_date));
      tableBody.innerHTML = rows.join('');
    } else {
      tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No active burial records found.</td></tr>';
    }
  };

  // open modal + prefill
  tableBody.addEventListener('click', (ev) => {
    if (!ev.target.classList.contains('process-renewal-btn')) return;
    const row = ev.target.closest('tr');
    const id  = row.dataset.burialId;
    const rec = Array.isArray(allBurials) ? allBurials.find(x=> String(x.burial_id)===String(id)) : null;

    form.reset();

    if (rec) {
      document.getElementById('modal_burial_id').value = rec.burial_id;
      document.getElementById('modal_plot_id').value   = rec.plot_id;
      document.getElementById('modal_deceased_name').textContent = row.dataset.deceasedName;
      document.getElementById('modal_expiry_date').textContent   = formatDate(rec.expiry_date);
      document.getElementById('modal_irh_name').value  = rec.interment_full_name || '';
      document.getElementById('modal_irh_email').value = rec.interment_email || '';
      // default payer = IRH (editable)
      document.getElementById('payer_name').value  = rec.interment_full_name || '';
      document.getElementById('payer_email').value = rec.interment_email || '';
    }
    // default payment date = today
    paymentDateEl.valueAsDate = new Date();

    renewalModal.show();
  });

  // copy buttons
  modalEl.addEventListener('click', (e) => {
    if (!e.target.classList.contains('copy-btn')) return;
    const src = document.getElementById(e.target.dataset.copySource);
    const dst = document.getElementById(e.target.dataset.copyTarget);
    if (src && dst) dst.value = src.value;
  });

  // network helper (uses staff endpoints)
  const submitTo = async (url, formData) => {
    themedSwal.fire({ title:'Processing…', html:'Saving transaction…', allowOutsideClick:false, didOpen:()=>themedSwal.showLoading() });
    try{
      const res = await fetch(url, { method:'POST', body:new URLSearchParams(formData) });
      const out = await res.json();

      if (out.ok) {
        if (url.includes('/processRenewal')) {
          themedSwal.fire({
            icon:'success',
            title:'Renewal Successful!',
            html:`Transaction saved.<br>Email Status: <strong>${out.email_status || 'N/A'}</strong>`,
            showDenyButton:true,
            confirmButtonText:'Done',
            denyButtonText:'<i class="fas fa-print"></i> Print New Contract'
          }).then((act)=>{
            if (act.isDenied) {
              window.open(`<?= URLROOT ?>/staff/printContract/${out.burial_id}?autoprint=1`, '_blank');
              setTimeout(()=>location.reload(), 500);
            } else {
              location.reload();
            }
          });
        } else {
          themedSwal.fire({ icon:'success', title:'Action Successful!', html:`${out.message}<br>Email Status: <strong>${out.email_status || 'N/A'}</strong>` })
            .then(()=>location.reload());
        }
      } else {
        themedSwal.fire('Error', out.message || 'An error occurred.', 'error');
      }
    }catch(err){
      themedSwal.fire('Network Error','Could not connect to the server. Please try again.','error');
    }
  };

  // confirm renewal (STAFF endpoint)
  document.getElementById('confirmRenewalBtn').addEventListener('click', ()=>{
    if (!form.checkValidity()) { form.reportValidity(); return; }
    submitTo('<?= URLROOT ?>/staff/processRenewal', new FormData(form));
  });

  // vacate (STAFF endpoint)
  document.getElementById('vacateBtn').addEventListener('click', async ()=>{
    const burialId = document.getElementById('modal_burial_id').value;
    const deceased = document.getElementById('modal_deceased_name').textContent;

    const result = await themedSwal.fire({
      title:'Are you sure?',
      text:`This will archive ${deceased} and mark the plot as vacant. This cannot be undone.`,
      icon:'warning',
      showCancelButton:true,
      customClass:{ confirmButton:'btn btn-danger mx-2', cancelButton:'btn btn-secondary mx-2' },
      confirmButtonText:'Yes, vacate the plot!'
    });
    if (!result.isConfirmed) return;

    const fd = new FormData(); fd.append('burial_id', burialId);
    submitTo('<?= URLROOT ?>/staff/processVacate', fd);
  });

  render();
});
</script>

<?php require APPROOT . '/views/includes/staff_footer.php'; ?>
