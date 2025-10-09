<?php require APPROOT . '/views/includes/admin_header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
  :root {
    --swal2-confirm-button-background-color: #800000 !important;
    --swal2-cancel-button-background-color: #6c757d !important;
    --swal2-deny-button-background-color: #dc3545 !important;
  }
  .modal-wizard .modal-dialog { max-width: 860px; }
  .modal-wizard .modal-body { max-height: 66vh; overflow-y: auto; }
  .form-control.is-invalid, .form-select.is-invalid { border-color: #dc3545; }
  /* REMOVED: .invalid-feedback { display: block; } This was causing the issue. */
  .wizard-actions .btn { min-width: 110px; }
  .qr-box { width: 180px; }

  /* FIX: huwag i-truncate ang mahahabang label/text */
  .form-check-label, .form-label { white-space: normal; }
  #summaryFields { font-size: 1rem; line-height: 1.35; word-break: break-word; }
  #summaryFields ul { padding-left: 1.1rem; margin-bottom: 0; }
  #summaryFields li { margin-bottom: 2px; }
</style>

<div class="container-xxl px-2 px-lg-4">
  <h2 class="mb-3">Add Burial Record</h2>
  <p class="text-muted mb-4">Fill out the forms step-by-step. Use <em>Previous</em> to review before saving.</p>
</div>

<div class="modal fade modal-wizard" id="step1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Step 1: Burial Information</h5></div>
      <div class="modal-body">
        <form id="formStep1" novalidate>
          <div class="row g-3">
            <div class="col-12">
              <label for="plot_id" class="form-label">Plot <span class="text-danger">*</span></label>
              <select class="form-select" id="plot_id" name="plot_id" required>
                <option value="" selected disabled>Select a plot...</option>
                <?php if (!empty($data['plots_grouped']['vacant'])): ?>
                <optgroup label="Vacant Plots">
                  <?php foreach($data['plots_grouped']['vacant'] as $p): ?>
                    <option value="<?= htmlspecialchars($p->id) ?>" data-status="vacant"><?= htmlspecialchars($p->plot_number) ?></option>
                  <?php endforeach; ?>
                </optgroup>
                <?php endif; ?>
                <?php if (!empty($data['plots_grouped']['occupied'])): ?>
                <optgroup label="Occupied Plots (for adding occupant)">
                  <?php foreach($data['plots_grouped']['occupied'] as $p): ?>
                    <option value="<?= htmlspecialchars($p->id) ?>" data-status="occupied"><?= htmlspecialchars($p->plot_number) ?></option>
                  <?php endforeach; ?>
                </optgroup>
                <?php endif; ?>
              </select>
              <div class="invalid-feedback">Required.</div>
              <input type="hidden" name="parent_burial_id" id="parent_burial_id" value="">
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-3">
              <label for="deceased_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" id="deceased_first_name" name="deceased_first_name" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-3">
              <label for="deceased_middle_name" class="form-label">Middle Name</label>
              <input type="text" id="deceased_middle_name" name="deceased_middle_name" class="form-control">
            </div>
            <div class="col-md-3">
              <label for="deceased_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" id="deceased_last_name" name="deceased_last_name" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-3">
              <label for="deceased_suffix" class="form-label">Suffix</label>
              <div class="d-flex gap-2">
                <select id="deceased_suffix" name="deceased_suffix" class="form-select">
                  <option value="">(None)</option>
                  <option>Jr.</option><option>Sr.</option>
                  <option>I</option><option>II</option><option>III</option><option>IV</option><option>V</option><option>VI</option>
                  <option value="OTHER">Other</option>
                </select>
                <input type="text" id="suffix_other" class="form-control d-none" placeholder="Specify…">
              </div>
              <div class="invalid-feedback">Specify suffix.</div>
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-4">
              <label for="date_born" class="form-label">Date Born <span class="text-danger">*</span></label>
              <input type="date" id="date_born" name="date_born" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-4">
              <label for="date_died" class="form-label">Date Died <span class="text-danger">*</span></label>
              <input type="date" id="date_died" name="date_died" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-2">
              <label for="age" class="form-label">Age</label>
              <input type="number" id="age" name="age" class="form-control" placeholder="(auto)" readonly>
            </div>
            <div class="col-md-2">
              <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
              <select id="sex" name="sex" class="form-select" required>
                <option value="">Select</option><option>Male</option><option>Female</option><option>Other</option>
              </select>
              <div class="invalid-feedback">Required.</div>
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-6">
              <label for="grave_level" class="form-label">Grave Level <span class="text-danger">*</span></label>
              <select id="grave_level" name="grave_level" class="form-select" required>
                <option value="" selected disabled>Select</option><option>A</option><option>B</option><option>C</option><option>D</option>
              </select>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-6">
              <label for="grave_type" class="form-label">Grave Type <span class="text-danger">*</span></label>
              <select id="grave_type" name="grave_type" class="form-select" required>
                <option value="" selected disabled>Select</option><option>Apartment</option><option>Crypt</option><option>Columbarium</option>
              </select>
              <div class="invalid-feedback">Required.</div>
            </div>

            <div class="col-12">
              <label for="cause_of_death" class="form-label">Cause of Death</label>
              <input type="text" id="cause_of_death" name="cause_of_death" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer wizard-actions">
        <a href="<?= URLROOT ?>/admin/burialRecords" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary" id="toStep2">Next</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-wizard" id="step2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Step 2: Interment Right Holder & Payment</h5></div>
      <div class="modal-body">
        <form id="formStep2" novalidate>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="interment_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" id="interment_full_name" name="interment_full_name" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-6">
              <label for="interment_relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
              <select id="interment_relationship" name="interment_relationship" class="form-select" required>
                <option value="" selected disabled>Select</option><option>Spouse</option><option>Parent</option><option>Child</option><option>Sibling</option><option>Relative</option><option>Other</option>
              </select>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-6">
              <label for="interment_contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
              <input type="text" id="interment_contact_number" name="interment_contact_number" class="form-control" maxlength="13" inputmode="tel" placeholder="0912 345 6789" required>
              <div class="invalid-feedback">Valid PH mobile number is required.</div>
            </div>
            <div class="col-md-6">
              <label for="interment_email" class="form-label">Email <small class="text-muted">(optional)</small></label>
              <input type="email" id="interment_email" name="interment_email" class="form-control" maxlength="150" placeholder="name@example.com">
              <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-md-3"><label for="addr_province" class="form-label">Province</label><select id="addr_province" class="form-select"><option value="">Select</option></select></div>
            <div class="col-md-3"><label for="addr_city" class="form-label">City/Municipality</label><select id="addr_city" class="form-select" disabled><option value="">Select</option></select></div>
            <div class="col-md-3"><label for="addr_brgy" class="form-label">Barangay</label><select id="addr_brgy" class="form-select" disabled><option value="">Select</option></select></div>
            <div class="col-md-3"><label for="addr_zip" class="form-label">ZIP</label><input type="text" id="addr_zip" name="addr_zip" class="form-control" maxlength="4" inputmode="numeric" placeholder="e.g. 3004"></div>
            <div class="col-12">
              <label for="addr_line" class="form-label">House/Lot & Street <span class="text-danger">*</span></label>
              <input type="text" id="addr_line" name="addr_line" class="form-control" placeholder="House no., Street, Subdivision" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <input type="hidden" id="interment_address" name="interment_address">
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-md-4">
              <label for="payment_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
              <input type="number" min="0" step="0.01" id="payment_amount" name="payment_amount" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-4">
              <label for="rental_date" class="form-label">Rental Date & Time <span class="text-danger">*</span></label>
              <input type="datetime-local" id="rental_date" name="rental_date" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-4">
              <label for="expiry_date_display" class="form-label">Expiry (auto +5 yrs)</label>
              <input type="text" id="expiry_date_display" class="form-control" readonly>
              <input type="hidden" id="expiry_date" name="expiry_date">
            </div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-12">
              <label class="form-label">Requirements (Check all that apply)</label>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-check">
                    <input id="req_dc" class="form-check-input req" type="checkbox" value="Death Certificate with registry number">
                    <label for="req_dc" class="form-check-label">Death Certificate with registry number</label>
                  </div>
                  <div class="form-check">
                    <input id="req_indigency" class="form-check-input req" type="checkbox" value="Barangay Indigency for Burial Assistance">
                    <label for="req_indigency" class="form-check-label">Barangay Indigency for Burial Assistance</label>
                  </div>
                  <div class="form-check">
                    <input id="req_voter" class="form-check-input req" type="checkbox" value="Voter's ID">
                    <label for="req_voter" class="form-check-label">Voter's ID</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check">
                    <input id="req_cedula" class="form-check-input req" type="checkbox" value="Cedula">
                    <label for="req_cedula" class="form-check-label">Cedula</label>
                  </div>
                  <div class="form-check">
                    <input id="req_kahilingan" class="form-check-input req" type="checkbox" value="Sulat Kahilingan">
                    <label for="req_kahilingan" class="form-check-label">Sulat Kahilingan</label>
                  </div>
                </div>
              </div>
              <input type="hidden" id="requirements" name="requirements">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer wizard-actions">
        <button class="btn btn-outline-secondary" id="backTo1">Previous</button>
        <button class="btn btn-primary" id="submitBurial">Save & Proceed</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-wizard" id="step3" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Step 3: Summary & Burial Form</h5></div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-8"><div id="summaryFields" class="border rounded p-3 bg-light"></div></div>
          <div class="col-md-4 text-center">
            <img id="qr1" class="qr-box mb-2" alt="QR">
            <div class="small text-muted">Scan for Burial ID</div>
          </div>
        </div>
      </div>
      <div class="modal-footer wizard-actions">
        <button class="btn btn-outline-secondary" id="s3Prev">Previous</button>
        <button class="btn btn-secondary" id="printForm">Print Form</button>
        <button class="btn btn-primary" id="toStep4">Next</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-wizard" id="step4" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Step 4: Contract</h5></div>
      <div class="modal-body d-flex align-items-start gap-3">
        <img src="<?= URLROOT ?>/public/img/seal.png" style="width:160px;height:160px" alt="Seal">
        <div class="lh-lg"><p>This is an official contract issued to <strong id="contractName"></strong>.</p></div>
      </div>
      <div class="modal-footer wizard-actions">
        <button class="btn btn-outline-secondary" id="s4Prev">Previous</button>
        <button class="btn btn-secondary" id="printContract">Print Contract</button>
        <button class="btn btn-primary" id="toStep5">Next</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-wizard" id="step5" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Step 5: QR Ticket</h5></div>
      <div class="modal-body d-flex align-items-start gap-3">
        <img src="<?= URLROOT ?>/public/img/bwlogo.png" style="width:120px;height:120px" alt="BW">
        <div class="flex-grow-1">
          <p>This is the QR code. Please ensure the IRH keeps a printed copy.</p>
          <img id="qr2" class="qr-box mb-2" alt="QR">
          <div><strong>Burial ID:</strong> <span id="qrBurial"></span></div>
          <div><strong>Transaction ID:</strong> <span id="qrTxn"></span></div>
        </div>
      </div>
      <div class="modal-footer wizard-actions">
        <button class="btn btn-outline-secondary" id="s5Prev">Previous</button>
        <button class="btn btn-secondary" id="printQr">Print Ticket</button>
        <button class="btn btn-success" id="finishBtn">Finish</button>
      </div>
    </div>
  </div>
</div>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const m1=new bootstrap.Modal(byId('step1')),m2=new bootstrap.Modal(byId('step2')),m3=new bootstrap.Modal(byId('step3')),m4=new bootstrap.Modal(byId('step4')),m5=new bootstrap.Modal(byId('step5'));
  m1.show();
  function byId(id){return document.getElementById(id)}
  const e=s=>{const p=document.createElement('p');p.textContent=s;return p.innerHTML};

  // Suffix toggle
  const suf = byId('deceased_suffix');
  const sufOther = byId('suffix_other');
  if (suf) {
    suf.addEventListener('change', () => {
      const on = suf.value === 'OTHER';
      sufOther.classList.toggle('d-none', !on);
      sufOther.classList.remove('is-invalid');
      if (!on) sufOther.value = '';
    });
  }

  // ===================================================================
  // [START] REVISED LOGIC FOR PLOT SELECTION
  // ===================================================================
  const plotSelect = byId('plot_id'),
        parentIdInput = byId('parent_burial_id'),
        graveLevelSelect = byId('grave_level'),
        graveTypeSelect = byId('grave_type');

  if (plotSelect && parentIdInput && graveLevelSelect && graveTypeSelect) {
      plotSelect.addEventListener('change', async function() {
          const selectedOption = this.options[this.selectedIndex];
          const status = selectedOption.dataset.status;
          const plotId = this.value;

          // Reset fields first
          parentIdInput.value = '';
          graveLevelSelect.value = '';
          graveTypeSelect.value = '';
          graveLevelSelect.disabled = false;
          graveTypeSelect.disabled = false;
          
          if (!plotId) return;

          Swal.fire({
              title: 'Fetching Plot Info...',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
          });

          try {
              // 1. Get parent_burial_id if plot is already occupied
              if (status === 'occupied') {
                  const parentRes = await fetch(`<?= URLROOT ?>/admin/getPrimaryBurialForPlot/${plotId}`).then(r => r.json());
                  if (parentRes.ok) {
                      parentIdInput.value = parentRes.parent_burial_id;
                  } else {
                      throw new Error(parentRes.message || 'No active primary record found for this plot.');
                  }
              }

              // 2. Get grave level and type details
              const detailsRes = await fetch(`<?= URLROOT ?>/admin/getPlotDetailsForForm/${plotId}`).then(r => r.json());
              if (!detailsRes.ok) {
                  throw new Error(detailsRes.message || 'Could not fetch plot layout details.');
              }

              const details = detailsRes.details;
              if (details.status === 'occupied') {
                  // For occupied plots, auto-fill and disable BOTH fields
                  graveLevelSelect.value = details.grave_level || '';
                  graveTypeSelect.value = details.grave_type || '';
                  graveLevelSelect.disabled = true;
                  graveTypeSelect.disabled = true;
              } else { // For vacant plots
                  // Auto-fill and disable ONLY grave_level. Grave_type is user-selectable.
                  graveLevelSelect.value = details.grave_level || '';
                  graveLevelSelect.disabled = true;
                  graveTypeSelect.disabled = false;
                  graveTypeSelect.value = ''; // Ensure grave type is reset for user selection
              }
              
              Swal.close();

          } catch (err) {
              Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: err.message
              });
              this.value = ''; // Reset plot selection on error
              // Re-enable fields on error
              graveLevelSelect.disabled = false;
              graveTypeSelect.disabled = false;
          }
      });
  }
  // ===================================================================
  // [END] REVISED LOGIC FOR PLOT SELECTION
  // ===================================================================


  byId('date_born').addEventListener('change',function(){
    if(!this.value){byId('age').value='';return}
    const b=new Date(this.value),t=new Date();
    let a=t.getFullYear()-b.getFullYear();const m=t.getMonth()-b.getMonth();
    if(m<0||(m===0&&t.getDate()<b.getDate()))a--;byId('age').value=a>=0?a:0
  });
  byId('date_died').addEventListener('change',function(){
    const b=byId('date_born').value;
    if(b&&new Date(this.value)<new Date(b)){this.value='';this.classList.add('is-invalid')}else{this.classList.remove('is-invalid')}
  });
  byId('rental_date').addEventListener('change',function(){
    const d=byId('expiry_date_display'),h=byId('expiry_date');
    if(!this.value){d.value='';h.value='';return}
    const s=new Date(this.value),x=new Date(s);x.setFullYear(x.getFullYear()+5);
    const p=n=>String(n).padStart(2,'0');
    h.value=`${x.getFullYear()}-${p(x.getMonth()+1)}-${p(x.getDate())} ${p(x.getHours())}:${p(x.getMinutes())}:${p(x.getSeconds())}`;
    d.value=x.toLocaleString('en-US',{month:'long',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit',hour12:true})
  });
  byId('interment_contact_number').addEventListener('input',e=>{let d=e.target.value.replace(/\D/g,'').slice(0,11);e.target.value=d.length<=4?d:d.length<=7?d.slice(0,4)+' '+d.slice(4):d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7)});
  const isPH=v=>/^09\d{2}\s\d{3}\s\d{4}$/.test(v.trim());
  const isEmail=v=>{if(!v)return true;if(v.length>150)return false;return/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)};
  function bakeAddress(){const p=[byId('addr_line').value.trim(),byId('addr_brgy').value?'Brgy. '+byId('addr_brgy').selectedOptions[0].text:'',byId('addr_city').value?byId('addr_city').selectedOptions[0].text:'',byId('addr_province').value?byId('addr_province').selectedOptions[0].text:'',byId('addr_zip').value.trim()].filter(Boolean);byId('interment_address').value=p.join(', ')}
  function collectReqs(){byId('requirements').value=Array.from(document.querySelectorAll('.req:checked')).map(c=>c.value).join(', ')}
  function validate(fs){let ok=true;fs.forEach(id=>{const el=byId(id);if(el&&!el.disabled&&!String(el.value||'').trim()){el.classList.add('is-invalid');ok=false}else if(el)el.classList.remove('is-invalid')});return ok}

  function v1(){
    const fs=['plot_id','deceased_first_name','deceased_last_name','date_born','date_died','sex','grave_level','grave_type'];
    let ok = validate(fs);
    if (suf && suf.value === 'OTHER' && !String(sufOther.value||'').trim()){
      sufOther.classList.add('is-invalid'); ok=false;
    } else if (sufOther) {
      sufOther.classList.remove('is-invalid');
    }
    return ok;
  }
  function v2(){
    const fs=['interment_full_name','interment_relationship','payment_amount','rental_date','interment_contact_number','addr_line'];
    let ok=validate(fs);
    const pE=byId('interment_contact_number'); if(pE.value && !isPH(pE.value)){pE.classList.add('is-invalid');ok=false}else{pE.classList.remove('is-invalid')}
    const mE=byId('interment_email'); if(mE.value && !isEmail(mE.value)){mE.classList.add('is-invalid');ok=false}else{mE.classList.remove('is-invalid')}
    collectReqs(); bakeAddress();
    return ok;
  }

  byId('toStep2').onclick=()=>{if(!v1())return;m1.hide();m2.show()};
  byId('backTo1').onclick=()=>{m2.hide();m1.show()};

  let savedBurialId='',savedTxn='';

  byId('submitBurial').onclick=async()=>{
    if(!v2())return;
    const r=await Swal.fire({title:'Confirm & Save',html:"Are you sure?",icon:'question',showCancelButton:true,confirmButtonText:'Yes, Save!',cancelButtonText:'Cancel'});
    if(r.isConfirmed){
      Swal.fire({title:'Saving...',html:'Please wait...',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
      const f1=new FormData(byId('formStep1')),f2=new FormData(byId('formStep2')),p=new URLSearchParams();
      for(const[k,v]of f1.entries())p.append(k,v);for(const[k,v]of f2.entries())p.append(k,v);

      // IMPORTANT: Get values from disabled fields manually since FormData ignores them
      p.set('grave_level', byId('grave_level').value);
      p.set('grave_type', byId('grave_type').value);

      if (suf && suf.value === 'OTHER') p.set('deceased_suffix', (sufOther.value || '').trim());

      try{
        const rp=await fetch('<?= URLROOT ?>/admin/addBurial',{method:'POST',body:p}).then(r=>r.json());
        if(!rp.ok)throw new Error(rp.message||'Unknown error');
        savedBurialId=rp.burial_id;savedTxn=rp.transaction_id;

        const fd=d=>d?new Date(d).toLocaleString('en-US',{month:'long',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit',hour12:true}):'-';
        const fdate=d=>d?new Date(d).toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'}):'-';

        const reqStr = p.get('requirements') || '';
        const reqArr = reqStr ? reqStr.split(/\s*,\s*/).filter(Boolean) : [];
        const reqHTML = reqArr.length ? ('<ul>' + reqArr.map(x=>`<li>${e(x)}</li>`).join('') + '</ul>') : '-';

        const suffixFinal = p.get('deceased_suffix') || '';

        byId('summaryFields').innerHTML = `
          <div class="row g-2">
            <div class="col-6"><strong>Plot:</strong> ${e(byId('plot_id').selectedOptions[0].text)}</div>
            <div class="col-6"><strong>Burial ID:</strong> ${e(savedBurialId)}</div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-12"><strong>Deceased:</strong>
              ${e(p.get('deceased_first_name'))}
              ${e(p.get('deceased_middle_name'))}
              ${e(p.get('deceased_last_name'))}
              ${suffixFinal ? e(' '+suffixFinal) : ''}
            </div>
            <div class="col-md-3"><strong>Date Born:</strong> ${fdate(p.get('date_born'))}</div>
            <div class="col-md-3"><strong>Date Died:</strong> ${fdate(p.get('date_died'))}</div>
            <div class="col-md-3"><strong>Age:</strong> ${e(byId('age').value || '-')}</div>
            <div class="col-md-3"><strong>Sex:</strong> ${e(p.get('sex') || '-')}</div>
            <div class="col-12"><strong>Cause of Death:</strong> ${e(p.get('cause_of_death') || '-')}</div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-md-6"><strong>Grave Level:</strong> ${e(p.get('grave_level'))}</div>
            <div class="col-md-6"><strong>Grave Type:</strong> ${e(p.get('grave_type'))}</div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-12"><strong>IRH:</strong> ${e(p.get('interment_full_name'))} (${e(p.get('interment_relationship'))})</div>
            <div class="col-md-6"><strong>Contact:</strong> ${e(p.get('interment_contact_number'))||'-'}</div>
            <div class="col-md-6"><strong>Email:</strong> ${e(p.get('interment_email'))||'-'}</div>
            <div class="col-12"><strong>Address:</strong> ${e(p.get('interment_address'))||'-'}</div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-md-4"><strong>Payment:</strong> ₱${Number(p.get('payment_amount')).toLocaleString('en-US',{minimumFractionDigits:2})}</div>
            <div class="col-md-4"><strong>Rental Date:</strong> ${fd(p.get('rental_date'))}</div>
            <div class="col-md-4"><strong>Expiry Date:</strong> ${fd(byId('expiry_date').value)}</div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-12"><strong>Requirements:</strong><br>${reqHTML}</div>
          </div>
        `;

        const qr='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='+encodeURIComponent(savedBurialId);
        byId('qr1').src=qr;byId('qr2').src=qr;
        byId('qrBurial').innerText=savedBurialId;
        byId('qrTxn').innerText=savedTxn;
        byId('contractName').innerText=p.get('interment_full_name');

        Swal.close();m2.hide();m3.show()
      }catch(err){Swal.fire('Save Failed',err.message,'error')}
    }
  };

  byId('s3Prev').onclick=()=>{m3.hide();m2.show()};
  byId('toStep4').onclick=()=>{m3.hide();m4.show()};
  byId('s4Prev').onclick=()=>{m4.hide();m3.show()};
  byId('toStep5').onclick=()=>{m4.hide();m5.show()};
  byId('s5Prev').onclick=()=>{m5.hide();m4.show()};

  const pu=(t,id)=>`<?= URLROOT ?>/admin/${t}/${encodeURIComponent(id)}?autoprint=1`;
  byId('printForm').onclick=()=>window.open(pu('printBurialForm',savedBurialId),'_blank');
  byId('printContract').onclick=()=>window.open(pu('printContract',savedBurialId),'_blank');
  byId('printQr').onclick=()=>window.open(pu('printQrTicket',savedBurialId),'_blank');

  byId('finishBtn').onclick=()=>Swal.fire({icon:'success',title:'Saved!',timer:1500,showConfirmButton:false}).then(()=>window.location='<?= URLROOT ?>/admin/burialRecords');

  // PSGC loaders
  const api='https://psgc.gitlab.io/api';const p=byId('addr_province'),c=byId('addr_city'),b=byId('addr_brgy');
  function f(s,l,ph='Select'){s.innerHTML=`<option value="">${ph}</option>`;l.sort((a,b)=>a.name.localeCompare(b.name));l.forEach(x=>{const o=document.createElement('option');o.value=x.code; o.textContent=x.name; s.appendChild(o)})}
  fetch(`${api}/provinces/`).then(r=>r.json()).then(l=>{f(p,l)});
  p.addEventListener('change',()=>{c.disabled=true;b.disabled=true;f(c,[]);f(b,[]);const v=p.value;if(!v)return;fetch(`${api}/provinces/${v}/cities-municipalities/`).then(r=>r.json()).then(l=>{f(c,l);c.disabled=false})});
  c.addEventListener('change',()=>{b.disabled=true;f(b,[]);const v=c.value;if(!v)return;fetch(`${api}/cities-municipalities/${v}/barangays/`).then(r=>r.json()).then(l=>{f(b,l);b.disabled=false})});
})();
</script>