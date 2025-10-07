<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renewal Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; margin: 20px auto; border: 1px solid #cccccc; background-color: #ffffff;">
        <thead>
            <tr>
                <td align="center" bgcolor="#800000" style="padding: 20px 0; color: #ffffff; font-size: 24px; font-weight: bold;">
                    Plaridel Public Cemetery
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 40px 30px;">
                    <h1 style="font-size: 20px; margin: 0 0 20px 0; color: #333333;">Official Renewal Receipt</h1>
                    <p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.5; color: #555555;">
                        Dear <?= htmlspecialchars($data['emailData']['payer_name'] ?? 'Valued Client') ?>,
                    </p>
                    <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.5; color: #555555;">
                        Thank you for your payment. This email serves as your official receipt for the rental renewal. Below are the details of the transaction.
                    </p>

                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                        <tr><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee; width: 40%;"><strong>Transaction ID:</strong></td><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><?= htmlspecialchars($data['emailData']['transaction_id'] ?? 'N/A') ?></td></tr>
                        <tr><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><strong>Payment Date:</strong></td><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><?= date('M d, Y', strtotime($data['emailData']['payment_date'] ?? '')) ?></td></tr>
                        <tr><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><strong>Amount Paid:</strong></td><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;">₱ <?= number_format($data['emailData']['payment_amount'] ?? 0, 2) ?></td></tr>
                        <tr><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><strong>Deceased Name:</strong></td><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><?= htmlspecialchars($data['emailData']['deceased_name'] ?? 'N/A') ?></td></tr>
                        <tr><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><strong>Plot Information:</strong></td><td style="padding: 10px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><?= htmlspecialchars($data['emailData']['plot_label'] ?? 'N/A') ?></td></tr>
                        <tr style="background-color: #f9f9f9;"><td style="padding: 10px; font-size: 16px;"><strong>New Expiry Date:</strong></td><td style="padding: 10px; font-size: 16px; font-weight: bold; color: #800000;"><?= date('F d, Y', strtotime($data['emailData']['new_expiry_date'] ?? '')) ?></td></tr>
                    </table>

                    <p style="margin: 30px 0 0 0; font-size: 16px; line-height: 1.5; color: #555555;">
                        Thank you for your continued trust.
                    </p>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td bgcolor="#333333" style="padding: 15px 30px; text-align: center; color: #aaaaaa; font-size: 12px;">
                    This is an automated message. Please do not reply to this email.
                    <br>
                    &copy; <?= date('Y') ?> Plaridel Public Cemetery. All Rights Reserved.
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>