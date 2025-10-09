<?php require APPROOT . '/views/includes/admin_header.php'; ?>
<style>
/* --- General Maroon Theme --- */
.btn-maroon {
    background-color: #7b1e28 !important;
    border-color: #7b1e28 !important;
    color: #fff !important;
}
.btn-maroon:hover {
    background-color: #6c1b24 !important;
    border-color: #6c1b24 !important;
}
.text-maroon { color: #7b1e28 !important; }
.bg-maroon { background-color: #7b1e28 !important; color: #fff !important; }

/* --- MAROON TOGGLE SWITCH FIX --- */
.form-check-input.user-toggle:checked {
  background-color: #7b1e28;
  border-color: #7b1e28;
}
.form-check-input.user-toggle:focus {
  box-shadow: 0 0 0 0.25rem rgba(123, 30, 40, 0.25);
}
.form-check-input.user-toggle {
    background-color: #adb5bd;
    border-color: #adb5bd;
}
</style>
<div class="main-content-header mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <h1>User Accounts</h1>
    <button class="btn btn-maroon" id="add-user-btn" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="fas fa-plus me-1"></i> Add New Staff</button>
  </div>
</div>

<div class="card card-body">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Manage Staff Accounts</h5>
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
          <tr><td colspan="7" class="text-center">No staff users found.</td></tr>
        <?php else: ?>
          <?php foreach ($data['users'] as $user): ?>
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
                <div class="d-flex align-items-center">
                    <div class="form-check form-switch">
                      <input class="form-check-input user-toggle" type="checkbox" role="switch" id="user-<?php echo $user->id; ?>" data-user-id="<?php echo $user->id; ?>" <?php echo ($user->is_active) ? 'checked' : ''; ?>>
                    </div>
                    <span class="ms-2 badge <?php echo $user->is_active ? 'bg-maroon' : 'bg-secondary'; ?>">
                        <?php echo $user->is_active ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
              </td>
              <td class="text-end">
                <button class="btn btn-action-icon btn-sm edit-user-btn text-maroon" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>' title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-action-icon btn-sm reset-pwd-btn text-maroon" data-user-id="<?php echo $user->id; ?>" data-email="<?php echo htmlspecialchars($user->email); ?>" title="Reset Password">
                  <i class="fas fa-key"></i>
                </button>
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
      <div class="modal-header bg-maroon text-white">
        <h5 class="modal-title">Add New Staff</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="addStaffForm" novalidate>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">First Name</label><input type="text" class="form-control" name="first_name" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" required></div>
          </div>
          <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" name="username" required><div class="form-text">Letters and numbers only. No spaces.</div></div>
          <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" name="email" required>
              <div class="invalid-feedback">Please provide a valid email address.</div>
          </div>
          <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="tel" class="form-control" name="phone" placeholder="e.g. 0912 345 6789" required maxlength="13">
              <div class="invalid-feedback">Please follow the format: 0912 345 6789.</div>
          </div>
          <div class="mb-3"><label class="form-label">Designation</label><input type="text" class="form-control" name="designation" required></div>
          <div class="alert alert-secondary" role="alert"><i class="fas fa-info-circle me-2"></i>Staff ID will be auto-generated.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-maroon">Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-maroon text-white">
        <h5 class="modal-title">Edit User Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="editUserForm" novalidate>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-user-id">
          <div class="mb-3"><label class="form-label">Staff ID</label><input type="text" class="form-control" id="edit-staff_id" name="staff_id" readonly disabled></div>
          <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" id="edit-username" name="username" readonly disabled></div>
          <hr>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">First Name</label><input type="text" class="form-control" id="edit-first_name" name="first_name" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Last Name</label><input type="text" class="form-control" id="edit-last_name" name="last_name" required></div>
          </div>
          <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" id="edit-email" name="email" required>
              <div class="invalid-feedback">Please provide a valid email address.</div>
          </div>
          <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="tel" class="form-control" id="edit-phone" name="phone" placeholder="e.g. 0912 345 6789" required maxlength="13">
              <div class="invalid-feedback">Please follow the format: 0912 345 6789.</div>
          </div>
          <div class="mb-3"><label class="form-label">Designation</label><input type="text" class="form-control" id="edit-designation" name="designation" required></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-maroon">Save Changes</button>
        </div>
      </form>
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
<script src="<?php echo URLROOT; ?>/js/user_accounts.js?v=<?php echo time(); ?>"></script>
<?php require APPROOT . '/views/includes/admin_footer.php'; ?>