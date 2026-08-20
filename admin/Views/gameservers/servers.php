<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Game Server Instances</h3>
<a class="btn primary" onclick="document.getElementById('srvForm').classList.toggle('hidden')">Add Server</a>
</div>
<div id="srvForm" class="card hidden" style="max-width:600px;margin-bottom:20px">
<form method="POST" action="/admin/games/servers/store">
<?php echo $csrfField ?? ''; ?>
<input type="hidden" name="id" id="editId" value="0">
<div class="form-group"><label>Server Name</label><input name="name" id="editName" required placeholder="e.g. My Minecraft Server"></div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Owner Account</label><select name="user_id" id="editOwner" required>
<option value="">Select account...</option>
<?php foreach ($ownerMap as $uid => $ulabel): ?>
<option value="<?php echo $uid; ?>"><?php echo htmlspecialchars($ulabel); ?></option>
<?php endforeach; ?>
</select></div>
<div style="flex:1"><label>Game Type</label><select name="type_id" id="editGame" required>
<option value="">Select game...</option>
<?php foreach ($types as $t): ?>
<option value="<?php echo $t->id; ?>"><?php echo htmlspecialchars($t->name); ?></option>
<?php endforeach; ?>
</select></div>
</div>
<div class="form-group"><label>Hosted On Node</label><select name="node_id" id="editNode">
<option value="">This panel server (local) — I'll type paths below</option>
<?php foreach ($nodes as $nd): ?>
<option value="<?php echo (int)$nd->id; ?>"><?php echo htmlspecialchars($nd->name . ($nd->type === 'local' ? ' (local)' : '')); ?></option>
<?php endforeach; ?>
</select>
<small style="color:#64748b;font-size:11px">Selecting a remote node runs the game on that machine via the agent — Install/Config paths are then derived from the node, not the panel.</small></div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Port</label><input name="port" id="editPort" type="number" value="<?php echo (int)($settings['default_port'] ?? 27015); ?>"></div>
<div style="flex:1"><label>Status</label><select name="status" id="editStatus">
<?php foreach ($serverStatusMap as $sk => $sl): ?>
<option value="<?php echo $sk; ?>"><?php echo $sl; ?></option>
<?php endforeach; ?>
</select></div>
</div>
<div class="form-group"><label>Install Path</label><input name="install_path" id="editInstall" placeholder="e.g. /home/user/servers/minecraft"></div>
<div class="form-group"><label>Config Path</label><input name="config_path" id="editConfig" placeholder="e.g. /home/user/servers/minecraft/server.properties"></div>
<div class="form-group"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
<button type="submit" class="btn primary">Save</button>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="cancelEdit()">Cancel</a>
</form></div>

<table><tr><th>ID</th><th>Name</th><th>Owner</th><th>Game</th><th>Node</th><th>Port</th><th>Status</th><th>State</th><th></th></tr>
<?php if (!empty($servers)): foreach ($servers as $s): ?>
<tr>
<td><?php echo $s->id; ?></td>
<td><strong><?php echo htmlspecialchars($s->name); ?></strong></td>
<td><?php echo htmlspecialchars($ownerMap[$s->user_id] ?? 'ID#'.$s->user_id); ?></td>
<td><?php echo htmlspecialchars($s->game_type ?: ($typeMap[$s->type_id] ?? 'Unknown')); ?></td>
<td><?php echo htmlspecialchars($nodeMap[$s->node_id] ?? 'Local'); ?></td>
<td><?php echo $s->port ?: '-'; ?></td>
<td><span class="status-badge status-<?php echo $s->status === 'running' ? 'active' : ($s->status === 'suspended' ? 'terminated' : 'pending'); ?>"><?php echo htmlspecialchars($serverStatusMap[$s->status] ?? $s->status); ?></span></td>
<td><?php echo $s->is_active ? 'Active' : 'Inactive'; ?></td>
<td>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="editServer(<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8'); ?>)">Edit</a>
<a href="/admin/games/servers/status/<?php echo $s->id; ?>?status=<?php echo $s->status === 'running' ? 'stopped' : 'running'; ?>" class="btn btn-sm" style="background:<?php echo $s->status === 'running' ? 'rgba(250,204,21,.12)' : 'rgba(74,222,128,.12)'; ?>;color:<?php echo $s->status === 'running' ? '#facc15' : '#4ade80'; ?>;text-decoration:none;border-radius:4px"><?php echo $s->status === 'running' ? 'Stop' : 'Start'; ?></a>
<a href="/admin/games/servers/toggle/<?php echo $s->id; ?>" class="btn btn-sm" style="background:<?php echo $s->is_active ? 'rgba(250,204,21,.12)' : 'rgba(74,222,128,.12)'; ?>;color:<?php echo $s->is_active ? '#facc15' : '#4ade80'; ?>;text-decoration:none;border-radius:4px"><?php echo $s->is_active ? 'Suspend' : 'Unsuspend'; ?></a>
<a href="/admin/games/servers/delete/<?php echo $s->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete this game server?')">Delete</a>
</td></tr>
<?php endforeach; else: ?><tr><td colspan="9" style="text-align:center;padding:20px;color:#64748b">No game servers yet.</td></tr>
<?php endif; ?></table>

<script>
function editServer(s) {
    document.getElementById('editId').value = s.id;
    document.getElementById('editName').value = s.name;
    document.getElementById('editOwner').value = s.user_id;
    document.getElementById('editGame').value = s.type_id;
    document.getElementById('editNode').value = s.node_id || '';
    document.getElementById('editPort').value = s.port;
    document.getElementById('editStatus').value = s.status || 'stopped';
    document.getElementById('editInstall').value = s.install_path || '';
    document.getElementById('editConfig').value = s.config_path || '';
    var cb = document.querySelector('#srvForm input[name="is_active"]');
    if (cb) cb.checked = s.is_active == 1;
    document.getElementById('srvForm').classList.remove('hidden');
}
function cancelEdit() {
    document.getElementById('editId').value = 0;
    document.getElementById('srvForm').classList.add('hidden');
}
</script>