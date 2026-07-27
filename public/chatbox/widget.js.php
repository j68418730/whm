<?php
header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *');
$tenantId = (int)($_GET['tenant_id'] ?? 0);
if (!$tenantId) { echo 'console.error("Chatbox: Invalid tenant_id");'; exit; }
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$stmt = $pdo->prepare("SELECT * FROM chatbox_tenants WHERE id = ?");
$stmt->execute([$tenantId]);
$tenant = $stmt->fetch(PDO::FETCH_OBJ);
if (!$tenant) { echo 'console.error("Chatbox: Tenant not found");'; exit; }
$rooms = $pdo->prepare("SELECT * FROM chatbox_rooms WHERE tenant_id = ? AND is_active = 1 ORDER BY sort_order");
$rooms->execute([$tenantId]);
$roomsList = $rooms->fetchAll(PDO::FETCH_OBJ);
?>
(function() {
    var tenantId = <?php echo $tenantId; ?>;
    var widgetTitle = <?php echo json_encode($tenant->widget_title ?? 'Chat Room'); ?>;
    var accentColor = <?php echo json_encode($tenant->widget_color ?? '#008cff'); ?>;
    var bgColor = <?php echo json_encode($tenant->widget_bg ?? '#0a0e1a'); ?>;
    var textColor = <?php echo json_encode($tenant->widget_text_color ?? '#ffffff'); ?>;
    var fontFamily = <?php echo json_encode($tenant->font_family ?? 'Inter, sans-serif'); ?>;
    var guestEnabled = <?php echo $tenant->guest_enabled ? 'true' : 'false'; ?>;
    var regEnabled = <?php echo $tenant->registration_enabled ? 'true' : 'false'; ?>;
    var voiceEnabled = <?php echo $tenant->voice_enabled ? 'true' : 'false'; ?>;
    var rooms = <?php echo json_encode($roomsList); ?>;
    var playerHtml = <?php echo json_encode($tenant->player_html ?? ''); ?>;
    var currentTheme = <?php echo json_encode($tenant->theme ?? 'default'); ?>;
    var customCss = <?php echo json_encode($tenant->custom_css ?? ''); ?>;
    var apiBase = '/chatbox/api.php';
    var currentUser = null;
    var currentRoomId = (rooms.length > 0) ? rooms[0].id : 0;
    var lastMsgId = 0;
    var pollTimer = null;

    // Theme definitions
    var themes = {
        'default': { accent: '#008cff', bg: '#0a0e1a', text: '#ffffff', input: 'rgba(0,0,0,.2)', msg_bg: 'rgba(255,255,255,.06)', msg_own: 'rgba(0,140,255,.15)', border: 'rgba(255,255,255,.1)' },
        'blue': { accent: '#0066ff', bg: '#001433', text: '#ffffff', input: 'rgba(0,0,0,.3)', msg_bg: 'rgba(255,255,255,.05)', msg_own: 'rgba(0,102,255,.15)', border: 'rgba(0,102,255,.2)' },
        'black': { accent: '#333', bg: '#000', text: '#e0e0e0', input: 'rgba(255,255,255,.05)', msg_bg: 'rgba(255,255,255,.04)', msg_own: 'rgba(255,255,255,.08)', border: 'rgba(255,255,255,.06)' },
        'discord': { accent: '#5865f2', bg: '#313338', text: '#dbdee1', input: 'rgba(0,0,0,.3)', msg_bg: 'rgba(255,255,255,.04)', msg_own: 'rgba(88,101,242,.15)', border: 'rgba(255,255,255,.06)' },
        'twitch': { accent: '#9146ff', bg: '#0e0e10', text: '#efeff1', input: 'rgba(255,255,255,.05)', msg_bg: 'rgba(255,255,255,.04)', msg_own: 'rgba(145,70,255,.15)', border: 'rgba(255,255,255,.06)' },
        'neon': { accent: '#00ff88', bg: '#001a0a', text: '#00ff88', input: 'rgba(0,255,136,.05)', msg_bg: 'rgba(0,255,136,.04)', msg_own: 'rgba(0,255,136,.12)', border: 'rgba(0,255,136,.2)' },
        'gaming': { accent: '#ff6600', bg: '#0d0d0d', text: '#ff9933', input: 'rgba(255,102,0,.05)', msg_bg: 'rgba(255,102,0,.04)', msg_own: 'rgba(255,102,0,.12)', border: 'rgba(255,102,0,.2)' },
        'hacker': { accent: '#00ff00', bg: '#000a00', text: '#00ff00', input: 'rgba(0,255,0,.05)', msg_bg: 'rgba(0,255,0,.04)', msg_own: 'rgba(0,255,0,.1)', border: 'rgba(0,255,0,.15)' },
        'purple': { accent: '#a855f7', bg: '#0a0014', text: '#e9d5ff', input: 'rgba(168,85,247,.05)', msg_bg: 'rgba(168,85,247,.04)', msg_own: 'rgba(168,85,247,.12)', border: 'rgba(168,85,247,.15)' },
        'retro': { accent: '#00ff00', bg: '#000080', text: '#00ff00', input: 'rgba(0,255,0,.1)', msg_bg: 'rgba(0,255,0,.05)', msg_own: 'rgba(0,255,0,.15)', border: 'rgba(0,255,0,.2)' }
    };

    function applyTheme(theme) {
        var t = themes[theme] || themes['default'];
        if (currentTheme !== 'custom') {
            accentColor = t.accent; bgColor = t.bg; textColor = t.text;
        }
    }
    applyTheme(currentTheme);

    // Inject CSS
    var css = '#chatbox-widget{position:fixed;bottom:20px;right:20px;z-index:999999;font-family:'+fontFamily+';direction:ltr}'+
    '#chatbox-toggle{width:56px;height:56px;border-radius:50%;background:'+accentColor+';color:#fff;border:none;font-size:26px;cursor:pointer;box-shadow:0 4px 20px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;transition:.2s;margin-left:auto}'+
    '#chatbox-toggle:hover{transform:scale(1.05)}'+
    '#chatbox-panel{position:fixed;bottom:80px;right:20px;width:360px;height:500px;background:'+bgColor+';border:1px solid '+(accentColor)+'33;border-radius:16px;overflow:hidden;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.4)}'+
    '#chatbox-panel.closed{display:none}'+
    '#chatbox-panel.open{display:flex}'+
    '#chatbox-hdr{padding:12px 16px;border-bottom:1px solid '+(accentColor)+'33;display:flex;justify-content:space-between;align-items:center;flex-shrink:0}'+
    '#chatbox-hdr .title{font-weight:700;font-size:13px;color:'+textColor+'}'+
    '#chatbox-hdr .close{background:none;border:none;color:'+textColor+'88;cursor:pointer;font-size:18px}'+
    '#chatbox-hdr .close:hover{color:'+textColor+'}'+
    '#chatbox-rooms{display:flex;gap:4px;padding:8px 12px;border-bottom:1px solid '+(accentColor)+'22;overflow-x:auto;flex-shrink:0}'+
    '#chatbox-rooms .rm{background:none;border:1px solid '+(accentColor)+'44;border-radius:12px;padding:4px 12px;font-size:10px;cursor:pointer;color:'+textColor+'88;white-space:nowrap;transition:.1s}'+
    '#chatbox-rooms .rm:hover{background:'+(accentColor)+'22;color:'+textColor+'}'+
    '#chatbox-rooms .rm.act{background:'+(accentColor)+'33;color:'+textColor+';border-color:'+accentColor+'}'+
    '#chatbox-msgs{flex:1;overflow-y:auto;padding:8px 12px}'+
    '#chatbox-msgs .msg{padding:6px 10px;margin-bottom:4px;border-radius:8px;font-size:12px;line-height:1.5;color:'+textColor+'cc}'+
    '#chatbox-msgs .msg .u{font-weight:600;color:'+accentColor+';font-size:11px;margin-bottom:2px}'+
    '#chatbox-msgs .msg .t{font-size:9px;color:'+textColor+'66;margin-left:6px}'+
    '#chatbox-msgs .msg.sys{text-align:center;color:'+textColor+'66;font-size:10px;font-style:italic}'+
    '#chatbox-inp{padding:8px 12px;border-top:1px solid '+(accentColor)+'22;flex-shrink:0}'+
    '#chatbox-inp .row{display:flex;gap:6px}'+
    '#chatbox-inp textarea{flex:1;padding:8px 12px;border-radius:10px;border:1px solid '+(accentColor)+'33;background:rgba(0,0,0,.2);color:'+textColor+';font-size:12px;outline:none;resize:none;min-height:34px;max-height:80px;font-family:inherit}'+
    '#chatbox-inp textarea:focus{border-color:'+accentColor+'}'+
    '#chatbox-inp .sb{width:34px;height:34px;border-radius:50%;background:'+accentColor+';color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}'+
    '#chatbox-inp .sb:disabled{opacity:.4}'+
    '#chatbox-login{padding:20px;text-align:center}'+
    '#chatbox-login input{width:100%;padding:8px 12px;border-radius:8px;border:1px solid '+(accentColor)+'33;background:rgba(0,0,0,.2);color:'+textColor+';font-size:12px;outline:none;box-sizing:border-box;margin-bottom:8px}'+
    '#chatbox-login input:focus{border-color:'+accentColor+'}'+
    '#chatbox-login .btn{width:100%;padding:8px;border-radius:8px;background:'+accentColor+';color:#fff;border:none;font-weight:600;cursor:pointer;font-size:12px}'+
    '#chatbox-login .btn:hover{opacity:.9}'+
    '#chatbox-login .link{color:'+accentColor+'88;cursor:pointer;font-size:11px;margin-top:6px;display:inline-block}'+
    '#chatbox-login .link:hover{color:'+accentColor+'}'+
    '#chatbox-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:'+textColor+'66;gap:4px;font-size:12px}'+
    '#chatbox-empty .ic{font-size:32px}'+
    (customCss ? customCss : '');

    function initWidget() {
        var style = document.createElement('style');
        style.textContent = css;
        document.head.appendChild(style);

        var html = '<div id="chatbox-widget">'+
            '<button id="chatbox-toggle" onclick="toggleChatbox()">💬</button>'+
            '<div id="chatbox-panel" class="closed">'+
            '<div id="chatbox-hdr"><span class="title">'+widgetTitle+'</span><button class="close" onclick="toggleChatbox()">✕</button></div>'+
            '<div id="chatbox-rooms"></div>'+
            '<div id="chatbox-msgs"><div id="chatbox-empty"><div class="ic">💬</div><div>Loading...</div></div></div>'+
            '<div id="chatbox-inp" style="display:none"><div class="row"><textarea id="chatbox-input" placeholder="Type a message..." rows="1" onkeydown="if(event.key===\'Enter\'&&!event.shiftKey){event.preventDefault();sendChatboxMsg()}"></textarea><button class="sb" onclick="sendChatboxMsg()">➤</button></div></div>'+
            '<div id="chatbox-login" style="display:none"></div>'+
            '</div></div>';

        var div = document.createElement('div');
        div.innerHTML = html;
        document.body.appendChild(div);

    // Make functions global
    window.toggleChatbox = function() {
        var p = document.getElementById('chatbox-panel');
        p.classList.toggle('closed');
        var isOpen = p.classList.contains('open');
        if (isOpen) {
            if (!currentUser && guestEnabled) showGuestLogin();
            else if (!currentUser && !guestEnabled) showRegisterLogin();
            else startPolling();
        } else { stopPolling(); }
    };

    window.sendChatboxMsg = function() {
        var inp = document.getElementById('chatbox-input');
        var msg = inp.value.trim();
        if (!msg || !currentRoomId) return;
        inp.disabled = true;
        var fd = new FormData();
        fd.append('action', 'send_message');
        fd.append('room_id', currentRoomId);
        fd.append('tenant_id', tenantId);
        fd.append('username', currentUser ? currentUser.username : 'Guest');
        fd.append('user_id', currentUser ? (currentUser.userId || 0) : 0);
        fd.append('message', msg);
        fetch(apiBase, {method:'POST', body: fd})
        .then(function(r){ return r.json(); })
        .then(function(d){
            inp.value = '';
            inp.style.height = 'auto';
            inp.disabled = false;
            if (d.success) fetchMessages();
        }).catch(function(){ inp.disabled = false; });
    };

    function renderRooms() {
        var el = document.getElementById('chatbox-rooms');
        if (!rooms || rooms.length < 2) { el.style.display = 'none'; return; }
        el.style.display = 'flex';
        var h = '';
        rooms.forEach(function(r){
            h += '<button class="rm'+(r.id === currentRoomId ? ' act' : '')+'" onclick="switchRoom('+r.id+')">#'+(r.name||'Room')+'</button>';
        });
        el.innerHTML = h;
    }
    window.switchRoom = function(id) {
        currentRoomId = id; lastMsgId = 0;
        renderRooms();
        document.getElementById('chatbox-msgs').innerHTML = '<div id="chatbox-empty"><div class="ic">💬</div><div>Loading...</div></div>';
        fetchMessages();
    };

    function fetchMessages() {
        var url = apiBase + '?action=get_messages&room_id=' + currentRoomId + '&tenant_id=' + tenantId;
        if (lastMsgId > 0) url += '&since=' + lastMsgId;
        fetch(url).then(function(r){ return r.json(); }).then(function(msgs){
            if (!msgs || msgs.length === 0) return;
            var el = document.getElementById('chatbox-msgs');
            var empty = document.getElementById('chatbox-empty');
            if (empty) empty.style.display = 'none';
            var isFirst = lastMsgId === 0;
            if (isFirst) el.innerHTML = '';
            msgs.forEach(function(m){
                if (m.message_type === 'system') { el.innerHTML += '<div class="msg sys">'+escHtml(m.message)+'</div>'; return; }
                var t = '';
                if (m.created_at) { var d = new Date(m.created_at); t = d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}); }
                el.innerHTML += '<div class="msg"><div class="u">'+escHtml(m.username)+'<span class="t">'+t+'</span></div><div>'+escHtml(m.message)+'</div></div>';
                if (m.id > lastMsgId) lastMsgId = m.id;
            });
            if (isFirst) el.scrollTop = el.scrollHeight;
            else el.scrollTop = el.scrollHeight;
        }).catch(function(){});
    }

    function startPolling() {
        fetchMessages();
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(fetchMessages, 3000);
    }
    function stopPolling() { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } }

    function showGuestLogin() {
        var el = document.getElementById('chatbox-login');
        el.style.display = 'block';
        el.innerHTML = '<div style="font-size:13px;font-weight:600;color:'+textColor+';margin-bottom:8px">Choose a nickname</div>'+
            '<input id="cb-guest-name" placeholder="Your name" maxlength="20" onkeydown="if(event.key===\'Enter\')doGuestLogin()">'+
            '<button class="btn" onclick="doGuestLogin()">Join Chat</button>'+
            (regEnabled ? '<span class="link" onclick="showRegisterLogin()">Create account</span>' : '');
        document.getElementById('chatbox-inp').style.display = 'none';
    }
    window.doGuestLogin = function() {
        var name = document.getElementById('cb-guest-name').value.trim();
        if (!name) return;
        var fd = new FormData();
        fd.append('action', 'guest_login');
        fd.append('tenant_id', tenantId);
        fd.append('username', name);
        fetch(apiBase, {method:'POST', body: fd}).then(function(r){ return r.json(); }).then(function(d){
            if (d.success) { currentUser = {username: name}; onLoggedIn(); }
        });
    };
    window.showRegisterLogin = function() {
        var el = document.getElementById('chatbox-login');
        el.style.display = 'block';
        el.innerHTML = '<div style="font-size:13px;font-weight:600;color:'+textColor+';margin-bottom:8px">Login or Register</div>'+
            '<input id="cb-login-user" placeholder="Username" onkeydown="if(event.key===\'Enter\')doLogin()"><input id="cb-login-pass" type="password" placeholder="Password" onkeydown="if(event.key===\'Enter\')doLogin()">'+
            '<button class="btn" onclick="doLogin()">Login</button>'+
            '<span class="link" onclick="showRegister()">Create account</span>'+
            (guestEnabled ? '<span class="link" onclick="showGuestLogin()">Guest access</span>' : '');
        document.getElementById('chatbox-inp').style.display = 'none';
    };
    window.doLogin = function() {
        var u = document.getElementById('cb-login-user').value.trim();
        var p = document.getElementById('cb-login-pass').value;
        if (!u || !p) return;
        var fd = new FormData(); fd.append('action','login'); fd.append('tenant_id',tenantId); fd.append('username',u); fd.append('password',p);
        fetch(apiBase, {method:'POST', body: fd}).then(function(r){ return r.json(); }).then(function(d){
            if (d.success) { currentUser = d; onLoggedIn(); }
            else { alert(d.error || 'Login failed'); }
        });
    };
    window.showRegister = function() {
        document.getElementById('chatbox-login').innerHTML = '<div style="font-size:13px;font-weight:600;color:'+textColor+';margin-bottom:8px">Register</div>'+
            '<input id="cb-reg-user" placeholder="Username" maxlength="20"><input id="cb-reg-pass" type="password" placeholder="Password (4+ chars)">'+
            '<input id="cb-reg-email" type="email" placeholder="Email (optional)">'+
            '<button class="btn" onclick="doRegister()">Register</button>'+
            '<span class="link" onclick="showRegisterLogin()">Back to login</span>';
    };
    window.doRegister = function() {
        var u = document.getElementById('cb-reg-user').value.trim();
        var p = document.getElementById('cb-reg-pass').value;
        var e = document.getElementById('cb-reg-email').value.trim();
        if (!u || !p) return;
        var fd = new FormData(); fd.append('action','register'); fd.append('tenant_id',tenantId); fd.append('username',u); fd.append('password',p); fd.append('email',e);
        fetch(apiBase, {method:'POST', body: fd}).then(function(r){ return r.json(); }).then(function(d){
            if (d.success) { currentUser = d; onLoggedIn(); }
            else { alert(d.error || 'Registration failed'); }
        });
    };

    function onLoggedIn() {
        document.getElementById('chatbox-login').style.display = 'none';
        document.getElementById('chatbox-inp').style.display = 'block';
        if (playerHtml) {
            var ph = document.getElementById('chatbox-hdr');
            ph.innerHTML += '<div style="margin-top:6px">'+playerHtml+'</div>';
        }
        renderRooms();
        startPolling();
    }

    function escHtml(t) { var d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML; }

    } // end initWidget

    // Start when DOM is ready
    if (document.body) { initWidget(); }
    else { document.addEventListener('DOMContentLoaded', initWidget); }
})();
