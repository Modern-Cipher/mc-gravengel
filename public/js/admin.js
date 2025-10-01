document.addEventListener('DOMContentLoaded', function () {

  // ---------- DASHBOARD COUNTS ----------
  (async function loadCards(){
    try {
      const r = await fetch(`${window.URLROOT}/admin/dashboardCards`, { credentials: 'same-origin' });
      const j = await r.json();
      if (j && j.ok) {
        const set = (id, v)=>{ const el=document.getElementById(id); if(el) el.textContent = Number(v||0).toLocaleString(); };
        set('card-active',  j.active);   // active burials (is_active=1)
        set('card-expired', j.expired);  // burials with expiry_date < NOW()
        set('card-today',   j.today);    // today's transactions (by rental_date or created_at, depending on your controller)
        set('card-staff',   j.staff);    // staff accounts count
      }
    } catch (e) {
      // silent — leave zeros if API not ready
    }
  })();


  // ---------- CALENDAR (Rental Expiry) ----------
  const calendarEl = document.getElementById('calendar-container');
  if (calendarEl) {
    const cal = new FullCalendar.Calendar(calendarEl, {
      initialView: (window.innerWidth < 768) ? 'listWeek' : 'dayGridMonth',
      headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listWeek' },
      height: 'auto',
      events: async (info, success, failure) => {
        try {
          const url = `${window.URLROOT}/admin/expiryEvents?from=${encodeURIComponent(info.startStr)}&to=${encodeURIComponent(info.endStr)}`;
          const r = await fetch(url, { credentials:'same-origin' });
          const j = await r.json();
          if (j && j.ok) success(j.events || []);
          else success([]);
        } catch (err) {
          console.error('Calendar events error:', err);
          failure(err);
        }
      },
      // Bigger, readable pill inside the day cell
      eventContent: function(arg){
        const p = arg.event.extendedProps || {};
        const timeLabel = (arg.timeText || '').replace(/^0/,''); // e.g., "12p"
        const holder = p.holder ? `<span class="exp-holder">${p.holder}</span>` : '';
        return {
          html: `
            <span class="exp-pill">
              ${timeLabel ? `<span class="exp-time">${timeLabel}</span>` : ``}
              Expiry
            </span>
            ${holder}
          `
        };
      },
      eventClick: function(info){
        const p = info.event.extendedProps || {};
        Swal.fire({
          title: 'Rental Expiry',
          html: `
            <div class="text-start">
              <p><strong>Interment Right Holder:</strong><br>${p.holder || '—'}</p>
              <p><strong>Burial ID:</strong> ${p.burial_id || '—'}</p>
              <p><strong>Block / Plot:</strong> ${p.block || '—'} — ${p.plot || '—'}</p>
              <p><strong>Grave:</strong> ${p.grave || '—'}</p>
              <p><strong>Expiry Date &amp; Time:</strong><br>${p.expiry || info.event.startStr}</p>
            </div>
          `,
          confirmButtonText: 'Close',
          confirmButtonColor: '#800000',
          width: 520
        });
      },
      windowResize: function () {
        cal.changeView(window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth');
      }
    });
    cal.render();
  }


  // ---------- (keep your existing code below) ----------
  // LOGOUT prompt
  document.querySelectorAll('a[href*="/auth/logout"]').forEach(a=>{
    a.addEventListener('click', function(e){
      e.preventDefault();
      const href=this.href;
      Swal.fire({
        title:'Are you sure?', text:'You will be logged out.',
        icon:'warning', showCancelButton:true,
        confirmButtonColor:'#800000', cancelButtonColor:'#6c757d',
      }).then(res=>{ if(res.isConfirmed) location.href=href; });
    });
  });

  // must-change-password prompt (once per session)
  const needsChange = (document.body.dataset.mustChangePwd === '1');
  if (needsChange && !sessionStorage.getItem('passwordPrompted')) {
    Swal.fire({
      title:'Update Password?', text:'For security, please update your password.',
      icon:'warning', showCancelButton:true, confirmButtonText:'Update Now',
      confirmButtonColor:'#800000'
    }).then(res=>{
      sessionStorage.setItem('passwordPrompted','true');
      if(res.isConfirmed) location.href=`${window.URLROOT}/auth/force_change`;
    });
  }

  // mobile sidebar toggle
  const tgl=document.querySelector('.sidebar-toggle'), sb=document.querySelector('#sidebar');
  if(tgl && sb){ tgl.addEventListener('click', ()=> sb.classList.toggle('open')); }

  // session poll
  let sessionInterval = null;
  async function checkSession(){
    try{
      const r=await fetch(`${window.URLROOT}/auth/checkSession`);
      const j=await r.json();
      if(!j.is_valid){ clearInterval(sessionInterval);
        Swal.fire({title:'Session Expired!',icon:'error'}).then(()=>location.href=`${window.URLROOT}/auth/logout`);
      }
    }catch(e){ clearInterval(sessionInterval); }
  }
  if (document.body.hasAttribute('data-must-change-pwd')) {
    sessionInterval = setInterval(checkSession, 15000);
  }

  // sidebar active highlight
  (function(){
    const links=[...document.querySelectorAll('#sidebar .sidebar-nav a')];
    if(!links.length) return;
    const clear=()=>links.forEach(a=>a.classList.remove('active'));
    function apply(){
      const path=location.pathname.replace(/\/+$/,'');
      let best=null,len=0;
      links.forEach(a=>{
        const p=new URL(a.getAttribute('href')||'',location.origin).pathname.replace(/\/+$/,'');
        if(path===p || (p!=='/' && path.startsWith(p))){
          if(p.length>len){ best=a; len=p.length; }
        }
      });
      clear(); (best||links[0]).classList.add('active');
    }
    apply();
    links.forEach(a=>a.addEventListener('click',()=>{ try{sessionStorage.setItem('ss_active_href',a.getAttribute('href')||'');}catch{} }));
    window.addEventListener('popstate',apply);
    window.addEventListener('hashchange',apply);
  })();

});
