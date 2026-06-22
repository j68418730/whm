<?php
define('BASE_PATH', realpath(__DIR__ . '/../../'));
// Load .env
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
            $_ENV[trim($key)] = trim($value);
        }
    }
}
require BASE_PATH . '/core/helpers.php';
spl_autoload_register(function ($class) {
    $relative = str_replace('\\', '/', $class) . '.php';
    $file = BASE_PATH . '/' . $relative;
    if (is_file($file)) { require $file; return; }
    $parts = explode('/', $relative);
    $parts[0] = strtolower($parts[0]);
    $lowerFile = BASE_PATH . '/' . implode('/', $parts);
    if (is_file($lowerFile)) { require $lowerFile; }
});
require BASE_PATH . '/core/Application.php';
require BASE_PATH . '/core/Config.php';
require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Request.php';
require BASE_PATH . '/core/Response.php';
require BASE_PATH . '/core/Router.php';
require BASE_PATH . '/core/Auth.php';
require BASE_PATH . '/core/Controller.php';
require BASE_PATH . '/core/View.php';
require BASE_PATH . '/core/Session.php';
require BASE_PATH . '/core/ServiceProvider.php';
require BASE_PATH . '/core/Plugin.php';
require BASE_PATH . '/core/PluginManager.php';
require BASE_PATH . '/core/License.php';
$config = require BASE_PATH . '/config/app.php';
$config['database'] = require BASE_PATH . '/config/database.php';
$config['plugins'] = require BASE_PATH . '/config/plugins.php';
new \Core\Application(BASE_PATH, $config);

session_start();

$app = \Core\Application::getInstance();
$auth = $app->get('auth');

if (!$auth->check() || !$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

$user = $auth->user();
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

$action = $_GET['action'] ?? 'list';

if ($action === 'delete_tenant' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM chatbox_tenants WHERE id = ?")->execute([(int)$_GET['id']]);
    header('Location: /admin/chat-dashboard.php');
    exit;
}

if ($action === 'create_tenant' && $_POST) {
    $name = trim($_POST['name'] ?? '');
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($name && $userId) {
        $u = $pdo->prepare("SELECT id FROM hosting_users WHERE id = ?");
        $u->execute([$userId]);
        if ($u->fetch()) {
            $pdo->prepare("INSERT IGNORE INTO chatbox_tenants (hosting_user_id, name, widget_title) VALUES (?, ?, ?)")
                ->execute([$userId, $name, $name . ' Chat']);
            $tid = $pdo->lastInsertId();
            $pdo->prepare("INSERT IGNORE INTO chatbox_rooms (tenant_id, name, type) VALUES (?, 'General', 'public'), (?, 'Support', 'public')")
                ->execute([$tid, $tid]);
        }
    }
    header('Location: /admin/chat-dashboard.php');
    exit;
}

$tenants = $pdo->query("SELECT ct.*, hu.username, hu.email FROM chatbox_tenants ct JOIN hosting_users hu ON ct.hosting_user_id = hu.id ORDER BY ct.created_at DESC")->fetchAll(PDO::FETCH_OBJ);
$users = $pdo->query("SELECT id, username, email FROM hosting_users ORDER BY username")->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Chat Dashboard - Planet Hosts</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#02050e;color:#fff;padding:24px}
h1{font-size:22px;margin-bottom:16px}h1 span{color:#008cff}
.card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:20px;margin-bottom:16px}
table{width:100%;border-collapse:collapse;font-size:13px}
td,th{padding:10px 8px;text-align:left;border-bottom:1px solid rgba(255,255,255,.04)}
select,input{padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;font-size:13px}
.btn{padding:8px 16px;border-radius:6px;border:none;font-weight:600;cursor:pointer;font-size:12px;text-decoration:none;display:inline-block}
.btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn-danger{background:rgba(248,113,113,.15);color:#f87171}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:14px;font-size:13px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80}
</style></head>
<body>
<h1>💬 Chat <span>Dashboard</span> <a href="/admin/dashboard" style="font-size:13px;color:#64748b;float:right">← Admin</a></h1>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="grid">
<div class="card">
<h3 style="color:#008cff;margin-bottom:12px">➕ Assign Chat to User</h3>
<form method="POST" action="/admin/chat-dashboard.php?action=create_tenant" style="display:flex;gap:8px;flex-wrap:wrap">
<select name="user_id" required style="flex:2;min-width:150px">
<option value="">Select user...</option>
<?php foreach ($users as $u): ?>
<option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->username); ?> (<?php echo htmlspecialchars($u->email); ?>)</option>
<?php endforeach; ?>
</select>
<input name="name" placeholder="Chat Name" required style="flex:1">
<button type="submit" class="btn btn-primary">Create</button>
</form>
</div>

<div class="card">
<h3 style="color:#008cff;margin-bottom:12px">📊 Stats</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px">
<div><span style="color:#64748b">Total Tenants:</span> <?php echo count($tenants); ?></div>
<div><span style="color:#64748b">Total Users:</span> <?php echo count($users); ?></div>
</div>
</div>
</div>

<div class="card">
<h3 style="color:#008cff;margin-bottom:12px">📋 All Chat Tenants</h3>
<table>
<tr><th>ID</th><th>User</th><th>Email</th><th>Chat Name</th><th>Widget</th><th>Voice</th><th>Actions</th></tr>
<?php if (empty($tenants)): ?>
<tr><td colspan="7" style="text-align:center;color:#64748b;padding:20px">No chat tenants yet</td></tr>
<?php else: foreach ($tenants as $t): ?>
<tr>
<td><?php echo $t->id; ?></td>
<td><strong><?php echo htmlspecialchars($t->username); ?></strong></td>
<td><?php echo htmlspecialchars($t->email); ?></td>
<td><?php echo htmlspecialchars($t->name); ?></td>
<td><a href="/chatbox/embed.php?tenant_id=<?php echo $t->id; ?>" target="_blank" style="color:#38bdf8;font-size:11px">🔗 Open</a></td>
<td><?php echo $t->voice_enabled ? '<span style="color:#4ade80">✓</span>' : '<span style="color:#64748b">✗</span>'; ?></td>
<td>
<a href="/chatbox/admin.php?tenant_id=<?php echo $t->id; ?>" target="_blank" class="btn btn-primary" style="padding:4px 10px;font-size:11px">Manage</a>
<a href="/admin/chat-dashboard.php?action=delete_tenant&id=<?php echo $t->id; ?>" class="btn btn-danger" style="padding:4px 10px;font-size:11px" onclick="return confirm('Delete this chat tenant?')">🗑</a>
</td>
</tr>
<?php endforeach; endif; ?>
</table>
</div>
</body></html>
