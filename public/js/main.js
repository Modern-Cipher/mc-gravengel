/* ===========================
   Gravengel – Professional UI Animations (rev12)
   =========================== */

// NAVBAR: solid maroon once not at the very top
function updateHeader() {
  const header = document.querySelector('.top-nav');
  if (!header) return;
  if (window.scrollY > 10) header.classList.add('scrolled');
  else header.classList.remove('scrolled');
}
window.addEventListener('load', updateHeader);
window.addEventListener('scroll', updateHeader);

// SIDEBAR toggle + body scroll lock
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.getElementById('sidebar');
const body = document.body;
const closeSidebarBtn = document.getElementById('close-sidebar');

function openSidebar(){ if(sidebar){ sidebar.classList.add('active'); body.classList.add('no-scroll'); } }
function closeSidebar(){ if(sidebar){ sidebar.classList.remove('active'); body.classList.remove('no-scroll'); } }

if (menuToggle) menuToggle.addEventListener('click', openSidebar);
if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', e => { e.preventDefault(); closeSidebar(); });
document.querySelectorAll('.sidebar a').forEach(a => {
    // Huwag i-apply ang closeSidebar sa login button para gumana ang transition
    if (!a.classList.contains('login-button')) {
        a.addEventListener('click', closeSidebar);
    }
});

/* ===== GSAP Animation Initialization ===== */
function waitForStableLayout({checks=8, interval=120, timeout=5000} = {}) {
  return new Promise(resolve => {
    let lastH = 0, stable = 0, elapsed = 0;
    const i = setInterval(() => {
      const h = document.documentElement.scrollHeight;
      if (Math.abs(h - lastH) < 2) stable++; else stable = 0;
      lastH = h; elapsed += interval;
      if (stable >= checks || elapsed >= timeout) { clearInterval(i); resolve(); }
    }, interval);
  });
}

(function loadGSAP(){
  const hasGSAP = !!window.gsap && !!window.ScrollTrigger;
  if (hasGSAP) return initAnimations();

  const s1 = document.createElement('script');
  s1.src = "https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js";
  s1.onload = () => {
    const s2 = document.createElement('script');
    s2.src = "https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js";
    s2.onload = initAnimations;
    document.head.appendChild(s2);
  };
  document.head.appendChild(s1);
})();

async function initAnimations(){
  if (document.fonts && document.fonts.ready) { try { await document.fonts.ready; } catch(e){} }
  await waitForStableLayout();

  gsap.registerPlugin(ScrollTrigger);

  const reveal = (target, fromVars) => {
    gsap.from(target, {
      ...fromVars,
      duration: 1.0,
      ease: "power3.out",
      immediateRender: false,
      scrollTrigger: {
        trigger: target,
        start: "top 88%",
        end: "bottom 12%",
        toggleActions: "play reverse play reverse",
      }
    });
  };

  const heroLogo = document.querySelector('.hero-main-logo');
  const tagline = document.querySelector('.hero-tagline');
  const cta = document.querySelector('.hero-section .cta-button'); 
  if (heroLogo) reveal(heroLogo, {opacity:0, scale:0.95, y: 10});
  if (tagline) reveal(tagline, {opacity:0, y:20});
  if (cta) reveal(cta, {opacity:0, y:20});
  document.querySelectorAll('.section-title').forEach(el => reveal(el, {opacity:0, y:30}));
  const aboutImg = document.querySelector('.about-media');
  const aboutTxt = document.querySelector('.about-text');
  if (aboutImg) reveal(aboutImg, {opacity:0, x:-50});
  if (aboutTxt) reveal(aboutTxt, {opacity:0, x:50});

  const cards = document.querySelectorAll('.feature-card');
  if (cards.length){
    gsap.from(cards, {
      opacity: 0,
      y: 40,
      duration: 0.8,
      ease: "power3.out",
      stagger: 0.15,
      immediateRender: false,
      scrollTrigger: {
        trigger: cards[0].closest('.feature-card-container') || cards[0],
        start: "top 85%",
        end: "bottom 15%",
        toggleActions: "play reverse play reverse",
      }
    });
  }

  const cForm = document.querySelector('.contact-form');
  const cMap  = document.querySelector('.contact-map');
  if (cForm) reveal(cForm, {opacity:0, x:-40});
  if (cMap)  reveal(cMap,  {opacity:0, x:40});

  const refresh = () => ScrollTrigger.refresh();
  window.addEventListener('load', refresh);
  window.addEventListener('resize', refresh);
  document.querySelectorAll('img, iframe').forEach(el => el.addEventListener('load', refresh));
}

/* ===== SMOOTH PAGE TRANSITION ===== */
document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;

    // Hanapin lang ang mga links na papuntang '/auth/login'
    const loginLinks = document.querySelectorAll('a[href*="/auth/login"]');

    loginLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const destination = this.href;
            e.preventDefault();
            body.classList.add('fade-out');
            setTimeout(() => {
                window.location.href = destination;
            }, 400); // Dapat tumugma sa transition-duration ng .fade-out
        });
    });
    
    // Para sa back/forward buttons ng browser
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            body.classList.remove('fade-out');
        }
    });
});