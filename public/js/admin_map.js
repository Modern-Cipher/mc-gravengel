/*
 * public/js/admin_map.js
 * [FINAL VERSION 6] - Added occupant count badge and detailed IHR info.
 */
(function () {
    // Required root image; if missing, do nothing
    const img = document.getElementById('map-img');
    if (!img) return;

    // ---- Element refs ----
    const wrap = document.getElementById('img-wrap');
    const mapEl = document.getElementById('image-map');
    const layer = document.getElementById('hotspots');
    const toggleHL = document.getElementById('toggle-highlights');
    const toggleLabel = document.querySelector('label[for="toggle-highlights"] span');
    const tooltip = document.getElementById('map-tooltip');
    const kebab = document.getElementById('kebab');
    const menu = document.getElementById('dev-menu');
    const menuClose = document.getElementById('menu-close');
    const calxVal = document.getElementById('calx-val');
    const calyVal = document.getElementById('caly-val');
    const hlSlider = document.getElementById('hl-slider');
    const hlVal = document.getElementById('hl-val');
    const resetBtn = document.getElementById('reset-cal');
    const saveBtn = document.getElementById('save-cal');
    const pin = document.getElementById('pin');
    const searchInput = document.getElementById('map-search-input');
    const searchBtn = document.getElementById('map-search-btn');
    const manageBtn = document.getElementById('manage-btn');
    const modalElement = document.getElementById('blockInfoModal');
    const modalDialog = modalElement ? modalElement.querySelector('.modal-dialog') : null;
    const modalBody = document.getElementById('blockInfoModalBody');
    const modalHeader = modalElement ? modalElement.querySelector('.modal-header') : null;
    const modalTitle = document.getElementById('blockInfoModalLabel');
    let modalInstance = modalElement ? new bootstrap.Modal(modalElement, { backdrop: true, focus: true, keyboard: true }) : null;
    let pointerEl = modalDialog ? modalDialog.querySelector('.modal-pointer') : null;
    if (modalDialog && !pointerEl) {
        pointerEl = document.createElement('div');
        pointerEl.className = 'modal-pointer';
        pointerEl.setAttribute('data-popper-arrow', '');
        modalDialog.appendChild(pointerEl);
    }
    const manageModalElement = document.getElementById('manageModal');
    const manageModalInstance = manageModalElement ? new bootstrap.Modal(manageModalElement) : null;
    const manageModalForm = document.getElementById('manageForm');
    
    const occupantsModalEl = document.getElementById('burialDetailsModal');
    const occupantsModal = occupantsModalEl ? new bootstrap.Modal(occupantsModalEl) : null;
    const occupantsModalBody = document.getElementById('burialDetailsModalBody');
    const occupantsModalLabel = document.getElementById('burialDetailsModalLabel');

    // ---- State ----
    const blockElements = new Map();
    let activeBlock = null;
    let allPlotsData = [];
    let closing = false;
    const URLROOT = window.URLROOT || '';
    let clickPoint = { x: window.innerWidth / 2, y: window.innerHeight / 2 };

    // ---- Helpers ----
    const debounce = (fn, ms = 60) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
    const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
    const norm = coords => { const c = coords.split(',').map(Number); return [Math.min(c[0], c[2]), Math.min(c[1], c[3]), Math.max(c[0], c[2]), Math.max(c[1], c[3])]; };
    
    function buildAreasFromConfig() {
        if (!window.CEMAP_BLOCKS || !mapEl || mapEl.children.length > 0) return;
        window.CEMAP_BLOCKS.forEach(block => {
            const a = document.createElement('area');
            a.shape = 'rect'; a.dataset.key = block.block_key; a.title = block.title;
            a.dataset.orig = norm(block.coords).join(',');
            a.dataset.offsetX = block.offset_x || 0; a.dataset.offsetY = block.offset_y || 0;
            a.dataset.id = block.id; mapEl.appendChild(a);
        });
    }

    function drawOverlays() {
        if (!img.complete || !img.naturalWidth || !mapEl || !layer) return;
        const scale = img.offsetWidth / img.naturalWidth;
        const frag = document.createDocumentFragment();
        mapEl.querySelectorAll('area[data-orig]').forEach(a => {
            const [l, t, r, b] = a.dataset.orig.split(',').map(Number);
            const dx = Number(a.dataset.offsetX), dy = Number(a.dataset.offsetY);
            const left = (l * scale) + dx, top = (t * scale) + dy, width = (r - l) * scale, height = (b - t) * scale;
            const box = document.createElement('div');
            box.className = 'hotspot'; box.dataset.key = a.dataset.key;
            box.style.cssText = `left:${left}px;top:${top}px;width:${width}px;height:${height}px;border-width:${hlSlider?.value ?? 2}px;`;
            box.addEventListener('click', (ev) => handleBlockClick(a.dataset.key, ev));
            box.addEventListener('mouseenter', (e) => showTooltip(e, a.title));
            box.addEventListener('mouseleave', hideTooltip);
            box.addEventListener('mousemove', moveTooltip);
            frag.appendChild(box);
            blockElements.set(a.dataset.key, box);
        });
        layer.innerHTML = ''; layer.appendChild(frag);
        layer.classList.toggle('hidden', !toggleHL?.checked);
    }
    const drawAll = debounce(drawOverlays);

    function measureDialog(){if(!modalDialog)return{width:480,height:320};const e=modalDialog.cloneNode(!0);return e.style.cssText="position:fixed; left:-9999px; top:-9999px; visibility:hidden; pointer-events:none;",e.classList.add("modal-anchored"),document.body.appendChild(e),t=e.getBoundingClientRect(),document.body.removeChild(e),{width:Math.max(320,t.width||480),height:Math.max(220,t.height||320)};var t}
    function choosePlacement(e,t){const o=window,{innerWidth:n,innerHeight:i}=o,{width:d,height:l}=measureDialog(),a=16,s=i-t-a,r=t-a,c=n-e-a,p=e-a;return s>=l?"bottom":r>=l?"top":c>=d?"right":p>=d?"left":[["bottom",s],["top",r],["right",c],["left",p]].sort(((e,t)=>t[1]-e[1]))[0][0]}
    function positionModal({hideDuring:e=!1}={}){if(modalElement&&modalDialog){modalDialog.classList.add("modal-anchored");const t=window,{innerWidth:o,innerHeight:n}=t,{width:i,height:d}=measureDialog(),l=14,a=clickPoint.x??o/2,s=clickPoint.y??n/2,r=choosePlacement(a,s);modalDialog.setAttribute("data-popper-placement",r);let c=0,p=0;"bottom"===r?(c=clamp(a-i/2,8,o-i-8),p=Math.min(s+l,n-d-8),pointerEl&&(pointerEl.style.left=clamp(a-c,10,i-10)+"px",pointerEl.style.top="")):"top"===r?(c=clamp(a-i/2,8,o-i-8),p=Math.max(s-l-d,8),pointerEl&&(pointerEl.style.left=clamp(a-c,10,i-10)+"px",pointerEl.style.top="")):"right"===r?(c=Math.min(a+l,o-i-8),p=clamp(s-d/2,8,n-d-8),pointerEl&&(pointerEl.style.top=clamp(s-p,10,d-10)+"px",pointerEl.style.left="")):(c=Math.max(a-l-i,8),p=clamp(s-d/2,8,n-d-8),pointerEl&&(pointerEl.style.top=clamp(s-p,10,d-10)+"px",pointerEl.style.left="")),e&&(modalDialog.style.opacity="0",modalDialog.style.visibility="hidden"),modalDialog.style.left=`${c}px`,modalDialog.style.top=`${p}px`,e&&requestAnimationFrame((()=>{modalDialog.style.visibility="",modalDialog.style.opacity=""}))}}
    const reanchor=debounce((()=>{modalElement&&modalElement.classList.contains("show")&&positionModal({hideDuring:!0})}),40);

    async function handleBlockClick(blockKey, ev) {
        clickPoint = { x: ev.clientX, y: ev.clientY };
        document.querySelectorAll('.hotspot.active').forEach(h => h.classList.remove('active'));
        const el = blockElements.get(blockKey);
        if (el && toggleHL?.checked) el.classList.add('active');
        const areaEl = mapEl ? mapEl.querySelector(`area[data-key="${blockKey}"]`) : null;
        activeBlock = areaEl ? { id: areaEl.dataset.id, key: blockKey, title: areaEl.title, offsetX: parseInt(areaEl.dataset.offsetX, 10) || 0, offsetY: parseInt(areaEl.dataset.offsetY, 10) || 0, element: el || null } : null;
        updateCalUI(); hidePin();
        if (modalElement && modalInstance && modalDialog) {
            if (!modalElement.classList.contains('show')) {
                positionModal({ hideDuring: true }); modalDialog.style.opacity = '0';
                modalElement.addEventListener('shown.bs.modal', () => {
                    positionModal();
                    modalDialog.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 160, easing: 'ease', fill: 'forwards' });
                }, { once: true });
                modalInstance.show();
            } else { positionModal({ hideDuring: true }); }
        }
        await openBlockData(blockKey);
    }

    async function openBlockData(blockKey) {
        if (!modalBody) return;
        modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"></div></div>';
        const res = await fetch(`${URLROOT}/maps/getBlockDetails/${encodeURIComponent(blockKey)}`);
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
    
    function renderCurrentPage(data, filtered = null) {
        if (!modalBody) return;

        const sourcePlots = filtered ?? allPlotsData;
        const actualCols = data.modal_cols || 8;
        const graveLevels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        const chunks = [];
        if (sourcePlots.length > 0) {
            for (let i = 0; i < sourcePlots.length; i += actualCols) {
                chunks.push(sourcePlots.slice(i, i + actualCols));
            }
        }
        
        const reversedChunks = [...chunks].reverse();
        const totalRows = reversedChunks.length;
        
        let html = `<div class="plot-grid" style="--cols:${actualCols};">`;
        if (reversedChunks.length > 0) {
            reversedChunks.forEach((row, rowIndex) => {
                const graveLevel = graveLevels[totalRows - 1 - rowIndex] || 'N/A';
                row.forEach(p => {
                    const plotNumber = p.plot_number || 'Unknown Plot';
                    const graveInfo = `Grave Level: ${graveLevel}`;
                    const statusText = p.status.charAt(0).toUpperCase() + p.status.slice(1);
                    const clickHandler = p.status !== 'vacant' ? `onclick="window.handlePlotClick(${p.id}, '${p.plot_number}', '${graveLevel}')"` : '';

                    const occupantCount = parseInt(p.occupant_count, 10) || 0;
                    const countBadge = occupantCount > 0 
                        ? `<span class="occupant-count">${occupantCount}</span>` 
                        : '';

                    html += `<div class="plot-cell ${p.status}" data-plot-id="${p.id}" ${clickHandler}>
                                 ${countBadge}
                                 <span class="plot-number">${plotNumber}</span>
                                 <span class="plot-details">${graveInfo}</span>
                                 <span class="plot-status">${statusText}</span>
                             </div>`;
                });
            });
        } else {
            html += `<div class="alert alert-warning" style="grid-column:1/-1">No plots found for this search.</div>`;
        }
        html += `</div>`;
        
        let ctrls = modalHeader?.querySelector('.modal-header-controls');
        if (modalHeader && !ctrls) {
            ctrls = document.createElement('div');
            ctrls.className = 'modal-header-controls';
            ctrls.innerHTML = `<div class="input-group input-group-sm"><span class="input-group-text"><i class="fas fa-search"></i></span><input type="search" id="modal-plot-search" class="form-control" placeholder="Search plot or status..."></div>`;
            modalHeader.insertBefore(ctrls, modalTitle.nextSibling);
            
            document.getElementById('modal-plot-search')?.addEventListener('input', debounce((e) => {
                const q = e.target.value.trim().toLowerCase();
                const filteredPlots = !q ? null : allPlotsData.filter(p => {
                    const plotNum = (p.plot_number || '').toLowerCase();
                    const status = (p.status || '').toLowerCase();
                    return plotNum.includes(q) || status.includes(q);
                });
                renderCurrentPage(data, filteredPlots);
            }, 200));
        }
        
        modalBody.innerHTML = html;
        requestAnimationFrame(() => reanchor());
    }

    window.handlePlotClick = async (plotId, plotNumber, graveLevel) => {
        if (!plotId || !occupantsModal) return;
        
        modalInstance.hide();
        occupantsModal.show();
        occupantsModalLabel.textContent = `Loading occupants for ${plotNumber}...`;
        occupantsModalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"></div></div>';
        
        const res = await fetch(`${URLROOT}/admin/getOccupantsForPlot/${plotId}`);
        const data = await res.json();

        if (!data.ok || !data.occupants || data.occupants.length === 0) {
            occupantsModalLabel.textContent = `Plot ${plotNumber}`;
            occupantsModalBody.innerHTML = '<p class="text-muted text-center p-3">This plot has no active burial records.</p>';
            return;
        }

        const occupants = data.occupants;
        const graveType = occupants.find(o => o.parent_burial_id === null)?.grave_type || occupants[0]?.grave_type || 'N/A';
        
        let title = `${plotNumber}:`;
        if (graveLevel && graveLevel !== 'N/A') title += ` ${graveLevel}`;
        if (graveType && graveType !== 'N/A') title += ` - ${graveType}`;
        occupantsModalLabel.textContent = title;

        let listHtml = '<div class="list-group list-group-flush">';
        occupants.forEach(occ => {
            const fullName = `${occ.deceased_first_name || ''} ${occ.deceased_last_name || ''}`.trim();
            const expiryDate = occ.expiry_date ? new Date(occ.expiry_date) : null;
            const expiryText = expiryDate ? `Expires ${expiryDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric'})}` : 'No Expiry';
            const ihrName = occ.interment_full_name || 'Not specified';
            const ihrContact = occ.interment_contact_number || 'N/A';
            const ihrEmail = occ.interment_email || 'N/A';

            listHtml += `
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${fullName}</h6>
                            <p class="mb-1">
                                <small class="text-muted">
                                    <strong>Burial ID:</strong> ${occ.burial_id || 'N/A'} | 
                                    <strong>Born:</strong> ${occ.date_born || 'N/A'} | 
                                    <strong>Died:</strong> ${occ.date_died || 'N/A'}
                                </small>
                            </p>
                            <small>
                                <strong>Contact Person:</strong> ${ihrName} <br>
                                <strong>Phone:</strong> ${ihrContact} | <strong>Email:</strong> ${ihrEmail}
                            </small>
                        </div>
                        <span class="badge bg-secondary rounded-pill ms-3">${expiryText}</span>
                    </div>
                </div>`;
        });
        listHtml += '</div>';
        occupantsModalBody.innerHTML = listHtml;
    };
    
    // ---- Event Listeners and Initialization ----
    if (modalElement && modalDialog && modalInstance) {
        modalElement.addEventListener('hide.bs.modal', (ev) => {
            if (closing) return; ev.preventDefault(); closing = true;
            const anim = modalDialog.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 160, easing: 'ease', fill: 'forwards' });
            const finalize = () => { try { modalInstance.hide(); } finally { setTimeout(() => { document.querySelectorAll('.modal-backdrop').forEach(el => el.remove()); document.body.classList.remove('modal-open'); document.body.style.removeProperty('overflow'); document.body.style.removeProperty('paddingRight'); closing = false; }, 0); } };
            anim.onfinish = finalize; setTimeout(() => { if (closing) finalize(); }, 220);
        });
        modalElement.addEventListener('hidden.bs.modal', () => { modalDialog.classList.remove('modal-anchored'); modalDialog.style.opacity = ''; modalDialog.removeAttribute('data-popper-placement'); closing = false; });
    }
    function showTooltip(e, text) { if (!tooltip || !wrap) return; tooltip.textContent = text; tooltip.style.display = 'block'; moveTooltip(e); }
    function hideTooltip() { if (!tooltip) return; tooltip.style.display = 'none'; }
    function moveTooltip(e) { if (!wrap || !tooltip) return; const r = wrap.getBoundingClientRect(); tooltip.style.left = `${e.clientX - r.left + 15}px`; tooltip.style.top = `${e.clientY - r.top}px`; }
    function handleSearch() { const q = searchInput?.value?.trim().toLowerCase(); if (!q || !window.CEMAP_BLOCKS) return; const f = window.CEMAP_BLOCKS.find(b => (b.title || '').toLowerCase().includes(q)); if (f) { const el = blockElements.get(f.block_key); if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); showPin(el, f.title); } } else { alert('Block not found!'); hidePin(); } }
    function showPin(target, title) { if (!pin || !wrap) return; const bubble = pin.querySelector('.pin-bubble'); const tr = target.getBoundingClientRect(), wr = wrap.getBoundingClientRect(); const x = (tr.left - wr.left) + tr.width / 2; const y = (tr.top - wr.top) + tr.height / 2; pin.style.left = `${x}px`; pin.style.top = `${y}px`; if (bubble) bubble.textContent = title; pin.style.display = 'flex'; }
    function hidePin() { if (pin) pin.style.display = 'none'; }
    function updateCalUI() { if (!activeBlock) return; if (calxVal) calxVal.textContent = activeBlock.offsetX; if (calyVal) calyVal.textContent = activeBlock.offsetY; if (hlVal && hlSlider) hlVal.textContent = `${hlSlider.value}px`; if (toggleLabel && toggleHL) toggleLabel.textContent = toggleHL.checked ? '(On)' : '(Off)'; }
    function handleCal(e) { const btn = e.target.closest?.('[data-cal]'); if (!btn || !activeBlock) return; const t = btn.dataset.cal; if (t === 'x-') activeBlock.offsetX--; if (t === 'x+') activeBlock.offsetX++; if (t === 'y-') activeBlock.offsetY--; if (t === 'y+') activeBlock.offsetY++; updateCalUI(); const scale = img.offsetWidth / img.naturalWidth; const areaEl = mapEl ? mapEl.querySelector(`area[data-key="${activeBlock.key}"]`) : null; if (areaEl && activeBlock.element) { const [l, t0] = areaEl.dataset.orig.split(',').map(Number); activeBlock.element.style.left = `${(l * scale) + activeBlock.offsetX}px`; activeBlock.element.style.top = `${(t0 * scale) + activeBlock.offsetY}px`; } reanchor(); }
    async function saveCalibration() { if (!activeBlock) { Swal.fire({ icon: 'info', title: 'No Block Selected', text: 'Please select a block on the map before saving.' }); return; } const { id, offsetX, offsetY } = activeBlock; const r = await fetch(`${URLROOT}/maps/saveCalibration`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ block_id: id, offset_x: offsetX, offset_y: offsetY }) }); const j = await r.json(); if (j.success) { const areaEl = mapEl?.querySelector(`area[data-key="${activeBlock.key}"]`); if (areaEl) { areaEl.dataset.offsetX = offsetX; areaEl.dataset.offsetY = offsetY; } Swal.fire({ icon: 'success', title: 'Calibration Saved!', showConfirmButton: false, timer: 2000 }); } else { Swal.fire({ icon: 'error', title: 'Error', text: j.message || 'Failed to save calibration.' }); } }
    if (manageBtn && manageModalInstance && manageModalForm) {
        manageBtn.addEventListener('click', () => {
            if (!activeBlock) {
                Swal.fire({ icon: 'info', title: 'No Block Selected', text: 'Please select a block on the map before managing its details.' });
                return;
            }
            manageModalForm.querySelector('#manage-block-id').value = activeBlock.id;
            manageModalForm.querySelector('#manage-offset-x').value = activeBlock.offsetX;
            manageModalForm.querySelector('#manage-offset-y').value = activeBlock.offsetY;
            manageModalForm.querySelector('#manage-title').value = activeBlock.title;
            const blockData = window.CEMAP_BLOCKS?.find?.(b => String(b.id) === String(activeBlock.id));
            if (blockData) {
                manageModalForm.querySelector('#manage-rows').value = blockData.modal_rows || 4;
                manageModalForm.querySelector('#manage-cols').value = blockData.modal_cols || 8;
            }
            manageModalInstance.show();
        });
        manageModalForm.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({ title: 'Saving changes...', html: 'Please wait...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading(); } });
            const formData = new FormData(this);
            fetch(this.action, { method: 'POST', body: formData }).then(response => {
                if (response.redirected || response.ok) {
                    window.location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'An unexpected error occurred. Please try again.' });
                }
            }).catch(error => {
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to the server.' });
                console.error('Form submission failed:', error);
            });
        });
    }
    
    kebab?.addEventListener('click', () => menu?.removeAttribute('hidden'));
    menuClose?.addEventListener('click', () => menu?.setAttribute('hidden', true));
    menu?.addEventListener('click', handleCal);
    resetBtn?.addEventListener('click', () => { if (!activeBlock) return; activeBlock.offsetX = 0; activeBlock.offsetY = 0; if (hlSlider) hlSlider.value = 2; updateCalUI(); drawAll(); reanchor(); });
    saveBtn?.addEventListener('click', saveCalibration);
    hlSlider?.addEventListener('input', () => { if (activeBlock?.element) activeBlock.element.style.borderWidth = `${hlSlider.value}px`; if (hlVal) hlVal.textContent = `${hlSlider.value}px`; });
    toggleHL?.addEventListener('change', () => { if (layer) layer.classList.toggle('hidden', !toggleHL.checked); updateCalUI(); });
    searchBtn?.addEventListener('click', handleSearch);
    searchInput?.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); handleSearch(); } });
    window.addEventListener('resize', reanchor, { passive: true });
    window.addEventListener('scroll', reanchor, { passive: true });
    window.addEventListener('resize', drawAll);

    function init() {
        buildAreasFromConfig();
        if (toggleHL && layer) { 
            toggleHL.checked = false;
            layer.classList.add('hidden'); 
        }
        drawAll();
        updateCalUI();
    }

    if (img.complete) { 
        init(); 
    } else { 
        img.addEventListener('load', init); 
    }

})();