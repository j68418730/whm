<style>
:root{--c-bg:#080f1c;--c-card:rgba(15,23,42,.5);--c-border:rgba(56,189,248,.08);--c-text:#e2e8f0;--c-t2:#94a3b8;--c-t3:#64748b;--c-blue:#008cff;--c-b2:rgba(0,140,255,.12);--c-own:rgba(0,140,255,.1);--c-red:#f87171;--c-green:#4ade80;--c-input:rgba(0,0,0,.35)}
*{box-sizing:border-box}
.chat-layout{display:grid;grid-template-columns:280px 1fr;gap:0;height:calc(100vh - 180px);min-height:500px;border:1px solid var(--c-border);border-radius:12px;overflow:hidden;background:var(--c-bg)}
.sidebar{display:flex;flex-direction:column;border-right:1px solid var(--c-border);background:rgba(0,0,0,.15)}
.sidebar-tabs{display:flex;border-bottom:1px solid var(--c-border);flex-shrink:0}
.sidebar-tab{padding:10px 14px;cursor:pointer;font-size:11px;font-weight:600;color:var(--c-t3);border-bottom:2px solid transparent;transition:.1s;flex:1;text-align:center}
.sidebar-tab:hover{color:var(--c-t2);background:var(--c-b2)}
.sidebar-tab.active{color:var(--c-blue);border-bottom-color:var(--c-blue)}
.sidebar-content{flex:1;overflow-y:auto;padding:0}
.sidebar-item{padding:10px 12px;cursor:pointer;border-bottom:1px solid var(--c-border);display:flex;align-items:center;gap:10px;transition:background .1s}
.sidebar-item:hover{background:var(--c-b2)}
.sidebar-item.active{background:var(--c-b2);border-left:3px solid var(--c-blue)}
.sidebar-item .av{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;flex-shrink:0}
.sidebar-item .info{flex:1;min-width:0}
.sidebar-item .info .nm{font-size:12px;font-weight:600;color:var(--c-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sidebar-item .info .pr{font-size:10px;color:var(--c-t3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px}
.sidebar-item .bdg{background:var(--c-blue);color:#fff;border-radius:10px;padding:1px 6px;font-size:9px;font-weight:700;min-width:18px;text-align:center}
.main-panel{display:flex;flex-direction:column}
.main-hdr{padding:10px 16px;border-bottom:1px solid var(--c-border);display:flex;justify-content:space-between;align-items:center;background:rgba(0,0,0,.1);flex-shrink:0}
.main-hdr .nm{font-size:14px;font-weight:700;color:var(--c-text);display:flex;align-items:center;gap:6px}
.main-hdr .nm .dot{width:8px;height:8px;border-radius:50%;display:inline-block}
.main-hdr .actions{display:flex;gap:4px}
.main-hdr .actions button{background:none;border:none;color:var(--c-t3);cursor:pointer;padding:4px 8px;border-radius:6px;font-size:11px;transition:.1s}
.main-hdr .actions button:hover{background:var(--c-b2);color:var(--c-text)}
.msgs{flex:1;overflow-y:auto;padding:12px 16px;display:flex;flex-direction:column;gap:5px}
.msg{max-width:78%;padding:8px 12px;border-radius:10px;font-size:12px;line-height:1.5;word-wrap:break-word;position:relative}
.msg.own{background:var(--c-own);align-self:flex-end;border-bottom-right-radius:4px}
.msg.ot{background:var(--c-card);align-self:flex-start;border-bottom-left-radius:4px}
.msg.sys{align-self:center;background:none;color:var(--c-t3);font-size:10px;font-style:italic;max-width:100%;text-align:center}
.msg .sd{font-size:10px;font-weight:600;color:var(--c-blue);margin-bottom:2px}
.msg .tm{font-size:9px;color:var(--c-t3);margin-top:2px;text-align:right}
.msg .ed{font-size:9px;color:var(--c-t3);font-style:italic}
.msg .rx{display:flex;gap:3px;margin-top:3px;flex-wrap:wrap}
.msg .rx span{background:var(--c-card);border:1px solid var(--c-border);border-radius:8px;padding:1px 5px;font-size:10px;cursor:pointer;transition:.1s}
.msg .rx span:hover{background:var(--c-b2)}
.msg .ab{display:none;position:absolute;top:-20px;right:0;background:var(--c-card);border:1px solid var(--c-border);border-radius:6px;padding:2px;gap:2px;z-index:5}
.msg:hover .ab{display:flex}
.msg .ab button{background:none;border:none;color:var(--c-t3);cursor:pointer;padding:2px 5px;font-size:10px;border-radius:4px}
.msg .ab button:hover{background:var(--c-b2);color:var(--c-text)}
.inp{padding:10px 16px;border-top:1px solid var(--c-border);background:rgba(0,0,0,.1);flex-shrink:0}
.inp-r{display:flex;gap:8px;align-items:flex-end}
.inp-r textarea{flex:1;padding:8px 12px;border-radius:10px;border:1px solid var(--c-border);background:var(--c-input);color:var(--c-text);font-size:13px;outline:none;resize:none;min-height:36px;max-height:120px;font-family:inherit}
.inp-r textarea:focus{border-color:var(--c-blue)}
.inp-r .sb{width:36px;height:36px;border-radius:50%;background:var(--c-blue);color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.inp-r .sb:disabled{opacity:.4;cursor:not-allowed}
.empty-msg{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--c-t3);gap:6px}
.empty-msg .ic{font-size:40px;opacity:.3}
.loading{text-align:center;padding:20px;color:var(--c-t3);font-size:12px}

/* Room create/edit sidebar panel */
.room-side{position:fixed;top:0;right:-360px;width:340px;height:100%;background:#0f172a;border-left:1px solid var(--c-border);z-index:200;transition:right .25s;overflow-y:auto;padding:20px}
.room-side.open{right:0}
.room-side h3{margin:0 0 14px;font-size:15px;color:var(--c-text);display:flex;align-items:center;gap:8px}
.room-side .fld{margin-bottom:10px}
.room-side .fld label{font-size:10px;color:var(--c-t3);display:block;margin-bottom:3px;font-weight:600;text-transform:uppercase;letter-spacing:.3px}
.room-side .fld input,.room-side .fld textarea,.room-side .fld select{width:100%;padding:8px 10px;border-radius:6px;border:1px solid var(--c-border);background:var(--c-input);color:var(--c-text);font-size:12px;outline:none;font-family:inherit}
.room-side .fld input:focus,.room-side .fld textarea:focus{border-color:var(--c-blue)}
.room-side .fld textarea{min-height:60px;resize:vertical}
.room-side .fld input[type=color]{height:40px;padding:2px;cursor:pointer}
.room-side .btn{width:100%;padding:9px;border-radius:8px;border:none;font-weight:600;font-size:12px;cursor:pointer;margin-top:6px}
.room-side .btn-p{background:var(--c-blue);color:#fff}
.room-side .btn-d{background:var(--c-b2);color:var(--c-text)}
.room-side .close-btn{position:absolute;top:14px;right:14px;background:none;border:none;color:var(--c-t3);cursor:pointer;font-size:18px}
@media(max-width:700px){.chat-layout{grid-template-columns:1fr;height:auto;min-height:auto}.sidebar{display:none}.sidebar.show{display:flex;position:fixed;inset:0;z-index:100;border:none}}
</style>

<div class="chat-layout" id="chatApp">
  <div class="sidebar" id="chatSide">
    <div class="sidebar-tabs">
      <div class="sidebar-tab active" onclick="switchTab(this,'conv')">Chats</div>
      <div class="sidebar-tab" onclick="switchTab(this,'rooms')">Rooms</div>
    </div>
    <div style="padding:8px;border-bottom:1px solid var(--c-border);flex-shrink:0">
      <input id="searchBox" placeholder="Search users or rooms..." style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid var(--c-border);background:var(--c-input);color:var(--c-text);font-size:11px;outline:none;box-sizing:border-box" oninput="doSearch(this.value)">
    </div>
    <div class="sidebar-content" id="sidebarContent"></div>
  </div>
  <div class="main-panel" id="mainPanel">
    <div class="main-hdr">
      <div class="nm" id="chatTitle"><span class="dot" style="background:var(--c-t3)"></span> Select a chat</div>
      <div class="actions">
        <button id="roomEditBtn" style="display:none" onclick="openRoomSettings()">⚙️</button>
        <button onclick="document.getElementById('chatSide').classList.toggle('show')">☰</button>
      </div>
    </div>
    <div class="msgs" id="msgArea">
      <div class="empty-msg"><div class="ic">💬</div><div class="nm" style="font-size:14px;color:var(--c-t2)">No conversation selected</div><div style="font-size:11px">Pick a chat or room from the sidebar</div></div>
    </div>
    <div class="inp" id="inputArea" style="display:none">
      <div class="inp-r"><textarea id="msgInput" placeholder="Type a message..." rows="1" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg()}"></textarea><button class="sb" id="sendBtn" onclick="sendMsg()">➤</button></div>
    </div>
  </div>
</div>

<!-- Room settings panel -->
<div class="room-side" id="roomSide">
  <button class="close-btn" onclick="closeRoomSettings()">✕</button>
  <div id="roomSideContent">
    <h3>✏️ Create Room</h3>
    <div class="fld"><label>Room Name</label><input id="rName" placeholder="e.g. General Chat"></div>
    <div class="fld"><label>Description</label><textarea id="rDesc" placeholder="What's this room about?"></textarea></div>
    <div class="fld"><label>Color</label><input id="rColor" type="color" value="#008cff"></div>
    <div class="fld"><label>Icon (emoji)</label><input id="rIcon" placeholder="e.g. 🎮" maxlength="2"></div>
    <button class="btn btn-p" onclick="createRoom()">Create Room</button>
  </div>
</div>

<script>
var curConv=0,poll=null,items=[],searchMode=false;
var usrId=<?=($user->id??0)?>;

function switchTab(el,tab){
document.querySelectorAll('.sidebar-tab').forEach(function(t){t.classList.remove('active')});
el.classList.add('active');
searchMode=false;
document.getElementById('searchBox').value='';
if(tab==='rooms')loadRooms();else loadConvs();
}

function loadConvs(){
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=conversations',true);
x.onload=function(){if(x.status!==200)return;try{renderList(JSON.parse(x.responseText));}catch(e){}};
x.send();
}

function loadRooms(){
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=rooms',true);
x.onload=function(){if(x.status!==200)return;try{renderRooms(JSON.parse(x.responseText));}catch(e){}};
x.send();
}

function renderList(convs){
items=convs;searchMode=false;
var el=document.getElementById('sidebarContent');
if(!convs||!convs.length){el.innerHTML='<div class="empty-msg" style="padding:30px"><div class="ic" style="font-size:32px">💬</div><div style="font-size:12px;color:var(--c-t2)">No conversations</div><div style="font-size:10px;color:var(--c-t3);cursor:pointer" onclick="openNewChat()">Start a new chat</div></div>';return;}
var h='<div style="padding:6px 10px;font-size:10px;color:var(--c-t3);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Direct Messages</div>';
convs.forEach(function(c){
if(c.type==='group'||c.type==='announcement')return;
var act=c.id===curConv?'active':'';
var nm=c.name||'User';
var last=(c.last_message||'').substring(0,35);
var t='';if(c.last_time){var d=new Date(c.last_time);t=d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
var un=c.unread>0?'<span class="bdg">'+(c.unread>99?'99+':c.unread)+'</span>':'';
h+='<div class="sidebar-item '+act+'" onclick="selectConv('+c.id+')"><div class="av" style="background:linear-gradient(135deg,var(--c-blue),#7c3aed)">👤</div><div class="info"><div class="nm">'+esc(nm)+'</div><div class="pr">'+esc(last)+'</div></div><div style="text-align:right;flex-shrink:0"><div style="font-size:9px;color:var(--c-t3)">'+t+'</div>'+un+'</div></div>';
});
h+='<div style="padding:6px 10px;margin-top:8px;font-size:10px;color:var(--c-t3);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Rooms</div>';
convs.forEach(function(c){
if(c.type!=='group'&&c.type!=='announcement')return;
var act=c.id===curConv?'active':'';
var nm=c.name||'Room';
var last=(c.last_message||'').substring(0,35);
var mem=c.member_count||0;
var hc=c.color||'#008cff';
h+='<div class="sidebar-item '+act+'" onclick="selectConv('+c.id+')"><div class="av" style="background:'+hc+'">#'+(c.icon||nm[0]||'R')+'</div><div class="info"><div class="nm">'+esc(nm)+'</div><div class="pr">'+esc(last)+'</div></div><div style="text-align:right;flex-shrink:0"><div style="font-size:9px;color:var(--c-t3)">'+mem+' members</div></div></div>';
});
h+='<div class="sidebar-item" style="color:var(--c-blue);font-size:12px" onclick="openRoomSettings()">➕ Create Room</div>';
el.innerHTML=h;
}

function renderRooms(rooms){
items=rooms;searchMode=true;
var el=document.getElementById('sidebarContent');
if(!rooms||!rooms.length){el.innerHTML='<div class="empty-msg" style="padding:30px"><div class="ic" style="font-size:32px">🏠</div><div style="font-size:12px;color:var(--c-t2)">No rooms yet</div><div style="font-size:10px;color:var(--c-blue);cursor:pointer" onclick="openRoomSettings()">Create the first room</div></div>';return;}
var h='';
rooms.forEach(function(r){
var act=r.id===curConv?'active':'';
var nm=r.name||'Room';
var desc=(r.description||'').substring(0,40);
var hc=r.color||'#008cff';
var mem=r.member_count||0;
h+='<div class="sidebar-item '+act+'" onclick="selectConv('+r.id+')"><div class="av" style="background:'+hc+'">#'+(r.icon||nm[0]||'R')+'</div><div class="info"><div class="nm">'+esc(nm)+'</div><div class="pr">'+esc(desc)+'</div></div><div style="text-align:right;flex-shrink:0"><div style="font-size:9px;color:var(--c-t3)">'+mem+' members</div></div></div>';
});
h+='<div class="sidebar-item" style="color:var(--c-blue);font-size:12px" onclick="openRoomSettings()">➕ Create Room</div>';
el.innerHTML=h;
}

function selectConv(id){
curConv=id;
document.getElementById('mainPanel').innerHTML='<div class="loading">Loading messages...</div>';
loadConvMessages(id);
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=conversations',true);
x.onload=function(){if(x.status!==200)return;try{renderList(JSON.parse(x.responseText));}catch(e){}};
x.send();
}

function loadConvMessages(convId){
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=messages&conversation_id='+convId,true);
x.onload=function(){
if(x.status!==200){document.getElementById('mainPanel').innerHTML='<div class="empty-msg"><div class="ic" style="font-size:32px">⚠️</div><div style="font-size:13px;color:var(--c-t2)">Error</div></div>';return;}
try{var msgs=JSON.parse(x.responseText);renderConv(convId,msgs);}catch(e){}
};
x.send();
}

function renderConv(convId,msgs){
var el=document.getElementById('mainPanel');
var conv=items.find(function(c){return c.id===convId;});
var nm=conv?conv.name||'Chat':'Chat';
var col=conv?conv.color||'#008cff':'#64748b';
var isOwner=conv?conv.created_by==usrId:false;
var editBtn=isOwner?'<button onclick="openRoomSettings()">⚙️</button>':'<button onclick="joinOrLeaveRoom('+convId+')">'+(conv&&conv.type!=='direct'?'🚪':'')+'</button>';
var h='<div class="main-hdr"><div class="nm"><span class="dot" style="background:'+col+'"></span>'+esc(nm)+'</div><div class="actions">'+editBtn+'<button onclick="document.getElementById(\'chatSide\').classList.toggle(\'show\')">☰</button></div></div><div class="msgs" id="msgArea">';
if(!msgs||!msgs.length){h+='<div class="empty-msg" style="padding:30px"><div class="ic">💬</div><div style="font-size:13px;color:var(--c-t2)">No messages yet</div><div style="font-size:10px;color:var(--c-t3)">Send the first message</div></div>';
}else{msgs.forEach(function(m){
if(m.message_type==='system'){h+='<div class="msg sys">'+esc(m.message)+'</div>';return;}
var own=m.user_id==usrId?'own':'ot';
var sd=own?'':'<div class="sd">'+esc(m.username)+'</div>';
var t='';if(m.created_at){var d=new Date(m.created_at);t=d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
var rx='';if(m.reactions&&m.reactions!='[]'){try{var rr=JSON.parse(m.reactions);if(rr&&rr.length){rx='<div class="rx">';rr.forEach(function(rc){rx+='<span onclick="toggleRx('+m.id+',\''+rc.emoji+'\')">'+rc.emoji+'</span>';});rx+='</div>';}}catch(e){}}
var ab='<div class="ab">';if(own)ab+='<button onclick="editMsg('+m.id+')">✏️</button><button onclick="delMsg('+m.id+')">🗑️</button>';ab+='<button onclick="toggleRx('+m.id+',\'👍\')">👍</button><button onclick="toggleRx('+m.id+',\'❤️\')">❤️</button><button onclick="toggleRx('+m.id+',\'😂\')">😂</button></div>';
h+='<div class="msg '+own+'">'+ab+sd+'<div>'+esc(m.message)+(m.edited_at?' <span class="ed">(edited)</span>':'')+'</div>'+rx+'<div class="tm">'+t+'</div></div>';
});}
h+='</div><div class="inp"><div class="inp-r"><textarea id="msgInput" placeholder="Type a message..." rows="1" onkeydown="if(event.key===\'Enter\'&&!event.shiftKey){event.preventDefault();sendMsg2()}"></textarea><button class="sb" id="sendBtn2" onclick="sendMsg2()">➤</button></div></div>';
el.innerHTML=h;
el.querySelector('.msgs').scrollTop=el.querySelector('.msgs').scrollHeight;
startPoll(convId);
}

// Polling
function startPoll(convId){
if(poll)clearInterval(poll);
poll=setInterval(function(){if(!curConv||curConv!==convId){clearInterval(poll);return;}
var x=new XMLHttpRequest();x.open('GET','/api/chatbox.php?action=messages&conversation_id='+convId+'&limit=10',true);
x.onload=function(){if(x.status!==200)return;try{var msgs=JSON.parse(x.responseText);if(msgs&&msgs.length)appendNewMsgs(msgs);}catch(e){}};x.send();
},3000);
}

function appendNewMsgs(msgs){
var area=document.getElementById('msgArea');if(!area)return;
var existing=area.querySelectorAll('.msg:not(.sys)');if(!existing.length){renderConv(curConv,msgs);return;}
var lastId=0;if(existing.length){var l=existing[existing.length-1];if(l.dataset&&l.dataset.msgId)lastId=parseInt(l.dataset.msgId);}
var newOnes=msgs.filter(function(m){return m.id>lastId;});
if(!newOnes.length)return;
newOnes.forEach(function(m){if(m.message_type==='system'){area.innerHTML+='<div class="msg sys">'+esc(m.message)+'</div>';return;}
var own=m.user_id==usrId?'own':'ot';var sd=own?'':'<div class="sd">'+esc(m.username)+'</div>';
var t='';if(m.created_at){var d=new Date(m.created_at);t=d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
area.innerHTML+='<div class="msg '+own+'" data-msg-id="'+m.id+'">'+sd+'<div>'+esc(m.message)+'</div><div class="tm">'+t+'</div></div>';});
area.scrollTop=area.scrollHeight;
}

// Send
function sendMsg2(){
var inp=document.getElementById('msgInput');var msg=inp.value.trim();if(!msg||!curConv)return;
document.getElementById('sendBtn2').disabled=true;
var fd=new FormData();fd.append('action','send');fd.append('conversation_id',curConv);fd.append('message',msg);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){inp.value='';inp.style.height='auto';document.getElementById('sendBtn2').disabled=false;loadConvMessages(curConv);};
x.send();
}

function sendMsg(){sendMsg2();}

// Reactions
function toggleRx(msgId,emoji){
var fd=new FormData();fd.append('action','react');fd.append('message_id',msgId);fd.append('emoji',emoji);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){if(curConv)loadConvMessages(curConv);};x.send();
}

// Edit/Delete
function editMsg(msgId){var m=prompt('Edit:');if(!m||!m.trim())return;
var fd=new FormData();fd.append('action','edit');fd.append('message_id',msgId);fd.append('message',m.trim());
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){if(curConv)loadConvMessages(curConv);};x.send();}
function delMsg(msgId){if(!confirm('Delete?'))return;
var fd=new FormData();fd.append('action','delete');fd.append('message_id',msgId);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){if(curConv)loadConvMessages(curConv);};x.send();}

// Room create/edit
var editingRoomId=0;
function openRoomSettings(){
var side=document.getElementById('roomSide');
var content=document.getElementById('roomSideContent');
var conv=items.find(function(c){return c.id===curConv&&(c.type==='group'||c.type==='announcement');});
if(conv&&conv.created_by==usrId){
editingRoomId=conv.id;
content.innerHTML='<h3>⚙️ Room Settings</h3><div class="fld"><label>Name</label><input id="rName" value="'+esc(conv.name||'')+'"></div><div class="fld"><label>Description</label><textarea id="rDesc">'+esc(conv.description||'')+'</textarea></div><div class="fld"><label>Color</label><input id="rColor" type="color" value="'+(conv.color||'#008cff')+'"></div><div class="fld"><label>Icon</label><input id="rIcon" value="'+(conv.icon||'')+'" maxlength="2"></div><button class="btn btn-p" onclick="saveRoom()">💾 Save</button><button class="btn btn-d" style="margin-top:6px;color:var(--c-red)" onclick="deleteRoom()">🗑️ Delete Room</button><button class="btn btn-d" onclick="closeRoomSettings()">Cancel</button>';
}else{
editingRoomId=0;
content.innerHTML='<h3>✏️ Create Room</h3><div class="fld"><label>Room Name</label><input id="rName" placeholder="e.g. General Chat"></div><div class="fld"><label>Description</label><textarea id="rDesc" placeholder="What\'s this room about?"></textarea></div><div class="fld"><label>Color</label><input id="rColor" type="color" value="#008cff"></div><div class="fld"><label>Icon (emoji)</label><input id="rIcon" placeholder="e.g. 🎮" maxlength="2"></div><button class="btn btn-p" onclick="createRoom()">Create Room</button>';
}
side.classList.add('open');
}
function closeRoomSettings(){document.getElementById('roomSide').classList.remove('open');}
function createRoom(){
var fd=new FormData();fd.append('action','create_room');fd.append('name',document.getElementById('rName').value);
fd.append('description',document.getElementById('rDesc').value);fd.append('color',document.getElementById('rColor').value);
fd.append('icon',document.getElementById('rIcon').value);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){closeRoomSettings();loadRooms();if(document.querySelector('.sidebar-tab.active'))switchTab(document.querySelector('.sidebar-tab.active'),'rooms');};
x.send();
}
function saveRoom(){
var fd=new FormData();fd.append('action','update_room');fd.append('room_id',editingRoomId);
fd.append('name',document.getElementById('rName').value);fd.append('description',document.getElementById('rDesc').value);
fd.append('color',document.getElementById('rColor').value);fd.append('icon',document.getElementById('rIcon').value);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){closeRoomSettings();if(curConv)selectConv(curConv);};
x.send();
}
function deleteRoom(){if(!confirm('Delete room and all messages?'))return;
var fd=new FormData();fd.append('action','delete_room');fd.append('room_id',editingRoomId);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){closeRoomSettings();curConv=0;loadRooms();if(document.querySelector('.sidebar-tab.active'))switchTab(document.querySelector('.sidebar-tab.active'),'rooms');};
x.send();
}

// Search
function doSearch(q){
if(q.length<2){document.querySelector('.sidebar-tab.active').click();return;}
var el=document.getElementById('sidebarContent');
var x=new XMLHttpRequest();x.open('GET','/api/chatbox.php?action=search_users&q='+encodeURIComponent(q),true);
x.onload=function(){if(x.status!==200)return;try{var users=JSON.parse(x.responseText);
if(!users||!users.length){el.innerHTML='<div class="empty-msg" style="padding:20px;font-size:12px">No users found</div>';return;}
var h='<div style="padding:6px 10px;font-size:10px;color:var(--c-t3);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Users</div>';
users.forEach(function(u){h+='<div class="sidebar-item" onclick="startDM('+u.id+')"><div class="av" style="background:linear-gradient(135deg,var(--c-blue),#7c3aed)">👤</div><div class="info"><div class="nm">'+esc(u.username)+'</div><div class="pr">'+esc(u.email)+'</div></div></div>';});
el.innerHTML=h;}catch(e){}};x.send();
}

function startDM(otherId){
var fd=new FormData();fd.append('action','create_conversation');fd.append('type','direct');fd.append('member_ids[]',otherId);
var x=new XMLHttpRequest();x.open('POST','/api/chatbox.php',true);
x.onload=function(){if(x.status===200){try{var r=JSON.parse(x.responseText);if(r.id)selectConv(r.id);}catch(e){}}searchMode=false;document.getElementById('searchBox').value='';};
x.send();
}

function openNewChat(){openRoomSettings();}

function esc(t){var d=document.createElement('div');d.textContent=t||'';return d.innerHTML;}
loadConvs();
</script>
