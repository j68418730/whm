<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $p->query("SELECT id, username, name, role, must_change_password FROM admins WHERE username='root'")->fetch(PDO::FETCH_OBJ);
if ($q) {
    echo "id:{$q->id} name:{$q->name} role:{$q->role} must_change:{$q->must_change_password}\n";
} else {
    echo "NOT FOUND\n";
}
