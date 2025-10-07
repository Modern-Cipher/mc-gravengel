<?php require APPROOT . '/views/includes/staff_header.php'; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/public/css/staff_map-styles.css">
<script>window.URLROOT = "<?= URLROOT ?>";</script>

<div class="main-content-header mb-4 d-flex justify-content-between align-items-start">
  <h1 class="me-auto">Cemetery Map</h1>
  <div class="d-flex flex-wrap align-items-center gap-3">
    <div class="input-group input-group-sm" style="max-width:250px;">
      <span class="input-group-text"><i class="fas fa-search"></i></span>
      <input type="search" id="map-search-input" class="form-control" placeholder="Search block...">
      <button id="map-search-btn" class="btn btn-primary">Search</button>
    </div>
    <div class="form-check form-switch d-flex align-items-center gap-2">
      <input class="form-check-input" type="checkbox" id="toggle-highlights">
      <label class="form-check-label" for="toggle-highlights">
        Highlight Toggle <span id="hl-toggle-label">(Off)</span>
      </label>
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
    <img src="<?= URLROOT ?>/public/img/cemeteryMap.png" alt="Cemetery Map" id="map-img" usemap="#image-map">
    <map name="image-map" id="image-map"></map>
    <div class="hotspots" id="hotspots"></div>
    <div class="map-tooltip" id="map-tooltip"></div>

    <!-- Search result pin -->
    <div class="pin" id="pin" style="display:none;">
      <div class="pin-bubble"></div>
      <div class="pin-arrow"></div>
    </div>
  </div>
</div>

<!-- Block grid (anchored) -->
<div class="modal fade" id="blockInfoModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="blockInfoModalLabel">Block Details</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" id="blockInfoModalBody"></div>
  </div></div>
</div>

<!-- Burial details (read-only) -->
<div class="modal fade" id="burialDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
    <div class="modal-header bg-danger text-white">
      <h5 class="modal-title" id="burialDetailsModalLabel">Burial Details</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" id="burialDetailsModalBody">
      <div class="text-center p-4">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
      </div>
    </div>
  </div></div>
</div>

<!-- expose blocks BEFORE the page script runs -->
<script>window.CEMAP_BLOCKS = <?= json_encode($data['blocks'] ?? []) ?>;</script>

<?php require APPROOT . '/views/includes/staff_footer.php'; ?>

<!-- ✅ Vendor first, then page JS (fixes the crash) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- one clean include (no duplicates) -->
<script src="<?= URLROOT ?>/public/js/staff_map.js?v=<?= time() ?>"></script>
