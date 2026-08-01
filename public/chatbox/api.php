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

// === GET MESSAGES (polling) ===
if ($action === 'get_messages') {
    $roomId = (int)($_GET['room_id'] ?? 0);
    $tenantId = (int)($_GET['tenant_id'] ?? 0);
    $since = (int)($_GET['since'] ?? 0);
    if (!$roomId && !$tenantId) { echo json_encode(['error'=>'Missing room or tenant']); exit; }
    $sql = "SELECT m.* FROM chatbox_messages m WHERE";
    $params = [];
    if ($roomId) { $sql .= " m.room_id = ?"; $params[] = $roomId; }
    else { $sql .= " m.tenant_id = ?"; $params[] = $tenantId; }
    if ($since > 0) { $sql .= " AND m.id > ?"; $params[] = $since; }
    $sql .= " ORDER BY m.id ASC LIMIT 100";
    $q = $pdo->prepare($sql);
    $q->execute($params);
    echo json_encode($q->fetchAll(PDO::FETCH_OBJ));
    exit;
}

// === SEND MESSAGE (guest or registered) ===
if ($action === 'send_message') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $username = trim($_POST['username'] ?? 'Guest');
    $message = trim($_POST['message'] ?? '');
    $userId = (int)($_POST['user_id'] ?? 0);
    if ((!$roomId && !$tenantId) || !$message) { echo json_encode(['error'=>'Missing fields']); exit; }
    if (strlen($message) > 2000) { echo json_encode(['error'=>'Message too long']); exit; }
    // Rate limit
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $rate = $pdo->prepare("SELECT COUNT(*) FROM chatbox_messages WHERE (user_id=? OR username=?) AND created_at > UNIX_TIMESTAMP()*1000-2000");
    $rate->execute([$userId ?: 0, $ip ?: $username]);
    if ($rate->fetchColumn() > 3) { echo json_encode(['error'=>'Slow down']); exit; }
    $now = round(microtime(true)*1000);
    $q = $pdo->prepare("INSERT INTO chatbox_messages (room_id, tenant_id, user_id, username, message, message_type, created_at) VALUES (?,?,?,?,?,?,?)");
    $q->execute([$roomId ?: null, $tenantId ?: null, $userId, $username, $message, 'text', $now]);
    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId(), 'username'=>$username, 'message'=>$message, 'created_at'=>$now]);
    exit;
}

// === DELETE MESSAGE (admin/mod) ===
if ($action === 'delete_message' && isset($_SESSION['chatbox_admin'])) {
    $msgId = (int)($_POST['message_id'] ?? 0);
    $pdo->prepare("UPDATE chatbox_messages SET message='[deleted]', message_type='system' WHERE id=?")->execute([$msgId]);
    echo json_encode(['success'=>true]); exit;
}

// === GUEST LOGIN (sets session for guest) ===
if ($action === 'guest_login') {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    if (!$tenantId || !$username || strlen($username) < 1 || strlen($username) > 30) { echo json_encode(['error'=>'Invalid username']); exit; }
    $_SESSION['chatbox_guest_' . $tenantId] = ['username' => $username, 'time' => time()];
    echo json_encode(['success'=>true, 'username'=>$username]);
    exit;
}

// === SAVE PROFILE (registered user) ===
if ($action === 'save_profile') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    if (!$userId || !$tenantId) { echo json_encode(['error'=>'Missing user']); exit; }
    $fields = [];
    $params = [];
    foreach (['display_name','avatar','font_style','font_color','font_size'] as $f) {
        if (isset($_POST[$f])) { $fields[] = "$f=?"; $params[] = $_POST[$f]; }
    }
    if (!empty($fields)) {
        $params[] = $userId; $params[] = $tenantId;
        $pdo->prepare("UPDATE chatbox_users SET " . implode(',', $fields) . " WHERE id=? AND tenant_id=?")->execute($params);
    }
    echo json_encode(['success'=>true]); exit;
}

// === GET PROFILE BY USERNAME ===
if ($action === 'get_profile_by_username') {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $q = $pdo->prepare("SELECT id, username, display_name, avatar, font_style, font_color, font_size, role FROM chatbox_users WHERE username=? AND tenant_id=?");
    $q->execute([$username, $tenantId]);
    $p = $q->fetch(PDO::FETCH_OBJ);
    echo json_encode($p ?: ['error'=>'Not found']); exit;
}

// === GET PROFILE (registered user) ===
if ($action === 'get_profile') {
    $userId = (int)($_GET['user_id'] ?? 0);
    $tenantId = (int)($_GET['tenant_id'] ?? 0);
    $q = $pdo->prepare("SELECT id, username, display_name, avatar, font_style, font_color, font_size, role FROM chatbox_users WHERE id=? AND tenant_id=?");
    $q->execute([$userId, $tenantId]);
    $p = $q->fetch(PDO::FETCH_OBJ);
    echo json_encode($p ?: ['error'=>'Not found']); exit;
}

// === SAVE GUEST PROFILE (session-based) ===
if ($action === 'save_guest_profile') {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $profile = [
        'display_name' => trim($_POST['display_name'] ?? ''),
        'avatar' => trim($_POST['avatar'] ?? ''),
        'font_style' => trim($_POST['font_style'] ?? 'Inter'),
        'font_color' => trim($_POST['font_color'] ?? '#ffffff'),
        'font_size' => (int)($_POST['font_size'] ?? 13),
    ];
    $_SESSION['chatbox_guest_profile_' . $tenantId] = $profile;
    echo json_encode(['success'=>true, 'profile'=>$profile]); exit;
}

// === GET GUEST PROFILE ===
if ($action === 'get_guest_profile') {
    $tenantId = (int)($_GET['tenant_id'] ?? 0);
    $p = $_SESSION['chatbox_guest_profile_' . $tenantId] ?? null;
    echo json_encode($p ?: ['display_name'=>'','avatar'=>'','font_style'=>'Inter','font_color'=>'#ffffff','font_size'=>13]); exit;
}

// === UPLOAD AVATAR ===
if ($action === 'upload_avatar') {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) { echo json_encode(['error'=>'Upload failed']); exit; }
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['png','jpg','jpeg','gif','webp'])) { echo json_encode(['error'=>'Invalid format']); exit; }
    $dir = 'storage/chatbox/avatars/' . $tenantId;
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    $url = '/' . $dir . '/' . time() . '_' . preg_replace('/[^a-z0-9]/i', '', basename($_FILES['avatar']['name'])) . '.' . $ext;
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $url)) {
        echo json_encode(['success'=>true, 'url'=>$url]);
    } else { echo json_encode(['error'=>'Could not save file']); }
    exit;
}

// === GUEST CHECK ===
if ($action === 'guest_check') {
    $tenantId = (int)($_GET['tenant_id'] ?? 0);
    $guest = $_SESSION['chatbox_guest_' . $tenantId] ?? null;
    echo json_encode(['logged_in' => !!$guest, 'username' => $guest['username'] ?? null]);
    exit;
}

// === REACT TO MESSAGE ===
if ($action === 'react') {
    $msgId = (int)($_POST['message_id'] ?? 0);
    $userId = (int)($_POST['user_id'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '');
    if (!$msgId || !$emoji) { echo json_encode(['error'=>'Missing fields']); exit; }
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS chatbox_message_reactions (id int(11) NOT NULL AUTO_INCREMENT, message_id bigint(20) NOT NULL, user_id int(11) NOT NULL DEFAULT 0, username varchar(50) DEFAULT NULL, emoji varchar(20) NOT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (id), UNIQUE KEY msg_user (message_id,user_id,emoji)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $q = $pdo->prepare("INSERT IGNORE INTO chatbox_message_reactions (message_id,user_id,username,emoji) VALUES (?,?,?,?)");
        $q->execute([$msgId,$userId,$_POST['username']??'Guest',$emoji]);
        echo json_encode(['success'=>true]);
    } catch (Exception $e) { echo json_encode(['error'=>$e->getMessage()]); }
    exit;
}

// === GET REACTIONS ===
if ($action === 'get_reactions') {
    $ids = $_GET['ids'] ?? '';
    $ids = preg_replace('/[^0-9,]/', '', $ids);
    if (!$ids) { echo json_encode([]); exit; }
    $q = $pdo->query("SELECT message_id, emoji, COUNT(*) as count FROM chatbox_message_reactions WHERE message_id IN ($ids) GROUP BY message_id, emoji");
    echo json_encode($q->fetchAll(PDO::FETCH_OBJ));
    exit;
}

// === GET EMOJIS ===
if ($action === 'get_emojis') {
    $tenantId = (int)($_GET['tenant_id'] ?? 0);
    $q = $pdo->prepare("SELECT * FROM chatbox_emojis WHERE tenant_id=? ORDER BY name");
    $q->execute([$tenantId]);
    echo json_encode($q->fetchAll(PDO::FETCH_OBJ));
    exit;
}

// === SAVE EMOJI (admin) ===
if ($action === 'save_emoji' && isset($_SESSION['chatbox_admin'])) {
    $tenantId = (int)$_SESSION['chatbox_admin']['tenant_id'];
    $name = trim($_POST['name'] ?? '');
    $url = trim($_POST['url'] ?? '');
    if (!$name || !$url) { echo json_encode(['error'=>'Name and URL required']); exit; }
    $isUrl = str_starts_with($url, 'http');
    $size = 0;
    if (!$isUrl && isset($_FILES['emoji'])) {
        $ext = strtolower(pathinfo($_FILES['emoji']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png','gif','webp','svg','jpg'])) { echo json_encode(['error'=>'Invalid format']); exit; }
        $dir = 'storage/chatbox/emojis/'.$tenantId;
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        $size = $_FILES['emoji']['size'];
        $url = '/'.$dir.'/'.time().'_'.preg_replace('/[^a-z0-9-]/i','',$name).'.'.$ext;
        move_uploaded_file($_FILES['emoji']['tmp_name'], $url);
    }
    $q = $pdo->prepare("INSERT INTO chatbox_emojis (tenant_id,name,url,is_url,file_size) VALUES (?,?,?,?,?)");
    $q->execute([$tenantId,$name,$url,$isUrl?1:0,$size]);
    echo json_encode(['success'=>true]);
    exit;
}

// === DELETE EMOJI (admin) ===
if ($action === 'delete_emoji' && isset($_SESSION['chatbox_admin'])) {
    $tenantId = (int)$_SESSION['chatbox_admin']['tenant_id'];
    $pdo->prepare("DELETE FROM chatbox_emojis WHERE id=? AND tenant_id=?")->execute([(int)($_POST['id']??0),$tenantId]);
    echo json_encode(['success'=>true]);
    exit;
}

// === WEBRTC SIGNALING ===
if ($action === 'signal') {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $roomId = (int)($_POST['room_id'] ?? 0);
    $userId = (int)($_POST['user_id'] ?? 0);
    $username = trim($_POST['username'] ?? 'Guest');
    $type = $_POST['type'] ?? ''; // offer / answer / ice / leave / join
    $payload = $_POST['payload'] ?? '';
    if (!$roomId || !$type) { echo json_encode(['error'=>'Missing fields']); exit; }
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS chatbox_signals (id int(11) NOT NULL AUTO_INCREMENT, room_id int(11) NOT NULL, user_id int(11) NOT NULL DEFAULT 0, username varchar(50) DEFAULT NULL, type varchar(10) NOT NULL, payload text, created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (id), KEY room_id (room_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if ($type === 'leave') {
            $pdo->prepare("DELETE FROM chatbox_signals WHERE room_id=? AND user_id=?")->execute([$roomId,$userId]);
            echo json_encode(['success'=>true]); exit;
        }
        $q = $pdo->prepare("INSERT INTO chatbox_signals (room_id,user_id,username,type,payload) VALUES (?,?,?,?,?)");
        $q->execute([$roomId,$userId,$username,$type,$payload]);
        echo json_encode(['success'=>true]);
    } catch (Exception $e) { echo json_encode(['error'=>$e->getMessage()]); }
    exit;
}

// === GET SIGNALS (poll) ===
if ($action === 'get_signals') {
    $roomId = (int)($_GET['room_id'] ?? 0);
    $userId = (int)($_GET['user_id'] ?? 0);
    $after = (int)($_GET['after'] ?? 0);
    if (!$roomId) { echo json_encode([]); exit; }
    $sql = "SELECT * FROM chatbox_signals WHERE room_id=? AND user_id!=? AND id>? ORDER BY id LIMIT 50";
    $q = $pdo->prepare($sql);
    $q->execute([$roomId,$userId,$after]);
    echo json_encode($q->fetchAll(PDO::FETCH_OBJ));
    exit;
}

// === ONLINE USERS IN ROOM ===
if ($action === 'room_users') {
    $roomId = (int)($_GET['room_id'] ?? 0);
    $tenantId = (int)($_GET['tenant_id'] ?? 0);
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS chatbox_online (id int(11) NOT NULL AUTO_INCREMENT, room_id int(11) NOT NULL, user_id int(11) NOT NULL DEFAULT 0, username varchar(50) DEFAULT NULL, joined_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (id), UNIQUE KEY room_user (room_id,user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $q = $pdo->prepare("SELECT username FROM chatbox_online WHERE room_id=? ORDER BY joined_at");
        $q->execute([$roomId]);
        echo json_encode($q->fetchAll(PDO::FETCH_OBJ));
    } catch (Exception $e) { echo json_encode([]); }
    exit;
}

// === JOIN ONLINE ===
if ($action === 'join_online') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $userId = (int)($_POST['user_id'] ?? 0);
    $username = trim($_POST['username'] ?? 'Guest');
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS chatbox_online (id int(11) NOT NULL AUTO_INCREMENT, room_id int(11) NOT NULL, user_id int(11) NOT NULL DEFAULT 0, username varchar(50) DEFAULT NULL, joined_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (id), UNIQUE KEY room_user (room_id,user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->prepare("INSERT IGNORE INTO chatbox_online (room_id,user_id,username) VALUES (?,?,?)")->execute([$roomId,$userId,$username]);
        echo json_encode(['success'=>true]);
    } catch (Exception $e) { echo json_encode(['error'=>$e->getMessage()]); }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
