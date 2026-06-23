<?php
header('Content-Type: application/json');
session_start();
require_once dirname(__DIR__, 3) . '/core/Application.php';
$app = \Core\Application::getInstance();
$db = $app->get('db');
$auth = $app->get('auth');

if (!$auth->check()) { echo json_encode(['error'=>'Unauthorized']); exit; }

$streamId = (int)($_POST['stream_id'] ?? 0);
if (!$streamId) { echo json_encode(['error'=>'No stream ID']); exit; }

$stream = $db->table('radio_streams')->where('id', $streamId)->first();
if (!$stream) { echo json_encode(['error'=>'Stream not found']); exit; }

// Check ownership (user or admin)
$user = $auth->user();
$isOwner = false;
$hosting = $db->table('hosting_users')->where('email', $user->email)->first();
if ($hosting && $hosting->id == $stream->user_id) $isOwner = true;
if ($auth->isAdmin()) $isOwner = true;
if (!$isOwner) { echo json_encode(['error'=>'Permission denied']); exit; }

$host = 'localhost';
$port = $stream->port ?? 8000;
$adminPass = $stream->password ?? 'admin';
$mount = '/stream';

// Kick source via Icecast admin interface
$ch = curl_init("http://{$host}:{$port}/admin/killsource?mount={$mount}");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => "admin:{$adminPass}",
    CURLOPT_TIMEOUT => 5,
    CURLOPT_HTTPGET => true,
]);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo json_encode(['success' => true, 'message' => 'Source kicked successfully']);
} else {
    echo json_encode(['error' => "Failed to kick source (HTTP {$httpCode})"]);
}
