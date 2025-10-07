(function(){
  const URLROOT = window.URLROOT || '';
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

  let stream = null, rafId = 0, running = false;

  function toast(msg, type='info'){
    // minimal fallback
    console[type === 'error' ? 'error' : 'log'](msg);
  }

  async function listCameras(){
    const devices = await navigator.mediaDevices.enumerateDevices();
    const cams = devices.filter(d=>d.kind==='videoinput');
    select.innerHTML = '';
    cams.forEach((c,i)=>{
      const opt = document.createElement('option');
      opt.value = c.deviceId;
      opt.textContent = c.label || `Camera ${i+1}`;
      select.appendChild(opt);
    });
  }

  async function start() {
    try{
      await stop();
      const constraints = {
        video: {
          deviceId: select.value ? { exact: select.value } : undefined,
          facingMode: 'environment',
          width: { ideal: 1280 },
          height:{ ideal: 720 }
        },
        audio:false
      };
      stream = await navigator.mediaDevices.getUserMedia(constraints);
      video.srcObject = stream;
      await video.play();
      running = true;
      btnStart.disabled = true; btnStop.disabled = false;
      scanLoop();
    }catch(e){
      toast('Unable to start camera: ' + e.message, 'error');
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
      // optional: auto-lookup as soon as seen
      if (!resultBox.classList.contains('d-none')) { /* keep showing */ }
      else lookup(code.data.trim());
      stop();
      return;
    } else {
      liveStatus.textContent = 'Scanning...';
    }
    rafId = requestAnimationFrame(scanLoop);
  }

  async function lookup(raw){
    const burialId = String(raw||'').trim();
    if (!burialId){ toast('No data found in QR','error'); return; }

    try{
      const res = await fetch(`${URLROOT}/maps/publicBurial/${encodeURIComponent(burialId)}`);
      const data = await res.json();

      if (!data.ok || !data.data){
        resultBody.innerHTML = `<div class="alert alert-warning mb-0"><i class="fas fa-triangle-exclamation me-1"></i> Burial not found for: <code>${burialId}</code></div>`;
        resultBox.classList.remove('d-none');
        return;
      }

      const d = data.data;
      const born = d.date_born ? new Date(d.date_born).toLocaleDateString() : '—';
      const died = d.date_died ? new Date(d.date_died).toLocaleDateString() : '—';

      resultBody.innerHTML = `
        <div class="mb-2"><span class="badge bg-dark">Burial ID</span> <code>${escapeHtml(d.burial_id||'')}</code></div>
        <dl class="mb-0">
          <dt>Deceased</dt><dd>${escapeHtml(d.deceased_full_name||'—')}</dd>
          <dt>Block / Plot</dt><dd>${escapeHtml(d.block_title||'—')} / ${escapeHtml(d.plot_number||'—')}</dd>
          <dt>Grave</dt><dd>${escapeHtml(d.grave_level||'—')} / ${escapeHtml(d.grave_type||'—')}</dd>
          <dt>Date Born</dt><dd>${born}</dd>
          <dt>Date Died</dt><dd>${died}</dd>
        </dl>`;
      resultBox.classList.remove('d-none');
    }catch(e){
      resultBody.innerHTML = `<div class="alert alert-danger mb-0">Network error. Please try again.</div>`;
      resultBox.classList.remove('d-none');
    }
  }

  function escapeHtml(s){
    if (s===null||s===undefined) return '';
    return String(s).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
  }

  // Upload decode
  async function decodeImageFile(){
    const file = filePicker.files?.[0];
    if (!file){ toast('Choose an image first'); return; }
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
    img.onerror = ()=> toast('Invalid image','error');
    img.src = URL.createObjectURL(file);
  }

  // events
  btnStart?.addEventListener('click', start);
  btnStop ?.addEventListener('click', stop);
  btnLookup?.addEventListener('click', ()=> lookup(detected.value));
  btnDecodeFile?.addEventListener('click', decodeImageFile);

  // init: permissions & camera list
  (async ()=>{
    if (!navigator.mediaDevices?.getUserMedia){
      toast('Camera API not supported, use Upload tab.');
      btnStart.disabled = true; btnStop.disabled = true; select.disabled = true;
      return;
    }
    await navigator.mediaDevices.getUserMedia({ video:true }).then(s=>{
      s.getTracks().forEach(t=>t.stop());
    }).catch(()=>{ /* ignore */ });
    await listCameras();
  })();
})();
