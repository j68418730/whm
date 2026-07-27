<style>
:root{--chat-bg:#080f1c;--chat-card:rgba(15,23,42,.5);--chat-border:rgba(56,189,248,.08);--chat-text:#e2e8f0;--chat-text2:#94a3b8;--chat-text3:#64748b;--chat-accent:#008cff;--chat-accent2:rgba(0,140,255,.12);--chat-own:rgba(0,140,255,.1);--chat-danger:#f87171;--chat-success:#4ade80;--chat-input:rgba(0,0,0,.35)}
.chatbox-layout{display:grid;grid-template-columns:300px 1fr;gap:0;height:calc(100vh - 180px);min-height:500px;border:1px solid var(--chat-border);border-radius:12px;overflow:hidden;background:var(--chat-bg)}
.chat-sidebar{border-right:1px solid var(--chat-border);display:flex;flex-direction:column;background:rgba(0,0,0,.15)}
.chat-sidebar .search-box{padding:10px;border-bottom:1px solid var(--chat-border)}
.chat-sidebar .search-box input{width:100%;padding:7px 10px;border-radius:8px;border:1px solid var(--chat-border);background:var(--chat-input);color:var(--chat-text);font-size:12px;outline:none;box-sizing:border-box}
.chat-sidebar .search-box input:focus{border-color:var(--chat-accent)}
.chat-conv-list{flex:1;overflow-y:auto;padding:0}
.chat-conv-item{padding:10px 12px;cursor:pointer;border-bottom:1px solid var(--chat-border);transition:background .1s;display:flex;align-items:center;gap:10px}
.chat-conv-item:hover{background:var(--chat-accent2)}
.chat-conv-item.active{background:var(--chat-accent2);border-left:3px solid var(--chat-accent)}
.chat-conv-item .avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--chat-accent),#7c3aed);display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;flex-shrink:0}
.chat-conv-item .info{flex:1;min-width:0}
.chat-conv-item .info .name{font-size:12px;font-weight:600;color:var(--chat-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.chat-conv-item .info .preview{font-size:10px;color:var(--chat-text3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px}
.chat-conv-item .unread-badge{background:var(--chat-accent);color:#fff;border-radius:10px;padding:1px 6px;font-size:9px;font-weight:700;min-width:18px;text-align:center}
.chat-main{display:flex;flex-direction:column}
.chat-header-bar{padding:10px 16px;border-bottom:1px solid var(--chat-border);display:flex;justify-content:space-between;align-items:center;background:rgba(0,0,0,.1)}
.chat-header-bar .name{font-size:14px;font-weight:700;color:var(--chat-text)}
.chat-header-bar .actions{display:flex;gap:6px}
.chat-header-bar .actions button{background:none;border:none;color:var(--chat-text3);cursor:pointer;padding:4px 8px;border-radius:6px;font-size:12px;transition:.1s}
.chat-header-bar .actions button:hover{background:var(--chat-accent2);color:var(--chat-text)}
.chat-messages{flex:1;overflow-y:auto;padding:12px 16px;display:flex;flex-direction:column;gap:6px}
.chat-msg{max-width:75%;padding:8px 12px;border-radius:10px;font-size:12px;line-height:1.5;word-wrap:break-word;position:relative}
.chat-msg.own{background:var(--chat-own);align-self:flex-end;border-bottom-right-radius:4px}
.chat-msg.other{background:var(--chat-card);align-self:flex-start;border-bottom-left-radius:4px}
.chat-msg.system{align-self:center;background:none;color:var(--chat-text3);font-size:10px;font-style:italic;max-width:100%;text-align:center}
.chat-msg .sender{font-size:10px;font-weight:600;color:var(--chat-accent);margin-bottom:2px}
.chat-msg .time{font-size:9px;color:var(--chat-text3);margin-top:2px;text-align:right}
.chat-msg .edited{font-size:9px;color:var(--chat-text3);font-style:italic}
.chat-msg .reactions{display:flex;gap:3px;margin-top:3px;flex-wrap:wrap}
.chat-msg .reactions span{background:var(--chat-card);border:1px solid var(--chat-border);border-radius:8px;padding:1px 5px;font-size:10px;cursor:pointer;transition:.1s}
.chat-msg .reactions span:hover{background:var(--chat-accent2)}
.chat-msg .actions-bar{display:none;position:absolute;top:-20px;right:0;background:var(--chat-card);border:1px solid var(--chat-border);border-radius:6px;padding:2px;gap:2px}
.chat-msg:hover .actions-bar{display:flex}
.chat-msg .actions-bar button{background:none;border:none;color:var(--chat-text3);cursor:pointer;padding:2px 5px;font-size:10px;border-radius:4px}
.chat-msg .actions-bar button:hover{background:var(--chat-accent2);color:var(--chat-text)}
.chat-input-area{padding:10px 16px;border-top:1px solid var(--chat-border);background:rgba(0,0,0,.1)}
.chat-input-row{display:flex;gap:8px;align-items:flex-end}
.chat-input-row textarea{flex:1;padding:8px 12px;border-radius:10px;border:1px solid var(--chat-border);background:var(--chat-input);color:var(--chat-text);font-size:13px;outline:none;resize:none;min-height:36px;max-height:120px;font-family:inherit;box-sizing:border-box}
.chat-input-row textarea:focus{border-color:var(--chat-accent)}
.chat-input-row .send-btn{width:36px;height:36px;border-radius:50%;background:var(--chat-accent);color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;transition:.15s}
.chat-input-row .send-btn:hover{transform:scale(1.05)}
.chat-input-row .send-btn:disabled{opacity:.4;cursor:not-allowed}
.chat-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--chat-text3);gap:8px}
.chat-empty .icon{font-size:48px;opacity:.3}
.chat-empty .text{font-size:13px}
.chat-empty .sub{font-size:11px}
.chat-loading{text-align:center;padding:20px;color:var(--chat-text3);font-size:12px}
.chat-new-btn{padding:8px 12px;margin:8px;border-radius:8px;background:var(--chat-accent2);color:var(--chat-accent);border:1px solid var(--chat-border);cursor:pointer;font-size:11px;font-weight:600;transition:.1s}
.chat-new-btn:hover{background:rgba(0,140,255,.2)}
.new-chat-modal{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.7);z-index:1000;display:none;align-items:center;justify-content:center}
.new-chat-modal.open{display:flex}
.new-chat-modal .modal-card{background:#0f172a;border:1px solid var(--chat-border);border-radius:16px;padding:24px;width:90%;max-width:400px;max-height:80vh;overflow-y:auto}
.new-chat-modal .modal-card h3{margin:0 0 12px;font-size:15px;color:var(--chat-text)}
.new-chat-modal .modal-card input{width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--chat-border);background:var(--chat-input);color:var(--chat-text);font-size:12px;outline:none;margin-bottom:8px;box-sizing:border-box}
.new-chat-modal .modal-card .user-result{padding:8px 10px;cursor:pointer;border-radius:6px;font-size:12px;color:var(--chat-text2);display:flex;align-items:center;gap:8px}
.new-chat-modal .modal-card .user-result:hover{background:var(--chat-accent2);color:var(--chat-text)}
.new-chat-modal .modal-card .selected-users{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:8px}
.new-chat-modal .modal-card .selected-users span{background:var(--chat-accent2);color:var(--chat-accent);padding:3px 8px;border-radius:4px;font-size:10px;display:flex;align-items:center;gap:4px}
.new-chat-modal .modal-card .selected-users span button{background:none;border:none;color:var(--chat-danger);cursor:pointer;font-size:12px;padding:0;margin-left:2px}
.new-chat-modal .modal-card .create-btn{width:100%;padding:10px;border-radius:8px;background:var(--chat-accent);color:#fff;border:none;font-weight:600;cursor:pointer;font-size:13px}
@media(max-width:700px){.chatbox-layout{grid-template-columns:1fr;height:auto;min-height:auto}.chat-sidebar{display:none}.chat-sidebar.show{display:flex;position:fixed;inset:0;z-index:100;border:none}}
</style>

<div class="chatbox-layout" id="chatboxApp">
  <!-- Sidebar -->
  <div class="chat-sidebar" id="chatSidebar">
    <div class="search-box">
      <input id="searchUsers" placeholder="Search users..." oninput="searchUsers(this.value)">
    </div>
    <button class="chat-new-btn" onclick="openNewChat()">✏️ New Conversation</button>
    <div class="chat-conv-list" id="convList">
      <div class="chat-loading">Loading conversations...</div>
    </div>
  </div>
  
  <!-- Main -->
  <div class="chat-main">
    <div class="chat-header-bar">
      <div class="name" id="chatTitle">Select a conversation</div>
      <div class="actions">
        <button onclick="toggleSidebar()" id="sidebarToggle" style="display:none">☰</button>
      </div>
    </div>
    <div class="chat-messages" id="msgArea">
      <div class="chat-empty">
        <div class="icon">💬</div>
        <div class="text">No conversation selected</div>
        <div class="sub">Choose a chat from the sidebar or start a new one</div>
      </div>
    </div>
    <div class="chat-input-area" id="inputArea" style="display:none">
      <div class="chat-input-row">
        <textarea id="msgInput" placeholder="Type a message..." rows="1" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg()}"></textarea>
        <button class="send-btn" id="sendBtn" onclick="sendMsg()">➤</button>
      </div>
    </div>
  </div>
</div>

<!-- New Chat Modal -->
<div class="new-chat-modal" id="newChatModal">
  <div class="modal-card">
    <h3>✏️ New Message</h3>
    <input id="newChatSearch" placeholder="Search users..." oninput="searchNewUsers(this.value)">
    <div id="newChatResults"></div>
    <div class="selected-users" id="newChatSelected"></div>
    <button class="create-btn" onclick="createConversation()">Start Chat</button>
    <button style="width:100%;padding:8px;margin-top:6px;background:none;border:1px solid var(--chat-border);border-radius:8px;color:var(--chat-text3);cursor:pointer;font-size:12px" onclick="closeNewChat()">Cancel</button>
  </div>
</div>

<script>
var currentConv = 0, pollTimer = null, convs = [], selectedUsers = [];

function toggleSidebar(){document.getElementById('chatSidebar').classList.toggle('show');}

// Load conversations
function loadConvs(){
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=conversations',true);
x.onload=function(){
if(x.status!==200)return;
try{convs=JSON.parse(x.responseText);renderConvs();}catch(e){}
};
x.send();
}

function renderConvs(){
var el=document.getElementById('convList');
if(!convs||!convs.length){el.innerHTML='<div style="text-align:center;padding:20px;color:#64748b;font-size:12px">No conversations yet</div>';return;}
var h='';
convs.forEach(function(c){
var active=c.id===currentConv?'active':'';
var name=c.name||'User '+(c.id);
var last=(c.last_message||'').substring(0,40);
var time='';if(c.last_time){var d=new Date(c.last_time);var n=d.getHours()%12||12;time=n+':'+(d.getMinutes()<10?'0':'')+d.getMinutes()+' '+(d.getHours()<12?'AM':'PM');}
var unread=c.unread>0?'<span class="unread-badge">'+(c.unread>99?'99+':c.unread)+'</span>':'';
h+='<div class="chat-conv-item '+active+'" onclick="selectConv('+c.id+')"><div class="avatar">'+(c.type==='group'?'👥':'👤')+'</div><div class="info"><div class="name">'+escHtml(name)+'</div><div class="preview">'+escHtml(last)+'</div></div><div style="text-align:right;flex-shrink:0"><div style="font-size:9px;color:#64748b">'+time+'</div>'+unread+'</div></div>';
});
el.innerHTML=h;
}

// Select conversation
function selectConv(id){
currentConv=id;
var el=document.getElementById('msgArea');
el.innerHTML='<div class="chat-loading">Loading messages...</div>';
document.getElementById('inputArea').style.display='flex';
document.getElementById('sidebarToggle').style.display='';
if(window.innerWidth<700)document.getElementById('chatSidebar').classList.remove('show');
loadMessages(id);
renderConvs();

var c=convs.find(function(x){return x.id===id;});
document.getElementById('chatTitle').textContent=c?c.name||'Chat':'Chat';
}

// Load messages
function loadMessages(convId){
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=messages&conversation_id='+convId,true);
x.onload=function(){
if(x.status!==200){document.getElementById('msgArea').innerHTML='<div class="chat-empty"><div class="icon" style="font-size:32px">⚠️</div><div class="text">Error loading messages</div></div>';return;}
try{
var msgs=JSON.parse(x.responseText);
renderMessages(msgs);
startPolling(convId);
}catch(e){}
};
x.send();
}

function renderMessages(msgs){
var el=document.getElementById('msgArea');
if(!msgs||!msgs.length){el.innerHTML='<div class="chat-empty"><div class="icon">💬</div><div class="text">No messages yet</div><div class="sub">Send a message to start the conversation</div></div>';return;}
var h='';
msgs.forEach(function(m){
if(m.message_type==='system'){h+='<div class="chat-msg system">'+escHtml(m.message)+'</div>';return;}
var isOwn=m.user_id==<?=($user->id??0)?>?'own':'other';
var showSender=!isOwn;
var time='';if(m.created_at){var d=new Date(m.created_at);time=d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
var reactions='';if(m.reactions&&m.reactions!='[]'){try{var r=JSON.parse(m.reactions);if(r&&r.length){reactions='<div class="reactions">';r.forEach(function(rc){reactions+='<span onclick="toggleReaction('+m.id+',\''+rc.emoji+'\')">'+rc.emoji+'</span>';});reactions+='</div>';}}catch(e){}}
var actions='<div class="actions-bar">';
if(isOwn)actions+='<button onclick="editMsg('+m.id+')">✏️</button><button onclick="deleteMsg('+m.id+')">🗑️</button>';
actions+='<button onclick="toggleReaction('+m.id+',\'👍\')">👍</button><button onclick="toggleReaction('+m.id+',\'❤️\')">❤️</button><button onclick="toggleReaction('+m.id+',\'😂\')">😂</button></div>';
h+='<div class="chat-msg '+isOwn+'" data-id="'+m.id+'">'+actions+(showSender?'<div class="sender">'+escHtml(m.username)+'</div>':'')+'<div>'+escHtml(m.message)+(m.edited_at?' <span class="edited">(edited)</span>':'')+'</div>'+reactions+'<div class="time">'+time+'</div></div>';
});
el.innerHTML=h;
el.scrollTop=el.scrollHeight;
}

// Poll new messages
function startPolling(convId){
if(pollTimer)clearInterval(pollTimer);
pollTimer=setInterval(function(){
if(!currentConv||currentConv!==convId){clearInterval(pollTimer);return;}
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=messages&conversation_id='+convId+'&limit=10',true);
x.onload=function(){
if(x.status!==200)return;
try{
var msgs=JSON.parse(x.responseText);
if(msgs&&msgs.length>0)renderMessages(msgs);
}catch(e){}
};
x.send();
},3000);
}

// Send message
function sendMsg(){
var input=document.getElementById('msgInput');
var msg=input.value.trim();
if(!msg||!currentConv)return;
document.getElementById('sendBtn').disabled=true;
var fd=new FormData();
fd.append('action','send');
fd.append('conversation_id',currentConv);
fd.append('message',msg);
var x=new XMLHttpRequest();
x.open('POST','/api/chatbox.php',true);
x.onload=function(){
input.value='';
input.style.height='auto';
document.getElementById('sendBtn').disabled=false;
if(x.status===200){loadMessages(currentConv);loadConvs();}
};
x.send(fd);
}

// Search users
function searchUsers(q){
document.getElementById('searchUsers').value=q;
if(q.length<2)return;
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=search_users&q='+encodeURIComponent(q),true);
x.onload=function(){
if(x.status!==200)return;
try{var users=JSON.parse(x.responseText);showUserResults(users);}catch(e){}
};
x.send();
}

function showUserResults(users){
var el=document.getElementById('convList');
if(!users||!users.length){renderConvs();return;}
var h='';
users.forEach(function(u){h+='<div class="chat-conv-item" onclick="startDirectChat('+u.id+')"><div class="avatar">👤</div><div class="info"><div class="name">'+escHtml(u.username)+'</div><div class="preview">'+escHtml(u.email)+'</div></div></div>';});
el.innerHTML=h;
}

function startDirectChat(otherId){
var fd=new FormData();
fd.append('action','create_conversation');
fd.append('type','direct');
fd.append('member_ids[]',otherId);
var x=new XMLHttpRequest();
x.open('POST','/api/chatbox.php',true);
x.onload=function(){
if(x.status===200){loadConvs();try{var r=JSON.parse(x.responseText);if(r.id)selectConv(r.id);}catch(e){}}
document.getElementById('searchUsers').value='';
};
x.send();
}

// New chat
function openNewChat(){document.getElementById('newChatModal').classList.add('open');document.getElementById('newChatSearch').focus();}
function closeNewChat(){document.getElementById('newChatModal').classList.remove('open');document.getElementById('newChatResults').innerHTML='';selectedUsers=[];document.getElementById('newChatSelected').innerHTML='';}
function searchNewUsers(q){
if(q.length<2){document.getElementById('newChatResults').innerHTML='';return;}
var x=new XMLHttpRequest();
x.open('GET','/api/chatbox.php?action=search_users&q='+encodeURIComponent(q),true);
x.onload=function(){
if(x.status!==200)return;
try{var users=JSON.parse(x.responseText);renderNewUserResults(users);}catch(e){}
};
x.send();
}
function renderNewUserResults(users){
var el=document.getElementById('newChatResults');
var h='';
users.forEach(function(u){
if(selectedUsers.find(function(s){return s.id===u.id;}))return;
h+='<div class="user-result" onclick="addNewChatUser('+u.id+',\''+escHtml(u.username)+'\')">👤 '+escHtml(u.username)+' <span style="color:#64748b">('+escHtml(u.email)+')</span></div>';
});
el.innerHTML=h;
}
function addNewChatUser(id,username){
if(selectedUsers.find(function(s){return s.id===id;}))return;
selectedUsers.push({id:id,username:username});
renderSelectedUsers();
document.getElementById('newChatSearch').value='';
document.getElementById('newChatResults').innerHTML='';
}
function renderSelectedUsers(){
var el=document.getElementById('newChatSelected');
var h='';
selectedUsers.forEach(function(u){h+='<span>'+escHtml(u.username)+' <button onclick="removeNewChatUser('+u.id+')">✕</button></span>';});
el.innerHTML=h;
}
function removeNewChatUser(id){selectedUsers=selectedUsers.filter(function(s){return s.id!==id;});renderSelectedUsers();}
function createConversation(){
var ids=selectedUsers.map(function(u){return u.id;});
if(ids.length===0)return;
var fd=new FormData();
fd.append('action','create_conversation');
fd.append('type',ids.length===1?'direct':'group');
fd.append('member_ids',JSON.stringify(ids));
if(ids.length>1)fd.append('name',prompt('Group name:')||'Group Chat');
var x=new XMLHttpRequest();
x.open('POST','/api/chatbox.php',true);
x.onload=function(){
if(x.status===200){loadConvs();try{var r=JSON.parse(x.responseText);if(r.id)selectConv(r.id);}catch(e){}}
closeNewChat();
};
x.send();
}

// Reactions
function toggleReaction(msgId,emoji){
var fd=new FormData();
fd.append('action','react');
fd.append('message_id',msgId);
fd.append('emoji',emoji);
var x=new XMLHttpRequest();
x.open('POST','/api/chatbox.php',true);
x.onload=function(){if(currentConv)loadMessages(currentConv);};
x.send();
}

// Edit message
function editMsg(msgId){
var newMsg=prompt('Edit message:');
if(!newMsg||!newMsg.trim())return;
var fd=new FormData();
fd.append('action','edit');
fd.append('message_id',msgId);
fd.append('message',newMsg.trim());
var x=new XMLHttpRequest();
x.open('POST','/api/chatbox.php',true);
x.onload=function(){if(currentConv)loadMessages(currentConv);};
x.send();
}

// Delete message
function deleteMsg(msgId){
if(!confirm('Delete this message?'))return;
var fd=new FormData();
fd.append('action','delete');
fd.append('message_id',msgId);
var x=new XMLHttpRequest();
x.open('POST','/api/chatbox.php',true);
x.onload=function(){if(currentConv)loadMessages(currentConv);};
x.send();
}

function escHtml(t){var d=document.createElement('div');d.textContent=t||'';return d.innerHTML;}

// Init
loadConvs();
</script>
