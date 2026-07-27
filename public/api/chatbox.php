<?php
/**
 * Chatbox Client API — Planet Hosts Chat System
 * Separated from Support Live Chat (chat_* tables)
 */
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Helper: get current user from session or auth header
function getAuthUser($pdo) {
    if (!empty($_SESSION['user'])) {
        $u = $_SESSION['user'];
        $id = is_object($u) ? ($u->id ?? 0) : ($u['id'] ?? 0);
        if ($id) {
            $q = $pdo->prepare("SELECT id, username, email, status FROM hosting_users WHERE id=?");
            $q->execute([$id]);
            return $q->fetch(PDO::FETCH_OBJ);
        }
    }
    // API key auth
    $key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
    if ($key) {
        $q = $pdo->prepare("SELECT hu.id, hu.username, hu.email, hu.status FROM hosting_users hu JOIN api_keys ak ON ak.user_id = hu.id WHERE ak.api_key = ? AND ak.is_active = 1");
        $q->execute([$key]);
        return $q->fetch(PDO::FETCH_OBJ);
    }
    return null;
}

// Helper: get package limits for a user
function getPackageLimits($pdo, $userId) {
    $q = $pdo->prepare("SELECT p.* FROM hosting_packages p JOIN hosting_users hu ON hu.package_id = p.id WHERE hu.id = ?");
    $q->execute([$userId]);
    $pkg = $q->fetch(PDO::FETCH_OBJ);
    if (!$pkg) return getDefaultLimits();
    
    // Map package type to chat limits
    $type = strtolower($pkg->type ?? 'web_hosting');
    $diskSpace = (int)($pkg->disk_space ?? 1);
    
    // Determine limits based on package disk_space as a proxy for tier
    if ($diskSpace >= 100) return getEnterpriseLimits();
    if ($diskSpace >= 30) return getBusinessLimits();
    if ($diskSpace >= 10) return getProLimits();
    if ($diskSpace >= 3) return getStarterLimits();
    return getDefaultLimits();
}

function getDefaultLimits() {
    return ['max_file_size' => 10485760, 'storage_limit' => 524288000, 'message_days' => 30, 'voice' => false, 'video' => false, 'screen_share' => false, 'max_emoji' => 10, 'stickers' => false, 'max_group' => 5, 'group_chat' => false];
}
function getStarterLimits() {
    return ['max_file_size' => 26214400, 'storage_limit' => 2147483648, 'message_days' => 90, 'voice' => false, 'video' => false, 'screen_share' => false, 'max_emoji' => 25, 'stickers' => true, 'max_group' => 10, 'group_chat' => true];
}
function getProLimits() {
    return ['max_file_size' => 104857600, 'storage_limit' => 10737418240, 'message_days' => 365, 'voice' => true, 'video' => false, 'screen_share' => false, 'max_emoji' => 100, 'stickers' => true, 'max_group' => 25, 'group_chat' => true];
}
function getBusinessLimits() {
    return ['max_file_size' => 262144000, 'storage_limit' => 21474836480, 'message_days' => 1095, 'voice' => true, 'video' => true, 'screen_share' => true, 'max_emoji' => 250, 'stickers' => true, 'max_group' => 50, 'group_chat' => true];
}
function getEnterpriseLimits() {
    return ['max_file_size' => 2147483647, 'storage_limit' => 0, 'message_days' => 0, 'voice' => true, 'video' => true, 'screen_share' => true, 'max_emoji' => -1, 'stickers' => true, 'max_group' => 999, 'group_chat' => true];
}

$user = getAuthUser($pdo);
$userId = $user ? (int)$user->id : 0;

// === GET CONVERSATIONS ===
if ($action === 'conversations' && $userId) {
    $q = $pdo->prepare("
        SELECT c.*, 
            (SELECT message FROM chatbox_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message,
            (SELECT username FROM chatbox_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_sender,
            (SELECT created_at FROM chatbox_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_time,
            (SELECT COUNT(*) FROM chatbox_participants WHERE conversation_id = c.id) as participant_count,
            (SELECT COUNT(*) FROM chatbox_messages WHERE conversation_id = c.id AND id > COALESCE((SELECT last_read_at FROM chatbox_participants WHERE conversation_id = c.id AND user_id = ?), 0)) as unread
        FROM chatbox_conversations c
        JOIN chatbox_participants p ON p.conversation_id = c.id AND p.user_id = ?
        WHERE p.is_archived = 0
        ORDER BY last_time DESC
    ");
    $q->execute([$userId, $userId]);
    echo json_encode($q->fetchAll(PDO::FETCH_OBJ));
    exit;
}

// === GET MESSAGES ===
if ($action === 'messages' && $userId) {
    $convId = (int)($_GET['conversation_id'] ?? 0);
    $before = (int)($_GET['before'] ?? 0);
    $limit = min((int)($_GET['limit'] ?? 50), 100);
    
    // Verify participant
    $check = $pdo->prepare("SELECT 1 FROM chatbox_participants WHERE conversation_id = ? AND user_id = ?");
    $check->execute([$convId, $userId]);
    if (!$check->fetch()) { echo json_encode(['error' => 'Access denied']); exit; }
    
    $sql = "SELECT m.*, COALESCE((SELECT JSON_ARRAYAGG(JSON_OBJECT('user_id', r.user_id, 'emoji', r.emoji)) FROM chatbox_reactions r WHERE r.message_id = m.id), '[]') as reactions FROM chatbox_messages m WHERE m.conversation_id = ?";
    if ($before > 0) $sql .= " AND m.id < ?";
    $sql .= " ORDER BY m.id DESC LIMIT " . $limit;
    $q = $pdo->prepare($sql);
    if ($before > 0) $q->execute([$convId, $before]);
    else $q->execute([$convId]);
    
    $msgs = $q->fetchAll(PDO::FETCH_OBJ);
    // Update last_read
    $pdo->prepare("UPDATE chatbox_participants SET last_read_at = UNIX_TIMESTAMP() * 1000 WHERE conversation_id = ? AND user_id = ?")->execute([$convId, $userId]);
    
    echo json_encode(array_reverse($msgs));
    exit;
}

// === SEND MESSAGE ===
if ($action === 'send' && $userId) {
    $convId = (int)($_POST['conversation_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $parentId = (int)($_POST['parent_id'] ?? 0);
    $msgType = $_POST['message_type'] ?? 'text';
    
    if (!$convId || !$message) { echo json_encode(['error' => 'Missing fields']); exit; }
    
    // Verify participant
    $check = $pdo->prepare("SELECT 1 FROM chatbox_participants WHERE conversation_id = ? AND user_id = ?");
    $check->execute([$convId, $userId]);
    if (!$check->fetch()) { echo json_encode(['error' => 'Not a participant']); exit; }
    
    // Check message rate limit (anti-spam)
    $rateCheck = $pdo->prepare("SELECT COUNT(*) FROM chatbox_messages WHERE user_id = ? AND created_at > UNIX_TIMESTAMP() * 1000 - 2000");
    $rateCheck->execute([$userId]);
    if ($rateCheck->fetchColumn() > 5) { echo json_encode(['error' => 'Slow down']); exit; }
    
    $username = $user->username ?? 'User';
    $q = $pdo->prepare("INSERT INTO chatbox_messages (conversation_id, tenant_id, user_id, username, message, message_type, parent_id, created_at) VALUES (?, 0, ?, ?, ?, ?, ?, UNIX_TIMESTAMP() * 1000)");
    $q->execute([$convId, $userId, $username, $message, $msgType, $parentId ?: null]);
    $msgId = $pdo->lastInsertId();
    
    // Update conversation timestamp
    $pdo->prepare("UPDATE chatbox_conversations SET updated_at = NOW() WHERE id = ?")->execute([$convId]);
    
    echo json_encode(['success' => true, 'id' => $msgId, 'username' => $username, 'message' => $message, 'created_at' => time() * 1000]);
    exit;
}

// === CREATE CONVERSATION ===
if ($action === 'create_conversation' && $userId) {
    $type = $_POST['type'] ?? 'direct';
    $name = trim($_POST['name'] ?? '');
    $memberIds = $_POST['member_ids'] ?? [];
    if (is_string($memberIds)) $memberIds = json_decode($memberIds, true) ?: [];
    $memberIds = array_map('intval', $memberIds);
    
    if ($type === 'direct' && count($memberIds) === 1) {
        $otherId = $memberIds[0];
        // Check existing direct conversation
        $check = $pdo->prepare("SELECT c.id FROM chatbox_conversations c JOIN chatbox_participants p1 ON p1.conversation_id = c.id AND p1.user_id = ? JOIN chatbox_participants p2 ON p2.conversation_id = c.id AND p2.user_id = ? WHERE c.type = 'direct' AND (SELECT COUNT(*) FROM chatbox_participants WHERE conversation_id = c.id) = 2");
        $check->execute([$userId, $otherId]);
        $existing = $check->fetchColumn();
        if ($existing) { echo json_encode(['success' => true, 'id' => $existing, 'existing' => true]); exit; }
    }
    
    $limits = getPackageLimits($pdo, $userId);
    if ($type === 'group' && !$limits['group_chat']) { echo json_encode(['error' => 'Group chat not available on your plan']); exit; }
    if ($type === 'group' && count($memberIds) > $limits['max_group']) { echo json_encode(['error' => 'Max ' . $limits['max_group'] . ' members on your plan']); exit; }
    
    $q = $pdo->prepare("INSERT INTO chatbox_conversations (type, name, created_by, tenant_id, created_at) VALUES (?, ?, ?, 0, NOW())");
    $q->execute([$type, $name ?: null, $userId]);
    $convId = $pdo->lastInsertId();
    
    // Add creator
    $pdo->prepare("INSERT INTO chatbox_participants (conversation_id, user_id, role, joined_at) VALUES (?, ?, 'owner', NOW())")->execute([$convId, $userId]);
    // Add members
    foreach ($memberIds as $mid) {
        $pdo->prepare("INSERT IGNORE INTO chatbox_participants (conversation_id, user_id, role, joined_at) VALUES (?, ?, 'member', NOW())")->execute([$convId, $mid]);
    }
    
    echo json_encode(['success' => true, 'id' => $convId]);
    exit;
}

// === SEARCH USERS ===
if ($action === 'search_users' && $userId) {
    $query = trim($_GET['q'] ?? '');
    if (strlen($query) < 2) { echo json_encode([]); exit; }
    $q = $pdo->prepare("SELECT id, username, email FROM hosting_users WHERE (username LIKE ? OR email LIKE ?) AND id != ? AND status = 'active' LIMIT 20");
    $q->execute(['%' . $query . '%', '%' . $query . '%', $userId]);
    echo json_encode($q->fetchAll(PDO::FETCH_OBJ));
    exit;
}

// === ADD REACTION ===
if ($action === 'react' && $userId) {
    $msgId = (int)($_POST['message_id'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '');
    if (!$msgId || !$emoji) { echo json_encode(['error' => 'Missing fields']); exit; }
    $q = $pdo->prepare("INSERT IGNORE INTO chatbox_reactions (message_id, user_id, emoji, created_at) VALUES (?, ?, ?, NOW())");
    $q->execute([$msgId, $userId, $emoji]);
    echo json_encode(['success' => true]);
    exit;
}

// === REMOVE REACTION ===
if ($action === 'unreact' && $userId) {
    $msgId = (int)($_POST['message_id'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '');
    $pdo->prepare("DELETE FROM chatbox_reactions WHERE message_id = ? AND user_id = ? AND emoji = ?")->execute([$msgId, $userId, $emoji]);
    echo json_encode(['success' => true]);
    exit;
}

// === EDIT MESSAGE ===
if ($action === 'edit' && $userId) {
    $msgId = (int)($_POST['message_id'] ?? 0);
    $newMsg = trim($_POST['message'] ?? '');
    $q = $pdo->prepare("UPDATE chatbox_messages SET message = ?, edited_at = NOW() WHERE id = ? AND user_id = ? AND message_type = 'text'");
    $q->execute([$newMsg, $msgId, $userId]);
    echo json_encode(['success' => $q->rowCount() > 0]);
    exit;
}

// === DELETE MESSAGE ===
if ($action === 'delete' && $userId) {
    $msgId = (int)($_POST['message_id'] ?? 0);
    $q = $pdo->prepare("UPDATE chatbox_messages SET message = '[deleted]', message_type = 'system', file_url = NULL WHERE id = ? AND user_id = ?");
    $q->execute([$msgId, $userId]);
    echo json_encode(['success' => $q->rowCount() > 0]);
    exit;
}

// === GET PACKAGE LIMITS ===
if ($action === 'my_limits' && $userId) {
    echo json_encode(getPackageLimits($pdo, $userId));
    exit;
}

// === UPDATE STATUS ===
if ($action === 'status' && $userId) {
    $status = $_POST['status'] ?? 'online';
    if (!in_array($status, ['online','away','busy','invisible','offline'])) $status = 'online';
    $pdo->prepare("UPDATE hosting_users SET status = ? WHERE id = ?")->execute([$status, $userId]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action or not authenticated']);
