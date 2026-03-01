<?php

$password = "123456";

$hashed_password = password_hash($password, PASSWORD_BCRYPT);

echo "<h3>Password Hash Generator</h3>";
echo "<strong>Plain Password:</strong> " . $password . "<br>";
echo "<strong>Hashed Password:</strong> <code style='background:#eee; padding:5px;'>" . $hashed_password . "</code><br><br>";
echo "Copy the hashed string above and paste it into your MySQL 'password_hash' column.";
?>