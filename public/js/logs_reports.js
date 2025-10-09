document.addEventListener('DOMContentLoaded', () => {
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const MAROON_COLOR = '#800000';

    const debounce = (fn, ms = 350) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
    const fmtMoney = n => `₱${(Number(n||0)).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}`;
    const fmtDateTime = s => s ? new Date(s.replace(' ', 'T')).toLocaleString('en-US', {dateStyle:'medium',timeStyle:'short'}) : '—';
    const esc = v => v === null || v === undefined ? '—' : String(v).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
    
    // Initialize tooltips and modals
    [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].forEach(el => new bootstrap.Tooltip(el));
    const activityDetailsModal = new bootstrap.Modal($('#activityDetailsModal'));
    const transactionDetailsModal = new bootstrap.Modal($('#transactionDetailsModal'));

    const fetchAndRender = async (url, params, tableBody, rowRenderer) => {
        tableBody.innerHTML = `<tr><td colspan="100%" class="text-center text-muted py-5"><div class="spinner-border spinner-border-sm" role="status"></div></td></tr>`;
        try {
            const res = await fetch(`${window.URLROOT}${url}?${params.toString()}`);
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            const data = await res.json();
            
            tableBody.innerHTML = '';
            if (!data.ok || !data.rows || data.rows.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="100%" class="text-center text-muted py-4">No data found for the selected filters.</td></tr>`;
                return;
            }
            data.rows.forEach(row => {
                const tr = rowRenderer(row);
                tableBody.appendChild(tr);
                $$('[data-bs-toggle="tooltip"]', tr).forEach(el => new bootstrap.Tooltip(el));
            });
        } catch (error) {
            console.error('Fetch error:', error);
            tableBody.innerHTML = `<tr><td colspan="100%" class="text-center text-danger py-4">Failed to load data. Please check connection.</td></tr>`;
        }
    };

    // --- Activity Logs ---
    const actTbody = $('#tbl-activity tbody');
    const renderActivityRow = r => {
        const tr = document.createElement('tr');
        const actionIcons = {
            'create_burial': 'fa-circle-plus text-success', 'update_burial': 'fa-pen-to-square text-warning',
            'process_renewal': 'fa-file-invoice-dollar text-primary', 'archive_burial': 'fa-box-archive text-secondary',
            'restore_burial': 'fa-trash-can-arrow-up text-info', 'delete_burial': 'fa-trash-can text-danger',
            'login': 'fa-right-to-bracket', 'logout': 'fa-right-from-bracket text-muted',
            'vacate_plot': 'fa-person-walking-dashed-line-arrow-right text-danger'
        };
        const icon = actionIcons[r.action_type] || 'fa-circle-info';
        
        tr.innerHTML = `
            <td>${esc(r.username)}</td>
            <td><code><i class="fa-solid ${icon} fa-fw me-2"></i>${esc(r.action_type)}</code></td>
            <td>${esc(r.details)}</td>
            <td>${fmtDateTime(r.timestamp)}</td>
            <td class="text-center">
                <i class="fas fa-eye action-icon view-log-btn" data-bs-toggle="tooltip" title="View Details"></i>
            </td>`;
        
        tr.querySelector('.view-log-btn').addEventListener('click', () => showActivityDetails(r));
        return tr;
    };
    const loadActivity = debounce(() => {
        const params = new URLSearchParams({ from: $('#act-from').value, to: $('#act-to').value, q: $('#act-search').value });
        fetchAndRender('/admin/fetchActivityLogs', params, actTbody, renderActivityRow);
    });
    $('#act-from').addEventListener('change', loadActivity);
    $('#act-to').addEventListener('change', loadActivity);
    $('#act-search').addEventListener('input', loadActivity);
    $('#act-reset').addEventListener('click', () => { 
        $('#act-from').value=''; $('#act-to').value=''; $('#act-search').value=''; loadActivity(); 
    });

    function showActivityDetails(log) {
        const body = $('#activityDetailsBody');
        body.innerHTML = `
            <dl class="row">
                <dt class="col-sm-3">User</dt><dd class="col-sm-9">${esc(log.username)}</dd>
                <dt class="col-sm-3">Action Type</dt><dd class="col-sm-9"><code>${esc(log.action_type)}</code></dd>
                <dt class="col-sm-3">Details</dt><dd class="col-sm-9">${esc(log.details)}</dd>
                <dt class="col-sm-3">IP Address</dt><dd class="col-sm-9">${esc(log.ip_address)}</dd>
                <dt class="col-sm-3">Timestamp</dt><dd class="col-sm-9">${fmtDateTime(log.timestamp)}</dd>
            </dl>`;
        activityDetailsModal.show();
    }

    // --- Transaction Reports ---
    const trxTbody = $('#tbl-transactions tbody');
    const renderTransactionRow = r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><code>${esc(r.transaction_id)}</code></td><td>${esc(r.burial_id)}</td>
            <td>${esc(r.block_title)} - ${esc(r.plot_number)}</td>
            <td>${esc(r.interment_full_name)}<small class="text-muted d-block">(${esc(r.interment_relationship)})</small></td>
            <td>${esc(r.interment_contact_number)}</td><td>${esc(r.interment_email)}</td>
            <td class="text-end">${fmtMoney(r.payment_amount)}</td><td>${fmtDateTime(r.rental_date)}</td>
            <td>${fmtDateTime(r.expiry_date)}</td><td>${esc(r.deceased_full_name)}<small class="text-muted d-block">(${esc(r.sex)}/${esc(r.age)})</small></td>
            <td>${esc(r.grave_level)}/${esc(r.grave_type)}</td><td>${esc(r.created_by_username)}</td>
            <td class="text-center">
                <i class="fas fa-eye action-icon view-trx-btn" data-bs-toggle="tooltip" title="View Details"></i>
            </td>`;
        
        tr.querySelector('.view-trx-btn').addEventListener('click', () => showTransactionDetails(r));
        return tr;
    };
    const loadTransactions = debounce(() => {
        const params = new URLSearchParams({ from: $('#trx-from').value, to: $('#trx-to').value, q: $('#trx-search').value });
        fetchAndRender('/admin/fetchTransactionReports', params, trxTbody, renderTransactionRow);
    });
    $('#trx-from').addEventListener('change', loadTransactions);
    $('#trx-to').addEventListener('change', loadTransactions);
    $('#trx-search').addEventListener('input', loadTransactions);
    $('#trx-reset').addEventListener('click', () => { 
        $('#trx-from').value=''; $('#trx-to').value=''; $('#trx-search').value=''; loadTransactions(); 
    });
    
    function showTransactionDetails(r) {
        const body = $('#transactionDetailsBody');
        body.innerHTML = `
            <div class="row">
                <div class="col-lg-6">
                    <h5>Deceased Information</h5>
                    <dl class="row">
                        <dt class="col-sm-4 detail-modal-label">Full Name</dt><dd class="col-sm-8">${esc(r.deceased_full_name)}</dd>
                        <dt class="col-sm-4 detail-modal-label">Age / Sex</dt><dd class="col-sm-8">${esc(r.age)} / ${esc(r.sex)}</dd>
                    </dl>
                    <hr>
                    <h5>Plot Information</h5>
                    <dl class="row">
                        <dt class="col-sm-4 detail-modal-label">Location</dt><dd class="col-sm-8">${esc(r.block_title)} - ${esc(r.plot_number)}</dd>
                        <dt class="col-sm-4 detail-modal-label">Grave Details</dt><dd class="col-sm-8">${esc(r.grave_level)} / ${esc(r.grave_type)}</dd>
                    </dl>
                </div>
                <div class="col-lg-6">
                    <h5>Interment & Payment</h5>
                    <dl class="row">
                        <dt class="col-sm-4 detail-modal-label">Responsible Head</dt><dd class="col-sm-8">${esc(r.interment_full_name)} (${esc(r.interment_relationship)})</dd>
                        <dt class="col-sm-4 detail-modal-label">Contact</dt><dd class="col-sm-8">${esc(r.interment_contact_number)}</dd>
                        <dt class="col-sm-4 detail-modal-label">Email</dt><dd class="col-sm-8">${esc(r.interment_email)}</dd>
                        <dt class="col-sm-4 detail-modal-label">Payment Amount</dt><dd class="col-sm-8 fw-bold">${fmtMoney(r.payment_amount)}</dd>
                    </dl>
                </div>
            </div>
            <hr>
            <h5>Transaction Details</h5>
            <dl class="row">
                <dt class="col-sm-3 detail-modal-label">Transaction ID</dt><dd class="col-sm-9"><code>${esc(r.transaction_id)}</code></dd>
                <dt class="col-sm-3 detail-modal-label">Burial ID</dt><dd class="col-sm-9"><code>${esc(r.burial_id)}</code></dd>
                <dt class="col-sm-3 detail-modal-label">Rental Date</dt><dd class="col-sm-9">${fmtDateTime(r.rental_date)}</dd>
                <dt class="col-sm-3 detail-modal-label">Expiry Date</dt><dd class="col-sm-9">${fmtDateTime(r.expiry_date)}</dd>
                <dt class="col-sm-3 detail-modal-label">Processed By</dt><dd class="col-sm-9">${esc(r.created_by_username)} (Staff ID: ${esc(r.created_by_staff_id)})</dd>
                <dt class="col-sm-3 detail-modal-label">Recorded On</dt><dd class="col-sm-9">${fmtDateTime(r.created_at)}</dd>
            </dl>`;
        transactionDetailsModal.show();
    }

    // --- [UPDATED] Professional Printing ---
    $('#btn-print').addEventListener('click', () => {
        const activeTab = $('.tab-pane.active');
        const table = $('table', activeTab);
        const title = $('button.nav-link.active').innerText.trim();
        if (!table) return;

        const visibleRows = $$('tbody tr', table).filter(tr => tr.style.display !== 'none' && !tr.querySelector('.spinner-border'));
        if (visibleRows.length === 0 || table.querySelector('.text-danger')) {
            Swal.fire({icon: 'info', title: 'No Data', text: 'There are no records to print for the current view.', confirmButtonColor: MAROON_COLOR});
            return;
        }
        
        const clonedTable = table.cloneNode(true);
        $$('thead th:last-child, tbody td:last-child', clonedTable).forEach(el => el.remove());

        const reportHtml = clonedTable.outerHTML;
        const iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:absolute;width:0;height:0;border:0;';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <html><head><title>${esc(title)} Report</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { 
                    font-family: sans-serif; 
                    -webkit-print-color-adjust: exact; 
                }
                .table { 
                    font-size: 8pt; /* Smaller font for print */
                    width: 100%;
                    border-collapse: collapse;
                } 
                .table th, .table td {
                    padding: 4px 6px; /* Reduced padding */
                    white-space: nowrap; /* CRITICAL: Prevents text wrapping */
                    border: 1px solid #dee2e6;
                }
                .table thead th {
                    background-color: #800000 !important; /* Maroon color */
                    color: #fff !important;
                }
                .table small { display: none; } /* Hide small text in print */
                @page { 
                    size: landscape; 
                    margin: 0.4in; /* Smaller margin */
                }
            </style></head><body>
            <h4 class="mb-3">${esc(title)} Report</h4>
            <p class="text-muted small">Generated on: ${new Date().toLocaleString()}</p>
            ${reportHtml}
            </body></html>`);
        doc.close();
        
        iframe.onload = () => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            setTimeout(() => document.body.removeChild(iframe), 1000);
        };
    });
    
    // Initial Load
    loadActivity();
    loadTransactions();
});