document.addEventListener('DOMContentLoaded', () => {
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const MAROON_COLOR = '#7b1d1d';
  const ROOT = window.URLROOT || '';

  // helpers
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
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Server returned an unexpected response. Please check logs.',
        confirmButtonColor: MAROON_COLOR
      });
      return { ok:false, rows:[] };
    }
  };

  // tooltips on static icons
  $$('.fa-gear, [data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

  // ========== Activity Logs (STAFF endpoints) ==========
  async function loadActivity() {
    const params = new URLSearchParams({
      from: $('#act-from').value || '',
      to:   $('#act-to').value   || '',
      q:    $('#act-search').value || ''
    });

    const res  = await fetch(`${ROOT}/staff/fetchActivityLogs?` + params.toString(), { credentials:'same-origin' });
    const data = await parseJSONSafe(res);

    const tbody = $('#tbl-activity tbody');
    tbody.innerHTML = '';

    if (!data.ok || !Array.isArray(data.rows) || data.rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No data found</td></tr>`;
      $('#act-count').textContent = '0 records';
      return;
    }

    data.rows.forEach(r => {
      const tr = document.createElement('tr');

      const kindIcon =
        r.kind === 'create_burial' ? 'fa-circle-plus text-success' :
        r.kind === 'update_burial' ? 'fa-pen-to-square text-warning' :
        r.kind === 'login'         ? 'fa-right-to-bracket text-primary' :
        r.kind === 'logout'        ? 'fa-right-from-bracket text-danger' :
                                     'fa-circle-info text-secondary';

      const raw = btoa(unescape(encodeURIComponent(JSON.stringify(r))));

      tr.innerHTML = `
        <td><span class="badge bg-dark text-white">${esc(r.staff_id) || '—'}</span></td>
        <td>${esc(r.username) || '—'}</td>
        <td>${fmtDateTime(r.ts)}</td>
        <td><i class="fa-solid ${kindIcon} me-2"></i>${esc(r.action_text)}</td>
        <td class="text-center col-actions">
          <button class="btn btn-sm btn-outline-primary"
                  data-bs-toggle="tooltip" title="View details"
                  data-raw="${raw}">
            <i class="fa-solid fa-eye"></i>
          </button>
        </td>`;
      tbody.appendChild(tr);
    });

    $$('#tbl-activity [data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    $$('#tbl-activity button[data-raw]').forEach(btn => {
      btn.addEventListener('click', () => {
        try {
          const obj = JSON.parse(decodeURIComponent(escape(atob(btn.dataset.raw))));
          showActivityDetails(obj);
        } catch (e) {
          console.error('Decode error:', e);
          Swal.fire({icon:'error', title:'Error', text:'Unable to open details.', confirmButtonColor: MAROON_COLOR});
        }
      });
    });

    $('#act-count').textContent = `${data.rows.length} record(s)`;
  }

  function showActivityDetails(o = {}) {
    const kindBadge =
      o.kind === 'create_burial' ? `<span class="badge bg-success"><i class="fa-solid fa-circle-plus me-1"></i>Create Burial</span>` :
      o.kind === 'update_burial' ? `<span class="badge bg-warning text-dark"><i class="fa-solid fa-pen-to-square me-1"></i>Update Burial</span>` :
      o.kind === 'login'         ? `<span class="badge" style="background-color:${MAROON_COLOR};"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</span>` :
      o.kind === 'logout'        ? `<span class="badge bg-secondary"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</span>` :
                                   `<span class="badge bg-info text-dark"><i class="fa-solid fa-circle-info me-1"></i>Info</span>`;

    Swal.fire({
      html: `
        <div class="container text-start" style="font-size:14px;">
          <h5 class="mb-3 text-start" style="color:${MAROON_COLOR};"><i class="fa-solid fa-clipboard-list me-2"></i>Activity Details</h5>
          <hr class="mt-0 mb-3"/>
          <div class="row g-3">
            <div class="col-md-6">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-id-badge fa-fw me-2 text-muted"></i>Staff ID</span> <strong>${esc(o.staff_id) || '—'}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-user fa-fw me-2 text-muted"></i>Username</span> <strong>${esc(o.username) || '—'}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-clock fa-fw me-2 text-muted"></i>Timestamp</span> <strong>${fmtDateTime(o.ts)}</strong></li>
              </ul>
            </div>
            <div class="col-md-6">
              <ul class="list-group">
                <li class="list-group-item">
                  <div class="mb-1"><i class="fa-solid fa-align-left fa-fw me-2 text-muted"></i><strong>Action</strong></div>
                  <div>${esc(o.action_text) || '—'}</div>
                </li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-tag fa-fw me-2 text-muted"></i>Type</span> <span>${kindBadge}</span></li>
              </ul>
            </div>
          </div>
        </div>`,
      width: 800,
      showConfirmButton: true,
      confirmButtonText: 'Close',
      confirmButtonColor: MAROON_COLOR
    });
  }

  // ========== Transaction Reports (STAFF endpoints) ==========
  async function loadTransactions() {
    const params = new URLSearchParams({
      from: $('#trx-from').value || '',
      to:   $('#trx-to').value   || '',
      q:    $('#trx-search').value || ''
    });

    const res  = await fetch(`${ROOT}/staff/fetchTransactionReports?` + params.toString(), { credentials:'same-origin' });
    const data = await parseJSONSafe(res);

    const tbody = $('#tbl-transactions tbody');
    tbody.innerHTML = '';

    if (!data.ok || !Array.isArray(data.rows) || data.rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="16" class="text-center text-muted py-4">No data found</td></tr>`;
      $('#trx-count').textContent = '0 records';
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
        <td>${esc(row.interment_full_name) || '—'} <small class="text-muted d-block">(${esc(row.interment_relationship) || '—'})</small></td>
        <td>${esc(row.interment_address) || '—'}</td>
        <td>${esc(row.interment_contact_number) || '—'}</td>
        <td>${esc(row.interment_email) || '—'}</td>
        <td class="text-end">₱ ${fmtMoney(row.payment_amount)}</td>
        <td>${fmtDateTime(row.rental_date)}</td>
        <td>${fmtDateTime(row.expiry_date)}</td>
        <td>${esc(row.deceased_full_name) || '—'} <small class="text-muted d-block">(${esc(row.sex) || '—'}/${esc(row.age) || '—'})</small></td>
        <td>${esc(row.grave_level) || '—'}/${esc(row.grave_type) || '—'}</td>
        <td>${esc(row.created_by_staff_id) || '—'} <small class="text-muted d-block">(${esc(row.created_by_username) || '—'})</small></td>
        <td>${fmtDateTime(row.created_at)}</td>
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
          Swal.fire({icon:'error', title:'Error', text:'Unable to open details.', confirmButtonColor: MAROON_COLOR});
        }
      });
    });

    $('#trx-count').textContent = `${data.rows.length} record(s)`;
  }

  function showTransactionDetails(o = {}) {
    Swal.fire({
      html: `
        <div class="container text-start" style="font-size:14px;">
          <h5 class="mb-3 text-start" style="color:${MAROON_COLOR};"><i class="fa-solid fa-receipt me-2"></i>Transaction Details</h5>
          <hr class="mt-0 mb-3"/>
          <div class="row g-3">
            <div class="col-md-6">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-hashtag fa-fw me-2 text-muted"></i>Transaction ID</span> <strong>${esc(o.transaction_id) || '—'}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-id-card fa-fw me-2 text-muted"></i>Burial ID</span> <strong>${esc(o.burial_id) || '—'}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-vihara fa-fw me-2 text-muted"></i>Plot</span> <strong>${esc(o.block_title) || '—'} / ${esc(o.plot_number) || '—'}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-peso-sign fa-fw me-2 text-muted"></i>Payment</span> <strong>₱ ${fmtMoney(o.payment_amount)}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-calendar-check fa-fw me-2 text-muted"></i>Rental Date</span> <strong>${fmtDateTime(o.rental_date)}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-hourglass-end fa-fw me-2 text-muted"></i>Expiry Date</span> <strong>${fmtDateTime(o.expiry_date)}</strong></li>
              </ul>
            </div>
            <div class="col-md-6">
              <ul class="list-group">
                <li class="list-group-item">
                  <div class="mb-1"><i class="fa-solid fa-user fa-fw me-2 text-muted"></i><strong>Interment Right Holder</strong></div>
                  <div>${esc(o.interment_full_name) || '—'} <small class="text-muted">(${esc(o.interment_relationship) || '—'})</small></div>
                </li>
                <li class="list-group-item">
                  <div class="mb-1"><i class="fa-solid fa-location-dot fa-fw me-2 text-muted"></i><strong>Address</strong></div>
                  <div>${esc(o.interment_address) || '—'}</div>
                </li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-phone fa-fw me-2 text-muted"></i>Contact</span> <strong>${esc(o.interment_contact_number) || '—'}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-envelope fa-fw me-2 text-muted"></i>Email</span> <strong>${esc(o.interment_email) || '—'}</strong></li>
                <li class="list-group-item">
                  <div class="mb-1"><i class="fa-solid fa-cross fa-fw me-2 text-muted"></i><strong>Deceased</strong></div>
                  <div>${esc(o.deceased_full_name) || '—'} <small class="text-muted">(${esc(o.sex) || '—'}/${esc(o.age) || '—'})</small></div>
                </li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-layer-group fa-fw me-2 text-muted"></i>Grave</span> <strong>${esc(o.grave_level) || '—'} / ${esc(o.grave_type) || '—'}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span><i class="fa-solid fa-user-tie fa-fw me-2 text-muted"></i>Created By</span> <strong>${esc(o.created_by_staff_id) || '—'} <small class="text-muted">(${esc(o.created_by_username) || '—'})</small></strong></li>
              </ul>
            </div>
          </div>
        </div>`,
      width: 900,
      showConfirmButton: true,
      confirmButtonText: 'Close',
      confirmButtonColor: MAROON_COLOR
    });
  }

  // filters + print
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

  // initial
  loadActivity();
  loadTransactions();
});
