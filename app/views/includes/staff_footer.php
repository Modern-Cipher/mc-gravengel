<?php
// app/views/includes/staff_footer.php
?>
        <!-- ⬆️ page content ends here -->

        <!-- Footer spacing sync -->
        <script>
          (function () {
            function syncFooterSpace(){
              var f = document.querySelector('.app-footer, .main-footer');
              var h = f ? Math.ceil(f.getBoundingClientRect().height) : 0;
              document.documentElement.style.setProperty('--footer-h', h + 'px');
              var main = document.querySelector('.main-content');
              if (main) main.style.paddingBottom = (h + 24) + 'px';
            }
            window.addEventListener('load', syncFooterSpace, { once:true });
            window.addEventListener('resize', syncFooterSpace);
            var f = document.querySelector('.app-footer, .main-footer');
            if (window.ResizeObserver && f){
              const ro = new ResizeObserver(syncFooterSpace);
              ro.observe(f);
            }
          })();
        </script>
      </div> <!-- /.container-fluid -->

      <!-- Sticky Footer -->
      <footer class="main-footer"
        style="
          position: fixed !important;
          left: 0; right: 0; bottom: 0;
          z-index: 5 !important;      /* footer behind the sidebar */
          width: 100%;
          margin: 0 !important;
          border-top: 1px solid #eee;
          background: #fff;
          padding: 12px 10px;
          box-sizing: border-box;
          transform: translateZ(0);
        "
      >
        &copy; <?= date('Y') ?> Gravengel. All Rights Reserved.
      </footer>

    </main> <!-- /.main-content -->
  </div> <!-- /.main-wrapper -->

  <!-- Vendor JS (must be FIRST before page scripts) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Guard: stop page script if Bootstrap failed to load -->
  <script>
    (function(){
      if (!window.bootstrap || !bootstrap.Modal) {
        console.error('Bootstrap JS not loaded. Modals/Tooltips will not work.');
        alert('Missing Bootstrap JS (bootstrap.bundle). Please check network/caching.');
      }
    })();
  </script>

  <!-- Global staff scripts -->
  <script src="<?= URLROOT ?>/js/staff.js?v=<?= time() ?>"></script>
  <script src="<?= URLROOT ?>/js/staff_profile.js?v=<?= time() ?>"></script>

  <!-- Page-specific: Burial Records -->
  <?php if (!empty($data['title']) && $data['title'] === 'Burial Records'): ?>
    <script src="<?= URLROOT ?>/js/staff-burial-records.js?v=<?= time() ?>"></script>
  <?php endif; ?>

  <!-- Notifications client -->
  <script src="<?= URLROOT ?>/js/notifications.js?v=<?= time() ?>"></script>

  <!-- Footer safe-area + notif dropdown lifter -->
  <script>
  (function () {
    function computeSafe(){
      var f = document.querySelector('.main-footer');
      if (!f) return 200;
      var h = f.getBoundingClientRect().height || 0;
      var isMobile = window.matchMedia('(max-width: 768px)').matches;
      var base = h + (isMobile ? 80 : 24);
      var value = isMobile ? Math.max(base, 220) : base;
      return value;
    }
    function apply(){
      var v = computeSafe();
      document.documentElement.style.setProperty('--footer-safe', v + 'px');
    }
    window.addEventListener('load', function(){
      apply(); setTimeout(apply,100); setTimeout(apply,300); setTimeout(apply,600);
    });
    window.addEventListener('resize', apply);
    window.addEventListener('orientationchange', function(){ setTimeout(apply,150); setTimeout(apply,600); });

    // move notif dropdown to body & position below header
    window.addEventListener('load', function(){
      var bell = document.getElementById('notifBell');
      var root = document.getElementById('notifRoot');
      if(!bell || !root) return;
      var menu = root.querySelector('.dropdown-menu');
      if(!menu) return;

      document.body.appendChild(menu);
      menu.classList.add('notif-floating');

      var open = false, OFFSET = 12;
      function placeMenu(){
        try{ bell.dispatchEvent(new Event('show.bs.dropdown', {bubbles:true})); }catch(e){}
        var r = bell.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top  = (r.bottom + OFFSET) + 'px';
        menu.style.right= (window.innerWidth - r.right) + 'px';
        menu.classList.add('show');
        open = true;
      }
      function hideMenu(){ menu.classList.remove('show'); open = false; }

      bell.addEventListener('click', function(ev){ ev.preventDefault(); open ? hideMenu() : placeMenu(); });
      document.addEventListener('click', function(ev){
        if(!open) return;
        if (ev.target === bell || bell.contains(ev.target)) return;
        if (!menu.contains(ev.target)) hideMenu();
      });
      window.addEventListener('resize', function(){ if(open) placeMenu(); });
      window.addEventListener('scroll', function(){ if(open) placeMenu(); }, {passive:true});

      try{ new bootstrap.Dropdown(bell); }catch(e){}
    });
  })();
  </script>

</body>
</html>
