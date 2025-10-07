<?php require APPROOT . '/views/includes/staff_header.php'; // Siguraduhing may staff_header.php ka ?>
<script>window.URLROOT = "<?= URLROOT ?>";</script>

<div class="main-content-header mb-4">
  <h1>My Profile</h1>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="profile-card">
      <div class="profile-header">
        <div class="profile-avatar-container zoomable">
          <?php if (!empty($data['user']->profile_image)): ?>
            <img src="<?= URLROOT . '/public/img/profiles/' . htmlspecialchars($data['user']->profile_image) ?>"
                 alt="Profile Avatar" class="profile-avatar" id="main-avatar">
          <?php else: ?>
            <div class="profile-avatar-icon" id="main-avatar-fallback"><i class="fas fa-user"></i></div>
          <?php endif; ?>
        </div>

        <div class="profile-info">
          <h4><?= htmlspecialchars($data['user']->first_name . ' ' . $data['user']->last_name) ?></h4>
          <p class="designation"><?= htmlspecialchars($data['user']->designation ?? 'Staff') ?></p>
          <p class="mt-1"><?= htmlspecialchars($data['user']->email) ?></p>
          <p class="mt-2">
            <strong>Staff ID:</strong> <?= htmlspecialchars($data['user']->staff_id ?? 'N/A') ?> |
            <strong>Username:</strong> <?= htmlspecialchars($data['user']->username) ?>
          </p>
        </div>

        <button class="btn btn-edit-info" data-bs-toggle="modal" data-bs-target="#editProfileModal">
          <i class="fas fa-pencil-alt me-1"></i> Edit Info
        </button>
      </div>
    </div>

    <div class="action-card">
      <div class="card-header">
        <h3 class="card-title-strong"><i class="fas fa-key"></i> Change Password</h3>
      </div>
      <div class="card-body">
        <form id="changePasswordForm" novalidate>
          <div class="mb-3">
            <label class="form-label" for="current_password">Current Password</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="new_password">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
            <div class="form-text">Minimum of 6 characters.</div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="confirm_password">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn text-white" style="background-color: var(--maroon);" id="password-update-btn">
            Update Password
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="action-card structured-card">
      <div class="card-header">
        <h3 class="card-title-strong"><i class="fas fa-history"></i> Recent Activity (Today)</h3>
      </div>
      <div class="card-body">
        <ul class="activity-list" id="my-activity-list">
          <li class="activity-item text-muted">Loading…</li>
        </ul>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted" id="act-count"></small>
        <button class="btn btn-outline-danger btn-sm" id="reset-recent-btn" title="Reload the list of activities">
          <i class="fas fa-sync-alt"></i> Reload List
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
    <div class="modal-header" style="background: var(--maroon); color:#fff;">
      <h5 class="modal-title" id="editProfileModalLabel"><i class="fas fa-user-edit me-2"></i> Edit Profile</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <form id="profileForm" enctype="multipart/form-data" novalidate>
        <div class="text-center mb-4">
          <div class="profile-avatar-container modal-avatar" id="modal-avatar-wrapper">
            <?php if (!empty($data['user']->profile_image)): ?>
              <img src="<?= URLROOT . '/public/img/profiles/' . htmlspecialchars($data['user']->profile_image) ?>"
                   id="modal-profile-image" alt="Profile">
            <?php else: ?>
              <div class="profile-avatar-icon" id="modal-profile-image"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <label for="imageUploadInput" class="btn btn-light btn-sm btn-upload" title="Select Image">
              <i class="fas fa-camera"></i>
            </label>
          </div>
          <input type="file" name="profile_image" id="imageUploadInput" accept="image/png, image/jpeg">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($data['user']->first_name) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($data['user']->last_name) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($data['user']->email) ?>">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($data['user']->phone ?? '') ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($data['user']->address ?? '') ?>">
          </div>
        </div>

        <hr>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Staff ID</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($data['user']->staff_id ?? '') ?>" disabled>
            <input type="hidden" name="staff_id" value="<?= htmlspecialchars($data['user']->staff_id ?? '') ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($data['user']->username) ?>" disabled>
            <input type="hidden" name="username" value="<?= htmlspecialchars($data['user']->username) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Designation</label>
          <input type="text" class="form-control" name="designation" value="<?= htmlspecialchars($data['user']->designation ?? 'Staff') ?>">
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      <button type="button" class="btn text-white" style="background-color: var(--maroon);" id="edit-save-btn">
        Save Changes
      </button>
    </div>
  </div></div>
</div>

<?php require APPROOT . '/views/includes/staff_footer.php'; // Siguraduhing may staff_footer.php ka ?>
