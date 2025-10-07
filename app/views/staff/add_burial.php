<?php require APPROOT . '/views/includes/staff_header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
  .modal-wizard .modal-dialog{max-width:860px}
  @media(max-width:991.98px){.modal-wizard .modal-dialog{max-width:96vw}}
  .modal-wizard .modal-body{max-height:66vh;overflow:auto}
  .form-control.is-invalid,.form-select.is-invalid{border-color:#dc3545}
  .invalid-feedback{display:block}
  .wizard-actions .btn{min-width:110px}
  .qr-box{width:180px}
</style>

<div class="container-xxl px-2 px-lg-4">
  <h2 class="mb-3">Add Burial</h2>
  <p class="text-muted mb-4">Fill out the forms step-by-step. Use <em>Previous</em> to review before saving.</p>
</div>

<!-- STEP 1 -->
<div class="modal fade modal-wizard" id="step1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Burial Information</h5></div>
      <div class="modal-body">
        <form id="formStep1" novalidate>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Plot (vacant only) <span class="text-danger">*</span></label>
              <select class="form-select" id="plot_id" name="plot_id" required>
                <option value="">Select vacant plot</option>
                <?php foreach($data['plots'] as $p): ?>
                  <option value="<?= htmlspecialchars($p->id) ?>">
                    <?= htmlspecialchars($p->plot_number) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Required.</div>
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-3">
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" id="deceased_first_name" name="deceased_first_name" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-3">
              <label class="form-label">Middle Name</label>
              <input type="text" id="deceased_middle_name" name="deceased_middle_name" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" id="deceased_last_name" name="deceased_last_name" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-3">
              <label class="form-label">Suffix</label>
              <div class="d-flex gap-2">
                <select id="deceased_suffix" name="deceased_suffix" class="form-select">
                  <option value="">(None)</option>
                  <option>Jr.</option><option>Sr.</option>
                  <option>I</option><option>II</option><option>III</option><option>IV</option><option>V</option><option>VI</option>
                  <option value="OTHER">Other (specify)</option>
                </select>
                <input type="text" id="suffix_other" class="form-control d-none" placeholder="Specify…">
              </div>
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-4">
              <label class="form-label">Date Born</label>
              <input type="date" id="date_born" name="date_born" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Date Died <span class="text-danger">*</span></label>
              <input type="date" id="date_died" name="date_died" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-2">
              <label class="form-label">Age</label>
              <input type="number" min="0" id="age" name="age" class="form-control" inputmode="numeric" placeholder="(auto)" readonly>
            </div>
            <div class="col-md-2">
              <label class="form-label">Sex</label>
              <select id="sex" name="sex" class="form-select">
                <option value="">Select</option><option>male</option><option>female</option><option>other</option>
              </select>
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-6">
              <label class="form-label">Grave Level</label>
              <select id="grave_level" name="grave_level" class="form-select">
                <option value="">Select</option>
                <option>A</option><option>B</option><option>C</option><option>D</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Grave Type</label>
              <select id="grave_type" name="grave_type" class="form-select">
                <option value="">Select</option>
                <option>Apartment</option><option>Crypt</option><option>Columbarium</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Cause of Death</label>
              <input type="text" id="cause_of_death" name="cause_of_death" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer wizard-actions">
        <a href="<?= URLROOT ?>/staff/burialRecords" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary" id="toStep2">Next</button>
      </div>
    </div>
  </div>
</div>

<!-- STEP 2 -->
<div class="modal fade modal-wizard" id="step2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Interment Right Holder &amp; Contact</h5></div>
      <div class="modal-body">
        <form id="formStep2" novalidate>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" id="interment_full_name" name="interment_full_name" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Relationship <span class="text-danger">*</span></label>
              <select id="interment_relationship" name="interment_relationship" class="form-select" required>
                <option value="">Select</option>
                <option>Spouse</option><option>Parent</option><option>Child</option>
                <option>Sibling</option><option>Relative</option><option>Other</option>
              </select>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Number <small class="text-muted">(0912 345 6789)</small></label>
              <input type="text" id="interment_contact_number" class="form-control" maxlength="13" inputmode="numeric" placeholder="0912 345 6789">
              <div class="invalid-feedback">Enter a valid PH mobile number.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email <small class="text-muted">(optional)</small></label>
              <input type="email" id="interment_email" name="interment_email" class="form-control" maxlength="150" placeholder="name@example.com">
              <div class="invalid-feedback">Please enter a valid email address (max 150 chars).</div>
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-3">
              <label class="form-label">Province</label>
              <select id="addr_province" class="form-select"><option value="">Select</option></select>
            </div>
            <div class="col-md-3">
              <label class="form-label">City/Municipality</label>
              <select id="addr_city" class="form-select" disabled><option value="">Select</option></select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Barangay</label>
              <select id="addr_brgy" class="form-select" disabled><option value="">Select</option></select>
            </div>
            <div class="col-md-3">
              <label class="form-label">ZIP</label>
              <input type="text" id="addr_zip" class="form-control" maxlength="4" inputmode="numeric" placeholder="e.g. 3004">
            </div>
            <div class="col-12">
              <label class="form-label">House/Lot &amp; Street / Subdivision</label>
              <input type="text" id="addr_line" class="form-control" placeholder="House no., Street, Subdivision">
            </div>
            <input type="hidden" id="interment_address" name="interment_address">

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-4">
              <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
              <input type="number" min="0" step="0.01" id="payment_amount" name="payment_amount" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Rental Date &amp; Time</label>
              <input type="datetime-local" id="rental_date" name="rental_date" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Expiry (auto +5 yrs)</label>
              <input type="text" id="expiry_date_display" class="form-control" disabled>
              <input type="hidden" id="expiry_date" name="expiry_date">
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-12">
              <label class="form-label">Requirements</label>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-check"><input class="form-check-input req" type="checkbox" value="Death Certificate with registry number" id="req1"><label class="form-check-label" for="req1">Death Certificate with registry number</label></div>
                  <div class="form-check"><input class="form-check-input req" type="checkbox" value="Barangay Indigency for Burial Assistance" id="req2"><label class="form-check-label" for="req2">Barangay Indigency for Burial Assistance</label></div>
                  <div class="form-check"><input class="form-check-input req" type="checkbox" value="Voter's ID" id="req3"><label class="form-check-label" for="req3">Voter's ID</label></div>
                </div>
                <div class="col-md-6">
                  <div class="form-check"><input class="form-check-input req" type="checkbox" value="Cedula" id="req4"><label class="form-check-label" for="req4">Cedula</label></div>
                  <div class="form-check"><input class="form-check-input req" type="checkbox" value="Sulat Kahilingan" id="req5"><label class="form-check-label" for="req5">Sulat Kahilingan</label></div>
                </div>
              </div>
              <input type="hidden" id="requirements" name="requirements">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer wizard-actions">
        <button class="btn btn-outline-secondary" id="backTo1">Previous</button>
        <button class="btn btn-primary" id="submitBurial">Next</button>
      </div>
    </div>
  </div>
</div>

<!-- STEP 3 -->
<div class="modal fade modal-wizard" id="step3" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Burial Form</h5></div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-8">
            <div id="summaryFields" class="border rounded p-3 bg-light"></div>
          </div>
          <div class="col-md-4 text-center">
            <img id="qr1" class="qr-box mb-2" alt="QR">
            <div class="small text-muted">Scan for Burial ID</div>
          </div>
        </div>
      </div>
      <div class="modal-footer wizard-actions">
        <button class="btn btn-outline-secondary" id="s3Prev">Previous</button>
        <button class="btn btn-secondary" id="printForm">Print</button>
        <button class="btn btn-primary" id="toStep4">Next</button>
      </div>
    </div>
  </div>
</div>

<!-- STEP 4 -->
<div class="modal fade modal-wizard" id="step4" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Official Contract</h5></div>
      <div class="modal-body d-flex align-items-start gap-3">
        <img src="<?= URLROOT ?>/public/img/seal.png" style="width:160px;height:160px" alt="Seal">
        <div class="lh-lg">
          <p>This is an official contract issued to <strong id="contractName"></strong> by <em>Plaridel Public Cemetery</em>. You may print or download this receipt as proof of payment and record ownership. Please retain a copy for your records.</p>
        </div>
      </div>
      <div class="modal-footer wizard-actions">
        <button class="btn btn-outline-secondary" id="s4Prev">Previous</button>
        <button class="btn btn-secondary" id="printContract">Print</button>
        <button class="btn btn-primary" id="toStep5">Next</button>
      </div>
    </div>
  </div>
</div>

<!-- STEP 5 -->
<div class="modal fade modal-wizard" id="step5" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">QR Ticket</h5></div>
      <div class="modal-body d-flex align-items-start gap-3">
        <img src="<?= URLROOT ?>/public/img/bwlogo.png" style="width:120px;height:120px" alt="BW">
        <div class="flex-grow-1">
          <p>This is the QR code issued by <em>Plaridel Public Cemetery</em>. Please ensure the IRH understands the importance of keeping a printed copy of the QR code.</p>
          <img id="qr2" class="qr-box mb-2" alt="QR">
          <div><strong>Burial ID:</strong> <span id="qrBurial"></span></div>
          <div><strong>Transaction ID:</strong> <span id="qrTxn"></span></div>
        </div>
      </div>
      <div class="modal-footer wizard-actions">
        <button class="btn btn-outline-secondary" id="s5Prev">Previous</button>
        <button class="btn btn-secondary" id="printQr">Print</button>
        <button class="btn btn-success" id="finishBtn">Finish</button>
      </div>
    </div>
  </div>
</div>

<?php require APPROOT . '/views/includes/staff_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const getModal = id => { const el=document.getElementById(id); if(!el) return null; try{ return bootstrap.Modal.getOrCreateInstance(el);}catch(_){ return null; } };

  const m1=getModal('step1'), m2=getModal('step2'), m3=getModal('step3'), m4=getModal('step4'), m5=getModal('step5');
  if(m1) m1.show();

  const byId = id => document.getElementById(id);

  // Auto-age
  byId('date_born').addEventListener('change', function() {
    const ageInput = byId('age');
    if (!this.value) { ageInput.value = ''; return; }
    const b = new Date(this.value), t = new Date();
    let a = t.getFullYear()-b.getFullYear();
    const m = t.getMonth()-b.getMonth();
    if (m<0 || (m===0 && t.getDate()<b.getDate())) a--;
    ageInput.value = a>=0? a:0;
  });

  // Expiry +5yrs
  byId('rental_date').addEventListener('change', function() {
    const disp = byId('expiry_date_display'), hid = byId('expiry_date');
    if (!this.value) { disp.value=''; hid.value=''; return; }
    const start = new Date(this.value), exp = new Date(start); exp.setFullYear(exp.getFullYear()+5);
    const pad = n=> String(n).padStart(2,'0');
    hid.value = `${exp.getFullYear()}-${pad(exp.getMonth()+1)}-${pad(exp.getDate())} ${pad(exp.getHours())}:${pad(exp.getMinutes())}:${pad(exp.getSeconds())}`;
    disp.value = exp.toLocaleString('en-US', { weekday:'short', year:'numeric', month:'long', day:'numeric', hour:'numeric', minute:'numeric', hour12:true });
  });

  // Suffix Other
  const suf = byId('deceased_suffix'), sufOther = byId('suffix_other');
  suf.addEventListener('change',()=>{ const on = suf.value==='OTHER'; sufOther.classList.toggle('d-none',!on); if(!on) sufOther.value=''; });

  // Phone mask
  const phone = byId('interment_contact_number');
  phone.addEventListener('input', e=>{
    let d = e.target.value.replace(/\D/g,'').slice(0,11);
    e.target.value = d.length<=4?d : d.length<=7? d.slice(0,4)+' '+d.slice(4) : d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7);
  });
  const isPH = v => /^09\d{2}\s\d{3}\s\d{4}$/.test((v||'').trim());
  const isEmail = v => !v ? true : (v.length<=150 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v));

  // Address builder
  function bakeAddress(){
    const parts = [
      byId('addr_line').value.trim(),
      byId('addr_brgy').value ? 'Brgy. '+byId('addr_brgy').selectedOptions[0].text : '',
      byId('addr_city').value ? byId('addr_city').selectedOptions[0].text : '',
      byId('addr_province').value ? byId('addr_province').selectedOptions[0].text : '',
      byId('addr_zip').value.trim()
    ].filter(Boolean);
    byId('interment_address').value = parts.join(', ');
  }

  // Requirements collect
  function collectReqs(){
    const vals = Array.from(document.querySelectorAll('.req:checked')).map(c=>c.value);
    byId('requirements').value = vals.join(', ');
  }

  // Validation
  function v1(){
    let ok=true; ['plot_id','deceased_first_name','deceased_last_name','date_died'].forEach(id=>{
      const el=byId(id); if(!el.value){ el.classList.add('is-invalid'); ok=false; } else el.classList.remove('is-invalid');
    });
    if(suf.value==='OTHER' && !sufOther.value.trim()){ sufOther.classList.add('is-invalid'); ok=false; } else sufOther.classList.remove('is-invalid');
    return ok;
  }
  function v2(){
    let ok=true; ['interment_full_name','interment_relationship','payment_amount'].forEach(id=>{
      const el=byId(id); if(!el.value){ el.classList.add('is-invalid'); ok=false; } else el.classList.remove('is-invalid');
    });
    if(phone.value && !isPH(phone.value)){ phone.classList.add('is-invalid'); ok=false; } else phone.classList.remove('is-invalid');
    const emailEl = byId('interment_email'); const emailVal = (emailEl.value||'').trim();
    if(emailVal && !isEmail(emailVal)){ emailEl.classList.add('is-invalid'); ok=false; } else emailEl.classList.remove('is-invalid');
    collectReqs(); bakeAddress();
    return ok;
  }

  byId('toStep2').onclick = ()=>{ if(!v1())return; m1.hide(); m2.show(); };
  byId('backTo1').onclick = ()=>{ m2.hide(); m1.show(); };

  let savedBurialId = '', savedTxn = '';

  // Submit -> save -> step3
  byId('submitBurial').onclick = async ()=>{
    if(!v2()) return;

    const payload = {
      burial_id: savedBurialId,
      plot_id: byId('plot_id').value,
      deceased_first_name: byId('deceased_first_name').value.trim(),
      deceased_middle_name: byId('deceased_middle_name').value.trim(),
      deceased_last_name: byId('deceased_last_name').value.trim(),
      deceased_suffix: (suf.value==='OTHER'?sufOther.value.trim():suf.value),
      age: byId('age').value.trim(),
      sex: byId('sex').value,
      date_born: byId('date_born').value,
      date_died: byId('date_died').value,
      cause_of_death: byId('cause_of_death').value.trim(),
      grave_level: byId('grave_level').value,
      grave_type: byId('grave_type').value,
      interment_full_name: byId('interment_full_name').value.trim(),
      interment_relationship: byId('interment_relationship').value,
      interment_contact_number: phone.value.trim(),
      interment_address: byId('interment_address').value.trim(),
      interment_email: byId('interment_email').value.trim(),
      payment_amount: byId('payment_amount').value,
      rental_date: byId('rental_date').value,
      expiry_date: byId('expiry_date').value,
      requirements: byId('requirements').value
    };

    const resp = await fetch('<?= URLROOT ?>/staff/addBurial', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      credentials:'same-origin',
      body: new URLSearchParams(payload).toString()
    }).then(r=>r.json()).catch(()=>({ok:false}));

    if(!resp.ok){
      Swal.fire({icon:'error',title:'Save failed',text: resp.message || 'Please try again.'});
      return;
    }

    savedBurialId = resp.burial_id || savedBurialId;
    savedTxn      = resp.transaction_id || savedTxn;

    const fmt = (s)=> s ? new Date(s).toLocaleString('en-US',{weekday:'short',year:'numeric',month:'long',day:'numeric',hour:'numeric',minute:'numeric',hour12:true}) : '';

    const sum = byId('summaryFields');
    sum.innerHTML =
      `<div class="row g-2">
        <div class="col-6"><strong>Plot:</strong> ${byId('plot_id').selectedOptions[0].text}</div>
        <div class="col-6"><strong>Burial ID:</strong> ${savedBurialId}</div>
        <div class="col-6"><strong>First Name:</strong> ${byId('deceased_first_name').value}</div>
        <div class="col-6"><strong>Relationship:</strong> ${byId('interment_relationship').value}</div>
        <div class="col-6"><strong>Middle Name:</strong> ${byId('deceased_middle_name').value || '-'}</div>
        <div class="col-6"><strong>Contact:</strong> ${byId('interment_contact_number').value || '-'}</div>
        <div class="col-12"><strong>Email:</strong> ${byId('interment_email').value || '-'}</div>
        <div class="col-12"><strong>Address:</strong> ${byId('interment_address').value || '-'}</div>
        <div class="col-6"><strong>Payment Amount:</strong> ${Number(byId('payment_amount').value||0).toLocaleString()}</div>
        <div class="col-6"><strong>Rental Date:</strong> ${fmt(byId('rental_date').value)}</div>
        <div class="col-6"><strong>Expiry Date:</strong> ${fmt(byId('expiry_date').value)}</div>
        <div class="col-12"><strong>Grave Level & Type:</strong> ${(byId('grave_level').value||'-')} / ${(byId('grave_type').value||'-')}</div>
        <div class="col-12"><strong>Requirements:</strong> ${byId('requirements').value || '-'}</div>
      </div>`;

    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='+encodeURIComponent(savedBurialId);
    byId('qr1').src = qrUrl; byId('qr2').src = qrUrl;
    byId('qrBurial').innerText = savedBurialId; byId('qrTxn').innerText = savedTxn;
    byId('contractName').innerText = byId('interment_full_name').value;

    m2.hide(); m3.show();
  };

  // Nav + prints
  byId('s3Prev').onclick = ()=>{ m3.hide(); m2.show(); };
  byId('toStep4').onclick = ()=>{ m3.hide(); m4.show(); };
  byId('s4Prev').onclick = ()=>{ m4.hide(); m3.show(); };
  byId('toStep5').onclick = ()=>{ m4.hide(); m5.show(); };
  byId('s5Prev').onclick = ()=>{ m5.hide(); m4.show(); };

  byId('printForm').onclick     = ()=> window.open('<?= URLROOT ?>/staff/printBurialForm/'+encodeURIComponent(savedBurialId)+'?autoprint=1','_blank');
  byId('printContract').onclick = ()=> window.open('<?= URLROOT ?>/staff/printContract/'+encodeURIComponent(savedBurialId)+'?autoprint=1','_blank');
  byId('printQr').onclick       = ()=> window.open('<?= URLROOT ?>/staff/printQrTicket/'+encodeURIComponent(savedBurialId)+'?autoprint=1','_blank');

  byId('finishBtn').onclick = ()=>{
    Swal.fire({ icon: 'success', title: 'Burial added successfully', timer: 1400, showConfirmButton: false })
      .then(()=>{ window.location = '<?= URLROOT ?>/staff/burialRecords'; });
  };

  // PSGC cascading
  const api='https://psgc.gitlab.io/api';
  const selProv=byId('addr_province'), selCity=byId('addr_city'), selBrgy=byId('addr_brgy');
  const fill = (sel,list,ph='Select')=>{
    sel.innerHTML=`<option value="">${ph}</option>`;
    list.forEach(x=>{
      const o=document.createElement('option'); o.value=x.code||x.id||x.name; o.textContent=x.name; sel.appendChild(o);
    });
  };
  fetch(`${api}/provinces/`).then(r=>r.json()).then(list=>{ list.sort((a,b)=>a.name.localeCompare(b.name)); fill(selProv,list); });
  selProv.addEventListener('change',()=>{
    selCity.disabled=true; selBrgy.disabled=true; fill(selCity,[]); fill(selBrgy,[]);
    const c=selProv.value; if(!c)return;
    fetch(`${api}/provinces/${c}/cities-municipalities/`).then(r=>r.json()).then(list=>{
      list.sort((a,b)=>a.name.localeCompare(b.name)); fill(selCity,list); selCity.disabled=false;
    });
  });
  selCity.addEventListener('change',()=>{
    selBrgy.disabled=true; fill(selBrgy,[]);
    const c=selCity.value; if(!c)return;
    fetch(`${api}/cities-municipalities/${c}/barangays/`).then(r=>r.json()).then(list=>{
      list.sort((a,b)=>a.name.localeCompare(b.name)); fill(selBrgy,list); selBrgy.disabled=false;
    });
  });
})();
</script>
