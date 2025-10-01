/* public/js/admin_map.js
 * Clean recode (as-is features retained, safer guards)
 * - No duplicate functions
 * - Will not open burial modal without burial_id
 * - Null-safe field rendering in Burial Details
 * - Keeps calibration/search/manage flows and anchored block modal
 */
(function () {
  // Required root image; if missing, do nothing
  const img = document.getElementById('map-img');
  if (!img) return;

  // ---- Element refs (keep existing IDs) ----
  const wrap       = document.getElementById('img-wrap');
  const mapEl      = document.getElementById('image-map');
  const layer      = document.getElementById('hotspots');
  const toggleHL   = document.getElementById('toggle-highlights');
  const toggleLabel= document.querySelector('label[for="toggle-highlights"] span');
  const tooltip    = document.getElementById('map-tooltip');

  // Dev menu
  const kebab      = document.getElementById('kebab');
  const menu       = document.getElementById('dev-menu');
  const menuClose  = document.getElementById('menu-close');

  // Calibration controls
  const calxVal    = document.getElementById('calx-val');
  const calyVal    = document.getElementById('caly-val');
  const hlSlider   = document.getElementById('hl-slider');
  const hlVal      = document.getElementById('hl-val');
  const resetBtn   = document.getElementById('reset-cal');
  const saveBtn    = document.getElementById('save-cal');

  // Search
  const pin        = document.getElementById('pin');
  const searchInput= document.getElementById('map-search-input');
  const searchBtn  = document.getElementById('map-search-btn');

  // Manage
  const manageBtn  = document.getElementById('manage-btn');

  // Modals (Bootstrap) — Block Info (anchored)
  const modalElement   = document.getElementById('blockInfoModal');
  const modalDialog    = modalElement ? modalElement.querySelector('.modal-dialog') : null;
  const modalBody      = document.getElementById('blockInfoModalBody');
  const modalHeader    = modalElement ? modalElement.querySelector('.modal-header') : null;
  const modalTitle     = document.getElementById('blockInfoModalLabel');
  let modalInstance    = modalElement ? new bootstrap.Modal(modalElement, { backdrop: true, focus: true, keyboard: true }) : null;

  // Pointer arrow for anchored modal
  let pointerEl = modalDialog ? modalDialog.querySelector('.modal-pointer') : null;
  if (modalDialog && !pointerEl) {
    pointerEl = document.createElement('div');
    pointerEl.className = 'modal-pointer';
    pointerEl.setAttribute('data-popper-arrow', '');
    modalDialog.appendChild(pointerEl);
  }

  // Manage Modal
  const manageModalElement = document.getElementById('manageModal');
  const manageModalInstance= manageModalElement ? new bootstrap.Modal(manageModalElement) : null;
  const manageModalForm    = document.getElementById('manageForm');

  // Burial Details Modal
  const burialDetailsModalElement = document.getElementById('burialDetailsModal');

  // ---- State ----
  const blockElements = new Map(); // key -> overlay div
  let activeBlock   = null;
  let allPlotsData  = [];
  let currentPage   = 1;
  let plotsPerPage  = 24;
  let closing       = false;
  const URLROOT     = window.URLROOT || '';

  // Last click (viewport coords) for anchored modal placement
  let clickPoint    = { x: window.innerWidth / 2, y: window.innerHeight / 2 };

  // ---- Helpers ----
  const debounce = (fn, ms = 60) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
  const clamp   = (v, min, max) => Math.max(min, Math.min(max, v));
  const norm    = coords => { const c = coords.split(',').map(Number); return [Math.min(c[0], c[2]), Math.min(c[1], c[3]), Math.max(c[0], c[2]), Math.max(c[1], c[3])]; };
  const val     = (x) => (x === null || x === undefined || x === '' ? '—' : x);

  // ---- Build map areas from config (window.CEMAP_BLOCKS) ----
  function buildAreasFromConfig() {
    if (!window.CEMAP_BLOCKS || !mapEl || mapEl.children.length > 0) return;
    window.CEMAP_BLOCKS.forEach(block => {
      const a = document.createElement('area');
      a.shape           = 'rect';
      a.dataset.key     = block.block_key;
      a.title           = block.title;
      a.dataset.orig    = norm(block.coords).join(',');
      a.dataset.offsetX = block.offset_x || 0;
      a.dataset.offsetY = block.offset_y || 0;
      a.dataset.id      = block.id;
      mapEl.appendChild(a);
    });
  }

  // ---- Draw overlay boxes for each area ----
  function drawOverlays() {
    if (!img.complete || !img.naturalWidth || !mapEl || !layer) return;
    const scale = img.offsetWidth / img.naturalWidth;

    const frag = document.createDocumentFragment();
    mapEl.querySelectorAll('area[data-orig]').forEach(a => {
      const [l, t, r, b] = a.dataset.orig.split(',').map(Number);
      const dx = Number(a.dataset.offsetX), dy = Number(a.dataset.offsetY);
      const left = (l * scale) + dx, top = (t * scale) + dy, width = (r - l) * scale, height = (b - t) * scale;

      const box = document.createElement('div');
      box.className   = 'hotspot';
      box.dataset.key = a.dataset.key;
      box.style.cssText = `left:${left}px;top:${top}px;width:${width}px;height:${height}px;border-width:${hlSlider?.value ?? 2}px;`;

      box.addEventListener('click', (ev) => handleBlockClick(a.dataset.key, ev));
      box.addEventListener('mouseenter', (e) => showTooltip(e, a.title));
      box.addEventListener('mouseleave', hideTooltip);
      box.addEventListener('mousemove', moveTooltip);

      frag.appendChild(box);
      blockElements.set(a.dataset.key, box);
    });

    layer.innerHTML = '';
    layer.appendChild(frag);
    layer.classList.toggle('hidden', !toggleHL?.checked);
  }
  const drawAll = debounce(drawOverlays);

  // ---- Anchored modal positioning ----
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

  function positionModal({ hideDuring = false } = {}) {
    if (!modalElement || !modalDialog) return;
    modalDialog.classList.add('modal-anchored');
    const { innerWidth: vw, innerHeight: vh } = window;
    const { width: mw, height: mh } = measureDialog();
    const gap = 14;

    const x = clickPoint.x ?? vw / 2;
    const y = clickPoint.y ?? vh / 2;

    const placement = choosePlacement(x, y);
    modalDialog.setAttribute('data-popper-placement', placement);

    let left = 0, top = 0;

    if (placement === 'bottom') {
      left = clamp(x - mw / 2, 8, vw - mw - 8);
      top  = Math.min(y + gap, vh - mh - 8);
      if (pointerEl) { pointerEl.style.left = clamp(x - left, 10, mw - 10) + 'px'; pointerEl.style.top = ''; }
    } else if (placement === 'top') {
      left = clamp(x - mw / 2, 8, vw - mw - 8);
      top  = Math.max(y - gap - mh, 8);
      if (pointerEl) { pointerEl.style.left = clamp(x - left, 10, mw - 10) + 'px'; pointerEl.style.top = ''; }
    } else if (placement === 'right') {
      left = Math.min(x + gap, vw - mw - 8);
      top  = clamp(y - mh / 2, 8, vh - mh - 8);
      if (pointerEl) { pointerEl.style.top = clamp(y - top, 10, mh - 10) + 'px'; pointerEl.style.left = ''; }
    } else {
      left = Math.max(x - gap - mw, 8);
      top  = clamp(y - mh / 2, 8, vh - mh - 8);
      if (pointerEl) { pointerEl.style.top = clamp(y - top, 10, mh - 10) + 'px'; pointerEl.style.left = ''; }
    }

    if (hideDuring) {
      modalDialog.style.opacity = '0';
      modalDialog.style.visibility = 'hidden';
    }

    modalDialog.style.left = `${left}px`;
    modalDialog.style.top  = `${top}px`;

    if (hideDuring) {
      requestAnimationFrame(() => {
        modalDialog.style.visibility = '';
        modalDialog.style.opacity = '';
      });
    }
  }

  const reanchor = debounce(() => {
    if (modalElement && modalElement.classList.contains('show')) positionModal({ hideDuring: true });
  }, 40);

  // ---- Block click -> open anchored modal + load data ----
  async function handleBlockClick(blockKey, ev) {
    clickPoint = { x: ev.clientX, y: ev.clientY };

    document.querySelectorAll('.hotspot.active').forEach(h => h.classList.remove('active'));
    const el = blockElements.get(blockKey);
    if (el && toggleHL?.checked) el.classList.add('active');

    const areaEl = mapEl ? mapEl.querySelector(`area[data-key="${blockKey}"]`) : null;
    activeBlock = areaEl ? {
      id: areaEl.dataset.id,
      key: blockKey,
      title: areaEl.title,
      offsetX: parseInt(areaEl.dataset.offsetX, 10) || 0,
      offsetY: parseInt(areaEl.dataset.offsetY, 10) || 0,
      element: el || null
    } : null;

    updateCalUI();
    hidePin();

    if (modalElement && modalInstance && modalDialog) {
      if (!modalElement.classList.contains('show')) {
        positionModal({ hideDuring: true });
        modalDialog.style.opacity = '0';
        modalElement.addEventListener('shown.bs.modal', () => {
          positionModal();
          modalDialog.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 160, easing: 'ease', fill: 'forwards' });
        }, { once: true });
        modalInstance.show();
      } else {
        positionModal({ hideDuring: true });
      }
    }

    await openBlockData(blockKey);
  }

  async function openBlockData(blockKey) {
    if (!modalBody) return;
    modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"></div></div>';
    currentPage = 1;

    const res  = await fetch(`${URLROOT}/maps/getBlockDetails/${encodeURIComponent(blockKey)}`);
    const data = await res.json();

    if (modalTitle) modalTitle.textContent = data.title || 'Block Details';

    if (!data.plots || data.plots.length === 0) {
      const ctrls = modalHeader?.querySelector('.modal-header-controls');
      if (ctrls) ctrls.remove();
      modalBody.innerHTML = '<div class="alert alert-info m-0">No plot layout. Go to <strong>Manage Blocks</strong> to configure.</div>';
      requestAnimationFrame(() => reanchor());
    } else {
      allPlotsData = data.plots;
      renderCurrentPage(data);
    }
  }

  // ---- Render grid inside anchored modal ----
  function renderCurrentPage(data, filtered = null) {
    if (!modalBody) return;

    const arr  = filtered ?? allPlotsData;
    const cols = window.innerWidth < 768 ? 3 : (data.modal_cols || 8);
    const rows = data.modal_rows || 4;
    plotsPerPage = rows * cols;

    const total = Math.max(1, Math.ceil((arr.length || 0) / plotsPerPage));
    currentPage = clamp(currentPage, 1, total);
    const start = (currentPage - 1) * plotsPerPage;
    const page  = arr.slice(start, start + plotsPerPage);

    let html = `<div class="plot-grid" style="--cols:${cols};">`;
    if (page.length) {
      page.forEach(p => {
        const fullName   = (p.deceased_first_name && p.deceased_last_name) ? `${p.deceased_first_name} ${p.deceased_last_name}` : '';
        const statusText = p.status ? (p.status.charAt(0).toUpperCase() + p.status.slice(1)) : 'Unknown';
        const plotContent= p.status === 'vacant' ? 'Available' : (fullName || statusText);
        const plotTooltip= p.status !== 'vacant' ? `Plot #${p.plot_number}: ${fullName || statusText}` : `Plot #${p.plot_number}: ${statusText}`;

        // Clickable ONLY if non-vacant AND has burial_id
        const clickHandler = (p.status !== 'vacant' && p.burial_id)
          ? `data-bs-toggle="modal" data-bs-target="#burialDetailsModal" data-burial-id="${p.burial_id}"`
          : '';

        html += `
          <div class="plot-cell ${p.status}" data-plot-number="${p.plot_number}" data-plot-id="${p.id}" title="${plotTooltip}" ${clickHandler}>
            <span class="plot-number">${p.plot_number}</span>
            <span class="plot-name">${plotContent}</span>
          </div>`;
      });
    } else {
      html += `<div class="alert alert-warning" style="grid-column:1/-1">No plots found.</div>`;
    }
    html += `</div>`;

    // Header controls (search + pagination)
    let ctrls = modalHeader?.querySelector('.modal-header-controls');
    const totalPages = total;
    if (modalHeader) {
      if (!ctrls) {
        ctrls = document.createElement('div');
        ctrls.className = 'modal-header-controls';
        ctrls.innerHTML = `
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="search" id="modal-plot-search" class="form-control" placeholder="Search Plot #">
          </div>
          <div class="pagination-controls">
            <button class="btn btn-secondary btn-sm" id="prev-page-btn" ${currentPage === 1 ? 'disabled' : ''}>Prev</button>
            <span class="page-info">Page ${currentPage} of ${totalPages}</span>
            <button class="btn btn-secondary btn-sm" id="next-page-btn" ${currentPage === totalPages || arr.length === 0 ? 'disabled' : ''}>Next</button>
          </div>`;
        modalHeader.insertBefore(ctrls, modalTitle ? modalTitle.nextSibling : null);

        document.getElementById('prev-page-btn')?.addEventListener('click', () => {
          if (currentPage > 1) { currentPage--; renderCurrentPage(data, filtered); }
        });
        document.getElementById('next-page-btn')?.addEventListener('click', () => {
          if (currentPage < totalPages) { currentPage++; renderCurrentPage(data, filtered); }
        });
        document.getElementById('modal-plot-search')?.addEventListener('input', debounce((e) => {
          const q = e.target.value.trim().toLowerCase();
          const filtered2 = !q ? null : allPlotsData.filter(p => (p.plot_number || '').toLowerCase().startsWith(q));
          currentPage = 1;
          renderCurrentPage(data, filtered2);
        }));
      } else {
        ctrls.querySelector('.page-info').textContent = `Page ${currentPage} of ${totalPages}`;
        ctrls.querySelector('#prev-page-btn').disabled = currentPage === 1;
        ctrls.querySelector('#next-page-btn').disabled = (currentPage === totalPages || arr.length === 0);
      }
    }

    modalBody.innerHTML = html;
    requestAnimationFrame(() => reanchor());
  }

  // ---- Anchored modal close (custom fade-out to prevent hide() null issues) ----
  if (modalElement && modalDialog && modalInstance) {
    modalElement.addEventListener('hide.bs.modal', (ev) => {
      if (closing) return;
      ev.preventDefault();
      closing = true;

      const anim = modalDialog.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 160, easing: 'ease', fill: 'forwards' });
      const finalize = () => {
        try {
          modalInstance.hide(); // ensure bootstrap state resets
        } finally {
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

    modalElement.addEventListener('hidden.bs.modal', () => {
      modalDialog.classList.remove('modal-anchored');
      modalDialog.style.opacity = '';
      modalDialog.removeAttribute('data-popper-placement');
      closing = false;
    });
  }

  // ---- Tooltip helpers ----
  function showTooltip(e, text) { if (!tooltip || !wrap) return; tooltip.textContent = text; tooltip.style.display = 'block'; moveTooltip(e); }
  function hideTooltip() { if (!tooltip) return; tooltip.style.display = 'none'; }
  function moveTooltip(e) {
    if (!wrap || !tooltip) return;
    const r = wrap.getBoundingClientRect();
    tooltip.style.left = `${e.clientX - r.left + 15}px`;
    tooltip.style.top  = `${e.clientY - r.top}px`;
  }

  // ---- Search helpers ----
  function handleSearch() {
    const q = searchInput?.value?.trim().toLowerCase();
    if (!q || !window.CEMAP_BLOCKS) return;
    const f = window.CEMAP_BLOCKS.find(b => (b.title || '').toLowerCase().includes(q));
    if (f) {
      const el = blockElements.get(f.block_key);
      if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); showPin(el, f.title); }
    } else {
      alert('Block not found!');
      hidePin();
    }
  }
  function showPin(target, title) {
    if (!pin || !wrap) return;
    const bubble = pin.querySelector('.pin-bubble');
    const tr = target.getBoundingClientRect(), wr = wrap.getBoundingClientRect();
    const x = (tr.left - wr.left) + tr.width / 2;
    const y = (tr.top  - wr.top ) + tr.height / 2;
    pin.style.left = `${x}px`; pin.style.top = `${y}px`;
    if (bubble) bubble.textContent = title;
    pin.style.display = 'flex';
  }
  function hidePin() { if (pin) pin.style.display = 'none'; }

  // ---- Calibration UI ----
  function updateCalUI() {
    if (!activeBlock) return;
    if (calxVal) calxVal.textContent = activeBlock.offsetX;
    if (calyVal) calyVal.textContent = activeBlock.offsetY;
    if (hlVal && hlSlider) hlVal.textContent = `${hlSlider.value}px`;
    if (toggleLabel && toggleHL) toggleLabel.textContent = toggleHL.checked ? '(On)' : '(Off)';
  }

  function handleCal(e) {
    const btn = e.target.closest?.('[data-cal]');
    if (!btn || !activeBlock) return;
    const t = btn.dataset.cal;
    if (t === 'x-') activeBlock.offsetX--;
    if (t === 'x+') activeBlock.offsetX++;
    if (t === 'y-') activeBlock.offsetY--;
    if (t === 'y+') activeBlock.offsetY++;

    updateCalUI();

    const scale = img.offsetWidth / img.naturalWidth;
    const areaEl = mapEl ? mapEl.querySelector(`area[data-key="${activeBlock.key}"]`) : null;
    if (areaEl && activeBlock.element) {
      const [l, t0] = areaEl.dataset.orig.split(',').map(Number);
      activeBlock.element.style.left = `${(l * scale) + activeBlock.offsetX}px`;
      activeBlock.element.style.top  = `${(t0 * scale) + activeBlock.offsetY}px`;
    }
    reanchor();
  }

  async function saveCalibration() {
    if (!activeBlock) {
      Swal.fire({ icon: 'info', title: 'No Block Selected', text: 'Please select a block on the map before saving.' });
      return;
    }
    const { id, offsetX, offsetY } = activeBlock;
    const r = await fetch(`${URLROOT}/maps/saveCalibration`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ block_id: id, offset_x: offsetX, offset_y: offsetY })
    });
    const j = await r.json();
    if (j.success) {
      const areaEl = mapEl ? mapEl.querySelector(`area[data-key="${activeBlock.key}"]`) : null;
      if (areaEl) { areaEl.dataset.offsetX = offsetX; areaEl.dataset.offsetY = offsetY; }
      Swal.fire({ icon: 'success', title: 'Calibration Saved!', showConfirmButton: false, timer: 2000 });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: j.message || 'Failed to save calibration.' });
    }
  }

  // ---- Manage Modal ----
  if (manageBtn && manageModalInstance && manageModalForm) {
    manageBtn.addEventListener('click', () => {
      if (!activeBlock) {
        Swal.fire({ icon: 'info', title: 'No Block Selected', text: 'Please select a block on the map before managing its details.' });
        return;
      }
      manageModalForm.querySelector('#manage-block-id').value = activeBlock.id;
      manageModalForm.querySelector('#manage-title').value    = activeBlock.title;

      const blockData = window.CEMAP_BLOCKS?.find?.(b => String(b.id) === String(activeBlock.id));
      if (blockData) {
        manageModalForm.querySelector('#manage-rows').value = blockData.modal_rows || 4;
        manageModalForm.querySelector('#manage-cols').value = blockData.modal_cols || 8;
      }
      manageModalInstance.show();
    });

    manageModalForm.addEventListener('submit', function (e) {
      e.preventDefault();
      Swal.fire({
        title: 'Saving changes...',
        html: 'Please wait while we update the plots.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
      });

      const formData = new FormData(this);
      fetch(`${URLROOT}/admin/updateBlock`, { method: 'POST', body: formData })
        .then(response => {
          if (response.redirected) {
            window.location.href = response.url;
          } else {
            Swal.fire({ icon: 'error', title: 'Error!', text: 'An unexpected error occurred. Please try again.' });
          }
        })
        .catch(error => {
          Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to the server.' });
          console.error('Form submission failed:', error);
        });
    });
  }

  // ---- Burial Details Modal (null-safe + guard) ----
  if (burialDetailsModalElement) {
    burialDetailsModalElement.addEventListener('show.bs.modal', async function (event) {
      const button   = event?.relatedTarget || null;
      const burialId = button ? button.getAttribute('data-burial-id') : null;

      // Guard: if no burialId, cancel opening
      if (!burialId) {
        event.preventDefault();
        return;
      }

      const modalBody  = burialDetailsModalElement.querySelector('#burialDetailsModalBody');
      const modalTitle = burialDetailsModalElement.querySelector('#burialDetailsModalLabel');

      modalBody.innerHTML = `
        <div class="text-center p-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>`;
      if (modalTitle) modalTitle.textContent = `Burial Details for ${val(burialId)}`;

      try {
        const res  = await fetch(`${URLROOT}/admin/getBurialDetails/${encodeURIComponent(burialId)}`);
        const data = await res.json();

        if (data && !data.error) {
          const d = {
            deceased_first_name:        val(data.deceased_first_name),
            deceased_last_name:         val(data.deceased_last_name),
            plot_number:                val(data.plot_number),
            burial_id:                  val(data.burial_id),
            age:                        val(data.age),
            sex:                        val(data.sex),
            date_born:                  val(data.date_born),
            date_died:                  val(data.date_died),
            cause_of_death:             val(data.cause_of_death),
            interment_full_name:        val(data.interment_full_name),
            interment_relationship:     val(data.interment_relationship),
            interment_contact_number:   val(data.interment_contact_number),
            interment_address:          val(data.interment_address),
            rental_date:                val(data.rental_date),
            expiry_date:                val(data.expiry_date),
          };

          modalBody.innerHTML = `
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
        } else {
          modalBody.innerHTML = `<div class="alert alert-danger m-0">Error: Burial details not found.</div>`;
        }
      } catch (error) {
        console.error('Error fetching burial details:', error);
        modalBody.innerHTML = `<div class="alert alert-danger m-0">Failed to load details. Please try again.</div>`;
      }
    });
  }

  // ---- Dev/Calib hooks ----
  kebab?.addEventListener('click', () => menu?.removeAttribute('hidden'));
  menuClose?.addEventListener('click', () => menu?.setAttribute('hidden', true));
  menu?.addEventListener('click', handleCal);

  resetBtn?.addEventListener('click', () => {
    if (!activeBlock) return;
    activeBlock.offsetX = 0; activeBlock.offsetY = 0;
    if (hlSlider) hlSlider.value = 2;
    updateCalUI(); drawAll(); reanchor();
  });
  saveBtn?.addEventListener('click', saveCalibration);

  hlSlider?.addEventListener('input', () => {
    if (activeBlock?.element) activeBlock.element.style.borderWidth = `${hlSlider.value}px`;
    if (hlVal) hlVal.textContent = `${hlSlider.value}px`;
  });
  toggleHL?.addEventListener('change', () => { if (layer) layer.classList.toggle('hidden', !toggleHL.checked); updateCalUI(); });

  searchBtn?.addEventListener('click', handleSearch);
  searchInput?.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); handleSearch(); } });

  // ---- Re-anchoring on window changes ----
  window.addEventListener('resize', reanchor, { passive: true });
  window.addEventListener('scroll', reanchor, { passive: true });

  // Redraw overlays on resize (scales with image)
  window.addEventListener('resize', drawAll);

  // ---- Init ----
  function init() {
    buildAreasFromConfig();
    drawAll();
    if (toggleHL && layer) { toggleHL.checked = false; layer.classList.add('hidden'); }
    updateCalUI();
  }
  if (img.complete) { init(); } else { img.addEventListener('load', init); }

})();
