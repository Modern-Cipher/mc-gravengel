<?php
// Ensures $link is defined.
$link = $data['link'] ?? '#';
$cemName = 'PLARIDEL PUBLIC CEMETERY';
$maroon = '#7b1e28'; // Maroon Color
$secondary = '#f7f7f8';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Password Reset</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: <?php echo $secondary; ?>; color: #111; }
        .content-area { padding: 24px; background-color: #ffffff; border-radius: 12px; }
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .content-area { padding: 15px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: <?php echo $secondary; ?>;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: <?php echo $secondary; ?>;">
        <tr>
            <td align="center" style="padding: 30px 10px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" class="container">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: <?php echo $maroon; ?>; padding: 20px 24px; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                            <h1 style="color: #ffffff; font-size: 20px; font-weight: bold; margin: 0;"><?php echo htmlspecialchars($cemName); ?></h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 24px;" class="content-area">
                            <h2 style="font-size: 18px; color: <?php echo $maroon; ?>; margin: 0 0 16px 0;">Password Reset Request</h2>
                            <p style="font-size: 14px; line-height: 1.6; margin: 0 0 24px 0;">
                                A system administrator has initiated a password reset for your account. Please click the button below to set a new password:
                            </p>
                            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto 30px auto;">
                                <tr>
                                    <td align="center" bgcolor="<?php echo $maroon; ?>" style="border-radius: 6px;">
                                        <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" style="display: block; padding: 12px 25px; border-radius: 6px; background-color: #7b1e28; color: #ffffff; text-decoration: none; font-weight: bold; font-size: 15px; border: 1px solid #7b1e28;">
                                            Reset My Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size: 12px; color: #555555; margin: 0 0 5px 0;">If the button does not work, copy and paste this link into your browser:</p>
                            <p style="font-size: 12px; color: <?php echo $maroon; ?>; word-break: break-all; margin: 0;">
                                <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" style="color: <?php echo $maroon; ?>; text-decoration: underline;"><?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?></a>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 15px 24px 20px 24px; border-top: 1px solid #eeeeee; font-size: 11px; color: #999999; text-align: center;">
                            <p style="margin: 0;">For security, this link will expire in 2 hours.</p>
                            <p style="margin: 5px 0 0 0;">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($cemName); ?>. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
