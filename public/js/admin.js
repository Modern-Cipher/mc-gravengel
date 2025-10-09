document.addEventListener('DOMContentLoaded', function () {

  // ---------- DASHBOARD COUNTS ----------
  (async function loadCards(){
    try {
      const r = await fetch(`${window.URLROOT}/admin/dashboardCards`, { credentials: 'same-origin' });
      const j = await r.json();
      if (j && j.ok) {
        const set = (id, v)=>{ const el=document.getElementById(id); if(el) el.textContent = Number(v||0).toLocaleString(); };
        set('card-active',  j.active);
        set('card-expired', j.expired);
        set('card-today',   j.today);
        set('card-staff',   j.staff);
      }
    } catch (e) {
      // leave zeros quietly
    }
  })();



 // ---------- WAVE CHART FUNCTION ----------
  (async function loadRentalChart() {
    const ctx = document.getElementById('rentalChart');
    if (!ctx) return;

    try {
        const response = await fetch(`${window.URLROOT}/admin/getDashboardChartData`);
        const result = await response.json();

        if (result.ok) {
            const data = result.data;
            new Chart(ctx, {
                // Pinalitan ang type sa 'line'
                type: 'line', 
                data: {
                    labels: ['New Rentals (This Month)', 'Expiring Soon (Next 30 Days)', 'Total Expired'],
                    datasets: [{
                        label: 'Number of Records',
                        data: [data.new_rentals, data.expiring_soon, data.total_expired],
                        
                        // Mga bagong style para sa "wave" effect
                        fill: true, // Ito ang naglalagay ng kulay sa ilalim ng linya
                        backgroundColor: 'rgba(123, 30, 40, 0.2)', // Semi-transparent maroon fill
                        borderColor: 'rgb(123, 30, 40)', // Solid maroon na linya
                        tension: 0.4, // Ito ang nagpapakurba sa linya para maging "wave"
                        pointBackgroundColor: 'rgb(123, 30, 40)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(123, 30, 40)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false 
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1 
                            }
                        }
                    }
                }
            });
        } else {
            console.error('Failed to load chart data:', result.message);
        }
    } catch(err) {
        console.error('Error fetching chart data:', err);
    }
  })();
  // ---------- END NG CHART FUNCTION ----------


   // ---------- BAGONG FINANCIAL CHART ----------
  (async function loadFinancialChart() {
    const ctx = document.getElementById('financialChart');
    if (!ctx) return;
    try {
        const response = await fetch(`${window.URLROOT}/admin/getFinancialChartData`);
        const result = await response.json();
        if (result.ok) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: result.labels, // Data mula sa controller
                    datasets: [{
                        label: 'Total Pesos',
                        data: result.data, // Data mula sa controller
                        fill: false,
                        borderColor: 'rgb(123, 30, 40)', // Maroon line
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // Magdagdag ng '₱' sa Y-axis labels
                                callback: function(value, index, values) {
                                    return '₱ ' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                // Magdagdag ng '₱' sa tooltip
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += '₱ ' + context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch(err) { console.error('Error fetching financial chart data:', err); }
  })();


  // ---------- CALENDAR (Rental Expiry only) ----------
  const calendarEl = document.getElementById('calendar-container');
  if (calendarEl) {
    const cal = new FullCalendar.Calendar(calendarEl, {
      initialView: (window.innerWidth < 768) ? 'listWeek' : 'dayGridMonth',
      headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listWeek' },
      height: 'auto',

      // --- ITO ANG IDINAGDAG PARA I-CAPITALIZE ANG MGA BUTTONS ---
      buttonText: {
          today:    'Today',
          month:    'Month',
          week:     'Week',
          list:     'List'
      },
      // --- END NG DAGDAG ---

      events: async (info, success, failure) => {
        try {
          const url = `${window.URLROOT}/admin/expiryEvents?from=${encodeURIComponent(info.startStr)}&to=${encodeURIComponent(info.endStr)}`;
          const res = await fetch(url, { credentials: 'same-origin' });
          const txt = await res.text();
          let json;
          try { json = JSON.parse(txt); }
          catch { 
            console.warn('Calendar events: non-JSON response received. Check PHP errors/redirects.');
            success([]); 
            return;
          }
          if (json && json.ok && Array.isArray(json.events)) {
            success(json.events);
          } else {
            success([]);
          }
        } catch (err) {
          console.error('Calendar events error:', err);
          failure(err);
        }
      },
      eventContent: function(arg){
        const p = arg.event.extendedProps || {};
        const timeLabel = (arg.timeText || '').replace(/^0/,'');
        const holder = p.holder ? `<span class="exp-holder">${p.holder}</span>` : '';
        return { html: `<span class="exp-pill">${timeLabel ? `<span class="exp-time">${timeLabel}</span>`:''} Expiry</span>${holder}` };
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

  // ---------- (keep your existing behaviour below) ----------
  document.querySelectorAll('a[href*="/auth/logout"]').forEach(a=>{
    a.addEventListener('click', function(e){
      e.preventDefault();
      const href=this.href;
      Swal.fire({
        title:'Are you sure?', text:'You will be logged out.',
        icon:'warning', showCancelButton:true,
        confirmButtonColor:'#800000', cancelButtonColor:'#6c757d'
      }).then(res=>{ if(res.isConfirmed) location.href=href; });
    });
  });

  // const needsChange = (document.body.dataset.mustChangePwd === '1');
  // if (needsChange && !sessionStorage.getItem('passwordPrompted')) {
  //   Swal.fire({
  //     title:'Update Password?', text:'For security, please update your password.',
  //     icon:'warning', showCancelButton:true, confirmButtonText:'Update Now',
  //     confirmButtonColor:'#800000'
  //   }).then(res=>{
  //     sessionStorage.setItem('passwordPrompted','true');
  //     if(res.isConfirmed) location.href=`${window.URLROOT}/auth/force_change`;
  //   });
  // }

  const tgl=document.querySelector('.sidebar-toggle'), sb=document.querySelector('#sidebar');
  if(tgl && sb){ tgl.addEventListener('click', ()=> sb.classList.toggle('open')); }

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
    links.forEach(a=>a.addEventListener('click',()=>{ try{sessionStorage.setItem('ss_active_href',a.getAttribute('href')||'');}catch{} }));
    window.addEventListener('popstate',apply);
    window.addEventListener('hashchange',apply);
  })();

});