<?php require APPROOT . '/views/includes/admin_header.php'; ?>

<div class="main-content-header mb-4">
    <h1>My Profile</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar-container zoomable"> 
                    <?php if (!empty($data['user']->profile_image)): ?>
                        <img src="<?php echo URLROOT . '/public/img/profiles/' . $data['user']->profile_image; ?>" alt="Profile Avatar" class="profile-avatar">
                    <?php else: ?>
                        <div class="profile-avatar-icon"><i class="fas fa-user"></i></div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h4><?php echo htmlspecialchars($data['user']->first_name . ' ' . $data['user']->last_name); ?></h4>
                    <p class="designation"><?php echo htmlspecialchars($data['user']->designation ?? 'No Designation'); ?></p>
                    <p class="mt-1"><?php echo htmlspecialchars($data['user']->email); ?></p>
                    <p class="mt-2">
                        <strong>Staff ID:</strong> <?php echo htmlspecialchars($data['user']->staff_id ?? 'N/A'); ?> | 
                        <strong>Username:</strong> <?php echo htmlspecialchars($data['user']->username); ?>
                    </p>
                </div>
                <button class="btn btn-edit-info" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-pencil-alt me-1"></i> Edit Info
                </button>
            </div>
        </div>

        <div class="action-card">
            <div class="card-header">
                <h3 class="card-title-strong"><i class="fas fa-key"></i>Change Password</h3>
            </div>
            <div class="card-body">
                <form id="changePasswordForm" novalidate>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn text-white" style="background-color: var(--maroon);" id="password-update-btn">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="action-card structured-card">
            <div class="card-header">
                 <h3 class="card-title-strong"><i class="fas fa-history"></i>Your Recent Activity</h3>
            </div>
            <div class="card-body">
                <ul class="activity-list">
                    <li class="activity-item">You added burial record G-003. <br><small>04/07/2025, 03:11:52</small></li>
                    <li class="activity-item">You changed your password. <br><small>04/06/2025, 07:34:26</small></li>
                    <li class="activity-item">You added burial record G-002. <br><small>04/05/2025, 03:11:52</small></li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="#" class="delete-account-btn"><i class="fas fa-trash-alt me-1"></i>Delete Account</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="imageUploadForm" class="text-center mb-4">
            <div class="image-upload-area">
                <div class="profile-avatar-container modal-avatar" id="modal-avatar-wrapper">
                    <?php if (!empty($data['user']->profile_image)): ?>
                        <img src="<?php echo URLROOT . '/public/img/profiles/' . $data['user']->profile_image; ?>" id="modal-profile-image" alt="Profile Image">
                    <?php else: ?>
                        <div class="profile-avatar-icon" id="modal-profile-image"><i class="fas fa-user"></i></div>
                    <?php endif; ?>
                     <label for="imageUploadInput" class="btn btn-light btn-sm btn-upload" title="Upload New Image"><i class="fas fa-camera"></i></label>
                </div>
                <input type="file" name="profile_image" id="imageUploadInput" accept="image/png, image/jpeg">
            </div>
            <button type="submit" class="btn btn-sm mt-2" style="background-color: var(--maroon); color: white;" id="upload-image-btn">Upload Image</button>
        </form>
        
        <form id="editDetailsForm" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($data['user']->first_name); ?>">
                </div>
                 <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($data['user']->last_name); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($data['user']->email); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($data['user']->phone ?? ''); ?>">
            </div>
             <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($data['user']->address ?? ''); ?></textarea>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Staff ID</label>
                    <input type="text" class="form-control" name="staff_id" value="<?php echo htmlspecialchars($data['user']->staff_id ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Designation</label>
                    <input type="text" class="form-control" name="designation" value="<?php echo htmlspecialchars($data['user']->designation ?? ''); ?>">
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" form="editDetailsForm" class="btn text-white" style="background-color: var(--maroon);" id="edit-save-btn">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo URLROOT; ?>/js/profile.js?v=<?php echo time(); ?>"></script>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>