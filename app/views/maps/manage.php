<?php require APPROOT . '/views/includes/header.php'; ?>
<?php require APPROOT . '/views/includes/navbar_map.php'; ?>

<div class="container mt-4">
    <h2><?php echo $data['title']; ?></h2>
    <p>Listahan ng lahat ng plaka sa mapa. I-click ang "Edit" para ayusin ang pangalan, alignment, at itsura ng plot modal.</p>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Key</th><th>Title</th><th>Offset X</th><th>Offset Y</th><th>Modal Rows</th><th>Modal Cols</th><th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['blocks'] as $block) : ?>
                <tr>
                    <td><?php echo $block->block_key; ?></td>
                    <td><?php echo $block->title; ?></td>
                    <td><?php echo $block->offset_x; ?></td>
                    <td><?php echo $block->offset_y; ?></td>
                    <td><?php echo $block->modal_rows ?? 'N/A'; ?></td>
                    <td><?php echo $block->modal_cols ?? 'N/A'; ?></td>
                    <td class="text-end">
                        <button class="btn btn-primary btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-block='<?php echo htmlspecialchars(json_encode($block), ENT_QUOTES, 'UTF-8'); ?>'>Edit</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- TINAMA ANG ACTION DITO -->
      <form action="<?php echo URLROOT; ?>/maps/updateBlock" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Block</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="editModalBody">
          </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const editModalEl = document.getElementById('editModal');
    const editModalBody = document.getElementById('editModalBody');
    const editModalLabel = document.getElementById('editModalLabel');
    editModalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const blockData = JSON.parse(button.dataset.block);

        editModalLabel.textContent = `Edit: ${blockData.title}`;
        editModalBody.innerHTML = `
            <input type="hidden" name="id" value="${blockData.id}">
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="${blockData.title}"></div>
            <div class="row">
               <div class="col"><label class="form-label">Offset X</label><input type="number" name="offset_x" class="form-control" value="${blockData.offset_x}"></div>
               <div class="col"><label class="form-label">Offset Y</label><input type="number" name="offset_y" class="form-control" value="${blockData.offset_y}"></div>
            </div>
            <hr>
            <p class="text-muted small">Configuration para sa plot grid modal:</p>
            <div class="row">
               <div class="col"><label class="form-label">Modal Rows</label><input type="number" name="modal_rows" class="form-control" value="${blockData.modal_rows || 4}"></div>
               <div class="col"><label class="form-label">Modal Columns</label><input type="number" name="modal_cols" class="form-control" value="${blockData.modal_cols || 8}"></div>
            </div>
        `;
    });
</script>
<?php if(isset($_SESSION['flash_message'])): ?>
    <script>
        Swal.fire({ icon: '<?php echo $_SESSION['flash_type']; ?>', title: '<?php echo $_SESSION['flash_message']; ?>', showConfirmButton: false, timer: 2000 });
    </script>
    <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
<?php endif; ?>

</body>
</html>