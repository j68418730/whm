<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $p->query("SELECT password_hash FROM admins WHERE username='root'")->fetchColumn();
if ($q) {
    echo "Hash: $q\n";
    echo "Verify 'Skylinehosting171': " . (password_verify('Skylinehosting171', $q) ? 'MATCH' : 'NO MATCH') . "\n";
    echo "Verify 'admin': " . (password_verify('admin', $q) ? 'MATCH' : 'NO MATCH') . "\n";
} else {
    echo "NOT FOUND\n";
}
