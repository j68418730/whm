<style>
*{box-sizing:border-box}
:root{--bg:#080f1c;--bg2:#0f172a;--card:rgba(15,23,42,.5);--brd:rgba(56,189,248,.06);--txt:#e2e8f0;--t2:#94a3b8;--t3:#64748b;--bl:#008cff;--bl2:rgba(0,140,255,.1);--gr:#4ade80;--rd:#f87171;--inp:rgba(0,0,0,.35)}
body{margin:0;font-family:Inter,sans-serif;background:var(--bg);color:var(--txt);height:100vh;overflow:hidden}
.chat{display:grid;grid-template-columns:240px 1fr 200px;height:100vh;gap:0}
/* Categories sidebar */
.cat-side{background:var(--bg2);border-right:1px solid var(--brd);overflow-y:auto;display:flex;flex-direction:column}
.cat-side .hdr{padding:14px 16px;font-size:13px;font-weight:700;border-bottom:1px solid var(--brd);display:flex;justify-content:space-between;align-items:center}
.cat-side .hdr button{background:var(--bl2);color:var(--bl);border:none;border-radius:6px;padding:4px 10px;font-size:10px;cursor:pointer;font-weight:600}
.cat-group{margin-bottom:2px}
.cat-header{padding:10px 16px 4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--t3);cursor:pointer;display:flex;align-items:center;gap:4px;user-select:none}
.cat-header:hover{color:var(--t2)}
.room-item{padding:6px 16px 6px 24px;cursor:pointer;border-radius:4px;margin:1px 6px;font-size:12px;color:var(--t2);display:flex;align-items:center;gap:8px;transition:.1s}
.room-item:hover{background:var(--bl2);color:var(--txt)}
.room-item.active{background:var(--bl2);color:var(--bl)}
.room-item .hash{font-size:14px;font-weight:700;width:16px;text-align:center;flex-shrink:0}
.room-item .lock{font-size:10px;color:var(--t3)}
.room-item .voice-icon{margin-left:auto;font-size:10px;color:var(--t3)}
/* Main chat area */
.main-area{display:flex;flex-direction:column;overflow:hidden}
.chat-hdr{padding:10px 18px;border-bottom:1px solid var(--brd);display:flex;justify-content:space-between;align-items:center;flex-shrink:0;background:var(--bg)}
.chat-hdr .nm{font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px}
.chat-hdr .nm .hash{color:var(--t3);font-size:16px}
.chat-hdr .nm .lock-icon{font-size:11px;color:var(--t3)}
.chat-hdr .desc{font-size:10px;color:var(--t3);margin-left:4px}
.chat-hdr .actions{display:flex;gap:4px}
.chat-hdr .actions button{background:none;border:none;color:var(--t3);cursor:pointer;padding:4px 8px;border-radius:6px;font-size:11px}
.chat-hdr .actions button:hover{background:var(--bl2);color:var(--txt)}
.msgs{flex:1;overflow-y:auto;padding:12px 18px;display:flex;flex-direction:column;gap:4px}
.msg{max-width:80%;padding:8px 12px;border-radius:10px;font-size:13px;line-height:1.5;word-wrap:break-word}
.msg.own{background:rgba(0,140,255,.08);align-self:flex-end;border-bottom-right-radius:4px}
.msg.ot{background:var(--card);align-self:flex-start;border-bottom-left-radius:4px}
.msg.sys{align-self:center;background:none;color:var(--t3);font-size:10px;font-style:italic;max-width:100%;text-align:center}
.msg .sd{font-size:10px;font-weight:600;color:var(--bl);margin-bottom:2px;cursor:pointer}
.msg .sd:hover{text-decoration:underline}
.msg .tm{font-size:9px;color:var(--t3);margin-top:2px;text-align:right}
.msg .role-badge{display:inline-block;font-size:8px;padding:1px 5px;border-radius:3px;margin-left:4px;font-weight:600}
.role-badge.o{background:rgba(249,115,22,.15);color:#f97316}
.role-badge.a{background:rgba(56,189,248,.15);color:#38bdf8}
.role-badge.m{background:rgba(168,85,247,.15);color:#a855f7}
.role-badge.v{background:rgba(234,179,8,.15);color:#eab308}
.inp-area{padding:10px 18px;border-top:1px solid var(--brd);flex-shrink:0}
.inp-row{display:flex;gap:8px;align-items:flex-end}
.inp-row textarea{flex:1;padding:8px 14px;border-radius:10px;border:1px solid var(--brd);background:var(--inp);color:var(--txt);font-size:13px;outline:none;resize:none;min-height:38px;max-height:120px;font-family:inherit}
.inp-row textarea:focus{border-color:var(--bl)}
.inp-row .sb{width:36px;height:36px;border-radius:50%;background:var(--bl);color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
/* Members sidebar */
.member-side{background:var(--bg2);border-left:1px solid var(--brd);overflow-y:auto;padding:12px}
.member-side .hdr{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--t3);margin-bottom:8px}
.member-item{display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:6px;font-size:11px;color:var(--t2);margin-bottom:2px}
.member-item:hover{background:var(--bl2)}
.member-item .dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.member-item .role-badge{font-size:8px;padding:1px 5px;border-radius:3px}
/* Empty state */
.empty-chat{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--t3);gap:8px}
.empty-chat .ic{font-size:48px;opacity:.3}
/* Room create modal */
.modal{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.7);z-index:1000;display:none;align-items:center;justify-content:center}
.modal.open{display:flex}
.modal-inner{background:#0f172a;border:1px solid var(--brd);border-radius:16px;padding:24px;width:90%;max-width:420px;max-height:80vh;overflow-y:auto}
.modal-inner h3{margin:0 0 14px;font-size:15px;color:var(--txt)}
.modal-inner .fld{margin-bottom:10px}
.modal-inner .fld label{font-size:10px;color:var(--t3);display:block;margin-bottom:3px;font-weight:600;text-transform:uppercase;letter-spacing:.3px}
.modal-inner .fld input,.modal-inner .fld textarea,.modal-inner .fld select{width:100%;padding:8px 10px;border-radius:6px;border:1px solid var(--brd);background:var(--inp);color:var(--txt);font-size:12px;outline:none;font-family:inherit}
.modal-inner .fld input:focus,.modal-inner .fld select:focus,.modal-inner .fld textarea:focus{border-color:var(--bl)}
.modal-inner .fld textarea{min-height:50px;resize:vertical}
.modal-inner .fld input[type=color]{height:38px;padding:2px;cursor:pointer}
.modal-inner .btn-p{width:100%;padding:9px;border-radius:8px;border:none;font-weight:600;font-size:12px;cursor:pointer;background:var(--bl);color:#fff;margin-top:6px}
.modal-inner .btn-d{width:100%;padding:8px;border-radius:6px;border:1px solid var(--brd);background:none;color:var(--t3);cursor:pointer;font-size:11px;margin-top:4px}
/* DMs */
.dm-list{margin-top:8px;border-top:1px solid var(--brd);padding-top:4px}
.dm-item{padding:5px 16px 5px 24px;cursor:pointer;border-radius:4px;margin:1px 6px;font-size:11px;color:var(--t2);display:flex;align-items:center;gap:8px;transition:.1s}
.dm-item:hover{background:var(--bl2);color:var(--txt)}
.dm-item.active{background:var(--bl2);color:var(--bl)}
.dm-item .unread{background:var(--bl);color:#fff;border-radius:10px;padding:1px 6px;font-size:8px;font-weight:700;margin-left:auto}
/* DM view */
.dm-hdr{padding:10px 18px;border-bottom:1px solid var(--brd);font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;flex-shrink:0;background:var(--bg)}
@media(max-width:768px){.chat{grid-template-columns:1fr}.member-side{display:none}.cat-side{display:none}.cat-side.show{display:flex;position:fixed;inset:0;z-index:100;background:var(--bg2)}}
</style>

<div class="chat" id="chatApp">
  <!-- Categories sidebar -->
  <div class="cat-side" id="catSide">
    <div class="hdr">Planet Hosts <button onclick="openCreateModal()">+</button></div>
    <div id="catList"></div>
  </div>
  
  <!-- Main area -->
  <div class="main-area" id="mainArea">
    <div class="empty-chat" id="emptyState">
      <div class="ic">💬</div>
      <div style="font-size:14px;color:var(--t2)">Select a room</div>
      <div style="font-size:11px">Choose a channel from the sidebar</div>
    </div>
  </div>
  
  <!-- Members sidebar -->
  <div class="member-side" id="memberSide">
    <div class="hdr" id="memberHdr">Members</div>
    <div id="memberList"></div>
  </div>
</div>

<!-- Create room modal -->
<div class="modal" id="createModal">
  <div class="modal-inner">
    <h3>✏️ Create Room</h3>
    <div class="fld"><label>Name</label><input id="rName" placeholder="e.g. general-chat"></div>
    <div class="fld"><label>Description</label><textarea id="rDesc" placeholder="What's this room about?"></textarea></div>
    <div class="fld"><label>Category</label><select id="rCat"></select></div>
    <div class="fld"><label>Visibility</label><select id="rVis"><option value="public">Public</option><option value="private">Private</option><option value="password">Password</option></select></div>
    <div class="fld" id="pwField" style="display:none"><label>Password</label><input id="rPass" type="password"></div>
    <div class="fld"><label>Icon (emoji)</label><input id="rIcon" maxlength="2" placeholder="🔊"></div>
    <div class="fld"><label>Color</label><input id="rColor" type="color" value="#008cff"></div>
    <div class="fld" style="display:flex;gap:12px"><label style="display:flex;align-items:center;gap:4px;font-size:12px;cursor:pointer"><input type="checkbox" id="rVoice"> Voice</label><label style="display:flex;align-items:center;gap:4px;font-size:12px;cursor:pointer"><input type="checkbox" id="rVideo"> Video</label></div>
    <button class="btn-p" onclick="createRoom()">Create Room</button>
    <button class="btn-d" onclick="closeCreateModal()">Cancel</button>
  </div>
</div>

<!-- Join password modal -->
<div class="modal" id="pwModal">
  <div class="modal-inner">
    <h3>🔒 Password Required</h3>
    <div class="fld"><label>Enter room password</label><input id="joinPass" type="password" onkeydown="if(event.key==='Enter')joinRoom(pwRoomId)"></div>
    <button class="btn-p" onclick="joinRoom(pwRoomId)">Join</button>
    <button class="btn-d" onclick="closePwModal()">Cancel</button>
  </div>
</div>

<script>
var uid=<?=($user->id??0)?>, curRoom=0, curDm=0, cats=[], rooms=[], dms=[];
var poll=null, pwRoomId=0;

// Init
function init(){
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=index',true);
x.onload=function(){if(x.status!==200)return;try{var d=JSON.parse(x.responseText);cats=d.categories;rooms=d.rooms;dms=d.dms;render();}catch(e){}};
x.send();
}

function render(){
var el=document.getElementById('catList');
var h='';
// DMs section
h+='<div class="cat-header" style="margin-top:4px">📩 Direct Messages</div>';
if(dms&&dms.length){dms.forEach(function(dm){
var act=curDm===dm.id&&!curRoom?'active':'';
var un=dm.unread>0?'<span class="unread">'+(dm.unread>9?'9+':dm.unread)+'</span>':'';
h+='<div class="dm-item '+act+'" onclick="selectDm('+dm.id+')">👤 <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">User #'+dm.id+'</span>'+un+'</div>';
});}
h+='<div class="dm-item" onclick="openDmSearch()" style="color:var(--bl)">➕ New DM</div>';

// Categories with rooms
cats.forEach(function(cat){
var catRooms=rooms.filter(function(r){return r.category_id===cat.id;});
if(!catRooms.length)return;
h+='<div class="cat-header" onclick="toggleCat(this)">▾ '+esc(cat.name)+'</div>';
h+='<div class="cat-rooms">';
catRooms.forEach(function(r){
var act=curRoom===r.id?'active':'';
var vis=r.visibility==='password'?'<span class="lock">🔒</span>':(r.visibility==='private'?'<span class="lock">🔐</span>':'<span class="hash">#</span>');
var vc=r.voice_enabled?'<span class="voice-icon">🔊</span>':'';
h+='<div class="room-item '+act+'" onclick="selectRoom('+r.id+')">'+vis+'<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+esc(r.name)+'</span>'+vc+'</div>';
});
h+='</div>';
});
el.innerHTML=h;
}

function toggleCat(el){
var next=el.nextElementSibling;
next.style.display=next.style.display==='none'?'':'none';
el.textContent=el.textContent.startsWith('▸')?'▾ '+el.textContent.substring(2):'▸ '+el.textContent.substring(2);
}

// Select room
function selectRoom(id){
curRoom=id;curDm=0;
render();
document.getElementById('emptyState').style.display='none';
document.getElementById('mainArea').innerHTML='<div style="text-align:center;padding:20px;color:var(--t3);font-size:12px">Loading...</div>';
var room=rooms.find(function(r){return r.id===id;});
if(room&&room.visibility!=='public'&&!room.my_role){
if(room.visibility==='password'){pwRoomId=id;document.getElementById('pwModal').classList.add('open');return;}
}
// Join if not member
if(room&&!room.my_role){
var fd=new FormData();fd.append('action','join_room');fd.append('room_id',id);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){loadRoom(id);};x.send(fd);
}else{loadRoom(id);}
}

function loadRoom(id){
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=messages&room_id='+id,true);
x.onload=function(){if(x.status!==200)return;try{renderRoom(id,JSON.parse(x.responseText));}catch(e){}};
x.send();
loadMembers(id);
}

function renderRoom(id,msgs){
var room=rooms.find(function(r){return r.id===id;});
var el=document.getElementById('mainArea');
var nm=room?room.name||'Room':'Room';
var desc=room?room.description||'':'';
var isOwner=room?room.owner_id===uid:false;
var col=room?room.color||'#008cff':'#64748b';
var vis=room&&room.visibility!=='public'?'<span class="lock-icon">'+(room.visibility==='password'?'🔒':'🔐')+'</span>':'<span class="hash">#</span>';
var actions='<div class="actions">';
actions+='<button onclick="document.getElementById(\'catSide\').classList.toggle(\'show\')">☰</button>';
if(isOwner)actions+='<button onclick="openEditModal('+id+')">⚙️</button>';
actions+='</div>';
var h='<div class="chat-hdr"><div class="nm">'+vis+' '+esc(nm)+' <span class="desc">'+esc(desc)+'</span></div>'+actions+'</div><div class="msgs" id="msgArea">';
if(!msgs||!msgs.length){h+='<div class="empty-chat" style="height:100%"><div class="ic">💬</div><div style="font-size:13px;color:var(--t2)">No messages yet</div><div style="font-size:10px">Be the first to say something</div></div>';
}else{msgs.forEach(function(m){
if(m.is_deleted){h+='<div class="msg sys">[deleted]</div>';return;}
var own=m.user_id===uid?'own':'ot';
var sd=own?'':'<div class="sd" onclick="openUserProfile('+m.user_id+')">'+esc(m.username)+'</div>';
var t='';if(m.created_at){var d=new Date(m.created_at);t=d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
h+='<div class="msg '+own+'">'+sd+'<div>'+esc(m.message)+'</div><div class="tm">'+t+'</div></div>';
});}
h+='</div><div class="inp-area"><div class="inp-row"><textarea id="msgInput" placeholder="Message #'+esc(nm)+'" rows="1" onkeydown="if(event.key===\'Enter\'&&!event.shiftKey){event.preventDefault();sendMsg()}"></textarea><button class="sb" onclick="sendMsg()">➤</button></div></div>';
el.innerHTML=h;
el.querySelector('.msgs').scrollTop=el.querySelector('.msgs').scrollHeight;
startPollRoom(id);
}

// Send room message
function sendMsg(){
var inp=document.getElementById('msgInput');var msg=inp.value.trim();if(!msg||!curRoom)return;
var fd=new FormData();fd.append('action','send');fd.append('room_id',curRoom);fd.append('message',msg);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){inp.value='';inp.style.height='auto';loadRoom(curRoom);};
x.send();
}

// Poll room messages
function startPollRoom(id){
if(poll)clearInterval(poll);
poll=setInterval(function(){if(!curRoom||curRoom!==id){clearInterval(poll);return;}
var x=new XMLHttpRequest();x.open('GET','/api/chatbox.php?action=messages&room_id='+id+'&limit=5',true);
x.onload=function(){if(x.status!==200)return;try{var msgs=JSON.parse(x.responseText);if(msgs&&msgs.length)appendMsgs(msgs);}catch(e){}};x.send();
},3000);
}

function appendMsgs(msgs){
var area=document.getElementById('msgArea');if(!area)return;
var last=area.lastElementChild;var lastId=0;if(last&&last.dataset)lastId=parseInt(last.dataset.id)||0;
var newOnes=msgs.filter(function(m){return m.id>lastId&&!m.is_deleted;});
if(!newOnes.length)return;
newOnes.forEach(function(m){
var own=m.user_id===uid?'own':'ot';var sd=own?'':'<div class="sd" onclick="openUserProfile('+m.user_id+')">'+esc(m.username)+'</div>';
var t='';if(m.created_at){var d=new Date(m.created_at);t=d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
area.innerHTML+='<div class="msg '+own+'" data-id="'+m.id+'">'+sd+'<div>'+esc(m.message)+'</div><div class="tm">'+t+'</div></div>';
});
area.scrollTop=area.scrollHeight;
}

// Load members
function loadMembers(id){
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=members&room_id='+id,true);
x.onload=function(){if(x.status!==200)return;try{renderMembers(JSON.parse(x.responseText));}catch(e){}};
x.send();
}

function renderMembers(list){
var el=document.getElementById('memberList');
document.getElementById('memberHdr').textContent='Members ('+(list.length||0)+')';
var h='';
var roles={owner:{lbl:'Owner',cls:'o'},admin:{lbl:'Admin',cls:'a'},moderator:{lbl:'Mod',cls:'m'},vip:{lbl:'VIP',cls:'v'},member:{lbl:'',cls:''}};
list.forEach(function(m){
var r=roles[m.role]||{lbl:m.role,cls:''};
var badge=r.lbl?'<span class="role-badge '+r.cls+'">'+r.lbl+'</span>':'';
h+='<div class="member-item"><span class="dot" style="background:var(--gr)"></span>'+esc(m.username)+' '+badge+'</div>';
});
el.innerHTML=h;
}

// Join password room
function joinRoom(id){
var pw=document.getElementById('joinPass').value;
var fd=new FormData();fd.append('action','join_room');fd.append('room_id',id);fd.append('password',pw);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){closePwModal();document.getElementById('joinPass').value='';init();selectRoom(id);};
x.send();
}
function closePwModal(){document.getElementById('pwModal').classList.remove('open');}

// Create room
function openCreateModal(){
var sel=document.getElementById('rCat');sel.innerHTML='';
cats.forEach(function(c){sel.innerHTML+='<option value="'+c.id+'">'+esc(c.name)+'</option>';});
document.getElementById('createModal').classList.add('open');
}
function closeCreateModal(){document.getElementById('createModal').classList.remove('open');}
document.getElementById('rVis').addEventListener('change',function(){document.getElementById('pwField').style.display=this.value==='password'?'':'none';});

function createRoom(){
var fd=new FormData();fd.append('action','create_room');
fd.append('name',document.getElementById('rName').value);
fd.append('description',document.getElementById('rDesc').value);
fd.append('category_id',document.getElementById('rCat').value);
fd.append('visibility',document.getElementById('rVis').value);
fd.append('password',document.getElementById('rPass').value);
fd.append('icon',document.getElementById('rIcon').value);
fd.append('color',document.getElementById('rColor').value);
fd.append('voice_enabled',document.getElementById('rVoice').checked?1:0);
fd.append('video_enabled',document.getElementById('rVideo').checked?1:0);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){closeCreateModal();init();};x.send();
}

// Edit room
var editRoomId=0;
function openEditModal(id){
editRoomId=id;
var room=rooms.find(function(r){return r.id===id;});
if(!room)return;
document.getElementById('rName').value=room.name||'';
document.getElementById('rDesc').value=room.description||'';
document.getElementById('rVis').value=room.visibility||'public';
document.getElementById('pwField').style.display=room.visibility==='password'?'':'none';
document.getElementById('rPass').value='';
document.getElementById('rIcon').value=room.icon||'';
document.getElementById('rColor').value=room.color||'#008cff';
document.getElementById('rVoice').checked=room.voice_enabled?true:false;
document.getElementById('rVideo').checked=room.video_enabled?true:false;
var sel=document.getElementById('rCat');sel.innerHTML='';
cats.forEach(function(c){sel.innerHTML+='<option value="'+c.id+'"'+(c.id===room.category_id?' selected':'')+'>'+esc(c.name)+'</option>';});
var btn=document.querySelector('#createModal .btn-p');
btn.textContent='💾 Save';
btn.onclick=saveRoom;
document.getElementById('createModal').classList.add('open');
}
function saveRoom(){
var fd=new FormData();fd.append('action','update_room');fd.append('room_id',editRoomId);
fd.append('name',document.getElementById('rName').value);
fd.append('description',document.getElementById('rDesc').value);
fd.append('visibility',document.getElementById('rVis').value);
fd.append('password',document.getElementById('rPass').value);
fd.append('icon',document.getElementById('rIcon').value);
fd.append('color',document.getElementById('rColor').value);
fd.append('voice_enabled',document.getElementById('rVoice').checked?1:0);
fd.append('video_enabled',document.getElementById('rVideo').checked?1:0);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){closeCreateModal();init();if(curRoom)selectRoom(curRoom);};
x.send();
}

// DM selection
function selectDm(id){curDm=id;curRoom=0;render();
document.getElementById('emptyState').style.display='none';
var x=new XMLHttpRequest();x.open('GET','/api/chatbox.php?action=dm_messages&conversation_id='+id,true);
x.onload=function(){if(x.status!==200)return;try{renderDm(id,JSON.parse(x.responseText));}catch(e){}};x.send();
}

function renderDm(id,msgs){
var el=document.getElementById('mainArea');
var h='<div class="dm-hdr">💬 Direct Message</div><div class="msgs" id="msgArea">';
if(!msgs||!msgs.length){h+='<div class="empty-chat" style="height:100%"><div class="ic">💬</div><div style="font-size:13px;color:var(--t2)">No messages yet</div></div>';
}else{msgs.forEach(function(m){
var own=m.user_id===uid?'own':'ot';var sd=own?'':'<div class="sd">'+esc(m.username)+'</div>';
var t='';if(m.created_at){var d=new Date(m.created_at);t=d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
h+='<div class="msg '+own+'">'+sd+'<div>'+esc(m.message)+'</div><div class="tm">'+t+'</div></div>';
});}
h+='</div><div class="inp-area"><div class="inp-row"><textarea id="msgInput" placeholder="Type a message..." rows="1" onkeydown="if(event.key===\'Enter\'&&!event.shiftKey){event.preventDefault();sendDm()}"></textarea><button class="sb" onclick="sendDm()">➤</button></div></div>';
el.innerHTML=h;el.querySelector('.msgs').scrollTop=el.querySelector('.msgs').scrollHeight;
startPollDm(id);
}

function sendDm(){
var inp=document.getElementById('msgInput');var msg=inp.value.trim();if(!msg||!curDm)return;
var fd=new FormData();fd.append('action','send_dm');fd.append('conversation_id',curDm);fd.append('message',msg);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){inp.value='';renderDm(curDm,[]);init();};x.send();
}

function startPollDm(id){
if(poll)clearInterval(poll);
poll=setInterval(function(){if(!curDm||curDm!==id){clearInterval(poll);return;}
var x=new XMLHttpRequest();x.open('GET','/api/chatbox.php?action=dm_messages&conversation_id='+id+'&limit=5',true);
x.onload=function(){if(x.status!==200)return;try{var msgs=JSON.parse(x.responseText);if(msgs&&msgs.length)appendDmMsgs(msgs);}catch(e){}};x.send();
},3000);
}

function appendDmMsgs(msgs){
var area=document.getElementById('msgArea');if(!area)return;
var last=area.lastElementChild;var lastId=0;if(last&&last.dataset)lastId=parseInt(last.dataset.id)||0;
msgs.filter(function(m){return m.id>lastId;}).forEach(function(m){
var own=m.user_id===uid?'own':'ot';var sd=own?'':'<div class="sd">'+esc(m.username)+'</div>';
var t='';if(m.created_at){var d=new Date(m.created_at);t=d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
area.innerHTML+='<div class="msg '+own+'" data-id="'+m.id+'">'+sd+'<div>'+esc(m.message)+'</div><div class="tm">'+t+'</div></div>';
});
area.scrollTop=area.scrollHeight;
}

// Open DM search
function openDmSearch(){
var user=prompt('Enter username to message:');
if(!user||user.length<2)return;
var x=new XMLHttpRequest();x.open('GET','/api/chatbox.php?action=search_users&q='+encodeURIComponent(user),true);
x.onload=function(){if(x.status!==200)return;try{var users=JSON.parse(x.responseText);
if(!users||!users.length){alert('User not found');return;}
createDm(users[0].id);}catch(e){}};x.send();
}

function createDm(otherId){
var fd=new FormData();fd.append('action','create_dm');fd.append('user_id',otherId);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){if(x.status===200){init();try{var r=JSON.parse(x.responseText);if(r.id)selectDm(r.id);}catch(e){}}};x.send();
}

function esc(t){var d=document.createElement('div');d.textContent=t||'';return d.innerHTML;}
init();
</script>
