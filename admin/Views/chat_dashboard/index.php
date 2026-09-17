<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0">💬 Chat Box Dashboard</h2>
<p style="color:#64748b;margin:4px 0 0">All chatboxes, their owners, rooms, and users.</p>
</div>
<div>
<a href="/chatbox/admin.php" class="btn primary"><i class="bi bi-phone"></i> Chat Admin</a>
<a href="/admin/licensing" class="btn secondary"><i class="bi bi-shield-check"></i> Licensing</a>
</div>
</div>

<?php
$totalRooms = 0; $totalUsers = 0; $totalOnline = 0;
foreach ($tenants as $t) {
    $totalRooms += (int)$t['room_count'];
    $totalUsers += (int)$t['user_count'];
    $totalOnline += (int)$t['online'];
}
?>
<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Chatboxes</h3><div class="value"><?php echo count($tenants); ?></div></div>
<div class="stat-card"><h3>Rooms</h3><div class="value"><?php echo $totalRooms; ?></div></div>
<div class="stat-card"><h3>Users</h3><div class="value" style="color:#4ade80"><?php echo $totalUsers; ?></div></div>
<div class="stat-card"><h3>Online</h3><div class="value" style="color:#38bdf8"><?php echo $totalOnline; ?></div></div>
</div>

<div style="margin-bottom:12px">
<a href="/admin/chat-dashboard/create" class="btn secondary"><i class="bi bi-plus"></i> Create Chatbox</a>
</div>

<?php if (empty($tenants)): ?>
<div class="card" style="text-align:center;padding:40px">
<p style="color:#64748b;font-size:14px">No chatboxes created yet. Add one from the hosting account's Chat settings.</p>
</div>
<?php else: ?>

<div class="card" style="padding:0;overflow:hidden">
<table>
<thead><tr><th>ID</th><th>Chatbox</th><th>Owner</th><th>Email</th><th>Rooms</th><th>Users</th><th>Online</th><th>Voice</th><th>Cam</th><th>Theme</th><th>Emotes</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($tenants as $t): ?>
<tr>
<td><?php echo (int)$t['id']; ?></td>
<td><strong><?php echo htmlspecialchars($t['name'] ?: ($t['widget_title'] ?: 'Chatbox #' . $t['id'])); ?></strong></td>
<td><?php echo htmlspecialchars($t['owner_username'] ?: '—'); ?></td>
<td><?php echo htmlspecialchars($t['owner_email'] ?: '—'); ?></td>
<td><?php echo (int)$t['room_count']; ?></td>
<td><?php echo (int)$t['user_count']; ?></td>
<td><span style="color:<?php echo $t['online'] > 0 ? '#4ade80' : '#64748b'; ?>"><?php echo (int)$t['online']; ?></span></td>
<td>
<form method="POST" style="display:inline" action="/chatbox/api.php?action=update_settings">
<input type="hidden" name="action" value="update_settings">
<input type="hidden" name="tenant_id" value="<?php echo (int)$t['id']; ?>">
<input type="hidden" name="voice" value="<?php echo ($t['voice_enabled'] ? '1' : '0'); ?>">
<button class="btn btn-sm <?php echo $t['voice_enabled'] ? 'btn-primary' : 'btn-outline-primary'; ?>" style="padding:4px 8px;font-size:11px">Voice<?php echo $t['voice_enabled'] ? ' ON' : ' OFF'; ?></button>
</form>
</td>
<td>
<form method="POST" style="display:inline" action="/chatbox/api.php?action=update_settings">
<input type="hidden" name="action" value="update_settings">
<input type="hidden" name="tenant_id" value="<?php echo (int)$t['id']; ?>">
<input type="hidden" name="video" value="<?php echo ($t['custom_css'] ? '1' : '0'); ?>"><!-- using custom_css flag as video enable, or add dedicated column later -->
<button class="btn btn-sm <?php echo !empty($t['custom_css']) ? 'btn-primary' : 'btn-outline-primary'; ?>" style="padding:4px 8px;font-size:11px">Cam<?php echo !empty($t['custom_css']) ? ' ON' : ' OFF'; ?></button>
</form>
</td>
<td>
<select class="btn btn-sm btn-outline-secondary" style="padding:4px 6px;font-size:11px;width:auto">
<option value="default" <?php echo !$t['custom_css'] ? 'selected' : ''; ?>>Default</option>
<option value="blue" <?php echo $t['custom_css'] === 'blue' ? 'selected' : ''; ?>>Blue</option>
<option value="black" <?php echo $t['custom_css'] === 'black' ? 'selected' : ''; ?>>Black</option>
<option value="discord" <?php echo $t['custom_css'] === 'discord' ? 'selected' : ''; ?>>Discord</option>
<option value="twitch" <?php echo $t['custom_css'] === 'twitch' ? 'selected' : ''; ?>>Twitch</option>
<option value="neon" <?php echo $t['custom_css'] === 'neon' ? 'selected' : ''; ?>>Neon</option>
<option value="gaming" <?php echo $t['custom_css'] === 'gaming' ? 'selected' : ''; ?>>Gaming</option>
<option value="hacker" <?php echo $t['custom_css'] === 'hacker' ? 'selected' : ''; ?>>Hacker</option>
<option value="purple" <?php echo $t['custom_css'] === 'purple' ? 'selected' : ''; ?>>Purple</option>
<option value="retro" <?php echo $t['custom_css'] === 'retro' ? 'selected' : ''; ?>>Retro</option>
</select>
</td>
<td>
<div style="display:flex;flex-wrap:wrap;gap:2px">
<?php
$emojiIcons = ['😀','😁','😂','😃','😄','😅','😆','😉','😊','😋','😌','😍','🥰','😘','😗','😙','😚','😛','😜','😝','😒','😓','😔','😕','😖','😞','😟','😠','😡','😢','😣'];
$shown = min(12, count($emojiIcons));
for ($i = 0; $i < $shown; $i++): ?>
<span title="<?php echo $emojiCodes[$i] ?? ''; ?>" style="font-size:14px;padding:2px 4px;cursor:pointer"> <?php echo $emojiIcons[$i]; ?> </span>
<?php endfor; ?>
</div>
</td>
<td><span class="status-badge status-<?php echo $t['is_active'] ? 'active' : 'terminated'; ?>"><?php echo $t['is_active'] ? 'Active' : 'Disabled'; ?></span>
<form method="POST" style="display:inline;margin-left:6px" action="/admin/chat-dashboard/toggle">
<input type="hidden" name="tenant_id" value="<?php echo (int)$t['id']; ?>">
<input type="hidden" name="action" value="<?php echo $t['is_active'] ? 'suspend' : 'activate'; ?>">
<button class="btn btn-sm <?php echo $t['is_active'] ? 'btn-danger' : 'btn-primary'; ?>" style="padding:2px 4px;font-size:10px">⏸️</button>
</form>
<form method="POST" style="display:inline;margin-left:6px" action="/admin/chat-dashboard/delete">
<input type="hidden" name="tenant_id" value="<?php echo (int)$t['id']; ?>">
<button class="btn btn-sm btn-danger" style="padding:2px 4px;font-size:10px" onclick="return confirm('Delete this chatbox and all its data?')">🗑️</button>
</form>
</td>
<td style="white-space:nowrap">
<a href="/admin/chat-dashboard/manage/<?php echo (int)$t['id']; ?>" class="btn btn-sm primary"><i class="bi bi-gear"></i> Manage</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<p style="margin-top:12px;color:#64748b;font-size:12px">Voice = enable/disable voice in chatbox. Cam = enable webcam streaming. Theme = prebuilt accent/color scheme. Emotes = quick-select smiley icons.</p>
<?php endif; ?>