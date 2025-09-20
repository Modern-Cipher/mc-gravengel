<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Plaridel Public Cemetery Management System</title>
</head>
<body>
    <p>Hello, <?php echo htmlspecialchars($data['full_name']); ?></p>
    <p>Welcome to the team! Your account has been created successfully. You can now log in to the Plaridel Public Cemetery Management System.</p>
    <p><strong>Your Temporary Password:</strong> <span><?php echo htmlspecialchars($data['temp_password']); ?></span></p>
    <p>Please use this link to log in:</p>
    <p><a href="<?php echo URLROOT; ?>/auth/login">Login Here</a></p>
    <p>For your security, you will be prompted to change your password upon your first login.</p>
    <p>Thank you,<br>Plaridel Public Cemetery Management</p>
</body>
</html>