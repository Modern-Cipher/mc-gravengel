<?php require APPROOT . '/views/includes/header.php'; ?>
<?php require APPROOT . '/views/includes/navbar_map.php'; ?>

<div class="stage">
    <div id="img-wrap" class="img-wrap">
        <button id="kebab" class="kebab" aria-label="Open Controls">
            <span></span><span></span><span></span>
        </button>
        <div id="dev-menu" class="menu" hidden>
            <div class="menu-head">
              <div class="menu-label">Map Controls</div>
              <button class="icon-btn close" id="menu-close" aria-label="Close">✕</button>
            </div>
            <div class="menu-row">
                <div class="menu-label">Toggle Highlights</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggle-highlights">
                    <label class="form-check-label" for="toggle-highlights">Off</label>
                </div>
            </div>
            <hr>
            <div class="menu-label">Manual Calibration</div>
             <p class="small text-muted mb-2">Select a block first to calibrate.</p>
            <div class="menu-row">
              <div class="menu-label">Move X (Left/Right)</div>
              <div class="ctrl-group"><button class="ctrl-btn" data-cal="x-">-</button><output id="calx-val">0</output><button class="ctrl-btn" data-cal="x+">+</button></div>
            </div>
            <div class="menu-row">
              <div class="menu-label">Move Y (Up/Down)</div>
              <div class="ctrl-group"><button class="ctrl-btn" data-cal="y-">-</button><output id="caly-val">0</output><button class="ctrl-btn" data-cal="y+">+</button></div>
            </div>
            <div class="menu-row">
              <div class="menu-label">Highlight Thickness</div>
              <div class="ctrl-group slider-group"><input type="range" id="hl-slider" min="1" max="5" step="1" value="2"><output id="hl-val">2px</output></div>
            </div>
            <div class="menu-row">
                <button id="reset-cal" class="btn-reset">Reset</button>
                <button id="save-cal" class="btn-save">Save Calibration</button>
            </div>
        </div>
        
        <img id="map-img" src="<?php echo URLROOT; ?>/public/img/cemeteryMap.png" alt="Cemetery Map" usemap="#image-map" />
        <map id="image-map" name="image-map"></map>
        <div id="hotspots" class="hotspots"></div>

        <div id="map-tooltip" class="map-tooltip" style="display: none;"></div>
        <div id="pin" class="pin" style="display: none;">
            <div class="pin-bubble"></div>
            <div class="pin-arrow"></div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="blockInfoModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="blockInfoModalLabel">Block Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="blockInfoModalBody"></div>
      <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.CEMAP_BLOCKS = <?php echo !empty($data['blocks']) ? json_encode($data['blocks']) : '[]'; ?>;
        window.URLROOT = '<?php echo URLROOT; ?>';
        
        var script = document.createElement('script');
        script.src = '<?php echo URLROOT; ?>/public/js/map.js';
        document.body.appendChild(script);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>