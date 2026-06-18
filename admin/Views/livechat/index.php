<style>
.chat-layout{display:grid;grid-template-columns:280px 1fr 280px;gap:0;min-height:600px;border-top:1px solid rgba(255,255,255,.06)}
.chat-sidebar{border-right:1px solid rgba(255,255,255,.06);padding:12px;overflow-y:auto;max-height:600px}
.chat-sidebar .item{padding:8px 10px;border-radius:6px;cursor:pointer;margin-bottom:2px;font-size:13px}
.chat-sidebar .item:hover{background:rgba(0,191,255,.06)}
.chat-sidebar .item.active{background:rgba(0,191,255,.1);border-left:3px solid var(--accent)}
.chat-main{display:flex;flex-direction:column;height:600px}
.chat-msgs{flex:1;overflow-y:auto;padding:12px}
.chat-msg{display:flex;gap:8px;margin-bottom:10px;align-items:start}
.chat-msg .bubble{display:inline-block;padding:8px 14px;border-radius:12px;font-size:14px;line-height:1.5;max-width:80%}
.chat-msg.visitor .bubble{background:rgba(255,255,255,.06);color:#e0e0e0}
.chat-msg.operator .bubble{background:rgba(0,140,255,.15);color:#cce5ff}
.chat-msg .meta{font-size:11px;color:var(--text-muted);margin-top:2px}
.chat-msg img.avatar{width:28px;height:28px;border-radius:50%;flex-shrink:0;margin-top:4px}
.chat-input-wrap{display:flex;gap:8px;padding:10px 16px;border-top:1px solid rgba(255,255,255,.06)}
.chat-input-wrap input{flex:1;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none;font-size:14px}
select{background:rgba(8,16,28,.9);border:1px solid rgba(255,255,255,.12);color:#e0e0e0;padding:6px 10px;border-radius:6px;outline:none;font-size:12px}
select option{background:#0a0f1a;color:#e0e0e0}
.badge{padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600}
.badge-waiting{background:rgba(250,204,21,.15);color:#facc15}
.badge-active{background:rgba(74,222,128,.15);color:#4ade80}
.badge-closed{background:rgba(100,116,139,.15);color:#64748b}
.tab-bar{display:flex;gap:0;border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:12px}
.tab-bar .tab{padding:10px 18px;font-size:13px;cursor:pointer;color:var(--text-muted);border-bottom:2px solid transparent;transition:all .2s}
.tab-bar .tab:hover{color:#e0e0e0}
.tab-bar .tab.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-content{display:none}
.tab-content.active{display:block}
.canned-item,.group-item{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border:1px solid rgba(255,255,255,.06);border-radius:6px;margin-bottom:4px;font-size:13px}
.canned-item:hover,.group-item:hover{background:rgba(255,255,255,.03)}
</style>

<div class="tab-bar">
<div class="tab active" onclick="switchTab('chats',this)">💬 Chats</div>
<div class="tab" onclick="switchTab('canned',this)">⚡ Canned</div>
<div class="tab" onclick="switchTab('groups',this)">👥 Groups</div>
<div class="tab" onclick="switchTab('remote',this)">🖥 Remote Support</div>
</div>

<!-- Tab: Chats -->
<div id="tab-chats" class="tab-content active">
<div class="card" style="padding:0">
<div class="stats-grid" style="margin:0;grid-template-columns:repeat(4,1fr);padding:12px;border-bottom:1px solid rgba(255,255,255,.06)">
<div><h3 style="font-size:11px;color:var(--text-muted);margin:0">Waiting</h3><div class="value" style="font-size:18px;color:#facc15"><?php echo count($waiting); ?></div></div>
<div><h3 style="font-size:11px;color:var(--text-muted);margin:0">Active</h3><div class="value" style="font-size:18px;color:#4ade80"><?php echo count($active); ?></div></div>
<div><h3 style="font-size:11px;color:var(--text-muted);margin:0">Visitors</h3><div class="value" style="font-size:18px;color:#60a5fa"><?php echo count($visitors); ?></div></div>
<div><h3 style="font-size:11px;color:var(--text-muted);margin:0">Canned</h3><div class="value" style="font-size:18px"><?php echo count($canned); ?></div></div>
</div>

<div class="chat-layout">
<div class="chat-sidebar">
<div style="font-size:11px;color:var(--text-muted);margin-bottom:6px">CHATS</div>
<div id="chatSidebarList">
<?php foreach ($sessions as $s): ?>
<div class="item" onclick="loadChat(<?php echo $s->id; ?>)" data-id="<?php echo $s->id; ?>">
<span class="badge badge-<?php echo $s->status; ?>"><?php echo $s->status; ?></span>
<strong style="font-size:13px"><?php echo htmlspecialchars($s->visitor_name ?: 'Visitor'); ?></strong>
<div style="font-size:11px;color:var(--text-muted)">#<?php echo $s->id; ?> · <?php echo htmlspecialchars($s->department); ?> <a href="/admin/livechat/delete/<?php echo $s->id; ?>" onclick="event.stopPropagation();return confirm('Delete chat #<?php echo $s->id; ?>?')" style="color:#ef4444;text-decoration:none;font-size:18px;font-weight:900;float:right;text-shadow:0 0 6px rgba(239,68,68,.5);line-height:1" title="Delete chat">🗑</a></div>
</div>
<?php endforeach; ?>
</div>
<?php if (empty($sessions)): ?><p style="color:var(--text-muted);font-size:13px;padding:20px;text-align:center">No chats</p><?php endif; ?>
</div>

<div class="chat-main">
<div class="chat-msgs" id="chatMessages">
<div style="text-align:center;color:var(--text-muted);padding:40px;font-size:14px">Select a chat</div>
</div>
<div class="chat-input-wrap" id="chatInputBar" style="display:none">
<input type="text" id="chatInput" placeholder="Type a message..." onkeydown="if(event.key==='Enter')sendMsg()">
<button class="btn btn-sm primary" onclick="sendMsg()">Send</button>
<button class="btn btn-sm secondary" onclick="document.getElementById('chatFileInput').click()">📎</button>
<input type="file" id="chatFileInput" style="display:none" onchange="uploadChatFile(this)">
<a href="#" onclick="event.preventDefault();remoteSupport(currentSession)" class="btn btn-sm secondary" style="text-decoration:none">🖥 Remote</a>
<button class="btn btn-sm danger" onclick="closeChat()" style="margin-left:auto">✕ Close</button>
</div>
</div>

<div class="chat-info" id="chatInfoPanel">
<div style="font-size:11px;color:var(--text-muted);margin-bottom:6px">VISITOR INFO</div>
<div id="visitorInfo">Select a chat</div>
<div style="font-size:11px;color:var(--text-muted);margin-top:16px;margin-bottom:4px">ACTIONS</div>
<select id="transferSelect" style="width:100%;margin-bottom:4px"><option value="">Transfer to...</option>
<?php foreach ($canned as $c): ?><option value="canned_<?php echo $c->id; ?>">⚡ <?php echo htmlspecialchars($c->title); ?></option><?php endforeach; ?>
</select>
<button class="btn btn-sm secondary" onclick="transferChat()" style="width:100%">Transfer</button>
</div>
</div>
</div>
</div>

<!-- Tab: Canned Responses -->
<div id="tab-canned" class="tab-content">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div class="card">
<h4 style="margin:0 0 12px;font-size:14px">Add Canned Response</h4>
<form method="POST" action="/admin/livechat/canned/store">
<input name="title" placeholder="Title" required style="width:100%;padding:8px;margin-bottom:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none">
<input name="category" placeholder="Category" value="General" style="width:100%;padding:8px;margin-bottom:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none">
<textarea name="message" rows="3" required placeholder="Message" style="width:100%;padding:8px;margin-bottom:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none"></textarea>
<button type="submit" class="btn primary btn-sm">Save</button>
</form>
</div>
<div class="card">
<h4 style="margin:0 0 12px;font-size:14px">Existing Responses (<?php echo count($canned); ?>)</h4>
<?php foreach ($canned as $c): ?>
<div class="canned-item">
<div><strong><?php echo htmlspecialchars($c->title); ?></strong><br><small style="color:var(--text-muted)"><?php echo htmlspecialchars($c->category); ?></small></div>
<div style="display:flex;gap:6px">
<button class="btn btn-sm secondary" onclick="copyCanned(<?php echo $c->id; ?>)">📋 Copy</button>
<a href="/admin/livechat/canned/delete/<?php echo $c->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete canned response?')">🗑</a>
</div>
</div>
<?php endforeach; ?>
<?php if (empty($canned)): ?><p style="color:var(--text-muted);font-size:13px">No canned responses yet.</p><?php endif; ?>
</div>
</div>
</div>

<!-- Tab: Groups -->
<div id="tab-groups" class="tab-content">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div class="card">
<h4 style="margin:0 0 12px;font-size:14px">Add Operator Group</h4>
<form method="POST" action="/admin/livechat/group/store">
<input name="name" placeholder="Group name" required style="width:100%;padding:8px;margin-bottom:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none">
<input name="department" placeholder="Department" style="width:100%;padding:8px;margin-bottom:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none">
<button type="submit" class="btn primary btn-sm">Save</button>
</form>
</div>
<div class="card">
<h4 style="margin:0 0 12px;font-size:14px">Existing Groups (<?php echo count($groups); ?>)</h4>
<?php foreach ($groups as $g): ?>
<div class="group-item">
<div><strong><?php echo htmlspecialchars($g->name); ?></strong><br><small style="color:var(--text-muted)"><?php echo htmlspecialchars($g->department ?? 'No department'); ?></small></div>
<a href="/admin/livechat/group/delete/<?php echo $g->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete group?')">🗑</a>
</div>
<?php endforeach; ?>
<?php if (empty($groups)): ?><p style="color:var(--text-muted);font-size:13px">No groups yet.</p><?php endif; ?>
</div>
</div>
</div>

<!-- Tab: Remote Support -->
<div id="tab-remote" class="tab-content">
<div class="card" style="max-width:500px;text-align:center;margin:20px auto">
<h4 style="margin:0 0 8px;font-size:15px">Generate Remote Support Link</h4>
<p style="color:var(--text-muted);font-size:13px;margin:0 0 16px">Share with a visitor to start a remote support session.</p>
<div id="remoteLinkBox" class="code-box" style="background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.2);border-radius:8px;padding:16px;font-family:monospace;font-size:16px;color:#4ade80;margin:16px 0;word-break:break-all">Click generate to create a link</div>
<button class="btn primary" onclick="generateRemoteLink()">🔗 Generate Link</button>
<button class="btn secondary" onclick="copyRemoteLink()">📋 Copy Link</button>
</div>
</div>

<script>
var currentSession = 0;
var lastMsgId = 0;
var pollTimer = null;
var visitorMap = {};
var remoteLink = '';

function switchTab(name, el) {
    document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(t) { t.classList.remove('active'); });
    if (el) el.classList.add('active');
    document.getElementById('tab-' + name).classList.add('active');
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

function loadChat(id) {
    currentSession = id;
    lastMsgId = 0;
    document.querySelectorAll('.chat-sidebar .item').forEach(function(el) { el.classList.remove('active'); });
    var el = document.querySelector('.chat-sidebar .item[data-id="' + id + '"]');
    if (el) el.classList.add('active');
    document.getElementById('chatInputBar').style.display = 'flex';
    document.getElementById('chatMessages').innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:20px">Loading...</div>';
    if (pollTimer) clearInterval(pollTimer);
    fetchMessages();
    pollTimer = setInterval(fetchMessages, 2000);
}

function fetchMessages() {
    if (!currentSession) return;
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/livechat/messages/' + currentSession + '?since=' + lastMsgId, true);
    x.onload = function() {
        try {
            var msgs = JSON.parse(x.responseText);
            if (msgs.error) return;
            var out = document.getElementById('chatMessages');
            if (msgs.length > 0) {
                var added = false;
                msgs.forEach(function(m) {
                    if (m.id > lastMsgId) { lastMsgId = m.id; added = true; } else return;
                    var cls = m.sender_type === 'operator' ? 'operator' : 'visitor';
                    var avatar = cls === 'operator' ? '/theme/assets/img/avatars/owner.png' : '/theme/assets/img/avatars/vistor.png';
                    out.innerHTML += '<div class="chat-msg ' + cls + '"><img src="' + avatar + '" class="avatar"><div><div class="bubble">' + (m.message||'').replace(/</g,'&lt;') + '</div><div class="meta">' + (m.sender_name||cls) + ' · ' + (m.created_at||'') + '</div></div></div>';
                });
                if (added) out.scrollTop = out.scrollHeight;
            }
        } catch(e) {}
    };
    x.send();
}

function sendMsg() {
    var input = document.getElementById('chatInput');
    var msg = input.value.trim();
    if (!msg || !currentSession) return;
    var x = new XMLHttpRequest();
    x.open('POST', '/admin/livechat/send', true);
    x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    x.onload = function() { input.value = ''; fetchMessages(); };
    x.send('session_id=' + currentSession + '&message=' + encodeURIComponent(msg));
}

function closeChat() {
    if (!currentSession || !confirm('Close this chat?')) return;
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/livechat/close/' + currentSession, true);
    x.onload = function() { location.reload(); };
    x.send();
}

function transferChat() {
    var sel = document.getElementById('transferSelect');
    if (!sel.value || !currentSession) return;
    var x = new XMLHttpRequest();
    x.open('POST', '/admin/livechat/transfer/' + currentSession, true);
    x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    x.onload = function() { alert('Chat transferred'); location.reload(); };
    x.send('group_id=' + encodeURIComponent(sel.value));
}

function remoteSupport(chatId) {
    if (!chatId) return;
    var x = new XMLHttpRequest();
    x.open('GET', '/remote_support.php?chat_id=' + chatId, true);
    x.onload = function() { fetchMessages(); alert('Remote support link sent to chat!'); };
    x.send();
}

function uploadChatFile(input) {
    if (!currentSession || !input.files[0]) return;
    var fd = new FormData();
    fd.append('file', input.files[0]);
    fd.append('session_id', currentSession);
    var x = new XMLHttpRequest();
    x.open('POST', '/upload_chat.php', true);
    x.onload = function() { fetchMessages(); };
    x.send(fd);
}

function generateRemoteLink() {
    var x = new XMLHttpRequest();
    x.open('GET', '/remote_support.php?json=1', true);
    x.onload = function() {
        try { var d = JSON.parse(x.responseText); remoteLink = d.url; document.getElementById('remoteLinkBox').textContent = d.url; } catch(e) {}
    };
    x.send();
}

function copyRemoteLink() {
    if (remoteLink) navigator.clipboard.writeText(remoteLink);
}
</script>
