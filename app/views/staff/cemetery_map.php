<?php require APPROOT . '/views/includes/staff_header.php'; ?>

<div class="main-content-header mb-4 d-flex justify-content-between align-items-center">
    <h1>Cemetery Map</h1>
    <div class="d-flex align-items-center gap-3">
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

<script>
    window.CEMAP_BLOCKS = <?php echo json_encode($data['blocks'] ?? []); ?>;
</script>
<script src="<?php echo URLROOT; ?>/js/map.js?v=<?php echo time(); ?>"></script>

<?php if(isset($_SESSION['flash_message'])): ?>
    <script>
        Swal.fire({ icon: '<?php echo $_SESSION['flash_type']; ?>', title: '<?php echo $_SESSION['flash_message']; ?>', showConfirmButton: false, timer: 2000 });
    </script>
    <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
<?php endif; ?>
<?php require APPROOT . '/views/includes/admin_footer.php'; ?>