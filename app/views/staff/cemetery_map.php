<?php require APPROOT . '/views/includes/staff_header.php'; ?>
<style>
  /* General Styles */
  .main-content-header { z-index: 10; }
  .legend-card { z-index: 10; }
  .stage { position: relative; }
  .img-wrap { position: relative; overflow: auto; max-height: 80vh; border: 1px solid #ddd; }
  #map-img { display: block; }
  .hotspot { position: absolute; border: 2px solid rgba(255, 0, 0, .7); cursor: pointer; }
  .hotspot:hover { background: rgba(255, 255, 0, .3); }
  .map-tooltip, .pin, .kebab, .menu { z-index: 15; /* Ensure controls are on top */ }

  /* Grid and Plot Styles */
  #blockInfoModal .modal-dialog { max-width: min(950px, 95vw); }
  #blockInfoModal .modal-body { background-color: #f1f3f5; padding: 0.75rem; overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; }
  .plot-grid { display: grid; grid-template-columns: repeat(var(--cols, 8), 1fr); gap: 8px; width: max-content; }
  .plot-cell { position: relative; border: 1px solid #ccc; border-radius: .375rem; padding: 5px; text-align: center; cursor: default; display: flex; flex-direction: column; justify-content: center; min-height: 75px; min-width: 105px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); font-size: 0.75rem; line-height: 1.3; user-select: none; }
  .occupant-count { position: absolute; top: 4px; right: 4px; background-color: rgba(0, 0, 0, 0.5); color: white; font-size: 0.7rem; font-weight: bold; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; line-height: 1; }
  .plot-cell.occupied { cursor: pointer; }
  .plot-cell.occupied:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,.1); }
  .plot-cell.vacant { background-color: #d4edda; color: #155724; border-color: #c3e6cb;}
  .plot-cell.occupied { background-color: #dc3545; color: #fff; border-color: #dc3545;}
  .plot-number { font-weight: 500; }
  .plot-details { font-size: 0.7rem; color: inherit; opacity: 0.9; }
  .plot-status { font-weight: normal; }
  .plot-cell.occupied .plot-status { font-weight: bold; }
  .modal-header-controls { margin-left: auto; }
  .modal-header-controls .input-group { max-width: 250px; }
</style>

<div class="main-content-header mb-4 d-flex justify-content-between align-items-start">
    <h1 class="me-auto">Cemetery Map</h1>
    <div class="d-flex flex-wrap align-items-center gap-3">
        <div class="input-group input-group-sm" style="max-width:250px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="search" id="map-search-input" class="form-control" placeholder="Search block...">
            <button id="map-search-btn" class="btn btn-primary">Search</button>
        </div>
        </div>
</div>

<div class="legend-card top-legend mb-4">
    <h6 class="legend-title">Plot Status Legend</h6>
    <div class="legend-items">
        <div class="legend-item"><span class="legend-color vacant"></span> Vacant</div>
        <div class="legend-item"><span class="legend-color occupied"></span> Occupied</div>
    </div>
</div>

<div class="stage">
    <div class="img-wrap" id="img-wrap">
        <img src="<?php echo URLROOT; ?>/public/img/cemeteryMap.png" alt="Cemetery Map" id="map-img" usemap="#image-map">
        <map name="image-map" id="image-map"></map>
        <div class="hotspots" id="hotspots"></div>
        <div class="map-tooltip" id="map-tooltip"></div>
        <div class="pin" id="pin" style="display:none;"><div class="pin-bubble"></div><div class="pin-arrow"></div></div>
        
        <div class="kebab" id="kebab"><i class="fas fa-sliders-h"></i></div>
        <div class="menu" id="dev-menu" hidden>
            <div class="menu-head">
                <span class="menu-label">Controls</span>
                <button class="icon-btn close" id="menu-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="menu-row mb-2">
                <div class="form-check form-switch d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="toggle-highlights">
                    <label class="form-check-label" for="toggle-highlights">Highlight Toggle <span id="hl-toggle-label">(Off)</span></label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="blockInfoModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="blockInfoModalLabel">Block Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="blockInfoModalBody"></div>
  </div></div>
</div>

<div class="modal fade" id="burialDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="burialDetailsModalLabel">Burial Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="burialDetailsModalBody"></div>
    </div></div>
</div>
<style>
    .scroll-spacer-dummy {
   
    height: 1200px; 
    opacity: 0;             
    visibility: hidden;    
    pointer-events: none;  
    padding: 0;
    margin: 0;
    width: 100%;
}
</style>
<div class="row">
    <div class="col-12">
        <div class="scroll-spacer-dummy">
            </div>
    </div>
</div>

<script>
    window.CEMAP_BLOCKS = <?php echo json_encode($data['blocks'] ?? []); ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?php echo URLROOT; ?>/js/staff_map.js?v=<?php echo time(); ?>"></script>

<?php if(isset($_SESSION['flash_message'])): ?>
    <script> Swal.fire({ icon: '<?php echo $_SESSION['flash_type']; ?>', title: '<?php echo $_SESSION['flash_message']; ?>', showConfirmButton: false, timer: 2000 }); </script>
    <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
<?php endif; ?>
<?php require APPROOT . '/views/includes/staff_footer.php'; ?>