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

// Guest password protect
if ($action === 'guest_protect' && isset($_SESSION['chatbox_admin'])) {
    $tenantId = (int)$_SESSION['chatbox_admin']['tenant_id'];
    $enable = (int)($_POST['enable'] ?? 0);
    $password = $_POST['password'] ?? '';
    $pdo->prepare("UPDATE chatbox_tenants SET guest_password = ?, guest_password_enabled = ? WHERE id = ?")
        ->execute([$enable ? password_hash($password, PASSWORD_DEFAULT) : '', $enable, $tenantId]);
    echo json_encode(['success' => true]);
    exit;
}

// Verify guest password
if ($action === 'verify_guest') {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT guest_password FROM chatbox_tenants WHERE id = ? AND guest_password_enabled = 1");
    $stmt->execute([$tenantId]);
    $hash = $stmt->fetchColumn();
    if ($hash && password_verify($password, $hash)) {
        $_SESSION['chatbox_guest_' . $tenantId] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid password']);
    }
    exit;
}

// Moderation log
if ($action === 'mod_log' && isset($_SESSION['chatbox_admin'])) {
    $tenantId = (int)$_SESSION['chatbox_admin']['tenant_id'];
    $stmt = $pdo->prepare("SELECT * FROM chatbox_moderation_log WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$tenantId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_OBJ));
    exit;
}

// Chat statistics
if ($action === 'stats' && isset($_SESSION['chatbox_admin'])) {
    $tenantId = (int)$_SESSION['chatbox_admin']['tenant_id'];
    $totalMsgs = $pdo->prepare("SELECT COUNT(*) FROM chatbox_messages WHERE tenant_id = ?");
    $totalMsgs->execute([$tenantId]);
    $totalUsers = $pdo->prepare("SELECT COUNT(*) FROM chatbox_users WHERE tenant_id = ?");
    $totalUsers->execute([$tenantId]);
    $onlineNow = $pdo->prepare("SELECT COUNT(*) FROM chatbox_users WHERE tenant_id = ? AND last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $onlineNow->execute([$tenantId]);
    $topCharts = $pdo->prepare("SELECT DATE(created_at) as d, COUNT(*) as c FROM chatbox_messages WHERE tenant_id = ? GROUP BY DATE(created_at) ORDER BY d DESC LIMIT 7");
    $topCharts->execute([$tenantId]);
    echo json_encode([
        'total_messages' => $totalMsgs->fetchColumn(),
        'total_users' => $totalUsers->fetchColumn(),
        'online_now' => $onlineNow->fetchColumn(),
        'daily_chart' => $topCharts->fetchAll(PDO::FETCH_OBJ),
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
