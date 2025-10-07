<?php require APPROOT . '/views/includes/admin_header.php'; ?>
<style>
    /* --- General Styles --- */
    .table-wrapper { max-height: 400px; overflow-y: auto; }
    .table thead th { position: sticky; top: 0; z-index: 1; background: var(--maroon, #800000); color: #fff; }
    .action-btn { min-width: 150px; }
    .badge { font-size: 0.8em; }
    .copy-btn i { pointer-events: none; }

    /* =========================================
     1. MAROON THEME VARIABLES
     ========================================= */
    :root {
      --g-maroon: #800000;
      --g-maroon-rgb: 128, 0, 0;
      --g-maroon-dark: #6a0000;
      --bs-primary: var(--g-maroon);
      --bs-primary-rgb: var(--g-maroon-rgb);
      --bs-primary-text: #ffffff;
      --bs-primary-bg-subtle: #fbe6e6;
      --bs-primary-border-subtle: #f3cccc;
      --bs-link-color: var(--g-maroon-dark);
      --bs-link-color-rgb: 106, 0, 0;
      --bs-link-hover-color: var(--g-maroon-dark);
      --bs-btn-focus-shadow-rgb: var(--g-maroon-rgb);
    }

    /* =========================================
     2. MODAL MAROON THEME STYLES
     ========================================= */
    .modal { padding-top: var(--header-h, 56px); }
    .modal-dialog { width: 95vw; max-width: 900px; margin-top: 1rem; margin-bottom: 1rem; }
    .modal-body { max-height: calc(85vh - 200px); overflow-y: auto; }
    .modal-content { border: 2px solid var(--g-maroon-dark); border-radius: 0.5rem; }
    .modal-header { background-color: var(--g-maroon) !important; color: #fff; border-bottom: 2px solid var(--g-maroon-dark); }
    .modal-body hr { border-top: 1px solid var(--bs-primary-border-subtle); }
    .modal-body .input-group-text { background-color: var(--bs-primary-bg-subtle); border-color: var(--bs-primary-border-subtle); font-weight: 500; color: var(--g-maroon-dark); }
    .modal-body h6 { color: var(--g-maroon-dark); font-weight: bold; }
    .modal-footer { background-color: var(--bs-primary-bg-subtle); border-top: 1px solid var(--bs-primary-border-subtle); }
    .btn-primary { background-color: var(--g-maroon); border-color: var(--g-maroon); }
    .btn-primary:hover { background-color: var(--g-maroon-dark); border-color: var(--g-maroon-dark); }
    .modal-footer .btn-secondary { background-color: #fff; border-color: var(--g-maroon); color: var(--g-maroon); }
    .modal-footer .btn-secondary:hover { background-color: var(--g-maroon); color: #fff; }
</style>

<div class="container-fluid py-4">
    <h2 class="mb-4">Renewals Management</h2>

    <div class="card mb-5">
        <div class="card-header" style="background-color: var(--g-maroon-dark, #6a1818); color: #fff;">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>All Active Rentals</h5>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Plot</th>
                            <th>Deceased Name</th>
                            <th>IRH Name</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="for-renewal-table">
                        <tr><td colspan="6" class="text-center text-muted">Calculating...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Renewal History</h5>
        <a href="<?= URLROOT ?>/admin/printRenewalHistory" target="_blank" class="btn btn-sm btn-secondary">
            <i class="fas fa-print me-1"></i> Generate Report
        </a>
    </div>
    <div class="card-body">
        <div class="table-wrapper">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Payer Name</th>
                        <th>New Expiry</th>
                        <th>Processed By</th>
                        <th>Receipt Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['history'])): ?>
                        <tr><td colspan="7" class="text-center text-muted">No renewal history found.</td></tr>
                    <?php else: foreach($data['history'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item->transaction_id ?? '') ?></td>
                            <td><?= date('M d, Y', strtotime($item->payment_date)) ?></td>
                            <td>₱ <?= number_format($item->payment_amount ?? 0, 2) ?></td>
                            <td><?= htmlspecialchars($item->payer_name ?? 'N/A') ?></td>
                            <td><?= date('M d, Y', strtotime($item->new_expiry_date)) ?></td>
                            <td><?= htmlspecialchars($item->processed_by ?? '') ?></td>
                            <td><?= htmlspecialchars($item->receipt_email_status ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="renewalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Process Renewal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="renewalForm">
                    <input type="hidden" id="modal_burial_id" name="burial_id">
                    <input type="hidden" id="modal_plot_id" name="plot_id">
                    
                    <h6 class="mb-3">Burial Details</h6>
                    <p><strong>Deceased:</strong> <span id="modal_deceased_name"></span></p>
                    <p><strong>Current Expiry Date:</strong> <span id="modal_expiry_date"></span></p>
                    <hr>
                    
                    <h6 class="mb-3">Interment Right Holder (IRH) Details</h6>
                    <div class="input-group mb-2">
                        <span class="input-group-text" style="width: 120px;">IRH Name</span>
                        <input type="text" id="modal_irh_name" class="form-control" readonly style="background-color: #e9ecef;">
                        <button class="btn btn-outline-secondary copy-btn" type="button" data-copy-source="modal_irh_name" data-copy-target="payer_name" title="Copy to Payer's Name"><i class="fas fa-copy"></i> Copy</button>
                    </div>
                    <div class="input-group mb-4">
                        <span class="input-group-text" style="width: 120px;">IRH Email</span>
                        <input type="text" id="modal_irh_email" class="form-control" readonly style="background-color: #e9ecef;">
                        <button class="btn btn-outline-secondary copy-btn" type="button" data-copy-source="modal_irh_email" data-copy-target="payer_email" title="Copy to Payer's Email"><i class="fas fa-copy"></i> Copy</button>
                    </div>
                    <hr>
                    
                    <h6 class="mb-3">Payer's Information</h6>
                    <div class="mb-3">
                        <label for="payer_name" class="form-label">Payer's Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payer_name" name="payer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="payer_email" class="form-label">Payer's Email (for receipt)</label>
                        <input type="email" class="form-control" id="payer_email" name="payer_email">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" min="0" required value="5000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" id="vacateBtn">Do Not Renew & Vacate Plot</button>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmRenewalBtn">Confirm & Renew</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const allBurials = <?= json_encode($data['all_burials'] ?? []) ?>;
    const renewalModal = new bootstrap.Modal(document.getElementById('renewalModal'));
    const modalEl = document.getElementById('renewalModal');
    const form = document.getElementById('renewalForm');
    const paymentDateInput = document.getElementById('payment_date');
    const renewalTableBody = document.getElementById('for-renewal-table');

    const themedSwal = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-primary mx-2',
            denyButton: 'btn btn-info mx-2',
            cancelButton: 'btn btn-secondary mx-2'
        },
        buttonsStyling: false
    });

    const e = (str) => { if (str === null || str === undefined) return ''; const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }; return String(str).replace(/[&<>"']/g, (m) => map[m]); };
    const formatDate = (dateString) => { if (!dateString) return ''; const date = new Date(dateString); return isNaN(date) ? '' : date.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }); };
    
    const createTableRow = (item, status) => {
        const deceasedName = e(((item.deceased_first_name || '') + ' ' + (item.deceased_last_name || '')).trim());
        const plotLabel = e(((item.block_title || 'N/A') + ' - ' + (item.plot_number || 'N/A')).trim());

        return `
            <tr data-burial-id="${e(item.burial_id)}"
                data-plot-id="${e(item.plot_id)}"
                data-deceased-name="${deceasedName}"
                data-expiry-date="${e(item.expiry_date)}">
                <td>${plotLabel}</td>
                <td>${deceasedName}</td>
                <td>${e(item.interment_full_name)}</td>
                <td>${formatDate(item.expiry_date)}</td>
                <td><span class="badge ${status.badge}">${status.text}</span></td>
                <td class="text-center">
                    <button class="btn btn-primary btn-sm action-btn process-renewal-btn">Process Renewal</button>
                </td>
            </tr>
        `;
    };
    
    const calculateAndRender = () => {
        const recordsToDisplay = [];
        const now = new Date();
        now.setHours(0, 0, 0, 0);

        if (!Array.isArray(allBurials)) {
             renewalTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error: Invalid data received from server.</td></tr>';
             console.error("Data received is not an array:", allBurials);
             return;
        }

        allBurials.forEach(item => {
            if (!item.expiry_date) return;
            const expiry = new Date(item.expiry_date);
            expiry.setHours(0, 0, 0, 0);
            const diffTime = expiry.getTime() - now.getTime();
            const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
            
            let status;
            if (diffDays < 0) { status = { text: `Expired ${Math.abs(diffDays)} days ago`, badge: 'bg-danger' };
            } else if (diffDays === 0) { status = { text: 'Expires Today', badge: 'bg-danger' };
            } else if (diffDays <= 30) { status = { text: `Expires in ${diffDays} days`, badge: 'bg-warning text-dark' };
            } else { status = { text: `Expires in ${diffDays} days`, badge: 'bg-secondary' }; }
            recordsToDisplay.push({ ...item, status: status });
        });
        
        if (recordsToDisplay.length > 0) {
            recordsToDisplay.sort((a, b) => new Date(a.expiry_date) - new Date(b.expiry_date));
            renewalTableBody.innerHTML = recordsToDisplay.map(rec => createTableRow(rec, rec.status)).join('');
        } else {
            renewalTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No active burial records found.</td></tr>';
        }
    };

    renewalTableBody.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('process-renewal-btn')) {
            const row = e.target.closest('tr');
            const burialId = row.dataset.burialId;
            const record = allBurials.find(b => b.burial_id === burialId);
            
            form.reset();

            if (record) {
                document.getElementById('modal_burial_id').value = record.burial_id;
                document.getElementById('modal_plot_id').value = record.plot_id;
                document.getElementById('modal_deceased_name').textContent = row.dataset.deceasedName;
                document.getElementById('modal_expiry_date').textContent = formatDate(record.expiry_date);
                document.getElementById('modal_irh_name').value = record.interment_full_name || '';
                document.getElementById('modal_irh_email').value = record.interment_email || '';
                document.getElementById('payer_name').value = record.interment_full_name || '';
                document.getElementById('payer_email').value = record.interment_email || '';
            }
            
            paymentDateInput.valueAsDate = new Date();
            renewalModal.show();
        }
    });

    modalEl.addEventListener('click', (e) => {
        if (e.target.classList.contains('copy-btn')) {
            const sourceId = e.target.dataset.copySource;
            const targetId = e.target.dataset.copyTarget;
            const sourceEl = document.getElementById(sourceId);
            const targetEl = document.getElementById(targetId);
            if (sourceEl && targetEl) {
                targetEl.value = sourceEl.value;
            }
        }
    });

    const handleFormSubmit = async (url, formData) => {
        themedSwal.fire({
            title: 'Processing...',
            html: 'Please wait while the transaction is being saved.',
            allowOutsideClick: false,
            didOpen: () => { themedSwal.showLoading(); }
        });

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: new URLSearchParams(formData)
            });
            const result = await response.json();

            if (result.ok) {
                if (url.includes('processRenewal')) {
                    themedSwal.fire({
                        icon: 'success',
                        title: 'Renewal Successful!',
                        html: `Transaction has been processed.<br>Email Status: <strong>${result.email_status || 'N/A'}</strong>`,
                        showDenyButton: true,
                        confirmButtonText: 'Done',
                        denyButtonText: `<i class="fas fa-print"></i> Print New Contract`,
                        denyButtonColor: '#3085d6',
                    }).then((action) => {
                        if (action.isDenied) {
                            window.open(`<?= URLROOT ?>/admin/printContract/${result.burial_id}?autoprint=1`, '_blank');
                            setTimeout(() => location.reload(), 500);
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    themedSwal.fire({
                        icon: 'success',
                        title: 'Action Successful!',
                        html: `${result.message}<br>Email Status: <strong>${result.email_status || 'N/A'}</strong>`,
                    }).then(() => location.reload());
                }
            } else {
                themedSwal.fire('Error', result.message || 'An error occurred.', 'error');
            }
        } catch (error) {
            themedSwal.fire('Network Error', 'Could not connect to the server. Please try again.', 'error');
        }
    };

    document.getElementById('confirmRenewalBtn').addEventListener('click', () => {
        if (!form.checkValidity()) { form.reportValidity(); return; }
        handleFormSubmit('<?= URLROOT ?>/admin/processRenewal', new FormData(form));
    });

    document.getElementById('vacateBtn').addEventListener('click', async () => {
        const burialId = document.getElementById('modal_burial_id').value;
        const deceasedName = document.getElementById('modal_deceased_name').textContent;
        const result = await themedSwal.fire({
            title: 'Are you sure?',
            text: `This will archive the record for ${deceasedName} and mark their plot as vacant. This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, vacate the plot!',
            customClass: {
                confirmButton: 'btn btn-danger mx-2',
                cancelButton: 'btn btn-secondary mx-2'
            }
        });
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('burial_id', burialId);
            // --- ITO ANG INAYOS ---
            // Tinanggal ang sobrang 'new FormData()'
            handleFormSubmit('<?= URLROOT ?>/admin/processVacate', formData);
        }
    });

    calculateAndRender();
});
</script>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>