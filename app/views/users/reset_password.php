<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 20px;">
                            <p>Hello, **<?php echo htmlspecialchars($data['full_name']); ?>**</p>
                            <p style="margin-bottom: 20px;">We received a request to reset the password for your account. If you did not make this request, you can safely ignore this email.</p>
                            
                            <p style="margin-bottom: 20px;">To reset your password, please click the button below:</p>
                            
                            <!-- CRITICAL FIX: Ginamit ang inline style button structure para gumana ang link -->
                            <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto 20px auto;">
                                <tr>
                                    <td align="left" bgcolor="#7b1e28" style="border-radius: 4px;">
                                        <a href="<?php echo htmlspecialchars($data['reset_link']); ?>" target="_blank" 
                                           style="display: block; padding: 10px 20px; border-radius: 4px; background-color: #7b1e28; color: #ffffff !important; 
                                                  text-decoration: none; font-weight: bold; font-size: 15px; border: 1px solid #7b1e28;">
                                            Reset My Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 12px; color: #999;">If the button does not work, copy and paste this link in your browser:</p>
                            <p style="font-size: 12px; word-break: break-all; color: #7b1e28;"><a href="<?php echo htmlspecialchars($data['reset_link']); ?>"><?php echo htmlspecialchars($data['reset_link']); ?></a></p>

                            <p style="font-size: 12px; color: #555;">This link is valid for 2 hours.</p>
                            
                            <p style="margin-top: 30px;">Thank you,<br>Plaridel Public Cemetery Management</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
</body>
</html>