<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
        body { font-family: sans-serif; }
        .table { font-size: 10pt; }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h3, .header h5 {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="header">
            <h3>User Activity Report</h3>
            <h5><?= htmlspecialchars($data['user_name']) ?></h5>
            <p class="text-muted small">Generated on: <?= date('F d, Y h:i A') ?></p>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th style="width: 25%;">Timestamp</th>
                    <th style="width: 20%;">Action Type</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['activities'])): ?>
                    <?php foreach($data['activities'] as $activity): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('M d, Y, h:i:s A', strtotime($activity->ts))) ?></td>
                            <td><code><?= htmlspecialchars($activity->kind) ?></code></td>
                            <td><?= htmlspecialchars($activity->action_text) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No activities found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>