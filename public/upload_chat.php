<?php
// Chat file upload handler
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file']);
    exit;
}

$chatId = (int)($_POST['chat_id'] ?? 0);
$sessionId = (int)($_POST['session_id'] ?? 0);

if (!$sessionId) { http_response_code(400); echo json_encode(['error' => 'No session']); exit; }

$uploadDir = __DIR__ . '/../storage/chat/';
@mkdir($uploadDir, 0755, true);

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','txt','zip','mp3','mp4','mov'];
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'File type not allowed']);
    exit;
}

$filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$dest = $uploadDir . $filename;
move_uploaded_file($file['tmp_name'], $dest);

$stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender_type, sender_name, message, file_url, file_name, created_at) VALUES (?, 'visitor', 'Visitor', 'Sent a file', ?, ?, NOW())");
$stmt->execute([$sessionId, '/storage/chat/' . $filename, $file['name']]);
$msgId = $pdo->lastInsertId();

// Also save to chat_attachments
$pdo->prepare("INSERT INTO chat_attachments (message_id, tenant_id, file_name, file_path, file_size, mime_type) VALUES (?, 0, ?, ?, ?, ?)")
    ->execute([$msgId, $file['name'], '/storage/chat/' . $filename, $file['size'], $file['type']]);

echo json_encode(['url' => '/storage/chat/' . $filename, 'name' => $file['name']]);
