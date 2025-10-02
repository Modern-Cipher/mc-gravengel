// public/js/staff-burial-records.js
document.addEventListener('DOMContentLoaded', function () {
  const addBurialBtn  = document.getElementById('addBurialBtn');
  const agreeCheck    = document.getElementById('agreeCheck');
  const proceedBtn    = document.getElementById('proceedBtn');
  const termsModalEl  = document.getElementById('termsModal');

  // Auto detect base from current path
  const pathParts = location.pathname.replace(/\/+$/, '').split('/');
  const base = pathParts.includes('staff') ? 'staff' : 'admin';
  const URLROOT = (window.URLROOT || '').replace(/\/+$/, '');

  const termsModal = termsModalEl
    ? new bootstrap.Modal(termsModalEl, { backdrop: 'static', keyboard: false })
    : null;

  if (addBurialBtn && termsModal) {
    addBurialBtn.addEventListener('click', () => termsModal.show());
  }

  if (agreeCheck && proceedBtn && termsModal) {
    agreeCheck.addEventListener('change', () => {
      proceedBtn.disabled = !agreeCheck.checked;
    });
    proceedBtn.addEventListener('click', () => {
      termsModal.hide();
      // ✅ stays under /staff when staff is logged in
      window.location.href = `${URLROOT}/${base}/addBurial`;
    });
  }
});
