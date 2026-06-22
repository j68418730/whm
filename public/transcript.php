<?php
// Chat Transcript Download / Email
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$chatId = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? 'download';

if (!$chatId) die('No chat specified');

$chat = $pdo->prepare("SELECT * FROM chat_sessions WHERE id = ?");
$chat->execute([$chatId]); $chat = $chat->fetch(PDO::FETCH_OBJ);
if (!$chat) die('Chat not found');

$msgs = $pdo->prepare("SELECT * FROM chat_messages WHERE session_id = ? ORDER BY id");
$msgs->execute([$chatId]); $msgs = $msgs->fetchAll(PDO::FETCH_OBJ);

$transcript = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$transcript .= " PLANET-HOSTS LIVE CHAT TRANSCRIPT\n";
$transcript .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$transcript .= "Chat #{$chat->id}\n";
$transcript .= "Visitor: {$chat->visitor_name}\n";
$transcript .= "Email: {$chat->visitor_email}\n";
$transcript .= "Department: {$chat->department}\n";
$transcript .= "Date: {$chat->created_at}\n";
$transcript .= "Status: {$chat->status}\n\n";
$transcript .= "──────────────────────────────────\n\n";
foreach ($msgs as $m) {
    $name = $m->sender_name ?: ucfirst($m->sender_type);
    $line = "[{$m->created_at}] {$name}: {$m->message}";
    if ($m->file_url) $line .= " [Attachment: {$m->file_name}]";
    $transcript .= $line . "\n";
}
$transcript .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$transcript .= " End of Transcript\n";
$transcript .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($action === 'email') {
    $to = $_GET['to'] ?? $chat->visitor_email;
    $subject = "Chat Transcript #{$chat->id} - PlanetHosts";
    $headers = "From: transcripts@planet-hosts.com\r\nContent-Type: text/plain; charset=UTF-8";
    mail($to, $subject, $transcript, $headers);
    echo "Transcript emailed to " . htmlspecialchars($to);
    exit;
}

// Download as TXT
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="chat_' . $chatId . '_transcript.txt"');
echo $transcript;
