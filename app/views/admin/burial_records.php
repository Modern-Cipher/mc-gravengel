<?php require APPROOT . '/views/includes/admin_header.php'; ?>

<div class="burial-records-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Burial Records</h2>
        <div class="action-buttons">
            <button id="addBurialBtn" class="btn btn-primary"><i class="fas fa-plus"></i> Add Burial</button>
            <button class="btn btn-warning"><i class="fas fa-archive"></i> Archive</button>
            <button class="btn btn-info"><i class="fas fa-file-export"></i> Generate Report</button>
        </div>
    </div>
    
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Plot ID</th>
                        <th>Burial ID</th>
                        <th>Name</th>
                        <th>Grave Level & Type</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Rental Duration</th>
                        <th>Action</th>
                    </tr>
                </thead>
                       <tbody>
                    <?php if (!empty($data['records'])): ?>
                    <?php foreach ($data['records'] as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record->block_title); ?></td>
                        <td><?php echo htmlspecialchars($record->burial_id); ?></td>
                        <td><?php echo htmlspecialchars($record->deceased_first_name . ' ' . $record->deceased_last_name); ?></td>
                        <td><?php echo htmlspecialchars($record->grave_level . ' ' . $record->grave_type); ?></td>
                        <td><?php echo htmlspecialchars($record->age); ?></td>
                        <td><?php echo htmlspecialchars($record->sex); ?></td>
                        <td>5 year/s</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info view-qr" data-burial-id="<?php echo htmlspecialchars($record->burial_id); ?>"><i class="fas fa-qrcode"></i> View QR</a>
                            <a href="#" class="btn btn-sm btn-success edit-record"><i class="fas fa-edit"></i></a>
                            <a href="#" class="btn btn-sm btn-danger delete-record"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No burial records found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>