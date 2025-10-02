document.addEventListener('DOMContentLoaded', function () {

  // ---------- DASHBOARD CARDS ----------
  (async function loadCards(){
    try {
      const r = await fetch(`${window.URLROOT}/staff/dashboardCards`, { credentials: 'same-origin' });
      const txt = await r.text();
      let j; try { j = JSON.parse(txt); } catch { j = null; }
      if (j && j.ok) {
        const set = (id, v)=>{ const el=document.getElementById(id); if(el) el.textContent = Number(v||0).toLocaleString(); };
        set('card-active',  j.active);
        set('card-expired', j.expired);
        set('card-today',   j.today);
        set('card-staff',   j.staff);
      }
    } catch (e) { /* leave zeros */ }
  })();

  // ---------- CALENDAR (Rental Expiry only) ----------
  const calendarEl = document.getElementById('calendar-container');
  if (calendarEl && window.FullCalendar) {
    const cal = new FullCalendar.Calendar(calendarEl, {
      initialView: (window.innerWidth < 768) ? 'listWeek' : 'dayGridMonth',
      headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listWeek' },
      height: 'auto',
      events: async (info, success, failure) => {
        try {
          const url = `${window.URLROOT}/staff/expiryEvents?from=${encodeURIComponent(info.startStr)}&to=${encodeURIComponent(info.endStr)}`;
          const res = await fetch(url, { credentials: 'same-origin' });
          const txt = await res.text();
          let json; try { json = JSON.parse(txt); } catch { success([]); return; }
          if (json && json.ok && Array.isArray(json.events)) success(json.events); else success([]);
        } catch (err) { failure(err); }
      },
      eventContent: function(arg){
        const p = arg.event.extendedProps || {};
        const timeLabel = (arg.timeText || '').replace(/^0/,'');
        const holder = p.holder ? `<span class="exp-holder">${p.holder}</span>` : '';
        return { html: `<span class="exp-pill">${timeLabel ? `<span class="exp-time">${timeLabel}</span>`:''} Expiry</span>${holder}` };
      },
      eventClick: function(info){
        const p = info.event.extendedProps || {};
        if (!window.Swal) return;
        Swal.fire({
          title: 'Rental Expiry',
          html: `
            <div class="text-start">
              <p><strong>Interment Right Holder:</strong><br>${p.holder || '—'}</p>
              <p><strong>Burial ID:</strong> ${p.burial_id || '—'}</p>
              <p><strong>Block / Plot:</strong> ${p.block || '—'} — ${p.plot || '—'}</p>
              <p><strong>Grave:</strong> ${p.grave || '—'}</p>
              <p><strong>Expiry Date &amp; Time:</strong><br>${p.expiry || info.event.startStr}</p>
            </div>`,
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

  // ---------- Logout confirm ----------
  document.querySelectorAll('a[href*="/auth/logout"]').forEach(a=>{
    a.addEventListener('click', function(e){
      e.preventDefault();
      const href=this.href;
      if (!window.Swal) { location.href = href; return; }
      Swal.fire({
        title:'Are you sure?', text:'You will be logged out.',
        icon:'warning', showCancelButton:true,
        confirmButtonColor:'#800000', cancelButtonColor:'#6c757d'
      }).then(res=>{ if(res.isConfirmed) location.href=href; });
    });
  });

  // ---------- Sidebar active link ----------
  (function(){
    const links=[...document.querySelectorAll('#sidebar .sidebar-nav a')];
    if(!links.length) return;
    const clear=()=>links.forEach(a=>a.classList.remove('active'));
    function apply(){
      const path=location.pathname.replace(/\/+$/,'');
      let best=null,len=0;
      links.forEach(a=>{
        const p=new URL(a.getAttribute('href')||'',location.origin).pathname.replace(/\/+$/,'');
        if(path===p || (p!=='/' && path.startsWith(p))) if(p.length>len){ best=a; len=p.length; }
      });
      clear(); (best||links[0]).classList.add('active');
    }
    apply();
    window.addEventListener('popstate',apply);
    window.addEventListener('hashchange',apply);
  })();
});
