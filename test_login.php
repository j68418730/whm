<?php
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$stmt = $pdo->prepare("SELECT d.*, s.port, s.status as stream_status, s.autodj_active FROM radio_djs d JOIN streaming_stations s ON d.stream_id = s.id JOIN radio_streams rs ON d.stream_id = rs.id WHERE d.username = ? AND d.status = 'active'");
$stmt->execute(['testing']);
$dj = $stmt->fetch(PDO::FETCH_OBJ);
var_dump($dj);
if ($dj && password_verify('password', $dj->password)) {
    echo "Password verified!\n";
} else {
    echo "Password NOT verified!\n";
}