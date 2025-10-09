/*
 * public/js/staff_map.js
 * [STAFF VERSION - CORRECTED] - Based on Admin Final Version 6.
 * Contains occupant counts, detailed info, and highlight toggle, without admin controls.
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
    const tooltip = document.getElementById('map-tooltip');
    const kebab = document.getElementById('kebab');
    const menu = document.getElementById('dev-menu');
    const menuClose = document.getElementById('menu-close');
    const pin = document.getElementById('pin');
    const searchInput = document.getElementById('map-search-input');
    const searchBtn = document.getElementById('map-search-btn');
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
    
    const occupantsModalEl = document.getElementById('burialDetailsModal');
    const occupantsModal = occupantsModalEl ? new bootstrap.Modal(occupantsModalEl) : null;
    const occupantsModalBody = document.getElementById('burialDetailsModalBody');
    const occupantsModalLabel = document.getElementById('burialDetailsModalLabel');

    // ---- State ----
    const blockElements = new Map();
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
            box.style.cssText = `left:${left}px;top:${top}px;width:${width}px;height:${height}px;border-width:2px;`;
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
            modalBody.innerHTML = '<div class="alert alert-info m-0">No plot layout for this block.</div>';
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
                    const countBadge = occupantCount > 0 ? `<span class="occupant-count">${occupantCount}</span>` : '';

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
        
        const res = await fetch(`${URLROOT}/staff/getOccupantsForPlot/${plotId}`);
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
    
    kebab?.addEventListener('click', () => menu?.removeAttribute('hidden'));
    menuClose?.addEventListener('click', () => menu?.setAttribute('hidden', true));
    toggleHL?.addEventListener('change', () => { 
        if (layer) layer.classList.toggle('hidden', !toggleHL.checked);
        const toggleLabel = document.querySelector('label[for="toggle-highlights"] span');
        if (toggleLabel) toggleLabel.textContent = toggleHL.checked ? '(On)' : '(Off)';
    });
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
            const toggleLabel = document.querySelector('label[for="toggle-highlights"] span');
            if (toggleLabel) toggleLabel.textContent = '(Off)';
        }
        drawAll();
    }

    if (img.complete) { 
        init(); 
    } else { 
        img.addEventListener('load', init); 
    }
})();