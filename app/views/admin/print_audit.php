<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore Audit Trail Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 2rem; }
        .header img { height: 80px; margin-bottom: 1rem; }
        .table { font-size: 0.9rem; }
        .table th { background-color: #f2f2f2; }
        .footer { text-align: center; margin-top: 2rem; font-size: 0.8rem; color: #888; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="header">
            <img src="<?= URLROOT ?>/public/img/ppclogo.png" alt="Cemetery Logo">
            <h4>Plaridel Public Cemetery</h4>
            <h5>Backup & Restore Audit Trail</h5>
            <p class="text-muted">Generated on: <?= date('F d, Y h:i A') ?></p>
        </div>

        <button onclick="window.print()" class="btn btn-primary mb-3 no-print">
            <i class="fas fa-print"></i> Print Report
        </button>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>Performed By</th>
                    <th>Status</th>
                    <th>Details / IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['logs'])): ?>
                    <tr><td colspan="5" class="text-center">No audit history found.</td></tr>
                <?php else: foreach($data['logs'] as $log): ?>
                    <tr>
                        <td><?= date('M d, Y, h:i A', strtotime($log->timestamp)) ?></td>
                        <td>
                            <?php if($log->action_type == 'backup_created'): ?>
                                <span class="badge bg-info">Backup Created</span>
                            <?php elseif($log->action_type == 'restore_attempted'): ?>
                                <span class="badge bg-warning text-dark">Restore Attempted</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($log->username ?? 'Unknown User') ?></td>
                        <td>
                            <?php if($log->status == 'success'): ?>
                                <span class="badge bg-success">Success</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Failure</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 0.85em;">
                            <?= htmlspecialchars($log->details ?? 'N/A') ?>
                            <br><small class="text-muted">IP: <?= htmlspecialchars($log->ip_address ?? 'N/A') ?></small>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Smart Records. Sacred Grounds.</p>
        </div>
    </div>
</body>
</html>