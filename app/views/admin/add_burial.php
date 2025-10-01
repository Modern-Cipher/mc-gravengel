<?php require APPROOT . '/views/includes/admin_header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

<!-- Step 1 -->
<div class="modal fade modal-wizard" id="step1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Deceased Information</h5></div>
      <div class="modal-body">
        <form id="formStep1" novalidate>
          <div class="row g-3">
            <div class="col-md-4">
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

            <div class="col-md-6">
              <label class="form-label">Suffix</label>
              <div class="d-flex gap-2">
                <select id="deceased_suffix" name="deceased_suffix" class="form-select">
                  <option value="">(None)</option>
                  <option>Jr.</option><option>Sr.</option>
                  <option>I</option><option>II</option><option>III</option><option>IV</option><option>V</option><option>VI</option>
                  <option>CPA</option><option>PhD</option>
                  <option value="OTHER">Other (specify)</option>
                </select>
                <input type="text" id="suffix_other" class="form-control d-none" placeholder="Specify suffix…">
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" id="deceased_first_name" name="deceased_first_name" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Middle Name</label>
              <input type="text" id="deceased_middle_name" name="deceased_middle_name" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" id="deceased_last_name" name="deceased_last_name" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>

            <div class="col-md-2">
              <label class="form-label">Age</label>
              <input type="number" min="0" id="age" name="age" class="form-control" inputmode="numeric" placeholder="e.g. 60">
            </div>
            <div class="col-md-2">
              <label class="form-label">Sex</label>
              <select id="sex" name="sex" class="form-select">
                <option value="">Select</option><option>male</option><option>female</option><option>other</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Date Born</label>
              <input type="text" id="date_born" name="date_born" class="form-control" placeholder="Tue-Sep 30, 2025">
            </div>
            <div class="col-md-4">
              <label class="form-label">Date Died <span class="text-danger">*</span></label>
              <input type="text" id="date_died" name="date_died" class="form-control" required placeholder="Tue-Sep 30, 2025">
              <div class="invalid-feedback">Required.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Grave Level</label>
              <select id="grave_level" name="grave_level" class="form-select">
                <option value="">Select</option>
                <option>Ground</option><option>Level 1</option><option>Level 2</option><option>Level 3</option><option>Columbarium</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Grave Type</label>
              <select id="grave_type" name="grave_type" class="form-select">
                <option value="">Select</option>
                <option>Apartment</option><option>Family Lot</option><option>Garden Lot</option><option>Urn (Columbarium)</option>
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
        <a href="<?= URLROOT ?>/admin/burialRecords" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary" id="toStep2">Next</button>
      </div>
    </div>
  </div>
</div>

<!-- Step 2 -->
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

            <div class="col-12"><hr class="my-2"></div>

            <!-- Address -->
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

            <!-- Payment / Rental -->
            <div class="col-md-4">
              <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
              <input type="number" min="0" step="0.01" id="payment_amount" name="payment_amount" class="form-control" required>
              <div class="invalid-feedback">Required.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Rental Date &amp; Time</label>
              <input type="text" id="rental_date" name="rental_date" class="form-control" placeholder="Wed-October 01, 2025 at 7:44 AM">
            </div>
            <div class="col-md-4">
              <label class="form-label">Expiry (auto +5 yrs)</label>
              <input type="text" id="expiry_date_display" class="form-control" disabled>
              <input type="hidden" id="expiry_date" name="expiry_date">
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <!-- Requirements -->
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

<!-- Step 3: Summary (print button) -->
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

<!-- Step 4: Contract (print button) -->
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

<!-- Step 5: QR Ticket (print button) -->
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

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  // Modals
  const m1 = new bootstrap.Modal(document.getElementById('step1'));
  const m2 = new bootstrap.Modal(document.getElementById('step2'));
  const m3 = new bootstrap.Modal(document.getElementById('step3'));
  const m4 = new bootstrap.Modal(document.getElementById('step4'));
  const m5 = new bootstrap.Modal(document.getElementById('step5'));
  m1.show();

  const byId = id => document.getElementById(id);

  // Flatpickr
  const fpBorn = flatpickr("#date_born",{altInput:true,altFormat:"D-M-d, Y",dateFormat:"Y-m-d"});
  const fpDied = flatpickr("#date_died",{altInput:true,altFormat:"D-M-d, Y",dateFormat:"Y-m-d"});
  const fpRent = flatpickr("#rental_date",{
    enableTime:true, enableSeconds:true, time_24hr:false,
    altInput:true, altFormat:"D-F d, Y 'at' h:i K",
    dateFormat:"Y-m-d H:i:S",
    onChange: function(sel){
      const disp = byId('expiry_date_display'), hid=byId('expiry_date');
      if(!sel.length){ disp.value=''; hid.value=''; return; }
      const start = sel[0]; const exp = new Date(start); exp.setFullYear(exp.getFullYear()+5);
      hid.value = flatpickr.formatDate(exp,"Y-m-d H:i:S");
      disp.value = flatpickr.formatDate(exp,"D-F d, Y 'at' h:i K");
    }
  });

  // suffix other
  const suf = byId('deceased_suffix'), sufOther = byId('suffix_other');
  suf.addEventListener('change',()=>{ const on = suf.value==='OTHER'; sufOther.classList.toggle('d-none',!on); if(!on) sufOther.value=''; });

  // phone mask
  const phone = byId('interment_contact_number');
  phone.addEventListener('input', e=>{
    let d = e.target.value.replace(/\D/g,'').slice(0,11);
    let out = d.length<=4?d : d.length<=7? d.slice(0,4)+' '+d.slice(4) : d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7);
    e.target.value = out;
  });
  const isPH = v => /^09\d{2}\s\d{3}\s\d{4}$/.test(v.trim());

  // address compose
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
  function collectReqs(){
    const vals = Array.from(document.querySelectorAll('.req:checked')).map(c=>c.value);
    byId('requirements').value = vals.join(', ');
  }

  // validate
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
    collectReqs(); bakeAddress();
    return ok;
  }

  // Step nav
  byId('toStep2').onclick = ()=>{ if(!v1())return; m1.hide(); m2.show(); };
  byId('backTo1').onclick = ()=>{ m2.hide(); m1.show(); };

  // state for create/update
  let savedBurialId = '', savedTxn = '';

  byId('submitBurial').onclick = async ()=>{
    if(!v2()) return;

    const payload = {
      burial_id: savedBurialId, // empty on first save (CREATE); filled on subsequent saves (UPDATE)
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
      payment_amount: byId('payment_amount').value,
      rental_date: byId('rental_date').value, // Y-m-d H:i:S
      expiry_date: byId('expiry_date').value, // Y-m-d H:i:S
      requirements: byId('requirements').value
    };

    const resp = await fetch('<?= URLROOT ?>/admin/addBurial', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams(payload).toString()
    }).then(r=>r.json()).catch(()=>({ok:false}));

    if(!resp.ok){ alert(resp.message || 'Save failed'); return; }

    savedBurialId = resp.burial_id || savedBurialId;
    savedTxn      = resp.transaction_id || savedTxn;

    // Fill Step3 summary
    const sum = document.getElementById('summaryFields');
    sum.innerHTML = `
      <div class="row g-2">
        <div class="col-6"><strong>Plot ID:</strong> ${byId('plot_id').selectedOptions[0].text}</div>
        <div class="col-6"><strong>Burial ID:</strong> ${savedBurialId}</div>
        <div class="col-6"><strong>First Name:</strong> ${byId('deceased_first_name').value}</div>
        <div class="col-6"><strong>Relationship:</strong> ${byId('interment_relationship').value}</div>
        <div class="col-6"><strong>Middle Name:</strong> ${byId('deceased_middle_name').value}</div>
        <div class="col-6"><strong>Contact:</strong> ${byId('interment_contact_number').value}</div>
        <div class="col-12"><strong>Address:</strong> ${byId('interment_address').value}</div>
        <div class="col-6"><strong>Payment Amount:</strong> ${Number(byId('payment_amount').value).toLocaleString()}</div>
        <div class="col-6"><strong>Rental Date:</strong> ${byId('rental_date').value ? flatpickr.formatDate(new Date(byId('rental_date').value),"D-F d, Y 'at' h:i K"):''}</div>
        <div class="col-6"><strong>Expiry Date:</strong> ${byId('expiry_date').value ? flatpickr.formatDate(new Date(byId('expiry_date').value),"D-F d, Y 'at' h:i K"):''}</div>
        <div class="col-12"><strong>Grave Level & Type:</strong> ${byId('grave_level').value || '-'} / ${byId('grave_type').value || '-'}</div>
        <div class="col-12"><strong>Requirements:</strong> ${byId('requirements').value || '-'}</div>
      </div>
    `;

    // QR images
    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='+encodeURIComponent(savedBurialId);
    document.getElementById('qr1').src = qrUrl;
    document.getElementById('qr2').src = qrUrl;
    document.getElementById('qrBurial').innerText = savedBurialId;
    document.getElementById('qrTxn').innerText = savedTxn;

    // Contract name
    document.getElementById('contractName').innerText = byId('interment_full_name').value;

    m2.hide(); m3.show();
  };

  // Step3 buttons
  byId('s3Prev').onclick = ()=>{ m3.hide(); m2.show(); };
  byId('toStep4').onclick = ()=>{ m3.hide(); m4.show(); };

  // Step4 buttons
  byId('s4Prev').onclick = ()=>{ m4.hide(); m3.show(); };
  byId('toStep5').onclick = ()=>{ m4.hide(); m5.show(); };

  // Step5 back
  byId('s5Prev').onclick = ()=>{ m5.hide(); m4.show(); };

  // PRINT buttons (open dedicated print routes so you can style per file)
// PRINT buttons (auto-print & close via ?autoprint=1)
byId('printForm').onclick     = ()=> window.open('<?= URLROOT ?>/admin/printBurialForm/'+encodeURIComponent(savedBurialId)+'?autoprint=1','_blank');
byId('printContract').onclick = ()=> window.open('<?= URLROOT ?>/admin/printContract/'+encodeURIComponent(savedBurialId)+'?autoprint=1','_blank');
byId('printQr').onclick       = ()=> window.open('<?= URLROOT ?>/admin/printQrTicket/'+encodeURIComponent(savedBurialId)+'?autoprint=1','_blank');

  // Finish with SweetAlert then redirect
  byId('finishBtn').onclick = ()=>{
    Swal.fire({
      icon: 'success',
      title: 'Burial added successfully',
      timer: 1400,
      showConfirmButton: false
    }).then(()=>{ window.location = '<?= URLROOT ?>/admin/burialRecords'; });
  };

  /* PSGC cascading */
  const api='https://psgc.gitlab.io/api';
  const selProv=byId('addr_province'), selCity=byId('addr_city'), selBrgy=byId('addr_brgy');
  function fill(sel,list,ph='Select'){ sel.innerHTML=`<option value="">${ph}</option>`; list.forEach(x=>{const o=document.createElement('option');o.value=x.code||x.id||x.name;o.textContent=x.name;sel.appendChild(o);}); }
  fetch(`${api}/provinces/`).then(r=>r.json()).then(list=>{ list.sort((a,b)=>a.name.localeCompare(b.name)); fill(selProv,list); });
  selProv.addEventListener('change',()=>{ selCity.disabled=true; selBrgy.disabled=true; fill(selCity,[]); fill(selBrgy,[]); const c=selProv.value; if(!c)return; fetch(`${api}/provinces/${c}/cities-municipalities/`).then(r=>r.json()).then(list=>{ list.sort((a,b)=>a.name.localeCompare(b.name)); fill(selCity,list); selCity.disabled=false; }); });
  selCity.addEventListener('change',()=>{ selBrgy.disabled=true; fill(selBrgy,[]); const c=selCity.value; if(!c)return; fetch(`${api}/cities-municipalities/${c}/barangays/`).then(r=>r.json()).then(list=>{ list.sort((a,b)=>a.name.localeCompare(b.name)); fill(selBrgy,list); selBrgy.disabled=false; }); });
})();
</script>
