<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

function auth() {
    global $pdo;
    if (!empty($_SESSION['user'])) {
        $u = $_SESSION['user']; $id = is_object($u) ? ($u->id??0) : ($u['id']??0);
        if ($id) { $q = $pdo->prepare("SELECT id,username,email FROM hosting_users WHERE id=?"); $q->execute([$id]); return $q->fetch(PDO::FETCH_OBJ); }
    }
    $key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
    if ($key) { $q = $pdo->prepare("SELECT hu.id,hu.username,hu.email FROM hosting_users hu JOIN api_keys ak ON ak.user_id=hu.id WHERE ak.api_key=? AND ak.is_active=1"); $q->execute([$key]); return $q->fetch(PDO::FETCH_OBJ); }
    return null;
}
$user = auth(); $uid = $user ? (int)$user->id : 0;

// === GET CATEGORIES + ROOMS ===
if ($action === 'index' && $uid) {
    $cats = $pdo->query("SELECT * FROM chat_categories ORDER BY sort_order")->fetchAll(PDO::FETCH_OBJ);
    $rooms = $pdo->prepare("SELECT r.*, (SELECT COUNT(*) FROM chat_room_members WHERE room_id=r.id) as members, (SELECT role FROM chat_room_members WHERE room_id=r.id AND user_id=?) as my_role FROM chat_rooms r WHERE r.is_active=1 ORDER BY r.sort_order");
    $rooms->execute([$uid]);
    $roomsList = $rooms->fetchAll(PDO::FETCH_OBJ);
    // DM conversations
    $dms = $pdo->prepare("SELECT c.*, (SELECT message FROM chatbox_messages WHERE conversation_id=c.id ORDER BY id DESC LIMIT 1) as last_msg, (SELECT COUNT(*) FROM chatbox_messages WHERE conversation_id=c.id AND id > COALESCE((SELECT last_read_at FROM chatbox_participants WHERE conversation_id=c.id AND user_id=?),0)) as unread FROM chatbox_conversations c JOIN chatbox_participants p ON p.conversation_id=c.id AND p.user_id=? WHERE c.type='direct' AND p.is_archived=0 ORDER BY c.updated_at DESC");
    $dms->execute([$uid, $uid]);
    echo json_encode(['categories'=>$cats, 'rooms'=>$roomsList, 'dms'=>$dms->fetchAll(PDO::FETCH_OBJ)]);
    exit;
}

// === GET ROOM MESSAGES ===
if ($action === 'messages' && $uid) {
    $roomId = (int)($_GET['room_id'] ?? 0);
    $before = (int)($_GET['before'] ?? 0);
    $limit = min((int)($_GET['limit'] ?? 50), 100);
    $check = $pdo->prepare("SELECT 1 FROM chat_room_members WHERE room_id=? AND user_id=?");
    $check->execute([$roomId, $uid]);
    if (!$check->fetch()) { echo json_encode(['error'=>'Access denied']); exit; }
    $sql = "SELECT m.* FROM chat_messages m WHERE m.room_id=? AND m.is_deleted=0";
    if ($before > 0) $sql .= " AND m.id < ?";
    $sql .= " ORDER BY m.id DESC LIMIT $limit";
    $q = $pdo->prepare($sql);
    if ($before > 0) $q->execute([$roomId, $before]); else $q->execute([$roomId]);
    $msgs = $q->fetchAll(PDO::FETCH_OBJ);
    $pdo->prepare("UPDATE chat_room_members SET last_seen=NOW() WHERE room_id=? AND user_id=?")->execute([$roomId, $uid]);
    echo json_encode(array_reverse($msgs));
    exit;
}

// === SEND ROOM MESSAGE ===
if ($action === 'send' && $uid) {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $msg = trim($_POST['message'] ?? '');
    $replyTo = (int)($_POST['reply_to'] ?? 0);
    if (!$roomId || !$msg) { echo json_encode(['error'=>'Missing fields']); exit; }
    $check = $pdo->prepare("SELECT 1 FROM chat_room_members WHERE room_id=? AND user_id=?");
    $check->execute([$roomId, $uid]);
    if (!$check->fetch()) { echo json_encode(['error'=>'Not a member']); exit; }
    $rate = $pdo->prepare("SELECT COUNT(*) FROM chat_messages WHERE user_id=? AND created_at>UNIX_TIMESTAMP()*1000-2000");
    $rate->execute([$uid]); if ($rate->fetchColumn() > 5) { echo json_encode(['error'=>'Slow down']); exit; }
    $now = round(microtime(true)*1000);
    $q = $pdo->prepare("INSERT INTO chat_messages (room_id, user_id, username, message, message_type, reply_to, created_at) VALUES (?,?,?,?,?,?,?)");
    $q->execute([$roomId, $uid, $user->username, $msg, 'text', $replyTo ?: null, $now]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success'=>true, 'id'=>$id, 'username'=>$user->username, 'message'=>$msg, 'created_at'=>$now]);
    exit;
}

// === CREATE ROOM ===
if ($action === 'create_room' && $uid) {
    $name = trim($_POST['name'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 1);
    if (!$name || strlen($name) < 2) { echo json_encode(['error'=>'Name required']); exit; }
    $q = $pdo->prepare("INSERT INTO chat_rooms (category_id, owner_id, name, description, icon, color, visibility, password, voice_enabled, video_enabled, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
    $q->execute([$catId, $uid, $name, $_POST['description']??'', $_POST['icon']??'', $_POST['color']??'#008cff', $_POST['visibility']??'public', $_POST['password']??null, (int)($_POST['voice_enabled']??0), (int)($_POST['video_enabled']??0)]);
    $roomId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO chat_room_members (room_id, user_id, role, joined_at) VALUES (?,?,'owner',NOW())")->execute([$roomId, $uid]);
    echo json_encode(['success'=>true, 'id'=>$roomId]);
    exit;
}

// === JOIN ROOM ===
if ($action === 'join_room' && $uid) {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $r = $pdo->prepare("SELECT * FROM chat_rooms WHERE id=? AND is_active=1");
    $r->execute([$roomId]); $room = $r->fetch(PDO::FETCH_OBJ);
    if (!$room) { echo json_encode(['error'=>'Room not found']); exit; }
    if ($room->visibility === 'password') {
        $pw = $_POST['password'] ?? '';
        if (!password_verify($pw, $room->password)) { echo json_encode(['error'=>'Wrong password']); exit; }
    }
    if ($room->max_users > 0) {
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM chat_room_members WHERE room_id=?");
        $cnt->execute([$roomId]);
        if ($cnt->fetchColumn() >= $room->max_users) { echo json_encode(['error'=>'Room full']); exit; }
    }
    $pdo->prepare("INSERT IGNORE INTO chat_room_members (room_id, user_id, role, joined_at) VALUES (?,?,'member',NOW())")->execute([$roomId, $uid]);
    echo json_encode(['success'=>true]);
    exit;
}

// === LEAVE ROOM ===
if ($action === 'leave_room' && $uid) {
    $pdo->prepare("DELETE FROM chat_room_members WHERE room_id=? AND user_id=? AND role!='owner'")->execute([(int)($_POST['room_id']??0), $uid]);
    echo json_encode(['success'=>true]); exit;
}

// === UPDATE ROOM ===
if ($action === 'update_room' && $uid) {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $check = $pdo->prepare("SELECT 1 FROM chat_room_members WHERE room_id=? AND user_id=? AND role IN ('owner','admin')");
    $check->execute([$roomId, $uid]);
    if (!$check->fetch()) { echo json_encode(['error'=>'Permission denied']); exit; }
    $sets = []; $params = [];
    foreach (['name','description','icon','color','visibility','voice_enabled','video_enabled','max_users','slow_mode'] as $f) {
        if (isset($_POST[$f])) { $sets[] = "$f=?"; $params[] = $_POST[$f]; }
    }
    if (isset($_POST['password']) && $_POST['password'] !== '') { $sets[] = "password=?"; $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT); }
    if (!empty($sets)) { $params[] = $roomId; $pdo->prepare("UPDATE chat_rooms SET " . implode(',', $sets) . " WHERE id=?")->execute($params); }
    echo json_encode(['success'=>true]); exit;
}

// === DELETE ROOM ===
if ($action === 'delete_room' && $uid) {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $pdo->prepare("DELETE FROM chat_room_members WHERE room_id=?")->execute([$roomId]);
    $pdo->prepare("DELETE FROM chat_messages WHERE room_id=?")->execute([$roomId]);
    $pdo->prepare("UPDATE chat_rooms SET is_active=0 WHERE id=? AND owner_id=?")->execute([$roomId, $uid]);
    echo json_encode(['success'=>true]); exit;
}

// === GET DM CONVERSATIONS ===
if ($action === 'dms' && $uid) {
    $q = $pdo->prepare("SELECT c.*,(SELECT message FROM chatbox_messages WHERE conversation_id=c.id ORDER BY id DESC LIMIT 1) as last_msg,(SELECT COUNT(*) FROM chatbox_messages WHERE conversation_id=c.id AND id>COALESCE((SELECT last_read_at FROM chatbox_participants WHERE conversation_id=c.id AND user_id=?),0)) as unread FROM chatbox_conversations c JOIN chatbox_participants p ON p.conversation_id=c.id AND p.user_id=? WHERE c.type='direct' AND p.is_archived=0 ORDER BY c.updated_at DESC");
    $q->execute([$uid, $uid]);
    echo json_encode($q->fetchAll(PDO::FETCH_OBJ)); exit;
}

// === GET DM MESSAGES ===
if ($action === 'dm_messages' && $uid) {
    $convId = (int)($_GET['conversation_id'] ?? 0);
    $before = (int)($_GET['before'] ?? 0);
    $limit = min((int)($_GET['limit'] ?? 50), 100);
    $check = $pdo->prepare("SELECT 1 FROM chatbox_participants WHERE conversation_id=? AND user_id=?");
    $check->execute([$convId, $uid]);
    if (!$check->fetch()) { echo json_encode(['error'=>'Access denied']); exit; }
    $sql = "SELECT * FROM chatbox_messages WHERE conversation_id=?";
    if ($before > 0) $sql .= " AND id < ?";
    $sql .= " ORDER BY id DESC LIMIT $limit";
    $q = $pdo->prepare($sql);
    if ($before > 0) $q->execute([$convId, $before]); else $q->execute([$convId]);
    $msgs = $q->fetchAll(PDO::FETCH_OBJ);
    $pdo->prepare("UPDATE chatbox_participants SET last_read_at=UNIX_TIMESTAMP()*1000 WHERE conversation_id=? AND user_id=?")->execute([$convId, $uid]);
    echo json_encode(array_reverse($msgs)); exit;
}

// === SEND DM ===
if ($action === 'send_dm' && $uid) {
    $convId = (int)($_POST['conversation_id'] ?? 0);
    $msg = trim($_POST['message'] ?? '');
    if (!$convId || !$msg) { echo json_encode(['error'=>'Missing fields']); exit; }
    $check = $pdo->prepare("SELECT 1 FROM chatbox_participants WHERE conversation_id=? AND user_id=?");
    $check->execute([$convId, $uid]);
    if (!$check->fetch()) { echo json_encode(['error'=>'Not a participant']); exit; }
    $q = $pdo->prepare("INSERT INTO chatbox_messages (conversation_id, user_id, username, message, message_type, created_at) VALUES (?,?,?,?,?,UNIX_TIMESTAMP()*1000)");
    $q->execute([$convId, $uid, $user->username, $msg, 'text']);
    $pdo->prepare("UPDATE chatbox_conversations SET updated_at=NOW() WHERE id=?")->execute([$convId]);
    echo json_encode(['success'=>true]); exit;
}

// === CREATE DM ===
if ($action === 'create_dm' && $uid) {
    $otherId = (int)($_POST['user_id'] ?? 0);
    if ($otherId === $uid) { echo json_encode(['error'=>'Cannot DM yourself']); exit; }
    $check = $pdo->prepare("SELECT c.id FROM chatbox_conversations c JOIN chatbox_participants p1 ON p1.conversation_id=c.id AND p1.user_id=? JOIN chatbox_participants p2 ON p2.conversation_id=c.id AND p2.user_id=? WHERE c.type='direct' AND (SELECT COUNT(*) FROM chatbox_participants WHERE conversation_id=c.id)=2");
    $check->execute([$uid, $otherId]);
    $existing = $check->fetchColumn();
    if ($existing) { echo json_encode(['success'=>true,'id'=>$existing,'existing'=>true]); exit; }
    $q = $pdo->prepare("INSERT INTO chatbox_conversations (type, created_at) VALUES ('direct', NOW())");
    $q->execute(); $convId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO chatbox_participants (conversation_id, user_id, role, joined_at) VALUES (?,?,'owner',NOW())")->execute([$convId, $uid]);
    $pdo->prepare("INSERT INTO chatbox_participants (conversation_id, user_id, role, joined_at) VALUES (?,?,'member',NOW())")->execute([$convId, $otherId]);
    echo json_encode(['success'=>true,'id'=>$convId]); exit;
}

// === SEARCH USERS ===
if ($action === 'search_users' && $uid) {
    $q = trim($_GET['q'] ?? ''); if (strlen($q) < 2) { echo json_encode([]); exit; }
    $s = $pdo->prepare("SELECT id,username,email FROM hosting_users WHERE (username LIKE ? OR email LIKE ?) AND id!=? AND status='active' LIMIT 15");
    $s->execute(["%$q%", "%$q%", $uid]); echo json_encode($s->fetchAll(PDO::FETCH_OBJ)); exit;
}

// === GET ROOM MEMBERS ===
if ($action === 'members' && $uid) {
    $q = $pdo->prepare("SELECT m.user_id, m.role, m.joined_at, hu.username FROM chat_room_members m JOIN hosting_users hu ON hu.id=m.user_id WHERE m.room_id=? ORDER BY FIELD(m.role,'owner','admin','moderator','vip','member')");
    $q->execute([(int)($_GET['room_id']??0)]); echo json_encode($q->fetchAll(PDO::FETCH_OBJ)); exit;
}

echo json_encode(['error'=>'Unknown action']);
