<?php
session_start();
header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Login
if ($action === 'login') {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$tenantId || !$username || !$password) {
        echo json_encode(['success' => false, 'error' => 'Missing fields']); exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM chatbox_users WHERE tenant_id = ? AND username = ?");
    $stmt->execute([$tenantId, $username]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if ($user && password_verify($password, $user->password_hash) && !$user->is_banned) {
        echo json_encode(['success' => true, 'userId' => $user->id, 'username' => $user->username,
            'displayName' => $user->display_name ?: $user->username, 'role' => $user->role]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials or banned']);
    }
    exit;
}

// Register
if ($action === 'register') {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    if (!$tenantId || !$username || strlen($username) < 3 || strlen($password) < 4) {
        echo json_encode(['success' => false, 'error' => 'Username (3+ chars) and password required']); exit;
    }
    $check = $pdo->prepare("SELECT id FROM chatbox_users WHERE tenant_id = ? AND username = ?");
    $check->execute([$tenantId, $username]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Username taken']); exit;
    }
    $stmt = $pdo->prepare("INSERT INTO chatbox_users (tenant_id, username, password_hash, email, role) VALUES (?, ?, ?, ?, 'member')");
    $stmt->execute([$tenantId, $username, password_hash($password, PASSWORD_DEFAULT), $email]);
    echo json_encode(['success' => true, 'username' => $username, 'role' => 'member']);
    exit;
}

// Ban user (admin only - requires session)
if ($action === 'ban' && isset($_SESSION['chatbox_admin'])) {
    $tenantId = (int)$_SESSION['chatbox_admin']['tenant_id'];
    $targetId = (int)($_POST['user_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $pdo->prepare("UPDATE chatbox_users SET is_banned = 1, ban_reason = ? WHERE id = ? AND tenant_id = ?")
        ->execute([$reason, $targetId, $tenantId]);
    $pdo->prepare("INSERT INTO chatbox_bans (tenant_id, user_id, reason, banned_by) VALUES (?, ?, ?, ?)")
        ->execute([$tenantId, $targetId, $reason, $_SESSION['chatbox_admin']['user_id']]);
    echo json_encode(['success' => true]);
    exit;
}

// Unban
if ($action === 'unban' && isset($_SESSION['chatbox_admin'])) {
    $tenantId = (int)$_SESSION['chatbox_admin']['tenant_id'];
    $targetId = (int)($_POST['user_id'] ?? 0);
    $pdo->prepare("UPDATE chatbox_users SET is_banned = 0, ban_reason = NULL WHERE id = ? AND tenant_id = ?")
        ->execute([$targetId, $tenantId]);
    echo json_encode(['success' => true]);
    exit;
}

// Deny voice
if ($action === 'deny_voice' && isset($_SESSION['chatbox_admin'])) {
    $tenantId = (int)$_SESSION['chatbox_admin']['tenant_id'];
    $targetId = (int)($_POST['user_id'] ?? 0);
    $pdo->prepare("UPDATE chatbox_users SET voice_denied = 1 WHERE id = ? AND tenant_id = ?")
        ->execute([$targetId, $tenantId]);
    echo json_encode(['success' => true]);
    exit;
}

// Get online users (stub - real data comes from SignalR)
if ($action === 'online') {
    echo json_encode(['online' => []]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
