document.addEventListener('DOMContentLoaded', function () {
  const addBurialBtn = document.getElementById('addBurialBtn');
  const agreeCheck = document.getElementById('agreeCheck');
  const proceedBtn = document.getElementById('proceedBtn');
  const termsModalEl = document.getElementById('termsModal');
  const termsModal = termsModalEl ? new bootstrap.Modal(termsModalEl, {
    backdrop: 'static',
    keyboard: false
  }) : null;

  if (addBurialBtn && termsModal) {
    addBurialBtn.addEventListener('click', function () {
      // open modal first (no close via backdrop/esc)
      termsModal.show();
    });
  }

  if (agreeCheck && proceedBtn) {
    agreeCheck.addEventListener('change', () => {
      proceedBtn.disabled = !agreeCheck.checked;
    });
    proceedBtn.addEventListener('click', () => {
      // go to add page only after agreement
      termsModal.hide();
      window.location.href = `${window.URLROOT || ''}/admin/addBurial`;
    });
  }
});
