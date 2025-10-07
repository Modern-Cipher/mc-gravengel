<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        /* Simple responsive styles */
        @media screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 15px !important;
            }
        }
    </style>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; background-color: #edf2f7; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table class="container" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
                    <tr>
                        <td style="background-color: #8a2c38; padding: 20px; text-align: center;">
                           
                            <h1 style="color: #ffffff; margin: 10px 0 0; font-size: 20px; font-weight: 600;">PLARIDEL PUBLIC CEMETERY</h1>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 35px 40px; color: #4a5568; line-height: 1.6;">
                            <h2 style="color: #2d3748; font-size: 22px; margin-top: 0;">Hello, <?= htmlspecialchars($data['full_name']); ?>!</h2>
                            <p style="margin-bottom: 25px; font-size: 16px;"><?= htmlspecialchars($data['message']); ?></p>
                            
                            <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto 30px auto;">
                                <tr>
                                    <td align="center" bgcolor="#8a2c38" style="border-radius: 6px;">
                                        <a href="<?= htmlspecialchars($data['reset_link']); ?>" target="_blank" 
                                           style="display: inline-block; padding: 14px 28px; border-radius: 6px; background-color: #8a2c38; color: #ffffff !important; text-decoration: none; font-weight: bold; font-size: 16px;">
                                            Set a New Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 14px; color: #718096;">If you're having trouble with the button above, copy and paste the URL below into your web browser.</p>
                            <p style="font-size: 12px; word-break: break-all; color: #4a5568; background-color: #f7fafc; padding: 10px; border-radius: 4px; margin-bottom: 25px;"><a href="<?= htmlspecialchars($data['reset_link']); ?>" style="color: #8a2c38;"><?= htmlspecialchars($data['reset_link']); ?></a></p>
                            
                            <p style="font-size: 14px; color: #718096;">Please note: This link is only valid for the next 24 hours.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f7fafc; padding: 20px; text-align: center; border-top: 1px solid #e8e8e8;">
                            <p style="font-size: 12px; color: #a0aec0; margin: 0;">&copy; <?= date('Y') ?> Plaridel Public Cemetery. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>