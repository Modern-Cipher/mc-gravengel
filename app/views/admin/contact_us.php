<?php
// header include
if (file_exists(APPROOT . '/views/includes/admin_header.php')) {
    require APPROOT . '/views/includes/admin_header.php';
}
$data['title'] = 'Contact Us';

// Developer emails
$developer_email_orion  = 'orionsealrobieloscano@gmail.com';
$developer_email_juana  = 'vinta.juanamarie.bsit@gmail.com';
$developer_email_althea = 'marcelino.althea.bsit@gmail.com';

// PHP color (also echoed in JS)
$maroon = '#800000';
?>

<style>
:root{ --maroon:#800000; --dark-maroon:#5a0000; }

/* PAGE */
.contact-hero{ max-width:1200px;margin:24px auto 0;padding:28px 24px;background:#fff;border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,.06); }
.brand{display:flex;justify-content:center;align-items:center;margin-top:6px;}
.brand img.logo{width:420px;height:auto;display:block;}
.hr{height:2px;background:#dedede;margin:26px auto 0;max-width:95%;border-radius:2px;}
.section-title{text-align:center;font-size:30px;margin:28px 0 10px;color:#a92a2a;font-weight:800;letter-spacing:.3px;}

/* CARDS */
.team-wrap{max-width:1200px;margin:26px auto;display:grid;gap:28px;grid-template-columns:repeat(3,1fr);}
.card{background:#fafafa;border-radius:16px;padding:26px 20px;text-align:center;box-shadow:0 8px 22px rgba(0,0,0,.05);border:1px solid #eee;display:flex;flex-direction:column;min-height:420px;}
.avatar{width:160px;height:160px;border-radius:50%;object-fit:cover;background:#fff;display:block;margin:4px auto 14px;border:8px solid #fff;box-shadow:0 2px 10px rgba(0,0,0,.06);}
.name{font-weight:800;color:#a92a2a;margin:8px 0 6px;font-size:20px;}
.role{color:#6f6f6f;font-size:13.5px;line-height:1.4;margin-bottom:6px;}

/* CONTACT */
.contact-link-group{display:flex;flex-direction:column;gap:6px;margin-top:12px;font-size:.96rem;}
.contact-link-group a{color:#333;text-decoration:none;transition:color .2s;display:inline-flex;align-items:center;gap:8px;}
.contact-link-group a:hover{color:var(--maroon);}
.contact-link-group .phone-detail,.contact-link-group .email-detail{display:flex;justify-content:center;align-items:center;gap:10px;}
.contact-icon{opacity:.75}

/* BUTTONS */
.btn-maroon{background-color:var(--maroon);border-color:var(--maroon);color:#fff;transition:background-color .2s,border-color .2s;}
.btn-maroon:hover{background-color:var(--dark-maroon);border-color:var(--dark-maroon);color:#fff;}
.btn-maroon:focus{box-shadow:0 0 0 0.2rem rgba(128,0,0,.30);}
.btn-dev-report{width:100%;margin-top:auto;}

/* MODALS */
.modal-header.report-style{background-color:var(--maroon);color:#fff;border-top-left-radius:8px;border-top-right-radius:8px;border-bottom:2px solid var(--dark-maroon);display:flex;align-items:center;}
.modal-header.report-style h5{font-weight:700;margin:0;}
.modal-title .fas{margin-right:10px;}
.form-group label{font-weight:600;}

/* Custom white X (BS4/BS5) */
.modal-close-x{margin-left:auto;background:transparent;border:0;color:#fff;opacity:1;padding:.25rem .5rem;line-height:1;cursor:pointer;}
.modal-close-x i{font-size:20px;}
.modal-close-x:focus{outline:none;box-shadow:none;}

/* QR icon */
.qr-popover{display:inline-block;cursor:pointer;line-height:1;}
.qr-popover i{color:<?php echo $maroon; ?>;}

.scroll-spacer-dummy{height:1000px;opacity:0;visibility:hidden;pointer-events:none;}

@media (max-width: 992px){ .team-wrap{grid-template-columns:1fr 1fr;} }
@media (max-width: 640px){ .team-wrap{grid-template-columns:1fr;} }
</style>

<section class="contact-hero">
  <div class="brand">
    <img class="logo" src="<?php echo URLROOT; ?>/public/img/tri.png" alt="Logo">
  </div>
  <div class="hr"></div>

  <h2 class="section-title">Meet Our Team</h2>

  <div class="team-wrap">
    <!-- ORION -->
    <div class="card">
      <img class="avatar" src="<?php echo URLROOT; ?>/public/img/Orion.png" alt="Orion Seal R.">
      <div class="name">Cano, Orion Seal R.</div>
      <div class="role">Full-Stack Developer &amp; Project Manager</div>

      <div class="contact-link-group">
        <div class="phone-detail">
          <span class="contact-icon"><i class="fas fa-phone"></i></span>
          <a href="tel:09161269394">0916 126 9394</a>
          <a href="#" class="qr-popover" data-type="phone" data-value="09161269394" data-title="Scan to Call Orion"><i class="fas fa-qrcode"></i></a>
        </div>
        <div class="email-detail">
          <span class="contact-icon"><i class="fas fa-envelope"></i></span>
          <a href="mailto:<?php echo $developer_email_orion; ?>"><?php echo $developer_email_orion; ?></a>
          <a href="#" class="qr-popover" data-type="email" data-value="<?php echo $developer_email_orion; ?>" data-title="Scan to Email Orion"><i class="fas fa-qrcode"></i></a>
        </div>
      </div>

      <button type="button" class="btn btn-sm btn-maroon btn-dev-report"
              data-toggle="modal" data-target="#reportModal"
              data-bs-toggle="modal" data-bs-target="#reportModal"
              data-developer-email="<?php echo $developer_email_orion; ?>"
              data-developer-name="Cano, Orion Seal R.">
        <i class="fas fa-bug"></i> Report Issue
      </button>
    </div>

    <!-- JUANA -->
    <div class="card">
      <img class="avatar" src="<?php echo URLROOT; ?>/public/img/Juana.png" alt="Juana Marie E.">
      <div class="name">Vinta, Juana Marie E.</div>
      <div class="role">System Analyst &amp; UI/UX</div>

      <div class="contact-link-group">
        <div class="phone-detail">
          <span class="contact-icon"><i class="fas fa-phone"></i></span>
          <a href="tel:09231334987">0923 133 4987</a>
          <a href="#" class="qr-popover" data-type="phone" data-value="09231334987" data-title="Scan to Call Juana"><i class="fas fa-qrcode"></i></a>
        </div>
        <div class="email-detail">
          <span class="contact-icon"><i class="fas fa-envelope"></i></span>
          <a href="mailto:<?php echo $developer_email_juana; ?>"><?php echo $developer_email_juana; ?></a>
          <a href="#" class="qr-popover" data-type="email" data-value="<?php echo $developer_email_juana; ?>" data-title="Scan to Email Juana"><i class="fas fa-qrcode"></i></a>
        </div>
      </div>

      <button type="button" class="btn btn-sm btn-maroon btn-dev-report"
              data-toggle="modal" data-target="#reportModal"
              data-bs-toggle="modal" data-bs-target="#reportModal"
              data-developer-email="<?php echo $developer_email_juana; ?>"
              data-developer-name="Vinta, Juana Marie E.">
        <i class="fas fa-bug"></i> Report Issue
      </button>
    </div>

    <!-- ALTHEA -->
    <div class="card">
      <img class="avatar" src="<?php echo URLROOT; ?>/public/img/Althea.png" alt="Althea DC.">
      <div class="name">Marcelino, Althea DC.</div>
      <div class="role">Document Specialist</div>

      <div class="contact-link-group">
        <div class="phone-detail">
          <span class="contact-icon"><i class="fas fa-phone"></i></span>
          <a href="tel:09298537819">0929 853 7819</a>
          <a href="#" class="qr-popover" data-type="phone" data-value="09298537819" data-title="Scan to Call Althea"><i class="fas fa-qrcode"></i></a>
        </div>
        <div class="email-detail">
          <span class="contact-icon"><i class="fas fa-envelope"></i></span>
          <a href="mailto:<?php echo $developer_email_althea; ?>"><?php echo $developer_email_althea; ?></a>
          <a href="#" class="qr-popover" data-type="email" data-value="<?php echo $developer_email_althea; ?>" data-title="Scan to Email Althea"><i class="fas fa-qrcode"></i></a>
        </div>
      </div>

      <button type="button" class="btn btn-sm btn-maroon btn-dev-report"
              data-toggle="modal" data-target="#reportModal"
              data-bs-toggle="modal" data-bs-target="#reportModal"
              data-developer-email="<?php echo $developer_email_althea; ?>"
              data-developer-name="Marcelino, Althea DC.">
        <i class="fas fa-bug"></i> Report Issue
      </button>
    </div>
  </div>

  <div class="contact-box" style="margin-top:6px">
    For general inquiries, you can use the contact details above. For system reports, click the 'Report Issue' button on the developer's card.
  </div>
</section>

<div class="row"><div class="col-12"><div class="scroll-spacer-dummy"></div></div></div>

<!-- REPORT MODAL -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header report-style">
        <h5 class="modal-title" id="reportModalLabel"><i class="fas fa-clipboard-list"></i> Critical System Report</h5>
        <button type="button" class="modal-close-x" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form id="developerReportForm" novalidate>
        <input type="hidden" name="developer_email" id="developer_email_input" value="">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 form-group">
              <label for="reporter_name"><i class="fas fa-user"></i> Your Name (Required)</label>
              <input type="text" class="form-control" id="reporter_name" name="reporter_name" required value="<?php echo htmlspecialchars($_SESSION['user']['name'] ?? ''); ?>">
            </div>
            <div class="col-md-6 form-group">
              <label for="reporter_email"><i class="fas fa-at"></i> Your Email (Required)</label>
              <input type="email" class="form-control" id="reporter_email" name="reporter_email" required value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 form-group">
              <label for="contact_number"><i class="fas fa-phone"></i> Contact Number (Optional)</label>
              <!-- mobile-friendly keypad; pattern allows digits & +; JS does strict regex -->
              <input type="text" class="form-control" id="contact_number" name="contact_number"
                     inputmode="numeric" autocomplete="tel" pattern="[0-9+\s-]*" placeholder="e.g., 09123456789 or +639123456789">
            </div>
            <div class="col-md-6 form-group">
              <label for="subject"><i class="fas fa-tag"></i> Report Subject (Required)</label>
              <select class="form-control" id="subject" name="subject" required>
                <option value="">Select Issue Type</option>
                <option value="Critical Bug/Error">Critical Bug/Error</option>
                <option value="Data Corruption Issue">Data Corruption Issue</option>
                <option value="Security Concern">Security Concern</option>
                <option value="Feature Request">Feature Request</option>
                <option value="Other Inquiry">Other Inquiry</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="message"><i class="fas fa-pencil-alt"></i> Detailed Report/Message (Required)</label>
            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
          </div>

          <p class="text-danger small mt-2">⚠️ This form is for system reports only. For general inquiries, please email the developers directly.</p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-maroon" id="sendReportBtn"><i class="fas fa-paper-plane"></i> Send Report</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- QR MODAL -->
<div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header report-style">
        <h5 class="modal-title" id="qrModalLabel"><i class="fas fa-qrcode"></i> Scan Code</h5>
        <button type="button" class="modal-close-x" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body text-center">
        <p id="qr_code_title" class="mb-2 font-weight-bold"></p>
        <div id="qrcode" class="d-inline-block"></div>
      </div>
    </div>
  </div>
</div>

<?php
// footer include (loads jQuery/Bootstrap)
if (file_exists(APPROOT . '/views/includes/admin_footer.php')) {
    require APPROOT . '/views/includes/admin_footer.php';
}
?>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function () {
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }

  function init() {
    var hasJQ = typeof window.$ === 'function';

    function openModalById(id){
      var el=document.getElementById(id); if(!el) return;
      if (window.bootstrap && typeof window.bootstrap.Modal==='function'){ bootstrap.Modal.getOrCreateInstance(el).show(); }
      else if (hasJQ && typeof $('#'+id).modal==='function'){ $('#'+id).modal('show'); }
    }
    function closeModalById(id){
      var el=document.getElementById(id); if(!el) return;
      if (window.bootstrap && typeof window.bootstrap.Modal==='function'){ (bootstrap.Modal.getInstance(el)||bootstrap.Modal.getOrCreateInstance(el)).hide(); }
      else if (hasJQ){ $('#'+id).modal('hide'); }
    }
    function cleanupBackdrops(){
      // Remove any leftover backdrops & body lock (BS4/BS5)
      document.querySelectorAll('.modal-backdrop').forEach(el=>el.parentNode.removeChild(el));
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
    }
    // also cleanup when modals hide
    ['reportModal','qrModal'].forEach(id=>{
      var el=document.getElementById(id);
      el.addEventListener('hidden.bs.modal', cleanupBackdrops);
      if (hasJQ && typeof $('#'+id).on==='function'){ $('#'+id).on('hidden.bs.modal', cleanupBackdrops); }
    });

    const QR_COLOR_DARK = '#000000'; const QR_COLOR_LIGHT = '#ffffff';

    // Fill report modal
    var reportModalEl=document.getElementById('reportModal');
    reportModalEl.addEventListener('show.bs.modal', function (event) {
      const btn=event.relatedTarget; if(!btn) return;
      const devEmail=btn.getAttribute('data-developer-email')||''; const devName=btn.getAttribute('data-developer-name')||'Developer';
      reportModalEl.querySelector('#developer_email_input').value=devEmail;
      reportModalEl.querySelector('#reportModalLabel').innerHTML =
        '<i class="fas fa-clipboard-list"></i> Critical System Report' +
        '<small class="d-block" style="font-size:0.85rem;opacity:.9">Sending to: <strong>'+devName+'</strong> &lt;'+devEmail+'&gt;</small>';
    });
    if (hasJQ && typeof $('#reportModal').on==='function'){
      $('#reportModal').on('show.bs.modal', function (e) {
        const b=$(e.relatedTarget); const devEmail=b.data('developer-email')||''; const devName=b.data('developer-name')||'Developer';
        $(this).find('#developer_email_input').val(devEmail);
        $(this).find('#reportModalLabel').html('<i class="fas fa-clipboard-list"></i> Critical System Report<small class="d-block" style="font-size:0.85rem;opacity:.9">Sending to: <strong>'+devName+'</strong> &lt;'+devEmail+'&gt;</small>');
      });
    }

    // STRICT Regex helpers
    function isValidEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
    // Optional field: allow empty OR PH formats 09XXXXXXXXX or +639XXXXXXXXX
    function isValidPHMobileOptional(v){
      if (!v) return true;
      const cleaned = v.replace(/\s|-/g,'');
      return /^(09\d{9}|\+?639\d{9})$/.test(cleaned);
    }

    // Submit
    document.getElementById('developerReportForm').addEventListener('submit', function(e){
      e.preventDefault();
      const btn=document.getElementById('sendReportBtn');

      const devEmail=document.getElementById('developer_email_input').value.trim();
      const name=document.getElementById('reporter_name').value.trim();
      const email=document.getElementById('reporter_email').value.trim();
      const phone=document.getElementById('contact_number').value.trim();
      const subject=document.getElementById('subject').value.trim();
      const message=document.getElementById('message').value.trim();

      const errs=[];
      if(!devEmail) errs.push('Missing developer email (open the modal via a developer card).');
      if(!name) errs.push('Please provide your name.');
      if(!isValidEmail(email)) errs.push('Please provide a valid email.');
      if(!isValidPHMobileOptional(phone)) errs.push('Contact number must be 11-digit 09XXXXXXXXX or +639XXXXXXXXX.');
      if(!subject) errs.push('Please select a subject.');
      if(!message) errs.push('Please enter a detailed message.');
      if(errs.length){
        Swal.fire({icon:'error',title:'Incomplete Form',html:'<div style="text-align:left"><ul style="margin:0;padding-left:18px">'+errs.map(e=>'<li>'+e+'</li>').join('')+'</ul></div>',confirmButtonColor:'<?php echo $maroon; ?>'});
        return;
      }

      const formData=new FormData(this);
      btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

      fetch('<?php echo URLROOT; ?>/admin/sendDeveloperReport',{method:'POST',body:formData,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json())
      .then(data=>{
        if(data && data.ok){
          // Close first, then alert, then cleanup to ensure no dark overlay remains
          closeModalById('reportModal');
          setTimeout(()=> {
            Swal.fire({icon:'success', title:'Report Sent', text:data.message || 'Your report has been sent.', confirmButtonColor:'<?php echo $maroon; ?>'})
            .then(()=> cleanupBackdrops());
          }, 200);
          e.target.reset();
        } else {
          Swal.fire({icon:'error', title:'Send Failed', text:(data && data.message) || 'Submission failed.', confirmButtonColor:'<?php echo $maroon; ?>'})
          .then(()=> cleanupBackdrops());
        }
      })
      .catch(err=>{
        console.error('Fetch Error:', err);
        Swal.fire({icon:'error', title:'Network Error', text:'Please check your connection and try again.', confirmButtonColor:'<?php echo $maroon; ?>'})
        .then(()=> cleanupBackdrops());
      })
      .finally(()=>{ btn.disabled=false; btn.innerHTML='<i class="fas fa-paper-plane"></i> Send Report'; });
    });

    // QR (phone/email)
    document.querySelectorAll('.qr-popover').forEach(function(item){
      item.addEventListener('click', function(e){
        e.preventDefault();
        const type=this.getAttribute('data-type');
        const raw=(this.getAttribute('data-value')||'');
        const title=this.getAttribute('data-title')||'Scan Code';

        let qr_content=raw;
        if(type==='phone'){
          const digits=raw.replace(/[^\d]/g,''); // keep numbers only
          qr_content='tel:'+digits;             // exactly 09...
        }else if(type==='email'){
          qr_content='MATMSG:TO:'+raw+';SUB:System Report;BODY:;;';
        }

        document.getElementById('qr_code_title').textContent=title;
        const qrBox=document.getElementById('qrcode'); qrBox.innerHTML='';
        new QRCode(qrBox,{text:qr_content,width:220,height:220,colorDark:'#000000',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.H});
        openModalById('qrModal');
      });
    });
  }
})();
</script>
