<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Contact Form Inquiry</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color: #8a2c38; padding: 20px; text-align: center; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">New Inquiry Received</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 25px;">
                            <h2 style="font-size: 20px; color: #8a2c38;">Message Details:</h2>
                            <table width="100%" border="0" cellspacing="0" cellpadding="5" style="border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="width: 100px; font-weight: bold; padding: 10px 0;">From:</td>
                                    <td><?= htmlspecialchars($data['name']) ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="font-weight: bold; padding: 10px 0;">Email:</td>
                                    <td><a href="mailto:<?= htmlspecialchars($data['email']) ?>" style="color: #8a2c38;"><?= htmlspecialchars($data['email']) ?></a></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="font-weight: bold; padding: 10px 0;">Subject:</td>
                                    <td><?= htmlspecialchars($data['subject']) ?></td>
                                </tr>
                            </table>
                            <h3 style="font-size: 18px; color: #8a2c38; margin-top: 30px; border-top: 2px solid #eee; padding-top: 20px;">Message:</h3>
                            <div style="background-color: #f9f9f9; border: 1px solid #eee; border-radius: 4px; padding: 15px; line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($data['message'])) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; color: #888; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            This is an automated message from the Gravengel Contact Form.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>