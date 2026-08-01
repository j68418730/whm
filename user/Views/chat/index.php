<?php
?>
<style>
:root{--bg-card:rgba(8,16,28,.6);--border:rgba(255,255,255,.04);--border2:rgba(0,191,255,.08);--text:#e0e0e0;--text2:#94a3b8;--text3:#64748b;--accent:#0A84FF;--accent2:rgba(0,140,255,.12);--danger:rgba(248,113,113,.12);--danger-text:#f87171}
.chat-hdr{background:linear-gradient(135deg,rgba(0,140,255,.06),rgba(168,85,247,.04));border:1px solid var(--border2);border-radius:12px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.chat-hdr h2{margin:0;font-size:16px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px}
.chat-hdr p{margin:2px 0 0;font-size:12px;color:var(--text2)}
.card{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px}
.card h3{font-size:13px;font-weight:600;color:var(--text);margin:0 0 12px;display:flex;align-items:center;gap:6px}
.room-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;margin-bottom:14px}
.room-card{background:var(--bg-card);border:1px solid var(--border2);border-radius:12px;padding:16px;transition:.15s;position:relative;overflow:hidden}
.room-card:hover{border-color:rgba(0,140,255,.2)}
.room-card .color-bar{height:4px;border-radius:12px 12px 0 0;margin:-16px -16px 12px}
.room-card .top{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.room-card .icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;color:#fff}
.room-card .name{font-size:14px;font-weight:700;color:var(--text);flex:1}
.room-card .type-badge{font-size:9px;padding:2px 8px;border-radius:4px;font-weight:600}
.room-card .type-public{background:rgba(74,222,128,.12);color:#4ade80}
.room-card .type-private{background:rgba(248,113,113,.12);color:#f87171}
.room-card .type-password{background:rgba(250,204,21,.12);color:#eab308}
.room-card .desc{font-size:11px;color:var(--text3);margin-bottom:8px;line-height:1.4}
.room-card .meta{display:flex;gap:12px;font-size:10px;color:var(--text3);margin-bottom:8px;flex-wrap:wrap}
.room-card .meta span{display:flex;align-items:center;gap:3px}
.room-card .actions{display:flex;gap:4px;flex-wrap:wrap}
.room-card .actions button{padding:5px 12px;border-radius:6px;border:none;font-size:10px;font-weight:600;cursor:pointer;transition:.1s}
.btn-edit{background:var(--accent2);color:var(--accent)}
.btn-edit:hover{background:rgba(0,140,255,.2)}
.btn-del{background:var(--danger);color:var(--danger-text)}
.btn-del:hover{background:rgba(248,113,113,.2)}
.btn-add{background:var(--accent2);color:var(--accent);border:1px dashed var(--accent);padding:10px;border-radius:12px;cursor:pointer;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px;min-height:100px;transition:.15s;width:100%}
.btn-add:hover{background:rgba(0,140,255,.18)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.form-grid .fld label{font-size:10px;color:var(--text3);display:block;margin-bottom:2px;font-weight:600;text-transform:uppercase;letter-spacing:.3px}
.form-grid .fld input,.form-grid .fld textarea,.form-grid .fld select{width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.35);color:var(--text);font-size:12px;outline:none;box-sizing:border-box;font-family:inherit}
.form-grid .fld input:focus,.form-grid .fld textarea:focus,.form-grid .fld select:focus{border-color:var(--accent)}
.form-grid .fld input[type=color]{height:36px;padding:2px;cursor:pointer}
.chk-row{display:flex;gap:14px;flex-wrap:wrap;margin:8px 0}
.chk-row label{display:flex;align-items:center;gap:5px;font-size:12px;color:#c0c0c0;cursor:pointer}
.modal{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.7);z-index:1000;display:none;align-items:center;justify-content:center}
.modal.open{display:flex}
.modal-inner{background:#0f172a;border:1px solid var(--border2);border-radius:14px;padding:24px;width:90%;max-width:500px;max-height:85vh;overflow-y:auto}
.modal-inner h3{margin:0 0 14px;font-size:15px;color:var(--text)}
.save-btn{padding:8px 20px;border-radius:8px;border:none;background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;font-weight:600;font-size:12px;cursor:pointer}
.cancel-btn{padding:8px 20px;border-radius:8px;border:1px solid rgba(255,255,255,.06);background:none;color:var(--text3);font-size:12px;cursor:pointer;margin-left:6px}
.embed-code{background:rgba(0,0,0,.4);border:1px solid var(--border2);border-radius:6px;padding:8px 10px;font-family:monospace;font-size:10px;color:#4ade80;word-break:break-all;user-select:all;margin-bottom:6px}
@media(max-width:600px){.room-grid{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}}
</style>

<div class="chat-hdr">
<div style="font-size:32px">💬</div>
<div style="flex:1"><h2>My Chat Rooms</h2><p>Manage your chat rooms, create new ones, and customize the widget.</p></div>
</div>

<?php if (!$tenant): ?>
<div class="card" style="text-align:center;padding:32px"><div style="font-size:48px;margin-bottom:10px">🚀</div><h3 style="margin-bottom:8px">Enable Live Chat</h3><p style="color:var(--text2);font-size:12px;margin-bottom:14px">Create your chat room to start chatting with visitors on your website.</p>
<form method="POST"><input type="hidden" name="action" value="create"><button class="save-btn" style="padding:10px 28px;font-size:14px">Enable Chat</button></form></div>
<?php else: ?>

<div class="room-grid">
<?php foreach ($roomsList as $r):
$color = $r->color ?? '#008cff';
$icon = $r->icon ?: ($r->name[0] ?? '#');
$typeClass = $r->type === 'public' ? 'type-public' : ($r->type === 'password' ? 'type-password' : 'type-private');
$typeLabel = $r->type === 'public' ? 'Public' : ($r->type === 'password' ? '🔒 Password' : '🔐 Private');
?>
<div class="room-card">
<div class="color-bar" style="background:<?=$color?>"></div>
<div class="top">
<div class="icon" style="background:<?=$color?>"><?=htmlspecialchars($icon)?></div>
<div class="name"><?=htmlspecialchars($r->name)?></div>
<span class="type-badge <?=$typeClass?>"><?=$typeLabel?></span>
</div>
<div class="desc"><?=htmlspecialchars($r->description ?: 'No description')?></div>
<div class="meta">
<span><?=$r->guest_enabled?'👤 Guests':'🚫 No guests'?></span>
<span><?=$r->registration_enabled?'📝 Registration':'🔒 No signup'?></span>
<span><?=$r->voice_enabled?'🔊 Voice':'🔇 No voice'?></span>
</div>
<div style="font-size:10px;color:var(--text3);margin-bottom:8px;display:flex;align-items:center;gap:6px">
<span>🔗</span>
<a href="/chat/<?=htmlspecialchars($r->slug ?? 'room-'.$r->id)?>" target="_blank" style="font-size:10px;color:var(--accent);background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px;text-decoration:none">/chat/<?=htmlspecialchars($r->slug ?? 'room-'.$r->id)?></a>
<button class="btn-edit" style="font-size:8px;padding:2px 6px" onclick="copySlug(<?=$r->id?>,'<?=htmlspecialchars($r->slug ?? 'room-'.$r->id)?>')">Copy</button>
</div>
<div class="actions">
<button class="btn-edit" onclick="openEdit(<?=$r->id?>,'<?=htmlspecialchars($r->name, ENT_QUOTES)?>','<?=htmlspecialchars($r->description, ENT_QUOTES)?>','<?=$r->type?>','<?=$r->color?>','<?=htmlspecialchars($r->icon??'', ENT_QUOTES)?>',<?=$r->guest_enabled?1:0?>,<?=$r->registration_enabled?1:0?>,<?=$r->voice_enabled?1:0?>)">✏️ Edit</button>
<form method="POST" style="display:inline" onsubmit="return confirm('Delete this room?')"><input type="hidden" name="action" value="delete_room"><input type="hidden" name="room_id" value="<?=$r->id?>"><button class="btn-del">🗑️ Delete</button></form>
</div>
</div>
<?php endforeach; ?>
<button class="btn-add" onclick="openCreate()">➕ New Room</button>
</div>

<!-- Tabs -->
<div style="display:flex;gap:4px;margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:4px">
<div class="tab-btn active" style="padding:7px 16px;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;background:var(--accent2);color:var(--accent)" onclick="switchTab(this,'rooms')">🏠 Rooms</div>
<div class="tab-btn" style="padding:7px 16px;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;color:var(--text3)" onclick="switchTab(this,'widget')">⚙️ Widget</div>
</div>

<!-- Widget Settings tab -->
<div id="tab-widget" style="display:none">
<div class="card">
<h3>⚙️ Widget Settings</h3>
<form method="POST"><input type="hidden" name="action" value="save_widget">
<div class="form-grid">
<div class="fld"><label>Title</label><input name="title" value="<?=htmlspecialchars($tenant->widget_title ?? '')?>"></div>
<div class="fld"><label>Font</label><input name="font" value="<?=htmlspecialchars($tenant->font_family ?? 'Inter, sans-serif')?>"></div>
<div class="fld"><label>Theme</label><select name="theme"><option value="custom">Custom</option><?php foreach($themes as $k=>$v):?><option value="<?=$k?>" <?=($tenant->theme??'default')===$k?'selected':''?>><?=$v?></option><?php endforeach?></select></div>
<div class="fld"><label>Accent</label><input name="color" type="color" value="<?=$tenant->widget_color??'#008cff'?>"></div>
<div class="fld"><label>Background</label><input name="bg" type="color" value="<?=$tenant->widget_bg??'#0a0e1a'?>"></div>
<div class="fld"><label>Text</label><input name="text_color" type="color" value="<?=$tenant->widget_text_color??'#ffffff'?>"></div>
<div class="fld"><label>Border</label><input name="border_color" type="color" value="<?=$tenant->widget_border_color??'rgba(255,255,255,.1)'?>"></div>
<div class="fld"><label>Glow</label><input name="glow_color" type="color" value="<?=$tenant->widget_glow_color??'#008cff'?>"></div>
<div class="fld"><label>Avatar</label><select name="avatar_shape"><option value="circle" <?=($tenant->widget_avatar_shape??'circle')==='circle'?'selected':''?>>Circle</option><option value="square" <?=($tenant->widget_avatar_shape??'')==='square'?'selected':''?>>Square</option><option value="rounded" <?=($tenant->widget_avatar_shape??'')==='rounded'?'selected':''?>>Rounded</option></select></div>
<div class="fld" style="grid-column:span 2"><label>Player HTML</label><input name="player_html" value="<?=htmlspecialchars($tenant->player_html??'')?>"></div>
</div>
<div class="chk-row">
<label><input type="checkbox" name="guest_all" value="1" <?=$tenant->guest_enabled?'checked':''?>> Allow Guests (global)</label>
<label><input type="checkbox" name="reg_all" value="1" <?=$tenant->registration_enabled?'checked':''?>> Registration (global)</label>
<label><input type="checkbox" name="voice_all" value="1" <?=$tenant->voice_enabled?'checked':''?>> Voice Chat (global)</label>
</div>
<div style="margin-bottom:8px"><label style="font-size:10px;color:var(--text3);display:block;margin-bottom:2px;font-weight:600">Custom CSS</label><textarea name="custom_css" rows="2" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.35);color:var(--text);font-size:11px;outline:none;font-family:monospace;resize:vertical"><?=htmlspecialchars($tenant->custom_css??'')?></textarea></div>
<button type="submit" class="save-btn">💾 Save Widget Settings</button>
</form></div>

<div class="card"><h3>📎 Embed Codes</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div><div style="font-size:10px;color:var(--text3);margin-bottom:4px;font-weight:600">JavaScript Widget</div>
<div class="embed-code">&lt;script src="https://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?=$tenant->id?>"&gt;&lt;/script&gt;</div>
<button class="save-btn" style="padding:4px 14px;font-size:10px" onclick="copy(this,'js')">📋 Copy</button></div>
<div><div style="font-size:10px;color:var(--text3);margin-bottom:4px;font-weight:600">iFrame Embed</div>
<div class="embed-code">&lt;iframe src="https://planet-hosts.com/chatbox/embed.php?tenant_id=<?=$tenant->id?>" width="360" height="500"&gt;&lt;/iframe&gt;</div>
<button class="save-btn" style="padding:4px 14px;font-size:10px" onclick="copy(this,'iframe')">📋 Copy</button></div>
</div></div>

<div class="card"><h3>😊 Custom Emojis</h3>
<div id="emoji-manager">
<div style="color:var(--text3);font-size:12px">Loading emojis...</div>
</div>
<div style="display:flex;gap:6px;margin-top:10px;flex-wrap:wrap">
<input id="emoji-name" placeholder="Emoji name (e.g. hype)" style="flex:1;min-width:120px;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.35);color:var(--text);font-size:12px;outline:none">
<input id="emoji-file" type="file" accept="image/png,image/gif,image/webp,image/svg+xml,image/jpeg" style="flex:1;min-width:150px;font-size:11px;color:var(--text3)">
<button class="save-btn" onclick="uploadEmoji()">📤 Upload</button>
</div>
</div>
</div>
<?php endif; ?>

<!-- Modal -->
<div class="modal" id="roomModal">
<div class="modal-inner">
<h3 id="modalTitle">✏️ Create Room</h3>
<form method="POST" id="roomForm">
<input type="hidden" name="action" id="formAction" value="create_room">
<input type="hidden" name="room_id" id="formRoomId" value="">
<div class="form-grid">
<div class="fld"><label>Room Name</label><input name="name" id="fName" required oninput="autoSlug()"></div>
<div class="fld"><label>Direct URL</label><input name="slug" id="fSlug" placeholder="my-room" style="font-family:monospace;font-size:11px"><div style="font-size:9px;color:var(--text3);margin-top:2px">planet-hosts.com/chat/<strong id="slugPreview" style="color:var(--accent)">my-room</strong></div></div>
<div class="fld"><label>Type</label><select name="type" id="fType" onchange="togglePw()"><option value="public">Public</option><option value="private">Private</option><option value="password">Password</option></select></div>
<div class="fld" id="pwField" style="display:none"><label>Password</label><input name="password" id="fPass" type="password"></div>
<div class="fld"><label>Color</label><input name="color" id="fColor" type="color" value="#008cff"></div>
<div class="fld"><label>Icon (emoji)</label><input name="icon" id="fIcon" maxlength="2" placeholder="🎮"></div>
<div class="fld" style="grid-column:span 2"><label>Description</label><textarea name="description" id="fDesc" rows="2"></textarea></div>
</div>
<div class="chk-row">
<label><input type="checkbox" name="guest" id="fGuest" value="1"> Allow Guests</label>
<label><input type="checkbox" name="reg" id="fReg" value="1"> Registration</label>
<label><input type="checkbox" name="voice" id="fVoice" value="1"> Voice Chat</label>
</div>
<div style="display:flex;gap:6px;margin-top:10px">
<button type="submit" class="save-btn">💾 Save</button>
<button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
</div>
</form>
</div>
</div>

<script>
function loadEmojis(){
var x=new XMLHttpRequest();
x.open('GET','/chatbox/api.php?action=get_emojis&tenant_id=<?=$tenant->id??0?>',true);
x.onload=function(){if(x.status!==200)return;try{var list=JSON.parse(x.responseText);
var el=document.getElementById('emoji-manager');
if(!list||!list.length){el.innerHTML='<div style="color:var(--text3);font-size:12px">No custom emojis yet. Upload one below.</div>';return;}
var h='<div style="display:flex;flex-wrap:wrap;gap:8px">';
list.forEach(function(e){h+='<div style="text-align:center;padding:8px;background:rgba(0,0,0,.2);border-radius:8px"><img src="'+e.url+'" style="width:36px;height:36px;border-radius:4px;object-fit:contain"><div style="font-size:9px;color:var(--text3);margin-top:3px">:'+e.name+':</div><button class="btn-del" style="font-size:8px;padding:2px 6px;margin-top:3px" onclick="deleteEmoji('+e.id+')">✕</button></div>';});
h+='</div>';el.innerHTML=h;}catch(e){}};x.send();
}
function uploadEmoji(){
var name=document.getElementById('emoji-name').value.trim();
var file=document.getElementById('emoji-file').files[0];
if(!name||!file){alert('Enter a name and choose a file');return;}
var fd=new FormData();
fd.append('action','save_emoji');
fd.append('name',name);
fd.append('emoji',file);
var x=new XMLHttpRequest();x.open('POST','/chatbox/api.php',true);
x.onload=function(){loadEmojis();document.getElementById('emoji-name').value='';document.getElementById('emoji-file').value='';};
x.send(fd);
}
function deleteEmoji(id){
if(!confirm('Delete this emoji?'))return;
var fd=new FormData();fd.append('action','delete_emoji');fd.append('id',id);
var x=new XMLHttpRequest();x.open('POST','/chatbox/api.php',true);
x.onload=function(){loadEmojis();};x.send(fd);
}
loadEmojis();
function autoSlug(){var n=document.getElementById('fName').value;var s=n.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');document.getElementById('fSlug').value=s;document.getElementById('slugPreview').textContent=s||'my-room';}
function copySlug(id,slug){navigator.clipboard.writeText('https://planet-hosts.com/chat/'+slug).then(function(){var b=event.target;b.textContent='✅';setTimeout(function(){b.textContent='Copy';},2000);});}
function switchTab(el,tab){document.querySelectorAll('.tab-btn').forEach(function(t){t.style.background='none';t.style.color='var(--text3)';});el.style.background='var(--accent2)';el.style.color='var(--accent)';document.getElementById('tab-widget').style.display=tab==='widget'?'':'none';document.querySelector('.room-grid').style.display=tab==='widget'?'none':'';}
function openCreate(){document.getElementById('modalTitle').textContent='✏️ Create Room';document.getElementById('formAction').value='create_room';document.getElementById('formRoomId').value='';document.getElementById('fName').value='';document.getElementById('fDesc').value='';document.getElementById('fType').value='public';document.getElementById('fPass').value='';document.getElementById('fColor').value='#008cff';document.getElementById('fIcon').value='';document.getElementById('fGuest').checked=false;document.getElementById('fReg').checked=false;document.getElementById('fVoice').checked=false;togglePw();document.getElementById('roomModal').classList.add('open');}
function openEdit(id,name,desc,type,color,icon,guest,reg,voice){document.getElementById('modalTitle').textContent='⚙️ Edit Room';document.getElementById('formAction').value='update_room';document.getElementById('formRoomId').value=id;document.getElementById('fName').value=name;document.getElementById('fDesc').value=desc;document.getElementById('fType').value=type;document.getElementById('fPass').value='';document.getElementById('fColor').value=color||'#008cff';document.getElementById('fIcon').value=icon||'';document.getElementById('fGuest').checked=guest==1;document.getElementById('fReg').checked=reg==1;document.getElementById('fVoice').checked=voice==1;togglePw();document.getElementById('roomModal').classList.add('open');}
function togglePw(){document.getElementById('pwField').style.display=document.getElementById('fType').value==='password'?'':'none';}
function closeModal(){document.getElementById('roomModal').classList.remove('open');}
function copy(btn,mode){var codes={js:'<script src="https://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?=$tenant->id??0?>"><\/script>',iframe:'<iframe src="https://planet-hosts.com/chatbox/embed.php?tenant_id=<?=$tenant->id??0?>" width="360" height="500"></iframe>'};navigator.clipboard.writeText(codes[mode]||'').then(function(){btn.textContent='✅ Copied!';setTimeout(function(){btn.textContent='📋 Copy';},2000);});}
</script>
