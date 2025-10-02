// public/js/notifications.js
(function () {
  const $ = (s, r = document) => r.querySelector(s);

  const root       = $('#notifRoot');
  if (!root) return; // walang bell sa page

  const bell       = $('#notifBell');
  const badge      = $('#notifCount');
  const list       = $('#notifList');
  const tabToday   = $('#notifTabToday');
  const tabHistory = $('#notifTabHistory');
  const btnMark    = $('#notifMarkRead');

  // Polling intervals (pwede mong baguhin)
  const POLL_BADGE_MS     = 30 * 1000; // 30s badge + seeding
  const POLL_FEED_OPEN_MS = 30 * 1000; // 30s refresh while dropdown is open

  let currentTab = 'today';
  let loadedOnce = false;
  let feedTimer  = null;
  let badgeTimer = null;

  const icon = (sev) => {
    if (sev === 'danger')  return '<i class="fas fa-exclamation-triangle text-danger me-2"></i>';
    if (sev === 'warning') return '<i class="fas fa-exclamation-circle text-warning me-2"></i>';
    return '<i class="fas fa-info-circle text-secondary me-2"></i>';
  };

  const row = (r) => `
    <div class="d-flex align-items-start gap-2 p-2 border-bottom ${r.is_read ? '' : 'bg-light'}">
      ${icon(r.severity)}
      <div class="flex-grow-1">
        <div class="fw-semibold">${esc(r.title) || ''}</div>
        <div class="small text-muted">${esc(r.message) || ''}</div>
        <div class="small text-muted">${fmtDT(r.created_at)}</div>
      </div>
    </div>`;

  function fmtDT(s){
    if(!s) return '';
    const d = new Date((s + '').replace(' ', 'T'));
    return isNaN(d) ? s : d.toLocaleString();
  }
  function esc(v){
    if (v === null || v === undefined) return '';
    return String(v).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }
  async function j(url, opt={}) {
    const r = await fetch(url, { credentials:'same-origin', ...opt });
    try { return await r.json(); } catch { return {ok:false}; }
  }
  function setBadge(n){
    n = Number(n||0);
    if (n > 0){ badge.textContent = n; badge.style.display='inline-block'; }
    else { badge.style.display='none'; }
  }
  function showEmpty(){
    list.innerHTML = `<div class="p-3 text-center text-muted">${
      currentTab === 'today' ? 'No new notifications.' : 'No notifications in history.'
    }</div>`;
  }
  function setTabUI(tab){
    if (!tabToday || !tabHistory) return;
    tabToday.classList.toggle('btn-light', tab==='today');
    tabToday.classList.toggle('btn-outline-secondary', tab!=='today');
    tabHistory.classList.toggle('btn-light', tab==='history');
    tabHistory.classList.toggle('btn-outline-secondary', tab!=='history');
  }

  async function pollSeedThenBadge() {
    // 1) seed on demand (idempotent)
    await j(`${window.URLROOT}/notifications/poll`);
    // 2) then get unread count
    const d = await j(`${window.URLROOT}/notifications/unreadCount`);
    if (d && d.ok !== false) setBadge(d.unread);
  }

  async function loadFeed(tab='today'){
    currentTab = tab;
    setTabUI(tab);
    if (list) list.innerHTML = `<div class="p-3 text-center text-muted">Loading…</div>`;
    const d = await j(`${window.URLROOT}/notifications/feed/${tab}`);
    setBadge(d.unread);
    if (d && d.ok && Array.isArray(d.rows) && d.rows.length){
      list.innerHTML = d.rows.map(row).join('');
    } else {
      showEmpty();
    }
  }

  async function markAllRead(){
    const d = await j(`${window.URLROOT}/notifications/markRead`, { method:'POST' });
    if (d && d.ok){
      setBadge(0);
      list?.querySelectorAll('.bg-light').forEach(el => el.classList.remove('bg-light'));
    }
  }

  // Dropdown open/close — detect via .show presence on menu
  const menu = document.body.querySelector('.dropdown-menu[aria-labelledby="notifBell"]')
           || root.querySelector('.dropdown-menu');

  function startFeedPolling(){
    stopFeedPolling();
    feedTimer = setInterval(() => {
      if (menu && menu.classList.contains('show')) loadFeed(currentTab);
    }, POLL_FEED_OPEN_MS);
  }
  function stopFeedPolling(){
    if (feedTimer){ clearInterval(feedTimer); feedTimer = null; }
  }

  // Click watcher (works with your custom lifter)
  document.addEventListener('click', () => {
    if (!menu) return;
    const open = menu.classList.contains('show');
    if (open){
      if (!loadedOnce){ loadedOnce = true; loadFeed('today'); }
      startFeedPolling();
    }else{
      stopFeedPolling();
    }
  });

  // Tab + Mark read
  tabToday?.addEventListener('click',  () => loadFeed('today'));
  tabHistory?.addEventListener('click', () => loadFeed('history'));
  btnMark?.addEventListener('click',   markAllRead);

  // ===== Initial + interval polling =====
  pollSeedThenBadge();                              // first check on page load
  badgeTimer = setInterval(pollSeedThenBadge, POLL_BADGE_MS); // keep it fresh

  window.addEventListener('beforeunload', () => {
    if (badgeTimer) clearInterval(badgeTimer);
    if (feedTimer)  clearInterval(feedTimer);
  });
})();
