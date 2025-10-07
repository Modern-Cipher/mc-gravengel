<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plot Vacation Confirmation</title>
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
                    <h1 style="font-size: 20px; margin: 0 0 20px 0; color: #333333;">Confirmation of Plot Vacation</h1>
                    <p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.5; color: #555555;">
                        Dear <?= htmlspecialchars($data['emailData']['interment_name'] ?? 'Valued Client') ?>,
                    </p>
                    <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.5; color: #555555;">
                        This email serves as a formal confirmation that the rental agreement for the plot detailed below has not been renewed and has been officially vacated as of today, <?= htmlspecialchars($data['emailData']['vacate_date'] ?? '') ?>.
                    </p>

                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; background-color: #f9f9f9;">
                        <tr><td style="padding: 12px; font-size: 16px; border-bottom: 1px solid #eeeeee; width: 40%;"><strong>Deceased Name:</strong></td><td style="padding: 12px; font-size: 16px; border-bottom: 1px solid #eeeeee;"><?= htmlspecialchars($data['emailData']['deceased_name'] ?? 'N/A') ?></td></tr>
                        <tr><td style="padding: 12px; font-size: 16px;"><strong>Plot Information:</strong></td><td style="padding: 12px; font-size: 16px;"><?= htmlspecialchars($data['emailData']['plot_label'] ?? 'N/A') ?></td></tr>
                    </table>

                    <p style="margin: 20px 0 0 0; font-size: 16px; line-height: 1.5; color: #555555;">
                        The burial record has been moved to our archives, and the plot is now considered vacant.
                    </p>
                     <p style="margin: 20px 0 0 0; font-size: 16px; line-height: 1.5; color: #555555;">
                        Thank you for your understanding.
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