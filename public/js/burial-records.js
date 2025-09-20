document.addEventListener('DOMContentLoaded', function() {
    const addBurialBtn = document.getElementById('addBurialBtn');

    if (addBurialBtn) {
        addBurialBtn.addEventListener('click', function() {
            // I-redirect sa addBurial page
            window.location.href = `${window.URLROOT}/admin/addBurial`;
        });
    }
});