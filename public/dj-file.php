<?php
/**
 * DJ Asset Server — serves images from /home/{username}/radio/dj/{djname}/
 * URL: /dj-file.php?dj=spectre&file=avatar.jpg
 */
$dj = preg_replace('/[^a-z0-9_\-]/', '', $_GET['dj'] ?? '');
$file = preg_replace('/[^a-z0-9_\-\.\/]/', '', $_GET['file'] ?? '');
if (!$dj || !$file) { http_response_code(400); exit; }
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$st = $pdo->prepare("SELECT ss.user_id FROM radio_djs d JOIN streaming_stations ss ON d.stream_id = ss.id WHERE d.username = ?");
$st->execute([$dj]);
$row = $st->fetch(PDO::FETCH_OBJ);
if (!$row) { http_response_code(404); exit; }
$hu = $pdo->prepare("SELECT username FROM hosting_users WHERE id = ?");
$hu->execute([$row->user_id]);
$user = $hu->fetch(PDO::FETCH_OBJ);
if (!$user) { http_response_code(404); exit; }
$path = "/home/{$user->username}/radio/dj/{$dj}/{$file}";
if (!file_exists($path)) { http_response_code(404); exit; }
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','mp4'=>'video/mp4','mov'=>'video/quicktime'];
header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
header('Cache-Control: max-age=86400');
readfile($path);