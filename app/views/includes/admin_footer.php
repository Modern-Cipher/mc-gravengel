</div>

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
  &copy; <?php echo date('Y'); ?> Gravengel. All Rights Reserved.
</footer>

</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="<?php echo URLROOT; ?>/js/admin.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo URLROOT; ?>/js/profile.js?v=<?php echo time(); ?>"></script>

<?php if (isset($data['title']) && $data['title'] === 'Burial Records'): ?>
<script src="<?php echo URLROOT; ?>/js/burial-records.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>

<!-- Footer safe-area: set CSS var for bottom padding -->
<script>
(function () {
  function computeSafe(){
    var f = document.querySelector('.main-footer');
    if (!f) return 200; // fallback
    var h = f.getBoundingClientRect().height || 0;

    // extra cushion sa mobile dahil sa URL bar/home indicator
    var isMobile = window.matchMedia('(max-width: 768px)').matches;
    var base = h + (isMobile ? 80 : 24); // +80px mobile cushion, +24px desktop
    var safeInset = 0;

    try {
      // some browsers support env(safe-area-inset-bottom) via CSS only,
      // but we leave it zero here; CSS already adds env() on padding.
      safeInset = 0;
    } catch(e){}

    // clamp to a sensible minimum para kahit mababa computed height, may luwag
    var minMobile = 220;
    var value = base + safeInset;
    if (isMobile) value = Math.max(value, minMobile);

    return value;
  }

  function apply(){
    var v = computeSafe();
    // set on :root so CSS can read it everywhere
    document.documentElement.style.setProperty('--footer-safe', v + 'px');
  }

  // run multiple times to catch late layout changes on mobile
  window.addEventListener('load', function(){
    apply();
    setTimeout(apply, 100);
    setTimeout(apply, 300);
    setTimeout(apply, 600);
  });

  window.addEventListener('resize', apply);
  window.addEventListener('orientationchange', function(){
    // small delay – wait for viewport to settle after rotation / URL bar move
    setTimeout(apply, 150);
    setTimeout(apply, 600);
  });

  document.addEventListener('visibilitychange', function(){
    if (!document.hidden) {
      setTimeout(apply, 100);
      setTimeout(apply, 400);
    }
  });
})();
</script>
<script src="<?php echo URLROOT; ?>/js/notifications.js?v=<?php echo time(); ?>"></script>



</body>
</html>
