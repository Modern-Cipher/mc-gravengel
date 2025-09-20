/* Follow-the-click modal (no Popper): fade-in/out only, no slide, exact arrow to click */
(function () {
  if (!document.getElementById('map-img')) return;

  const img = document.getElementById('map-img');
  const wrap = document.getElementById('img-wrap');
  const mapEl = document.getElementById('image-map');
  const layer = document.getElementById('hotspots');
  const toggleHL = document.getElementById('toggle-highlights');
  const toggleLabel = document.querySelector('label[for="toggle-highlights"]');
  const tooltip = document.getElementById('map-tooltip');

  // Dev menu
  const kebab = document.getElementById('kebab');
  const menu = document.getElementById('dev-menu');
  const menuClose = document.getElementById('menu-close');

  // Calibration
  const calxVal = document.getElementById('calx-val');
  const calyVal = document.getElementById('caly-val');
  const hlSlider = document.getElementById('hl-slider');
  const hlVal = document.getElementById('hl-val');
  const resetBtn = document.getElementById('reset-cal');
  const saveBtn = document.getElementById('save-cal');

  // Search pin
  const pin = document.getElementById('pin');
  const searchInput = document.getElementById('map-search-input');
  const searchBtn = document.getElementById('map-search-btn');

  // Modal (Bootstrap para sa show/hide lang; positioning tayo na)
  const modalElement = document.getElementById('blockInfoModal');
  modalElement.classList.remove('fade'); // no bootstrap animations
  const modalInstance = new bootstrap.Modal(modalElement, { backdrop: true, focus: true, keyboard: true });
  const modalDialog = modalElement.querySelector('.modal-dialog');
  const modalBody = document.getElementById('blockInfoModalBody');
  const modalHeader = modalElement.querySelector('.modal-header');
  const modalTitle  = document.getElementById('blockInfoModalLabel');

  // Arrow element (we still use CSS based on data-popper-placement)
  let pointerEl = modalDialog.querySelector('.modal-pointer');
  if (!pointerEl) {
    pointerEl = document.createElement('div');
    pointerEl.className = 'modal-pointer';
    pointerEl.setAttribute('data-popper-arrow', '');
    modalDialog.appendChild(pointerEl);
  }

  const blockElements = new Map();
  let activeBlock = null;
  let allPlotsData = [];
  let currentPage = 1;
  let plotsPerPage = 24;

  // Last click (viewport coords)
  let clickPoint = { x: window.innerWidth/2, y: window.innerHeight/2 };

  // Helpers
  const debounce = (fn, ms=60) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; };
  const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
  const norm = coords => { const c=coords.split(',').map(Number); return [Math.min(c[0],c[2]),Math.min(c[1],c[3]),Math.max(c[0],c[2]),Math.max(c[1],c[3])]; };

  // Build <area> from config
  function buildAreasFromConfig() {
    if (!window.CEMAP_BLOCKS || mapEl.children.length > 0) return;
    window.CEMAP_BLOCKS.forEach(block => {
      const a = document.createElement('area');
      a.shape = 'rect';
      a.dataset.key = block.block_key;
      a.title = block.title;
      a.dataset.orig = norm(block.coords).join(',');
      a.dataset.offsetX = block.offset_x || 0;
      a.dataset.offsetY = block.offset_y || 0;
      a.dataset.id = block.id;
      mapEl.appendChild(a);
    });
  }

  // Draw overlay boxes
  function drawOverlays() {
    if (!img.complete || !img.naturalWidth) return;
    const scale = img.offsetWidth / img.naturalWidth;

    const frag = document.createDocumentFragment();
    mapEl.querySelectorAll('area[data-orig]').forEach(a=>{
      const [l,t,r,b] = a.dataset.orig.split(',').map(Number);
      const dx = Number(a.dataset.offsetX), dy = Number(a.dataset.offsetY);
      const left=(l*scale)+dx, top=(t*scale)+dy, width=(r-l)*scale, height=(b-t)*scale;

      const box = document.createElement('div');
      box.className='hotspot';
      box.dataset.key=a.dataset.key;
      box.style.cssText=`left:${left}px;top:${top}px;width:${width}px;height:${height}px;border-width:${hlSlider?.value ?? 2}px;`;

      box.addEventListener('click',(ev)=>handleBlockClick(a.dataset.key,ev));
      box.addEventListener('mouseenter',(e)=>showTooltip(e,a.title));
      box.addEventListener('mouseleave', hideTooltip);
      box.addEventListener('mousemove', moveTooltip);

      frag.appendChild(box);
      blockElements.set(a.dataset.key, box);
    });

    layer.innerHTML='';
    layer.appendChild(frag);
    layer.classList.toggle('hidden', !toggleHL?.checked);
  }
  const drawAll = debounce(drawOverlays);

  // ---- Custom deterministic positioning (no Popper) ----
  function measureDialog() {
    // clone offscreen to measure layout
    const clone = modalDialog.cloneNode(true);
    clone.style.position = 'fixed';
    clone.style.left = '-9999px';
    clone.style.top = '-9999px';
    clone.style.visibility = 'hidden';
    clone.style.pointerEvents = 'none';
    clone.classList.add('modal-anchored');
    document.body.appendChild(clone);
    const rect = clone.getBoundingClientRect();
    document.body.removeChild(clone);
    return {
      width: Math.max(320, rect.width || 480),
      height: Math.max(220, rect.height || 320)
    };
  }

  function choosePlacement(x, y) {
    const { innerWidth: vw, innerHeight: vh } = window;
    const { width: mw, height: mh } = measureDialog();
    const pad = 16;
    const spaceTop = y - pad;
    const spaceBottom = vh - y - pad;
    const spaceLeft = x - pad;
    const spaceRight = vw - x - pad;
    if (spaceBottom >= mh) return 'bottom';
    if (spaceTop >= mh)    return 'top';
    if (spaceRight >= mw)  return 'right';
    if (spaceLeft >= mw)   return 'left';
    return [['bottom',spaceBottom],['top',spaceTop],['right',spaceRight],['left',spaceLeft]]
      .sort((a,b)=>b[1]-a[1])[0][0];
  }

  function positionModal({ hideDuring=false } = {}) {
    modalDialog.classList.add('modal-anchored');
    const { innerWidth: vw, innerHeight: vh } = window;
    const { width: mw, height: mh } = measureDialog();
    const gap = 14;

    const x = clickPoint.x ?? vw/2;
    const y = clickPoint.y ?? vh/2;

    const placement = choosePlacement(x, y);
    modalDialog.setAttribute('data-popper-placement', placement);

    let left=0, top=0;

    if (placement === 'bottom') {
      left = clamp(x - mw/2, 8, vw - mw - 8);
      top  = Math.min(y + gap, vh - mh - 8);
      pointerEl.style.left = clamp(x - left, 10, mw - 10) + 'px';
      pointerEl.style.top  = '';
    } else if (placement === 'top') {
      left = clamp(x - mw/2, 8, vw - mw - 8);
      top  = Math.max(y - gap - mh, 8);
      pointerEl.style.left = clamp(x - left, 10, mw - 10) + 'px';
      pointerEl.style.top  = '';
    } else if (placement === 'right') {
      left = Math.min(x + gap, vw - mw - 8);
      top  = clamp(y - mh/2, 8, vh - mh - 8);
      pointerEl.style.top  = clamp(y - top, 10, mh - 10) + 'px';
      pointerEl.style.left = '';
    } else { // left
      left = Math.max(x - gap - mw, 8);
      top  = clamp(y - mh/2, 8, vh - mh - 8);
      pointerEl.style.top  = clamp(y - top, 10, mh - 10) + 'px';
      pointerEl.style.left = '';
    }

    if (hideDuring) {
      modalDialog.style.opacity = '0';
      modalDialog.style.visibility = 'hidden';
    }

    modalDialog.style.left = `${left}px`;
    modalDialog.style.top  = `${top}px`;

    if (hideDuring) {
      // show next frame without any slide
      requestAnimationFrame(() => {
        modalDialog.style.visibility = '';
        modalDialog.style.opacity = '';
      });
    }
  }

  const reanchor = debounce(() => {
    if (modalElement.classList.contains('show')) positionModal({ hideDuring:true });
  }, 40);

  // ---- Interactions ----
  async function handleBlockClick(blockKey, ev){
    clickPoint = { x: ev.clientX, y: ev.clientY };

    document.querySelectorAll('.hotspot.active').forEach(h=>h.classList.remove('active'));
    const el = blockElements.get(blockKey);
    if (el) {
      if (toggleHL?.checked) el.classList.add('active');
      const areaEl = mapEl.querySelector(`area[data-key="${blockKey}"]`);
      activeBlock = {
        id: areaEl.dataset.id,
        key: blockKey,
        offsetX: parseInt(areaEl.dataset.offsetX,10),
        offsetY: parseInt(areaEl.dataset.offsetY,10),
        element: el
      };
      updateCalUI();
    }
    hidePin();

    if (!modalElement.classList.contains('show')) {
      // Pre-position (hidden), then show with fade-in
      positionModal({ hideDuring:true });
      modalDialog.style.opacity = '0';

      modalElement.addEventListener('shown.bs.modal', () => {
        // ensure final coords (no searching)
        positionModal();
        // pure fade-in (Web Animations)
        modalDialog.animate([{opacity:0},{opacity:1}],{duration:160,easing:'ease',fill:'forwards'});
      }, { once:true });

      modalInstance.show();
    } else {
      // already open: jump to new click silently (no animation)
      positionModal({ hideDuring:true });
    }

    await openBlockData(blockKey);
  }

  async function openBlockData(blockKey){
    modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"></div></div>';
    currentPage = 1;

    const res = await fetch(`${window.URLROOT}/maps/getBlockDetails/${blockKey}`);
    const data = await res.json();
    modalTitle.textContent = data.title || 'Block Details';

    if (!data.plots || data.plots.length === 0) {
      const old = modalHeader.querySelector('.modal-header-controls'); if(old) old.remove();
      modalBody.innerHTML = '<div class="alert alert-info m-0">No plot layout. Go to <strong>Manage Blocks</strong> to configure.</div>';
      requestAnimationFrame(()=>reanchor());
    } else {
      allPlotsData = data.plots;
      renderCurrentPage(data);
    }
  }

  function renderCurrentPage(data, filtered=null){
    const arr = filtered ?? allPlotsData;
    const cols = window.innerWidth < 768 ? 3 : 6;
    const rows = data.modal_rows || 1;
    plotsPerPage = rows * cols;

    const total = Math.max(1, Math.ceil((arr.length||0)/plotsPerPage));
    currentPage = Math.min(currentPage, total);
    const start = (currentPage-1)*plotsPerPage;
    const page = arr.slice(start, start+plotsPerPage);

    let html = `<div class="plot-grid" style="--cols:${cols};">`;
    if (page.length) {
      page.forEach(p=>{
        const name = p.deceased_name ? `<span class="plot-name">${p.deceased_name}</span>` : 'Available';
        html += `<div class="plot-cell ${p.status}" data-plot-number="${p.plot_number}" title="Plot #${p.plot_number}">
                  <span class="plot-number">${p.plot_number}</span>${name}
                 </div>`;
      });
    } else {
      html += `<div class="alert alert-warning" style="grid-column:1/-1">No plots found.</div>`;
    }
    html += `</div>`;

    const old = modalHeader.querySelector('.modal-header-controls'); if(old) old.remove();
    const ctrls = document.createElement('div');
    ctrls.className='modal-header-controls';
    ctrls.innerHTML = `
      <input type="search" id="modal-plot-search" class="form-control form-control-sm" placeholder="Search Plot #">
      <div class="pagination-controls">
        <button class="btn btn-secondary btn-sm" id="prev-page-btn" ${currentPage===1?'disabled':''}>Prev</button>
        <span class="page-info">Page ${currentPage} of ${total}</span>
        <button class="btn btn-secondary btn-sm" id="next-page-btn" ${currentPage===total||arr.length===0?'disabled':''}>Next</button>
      </div>`;
    modalHeader.insertBefore(ctrls, modalTitle.nextSibling);

    modalBody.innerHTML = html;

    document.getElementById('prev-page-btn')?.addEventListener('click',()=>{ if(currentPage>1){ currentPage--; renderCurrentPage(data, filtered); } });
    document.getElementById('next-page-btn')?.addEventListener('click',()=>{ if(currentPage<total){ currentPage++; renderCurrentPage(data, filtered); } });
    document.getElementById('modal-plot-search')?.addEventListener('input',(e)=>{
      const q = e.target.value.trim();
      const filtered2 = !q ? null : allPlotsData.filter(p=>String(p.plot_number).startsWith(q));
      currentPage=1; renderCurrentPage(data, filtered2);
    });

    requestAnimationFrame(()=>reanchor());
  }

  // Fade-out on close (no CSS transition dependency)
  let closing = false;
  modalElement.addEventListener('hide.bs.modal', (ev) => {
    if (closing) return;
    ev.preventDefault();
    closing = true;

    const anim = modalDialog.animate([{opacity:1},{opacity:0}], { duration:160, easing:'ease', fill:'forwards' });
    const finalize = () => {
      modalInstance.hide();
      setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('paddingRight');
        closing = false;
      }, 0);
    };
    anim.onfinish = finalize;
    setTimeout(() => { if (closing) finalize(); }, 220);
  });

  modalElement.addEventListener('hidden.bs.modal', () => {
    modalDialog.classList.remove('modal-anchored');
    modalDialog.style.opacity = '';
    modalDialog.removeAttribute('data-popper-placement');
    closing = false;
  });

  // Tooltip
  function showTooltip(e,text){ tooltip.textContent=text; tooltip.style.display='block'; moveTooltip(e); }
  function hideTooltip(){ tooltip.style.display='none'; }
  function moveTooltip(e){ const r=wrap.getBoundingClientRect(); tooltip.style.left=`${e.clientX-r.left+15}px`; tooltip.style.top=`${e.clientY-r.top}px`; }

  // Search
  function handleSearch(){
    const q = searchInput?.value?.trim().toLowerCase();
    if(!q) return;
    const f = window.CEMAP_BLOCKS.find(b=>b.title.toLowerCase().includes(q));
    if(f){
      const el = blockElements.get(f.block_key);
      if(el){ el.scrollIntoView({behavior:'smooth',block:'center'}); showPin(el,f.title); }
    } else { alert('Block not found!'); hidePin(); }
  }
  function showPin(target,title){
    if(!pin) return;
    const bubble=pin.querySelector('.pin-bubble');
    const tr=target.getBoundingClientRect(), wr=wrap.getBoundingClientRect();
    const x=(tr.left-wr.left)+tr.width/2, y=(tr.top-wr.top)+tr.height/2;
    pin.style.left=`${x}px`; pin.style.top=`${y}px`; bubble.textContent=title; pin.style.display='flex';
  }
  function hidePin(){ if(pin) pin.style.display='none'; }

  // Calibration
  function updateCalUI(){
    if(!activeBlock) return;
    calxVal && (calxVal.textContent = activeBlock.offsetX);
    calyVal && (calyVal.textContent = activeBlock.offsetY);
    hlVal  && (hlVal.textContent  = `${hlSlider?.value ?? 2}px`);
  }

  function handleCal(e){
    const btn = e.target.closest('[data-cal]');
    if(!btn || !activeBlock) return;
    const t = btn.dataset.cal;
    if(t==='x-') activeBlock.offsetX--;
    if(t==='x+') activeBlock.offsetX++;
    if(t==='y-') activeBlock.offsetY--;
    if(t==='y+') activeBlock.offsetY++;

    updateCalUI();

    const scale=img.offsetWidth/img.naturalWidth;
    const areaEl=mapEl.querySelector(`area[data-key="${activeBlock.key}"]`);
    if (areaEl && activeBlock.element){
      const [l,t0]=areaEl.dataset.orig.split(',').map(Number);
      activeBlock.element.style.left = `${(l*scale)+activeBlock.offsetX}px`;
      activeBlock.element.style.top  = `${(t0*scale)+activeBlock.offsetY}px`;
    }
    reanchor();
  }

  async function saveCalibration(){
    if(!activeBlock){ alert('No block selected to save.'); return; }
    const {id,offsetX,offsetY}=activeBlock;
    const r=await fetch(`${window.URLROOT}/maps/saveCalibration`,{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({block_id:id,offset_x:offsetX,offset_y:offsetY})
    });
    const j=await r.json();
    if(j.success){
      const areaEl=mapEl.querySelector(`area[data-key="${activeBlock.key}"]`);
      if (areaEl){ areaEl.dataset.offsetX=offsetX; areaEl.dataset.offsetY=offsetY; }
      alert('Calibration saved successfully!');
    }else{ alert('Error: '+j.message); }
  }

  // Hooks
  kebab?.addEventListener('click', ()=> menu?.removeAttribute('hidden'));
  menuClose?.addEventListener('click', ()=> menu?.setAttribute('hidden', true));
  menu?.addEventListener('click', handleCal);

  resetBtn?.addEventListener('click',()=>{ 
    if(!activeBlock) return;
    activeBlock.offsetX=0; activeBlock.offsetY=0;
    if (hlSlider) hlSlider.value=2;
    updateCalUI(); drawAll(); reanchor();
  });
  saveBtn?.addEventListener('click',saveCalibration);

  hlSlider?.addEventListener('input',()=>{ 
    if(activeBlock && activeBlock.element) activeBlock.element.style.borderWidth=`${hlSlider.value}px`; 
    hlVal && (hlVal.textContent=`${hlSlider.value}px`); 
  });
  toggleHL?.addEventListener('change',()=>{ layer.classList.toggle('hidden',!toggleHL.checked); toggleLabel && (toggleLabel.textContent=toggleHL.checked?'On':'Off'); });

  searchBtn?.addEventListener('click',handleSearch);
  searchInput?.addEventListener('keydown',(e)=>{ if(e.key==='Enter'){ e.preventDefault(); handleSearch(); } });

  // Reanchor on resize/scroll
  window.addEventListener('resize', reanchor, {passive:true});
  window.addEventListener('scroll', reanchor, {passive:true});

  // Init
  function init(){
    buildAreasFromConfig();
    drawAll();
    if (toggleHL) { toggleHL.checked=false; layer.classList.add('hidden'); toggleLabel && (toggleLabel.textContent='Off'); }
  }
  window.addEventListener('resize', drawAll);
  if(img.complete){ init(); } else { img.addEventListener('load', init); }
})();
