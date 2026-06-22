<div class="stats-grid">
<div class="stat-card" style="grid-column:1/-1;text-align:left">
<h3>System Information</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:12px">
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">Hostname</strong><br><?php echo htmlspecialchars($serverStats['hostname'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">OS</strong><br><?php echo htmlspecialchars($serverStats['os'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">Kernel</strong><br><?php echo htmlspecialchars($serverStats['kernel'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">CPU</strong><br><?php echo htmlspecialchars($serverStats['cpu_model'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">Uptime</strong><br><?php echo $serverStats['uptime'] ?? 'N/A'; ?><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="stat-card"><h3>CPU Load</h3><div class="value"><?php echo $serverStats['cpu_load']; ?>%<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div><div class="label">Load: <?php echo $serverStats['load_average']['1min'] ?? '?'; ?> / <?php echo $serverStats['load_average']['5min'] ?? '?'; ?> / <?php echo $serverStats['load_average']['15min'] ?? '?'; ?><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="stat-card"><h3>RAM Usage</h3><div class="value"><?php echo $serverStats['ram_usage']; ?>%<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div><div class="label"><?php echo $serverStats['ram_total']; ?> GB total<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="stat-card"><h3>Disk Usage</h3><div class="value"><?php echo $serverStats['disk_usage']; ?>%<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div><div class="label"><?php echo $serverStats['disk_total']; ?> total<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="stat-card"><h3>Active Accounts</h3><div class="value"><?php echo $serverStats['active_accounts']; ?><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div><div class="label">Hosting accounts<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">Service Status</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px">
<?php foreach ($serverStats['service_status'] as $label => $st): ?>
<div style="display:flex;justify-content:space-between;padding:8px 12px;background:rgba(255,255,255,.02);border-radius:6px">
<span style="font-size:14px"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
<span style="font-size:12px;padding:2px 10px;border-radius:4px;<?php echo $st === 'active' ? 'background:#1a3a2a;color:#4ade80' : 'background:#3a1a1a;color:#f87171'; ?>"><?php echo $st; ?></span>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<?php endforeach; ?>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">📹 Voice & Camera Services</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px">
<div style="display:flex;justify-content:space-between;padding:8px 12px;background:rgba(255,255,255,.02);border-radius:6px"><span>WebRTC Voice (SignalR)</span><span style="font-size:12px;padding:2px 10px;border-radius:4px;background:#1a3a2a;color:#4ade80">active</span><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div style="display:flex;justify-content:space-between;padding:8px 12px;background:rgba(255,255,255,.02);border-radius:6px"><span>WebRTC Camera</span><span style="font-size:12px;padding:2px 10px;border-radius:4px;background:#1a3a2a;color:#4ade80">available</span><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div style="display:flex;justify-content:space-between;padding:8px 12px;background:rgba(255,255,255,.02);border-radius:6px"><span>STUN Server</span><span style="font-size:12px;padding:2px 10px;border-radius:4px;background:#1a3a2a;color:#4ade80">Google STUN</span><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label><input name="main_domain" value="" placeholder="example.com" style="width:100%"><div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">🔧 Server Configuration</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
<form method="POST" action="/admin/serverconfig/hostname" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($serverStats['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Hostname</button>
</form>
</div>
<div>
<form method="POST" action="/admin/serverconfig/main-domain" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px"><label>Main Domain</label>
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary">Set Main Domain</button>
</form>
</div>
</div>
</div>
</div>
