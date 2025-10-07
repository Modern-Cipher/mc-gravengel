<?php /* app/views/pages/qr_scanner.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>QR Scanner • Gravengel</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>

  <script>window.URLROOT = "<?= URLROOT ?>";</script>

  <style>
    :root{
      --g-maroon:#7b1d1d;
      --g-maroon-dark:#6a1818;
    }

    /* Topbar */
    .qr-topbar{
      background: var(--g-maroon);
      color:#fff;
    }
    .qr-topbar .btn{
      border-color:#fff;
      color:#fff;
    }
    .qr-topbar .btn:hover{
      background:#fff;
      color:var(--g-maroon);
    }

    /* Buttons = maroon */
    .btn, .btn:focus { box-shadow:none !important; }
    .btn-primary, .btn-success, .btn-outline-secondary {
      background: var(--g-maroon) !important;
      border-color: var(--g-maroon) !important;
      color:#fff !important;
    }
    .btn-outline-secondary { /* gawing solid maroon na rin */
      background: var(--g-maroon) !important;
    }
    .btn-primary:hover, .btn-success:hover, .btn-outline-secondary:hover {
      background: var(--g-maroon-dark) !important;
      border-color: var(--g-maroon-dark) !important;
      color:#fff !important;
    }

    /* Tabs */
    .card-header .nav-link { color:#495057; }
    .card-header .nav-link.active{
      background: var(--g-maroon);
      color:#fff;
    }

    /* Result header */
    #resultHeader{ background: var(--g-maroon); color:#fff; }

    /* Misc */
    #cameraBox video{ object-fit:cover; border-radius:.25rem; }
    #resultBody dl dt{ width:140px; }
    #resultBody dl dd{ margin-left:0; }
    @media (min-width:576px){
      #resultBody dl{ display:grid; grid-template-columns:160px 1fr; row-gap:.4rem; }
      #resultBody dl dt{ grid-column:1; }
      #resultBody dl dd{ grid-column:2; }
    }

    /* SweetAlert maroon toast */
    .swal2-toast { background: var(--g-maroon) !important; color:#fff; }
    .swal2-toast .swal2-title{ color:#fff; }

    body{ background:#f7f7f7; }
  </style>
</head>
<body>

  <!-- TOPBAR -->
  <div class="qr-topbar py-2">
    <div class="container d-flex align-items-center justify-content-between">
      <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>QR Scanner</h5>
      <a href="<?= URLROOT ?>/" class="btn btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Home
      </a>
    </div>
  </div>

  <div class="container py-4" id="qr-page">
    <div class="alert alert-info mb-3">
      <i class="fas fa-info-circle me-1"></i>
      Point your camera at the QR, or upload a screenshot/photo of the QR code. The QR must contain a
      <strong>burial_id</strong> (e.g., <code>B-589902</code>).
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="qrTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="live-tab" data-bs-toggle="tab" data-bs-target="#live" type="button" role="tab">
              <i class="fas fa-camera me-1"></i> Live Scan
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
              <i class="fas fa-upload me-1"></i> Upload Image
            </button>
          </li>
        </ul>
      </div>

      <div class="card-body tab-content">
        <!-- Live -->
        <div class="tab-pane fade show active" id="live" role="tabpanel">
          <div class="row g-3 align-items-center">
            <div class="col-md-7">
              <div class="ratio ratio-4x3 border rounded position-relative bg-light" id="cameraBox">
                <video id="qrVideo" playsinline class="w-100 h-100"></video>
                <canvas id="qrCanvas" class="d-none"></canvas>
                <div id="liveStatus" class="position-absolute bottom-0 start-0 m-2 small text-white px-2 py-1"
                     style="background:rgba(0,0,0,.45); border-radius:.25rem;">Idle</div>
              </div>
              <div class="mt-2 d-flex gap-2 flex-wrap">
                <button id="btnStart" class="btn btn-primary btn-sm"><i class="fas fa-play me-1"></i> Start</button>
                <button id="btnStop"  class="btn btn-primary btn-sm" disabled><i class="fas fa-stop me-1"></i> Stop</button>
                <select id="cameraSelect" class="form-select form-select-sm" style="max-width:280px"></select>
              </div>
            </div>
            <div class="col-md-5">
              <div class="small text-muted mb-1">Detected Text</div>
              <input type="text" id="detectedText" class="form-control mb-2" readonly placeholder="—" />
              <button id="btnLookup" class="btn btn-primary btn-sm" disabled>
                <i class="fas fa-search me-1"></i> Lookup Burial
              </button>
            </div>
          </div>
        </div>

        <!-- Upload -->
        <div class="tab-pane fade" id="upload" role="tabpanel">
          <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 mb-2">
            <input type="file" id="filePicker" class="form-control" accept="image/*">
            <button id="btnDecodeFile" class="btn btn-primary">
              <i class="fas fa-barcode me-1"></i> Decode & Lookup
            </button>
          </div>
          <small class="text-muted">Tip: Screenshot a QR then upload here.</small>
          <canvas id="fileCanvas" class="d-none"></canvas>
        </div>
      </div>
    </div>

    <div id="resultBox" class="d-none">
      <div class="card">
        <div class="card-header" id="resultHeader">
          <i class="fas fa-receipt me-1"></i> Burial Info
        </div>
        <div class="card-body" id="resultBody"></div>
      </div>
    </div>
  </div>

  <!-- Libs (order matters) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const URLROOT = window.URLROOT || '';

    // elems
    const video   = document.getElementById('qrVideo');
    const canvas  = document.getElementById('qrCanvas');
    const ctx     = canvas.getContext('2d');
    const liveStatus = document.getElementById('liveStatus');
    const select  = document.getElementById('cameraSelect');
    const btnStart= document.getElementById('btnStart');
    const btnStop = document.getElementById('btnStop');
    const detected= document.getElementById('detectedText');
    const btnLookup = document.getElementById('btnLookup');

    const filePicker   = document.getElementById('filePicker');
    const btnDecodeFile= document.getElementById('btnDecodeFile');
    const fileCanvas   = document.getElementById('fileCanvas');
    const fctx         = fileCanvas.getContext('2d');

    const resultBox  = document.getElementById('resultBox');
    const resultBody = document.getElementById('resultBody');

    const toast = Swal.mixin({
      toast:true, position:'top-end', timer:2200, showConfirmButton:false, timerProgressBar:true
    });

    let stream = null, rafId = 0, running = false;

    const esc = s => (s==null)?'':String(s).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));

    async function listCameras(){
      try{
        const devices = await navigator.mediaDevices.enumerateDevices();
        const cams = devices.filter(d=>d.kind==='videoinput');
        select.innerHTML = '';
        if (!cams.length){
          const opt = document.createElement('option');
          opt.value=''; opt.textContent='No camera detected';
          select.appendChild(opt);
          select.disabled = true;
          btnStart.disabled = true;
          return;
        }
        cams.forEach((c,i)=>{
          const opt = document.createElement('option');
          opt.value = c.deviceId;
          opt.textContent = c.label || `Camera ${i+1}`;
          select.appendChild(opt);
        });
      }catch(e){
        console.error(e);
      }
    }

    async function start() {
      try{
        await stop();
        const constraints = {
          video: {
            deviceId: select.value ? { exact: select.value } : undefined,
            facingMode: 'environment',
            width: { ideal: 1280 }, height:{ ideal: 720 }
          }, audio:false
        };
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = stream;
        await video.play();
        running = true;
        btnStart.disabled = true; btnStop.disabled = false;
        scanLoop();
        liveStatus.textContent = 'Scanning...';
      }catch(e){
        toast.fire({icon:'error', title:'Unable to start camera'});
      }
    }

    async function stop(){
      running = false;
      btnStart.disabled = false; btnStop.disabled = true;
      if (rafId) cancelAnimationFrame(rafId);
      if (video) video.pause();
      if (stream){
        stream.getTracks().forEach(t=>t.stop());
        stream = null;
      }
      liveStatus.textContent = 'Idle';
    }

    function extractQRFromFrame() {
      if (!video.videoWidth) return null;
      const w = video.videoWidth, h = video.videoHeight;
      canvas.width = w; canvas.height = h;
      ctx.drawImage(video, 0, 0, w, h);
      const img = ctx.getImageData(0, 0, w, h);
      return window.jsQR ? jsQR(img.data, img.width, img.height) : null;
    }

    function scanLoop(){
      if (!running) return;
      const code = extractQRFromFrame();
      if (code && code.data){
        liveStatus.textContent = 'QR detected!';
        detected.value = code.data.trim();
        btnLookup.disabled = false;
        lookup(code.data.trim()); // auto-lookup
        stop();
        return;
      }
      rafId = requestAnimationFrame(scanLoop);
    }

    async function lookup(raw){
      const burialId = String(raw||'').trim();
      if (!burialId){ toast.fire({icon:'warning', title:'No data in QR'}); return; }

      try{
        const res  = await fetch(`${URLROOT}/maps/publicBurial/${encodeURIComponent(burialId)}`);
        const data = await res.json();

        if (!data.ok || !data.data){
          resultBody.innerHTML = `<div class="alert alert-warning mb-0">
            <i class="fas fa-triangle-exclamation me-1"></i> Burial not found for:
            <code>${esc(burialId)}</code></div>`;
          resultBox.classList.remove('d-none');
          return;
        }

        const d = data.data;
        const born = d.date_born ? new Date(d.date_born).toLocaleDateString() : '—';
        const died = d.date_died ? new Date(d.date_died).toLocaleDateString() : '—';

        resultBody.innerHTML = `
          <div class="mb-2"><span class="badge bg-dark">Burial ID</span> <code>${esc(d.burial_id||'')}</code></div>
          <dl class="mb-0">
            <dt>Deceased</dt><dd>${esc(d.deceased_full_name||'—')}</dd>
            <dt>Block / Plot</dt><dd>${esc(d.block_title||'—')} / ${esc(d.plot_number||'—')}</dd>
            <dt>Grave</dt><dd>${esc(d.grave_level||'—')} / ${esc(d.grave_type||'—')}</dd>
            <dt>Date Born</dt><dd>${born}</dd>
            <dt>Date Died</dt><dd>${died}</dd>
          </dl>`;
        resultBox.classList.remove('d-none');
      }catch(e){
        resultBody.innerHTML = `<div class="alert alert-danger mb-0">Network error. Please try again.</div>`;
        resultBox.classList.remove('d-none');
      }
    }

    async function decodeImageFile(){
      const file = filePicker.files?.[0];
      if (!file){ toast.fire({icon:'info', title:'Choose an image first'}); return; }
      const img = new Image();
      img.onload = ()=>{
        const w = img.naturalWidth, h = img.naturalHeight;
        fileCanvas.width = w; fileCanvas.height = h;
        fctx.drawImage(img, 0, 0);
        const data = fctx.getImageData(0,0,w,h);
        const code = window.jsQR ? jsQR(data.data, data.width, data.height) : null;
        if (code && code.data){
          detected.value = code.data.trim();
          btnLookup.disabled = false;
          lookup(code.data.trim());
        } else {
          resultBody.innerHTML = `<div class="alert alert-warning mb-0">No QR found in the image.</div>`;
          resultBox.classList.remove('d-none');
        }
      };
      img.onerror = ()=> toast.fire({icon:'error', title:'Invalid image'});
      img.src = URL.createObjectURL(file);
    }

    // events
    btnStart.addEventListener('click', start);
    btnStop.addEventListener('click', stop);
    btnLookup.addEventListener('click', ()=> lookup(detected.value));
    btnDecodeFile.addEventListener('click', decodeImageFile);

    // init
    (async ()=>{
      if (!navigator.mediaDevices?.getUserMedia){
        toast.fire({icon:'warning', title:'Camera API not supported. Use Upload tab.'});
        btnStart.disabled = true; btnStop.disabled = true; select.disabled = true;
        return;
      }
      // pre-prompt permission to show camera labels
      try{
        const tmp = await navigator.mediaDevices.getUserMedia({ video:true, audio:false });
        tmp.getTracks().forEach(t=>t.stop());
      }catch(e){}
      await listCameras();
    })();
  });
  </script>
</body>
</html>
