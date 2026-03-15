<?php
// Generate correct password hash for Admin@123
$password = "Admin@123";
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";

// Verify the hash
if (password_verify($password, $hash)) {
    echo "Hash is valid!\n";
} else {
    echo "Hash is invalid!\n";
}
?>
