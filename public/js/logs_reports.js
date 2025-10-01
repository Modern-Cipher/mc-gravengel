document.addEventListener('DOMContentLoaded', () => {
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  // ---------- helpers ----------
  const fmtMoney = n =>
    (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const fmtDateTime = s => {
    if (!s) return '—';
    const d = new Date((s + '').replace(' ', 'T'));
    return isNaN(d) ? s : d.toLocaleString();
  };

  const esc = v => {
    if (v === null || v === undefined) return '—';
    return String(v)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  };

  const parseJSONSafe = async (res) => {
    try { return await res.json(); }
    catch (e) {
      const txt = await res.text().catch(() => '');
      console.error('Non-JSON response:', txt);
      Swal.fire('Error', 'Server returned an unexpected response. Please check logs.', 'error');
      return { ok:false, rows:[] };
    }
  };

  // tooltips on static icons
  $$('.fa-gear, [data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

  // ---------- Activity Logs (view modal) ----------
  async function loadActivity() {
    const params = new URLSearchParams({
      from: $('#act-from').value || '',
      to:   $('#act-to').value || '',
      q:    $('#act-search').value || ''
    });

    const res  = await fetch(`${window.URLROOT}/admin/fetchActivityLogs?` + params.toString(), { credentials:'same-origin' });
    const data = await parseJSONSafe(res);

    const tbody = $('#tbl-activity tbody');
    tbody.innerHTML = '';

    if (!data.ok || !Array.isArray(data.rows) || data.rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No data</td></tr>`;
      $('#act-count').textContent = '';
      return;
    }

    data.rows.forEach(r => {
      const tr = document.createElement('tr');

      const kindIcon =
        r.kind === 'create_burial' ? 'fa-circle-plus text-success' :
        r.kind === 'update_burial' ? 'fa-pen-to-square text-warning' :
        r.kind === 'login'         ? 'fa-right-to-bracket text-success' :
        r.kind === 'logout'        ? 'fa-right-from-bracket text-danger' :
                                     'fa-circle-info text-secondary';

      // keep object in dataset safely (base64)
      const raw = btoa(unescape(encodeURIComponent(JSON.stringify(r))));

      tr.innerHTML = `
        <td><span class="badge bg-secondary">${esc(r.staff_id) || '—'}</span></td>
        <td>${esc(r.username) || '—'}</td>
        <td>${esc(r.ts) || '—'}</td>
        <td><i class="fa-solid ${kindIcon} me-1"></i>${esc(r.action_text)}</td>
        <td class="text-center col-actions">
          <button class="btn btn-sm btn-outline-primary"
                  data-bs-toggle="tooltip" title="View details"
                  data-raw="${raw}">
            <i class="fa-solid fa-eye"></i>
          </button>
        </td>`;
      tbody.appendChild(tr);
    });

    // tooltips & modal openers
    $$('#tbl-activity [data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    $$('#tbl-activity button[data-raw]').forEach(btn => {
      btn.addEventListener('click', () => {
        try {
          const obj = JSON.parse(decodeURIComponent(escape(atob(btn.dataset.raw))));
          showActivityDetails(obj);
        } catch (e) {
          console.error('Decode error:', e);
          Swal.fire('Error', 'Unable to open details.', 'error');
        }
      });
    });

    $('#act-count').textContent = `${data.rows.length} record(s)`;
  }

  function showActivityDetails(o = {}) {
    const kindBadge =
      o.kind === 'create_burial' ? `<span class="badge bg-success"><i class="fa-solid fa-circle-plus me-1"></i>Create Burial</span>` :
      o.kind === 'update_burial' ? `<span class="badge bg-warning text-dark"><i class="fa-solid fa-pen-to-square me-1"></i>Update Burial</span>` :
      o.kind === 'login'         ? `<span class="badge bg-primary"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</span>` :
      o.kind === 'logout'        ? `<span class="badge bg-secondary"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</span>` :
                                   `<span class="badge bg-info text-dark"><i class="fa-solid fa-circle-info me-1"></i>Info</span>`;

    const html = `
      <div class="container text-start">
        <div class="row g-3">
          <div class="col-12">
            <h5 class="mb-1"><i class="fa-solid fa-clipboard-list me-2"></i>Activity Details</h5>
            <hr class="mt-2 mb-3"/>
          </div>

          <div class="col-md-6">
            <div class="list-group">
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-id-badge me-2"></i><strong>Staff ID</strong></div>
                <div>${esc(o.staff_id) || '—'}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-user me-2"></i><strong>Username</strong></div>
                <div>${esc(o.username) || '—'}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-clock me-2"></i><strong>Timestamp</strong></div>
                <div>${fmtDateTime(o.ts)}</div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="list-group">
              <div class="list-group-item">
                <div class="mb-1"><i class="fa-solid fa-align-left me-2"></i><strong>Action</strong></div>
                <div>${esc(o.action_text) || '—'}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-tag me-2"></i><strong>Type</strong></div>
                <div>${kindBadge}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

    Swal.fire({
      title: 'Activity',
      html,
      width: 800,
      showConfirmButton: true,
      confirmButtonText: 'Close'
    });
  }

  // ---------- Transaction Reports (view modal) ----------
  async function loadTransactions() {
    const params = new URLSearchParams({
      from: $('#trx-from').value || '',
      to:   $('#trx-to').value || '',
      q:    $('#trx-search').value || ''
    });

    const res  = await fetch(`${window.URLROOT}/admin/fetchTransactionReports?` + params.toString(), { credentials:'same-origin' });
    const data = await parseJSONSafe(res);

    const tbody = $('#tbl-transactions tbody');
    tbody.innerHTML = '';

    if (!data.ok || !Array.isArray(data.rows) || data.rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="14" class="text-center text-muted">No data</td></tr>`;
      $('#trx-count').textContent = '';
      return;
    }

    data.rows.forEach(row => {
      const tr = document.createElement('tr');
      const raw = btoa(unescape(encodeURIComponent(JSON.stringify(row))));
      tr.innerHTML = `
        <td><code>${esc(row.transaction_id) || '—'}</code></td>
        <td>${esc(row.burial_id) || '—'}</td>
        <td>${esc(row.block_title) || '—'}</td>
        <td>${esc(row.plot_number) || '—'}</td>
        <td>${esc(row.interment_full_name) || '—'} <small class="text-muted">(${esc(row.interment_relationship) || '—'})</small></td>
        <td>${esc(row.interment_address) || '—'}</td>
        <td>${esc(row.interment_contact_number) || '—'}</td>
        <td class="text-end">₱ ${fmtMoney(row.payment_amount)}</td>
        <td>${esc(row.rental_date) || '—'}</td>
        <td>${esc(row.expiry_date) || '—'}</td>
        <td>${esc(row.deceased_full_name) || '—'} <small class="text-muted">(${esc(row.sex) || '—'}/${esc(row.age) || '—'})</small></td>
        <td>${esc(row.grave_level) || '—'}/${esc(row.grave_type) || '—'}</td>
        <td>${esc(row.created_by_staff_id) || '—'} <small class="text-muted">(${esc(row.created_by_username) || '—'})</small></td>
        <td>${esc(row.created_at) || '—'}</td>
        <td class="text-center col-actions">
          <button class="btn btn-sm btn-outline-primary"
                  data-bs-toggle="tooltip" title="View details"
                  data-raw="${raw}">
            <i class="fa-solid fa-eye"></i>
          </button>
        </td>`;
      tbody.appendChild(tr);
    });

    $$('#tbl-transactions [data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    $$('#tbl-transactions button[data-raw]').forEach(btn => {
      btn.addEventListener('click', () => {
        try {
          const obj = JSON.parse(decodeURIComponent(escape(atob(btn.dataset.raw))));
          showTransactionDetails(obj);
        } catch (e) {
          console.error('Decode error:', e);
          Swal.fire('Error', 'Unable to open details.', 'error');
        }
      });
    });

    $('#trx-count').textContent = `${data.rows.length} record(s)`;
  }

  function showTransactionDetails(o = {}) {
    const html = `
      <div class="container text-start">
        <div class="row g-3">
          <div class="col-12">
            <h5 class="mb-1"><i class="fa-solid fa-receipt me-2"></i>Transaction Details</h5>
            <hr class="mt-2 mb-3"/>
          </div>

          <div class="col-md-6">
            <div class="list-group">
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-hashtag me-2"></i><strong>Transaction ID</strong></div>
                <div>${esc(o.transaction_id) || '—'}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-id-card me-2"></i><strong>Burial ID</strong></div>
                <div>${esc(o.burial_id) || '—'}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-vihara me-2"></i><strong>Plot</strong></div>
                <div>${esc(o.block_title) || '—'} / ${esc(o.plot_number) || '—'}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-peso-sign me-2"></i><strong>Payment</strong></div>
                <div>₱ ${fmtMoney(o.payment_amount)}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-calendar-check me-2"></i><strong>Rental Date</strong></div>
                <div>${fmtDateTime(o.rental_date)}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-hourglass-end me-2"></i><strong>Expiry Date</strong></div>
                <div>${fmtDateTime(o.expiry_date)}</div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="list-group">
              <div class="list-group-item">
                <div class="mb-1"><i class="fa-solid fa-user me-2"></i><strong>Interment Right Holder</strong></div>
                <div>${esc(o.interment_full_name) || '—'} <small class="text-muted">(${esc(o.interment_relationship) || '—'})</small></div>
              </div>
              <div class="list-group-item">
                <div class="mb-1"><i class="fa-solid fa-location-dot me-2"></i><strong>Address</strong></div>
                <div>${esc(o.interment_address) || '—'}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-phone me-2"></i><strong>Contact</strong></div>
                <div>${esc(o.interment_contact_number) || '—'}</div>
              </div>
              <div class="list-group-item">
                <div class="mb-1"><i class="fa-solid fa-cross me-2"></i><strong>Deceased</strong></div>
                <div>${esc(o.deceased_full_name) || '—'} <small class="text-muted">(${esc(o.sex) || '—'}/${esc(o.age) || '—'})</small></div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-layer-group me-2"></i><strong>Grave</strong></div>
                <div>${esc(o.grave_level) || '—'} / ${esc(o.grave_type) || '—'}</div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div><i class="fa-solid fa-user-tie me-2"></i><strong>Created By</strong></div>
                <div>${esc(o.created_by_staff_id) || '—'} <small class="text-muted">(${esc(o.created_by_username) || '—'})</small></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

    Swal.fire({
      title: 'Transaction',
      html,
      width: 900,
      showConfirmButton: true,
      confirmButtonText: 'Close'
    });
  }

  // ---------- filters + print ----------
  $('#act-filter')?.addEventListener('click', loadActivity);
  $('#act-reset')?.addEventListener('click', () => {
    $('#act-from').value=''; $('#act-to').value=''; $('#act-search').value='';
    loadActivity();
  });
  $('#act-search')?.addEventListener('input', () => {
    clearTimeout(window._act_t); window._act_t = setTimeout(loadActivity, 250);
  });

  $('#trx-filter')?.addEventListener('click', loadTransactions);
  $('#trx-reset')?.addEventListener('click', () => {
    $('#trx-from').value=''; $('#trx-to').value=''; $('#trx-search').value='';
    loadTransactions();
  });
  $('#trx-search')?.addEventListener('input', () => {
    clearTimeout(window._trx_t); window._trx_t = setTimeout(loadTransactions, 250);
  });

  $('#btn-print')?.addEventListener('click', () => window.print());

  // ---------- initial load ----------
  loadActivity();
  loadTransactions();
});
