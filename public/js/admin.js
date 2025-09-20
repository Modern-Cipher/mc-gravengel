document.addEventListener('DOMContentLoaded', function() {

    // --- CALENDAR INITIALIZATION ---
    const calendarEl = document.getElementById('calendar-container');
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            height: 'auto',
            handleWindowResize: true,
            windowResize: function(view) {
                if (window.innerWidth < 768) {
                    calendar.changeView('listWeek');
                } else {
                    calendar.changeView('dayGridMonth');
                }
            }
        });
        calendar.render();
    }

    // --- LOGOUT ALERT ---
    const logoutLinks = document.querySelectorAll('a[href*="/auth/logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const logoutUrl = this.href;
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#800000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, log me out!',
                backdrop: false,
                heightAuto: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutUrl;
                }
            });
        });
    });

    // --- CHANGE PASSWORD ALERT (ONCE PER SESSION) ---
    const bodyEl = document.querySelector('body');
    const needsChange = bodyEl.dataset.mustChangePwd === '1';
    const hasBeenPrompted = sessionStorage.getItem('passwordPrompted');

    if (needsChange && !hasBeenPrompted) {
        Swal.fire({
            title: 'Update Password?',
            text: 'For security, it is recommended to update your password.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Update Now',
            cancelButtonText: 'Later',
            confirmButtonColor: '#800000',
            backdrop: false,
            heightAuto: false
        }).then(res => {
            sessionStorage.setItem('passwordPrompted', 'true');
            if (res.isConfirmed) {
                location.href = `${window.URLROOT}/auth/force_change`;
            }
        });
    }

    // --- MOBILE SIDEBAR TOGGLE ---
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('#sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }

    // --- POLLING SCRIPT PARA SA SESSION CHECK ---
    const checkSession = async () => {
        try {
            const response = await fetch(`${window.URLROOT}/auth/checkSession`);
            if (!response.ok) {
                clearInterval(sessionInterval);
                console.error('Server error during session check.');
                return;
            }
            const data = await response.json();

            if (!data.is_valid) {
                clearInterval(sessionInterval);
                Swal.fire({
                    title: 'Session Expired!',
                    text: 'Your account was logged in from another device. You will be logged out.',
                    icon: 'error',
                    confirmButtonColor: '#800000',
                    allowOutsideClick: false,
                    backdrop: false,
                    heightAuto: false
                }).then(() => {
                    window.location.href = `${window.URLROOT}/auth/logout`;
                });
            }
        } catch (error) {
            console.error('Session check failed:', error);
            clearInterval(sessionInterval);
        }
    };

    // Simulan lang ang pag-check kung may user na naka-login
    if (document.querySelector('body[data-must-change-pwd]')) {
        const sessionInterval = setInterval(checkSession, 15000);
    }

});