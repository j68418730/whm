<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$u = $p->query("SELECT id,username,email,first_name FROM hosting_users WHERE username='callspectre'")->fetch(PDO::FETCH_OBJ);
if ($u) {
    echo "id:{$u->id} username:{$u->username} email:{$u->email} first_name:{$u->first_name}\n";
} else {
    echo "NOT FOUND IN hosting_users\n";
}
// Also check if callspectre exists in admins
$a = $p->query("SELECT id,username,email FROM admins WHERE username='callspectre' OR email='callspectre@planet-hosts.com'")->fetch(PDO::FETCH_OBJ);
if ($a) {
    echo "ALSO IN admins: {$a->username} / {$a->email}\n";
} else {
    echo "Not in admins table\n";
}
