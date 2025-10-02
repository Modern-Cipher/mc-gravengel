</div>

<footer class="main-footer app-footer"
  style="
    position: fixed !important;
    left: 0; right: 0; bottom: 0;
    z-index: 5 !important;
    width: 100%;
    margin: 0 !important;
    border-top: 1px solid #eee;
    background: #fff;
    padding: 12px 10px;
    box-sizing: border-box;
    transform: translateZ(0);
  "
>
  &copy; <?php echo date('Y'); ?> Gravengel. All Rights Reserved.
</footer>

</main>
</div>

<!-- JS order same as admin to avoid '<' token errors & FullCalendar undefined -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Staff scripts -->
<script src="<?php echo URLROOT; ?>/js/staff.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo URLROOT; ?>/js/profile.js?v=<?php echo time(); ?>"></script>

<?php if (isset($data['title']) && $data['title'] === 'Burial Records'): ?>
<script src="<?php echo URLROOT; ?>/js/burial-records.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>

<!-- Notifications client (same as admin) -->
<script src="<?php echo URLROOT; ?>/js/notifications.js?v=<?php echo time(); ?>"></script>

<!-- Dropdown lifter (clone from admin_header) -->
<script>
  (function(){
    var bell = document.getElementById('notifBell');
    var root = document.getElementById('notifRoot');
    if(!bell || !root) return;

    var menu = root.querySelector('.dropdown-menu');
    if(!menu) return;

    document.body.appendChild(menu);
    menu.classList.add('notif-floating');

    var open = false;
    var OFFSET = 12;

    function placeMenu(){
      try{ bell.dispatchEvent(new Event('show.bs.dropdown', {bubbles:true})); }catch(e){}
      var r = bell.getBoundingClientRect();
      menu.style.position = 'fixed';
      menu.style.top  = (r.bottom + OFFSET) + 'px';
      menu.style.right= (window.innerWidth - r.right) + 'px';
      menu.classList.add('show');
      open = true;
    }
    function hideMenu(){ menu.classList.remove('show'); open=false; }

    bell.addEventListener('click', function(ev){
      ev.preventDefault();
      open ? hideMenu() : placeMenu();
    });
    document.addEventListener('click', function(ev){
      if(!open) return;
      if (ev.target === bell || bell.contains(ev.target)) return;
      if (!menu.contains(ev.target)) hideMenu();
    });
    window.addEventListener('resize', function(){ if(open) placeMenu(); });
    window.addEventListener('scroll', function(){ if(open) placeMenu(); }, {passive:true});

    try{ new bootstrap.Dropdown(bell); }catch(e){}
  })();
</script>

</body>
</html>
