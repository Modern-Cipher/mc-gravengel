<nav class="navbar navbar-expand-lg navbar-light bg-light mb-3 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">GRAVENGEL Map Editor</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mapNav" aria-controls="mapNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mapNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo URLROOT; ?>/maps">View Map</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo URLROOT; ?>/maps/manage">Manage Blocks</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="<?php echo URLROOT; ?>/maps?mode=dev">Dev Mode</a>
                </li>
            </ul>
            <div class="d-flex" id="map-search-container">
                <input class="form-control me-2" type="search" id="map-search-input" placeholder="Search Title..." aria-label="Search">
                <button class="btn btn-outline-success" id="map-search-btn" type="button">Search</button>
            </div>
        </div>
    </div>
</nav>