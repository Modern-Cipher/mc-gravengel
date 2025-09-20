<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
</head>
<body>
    <p>Hello, <?php echo htmlspecialchars($data['full_name']); ?></p>
    <p>We received a request to reset the password for your account. If you did not make this request, you can safely ignore this email.</p>
    <p>To reset your password, please click the link below:</p>
    <p><a href="<?php echo $data['reset_link']; ?>">Reset My Password</a></p>
    <p>This link is valid for 24 hours.</p>
    <p>Thank you,<br>Plaridel Public Cemetery Management</p>
</body>
</html>