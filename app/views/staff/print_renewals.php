<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renewal History Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h5>Renewal History Report</h5>
            <p class="text-muted">Generated on: <?= date('F d, Y h:i A') ?></p>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Transaction ID</th>
                    <th>Payment Date</th>
                    <th>Amount</th>
                    <th>Payer Name</th>
                    <th>New Expiry</th>
                    <th>Processed By</th>
                    <th>Receipt Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['history'])): ?>
                    <tr><td colspan="7" class="text-center">No renewal history found.</td></tr>
                <?php else: foreach($data['history'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->transaction_id ?? '') ?></td>
                        <td><?= date('M d, Y', strtotime($item->payment_date)) ?></td>
                        <td>₱ <?= number_format($item->payment_amount ?? 0, 2) ?></td>
                        <td><?= htmlspecialchars($item->payer_name ?? 'N/A') ?></td>
                        <td><?= date('M d, Y', strtotime($item->new_expiry_date)) ?></td>
                        <td><?= htmlspecialchars($item->processed_by ?? '') ?></td>
                        <td><?= htmlspecialchars($item->receipt_email_status ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Smart Records. Sacred Grounds.</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>