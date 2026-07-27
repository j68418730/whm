<?php
if (!isset($hosting) || !$hosting) { echo '<div class="card"><p style="color:#64748b;padding:20px;text-align:center">No account found.</p></div>'; exit; }
$tenant = $pdo->prepare("SELECT * FROM chatbox_tenants WHERE hosting_user_id = ?");
$tenant->execute([$hosting->id]);
$tenant = $tenant->fetch(PDO::FETCH_OBJ);

if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $pdo->prepare("INSERT IGNORE INTO chatbox_tenants (hosting_user_id, name, widget_title) VALUES (?, ?, ?)")->execute([$hosting->id, $hosting->username . "'s Chat", $hosting->username . ' Chat']);
        $tid = $pdo->lastInsertId();
        $pdo->prepare("INSERT IGNORE INTO chatbox_rooms (tenant_id, name, type) VALUES (?, 'General', 'public'), (?, 'Support', 'public')")->execute([$tid, $tid]);
        header('Location: /user/chat'); exit;
    }
    if ($_POST['action'] === 'save_settings' && $tenant) {
        $pdo->prepare("UPDATE chatbox_tenants SET widget_title=?, widget_color=?, widget_bg=?, widget_text_color=?, widget_border_color=?, widget_glow_color=?, widget_avatar_shape=?, font_family=?, theme=?, custom_css=?, guest_enabled=?, registration_enabled=?, voice_enabled=?, player_html=? WHERE id=?")
            ->execute([$_POST['title'], $_POST['color'], $_POST['bg'], $_POST['text_color'], $_POST['border_color'], $_POST['glow_color'], $_POST['avatar_shape'], $_POST['font'], $_POST['theme'], $_POST['custom_css'], (int)$_POST['guest'], (int)$_POST['reg'], (int)$_POST['voice'], $_POST['player_html'], $tenant->id]);
        header('Location: /user/chat'); exit;
    }
    if ($_POST['action'] === 'add_room' && $tenant) {
        $pdo->prepare("INSERT INTO chatbox_rooms (tenant_id, name, type, password) VALUES (?, ?, ?, ?)")->execute([$tenant->id, $_POST['name'], $_POST['type'], $_POST['type'] === 'password' ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null]);
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
$server = $_SERVER['HTTP_HOST'] ?? 'planet-hosts.com';
?>
<style>
.chat-settings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:12px}
.chat-settings-grid .field label{font-size:10px;color:#64748b;display:block;margin-bottom:2px;text-transform:uppercase;letter-spacing:.3px;font-weight:600}
.chat-settings-grid .field input,.chat-settings-grid .field select{width:100%;padding:6px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none}
.chat-settings-grid .field input:focus{border-color:#0A84FF}
.chat-code{background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.1);border-radius:6px;padding:8px 10px;font-family:'Courier New',monospace;font-size:10px;color:#4ade80;word-break:break-all;user-select:all;margin-bottom:6px}
.chat-room-item{display:flex;justify-content:space-between;align-items:center;padding:6px 10px;border:1px solid rgba(255,255,255,.04);border-radius:6px;margin-bottom:4px;font-size:12px}
.chat-room-item:hover{background:rgba(255,255,255,.02)}
</style>

<h2>💬 Live Chat</h2>
<p style="color:#64748b;margin-bottom:16px">Configure your live chat widget and chat rooms.</p>
<?php if (!$tenant): ?>
<div class="card" style="text-align:center;padding:32px"><h3 style="margin-bottom:8px">Enable Live Chat</h3><p style="color:#64748b;font-size:13px;margin-bottom:14px">Create your chat room to start chatting with visitors on your website.</p>
<form method="POST"><input type="hidden" name="action" value="create"><button class="btn btn-primary">🚀 Enable Chat</button></form></div>
<?php else: ?>
<div class="card"><h3 style="margin-bottom:12px">📎 Embed Codes</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div><div style="font-size:10px;color:#64748b;margin-bottom:3px">JavaScript Widget</div>
<div class="chat-code">&lt;script src="http://<?php echo $server; ?>/chatbox/widget.js.php?tenant_id=<?php echo $tenant->id; ?>"&gt;&lt;/script&gt;</div>
<button class="btn btn-sm btn-primary" onclick="copyText(this,'js')" data-txt='&lt;script src="http://<?php echo $server; ?>/chatbox/widget.js.php?tenant_id=<?php echo $tenant->id; ?>"&gt;&lt;/script&gt;'>📋 Copy</button></div>
<div><div style="font-size:10px;color:#64748b;margin-bottom:3px">iFrame Embed</div>
<div class="chat-code">&lt;iframe src="http://<?php echo $server; ?>/chatbox/embed.php?tenant_id=<?php echo $tenant->id; ?>" width="360" height="500"&gt;&lt;/iframe&gt;</div>
<button class="btn btn-sm btn-primary" onclick="copyText(this,'iframe')" data-txt='&lt;iframe src="http://<?php echo $server; ?>/chatbox/embed.php?tenant_id=<?php echo $tenant->id; ?>" width="360" height="500"&gt;&lt;/iframe&gt;'>📋 Copy</button></div>
</div></div>

<div class="card"><h3 style="margin-bottom:12px">⚙️ Widget Settings</h3>
<form method="POST"><input type="hidden" name="action" value="save_settings">
<div class="chat-settings-grid">
<div class="field"><label>Title</label><input name="title" value="<?php echo htmlspecialchars($tenant->widget_title ?? ''); ?>"></div>
<div class="field"><label>Font</label><input name="font" value="<?php echo htmlspecialchars($tenant->font_family ?? 'Inter, sans-serif'); ?>"></div>
<div class="field"><label>Theme</label><select name="theme"><option value="custom">Custom</option><?php foreach($themes as $k=>$v): ?><option value="<?php echo $k;?>" <?php echo ($tenant->theme??'default')===$k?'selected':'';?>><?php echo $v;?></option><?php endforeach;?></select></div>
<div class="field"><label>Accent</label><input name="color" type="color" value="<?php echo $tenant->widget_color??'#008cff';?>"></div>
<div class="field"><label>Background</label><input name="bg" type="color" value="<?php echo $tenant->widget_bg??'#0a0e1a';?>"></div>
<div class="field"><label>Text Color</label><input name="text_color" type="color" value="<?php echo $tenant->widget_text_color??'#ffffff';?>"></div>
<div class="field"><label>Border</label><input name="border_color" type="color" value="<?php echo $tenant->widget_border_color??'rgba(255,255,255,.1)';?>"></div>
<div class="field"><label>Glow</label><input name="glow_color" type="color" value="<?php echo $tenant->widget_glow_color??'#008cff';?>"></div>
<div class="field"><label>Avatar</label><select name="avatar_shape"><option value="circle" <?php echo ($tenant->widget_avatar_shape??'circle')==='circle'?'selected':'';?>>Circle</option><option value="square" <?php echo ($tenant->widget_avatar_shape??'')==='square'?'selected':'';?>>Square</option><option value="rounded" <?php echo ($tenant->widget_avatar_shape??'')==='rounded'?'selected':'';?>>Rounded</option></select></div>
<div class="field" style="grid-column:span 2"><label>Player HTML</label><input name="player_html" value="<?php echo htmlspecialchars($tenant->player_html??'');?>"></div>
</div>
<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin:8px 0">
<label style="display:flex;align-items:center;gap:4px;font-size:11px;cursor:pointer"><input type="checkbox" name="guest" value="1" <?php echo $tenant->guest_enabled?'checked':'';?>> Allow Guests</label>
<label style="display:flex;align-items:center;gap:4px;font-size:11px;cursor:pointer"><input type="checkbox" name="reg" value="1" <?php echo $tenant->registration_enabled?'checked':'';?>> Registration</label>
<label style="display:flex;align-items:center;gap:4px;font-size:11px;cursor:pointer"><input type="checkbox" name="voice" value="1" <?php echo $tenant->voice_enabled?'checked':'';?>> Voice Chat</label>
</div>
<div style="margin-bottom:8px"><label style="font-size:10px;color:#64748b;display:block;margin-bottom:2px">Custom CSS</label>
<textarea name="custom_css" rows="2" style="width:100%;padding:6px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none;font-family:monospace"><?php echo htmlspecialchars($tenant->custom_css??'');?></textarea></div>
<button type="submit" class="btn btn-primary btn-sm">💾 Save Settings</button>
</form></div>

<div class="card"><h3 style="margin-bottom:12px">🚪 Rooms</h3>
<form method="POST" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
<input type="hidden" name="action" value="add_room">
<input name="name" placeholder="Room name" required style="flex:1;min-width:120px;padding:6px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<select name="type" style="padding:6px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none"><option value="public">Public</option><option value="private">Private</option><option value="password">Password</option></select>
<input name="password" placeholder="Password" style="width:100px;padding:6px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<button type="submit" class="btn btn-sm btn-primary">➕ Add Room</button>
</form>
<?php foreach($roomsList as $r): ?>
<div class="chat-room-item"><span><?php echo htmlspecialchars($r->name); ?> <span style="color:#64748b;font-size:10px">(<?php echo $r->type; ?>)</span></span>
<form method="POST"><input type="hidden" name="action" value="delete_room"><input type="hidden" name="room_id" value="<?php echo $r->id;?>"><button class="btn btn-sm btn-danger">✕</button></form></div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<script>
function copyText(btn) {
    var txt = btn.getAttribute('data-txt');
    navigator.clipboard.writeText(txt);
    btn.textContent = '✅ Copied!';
    setTimeout(function(){btn.textContent = '📋 Copy';},2000);
}
</script>
