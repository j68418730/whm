<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['generated_key'])): $genKey = $_SESSION['generated_key']; unset($_SESSION['generated_key']); ?>
<div class="alert alert-success" style="border:2px solid #22c55e;background:rgba(34,197,94,.08)">
  <strong style="font-size:13px">🔑 Key Generated — Copy it now!</strong>
  <div style="font-size:11px;color:#64748b;margin:4px 0 8px">This key will not be shown again.</div>
  <div style="display:flex;gap:6px;align-items:center">
    <code id="genKeyDisplay" style="flex:1;padding:8px 12px;background:rgba(0,0,0,.4);border-radius:6px;font-size:13px;word-break:break-all;color:#22c55e"><?php echo htmlspecialchars($genKey); ?></code>
    <button onclick="copyKey()" class="btn primary" style="white-space:nowrap;flex-shrink:0">📋 Copy</button>
  </div>
</div>
<script>
function copyKey() {
  var el = document.getElementById('genKeyDisplay');
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(el.textContent).then(function() {
      var btn = event.target; btn.textContent = '✅ Copied!'; setTimeout(function() { btn.textContent = '📋 Copy'; }, 2000);
    });
  } else {
    var range = document.createRange(); range.selectNode(el); window.getSelection().removeAllRanges(); window.getSelection().addRange(range);
    document.execCommand('copy'); window.getSelection().removeAllRanges();
    alert('Key copied!');
  }
}
</script>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<?php $currentTab = 'api'; require __DIR__ . '/_tabs.php'; ?>

<style>
.api-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:24px}
.api-cards .card{padding:20px;margin-bottom:0}
.api-cards h3{color:var(--accent);margin:0 0 4px;font-size:15px}
.api-cards .desc{font-size:12px;color:var(--text-muted);margin-bottom:16px}
.api-cards .form-group{margin-bottom:10px}
.api-cards .form-group label{font-size:12px;display:block;margin-bottom:3px}
.api-cards .form-group select,.api-cards .form-group input{padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;width:100%}
.badge-role{display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
.badge-super{background:#fef3c7;color:#92400e}
.badge-admin{background:#dbeafe;color:#1e40af}
.badge-support{background:#e0f2fe;color:#075985}
.badge-sales{background:#dcfce7;color:#166534}
.badge-billing{background:#f3e8ff;color:#6b21a8}
.badge-technical{background:#e0f2fe;color:#0c4a6e}
.badge-server{background:#fce7f3;color:#9d174d}
.badge-streaming{background:#ede9fe;color:#5b21b6}
.badge-game{background:#fef3c7;color:#92400e}
.badge-domain{background:#e0f2fe;color:#0c4a6e}
.badge-cpanel{background:#dbeafe;color:#1e40af}
.badge-abuse{background:#fee2e2;color:#991b1b}
.badge-dmca{background:#fce7f3;color:#9d174d}
.badge-linux{background:#e0f2fe;color:#0c4a6e}
.badge-windows{background:#dbeafe;color:#1e40af}
.badge-user{background:#f1f5f9;color:#475569}
</style>

<div class="api-cards">

<!-- ── API Settings Card ── -->
<div class="card">
<h3>API Settings</h3>
<p class="desc">Globally enable/disable the REST API and configure defaults.</p>
<form method="POST" action="/admin/settings/api/save">
<input type="hidden" name="mode" value="settings">
<div class="form-group"><label><input name="api_enabled" type="checkbox" value="1" <?php echo $api_enabled==='1'?'checked':''; ?>> Enable REST API</label></div>
<div class="form-group"><label>Default Rate Limit (req/min)</label><input name="api_rate_limit_default" type="number" value="<?php echo htmlspecialchars($api_rate_limit_default); ?>"></div>
<div class="form-group"><label><input name="api_debug_mode" type="checkbox" value="1" <?php echo $api_debug_mode==='1'?'checked':''; ?>> Debug Mode</label></div>
<div class="form-group">
<label>OpenAI API Key</label>
<input name="openai_api_key" type="password" value="<?php echo htmlspecialchars($openai_api_key ?? ''); ?>" placeholder="sk-...">
<div style="font-size:10px;color:#64748b;margin-top:2px">Required for AI Website Generator</div>
</div>
<div class="form-group">
<label>Key Name (optional)</label>
<select name="new_key_name">
<option value="">— Don't generate —</option>
<option>Full Access Key</option>
<option>Support Integration</option>
<option>Development</option>
<option>Custom Integration</option>
</select>
</div>
<button type="submit" class="btn primary" style="width:100%">💾 Save Settings & Generate</button>
</form>
</div>

<!-- ── Desktop API Card ── -->
<div class="card" style="border:1px solid rgba(0,191,255,.25)">
<h3>🖥 Desktop API</h3>
<p class="desc">Generate API keys tied to a specific user and their role.</p>
<form method="POST" action="/admin/settings/api/save">
<input type="hidden" name="mode" value="desktop">
<div class="form-group">
<label>👤 Select User</label>
<select id="desktopUser" required onchange="setDesktopUser()">
<option value="">— Choose user —</option>
<optgroup label="👑 Admins">
<?php if (!empty($admins)): foreach ($admins as $a): ?>
<option value="<?php echo $a->id; ?>" data-type="admin" data-role="<?php echo htmlspecialchars($roleMap[$a->id]->role ?? 'admin'); ?>"><?php echo htmlspecialchars($a->username ?: $a->name); ?> (<?php echo htmlspecialchars($a->email); ?>)</option>
<?php endforeach; endif; ?>
</optgroup>
<optgroup label="👥 Hosting Users">
<?php if (!empty($hostingUsers)): foreach ($hostingUsers as $u): ?>
<option value="<?php echo $u->id; ?>" data-type="hosting" data-role="<?php echo htmlspecialchars($roleMap[$u->id]->role ?? 'user'); ?>"><?php echo htmlspecialchars($u->username); ?> @ <?php echo htmlspecialchars($u->domain ?: '-'); ?></option>
<?php endforeach; endif; ?>
</optgroup>
</select>
<input type="hidden" name="user_id" id="desktopUserId" value="">
<input type="hidden" name="user_type" id="desktopUserType" value="admin">
</div>
<div class="form-group">
<label>🎭 Role</label>
<div id="desktopRoleDisplay" style="padding:7px 10px;border-radius:6px;background:rgba(0,0,0,.3);font-size:12px;color:#64748b">Select a user to see role</div>
</div>
<div class="form-group">
<label>🔑 Key Name</label>
<input name="desktop_key_name" value="Desktop API Key">
</div>
<div class="form-group">
<label>🔒 Permissions</label>
<select name="desktop_permissions">
<option value="admin">Admin (full access)</option>
<option value="read,write">Read + Write</option>
<option value="read">Read Only</option>
</select>
</div>
<div class="form-group">
<label>⏱ Rate Limit (req/min, 0 = unlimited)</label>
<input name="desktop_rate_limit" type="number" value="120">
</div>
<button type="submit" class="btn primary" style="width:100%">🔑 Generate Desktop Key</button>
</form>
</div>

<!-- ── Root API Card ── -->
<div class="card" style="border:1px solid rgba(255,200,0,.4);background:linear-gradient(135deg,rgba(255,200,0,.04),rgba(255,150,0,.02))">
<h3 style="color:#f59e0b">⚡ Root API Key</h3>
<p class="desc">Unrestricted key with <strong>all permissions</strong>. Only generate when needed — treat like a root password.</p>
<form method="POST" action="/admin/settings/api/save" onsubmit="return confirm('Generate a Root API key with FULL ACCESS? This key can do everything.')">
<input type="hidden" name="mode" value="root">
<div style="background:rgba(255,200,0,.08);border-radius:8px;padding:10px;margin-bottom:14px;font-size:11px;color:#fbbf24">
<strong>⚠️ Root keys bypass all permission checks.</strong> Keep this key secret and rotate regularly.
</div>
<div style="font-size:12px;color:var(--text-secondary);margin-bottom:14px">
Current root keys: <strong><?php echo count(array_filter($keys ?? [], function($k) { return ($k->user_type ?? '') === 'root'; })); ?></strong>
</div>
<button type="submit" class="btn" style="width:100%;background:linear-gradient(135deg,#f59e0b,#d97706);color:#1e293b;font-weight:700;border:none">⚡ Generate Root Key</button>
</form>
</div>
</div>

<!-- ── Assigned API Keys ── -->
<h3 style="color:var(--accent);margin:0 0 6px">🔑 Assigned API Keys</h3>
<p style="font-size:12px;color:var(--text-muted);margin:0 0 12px">Each key is linked to a user and inherits their role permissions.</p>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px">
<?php if (!empty($keys)): foreach ($keys as $k):
$isRoot = ($k->user_type ?? '') === 'root';
$hasUser = ($k->user_id ?? null) !== null && $k->user_id !== '' && !$isRoot;
$assignedUser = null;
$assignedName = '';
$assignedRole = '';
$assignedType = '';
if ($hasUser) {
    $uType = $k->user_type ?? 'admin';
    if ($uType === 'admin') {
        foreach ($admins as $a) { if ($a->id == $k->user_id) { $assignedUser = $a; $assignedName = $a->username ?: $a->name; $assignedType = 'admin'; break; } }
    } elseif ($uType === 'hosting') {
        foreach ($hostingUsers as $u) { if ($u->id == $k->user_id) { $assignedUser = $u; $assignedName = $u->username; $assignedType = 'hosting'; break; } }
    }
    if ($assignedUser) {
        $assignedRole = $roleMap[$assignedUser->id]->role ?? ($assignedType === 'admin' ? 'admin' : 'user');
    }
}
?>
<div class="card" style="margin-bottom:0;padding:14px;<?php echo $isRoot ? 'border-color:rgba(255,200,0,.3)' : ($hasUser ? 'border-color:rgba(0,191,255,.15)' : ''); ?>">
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
<strong style="font-size:13px"><?php echo htmlspecialchars($k->name); ?></strong>
<div style="display:flex;gap:4px;flex-wrap:wrap">
<?php if ($isRoot): ?>
<span style="font-size:10px;padding:2px 8px;border-radius:4px;background:rgba(255,200,0,.15);color:#f59e0b;font-weight:700">ROOT</span>
<?php endif; ?>
<span class="status-badge status-<?php echo $k->is_active ? 'active' : 'terminated'; ?>" style="font-size:10px"><?php echo $k->is_active ? 'Active' : 'Inactive'; ?></span>
<span style="font-size:10px;padding:2px 8px;border-radius:4px;background:rgba(0,191,255,.1);color:#00bfff"><?php echo htmlspecialchars($k->permissions ?? 'read'); ?></span>
</div>
</div>
<?php if ($isRoot): ?>
<div style="font-size:12px;color:#f59e0b">⚠️ Super Admin — full access to all endpoints</div>
<?php elseif ($hasUser && $assignedUser): ?>
<div style="display:flex;align-items:center;gap:6px;margin:2px 0">
<span style="font-size:12px">👤 <?php echo htmlspecialchars($assignedName); ?></span>
<span style="font-size:10px;padding:1px 6px;border-radius:3px;background:rgba(255,255,255,.06);color:#94a3b8"><?php echo htmlspecialchars($assignedType); ?></span>
<span class="badge-role badge-<?php echo $assignedRole; ?>" style="font-size:10px;padding:1px 6px"><?php echo htmlspecialchars(ucfirst($assignedRole)); ?></span>
</div>
<?php else: ?>
<div style="font-size:12px;color:#64748b">Unassigned (standalone key)</div>
<?php endif; ?>
<div style="font-size:11px;font-family:monospace;color:#64748b;margin-top:2px"><?php echo substr($k->key_hash, 0, 20); ?>…</div>
<div style="font-size:11px;color:#64748b">Rate: <?php echo $k->rate_limit ?? 60; ?>/min · <?php echo $k->created_at ?? '-'; ?></div>
<div style="margin-top:8px;display:flex;gap:4px">
<?php if ($isRoot): ?>
<a href="/admin/api/permissions" class="btn btn-sm secondary">🔒 Permissions</a>
<?php else: ?>
<a href="/admin/api/permissions" class="btn btn-sm secondary">🔒 Permissions</a>
<a href="/admin/roles" class="btn btn-sm secondary">🎭 Roles</a>
<?php endif; ?>
<a href="/admin/api/delete/<?php echo $k->id; ?>" class="btn btn-sm danger" onclick="return confirm('Revoke key?')">🗑 Revoke</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:30px;grid-column:1/-1;color:#64748b">No API keys yet. Generate one above.</div>
<?php endif; ?>
</div>

<div style="margin-top:24px;display:flex;gap:8px;flex-wrap:wrap">
<a href="/admin/api" class="btn secondary">🔑 Key Manager</a>
<a href="/admin/api/permissions" class="btn secondary">🔒 Permissions</a>
<a href="/admin/api/webhooks" class="btn secondary">🔗 Webhooks</a>
<a href="/admin/api/docs" class="btn secondary">📘 API Docs</a>
<a href="/admin/api/logs" class="btn secondary">📋 API Logs</a>
<a href="/admin/api/rate-limits" class="btn secondary">⏱ Rate Limits</a>
</div>

<script>
function setDesktopUser() {
    var sel = document.getElementById('desktopUser');
    var display = document.getElementById('desktopRoleDisplay');
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('desktopUserId').value = opt.value;
        document.getElementById('desktopUserType').value = opt.getAttribute('data-type') || 'admin';
        var role = opt.getAttribute('data-role') || 'user';
        var type = opt.getAttribute('data-type') || 'admin';
        display.innerHTML = '<strong>' + role.charAt(0).toUpperCase() + role.slice(1) + '</strong> (' + type + ')';
    } else {
        document.getElementById('desktopUserId').value = '';
        document.getElementById('desktopUserType').value = 'admin';
        display.innerHTML = 'Select a user to see role';
    }
}
</script>