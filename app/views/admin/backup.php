<?php require APPROOT . '/views/includes/admin_header.php'; ?>

<!-- CSS para sa custom progress bar -->
<style>
    .swal2-progress-bar { background-color: #f8f9fa; border-radius: 5px; height: 20px; width: 100%; overflow: hidden; border: 1px solid #ccc; }
    .swal2-progress-fill { background-color: #8a2c38; border-radius: 5px; width: 0%; height: 100%; text-align: center; line-height: 20px; color: white; font-weight: bold; font-size: 12px; transition: width 0.2s ease-in-out; }
</style>

<div class="container-fluid py-4">
    <div class="main-content-header mb-4">
        <h1>Backup & Restore</h1>
        <p class="text-muted">Manage your database backups. Regular backups are crucial for data safety.</p>
    </div>

    <?php 
        if (isset($_SESSION['flash_message'])) {
            $type = $_SESSION['flash_type'] ?? 'info';
            echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' . $_SESSION['flash_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        }
    ?>

    <div class="row">
        <!-- Cards for Backup and Restore -->
        <div class="col-lg-6 mb-4"><div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-download me-2"></i>Create & Download Backup</h5></div>
            <div class="card-body"><p>Click the button below to generate a complete backup of the database.</p></div>
            <div class="card-footer text-end"><button id="downloadBtn" data-url="<?= URLROOT ?>/admin/createBackup" class="btn btn-primary"><i class="fas fa-play-circle me-2"></i>Start Backup</button></div>
        </div></div>
        <div class="col-lg-6 mb-4"><div class="card h-100 border-danger">
            <div class="card-header bg-danger text-white"><h5 class="mb-0"><i class="fas fa-upload me-2"></i>Restore from Backup</h5></div>
            <div class="card-body">
                <div class="alert alert-warning" role="alert"><strong><i class="fas fa-exclamation-triangle"></i> CAUTION!</strong><p class="mb-0 mt-2">Restoring will <strong>overwrite all current data</strong>. This is irreversible.</p></div>
                <form id="restoreForm" action="<?= URLROOT ?>/admin/restoreBackup" method="post" enctype="multipart/form-data">
                    <div class="mb-3"><label for="backup_file" class="form-label">Select .sql Backup File:</label><input class="form-control" type="file" id="backup_file" name="backup_file" accept=".sql" required></div>
                    <button type="submit" class="btn btn-danger w-100"><i class="fas fa-hdd me-2"></i>Upload & Restore</button>
                </form>
            </div>
        </div></div>
    </div>

    <!-- History Card -->
    <div class="row mt-4"><div class="col-12"><div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Backup & Restore History</h5>
            <a href="<?= URLROOT ?>/admin/printAuditHistory" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-print me-1"></i> Generate Report</a>
        </div>
        <div class="card-body"><div class="table-responsive" style="max-height: 400px;">
            <table class="table table-striped table-hover">
                <thead><tr><th>Timestamp</th><th>Action</th><th>Performed By</th><th>Status</th><th>Details</th></tr></thead>
                <tbody>
                    <?php if (empty($data['logs'])): ?>
                        <tr><td colspan="5" class="text-center text-muted">No history found.</td></tr>
                    <?php else: foreach($data['logs'] as $log): ?>
                        <tr>
                            <td><?= date('M d, Y, h:i A', strtotime($log->timestamp)) ?></td>
                            <td>
                                <?php if($log->action_type == 'backup_created'): ?> <span class="badge bg-info">Backup Created</span>
                                <?php elseif($log->action_type == 'restore_attempted'): ?> <span class="badge bg-warning text-dark">Restore Attempted</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($log->username ?? 'Unknown') ?></td>
                            <td>
                                <?php if($log->status == 'success'): ?> <span class="badge bg-success">Success</span>
                                <?php else: ?> <span class="badge bg-danger">Failure</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.85em;"><?= htmlspecialchars($log->details ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div></div>
    </div></div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const downloadBtn = document.getElementById('downloadBtn');
    if(downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.dataset.url;
            Swal.fire({ title: 'Generating Backup', html: 'Please wait...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            fetch(url)
                .then(response => {
                    if (!response.ok) { throw new Error('Network response was not ok.'); }
                    const disposition = response.headers.get('content-disposition');
                    let filename = 'backup.sql';
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) { filename = matches[1].replace(/['"]/g, ''); }
                    }
                    return response.blob().then(blob => ({ blob, filename }));
                })
                .then(({ blob, filename }) => {
                    const a = document.createElement('a');
                    a.href = window.URL.createObjectURL(blob);
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    Swal.close();
                    
                    Swal.fire({
                        title: 'Success!',
                        text: 'Backup downloaded. The page will now refresh.',
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true,
                        willClose: () => { location.reload(); }
                    });
                })
                .catch(error => { Swal.fire('Error', 'Could not generate backup file. ' + error.message, 'error'); });
        });
    }

    const restoreForm = document.getElementById('restoreForm');
    if (restoreForm) {
        restoreForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const fileInput = document.getElementById('backup_file');
            if (fileInput.files.length === 0) { Swal.fire('No File Selected', 'Please select a .sql file.', 'error'); return; }
            Swal.fire({
                title: 'ARE YOU SURE?',
                html: "This will <b>ERASE</b> all current data. To confirm, type <strong>OVERWRITE</strong> below.",
                icon: 'warning', input: 'text', showCancelButton: true,
                confirmButtonText: 'Yes, Restore Now!', confirmButtonColor: '#d33',
                preConfirm: (val) => { if (val !== 'OVERWRITE') { Swal.showValidationMessage('Incorrect confirmation text.'); } },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => { if (result.isConfirmed) { uploadAndRestore(new FormData(restoreForm)); } });
        });
    }

    function uploadAndRestore(formData) {
        Swal.fire({
            title: 'Uploading...',
            html: `<div class="swal2-progress-bar"><div id="upload-progress-fill" class="swal2-progress-fill">0%</div></div>`,
            allowOutsideClick: false, showConfirmButton: false,
            willOpen: () => {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        const fill = document.getElementById('upload-progress-fill');
                        if(fill) { fill.style.width = percent + '%'; fill.textContent = percent + '%'; }
                    }
                });
                xhr.addEventListener('load', () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        Swal.update({ title: 'Processing Restore...', html: 'Database is being restored. Please wait.' });
                        location.reload();
                    } else { Swal.fire('Upload Failed', 'An error occurred during upload.', 'error'); }
                });
                xhr.addEventListener('error', () => { Swal.fire('Network Error', 'Could not upload file.', 'error'); });
                xhr.open('POST', restoreForm.action, true);
                xhr.send(formData);
            }
        });
    }
});
</script>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>