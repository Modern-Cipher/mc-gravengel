</div> <footer class="main-footer">
                &copy; <?php echo date('Y'); ?> Gravengel. All Rights Reserved.
            </footer>

        </main> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="<?php echo URLROOT; ?>/js/admin.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo URLROOT; ?>/js/profile.js?v=<?php echo time(); ?>"></script>
    
    <?php if (isset($data['title']) && $data['title'] === 'Burial Records'): ?>
    <script src="<?php echo URLROOT; ?>/js/burial-records.js?v=<?php echo time(); ?>"></script>
    <?php endif; ?>
</body>
</html>