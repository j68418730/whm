<?php
$scriptName = $_SERVER['SCRIPT_FILENAME'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_ends_with($scriptName, '/public/user/chat.php') && !str_contains($requestUri, '/user/chat.php')) {
    header('Location: /user/chat');
    exit;
}
if (!isset($hosting) || !$hosting) { echo 'No account'; exit; }

$tenant = $pdo->prepare("SELECT * FROM chatbox_tenants WHERE hosting_user_id = ?");
$tenant->execute([$hosting->id]);
$tenant = $tenant->fetch(PDO::FETCH_OBJ);

if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $pdo->prepare("INSERT IGNORE INTO chatbox_tenants (hosting_user_id, name, widget_title) VALUES (?, ?, ?)")
            ->execute([$hosting->id, $hosting->username . "'s Chat", $hosting->username . ' Chat']);
        $tid = $pdo->lastInsertId();
        $pdo->prepare("INSERT IGNORE INTO chatbox_rooms (tenant_id, name, type) VALUES (?, 'General', 'public'), (?, 'Support', 'public')")
            ->execute([$tid, $tid]);
        header('Location: /user/chat'); exit;
    }
    if ($_POST['action'] === 'save_settings' && $tenant) {
        $pdo->prepare("UPDATE chatbox_tenants SET widget_title=?, widget_color=?, widget_bg=?, widget_text_color=?, widget_border_color=?, widget_glow_color=?, widget_avatar_shape=?, font_family=?, theme=?, custom_css=?, guest_enabled=?, registration_enabled=?, voice_enabled=?, player_html=? WHERE id=?")
            ->execute([$_POST['title'], $_POST['color'], $_POST['bg'], $_POST['text_color'], $_POST['border_color'], $_POST['glow_color'], $_POST['avatar_shape'], $_POST['font'], $_POST['theme'], $_POST['custom_css'], (int)$_POST['guest'], (int)$_POST['reg'], (int)$_POST['voice'], $_POST['player_html'], $tenant->id]);
        header('Location: /user/chat'); exit;
    }
    if ($_POST['action'] === 'add_room' && $tenant) {
        $pdo->prepare("INSERT INTO chatbox_rooms (tenant_id, name, type, password) VALUES (?, ?, ?, ?)")
            ->execute([$tenant->id, $_POST['name'], $_POST['type'], $_POST['type'] === 'password' ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null]);
        header('Location: /user/chat'); exit;
    }
    if ($_POST['action'] === 'delete_room' && $tenant) {
        $pdo->prepare("DELETE FROM chatbox_rooms WHERE id=? AND tenant_id=?")->execute([(int)$_POST['room_id'], $tenant->id]);
        header('Location: /user/chat'); exit;
    }
}

$roomsList = [];
if ($tenant) {
    $rs = $pdo->prepare("SELECT * FROM chatbox_rooms WHERE tenant_id = ?");
    $rs->execute([$tenant->id]);
    $roomsList = $rs->fetchAll(PDO::FETCH_OBJ);
}
$themes = ['default'=>'Default Dark','blue'=>'Blue','black'=>'Black','white'=>'White','gray'=>'Gray','neon'=>'Neon','gaming'=>'Gaming','hacker'=>'Hacker','matrix'=>'Matrix','discord'=>'Discord','twitch'=>'Twitch','retro'=>'Retro','purple'=>'Purple','red'=>'Red','gold'=>'Gold'];
?>
<div class="card">
<h3>Chat Room</h3>
<?php if (!$tenant): ?>
<p style="color:var(--text_muted);margin-bottom:12px">You don't have a chat room yet. Create one to let visitors chat in real time.</p>
<form method="POST"><input type="hidden" name="action" value="create"><button class="btn btn-primary">Create Chat Room</button></form>
<?php else: ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
<div style="background:rgba(0,0,0,.2);border-radius:8px;padding:12px">
<div style="font-size:11px;color:var(--text_muted);margin-bottom:4px">Embed Code</div>
<code style="font-size:11px;word-break:break-all">&lt;script src="http://45.61.59.55/chatbox/widget.js.php?tenant_id=<?php echo $tenant->id; ?>"&gt;&lt;/script&gt;</code>
</div>
<div style="background:rgba(0,0,0,.2);border-radius:8px;padding:12px">
<div style="font-size:11px;color:var(--text_muted);margin-bottom:4px">Iframe</div>
<code style="font-size:11px;word-break:break-all">&lt;iframe src="http://45.61.59.55/chatbox/embed.php?tenant_id=<?php echo $tenant->id; ?>" width="360" height="500"&gt;&lt;/iframe&gt;</code>
</div>
</div>

<form method="POST">
<input type="hidden" name="action" value="save_settings">
<div class="row g-2">
<div class="col-md-4"><div class="form-group"><label>Widget Title</label><input name="title" class="form-control" value="<?php echo htmlspecialchars($tenant->widget_title ?? ''); ?>"></div></div>
<div class="col-md-4"><div class="form-group"><label>Font</label><input name="font" class="form-control" value="<?php echo htmlspecialchars($tenant->font_family ?? 'Inter, sans-serif'); ?>"></div></div>
<div class="col-md-4"><div class="form-group"><label>Theme</label><select name="theme" class="form-select"><option value="custom">Custom</option><?php foreach ($themes as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo ($tenant->theme ?? 'default') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option><?php endforeach; ?></select></div></div>
<div class="col-md-2"><div class="form-group"><label>Accent</label><input name="color" type="color" class="form-control" value="<?php echo $tenant->widget_color ?? '#008cff'; ?>"></div></div>
<div class="col-md-2"><div class="form-group"><label>Background</label><input name="bg" type="color" class="form-control" value="<?php echo $tenant->widget_bg ?? '#0a0e1a'; ?>"></div></div>
<div class="col-md-2"><div class="form-group"><label>Text</label><input name="text_color" type="color" class="form-control" value="<?php echo $tenant->widget_text_color ?? '#ffffff'; ?>"></div></div>
<div class="col-md-2"><div class="form-group"><label>Border</label><input name="border_color" type="color" class="form-control" value="<?php echo $tenant->widget_border_color ?? 'rgba(255,255,255,.1)'; ?>"></div></div>
<div class="col-md-2"><div class="form-group"><label>Glow</label><input name="glow_color" type="color" class="form-control" value="<?php echo $tenant->widget_glow_color ?? '#008cff'; ?>"></div></div>
<div class="col-md-2"><div class="form-group"><label>Avatar</label><select name="avatar_shape" class="form-select"><option value="circle" <?php echo ($tenant->widget_avatar_shape ?? 'circle') === 'circle' ? 'selected' : ''; ?>>Circle</option><option value="square" <?php echo ($tenant->widget_avatar_shape ?? '') === 'square' ? 'selected' : ''; ?>>Square</option><option value="rounded" <?php echo ($tenant->widget_avatar_shape ?? '') === 'rounded' ? 'selected' : ''; ?>>Rounded</option></select></div></div>
<div class="col-12"><div class="form-group"><label>Custom CSS</label><textarea name="custom_css" class="form-control" rows="3" placeholder="/* Custom CSS */"><?php echo htmlspecialchars($tenant->custom_css ?? ''); ?></textarea></div></div>
<div class="col-12"><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="guest" value="1" <?php echo $tenant->guest_enabled ? 'checked' : ''; ?>><label class="form-check-label">Allow Guests</label></div>
<div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="reg" value="1" <?php echo $tenant->registration_enabled ? 'checked' : ''; ?>><label class="form-check-label">Allow Registration</label></div>
<div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="voice" value="1" <?php echo $tenant->voice_enabled ? 'checked' : ''; ?>><label class="form-check-label">Voice</label></div></div>
<div class="col-12"><div class="form-group"><label>Player HTML</label><textarea name="player_html" class="form-control" rows="2"><?php echo htmlspecialchars($tenant->player_html ?? ''); ?></textarea></div></div>
<div class="col-12"><button class="btn btn-primary">Save Settings</button></div>
</div>
</form>

<h4 style="margin:14px 0 8px">Rooms</h4>
<form method="POST" class="row g-2 mb-2">
<input type="hidden" name="action" value="add_room">
<div class="col-md-4"><input name="name" class="form-control" placeholder="Room name" required></div>
<div class="col-md-2"><select name="type" class="form-select"><option value="public">Public</option><option value="private">Private</option><option value="password">Password</option></select></div>
<div class="col-md-3"><input name="password" class="form-control" placeholder="Password"></div>
<div class="col-md-2"><button class="btn btn-primary">Add Room</button></div>
</form>
<?php foreach ($roomsList as $r): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border,rgba(0,191,255,.04))">
<span><?php echo htmlspecialchars($r->name); ?> <span style="color:var(--text_muted);font-size:11px">(<?php echo $r->type; ?>)</span></span>
<form method="POST"><input type="hidden" name="action" value="delete_room"><input type="hidden" name="room_id" value="<?php echo $r->id; ?>"><button class="btn btn-sm btn-danger">&#10005;</button></form>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
