<?php require APPROOT . '/views/includes/admin_header.php'; ?>

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
        <div class="legend-item"><span class="legend-color reserved"></span> Reserved</div>
        <div class="legend-item"><span class="legend-color bone"></span> Bone</div>
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
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="manageForm">
        <div class="modal-header">
          <h5 class="modal-title">Manage Block</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="manage-block-id">
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
            <p class="text-muted small">
                Note: Changing the rows and columns will reset all plots within this block.
            </p>
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
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
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