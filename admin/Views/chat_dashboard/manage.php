<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.cm-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:20px;margin-bottom:16px}
.cm-card h2{font-size:15px;color:#008cff;margin:0 0 12px;display:flex;align-items:center;gap:6px}
.cm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
.cm-table{width:100%;border-collapse:collapse;font-size:13px}
.cm-table th{padding:8px;text-align:left;color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid rgba(255,255,255,.08)}
.cm-table td{padding:8px;border-bottom:1px solid rgba(255,255,255,.04)}
.cm-btn{padding:6px 12px;border-radius:6px;border:none;font-weight:600;cursor:pointer;font-size:12px;font-family:Inter;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.cm-btn.danger{background:rgba(248,113,113,.15);color:#f87171}
.cm-btn.sm{padding:4px 10px;font-size:11px}
.cm-input{width:100%;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;font-size:13px;margin-bottom:8px;box-sizing:border-box;font-family:Inter}
.cm-embed{background:rgba(0,0,0,.3);padding:12px;border-radius:6px;font-family:monospace;font-size:12px;color:#4ade80;word-break:break-all;margin-bottom:8px}
.cm-status{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:100px;font-size:12px;font-weight:600}
.cm-status.on{background:rgba(74,222,128,.12);color:#4ade80}
.cm-status.off{background:rgba(248,113,113,.12);color:#f87171}
.cm-back{display:inline-flex;align-items:center;gap:6px;color:#38bdf8;text-decoration:none;font-size:13px;margin-bottom:16px}
.cm-back:hover{opacity:.8}
</style>

<a href="/admin/chat-dashboard" class="cm-back"><i class="bi bi-arrow-left"></i> Back to Chat Dashboard</a>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px">
<div>
<h2 style="margin:0">💬 <?php echo htmlspecialchars($tenant['widget_title'] ?: $tenant['name']); ?></h2>
<p style="color:#64748b;margin:4px 0 0">Owner: <?php echo htmlspecialchars($tenant['owner_username'] ?: '—'); ?> (<?php echo htmlspecialchars($tenant['owner_email'] ?: ''); ?>)</p>
</div>
<div style="display:flex;align-items:center;gap:8px">
<span class="cm-status <?php echo $tenant['is_active'] ? 'on' : 'off'; ?>"><?php echo $tenant['is_active'] ? 'Live' : 'Disabled'; ?></span>
<a href="/chatbox/embed.php?tenant_id=<?php echo (int)$tenant['id']; ?>" target="_blank" class="cm-btn">Preview</a>
</div>
</div>

<div class="cm-grid">

<div class="cm-card">
<h2>⚙️ Settings</h2>
<form method="POST">
<input type="hidden" name="action" value="update_settings">
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600">Widget Title</label>
<input class="cm-input" name="title" value="<?php echo htmlspecialchars($tenant['widget_title'] ?? ''); ?>">
<div style="display:flex;gap:8px">
<div style="flex:1"><label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600">Accent</label><input class="cm-input" name="color" type="color" value="<?php echo $tenant['widget_color'] ?? '#008cff'; ?>"></div>
<div style="flex:1"><label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600">Background</label><input class="cm-input" name="bg" type="color" value="<?php echo $tenant['widget_bg'] ?? '#0a0e1a'; ?>"></div>
<div style="flex:1"><label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600">Text</label><input class="cm-input" name="text_color" type="color" value="<?php echo $tenant['widget_text_color'] ?? '#ffffff'; ?>"></div>
</div>
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600">Font Family</label>
<input class="cm-input" name="font" value="<?php echo htmlspecialchars($tenant['font_family'] ?? 'Inter, sans-serif'); ?>">
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600">Player Embed Code (HTML/iframe)</label>
<textarea class="cm-input" name="player_html" rows="3" placeholder="&lt;iframe src=&quot;https://player.example.com/stream&quot; width=&quot;100%&quot; height=&quot;150&quot;&gt;&lt;/iframe&gt;"><?php echo htmlspecialchars($tenant['player_html'] ?? ''); ?></textarea>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;margin:6px 0"><input type="checkbox" name="guest" value="1" <?php echo $tenant['guest_enabled'] ? 'checked' : ''; ?>> Allow Guests</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;margin:6px 0"><input type="checkbox" name="reg" value="1" <?php echo $tenant['registration_enabled'] ? 'checked' : ''; ?>> Allow Registration</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;margin:6px 0"><input type="checkbox" name="voice" value="1" <?php echo $tenant['voice_enabled'] ? 'checked' : ''; ?>> Enable Voice</label>
<button class="cm-btn" style="margin-top:8px">Save Settings</button>
</form>
</div>

<div class="cm-card">
<h2>🪪 Embed Code</h2>
<div class="cm-embed">&lt;script src="http://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?php echo (int)$tenant['id']; ?>"&gt;&lt;/script&gt;</div>
<button class="cm-btn sm" onclick="navigator.clipboard.writeText('&lt;script src=&quot;http://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?php echo (int)$tenant['id']; ?>&quot;&gt;&lt;/script&gt;')">📋 Copy Embed</button>
<div style="margin-top:12px;font-size:12px;color:#64748b">Or use iframe:</div>
<div class="cm-embed">&lt;iframe src="http://planet-hosts.com/chatbox/embed.php?tenant_id=<?php echo (int)$tenant['id']; ?>" width="360" height="500"&gt;&lt;/iframe&gt;</div>
<button class="cm-btn sm" onclick="navigator.clipboard.writeText('&lt;iframe src=&quot;http://planet-hosts.com/chatbox/embed.php?tenant_id=<?php echo (int)$tenant['id']; ?>&quot; width=&quot;360&quot; height=&quot;500&quot;&gt;&lt;/iframe&gt;')">📋 Copy Iframe</button>
</div>

<div class="cm-card">
<h2>🚪 Rooms (<?php echo count($rooms); ?>)</h2>
<form method="POST" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
<input type="hidden" name="action" value="add_room">
<input class="cm-input" name="name" placeholder="Room name" required style="flex:1;min-width:120px;margin:0">
<select name="type" style="width:auto;padding:6px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;margin:0"><option value="public">Public</option><option value="private">Private</option><option value="password">Password</option></select>
<input class="cm-input" name="password" placeholder="Password" style="width:auto;margin:0">
<button class="cm-btn sm">+ Add</button>
</form>
<?php if (empty($rooms)): ?><p style="color:#64748b;font-size:12px">No rooms yet.</p>
<?php else: ?>
<table class="cm-table">
<thead><tr><th>Room</th><th>Type</th><th></th></tr></thead>
<tbody>
<?php foreach ($rooms as $r): ?>
<tr>
<td><?php echo htmlspecialchars($r['name']); ?></td>
<td><?php echo htmlspecialchars($r['type']); ?></td>
<td><form method="POST" style="display:inline"><input type="hidden" name="action" value="delete_room"><input type="hidden" name="room_id" value="<?php echo (int)$r['id']; ?>"><button class="cm-btn sm danger">✕</button></form></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>

<div class="cm-card">
<h2>👥 Users (<?php echo count($users); ?>)</h2>
<form method="POST" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
<input type="hidden" name="action" value="add_user">
<input class="cm-input" name="username" placeholder="Username" required style="flex:1;min-width:100px;margin:0">
<input class="cm-input" name="password" type="password" placeholder="Password" required style="width:auto;margin:0">
<input class="cm-input" name="display_name" placeholder="Display name" style="width:auto;margin:0">
<select name="role" style="width:auto;padding:6px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;margin:0"><option value="member">Member</option><option value="mod">Mod</option><option value="admin">Admin</option></select>
<button class="cm-btn sm">+ Add</button>
</form>
<?php if (empty($users)): ?><p style="color:#64748b;font-size:12px">No users yet.</p>
<?php else: ?>
<table class="cm-table">
<thead><tr><th>User</th><th>Role</th><th>Status</th><th></th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr>
<td><?php echo htmlspecialchars($u['display_name'] ?: $u['username']); ?></td>
<td><?php echo htmlspecialchars($u['role']); ?></td>
<td><?php if (!empty($u['is_banned'])): ?><span style="color:#f87171">Banned</span><?php elseif (!empty($u['voice_denied'])): ?><span style="color:#facc15">No Voice</span><?php else: ?><span style="color:#4ade80">OK</span><?php endif; ?></td>
<td style="white-space:nowrap">
<form method="POST" style="display:inline"><input type="hidden" name="action" value="ban_user"><input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>"><button class="cm-btn sm danger" onclick="return confirm('Ban <?php echo htmlspecialchars(addslashes($u['username'])); ?>?')">Ban</button></form>
<form method="POST" style="display:inline"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>"><button class="cm-btn sm danger" onclick="return confirm('Delete user?')">✕</button></form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>

<div class="cm-card">
<h2>🔒 Guest Password Protection</h2>
<form method="POST" action="/chatbox/api.php?action=guest_protect" style="display:flex;gap:8px;flex-wrap:wrap">
<input type="hidden" name="action" value="guest_protect">
<label style="display:flex;align-items:center;gap:4px;font-size:13px"><input type="checkbox" name="enable" value="1" <?php echo !empty($tenant['guest_password_enabled']) ? 'checked' : ''; ?>> Require password for guests</label>
<input class="cm-input" name="password" placeholder="Guest password" style="flex:1;min-width:120px">
<button class="cm-btn sm" onclick="var f=this.form;fetch(f.action,{method:'POST',body:new FormData(f)}).then(r=>r.json()).then(d=>alert(d.success?'Saved':'Error'));return false">Save</button>
</form>
</div>

<div class="cm-card">
<h2>📊 Chat Statistics</h2>
<div id="chatStats" style="font-size:13px;color:#94a3b8"><p>Loading...</p></div>
</div>

<div class="cm-card">
<h2>📋 Moderation Log</h2>
<div id="modLog" style="font-size:12px;max-height:200px;overflow-y:auto;color:#94a3b8"><p>Loading...</p></div>
</div>

</div>

<script>
fetch('/chatbox/api.php?action=stats', {credentials:'include'}).then(function(r){return r.json()}).then(function(d){
    if(d.total_messages !== undefined) {
        document.getElementById('chatStats').innerHTML =
            '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">' +
            '<div style="background:rgba(0,0,0,.3);padding:12px;border-radius:8px;text-align:center"><div style="font-size:26px;font-weight:700;color:#38bdf8">' + d.total_messages + '</div><div style="font-size:10px;color:#64748b">Messages</div></div>' +
            '<div style="background:rgba(0,0,0,.3);padding:12px;border-radius:8px;text-align:center"><div style="font-size:26px;font-weight:700;color:#4ade80">' + d.total_users + '</div><div style="font-size:10px;color:#64748b">Users</div></div>' +
            '<div style="background:rgba(0,0,0,.3);padding:12px;border-radius:8px;text-align:center"><div style="font-size:26px;font-weight:700;color:#facc15">' + d.online_now + '</div><div style="font-size:10px;color:#64748b">Online</div></div></div>';
    }
});
fetch('/chatbox/api.php?action=mod_log', {credentials:'include'}).then(function(r){return r.json()}).then(function(d){
    if(d && d.length) {
        document.getElementById('modLog').innerHTML = '<table style="width:100%;font-size:11px">' + d.map(function(m){
            return '<tr><td>' + m.action + '</td><td>' + (m.target_username||'') + '</td><td style="color:#64748b">' + m.created_at + '</td></tr>';
        }).join('') + '</table>';
    } else { document.getElementById('modLog').innerHTML = '<p>No moderation actions</p>'; }
});
</script>