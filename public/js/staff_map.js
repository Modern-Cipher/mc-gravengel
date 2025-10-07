/* public/js/staff_map.js
 * Staff (read-only) map with anchored modal:
 * - clickable overlays (from window.CEMAP_BLOCKS)
 * - search w/ centered pin + toast kapag not found
 * - highlight toggle (show/hide overlays)
 * - anchored block modal (follows click; never under header/footer)
 * - grid + plot search (NO pagination; scrollbar lang; live filter)
 * - burial details modal (read-only; guarded)
 */
(function () {
  // ---- required DOM ----
  const img         = document.getElementById('map-img');
  if (!img) return;

  const wrap        = document.getElementById('img-wrap');
  const mapEl       = document.getElementById('image-map');
  const layer       = document.getElementById('hotspots');
  const tooltip     = document.getElementById('map-tooltip');
  const pin         = document.getElementById('pin');

  const toggleHL    = document.getElementById('toggle-highlights');
  const toggleLbl   = document.getElementById('hl-toggle-label');

  const searchInput = document.getElementById('map-search-input');
  const searchBtn   = document.getElementById('map-search-btn');

  // Block grid modal
  const modalEl     = document.getElementById('blockInfoModal');
  const modalDialog = modalEl ? modalEl.querySelector('.modal-dialog') : null;
  const modalBody   = document.getElementById('blockInfoModalBody');
  const modalTitle  = document.getElementById('blockInfoModalLabel');
  const modal       = modalEl ? new bootstrap.Modal(modalEl, { backdrop: true, focus: true, keyboard: true }) : null;

  // pointer arrow once
  let pointerEl = modalDialog ? modalDialog.querySelector('.modal-pointer') : null;
  if (modalDialog && !pointerEl) {
    pointerEl = document.createElement('div');
    pointerEl.className = 'modal-pointer';
    pointerEl.setAttribute('data-popper-arrow', '');
    modalDialog.appendChild(pointerEl);
  }

  // Burial details modal
  const burialModalEl    = document.getElementById('burialDetailsModal');
  const burialModalBody  = document.getElementById('burialDetailsModalBody');
  const burialModalTitle = document.getElementById('burialDetailsModalLabel');

  const URLROOT = window.URLROOT || '';
  const blocks  = Array.isArray(window.CEMAP_BLOCKS) ? window.CEMAP_BLOCKS : [];

  // ---- state ----
  const blockBoxes = new Map(); // block_key -> overlay div
  let currentBlock = null;
  let allPlots     = [];

  // track last click for anchoring
  let clickPoint   = { x: window.innerWidth / 2, y: window.innerHeight / 2 };

  // ---- utils ----
  const debounce = (fn, ms=60) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };
  const norm     = (coords)=>{ const c=String(coords||'').split(',').map(Number); return [Math.min(c[0],c[2]),Math.min(c[1],c[3]),Math.max(c[0],c[2]),Math.max(c[1],c[3])]; };
  const safe     = (x)=> (x===undefined||x===null||x==='') ? '—' : x;

  // SweetAlert2 toast
  const Toast = (opts={}) => {
    if (!window.Swal) { alert(opts.title || 'Notice'); return; }
    const t = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2200, timerProgressBar:true });
    t.fire(Object.assign({ icon:'info', title:'Notice' }, opts));
  };

  // ---- areas from config ----
  function buildAreas() {
    if (!blocks.length || !mapEl || mapEl.children.length) return;
    blocks.forEach(b=>{
      const a = document.createElement('area');
      a.shape           = 'rect';
      a.dataset.key     = b.block_key;
      a.title           = b.title || b.block_key;
      a.dataset.orig    = norm(b.coords).join(',');
      a.dataset.offsetX = b.offset_x || 0;
      a.dataset.offsetY = b.offset_y || 0;
      a.dataset.id      = b.id;
      mapEl.appendChild(a);
    });
  }

  // ---- draw overlays ----
  function drawOverlays() {
    if (!img.complete || !img.naturalWidth || !layer) return;
    const scale = img.offsetWidth / img.naturalWidth;

    const frag = document.createDocumentFragment();
    blockBoxes.clear();

    mapEl.querySelectorAll('area[data-orig]').forEach(a=>{
      const [l,t,r,b] = a.dataset.orig.split(',').map(Number);
      const dx = +a.dataset.offsetX || 0;
      const dy = +a.dataset.offsetY || 0;

      const box = document.createElement('div');
      box.className   = 'hotspot';
      box.dataset.key = a.dataset.key;
      box.style.left  = ((l * scale) + dx) + 'px';
      box.style.top   = ((t * scale) + dy) + 'px';
      box.style.width = ((r - l) * scale) + 'px';
      box.style.height= ((b - t) * scale) + 'px';

      box.addEventListener('click', (ev)=> onBlockClick(a, box, ev));
      box.addEventListener('mouseenter', (e)=> showTip(e, a.title));
      box.addEventListener('mouseleave', hideTip);
      box.addEventListener('mousemove', moveTip);

      frag.appendChild(box);
      blockBoxes.set(a.dataset.key, box);
    });

    layer.innerHTML = '';
    layer.appendChild(frag);
    layer.classList.toggle('hidden', !(toggleHL && toggleHL.checked));
  }
  const redraw = debounce(drawOverlays);

  // ---- anchored modal helpers ----
  function measureDialog() {
    if (!modalDialog) return { width: 480, height: 320 };
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
      width:  Math.max(320, rect.width  || 480),
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
  function clamp(v,min,max){ return Math.max(min, Math.min(max, v)); }
  function positionModal({ hideDuring=false } = {}) {
    if (!modalEl || !modalDialog) return;

    modalDialog.classList.add('modal-anchored');

    const { innerWidth: vw, innerHeight: vh } = window;
    const { width: mw, height: mh } = measureDialog();
    const gap = 14;

    const x = clickPoint.x ?? vw/2;
    const y = clickPoint.y ?? vh/2;

    const placement = choosePlacement(x, y);
    modalDialog.setAttribute('data-popper-placement', placement);

    let left = 0, top = 0;
    if (placement === 'bottom') {
      left = clamp(x - mw/2, 8, vw - mw - 8);
      top  = Math.min(y + gap, vh - mh - 8);
      if (pointerEl) { pointerEl.style.left = clamp(x - left, 10, mw - 10) + 'px'; pointerEl.style.top = ''; }
    } else if (placement === 'top') {
      left = clamp(x - mw/2, 8, vw - mw - 8);
      top  = Math.max(y - gap - mh, 8);
      if (pointerEl) { pointerEl.style.left = clamp(x - left, 10, mw - 10) + 'px'; pointerEl.style.top = ''; }
    } else if (placement === 'right') {
      left = Math.min(x + gap, vw - mw - 8);
      top  = clamp(y - mh/2, 8, vh - mh - 8);
      if (pointerEl) { pointerEl.style.top = clamp(y - top, 10, mh - 10) + 'px'; pointerEl.style.left = ''; }
    } else {
      left = Math.max(x - gap - mw, 8);
      top  = clamp(y - mh/2, 8, vh - mh - 8);
      if (pointerEl) { pointerEl.style.top = clamp(y - top, 10, mh - 10) + 'px'; pointerEl.style.left = ''; }
    }

    if (hideDuring) {
      modalDialog.style.opacity = '0';
      modalDialog.style.visibility = 'hidden';
    }
    modalDialog.style.left = left + 'px';
    modalDialog.style.top  = top  + 'px';
    if (hideDuring) {
      requestAnimationFrame(() => {
        modalDialog.style.visibility = '';
        modalDialog.style.opacity = '';
      });
    }
  }
  const reanchor = debounce(() => {
    if (modalEl && modalEl.classList.contains('show')) positionModal({ hideDuring: true });
  }, 40);

  // custom fade-out
  let closing = false;
  if (modalEl && modalDialog && modal) {
    modalEl.addEventListener('hide.bs.modal', (ev) => {
      if (closing) return;
      ev.preventDefault();
      closing = true;

      const anim = modalDialog.animate([{opacity:1},{opacity:0}], { duration:160, easing:'ease', fill:'forwards' });
      const finalize = () => {
        try { modal.hide(); }
        finally {
          setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('paddingRight');
            closing = false;
          }, 0);
        }
      };
      anim.onfinish = finalize;
      setTimeout(() => { if (closing) finalize(); }, 220);
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
      modalDialog.classList.remove('modal-anchored');
      modalDialog.style.opacity = '';
      modalDialog.removeAttribute('data-popper-placement');
      closing = false;
    });
  }

  // ---- tooltip ----
  function showTip(e, text){ if(!tooltip||!wrap) return; tooltip.textContent = text||''; tooltip.style.display='block'; moveTip(e); }
  function hideTip(){ if(!tooltip) return; tooltip.style.display='none'; }
  function moveTip(e){
    if(!tooltip||!wrap) return;
    const r = wrap.getBoundingClientRect();
    tooltip.style.left = (e.clientX - r.left + 14) + 'px';
    tooltip.style.top  = (e.clientY - r.top) + 'px';
  }

  // ---- search + pin ----
  function onSearch(){
    const q = (searchInput?.value||'').trim().toLowerCase();
    if(!q){ Toast({icon:'info', title:'Type a block name first'}); return; }

    const found = blocks.find(b => (b.title||'').toLowerCase().includes(q));
    if(!found){ hidePin(); Toast({icon:'warning', title:'Block not found'}); return; }

    const box = blockBoxes.get(found.block_key);
    if(!box){ hidePin(); Toast({icon:'warning', title:'Block not on screen'}); return; }

    box.scrollIntoView({ behavior:'smooth', block:'center', inline:'center' });
    showPinOn(box, found.title || found.block_key);
  }
  function showPinOn(target, title){
    if(!pin||!wrap||!target) return;
    const bubble = pin.querySelector('.pin-bubble');
    const tr = target.getBoundingClientRect();
    const wr = wrap.getBoundingClientRect();
    const x = (tr.left - wr.left) + tr.width/2;
    const y = (tr.top  - wr.top ) + tr.height/2;
    pin.style.left = x + 'px';
    pin.style.top  = y + 'px';
    if(bubble) bubble.textContent = title || '';
    pin.style.display = 'flex';
  }
  function hidePin(){ if(pin) pin.style.display='none'; }

  // ---- on block click -> anchored grid modal ----
  async function onBlockClick(areaEl, boxEl, ev){
    clickPoint = { x: ev.clientX, y: ev.clientY };
    hidePin();

    if(toggleHL && toggleHL.checked){
      document.querySelectorAll('.hotspot.active').forEach(x=>x.classList.remove('active'));
      boxEl.classList.add('active');
    }

    currentBlock = {
      id:    areaEl.dataset.id,
      key:   areaEl.dataset.key,
      title: areaEl.title,
    };

    if (modalEl && modal && modalDialog) {
      if (!modalEl.classList.contains('show')) {
        positionModal({ hideDuring:true });
        modalDialog.style.opacity = '0';
        modalEl.addEventListener('shown.bs.modal', () => {
          positionModal();
          modalDialog.animate([{opacity:0},{opacity:1}], { duration:160, easing:'ease', fill:'forwards' });
        }, { once:true });
        modal.show();
      } else {
        positionModal({ hideDuring:true });
      }
    }

    if(modalTitle) modalTitle.textContent = currentBlock.title || 'Block Details';
    if(modalBody)  modalBody.innerHTML = `<div class="text-center p-4"><div class="spinner-border" role="status"></div></div>`;

    try{
      const res  = await fetch(`${URLROOT}/maps/getBlockDetails/${encodeURIComponent(currentBlock.key)}`);
      const data = await res.json();

      allPlots = Array.isArray(data.plots) ? data.plots : [];
      renderBlockGrid(data);
      if (!allPlots.length) {
        Toast({icon:'info', title:'No plots configured for this block'});
      }
    }catch(e){
      if(modalBody) modalBody.innerHTML = `<div class="alert alert-danger m-0">Failed to load block details.</div>`;
      Toast({icon:'error', title:'Failed to load block details'});
    }
  }

  // ---- grid renderer (NO pagination; LIVE FILTER; input persists) ----
  function renderBlockGrid(meta) {
    if (!modalBody) return;

    const rows = meta.modal_rows || 4;
    const cols = meta.modal_cols || 8;

    // header + empty grid container ONCE
    modalBody.innerHTML = `
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="input-group input-group-sm" style="max-width:220px;">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
          <input type="search" class="form-control" id="plot-search" placeholder="Search Plot #">
        </div>
      </div>
      <div class="plot-grid" id="plot-grid" style="--cols:${cols};"></div>
    `;

    const gridEl  = modalBody.querySelector('#plot-grid');
    const inputEl = modalBody.querySelector('#plot-search');

    // draw only the grid (so the input is not recreated/cleared)
    function drawGrid(list) {
      if (!gridEl) return;
      if (!list || !list.length) {
        gridEl.innerHTML = `<div class="alert alert-warning m-0" style="grid-column:1/-1">No plots found.</div>`;
        requestAnimationFrame(() => reanchor());
        return;
      }
      let html = '';
      list.forEach(p => {
        const fullName = (p.deceased_first_name && p.deceased_last_name)
          ? `${p.deceased_first_name} ${p.deceased_last_name}` : '';
        const status   = p.status || 'unknown';
        const label    = status === 'vacant' ? 'Available' : (fullName || 'Occupied');
        const title    = status === 'vacant'
          ? `Plot #${p.plot_number}: Vacant`
          : `Plot #${p.plot_number}: ${fullName || 'Occupied'}`;

        const clickable = (status !== 'vacant' && p.burial_id)
          ? `data-bs-toggle="modal" data-bs-target="#burialDetailsModal" data-burial-id="${p.burial_id}"`
          : '';

        html += `
          <div class="plot-cell ${status}" data-plot-id="${p.id}" data-plot-number="${p.plot_number}"
               title="${title}" ${clickable}>
            <span class="plot-number">${p.plot_number}</span>
            <span class="plot-name">${label}</span>
          </div>`;
      });
      gridEl.innerHTML = html;
      requestAnimationFrame(() => reanchor());
    }

    // initial render
    drawGrid(allPlots);

    // live filter (doesn't rebuild the input)
    inputEl && inputEl.addEventListener('input', debounce((e) => {
      const q = (e.target.value || '').trim().toLowerCase();
      if (!q) {
        drawGrid(allPlots);
        return;
      }
      const filtered = allPlots.filter(p =>
        String(p.plot_number || '').toLowerCase().includes(q)
      );
      drawGrid(filtered);
      if (!filtered.length) {
        Toast({ icon: 'warning', title: 'No plots match your search' });
      }
    }, 100));
  }

  // ---- burial details (guard: needs burial_id) ----
  if (burialModalEl) {
    burialModalEl.addEventListener('show.bs.modal', async function (ev) {
      const trigger = ev.relatedTarget;
      const burialId= trigger ? trigger.getAttribute('data-burial-id') : null;
      if(!burialId){ ev.preventDefault(); return; }

      if(burialModalBody) burialModalBody.innerHTML = `
        <div class="text-center p-4">
          <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>`;
      if(burialModalTitle) burialModalTitle.textContent = `Burial Details for ${safe(burialId)}`;

      try{
        const res  = await fetch(`${URLROOT}/staff/getBurialDetails/${encodeURIComponent(burialId)}`);
        const data = await res.json();

        if(data && !data.error){
          const d = {
            deceased_first_name:      safe(data.deceased_first_name),
            deceased_last_name:       safe(data.deceased_last_name),
            plot_number:              safe(data.plot_number),
            burial_id:                safe(data.burial_id),
            age:                      safe(data.age),
            sex:                      safe(data.sex),
            date_born:                safe(data.date_born),
            date_died:                safe(data.date_died),
            cause_of_death:           safe(data.cause_of_death),
            interment_full_name:      safe(data.interment_full_name),
            interment_relationship:   safe(data.interment_relationship),
            interment_contact_number: safe(data.interment_contact_number),
            interment_address:        safe(data.interment_address),
            rental_date:              safe(data.rental_date),
            expiry_date:              safe(data.expiry_date),
          };
          burialModalBody.innerHTML = `
            <h4 class="mb-3">Deceased: ${d.deceased_first_name} ${d.deceased_last_name}</h4>
            <div class="row">
              <div class="col-md-6">
                <p><strong>Plot #:</strong> ${d.plot_number}</p>
                <p><strong>Burial ID:</strong> ${d.burial_id}</p>
                <p><strong>Age:</strong> ${d.age}</p>
                <p><strong>Sex:</strong> ${d.sex}</p>
                <p><strong>Born:</strong> ${d.date_born}</p>
                <p><strong>Died:</strong> ${d.date_died}</p>
                <p><strong>Cause of Death:</strong> ${d.cause_of_death}</p>
              </div>
              <div class="col-md-6">
                <h5>Interment Right Holder</h5>
                <p><strong>Name:</strong> ${d.interment_full_name}</p>
                <p><strong>Relationship:</strong> ${d.interment_relationship}</p>
                <p><strong>Contact:</strong> ${d.interment_contact_number}</p>
                <p><strong>Address:</strong> ${d.interment_address}</p>
                <p><strong>Rental Date:</strong> ${d.rental_date}</p>
                <p><strong>Expiry Date:</strong> ${d.expiry_date}</p>
              </div>
            </div>`;
        }else{
          burialModalBody.innerHTML = `<div class="alert alert-danger m-0">Burial details not found.</div>`;
          Toast({icon:'warning', title:'Burial details not found'});
        }
      }catch(err){
        burialModalBody.innerHTML = `<div class="alert alert-danger m-0">Failed to load details. Please try again.</div>`;
        Toast({icon:'error', title:'Failed to load burial details'});
      }
    });
  }

  // ---- highlight toggle ----
  function onToggle(){
    const on = !!(toggleHL && toggleHL.checked);
    layer.classList.toggle('hidden', !on);
    if(toggleLbl) toggleLbl.textContent = on ? '(On)' : '(Off)';
  }

  // ---- events ----
  toggleHL && toggleHL.addEventListener('change', onToggle);
  searchBtn && searchBtn.addEventListener('click', onSearch);
  searchInput && searchInput.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); onSearch(); } });

  window.addEventListener('resize', () => { redraw(); reanchor(); }, { passive:true });
  window.addEventListener('scroll', reanchor, { passive:true });

  // ---- init ----
  function init(){
    buildAreas();
    drawOverlays();
    if(toggleHL){ toggleHL.checked = false; onToggle(); }
  }
  if (img.complete) init();
  else img.addEventListener('load', init);
})();
