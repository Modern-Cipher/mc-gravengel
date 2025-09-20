<?php
// Set the password you want to use
$passwordToHash = 'admin123';

// Generate the hash
$hashedPassword = password_hash($passwordToHash, PASSWORD_DEFAULT);

// Display the hash
echo "<h3>Password Hashing Tool</h3>";
echo "<p><strong>Password to hash:</strong> " . htmlspecialchars($passwordToHash) . "</p>";
echo "<p><strong>Generated Hash:</strong></p>";
echo "<textarea rows='3' cols='80' readonly>" . htmlspecialchars($hashedPassword) . "</textarea>";
echo "<p><br>Copy the hash above and paste it into the 'password_hash' column for the 'admin' user in your database.</p>";
