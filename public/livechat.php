<?php
// Live Chat Portal - Multi-tenant operator panel
session_start();
$action = $_GET['action'] ?? 'login';
$error = '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// ─── LOGIN ───
if ($_POST && $action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT u.*, t.id as tenant_id, t.company_name, t.widget_color, h.domain FROM chat_users u JOIN chat_tenants t ON u.tenant_id = t.id JOIN hosting_users h ON t.hosting_user_id = h.id WHERE u.username = ? AND u.is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if ($user && password_verify($password, $user->password_hash)) {
        $_SESSION['livechat_user'] = ['id'=>$user->id,'tenant_id'=>$user->tenant_id,'username'=>$user->username,'display_name'=>$user->display_name?:$user->username,'role'=>$user->role,'company'=>$user->company_name,'domain'=>$user->domain,'color'=>$user->widget_color];
        $pdo->prepare("UPDATE chat_users SET status='online' WHERE id=?")->execute([$user->id]);
        header('Location: /livechat?action=panel');
        exit;
    }
    $error = 'Invalid credentials';
}

if ($action === 'logout') {
    if (isset($_SESSION['livechat_user'])) $pdo->prepare("UPDATE chat_users SET status='offline' WHERE id=?")->execute([$_SESSION['livechat_user']['id']]);
    session_destroy();
    header('Location: /livechat'); exit;
}

if ($action !== 'panel') { ?>
<!DOCTYPE html><html><head><title>Live Chat Login - Planet Hosts</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0"><link rel="stylesheet" href="/theme/assets/css/style.css">
<style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;font-family:Inter,sans-serif}.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}.login-wrap{width:100%;max-width:380px;padding:20px;position:relative}.login-card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:32px 28px;text-align:center}.logo img{width:40px;height:40px;border-radius:10px;margin-bottom:6px}h1{color:#fff;font-size:20px;margin:0 0 4px}h1 span{color:#008cff}p{color:#64748b;font-size:13px;margin:0 0 20px}.form-group{margin-bottom:14px;text-align:left}.form-group label{display:block;margin-bottom:4px;font-size:12px;color:#94a3b8;font-weight:600}.form-group input{width:100%;padding:11px 14px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:14px;outline:none;box-sizing:border-box}.form-group input:focus{border-color:#008cff}.btn{width:100%;padding:12px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer}.alert{padding:10px;border-radius:8px;margin-bottom:14px;font-size:13px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
select{background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e0e0e0;padding:10px;width:100%;outline:none;box-sizing:border-box}select option{background:#0a0f1a;color:#e0e0e0}
textarea{width:100%;padding:11px 14px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:14px;outline:none;box-sizing:border-box;font-family:Inter,sans-serif}
</style></head><body>
<div class="bg"></div>
<div class="login-wrap"><div class="login-card">
<div class="logo"><img src="/theme/assets/img/logo.png" alt=""><h1><span>Live</span> Chat</h1></div>
<p>Operator sign in</p>
<?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST"><div class="form-group"><label>Username</label><input name="username" required></div>
<div class="form-group"><label>Password</label><input name="password" type="password" required></div>
<button type="submit" class="btn">Sign In</button></form></div></div></body></html>
<?php exit; }

// ─── PANEL ───
$user = $_SESSION['livechat_user'] ?? null;
if (!$user) { header('Location: /livechat'); exit; }
$tid = $user['tenant_id'];
$role = $user['role'];
$isMgr = $role === 'manager';
$tab = $_GET['tab'] ?? 'dashboard';

// ─── CRUD HANDLERS ───
if ($_POST && $tab === 'operators' && $isMgr) {
    $pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO chat_users (tenant_id,username,password_hash,display_name,email,role,max_chats) VALUES (?,?,?,?,?,?,?)")->execute([$tid,$_POST['username'],$pw,$_POST['display_name'],$_POST['email'],$_POST['role'],(int)$_POST['max_chats']]);
    $_SESSION['msg'] = 'Operator created';
    header('Location: /livechat?action=panel&tab=operators'); exit;
}
if ($tab === 'delete_op' && $isMgr && $_GET['id']) {
    $pdo->prepare("DELETE FROM chat_users WHERE id=? AND tenant_id=?")->execute([$_GET['id'], $tid]);
    header('Location: /livechat?action=panel&tab=operators'); exit;
}
if ($_POST && $tab === 'departments' && $isMgr) {
    $pdo->prepare("INSERT INTO chat_departments (tenant_id,name,description) VALUES (?,?,?)")->execute([$tid,$_POST['name'],$_POST['description']]);
    header('Location: /livechat?action=panel&tab=departments'); exit;
}
if ($tab === 'delete_dept' && $isMgr && $_GET['id']) {
    $pdo->prepare("DELETE FROM chat_departments WHERE id=? AND tenant_id=?")->execute([$_GET['id'], $tid]);
    header('Location: /livechat?action=panel&tab=departments'); exit;
}
if ($_POST && $tab === 'canned') {
    $pdo->prepare("INSERT INTO chat_canned_responses (tenant_id,title,message,category) VALUES (?,?,?,?)")->execute([$tid,$_POST['title'],$_POST['message'],$_POST['category']]);
    header('Location: /livechat?action=panel&tab=canned'); exit;
}
if ($tab === 'delete_canned' && $_GET['id']) {
    $pdo->prepare("DELETE FROM chat_canned_responses WHERE id=? AND tenant_id=?")->execute([$_GET['id'], $tid]);
    header('Location: /livechat?action=panel&tab=canned'); exit;
}
if ($tab === 'close_chat' && $_GET['id']) {
    $pdo->prepare("UPDATE chat_sessions SET status='closed', closed_at=NOW() WHERE id=? AND tenant_id=?")->execute([$_GET['id'], $tid]);
    header('Location: /livechat?action=panel'); exit;
}
if ($tab === 'assign' && $_GET['id'] && $_GET['op']) {
    $pdo->prepare("UPDATE chat_sessions SET assigned_to=? WHERE id=? AND tenant_id=?")->execute([$_GET['op'], $_GET['id'], $tid]);
    header('Location: /livechat?action=panel'); exit;
}
if ($_POST && $tab === 'transfer' && $_GET['id']) {
    $pdo->prepare("INSERT INTO chat_transfers (session_id,transferred_by,transferred_to,department) VALUES (?,?,?,?)")->execute([$_GET['id'], $user['id'], $_POST['operator_id'] ?: null, $_POST['department_id'] ?: null]);
    $_SESSION['msg'] = 'Chat transferred';
    header('Location: /livechat?action=panel'); exit;
}

$_SESSION['msg'] = ''; // Clear message after redirect

// ─── DATA ───
$chats = $pdo->prepare("SELECT cs.*, cd.name as dept_name, cu.display_name as assigned_name FROM chat_sessions cs LEFT JOIN chat_departments cd ON cs.department_id = cd.id LEFT JOIN chat_users cu ON cs.assigned_to = cu.id WHERE cs.tenant_id = ? ORDER BY FIELD(cs.status,'waiting','active','closed'), cs.updated_at DESC LIMIT 50");
$chats->execute([$tid]); $chats = $chats->fetchAll(PDO::FETCH_OBJ);
$depts = $pdo->prepare("SELECT * FROM chat_departments WHERE tenant_id = ?");
$depts->execute([$tid]); $depts = $depts->fetchAll(PDO::FETCH_OBJ);
$ops = $pdo->prepare("SELECT * FROM chat_users WHERE tenant_id = ?");
$ops->execute([$tid]); $ops = $ops->fetchAll(PDO::FETCH_OBJ);
$canned = $pdo->prepare("SELECT * FROM chat_canned_responses WHERE tenant_id = ?");
$canned->execute([$tid]); $canned = $canned->fetchAll(PDO::FETCH_OBJ);

// Messages for active chat
$msgs = [];
if ($_GET['view'] ?? null) {
    $msgs = $pdo->prepare("SELECT * FROM chat_messages WHERE session_id = ? ORDER BY id");
    $msgs->execute([$_GET['view']]); $msgs = $msgs->fetchAll(PDO::FETCH_OBJ);
}

// Reports
$totalChats = count($chats);
$avgResponse = $pdo->prepare("SELECT AVG(TIMESTAMPDIFF(SECOND,cs.created_at,cm.created_at)) as avg_response FROM chat_sessions cs JOIN chat_messages cm ON cm.session_id = cs.id AND cm.sender_type='operator' WHERE cs.tenant_id = ? AND cs.status='closed'");
$avgResponse->execute([$tid]); $avgResponse = round(($avgResponse->fetchColumn() ?: 0) / 60, 1);
?>
<!DOCTYPE html><html><head><title>Live Chat - <?php echo htmlspecialchars($user['company']); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#0a0e1a;color:#e0e0e0;font-family:Inter,sans-serif;font-size:14px}
.header{background:rgba(8,16,28,.95);border-bottom:1px solid rgba(255,255,255,.06);padding:12px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px}
.header h1{margin:0;font-size:18px;color:#fff}.header .meta{color:#64748b;font-size:13px}.header a{color:#f87171;text-decoration:none;font-size:13px;margin-left:12px}
.tabs{display:flex;gap:2px;padding:0 24px;background:rgba(8,16,28,.6);border-bottom:1px solid rgba(255,255,255,.04);overflow-x:auto}
.tabs a{padding:10px 18px;text-decoration:none;font-size:13px;color:#64748b;border-bottom:2px solid transparent;white-space:nowrap}
.tabs a.active{color:#008cff;border-bottom-color:#008cff;background:rgba(0,140,255,.04)}
.content{padding:20px 24px}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:20px}
.stat-card{background:rgba(8,16,28,.9);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:14px;text-align:center}
.stat-card h3{font-size:11px;color:#64748b;margin:0 0 4px}
.stat-card .value{font-size:22px;font-weight:700}
table{width:100%;border-collapse:collapse;margin-top:8px}
th,td{padding:8px 12px;text-align:left;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px}
th{color:#008cff;font-weight:600}
.badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
.badge-waiting{background:rgba(250,204,21,.15);color:#facc15}.badge-active{background:rgba(74,222,128,.15);color:#4ade80}.badge-closed{background:rgba(100,116,139,.15);color:#64748b}
.badge-online{background:rgba(74,222,128,.15);color:#4ade80}.badge-away{background:rgba(250,204,21,.15);color:#facc15}.badge-offline{background:rgba(100,116,139,.15);color:#64748b}
.btn{padding:8px 16px;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px;display:inline-block;text-decoration:none}
.btn-sm{padding:4px 10px;font-size:11px}
.chat-msg{margin-bottom:8px;max-width:80%}
.chat-msg .bubble{display:inline-block;padding:8px 14px;border-radius:12px;font-size:14px;background:rgba(255,255,255,.06);color:#e0e0e0}
.chat-msg.operator .bubble{background:rgba(0,140,255,.15);color:#cce5ff}
.chat-msg .meta{font-size:11px;color:#64748b;margin-top:2px}
.chat-view{display:grid;grid-template-columns:1fr 300px;gap:16px;margin-top:12px}
.chat-messages{max-height:400px;overflow-y:auto;padding:12px;background:rgba(0,0,0,.2);border-radius:8px}
.msg{color:#64748b;font-size:13px;margin-bottom:16px}
select,textarea,input:not([type=checkbox]):not([type=file]){background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#e0e0e0;padding:8px 12px;outline:none;width:100%;box-sizing:border-box;margin-top:4px}
select option{background:#0a0f1a;color:#e0e0e0}
</style></head><body>
<div class="header">
<div><h1>💬 Live Chat — <?php echo htmlspecialchars($user['company']); ?></h1>
<div class="meta">👤 <?php echo htmlspecialchars($user['display_name']); ?> (<?php echo $role; ?>) · <?php echo date('M j, Y g:i A'); ?></div></div>
<div style="display:flex;align-items:center;gap:12px">
<label style="color:#64748b;font-size:12px">Status:</label>
<select onchange="fetch('/livechat.php?action=status&s='+this.value)" style="width:auto;padding:4px 8px;font-size:12px">
<option value="online" <?php echo ($user['status']??'')==='online'?'selected':''; ?>>Online</option>
<option value="away" <?php echo ($user['status']??'')==='away'?'selected':''; ?>>Away</option>
<option value="offline" <?php echo ($user['status']??'')==='offline'?'selected':''; ?>>Offline</option>
</select>
<a href="/livechat?action=logout" style="color:#f87171">Logout</a>
</div></div>

<div class="tabs">
<a href="/livechat?action=panel&tab=dashboard" class="<?php echo $tab==='dashboard'?'active':''; ?>">📊 Dashboard</a>
<a href="/livechat?action=panel&tab=chats" class="<?php echo $tab==='chats'?'active':''; ?>">💬 Chats</a>
<a href="/livechat?action=panel&tab=operators" class="<?php echo $tab==='operators'?'active':''; ?>">👤 Operators</a>
<a href="/livechat?action=panel&tab=departments" class="<?php echo $tab==='departments'?'active':''; ?>">📂 Departments</a>
<a href="/livechat?action=panel&tab=canned" class="<?php echo $tab==='canned'?'active':''; ?>">⚡ Canned</a>
<a href="/livechat?action=panel&tab=reports" class="<?php echo $tab==='reports'?'active':''; ?>">📈 Reports</a>
</div>

<div class="content">
<?php if ($_SESSION['msg'] ?? ''): ?>
<div class="alert alert-success" style="margin-bottom:12px"><?php echo htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
<?php endif; ?>

<?php if ($tab === 'dashboard'): ?>
<div class="stats-grid">
<div class="stat-card"><h3>Waiting</h3><div class="value" style="color:#facc15"><?php echo count(array_filter($chats,fn($c)=>$c->status==='waiting')); ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo count(array_filter($chats,fn($c)=>$c->status==='active')); ?></div></div>
<div class="stat-card"><h3>Closed</h3><div class="value" style="color:#64748b"><?php echo count(array_filter($chats,fn($c)=>$c->status==='closed')); ?></div></div>
<div class="stat-card"><h3>Operators</h3><div class="value"><?php echo count($ops); ?></div></div>
<div class="stat-card"><h3>Avg Response</h3><div class="value" style="font-size:16px"><?php echo $avgResponse; ?> min</div></div>
</div>
<table><tr><th>#</th><th>Visitor</th><th>Dept</th><th>Assigned</th><th>Status</th><th>Since</th><th></th></tr>
<?php foreach ($chats as $c): ?>
<tr><td><?php echo $c->id; ?></td><td><?php echo htmlspecialchars($c->visitor_name?:'Visitor'); ?></td><td><?php echo htmlspecialchars($c->dept_name?:$c->department); ?></td><td><?php echo htmlspecialchars($c->assigned_name?:'-'); ?></td>
<td><span class="badge badge-<?php echo $c->status; ?>"><?php echo $c->status; ?></span></td><td><?php echo substr($c->created_at,0,16); ?></td>
<td><a href="/livechat?action=panel&tab=chats&view=<?php echo $c->id; ?>" class="btn btn-sm" style="background:rgba(0,140,255,.15);color:#008cff">View</a>
<?php if ($c->status!=='closed'): ?><a href="/livechat?action=panel&tab=close_chat&id=<?php echo $c->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Close?')">Close</a><?php endif; ?></td></tr>
<?php endforeach; ?></table>

<?php elseif ($tab === 'chats'): ?>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<a href="/livechat?action=panel&tab=chats" class="btn btn-sm" style="background:rgba(0,140,255,.15);color:#008cff">All</a>
<a href="/livechat?action=panel&tab=chats&filter=waiting" class="btn btn-sm" style="background:rgba(250,204,21,.15);color:#facc15">Waiting</a>
<a href="/livechat?action=panel&tab=chats&filter=active" class="btn btn-sm" style="background:rgba(74,222,128,.15);color:#4ade80">Active</a>
</div>
<?php if ($_GET['view'] ?? null): $sessionId = (int)$_GET['view']; ?>
<div class="chat-view">
<div>
<h3 style="margin:0 0 8px;font-size:16px;color:var(--accent)">Chat #<?php echo $sessionId; ?></h3>
<div class="chat-messages">
<?php foreach ($msgs as $m): ?>
<div class="chat-msg <?php echo $m->sender_type; ?>"><div class="bubble"><?php echo htmlspecialchars($m->message); ?></div><div class="meta"><?php echo htmlspecialchars($m->sender_name); ?> · <?php echo substr($m->created_at,11,5); ?></div></div>
<?php endforeach; ?></div>
</div>
<div>
<h4 style="margin:0 0 8px;color:var(--accent);font-size:14px">Actions</h4>
<form method="POST" action="/livechat?action=panel&tab=transfer&id=<?php echo $sessionId; ?>">
<div style="margin-bottom:8px"><label style="font-size:12px;color:#64748b">Assign Operator</label>
<select name="operator_id"><option value="">— None —</option>
<?php foreach ($ops as $o): ?><option value="<?php echo $o->id; ?>"><?php echo htmlspecialchars($o->display_name?:$o->username); ?></option><?php endforeach; ?></select></div>
<div style="margin-bottom:8px"><label style="font-size:12px;color:#64748b">Transfer Dept</label>
<select name="department_id"><option value="">— None —</option>
<?php foreach ($depts as $d): ?><option value="<?php echo $d->id; ?>"><?php echo htmlspecialchars($d->name); ?></option><?php endforeach; ?></select></div>
<button type="submit" class="btn btn-sm" style="background:rgba(0,140,255,.15);color:#008cff">Transfer</button>
</form>
<a href="/livechat?action=panel&tab=close_chat&id=<?php echo $sessionId; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171;margin-top:8px;display:inline-block" onclick="return confirm('Close chat?')">Close Chat</a>
</div></div>
<?php else: ?>
<table><tr><th>#</th><th>Visitor</th><th>Dept</th><th>Assigned</th><th>Status</th><th>Since</th><th></th></tr>
<?php $filter = $_GET['filter'] ?? ''; foreach ($chats as $c): if ($filter && $c->status !== $filter) continue; ?>
<tr><td><?php echo $c->id; ?></td><td><?php echo htmlspecialchars($c->visitor_name?:'Visitor'); ?></td><td><?php echo htmlspecialchars($c->dept_name?:$c->department); ?></td><td><?php echo htmlspecialchars($c->assigned_name?:'-'); ?></td>
<td><span class="badge badge-<?php echo $c->status; ?>"><?php echo $c->status; ?></span></td><td><?php echo substr($c->created_at,0,16); ?></td>
<td><a href="/livechat?action=panel&tab=chats&view=<?php echo $c->id; ?>" class="btn btn-sm" style="background:rgba(0,140,255,.15);color:#008cff">View</a></td></tr>
<?php endforeach; ?></table>
<?php endif; ?>

<?php elseif ($tab === 'operators'): ?>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<a class="btn btn-sm primary" onclick="document.getElementById('opForm').classList.toggle('hidden')">+ Add Operator</a>
</div>
<div id="opForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/livechat?action=panel&tab=operators">
<div class="form-group"><label>Username</label><input name="username" required></div>
<div class="form-group"><label>Display Name</label><input name="display_name"></div>
<div class="form-group"><label>Email</label><input name="email" type="email"></div>
<div class="form-group"><label>Password</label><input name="password" type="password" required></div>
<div class="form-group"><label>Role</label><select name="role"><option value="operator">Operator</option><option value="agent">Agent</option><option value="manager">Manager</option></select></div>
<div class="form-group"><label>Max Chats</label><input name="max_chats" type="number" value="5"></div>
<button type="submit" class="btn btn-sm primary">Create</button>
</form></div>
<table><tr><th>Username</th><th>Name</th><th>Role</th><th>Status</th><th>Chats</th><th></th></tr>
<?php foreach ($ops as $o): ?>
<tr><td><?php echo htmlspecialchars($o->username); ?></td><td><?php echo htmlspecialchars($o->display_name?:'-'); ?></td><td><?php echo $o->role; ?></td>
<td><span class="badge badge-<?php echo $o->status; ?>"><?php echo $o->status; ?></span></td><td><?php echo $o->max_chats; ?></td>
<td><a href="/livechat?action=panel&tab=delete_op&id=<?php echo $o->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; ?></table>

<?php elseif ($tab === 'departments'): ?>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<a class="btn btn-sm primary" onclick="document.getElementById('deptForm').classList.toggle('hidden')">+ Add Department</a>
</div>
<div id="deptForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/livechat?action=panel&tab=departments">
<div class="form-group"><label>Name</label><input name="name" required></div>
<div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
<button type="submit" class="btn btn-sm primary">Create</button>
</form></div>
<table><tr><th>Name</th><th>Description</th><th></th></tr>
<?php foreach ($depts as $d): ?>
<tr><td><?php echo htmlspecialchars($d->name); ?></td><td><?php echo htmlspecialchars($d->description?:'-'); ?></td>
<td><a href="/livechat?action=panel&tab=delete_dept&id=<?php echo $d->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; ?></table>

<?php elseif ($tab === 'canned'): ?>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<a class="btn btn-sm primary" onclick="document.getElementById('cannedForm').classList.toggle('hidden')">+ Add Canned Response</a>
</div>
<div id="cannedForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/livechat?action=panel&tab=canned">
<div class="form-group"><label>Title</label><input name="title" required></div>
<div class="form-group"><label>Category</label><input name="category" value="General"></div>
<div class="form-group"><label>Message</label><textarea name="message" rows="3" required></textarea></div>
<button type="submit" class="btn btn-sm primary">Save</button>
</form></div>
<table><tr><th>Title</th><th>Category</th><th>Message</th><th></th></tr>
<?php foreach ($canned as $c): ?>
<tr><td><?php echo htmlspecialchars($c->title); ?></td><td><?php echo htmlspecialchars($c->category); ?></td><td style="font-size:12px;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($c->message); ?></td>
<td><a href="/livechat?action=panel&tab=delete_canned&id=<?php echo $c->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; ?></table>

<?php elseif ($tab === 'reports'): ?>
<div class="stats-grid">
<div class="stat-card"><h3>Total Chats</h3><div class="value"><?php echo $totalChats; ?></div></div>
<div class="stat-card"><h3>Avg Response</h3><div class="value" style="font-size:16px"><?php echo $avgResponse; ?> min</div></div>
<div class="stat-card"><h3>Departments</h3><div class="value"><?php echo count($depts); ?></div></div>
<div class="stat-card"><h3>Operators</h3><div class="value"><?php echo count($ops); ?></div></div>
</div>
<table><tr><th>Metric</th><th>Value</th></tr>
<tr><td>Total Chats</td><td><?php echo $totalChats; ?></td></tr>
<tr><td>Average Response Time</td><td><?php echo $avgResponse; ?> minutes</td></tr>
<tr><td>Active Operators</td><td><?php echo count(array_filter($ops, fn($o)=>$o->status==='online')); ?></td></tr>
<tr><td>Departments</td><td><?php echo count($depts); ?></td></tr>
<tr><td>Canned Responses</td><td><?php echo count($canned); ?></td></tr>
</table>
<?php endif; ?>
</div>
</body></html>
