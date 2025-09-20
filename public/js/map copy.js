(function () {
  if (!document.getElementById('map-img')) return;

  const img = document.getElementById('map-img');
  const wrap = document.getElementById('img-wrap');
  const mapEl = document.getElementById('image-map');
  const layer = document.getElementById('hotspots');
  const toggleHL = document.getElementById('toggle-highlights');
  const toggleLabel = document.querySelector('label[for="toggle-highlights"]');
  const kebab = document.getElementById('kebab');
  const menu = document.getElementById('dev-menu');
  const menuClose = document.getElementById('menu-close');
  const tooltip = document.getElementById('map-tooltip');
  
  const calxVal = document.getElementById('calx-val');
  const calyVal = document.getElementById('caly-val');
  const hlSlider = document.getElementById('hl-slider');
  const hlVal = document.getElementById('hl-val');
  const resetBtn = document.getElementById('reset-cal');
  const saveBtn = document.getElementById('save-cal');
  
  const pin = document.getElementById('pin');
  const pinBubble = pin?.querySelector('.pin-bubble');
  const searchInput = document.getElementById('map-search-input');
  const searchBtn = document.getElementById('map-search-btn');

  const modalInstance = new bootstrap.Modal(document.getElementById('blockInfoModal'));
  const modalBody = document.getElementById('blockInfoModalBody');
  const modalTitle = document.getElementById('blockInfoModalLabel');

  const blockElements = new Map();
  let activeBlock = null;
  // --- VARIABLES PARA SA PAGINATION ---
  let allPlotsData = [];
  let currentPage = 1;
  const PLOTS_PER_PAGE = 24; // Pwedeng baguhin kung ilan ang gusto mo per page

  const debounce = (fn, ms = 50) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); }; };
  const norm = coords => {
      const c = coords.split(',').map(Number);
      return [Math.min(c[0], c[2]), Math.min(c[1], c[3]), Math.max(c[0], c[2]), Math.max(c[1], c[3])];
  };

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

  function drawOverlays() {
    if (!img.complete || !img.naturalWidth || img.naturalWidth === 0) return;
    
    const scaleFactor = img.offsetWidth / img.naturalWidth;

    let fragment = document.createDocumentFragment();
    mapEl.querySelectorAll('area[data-orig]').forEach(a => {
      const [l, t, r, b] = a.dataset.orig.split(',').map(Number);
      const dbOffsetX = Number(a.dataset.offsetX);
      const dbOffsetY = Number(a.dataset.offsetY);
      
      const left = (l * scaleFactor) + dbOffsetX;
      const top = (t * scaleFactor) + dbOffsetY;
      const width = (r - l) * scaleFactor;
      const height = (b - t) * scaleFactor;
      
      const box = document.createElement('div');
      box.className = 'hotspot';
      box.dataset.key = a.dataset.key;
      box.style.cssText = `left:${left}px; top:${top}px; width:${width}px; height:${height}px; border-width:${hlSlider.value}px;`;
      
      box.addEventListener('click', () => handleBlockClick(a.dataset.key));
      box.addEventListener('mouseenter', (e) => showTooltip(e, a.title));
      box.addEventListener('mouseleave', hideTooltip);
      box.addEventListener('mousemove', moveTooltip);

      fragment.appendChild(box);
      blockElements.set(a.dataset.key, box);
    });
    
    layer.innerHTML = '';
    layer.appendChild(fragment);
    
    layer.classList.toggle('hidden', !toggleHL.checked);
  }

  const drawAll = debounce(drawOverlays);

  async function handleBlockClick(blockKey) {
    document.querySelectorAll('.hotspot.active').forEach(h => h.classList.remove('active'));
    const clickedElement = blockElements.get(blockKey);
    if (clickedElement) {
        if(toggleHL.checked) {
          clickedElement.classList.add('active');
        }
        const areaEl = mapEl.querySelector(`area[data-key="${blockKey}"]`);
        activeBlock = {
            id: areaEl.dataset.id,
            key: blockKey,
            offsetX: parseInt(areaEl.dataset.offsetX, 10),
            offsetY: parseInt(areaEl.dataset.offsetY, 10),
            element: clickedElement
        };
        updateCalUI();
    }
    hidePin();
    await openBlockModal(blockKey);
  }
  
  // --- BAGONG FUNCTION PARA MAG-RENDER NG PLOTS BY PAGE ---
  function renderCurrentPage() {
    const totalPages = Math.ceil(allPlotsData.length / PLOTS_PER_PAGE);
    const start = (currentPage - 1) * PLOTS_PER_PAGE;
    const end = start + PLOTS_PER_PAGE;
    const plotsForPage = allPlotsData.slice(start, end);

    let gridHTML = '<div class="plot-grid">';
    plotsForPage.forEach(plot => {
      const displayName = plot.deceased_name ? `<span class="plot-name">${plot.deceased_name}</span>` : 'Available';
      gridHTML += `<div class="plot-cell ${plot.status}" title="Plot #${plot.plot_number}">
          <span class="plot-number">${plot.plot_number}</span>
          ${displayName}
      </div>`;
    });
    gridHTML += `</div>`;
    
    let paginationHTML = `
      <div class="pagination-controls">
        <button class="btn btn-secondary" id="prev-page-btn" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>
        <span class="page-info">Page ${currentPage} of ${totalPages}</span>
        <button class="btn btn-secondary" id="next-page-btn" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}>Next</button>
      </div>
    `;

    modalBody.innerHTML = gridHTML + (totalPages > 1 ? paginationHTML : '');

    // Mag-add ng event listeners sa bagong buttons
    document.getElementById('prev-page-btn')?.addEventListener('click', () => {
      if(currentPage > 1) {
        currentPage--;
        renderCurrentPage();
      }
    });
    document.getElementById('next-page-btn')?.addEventListener('click', () => {
      if(currentPage < totalPages) {
        currentPage++;
        renderCurrentPage();
      }
    });
  }

  // --- INAYOS ANG MODAL PARA GAMITIN ANG PAGINATION ---
  async function openBlockModal(blockKey) {
      modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
      modalInstance.show();
      currentPage = 1; // I-reset palagi sa page 1
      
      const response = await fetch(`${window.URLROOT}/maps/getBlockDetails/${blockKey}`);
      const data = await response.json();
      modalTitle.textContent = data.title || 'Block Details';
      
      if (!data.plots || data.plots.length === 0) {
          modalBody.innerHTML = '<div class="alert alert-info">No plot layout. Go to <strong>Manage Blocks</strong> to configure.</div>';
          return;
      }
      
      allPlotsData = data.plots; // I-save ang lahat ng plots
      renderCurrentPage(); // I-render ang unang page
  }

  function showTooltip(e, text) { tooltip.textContent = text; tooltip.style.display = 'block'; moveTooltip(e); }
  function hideTooltip() { tooltip.style.display = 'none'; }
  function moveTooltip(e) {
      const rWrap = wrap.getBoundingClientRect();
      tooltip.style.left = `${e.clientX - rWrap.left + 15}px`;
      tooltip.style.top = `${e.clientY - rWrap.top}px`;
  }
  
  function handleSearch() {
      const query = searchInput.value.trim().toLowerCase();
      if (!query) return;

      const foundBlock = window.CEMAP_BLOCKS.find(b => b.title.toLowerCase().includes(query));
      
      if (foundBlock) {
          const el = blockElements.get(foundBlock.block_key);
          if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            showPin(el, foundBlock.title);
          }
      } else { 
          alert('Block not found!'); 
          hidePin();
      }
  }

  function showPin(targetElement, title) {
    if (!pin || !pinBubble) return;
    const targetRect = targetElement.getBoundingClientRect();
    const wrapRect = wrap.getBoundingClientRect();
    const pinX = (targetRect.left - wrapRect.left) + (targetRect.width / 2);
    const pinY = (targetRect.top - wrapRect.top) + (targetRect.height / 2);
    pin.style.left = `${pinX}px`;
    pin.style.top = `${pinY}px`;
    pinBubble.textContent = title;
    pin.style.display = 'flex';
  }

  function hidePin() { if(pin) pin.style.display = 'none'; }

  function updateCalUI() {
    if(!activeBlock) return;
    calxVal.textContent = activeBlock.offsetX;
    calyVal.textContent = activeBlock.offsetY;
    hlVal.textContent = `${hlSlider.value}px`;
  }

  function handleCal(e) {
      if(!activeBlock) return; 
      
      const type = e.target.dataset.cal;
      if (!type) return;
      if (type === 'x-') activeBlock.offsetX--;
      if (type === 'x+') activeBlock.offsetX++;
      if (type === 'y-') activeBlock.offsetY--;
      if (type === 'y+') activeBlock.offsetY++;
      updateCalUI();
      
      const scaleFactor = img.offsetWidth / img.naturalWidth;
      const areaEl = mapEl.querySelector(`area[data-key="${activeBlock.key}"]`);
      const [l, t, r, b] = areaEl.dataset.orig.split(',').map(Number);
      activeBlock.element.style.left = `${(l * scaleFactor) + activeBlock.offsetX}px`;
      activeBlock.element.style.top = `${(t * scaleFactor) + activeBlock.offsetY}px`;
  }

  async function saveCalibration() {
      if(!activeBlock) { alert("No block selected to save."); return; }
      const { id, offsetX, offsetY } = activeBlock;
      
      const response = await fetch(`${window.URLROOT}/maps/saveCalibration`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ block_id: id, offset_x: offsetX, offset_y: offsetY })
      });
      const result = await response.json();
      if(result.success) {
          const areaEl = mapEl.querySelector(`area[data-key="${activeBlock.key}"]`);
          areaEl.dataset.offsetX = offsetX;
          areaEl.dataset.offsetY = offsetY;
          alert('Calibration saved successfully!');
      } else {
          alert('Error: ' + result.message);
      }
  }

  kebab?.addEventListener('click', () => menu?.removeAttribute('hidden'));
  menuClose?.addEventListener('click', () => menu?.setAttribute('hidden', true));
  menu?.addEventListener('click', handleCal);

  hlSlider?.addEventListener('input', () => {
    if(activeBlock) activeBlock.element.style.borderWidth = `${hlSlider.value}px`;
    updateCalUI();
  });
  
  resetBtn?.addEventListener('click', () => {
    if(!activeBlock) return;
    activeBlock.offsetX = 0;
    activeBlock.offsetY = 0;
    hlSlider.value = 2;
    updateCalUI();
    drawAll();
  });

  saveBtn?.addEventListener('click', saveCalibration);
  
  toggleHL?.addEventListener('change', () => {
    layer.classList.toggle('hidden', !toggleHL.checked);
    toggleLabel.textContent = toggleHL.checked ? 'On' : 'Off';
  });
  
  searchBtn?.addEventListener('click', handleSearch);
  searchInput?.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); handleSearch(); } });

  function init() {
    buildAreasFromConfig();
    drawAll();
    toggleHL.checked = false;
    layer.classList.add('hidden');
    toggleLabel.textContent = 'Off';
  }
  
  window.addEventListener('resize', drawAll);
  
  if (img.complete) { init(); } else { img.addEventListener('load', init); }
})();