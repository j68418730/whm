<?php
$themes = ['default'=>'Default Dark','blue'=>'Blue','black'=>'Black','white'=>'White','gray'=>'Gray','neon'=>'Neon','gaming'=>'Gaming','hacker'=>'Hacker','matrix'=>'Matrix','discord'=>'Discord','twitch'=>'Twitch','retro'=>'Retro','purple'=>'Purple','red'=>'Red','gold'=>'Gold'];
$server = $_SERVER['HTTP_HOST'] ?? 'planet-hosts.com';
?>
<style>
:root{--bg-card:rgba(8,16,28,.6);--bg-card2:rgba(8,16,28,.85);--border:rgba(255,255,255,.04);--border2:rgba(0,191,255,.08);--text:#e0e0e0;--text2:#94a3b8;--text3:#64748b;--accent:#0A84FF;--accent2:rgba(0,140,255,.15);--danger:rgba(248,113,113,.12);--danger-text:#f87171;--success:rgba(74,222,128,.12);--success-text:#4ade80}
.chat-header{background:linear-gradient(135deg,rgba(0,140,255,.06),rgba(168,85,247,.04));border:1px solid var(--border2);border-radius:12px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.chat-header h2{margin:0;font-size:16px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px}
.chat-header p{margin:2px 0 0;font-size:12px;color:var(--text2)}
.chat-card{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px}
.chat-card h3{font-size:13px;font-weight:600;color:var(--text);margin:0 0 12px;display:flex;align-items:center;gap:6px}
.chat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px}
.chat-grid .fld label{font-size:10px;color:var(--text3);display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:.3px;font-weight:600}
.chat-grid .fld input,.chat-grid .fld select{width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.35);color:var(--text);font-size:12px;outline:none;box-sizing:border-box;transition:border-color .15s}
.chat-grid .fld input:focus,.chat-grid .fld select:focus{border-color:var(--accent);box-shadow:0 0 0 2px var(--accent2)}
.chat-grid .fld input[type=color]{height:38px;padding:2px;cursor:pointer}
.chat-code{background:rgba(0,0,0,.4);border:1px solid var(--border2);border-radius:8px;padding:10px 12px;font-family:"Courier New",monospace;font-size:11px;color:var(--success-text);word-break:break-all;user-select:all;margin-bottom:6px;line-height:1.5}
.chat-room-item{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:rgba(0,0,0,.2);border:1px solid var(--border);border-radius:8px;margin-bottom:4px;font-size:13px;transition:background .15s}
.chat-room-item:hover{background:rgba(0,0,0,.35)}
.chat-room-item .tag{font-size:10px;color:var(--text3);margin-left:6px}
.chk-row{display:flex;gap:16px;flex-wrap:wrap;margin:10px 0}
.chk-row label{display:flex;align-items:center;gap:6px;font-size:12px;color:#c0c0c0;cursor:pointer}
.chk-row input{cursor:pointer}
.enable-card{text-align:center;padding:40px 20px}
.enable-card .icon{font-size:48px;margin-bottom:10px}
.enable-card h3{font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px}
.enable-card p{font-size:12px;color:var(--text2);margin-bottom:16px}
.embed-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:600px){.embed-row{grid-template-columns:1fr}.chat-grid{grid-template-columns:1fr 1fr}}
</style>

<div class="chat-header">
<div style="font-size:32px">💬</div>
<div style="flex:1"><h2>Live Chat</h2><p>Configure your chat widget, rooms, and embed options for your website.</p></div>
<?php if ($tenant): ?>
<a href="/chatbox/admin.php?tenant_id=<?=$tenant->id?>" target="_blank" class="btn btn-sm" style="background:var(--accent2);color:var(--accent);border:none;border-radius:6px;padding:6px 14px;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none">🎛️ Admin Panel</a>
<?php endif; ?>
</div>

<?php if (!$hosting): ?>
<div class="chat-card enable-card"><p style="color:#f87171">Account not found.</p></div>
<?php elseif (!$tenant): ?>
<div class="chat-card enable-card">
<div class="icon">🚀</div>
<h3>Enable Live Chat</h3>
<p>Create your chat room to start chatting with visitors on your website. Guests can join public rooms, register accounts, and use voice chat.</p>
<form method="POST"><input type="hidden" name="action" value="create"><button class="btn btn-primary" style="padding:10px 28px;font-size:14px;background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer"><i class="bi bi-plus-circle"></i> Enable Chat</button></form>
</div>
<?php else: ?>

<div class="chat-card">
<h3>📎 Embed Codes</h3>
<div class="embed-row">
<div><div style="font-size:10px;color:var(--text3);margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:.3px">JavaScript Widget</div>
<div class="chat-code">&lt;script src="http://<?=$server;?>/chatbox/widget.js.php?tenant_id=<?=$tenant->id;?>"&gt;&lt;/script&gt;</div>
<button class="btn btn-sm" style="background:var(--accent2);color:var(--accent);border:none;border-radius:6px;padding:5px 12px;font-size:10px;font-weight:600;cursor:pointer" onclick="cpt(this,'js')">📋 Copy JS</button></div>
<div><div style="font-size:10px;color:var(--text3);margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:.3px">iFrame Embed</div>
<div class="chat-code">&lt;iframe src="http://<?=$server;?>/chatbox/embed.php?tenant_id=<?=$tenant->id;?>" width="360" height="500"&gt;&lt;/iframe&gt;</div>
<button class="btn btn-sm" style="background:var(--accent2);color:var(--accent);border:none;border-radius:6px;padding:5px 12px;font-size:10px;font-weight:600;cursor:pointer" onclick="cpt(this,'iframe')">📋 Copy Iframe</button></div>
</div></div>

<div class="chat-card">
<h3>⚙️ Widget Settings</h3>
<form method="POST"><input type="hidden" name="action" value="save_settings">
<div class="chat-grid">
<div class="fld"><label>Title</label><input name="title" value="<?=htmlspecialchars($tenant->widget_title ?? '');?>"></div>
<div class="fld"><label>Font</label><input name="font" value="<?=htmlspecialchars($tenant->font_family ?? 'Inter, sans-serif');?>"></div>
<div class="fld"><label>Theme</label><select name="theme"><option value="custom">Custom</option><?php foreach($themes as $k=>$v):?><option value="<?=$k;?>" <?=($tenant->theme??'default')===$k?'selected':'';?>><?=$v;?></option><?php endforeach;?></select></div>
<div class="fld"><label>Accent</label><input name="color" type="color" value="<?=$tenant->widget_color??'#008cff';?>"></div>
<div class="fld"><label>Background</label><input name="bg" type="color" value="<?=$tenant->widget_bg??'#0a0e1a';?>"></div>
<div class="fld"><label>Text</label><input name="text_color" type="color" value="<?=$tenant->widget_text_color??'#ffffff';?>"></div>
<div class="fld"><label>Border</label><input name="border_color" type="color" value="<?=$tenant->widget_border_color??'rgba(255,255,255,.1)';?>"></div>
<div class="fld"><label>Glow</label><input name="glow_color" type="color" value="<?=$tenant->widget_glow_color??'#008cff';?>"></div>
<div class="fld"><label>Avatar</label><select name="avatar_shape"><option value="circle" <?=($tenant->widget_avatar_shape??'circle')==='circle'?'selected':'';?>>Circle</option><option value="square" <?=($tenant->widget_avatar_shape??'')==='square'?'selected':'';?>>Square</option><option value="rounded" <?=($tenant->widget_avatar_shape??'')==='rounded'?'selected':'';?>>Rounded</option></select></div>
<div class="fld" style="grid-column:span 2"><label>Player HTML</label><input name="player_html" value="<?=htmlspecialchars($tenant->player_html??'');?>"></div>
</div>
<div class="chk-row">
<label><input type="checkbox" name="guest" value="1" <?=$tenant->guest_enabled?'checked':'';?>> Allow Guests</label>
<label><input type="checkbox" name="reg" value="1" <?=$tenant->registration_enabled?'checked':'';?>> Registration</label>
<label><input type="checkbox" name="voice" value="1" <?=$tenant->voice_enabled?'checked':'';?>> Voice Chat</label>
</div>
<div style="margin-bottom:10px">
<label style="font-size:10px;color:var(--text3);display:block;margin-bottom:3px;font-weight:600;text-transform:uppercase;letter-spacing:.3px">Custom CSS</label>
<textarea name="custom_css" rows="2" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.35);color:var(--text);font-size:11px;outline:none;font-family:monospace;box-sizing:border-box;resize:vertical"><?=htmlspecialchars($tenant->custom_css??'');?></textarea>
</div>
<button type="submit" class="btn btn-primary" style="padding:8px 20px;font-size:12px;background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer"><i class="bi bi-check-lg"></i> Save Settings</button>
</form></div>

<div class="chat-card">
<h3>🚪 Rooms <span style="font-weight:400;font-size:10px;color:var(--text3)">(<?=count($roomsList);?>)</span></h3>
<form method="POST" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
<input type="hidden" name="action" value="add_room">
<input name="name" placeholder="Room name" required style="flex:1;min-width:120px;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.35);color:var(--text);font-size:12px;outline:none">
<select name="type" style="padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.35);color:var(--text);font-size:12px;outline:none"><option value="public">Public</option><option value="private">Private</option><option value="password">Password</option></select>
<input name="password" placeholder="Password" style="width:100px;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.35);color:var(--text);font-size:12px;outline:none">
<button type="submit" class="btn btn-sm" style="background:var(--accent2);color:var(--accent);border:none;border-radius:6px;padding:7px 14px;font-size:11px;font-weight:600;cursor:pointer"><i class="bi bi-plus"></i> Add Room</button>
</form>
<?php foreach($roomsList as $r): ?>
<div class="chat-room-item">
<span><?=htmlspecialchars($r->name);?> <span class="tag">(<?=$r->type;?>)</span></span>
<form method="POST" style="margin:0"><input type="hidden" name="action" value="delete_room"><input type="hidden" name="room_id" value="<?=$r->id;?>"><button class="btn btn-sm" style="background:var(--danger);color:var(--danger-text);border:none;border-radius:5px;padding:4px 8px;font-size:10px;cursor:pointer">✕</button></form>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<script>function cpt(btn,mode){var c={js:'<script src="http://<?=$server;?>/chatbox/widget.js.php?tenant_id=<?=$tenant->id??0;?>"><\/script>',iframe:'<iframe src="http://<?=$server;?>/chatbox/embed.php?tenant_id=<?=$tenant->id??0;?>" width="360" height="500"></iframe>'};navigator.clipboard.writeText(c[mode]||'').then(function(){btn.textContent='✅ Copied!';setTimeout(function(){btn.textContent='📋 Copy '+(mode==='js'?'JS':'Iframe');},2000);});}</script>
