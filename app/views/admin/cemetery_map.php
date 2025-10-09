<?php require APPROOT . '/views/includes/admin_header.php'; ?>
<style>
  /* General Styles */
  .main-content-header { z-index: 10; }
  .legend-card { z-index: 10; }
  .stage { position: relative; }
  .img-wrap { position: relative; overflow: auto; max-height: 80vh; border: 1px solid #ddd; }
  #map-img { display: block; }
  .hotspot { position: absolute; border: 2px solid rgba(255, 0, 0, .7); cursor: pointer; }
  .hotspot:hover { background: rgba(255, 255, 0, .3); }
  .map-tooltip { /* as is */ }

  /* === CSS For Smaller & Responsive Grid === */
  #blockInfoModal .modal-dialog {
    /* Make modal width responsive, but not more than 950px */
    max-width: min(950px, 95vw); 
  }
  #blockInfoModal .modal-body {
    background-color: #f1f3f5;
    padding: 0.75rem; /* Reduced padding */
    overflow-x: auto;   /* ENABLE horizontal scrolling */
    overflow-y: hidden; /* DISABLE vertical scrolling */
    -webkit-overflow-scrolling: touch; /* Smoother scrolling on mobile */
  }
  .plot-grid {
    display: grid;
    grid-template-columns: repeat(var(--cols, 8), 1fr);
    gap: 8px; /* Reduced gap */
    width: max-content; /* Allows the grid to be wider than the container */
  }
  .plot-cell {
    border: 1px solid #ccc;
    border-radius: .375rem;
    padding: 5px; /* Reduced padding */
    text-align: center;
    cursor: default;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 75px; /* Reduced height */
    min-width: 105px; /* Reduced width */
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    font-size: 0.75rem; /* Reduced font size */
    line-height: 1.3;
    -webkit-user-select: none; /* Prevent text selection on mobile tap */
    -ms-user-select: none;
    user-select: none;
  }
  .plot-cell.occupied {
    cursor: pointer;
  }
  .plot-cell.occupied:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,.1);
  }
  .plot-cell.vacant { background-color: #d4edda; color: #155724; border-color: #c3e6cb;}
  .plot-cell.occupied { background-color: #dc3545; color: #fff; border-color: #dc3545;}
  
  .plot-number {
    font-weight: 500;
  }
  .plot-details {
      font-size: 0.7rem;
      color: inherit;
      opacity: 0.9;
  }
  .plot-status {
      font-weight: normal;
  }
  .plot-cell.occupied .plot-status {
      font-weight: bold;
  }
  
  .modal-header-controls {
    margin-left: auto;
  }
  .modal-header-controls .input-group {
    max-width: 250px;
  }

  .plot-cell {
    position: relative; /* This is needed for the count badge */
    border: 1px solid #ccc;
    border-radius: .375rem;
    padding: 5px;
    text-align: center;
    cursor: default;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 75px;
    min-width: 105px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    font-size: 0.75rem;
    line-height: 1.3;
    user-select: none;
  }
  
  /* [NEW] Style for the occupant count badge */
  .occupant-count {
    position: absolute;
    top: 4px;
    right: 4px;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    font-size: 0.7rem;
    font-weight: bold;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }
</style>

<div class="main-content-header mb-4 d-flex justify-content-between align-items-start">
    <h1 class="me-auto">Cemetery Map</h1>
    <div class="d-flex flex-wrap align-items-center gap-3">
        <div class="input-group input-group-sm" style="max-width:250px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="search" id="map-search-input" class="form-control" placeholder="Search block...">
            <button id="map-search-btn" class="btn btn-primary">Search</button>
        </div>
        <button id="manage-btn" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit me-1"></i> Manage Blocks</button>
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
        <div class="pin" id="pin" style="display:none;">
            <div class="pin-bubble"></div>
            <div class="pin-arrow"></div>
        </div>
        <div class="kebab" id="kebab"><i class="fas fa-sliders-h"></i></div>
        <div class="menu" id="dev-menu" hidden>
            <div class="menu-head">
                <span class="menu-label">Calibration Controls</span>
                <button class="icon-btn close" id="menu-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="menu-row">
                <span>Offset X: <strong id="calx-val">0</strong></span>
                <div class="ctrl-group" data-cal-for="x">
                    <button class="ctrl-btn" data-cal="x-">-</button>
                    <button class="ctrl-btn" data-cal="x+">+</button>
                </div>
            </div>
            <div class="menu-row">
                <span>Offset Y: <strong id="caly-val">0</strong></span>
                <div class="ctrl-group" data-cal-for="y">
                    <button class="ctrl-btn" data-cal="y-">-</button>
                    <button class="ctrl-btn" data-cal="y+">+</button>
                </div>
            </div>
            <div class="menu-row">
                <span>Highlight Border: <strong id="hl-val">2px</strong></span>
                <div class="slider-group"><input type="range" id="hl-slider" min="0" max="10" value="2"></div>
            </div>
            <div class="menu-row mb-2">
                <div class="form-check form-switch d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="toggle-highlights">
                    <label class="form-check-label" for="toggle-highlights">Highlight Toggle <span id="hl-toggle-label">(Off)</span></label>
                </div>
            </div>
            <button class="btn-reset" id="reset-cal">Reset Offsets</button>
            <button class="btn-save" id="save-cal">Save Calibration</button>
        </div>
    </div>
</div>

<div class="modal fade" id="blockInfoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="blockInfoModalLabel">Block Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="blockInfoModalBody">
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="manageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="manageForm" method="POST" action="<?php echo URLROOT; ?>/admin/updateBlock">
        <div class="modal-header">
          <h5 class="modal-title">Manage Block</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="manage-block-id">
            <input type="hidden" name="offset_x" id="manage-offset-x">
            <input type="hidden" name="offset_y" id="manage-offset-y">
            <div class="mb-3">
                <label for="manage-title" class="form-label">Title</label>
                <input type="text" name="title" id="manage-title" class="form-control" required>
            </div>
            <div class="row">
               <div class="col-md-6 mb-3">
                   <label for="manage-rows" class="form-label">Plot Rows</label>
                   <input type="number" name="modal_rows" id="manage-rows" class="form-control" required>
               </div>
               <div class="col-md-6 mb-3">
                   <label for="manage-cols" class="form-label">Plot Columns</label>
                   <input type="number" name="modal_cols" id="manage-cols" class="form-control" required>
               </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="burialDetailsModal" tabindex="-1" aria-labelledby="burialDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="burialDetailsModalLabel">Burial Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="burialDetailsModalBody">
            </div>
        </div>
    </div>
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
<script src="<?php echo URLROOT; ?>/js/admin_map.js?v=<?php echo time(); ?>"></script>

<?php if(isset($_SESSION['flash_message'])): ?>
    <script>
        Swal.fire({ icon: '<?php echo $_SESSION['flash_type']; ?>', title: '<?php echo $_SESSION['flash_message']; ?>', showConfirmButton: false, timer: 2000 });
    </script>
    <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
<?php endif; ?>
<?php require APPROOT . '/views/includes/admin_footer.php'; ?>