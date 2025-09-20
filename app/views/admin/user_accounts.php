<?php require APPROOT . '/views/includes/admin_header.php'; ?>

<div class="main-content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1>User Accounts</h1>
        <button class="btn btn-primary" id="add-user-btn" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="fas fa-plus me-1"></i> Add New Staff</button>
    </div>
</div>

<div class="card card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Manage Staff & User Accounts</h5>
        <div class="input-group input-group-sm w-auto">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="user-search" class="form-control" placeholder="Search users...">
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover" id="user-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Staff ID</th>
                    <th>Designation</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['users'])): ?>
                    <tr>
                        <td colspan="7" class="text-center">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($data['users'] as $user): ?>
                        <tr>
                            <td>
                                <?php if ($user->profile_image): ?>
                                    <img src="<?php echo URLROOT . '/public/img/profiles/' . htmlspecialchars($user->profile_image); ?>" class="profile-thumb zoomable" alt="Profile">
                                <?php else: ?>
                                    <div class="profile-thumb-icon"><i class="fas fa-user"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user->first_name . ' ' . $user->last_name); ?></td>
                            <td><?php echo htmlspecialchars($user->staff_id ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user->designation ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user->email); ?></td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input user-toggle" type="checkbox" role="switch" id="user-<?php echo $user->id; ?>" data-user-id="<?php echo $user->id; ?>" <?php echo ($user->is_active) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="user-<?php echo $user->id; ?>"></label>
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-action-icon edit-user-btn" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>' title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-action-icon" title="Reset Password"><i class="fas fa-key"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addStaffModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Staff</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="addStaffForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="add-first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="add-first_name" name="first_name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="add-last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="add-last_name" name="last_name" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="add-username" class="form-label">Username</label>
            <input type="text" class="form-control" id="add-username" name="username" required>
            <div class="form-text">Letters and numbers only.</div>
          </div>
          <div class="mb-3">
            <label for="add-email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="add-email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="add-phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="add-phone" name="phone" placeholder="e.g. 0912 345 6789" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="add-staff_id" class="form-label">Staff ID</label>
              <input type="text" class="form-control" id="add-staff_id" name="staff_id" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="add-designation" class="form-label">Designation</label>
              <input type="text" class="form-control" id="add-designation" name="designation" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editUserForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-user-id">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit-first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="edit-first_name" name="first_name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit-last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="edit-last_name" name="last_name" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="edit-username" class="form-label">Username</label>
            <input type="text" class="form-control" id="edit-username" name="username" readonly>
            <div class="form-text">Username cannot be changed.</div>
          </div>
          <div class="mb-3">
            <label for="edit-email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="edit-email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="edit-phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="edit-phone" name="phone" placeholder="e.g. 0912 345 6789" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit-staff_id" class="form-label">Staff ID</label>
              <input type="text" class="form-control" id="edit-staff_id" name="staff_id" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit-designation" class="form-label">Designation</label>
              <input type="text" class="form-control" id="edit-designation" name="designation" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?php echo URLROOT; ?>/js/user_accounts.js?v=<?php echo time(); ?>"></script>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>