<style>
.chat-layout{display:grid;grid-template-columns:280px 1fr 300px;gap:0;min-height:600px}
.chat-sidebar{border-right:1px solid rgba(255,255,255,.06);padding:12px;overflow-y:auto;max-height:600px}
.chat-sidebar .item{padding:10px 12px;border-radius:8px;cursor:pointer;margin-bottom:4px;transition:.15s}
.chat-sidebar .item:hover{background:rgba(0,191,255,.06)}
.chat-sidebar .item.active{background:rgba(0,191,255,.1);border-left:3px solid var(--accent)}
.chat-sidebar .item .status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px}
.chat-main{display:flex;flex-direction:column;height:600px}
.chat-msgs{flex:1;overflow-y:auto;padding:16px}
.chat-msg{margin-bottom:12px;max-width:80%}
.chat-msg.operator{margin-left:auto}
.chat-msg .bubble{padding:10px 14px;border-radius:12px;font-size:14px;line-height:1.5}
.chat-msg.visitor .bubble{background:rgba(255,255,255,.06);color:#e0e0e0}
.chat-msg.operator .bubble{background:rgba(0,140,255,.15);color:#cce5ff}
.chat-msg .meta{font-size:11px;color:var(--text-muted);margin-top:4px}
.chat-input-wrap{display:flex;gap:8px;padding:12px 16px;border-top:1px solid rgba(255,255,255,.06)}
.chat-input-wrap input{flex:1;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none}
.chat-info{padding:16px;border-left:1px solid rgba(255,255,255,.06);overflow-y:auto;max-height:600px}
.chat-canned{padding:8px;border-radius:6px;cursor:pointer;font-size:13px;margin-bottom:4px}
.chat-canned:hover{background:rgba(0,191,255,.06)}
select{background:rgba(8,16,28,.9);border:1px solid rgba(255,255,255,.12);color:#e0e0e0;padding:8px 12px;border-radius:6px;outline:none}
select option{background:#0a0f1a;color:#e0e0e0}
</style>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('cannedForm').classList.toggle('hidden')">+ Canned Response</a>
<a class="btn secondary" onclick="document.getElementById('groupForm').classList.toggle('hidden')">+ Operator Group</a>
</div>

<div id="cannedForm" class="card hidden" style="max-width:500px;margin-bottom:12px">
<form method="POST" action="/admin/livechat/canned/store">
<div class="form-group"><label>Title</label><input name="title" required></div>
<div class="form-group"><label>Message</label><textarea name="message" rows="3" required></textarea></div>
<div class="form-group"><label>Category</label><input name="category" value="General"></div>
<button type="submit" class="btn primary">Save</button>
</form></div>

<div id="groupForm" class="card hidden" style="max-width:500px;margin-bottom:12px">
<form method="POST" action="/admin/livechat/group/store">
<div class="form-group"><label>Group Name</label><input name="name" required></div>
<div class="form-group"><label>Department</label><input name="department" placeholder="Billing, Support, Sales"></div>
<button type="submit" class="btn primary">Save</button>
</form></div>

<div class="card" style="padding:0">
<div class="stats-grid" style="margin:0;grid-template-columns:repeat(4,1fr);padding:16px;border-bottom:1px solid rgba(255,255,255,.06)">
<div><h3 style="font-size:12px;color:var(--text-muted)">Waiting</h3><div class="value" style="font-size:20px;color:#facc15"><?php echo count($waiting); ?></div></div>
<div><h3 style="font-size:12px;color:var(--text-muted)">Active</h3><div class="value" style="font-size:20px;color:#4ade80"><?php echo count($active); ?></div></div>
<div><h3 style="font-size:12px;color:var(--text-muted)">Visitors</h3><div class="value" style="font-size:20px;color:#60a5fa"><?php echo count($visitors); ?></div></div>
<div><h3 style="font-size:12px;color:var(--text-muted)">Canned</h3><div class="value" style="font-size:20px"><?php echo count($canned); ?></div></div>
</div>

<div class="chat-layout">
<div class="chat-sidebar">
<div style="font-size:12px;color:var(--text-muted);margin-bottom:8px">CHATS</div>
<?php if (!empty($sessions)): foreach ($sessions as $s): ?>
<div class="item <?php echo $s->status === 'waiting' ? 'active' : ''; ?>" onclick="loadChat(<?php echo $s->id; ?>)">
<span class="status-dot" style="background:<?php echo $s->status === 'waiting' ? '#facc15' : ($s->status === 'active' ? '#4ade80' : '#64748b'); ?>"></span>
<strong style="font-size:13px"><?php echo htmlspecialchars($s->visitor_name ?: 'Visitor'); ?></strong>
<div style="font-size:11px;color:var(--text-muted)"><?php echo htmlspecialchars($s->subject ?: $s->department); ?> &middot; <?php echo $s->status; ?></div>
</div>
<?php endforeach; else: ?>
<p style="color:var(--text-muted);font-size:13px;padding:20px;text-align:center">No chats yet</p>
<?php endif; ?>
</div>

<div class="chat-main">
<div class="chat-msgs" id="chatMessages">
<div style="text-align:center;color:var(--text-muted);padding:40px;font-size:14px">Select a chat to view messages</div>
</div>
<div class="chat-input-wrap" style="display:none" id="chatInputBar">
<input type="text" id="chatInput" placeholder="Type a message..." onkeydown="if(event.key==='Enter')sendMsg()">
<button class="btn btn-sm primary" onclick="sendMsg()">Send</button>
<button class="btn btn-sm secondary" onclick="document.getElementById('fileInput').click()">📎</button>
<input type="file" id="fileInput" style="display:none" onchange="uploadFile(this)">
</div>
</div>

<div class="chat-info">
<div id="chatInfoPanel">
<div style="font-size:12px;color:var(--text-muted);margin-bottom:8px">CANNED RESPONSES</div>
<?php if (!empty($canned)): foreach ($canned as $c): ?>
<div class="chat-canned" onclick="insertCanned(<?php echo $c->id; ?>)" title="<?php echo htmlspecialchars($c->message); ?>">
<strong style="font-size:13px">⚡ <?php echo htmlspecialchars($c->title); ?></strong>
<div style="font-size:11px;color:var(--text-muted)"><?php echo htmlspecialchars($c->category); ?></div>
</div>
<?php endforeach; else: ?>
<p style="color:var(--text-muted);font-size:12px">No canned responses. Create one above.</p>
<?php endif; ?>
<div style="font-size:12px;color:var(--text-muted);margin-top:16px;margin-bottom:8px">GROUPS</div>
<?php if (!empty($groups)): foreach ($groups as $g): ?>
<div style="font-size:13px;padding:4px 0">👥 <?php echo htmlspecialchars($g->name); ?> <span style="font-size:11px;color:var(--text-muted)"><?php echo htmlspecialchars($g->department); ?></span></div>
<?php endforeach; else: ?>
<p style="color:var(--text-muted);font-size:12px">No groups yet.</p>
<?php endif; ?>
</div>
</div>
</div>
</div>

<script>
var currentSession = 0;
var lastMsgId = 0;

function loadChat(id) {
    currentSession = id;
    document.getElementById('chatInputBar').style.display = 'flex';
    document.getElementById('chatMessages').innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:20px">Loading...</div>';
    lastMsgId = 0;
    pollChat();
    setInterval(pollChat, 3000);
}

function pollChat() {
    if (!currentSession) return;
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/livechat/messages/' + currentSession + '?since=' + lastMsgId, true);
    x.onload = function() {
        try {
            var msgs = JSON.parse(x.responseText);
            if (msgs.error) return;
            var out = document.getElementById('chatMessages');
            if (msgs.length > 0) {
                msgs.forEach(function(m) {
                    if (m.id > lastMsgId) lastMsgId = m.id;
                    var cls = m.sender_type === 'operator' ? 'operator' : 'visitor';
                    var fileHtml = m.file_url ? '<br><a href="' + m.file_url + '" target="_blank" style="color:var(--accent)">📎 ' + (m.file_name || 'File') + '</a>' : '';
                    out.innerHTML += '<div class="chat-msg ' + cls + '"><div class="bubble">' + m.message.replace(/</g,'&lt;') + fileHtml + '</div><div class="meta">' + m.sender_name + ' &middot; ' + m.created_at + '</div></div>';
                });
                out.scrollTop = out.scrollHeight;
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
    x.onload = function() { input.value = ''; pollChat(); };
    x.send('session_id=' + currentSession + '&message=' + encodeURIComponent(msg));
}

function uploadFile(input) {
    if (!currentSession || !input.files[0]) return;
    var fd = new FormData();
    fd.append('file', input.files[0]);
    var x = new XMLHttpRequest();
    x.open('POST', '/admin/livechat/upload/' + currentSession, true);
    x.onload = function() { pollChat(); };
    x.send(fd);
}

function insertCanned(id) {
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/livechat/canned/' + id, true);
    x.onload = function() {
        try {
            var r = JSON.parse(x.responseText);
            document.getElementById('chatInput').value = r.message;
        } catch(e) {}
    };
    x.send();
}
</script>
