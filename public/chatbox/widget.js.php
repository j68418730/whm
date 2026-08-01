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

// Room pre-select via ?room={slug or id}
$preselect = trim($_GET['room'] ?? '');
if ($preselect !== '') {
    $target = null;
    foreach ($roomsList as $r) {
        if ($r->slug === $preselect || (string)$r->id === $preselect) { $target = $r; break; }
    }
    if ($target) {
        // Reorder so the requested room is first (default selected)
        $roomsList = array_values(array_filter($roomsList, function($r) use ($target) { return $r->id != $target->id; }));
        array_unshift($roomsList, $target);
    }
}
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
    var apiBase = 'https://planet-hosts.com/chatbox/api.php';
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
    '#chatbox-body{display:flex;flex:1;overflow:hidden}'+
    '#chatbox-main{display:flex;flex-direction:column;flex:1;min-width:0}'+
    '#chatbox-resize{width:4px;flex-shrink:0;cursor:col-resize;background:transparent;transition:background .15s}'+
    '#chatbox-resize:hover{background:'+(accentColor)+'33}'+
    '#chatbox-resize.active{background:'+(accentColor)+'55}'+
    '#chatbox-online{width:110px;flex-shrink:0;border-left:1px solid '+(accentColor)+'22;overflow-y:auto;padding-bottom:8px}'+
    '#chatbox-online .ou{display:flex;align-items:center;gap:4px;padding:5px 8px;font-size:10px;color:'+textColor+'bb;cursor:pointer;border-radius:4px;margin:1px 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}'+
    '#chatbox-online .ou:hover{background:'+(accentColor)+'22}'+
    '#chatbox-online .ou .cam-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}'+
    '#chatbox-online .ou .role-badge{font-size:7px;padding:0 4px;border-radius:3px;font-weight:700}'+
    '#chatbox-camview{display:none;flex-shrink:0;padding:4px 8px;border-bottom:1px solid '+(accentColor)+'22}'+
    '#chatbox-camview.open{display:flex;gap:4px;overflow-x:auto}'+
    '#chatbox-camview video{width:80px;height:60px;border-radius:6px;object-fit:cover;background:#000;flex-shrink:0}'+
    '#chatbox-vc{display:none;justify-content:center;gap:8px;padding:8px 10px;border-top:1px solid '+(accentColor)+'22;align-items:center;flex-shrink:0}'+
    '#chatbox-vc.open{display:flex}'+
    '#chatbox-vc .sb{width:30px;height:30px;border-radius:50%;background:none;border:1px solid '+(accentColor)+'44;color:'+textColor+';cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;transition:.15s}'+
    '#chatbox-vc .sb.active{background:'+accentColor+'55;border-color:'+accentColor+'}'+
    '#chatbox-vc .sb:hover{background:'+(accentColor)+'22}'+
    '#chatbox-vc #vc-meter{width:120px;height:22px;background:'+bgColor+';border:1px solid '+(accentColor)+'33;border-radius:11px;overflow:hidden;position:relative}'+
    '#chatbox-vc #vc-meter::after{content:"MIC";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:7px;color:'+textColor+'66;letter-spacing:1px}'+
    '#chatbox-vc #vc-meter-fill{height:100%;width:0%;background:linear-gradient(90deg,'+accentColor+'88,'+accentColor+');border-radius:11px;transition:width .1s}'+
    '#chatbox-ctxmenu{position:fixed;z-index:1000;background:'+bgColor+';border:1px solid '+(accentColor)+'44;border-radius:8px;padding:4px;min-width:140px;box-shadow:0 4px 20px rgba(0,0,0,.5)}'+
    '#chatbox-ctxmenu .ctx-item{padding:6px 10px;font-size:11px;cursor:pointer;border-radius:4px;color:'+textColor+'cc;display:flex;align-items:center;gap:6px}'+
    '#chatbox-ctxmenu .ctx-item:hover{background:'+(accentColor)+'22}'+
    '#chatbox-ctxmenu .ctx-item.danger{color:#f87171}'+
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
    '#chatbox-msgs .rx{display:flex;gap:3px;margin-top:3px;flex-wrap:wrap}'+
    '#chatbox-msgs .rx-add{display:inline-block;margin-top:2px;opacity:.6}'+
    '#chatbox-msgs .msg:hover .rx-add{opacity:1}'+
    '#chatbox-msgs .msg .rx span:hover{background:'+(accentColor)+'22}'+
    '#emoji-picker span:hover{background:'+(accentColor)+'22}'+
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
            '<div id="chatbox-hdr"><span class="title">'+widgetTitle+'</span><div style="display:flex;gap:4px;align-items:center"><button class="close" onclick="openSettings()" title="Settings">⚙️</button><button class="close" onclick="logoutChat()" title="Close & Logout">✕</button></div></div>'+
            '<div id="chatbox-settings" style="display:none;position:absolute;top:40px;right:8px;background:'+bgColor+';border:1px solid '+(accentColor)+'33;border-radius:12px;padding:14px;width:230px;z-index:20;box-shadow:0 8px 30px rgba(0,0,0,.5)"></div>'+
            '<div id="chatbox-body">'+
            '<div id="chatbox-main">'+
            '<div id="chatbox-rooms"></div>'+
            '<div id="chatbox-camview"></div>'+
            '<div id="chatbox-msgs"></div>'+
            '<div id="chatbox-vc"><button class="sb" id="talkBtn" onmousedown="talkStart()" onmouseup="talkStop()" onmouseleave="talkStop()" title="Push to talk" style="'+(voiceEnabled?'':'display:none')+'">🎤</button><button class="sb" id="muteBtn" onclick="toggleMute()" title="Mute">🔇</button><div id="vc-meter"><div id="vc-meter-fill"></div></div></div>'+
            '<div id="chatbox-inp" style="display:none"><div class="row"><button class="sb" style="background:none;border:1px solid '+(accentColor)+'44;font-size:16px" onclick="toggleEmojiPicker()">😊</button>'+(voiceEnabled?'<button class="sb" style="background:none;border:1px solid '+(accentColor)+'44;font-size:16px" id="voiceBtn" onclick="toggleVoice()">🎤</button>':'')+'<button class="sb" style="background:none;border:1px solid '+(accentColor)+'44;font-size:16px" onclick="toggleCam()">📹</button><textarea id="chatbox-input" placeholder="Type a message..." rows="1" onkeydown="if(event.key===\'Enter\'&&!event.shiftKey){event.preventDefault();sendChatboxMsg()}"></textarea><button class="sb" onclick="sendChatboxMsg()">➤</button></div>'+
            '<div id="emoji-picker" style="display:none;position:absolute;bottom:52px;right:12px;background:'+bgColor+';border:1px solid '+(accentColor)+'33;border-radius:10px;padding:8px;width:220px;max-height:200px;overflow-y:auto;z-index:10;box-shadow:0 4px 20px rgba(0,0,0,.4)"></div></div>'+
            '</div>'+
            '<div id="chatbox-resize" title="Drag to resize"></div>'+
            '<div id="chatbox-online"><div style="padding:8px 10px;font-size:10px;color:'+textColor+'88;font-weight:600;text-transform:uppercase;letter-spacing:.5px">In Chat</div><div id="chatbox-online-list"></div></div>'+
            '</div>'+
            '<div id="chatbox-video" style="display:none;position:relative;flex-shrink:0;padding:4px 8px"><video id="local-video" autoplay muted style="width:100%;height:120px;border-radius:8px;object-fit:cover;background:#000"></video><button onclick="stopCam()" style="position:absolute;top:8px;right:12px;background:'+accentColor+';border:none;color:#fff;border-radius:4px;cursor:pointer;font-size:10px;padding:3px 8px">✕</button></div>'+
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
        profileCache = {};
        renderRooms();
        document.getElementById('chatbox-msgs').innerHTML = '<div id="chatbox-empty"><div class="ic">💬</div><div>Loading...</div></div>';
        fetchMessages();
        // Load this room's profile for current user
        if (currentUser && currentUser.userId) {
            fetch(apiBase + '?action=get_profile&user_id=' + currentUser.userId + '&room_id=' + currentRoomId).then(function(r){ return r.json(); }).then(function(p){
                if (p && !p.error) myProfile = { display_name:p.display_name||'', avatar:p.avatar||'', font_style:p.font_style||'Inter', font_color:p.font_color||'#ffffff', font_size:p.font_size||13 };
            }).catch(function(){});
        } else {
            fetch(apiBase + '?action=get_guest_profile&tenant_id=' + tenantId + '&room_id=' + currentRoomId).then(function(r){ return r.json(); }).then(function(p){
                if (p) myProfile = { display_name:p.display_name||'', avatar:p.avatar||'', font_style:p.font_style||'Inter', font_color:p.font_color||'#ffffff', font_size:p.font_size||13 };
            }).catch(function(){});
        }
        joinOnline();
    };

    function fetchMessages() {
        var url = apiBase + '?action=get_messages&room_id=' + currentRoomId + '&tenant_id=' + tenantId;
        if (lastMsgId > 0) url += '&since=' + lastMsgId;
        fetch(url).then(function(r){ return r.json(); }).then(function(msgs){
            var el = document.getElementById('chatbox-msgs');
            if (!msgs) return;
            var isFirst = lastMsgId === 0;
            if (isFirst) {
                if (msgs.length === 0) {
                    el.innerHTML = '<div style="text-align:center;padding:30px;color:'+textColor+'66;font-size:12px">No messages yet. Be the first to say something!</div>';
                    return;
                }
                el.innerHTML = '';
            }
            msgs.forEach(function(m){
                if (m.message_type === 'system') { el.innerHTML += '<div class="msg sys">'+escHtml(m.message)+'</div>'; return; }
                var t = '';
                if (m.created_at) { var d = new Date(m.created_at); t = d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}); }
                var p = getUserProfile(m.username);
                var av = p.avatar ? '<img src="'+p.avatar+'" style="width:16px;height:16px;border-radius:50%;vertical-align:middle;margin-right:4px">' : '';
                el.innerHTML += '<div class="msg" data-id="'+m.id+'" style="'+profileStyle(p)+'"><div class="u" style="cursor:pointer" onclick="userMenu(\''+escAttr(m.username)+'\')">'+av+escHtml(p.display_name || m.username)+'<span class="t">'+t+'</span></div><div>'+escHtml(m.message)+'</div><div class="rx" id="rx-'+m.id+'"></div><span class="rx-add" onclick="addReaction('+m.id+')" style="font-size:11px;cursor:pointer;color:'+accentColor+'" title="React">😊</span></div>';
                if (m.id > lastMsgId) lastMsgId = m.id;
            });
            loadReactions(msgs);
            el.scrollTop = el.scrollHeight;
            cacheProfiles(msgs.map(function(m){ return m.username; }));
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
        document.getElementById('chatbox-vc').classList.add('open');
        if (playerHtml) {
            var ph = document.getElementById('chatbox-hdr');
            ph.innerHTML += '<div style="margin-top:6px">'+playerHtml+'</div>';
        }
        renderRooms();
        startPolling();
        joinOnline();
    }

    // === LOGOUT ===
    window.logoutChat = function() {
        // Clear server guest session
        var fd = new FormData();
        fd.append('action', 'logout');
        fd.append('tenant_id', tenantId);
        fetch(apiBase, {method:'POST', body: fd}).then(function(){
            // Reset client state
            currentUser = null;
            stopPolling();
            voiceOn = false;
            camOn = false;
            if (localStream) { localStream.getTracks().forEach(function(t){ t.stop(); }); localStream = null; }
            Object.keys(pcMap).forEach(function(k){ if (pcMap[k]) pcMap[k].close(); });
            pcMap = {};
            document.getElementById('chatbox-inp').style.display = 'none';
            document.getElementById('chatbox-video').style.display = 'none';
            document.getElementById('chatbox-vc').classList.remove('open');
            document.getElementById('chatbox-msgs').innerHTML = '';
            document.getElementById('chatbox-online-list').innerHTML = '';
            // Close panel and show login
            var panel = document.getElementById('chatbox-panel');
            panel.classList.remove('open');
            panel.classList.add('closed');
            if (guestEnabled) showGuestLogin();
            else if (regEnabled) showRegisterLogin();
            else showGuestLogin();
        }).catch(function(){
            currentUser = null;
            var panel = document.getElementById('chatbox-panel');
            panel.classList.remove('open');
            panel.classList.add('closed');
            if (guestEnabled) showGuestLogin();
            else if (regEnabled) showRegisterLogin();
            else showGuestLogin();
        });
    };

    // === SETTINGS / PROFILE ===
    var myProfile = { display_name:'', avatar:'', font_style:'Inter', font_color:'#ffffff', font_size:13 };
    var fontStyles = ['Inter','Arial','Helvetica','Verdana','Georgia','Times New Roman','Courier New','Comic Sans MS','Tahoma','Trebuchet MS','Poppins','Roboto','Open Sans','Montserrat','Lato','Playfair Display','Monospace'];
    window.openSettings = function() {
        var panel = document.getElementById('chatbox-settings');
        if (panel.style.display === 'block') { panel.style.display = 'none'; return; }
        // Load current profile (per room)
        if (currentUser && currentUser.userId) {
            fetch(apiBase + '?action=get_profile&user_id=' + currentUser.userId + '&room_id=' + currentRoomId).then(function(r){ return r.json(); }).then(function(p){
                if (p && !p.error) { myProfile = { display_name:p.display_name||'', avatar:p.avatar||'', font_style:p.font_style||'Inter', font_color:p.font_color||'#ffffff', font_size:p.font_size||13 }; buildSettingsPanel(); }
                else buildSettingsPanel();
            }).catch(function(){ buildSettingsPanel(); });
        } else {
            fetch(apiBase + '?action=get_guest_profile&tenant_id=' + tenantId + '&room_id=' + currentRoomId).then(function(r){ return r.json(); }).then(function(p){
                if (p) { myProfile = { display_name:p.display_name||'', avatar:p.avatar||'', font_style:p.font_style||'Inter', font_color:p.font_color||'#ffffff', font_size:p.font_size||13 }; }
                buildSettingsPanel();
            }).catch(function(){ buildSettingsPanel(); });
        }
    };
    function buildSettingsPanel() {
        var panel = document.getElementById('chatbox-settings');
        var fs = '';
        fontStyles.forEach(function(f){ fs += '<option value="'+f+'"'+(myProfile.font_style===f?' selected':'')+'>'+f+'</option>'; });
        panel.innerHTML = '<div style="font-size:13px;font-weight:700;color:'+textColor+';margin-bottom:10px">My Profile</div>'+
            '<div class="pf-row" style="margin-bottom:8px"><label style="font-size:10px;color:'+textColor+'88;display:block;margin-bottom:2px">Display Name</label><input id="pf-name" value="'+escAttr(myProfile.display_name)+'" placeholder="Your name" style="width:100%;padding:6px 8px;border-radius:6px;border:1px solid '+(accentColor)+'44;background:rgba(0,0,0,.2);color:'+textColor+';font-size:12px;outline:none;box-sizing:border-box"></div>'+
            '<div class="pf-row" style="margin-bottom:8px"><label style="font-size:10px;color:'+textColor+'88;display:block;margin-bottom:2px">Avatar</label><div style="display:flex;gap:6px;align-items:center"><input id="pf-avatar" value="'+escAttr(myProfile.avatar)+'" placeholder="Image URL" style="flex:1;padding:6px 8px;border-radius:6px;border:1px solid '+(accentColor)+'44;background:rgba(0,0,0,.2);color:'+textColor+';font-size:11px;outline:none;min-width:0">'+
            '<label style="cursor:pointer;background:'+(accentColor)+'22;color:'+accentColor+';padding:5px 8px;border-radius:6px;font-size:10px;white-space:nowrap">Upload<input type="file" id="pf-avatar-file" accept="image/*" style="display:none" onchange="uploadAvatar()"></label></div></div>'+
            '<div class="pf-row" style="margin-bottom:8px"><label style="font-size:10px;color:'+textColor+'88;display:block;margin-bottom:2px">Font Style</label><select id="pf-font" style="width:100%;padding:6px 8px;border-radius:6px;border:1px solid '+(accentColor)+'44;background:rgba(0,0,0,.2);color:'+textColor+';font-size:12px;outline:none">'+fs+'</select></div>'+
            '<div class="pf-row" style="margin-bottom:8px"><label style="font-size:10px;color:'+textColor+'88;display:block;margin-bottom:2px">Font Color</label><input id="pf-color" type="color" value="'+myProfile.font_color+'" style="width:100%;height:32px;border-radius:6px;border:1px solid '+(accentColor)+'44;background:rgba(0,0,0,.2);cursor:pointer;padding:2px"></div>'+
            '<div class="pf-row" style="margin-bottom:10px"><label style="font-size:10px;color:'+textColor+'88;display:block;margin-bottom:2px">Font Size: <span id="pf-size-lbl">'+myProfile.font_size+'px</span></label><input id="pf-size" type="range" min="11" max="20" value="'+myProfile.font_size+'" oninput="document.getElementById(\'pf-size-lbl\').textContent=this.value+\'px\'" style="width:100%"></div>'+
            '<button class="btn" onclick="saveProfile()" style="width:100%;padding:8px;border-radius:8px;background:'+accentColor+';color:#fff;border:none;font-weight:600;cursor:pointer;font-size:12px">💾 Save Profile</button>';
        panel.style.display = 'block';
    }
    window.uploadAvatar = function() {
        var file = document.getElementById('pf-avatar-file').files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('action', 'upload_avatar');
        fd.append('tenant_id', tenantId);
        fd.append('avatar', file);
        fetch(apiBase, {method:'POST', body: fd}).then(function(r){ return r.json(); }).then(function(d){
            if (d.success) { document.getElementById('pf-avatar').value = d.url; myProfile.avatar = d.url; }
            else alert(d.error || 'Upload failed');
        });
    };
    window.saveProfile = function() {
        myProfile = {
            display_name: document.getElementById('pf-name').value.trim(),
            avatar: document.getElementById('pf-avatar').value.trim(),
            font_style: document.getElementById('pf-font').value,
            font_color: document.getElementById('pf-color').value,
            font_size: parseInt(document.getElementById('pf-size').value) || 13,
        };
        var fd = new FormData();
        if (currentUser && currentUser.userId) {
            fd.append('action', 'save_profile');
            fd.append('user_id', currentUser.userId);
            fd.append('username', currentUser.username);
        } else {
            fd.append('action', 'save_guest_profile');
            fd.append('tenant_id', tenantId);
            fd.append('username', currentUser ? currentUser.username : 'Guest');
        }
        fd.append('room_id', currentRoomId);
        fd.append('display_name', myProfile.display_name);
        fd.append('avatar', myProfile.avatar);
        fd.append('font_style', myProfile.font_style);
        fd.append('font_color', myProfile.font_color);
        fd.append('font_size', myProfile.font_size);
        fetch(apiBase, {method:'POST', body: fd}).then(function(r){ return r.json(); }).then(function(){
            document.getElementById('chatbox-settings').style.display = 'none';
            renderOnline();
        });
    };
    function profileStyle(u) {
        var s = '';
        if (u && u.font_color) s += 'color:' + u.font_color + ';';
        if (u && u.font_style) s += 'font-family:' + u.font_style + ';';
        if (u && u.font_size) s += 'font-size:' + u.font_size + 'px;';
        return s;
    }

    // === EMOJI PICKER ===
    var emojis = ['😊','😂','❤️','👍','🔥','🎉','😍','😢','😎','🤔','👏','🙏','💯','✨','🎵','🎮','🍕','☕','⭐','🌟','💪','🤝','😴','🥳','😇','🤗','😅','🙈','💜','🦄'];
    window.toggleEmojiPicker = function() {
        var picker = document.getElementById('emoji-picker');
        if (picker.style.display === 'block') { picker.style.display = 'none'; return; }
        var h = '';
        emojis.forEach(function(e){ h += '<span style="font-size:20px;cursor:pointer;display:inline-block;padding:3px;margin:2px;border-radius:4px" onmouseover="this.style.background='+(accentColor)+'33" onmouseout="this.style.background=\'transparent\'" onclick="insertEmoji(\''+e+'\')">'+e+'</span>'; });
        picker.innerHTML = h;
        picker.style.display = 'block';
        // Load custom emojis
        fetch(apiBase + '?action=get_emojis&tenant_id=' + tenantId).then(function(r){ return r.json(); }).then(function(list){
            if (list && list.length) {
                list.forEach(function(em){
                    var img = document.createElement('img');
                    img.src = em.url;
                    img.style.width = '22px'; img.style.height = '22px';
                    img.style.cursor = 'pointer'; img.style.margin = '2px';
                    img.style.borderRadius = '4px';
                    img.title = ':' + em.name + ':';
                    img.onclick = function(){ insertEmoji(':' + em.name + ':'); };
                    picker.appendChild(img);
                });
            }
        });
    };
    window.insertEmoji = function(e) {
        var inp = document.getElementById('chatbox-input');
        inp.value += e;
        inp.focus();
        document.getElementById('emoji-picker').style.display = 'none';
    };

    // === REACTIONS ===
    function loadReactions(msgs) {
        var ids = msgs.map(function(m){ return m.id; }).join(',');
        if (!ids) return;
        fetch(apiBase + '?action=get_reactions&ids=' + encodeURIComponent(ids)).then(function(r){ return r.json(); }).then(function(reactions){
            if (!reactions || !reactions.length) return;
            reactions.forEach(function(rx){
                var box = document.getElementById('rx-' + rx.message_id);
                if (box) box.innerHTML += '<span style="background:'+bgColor+';border:1px solid '+(accentColor)+'33;border-radius:8px;padding:1px 5px;font-size:10px;cursor:pointer;margin-right:3px" onclick="reactToMsg('+rx.message_id+',\''+rx.emoji+'\')">'+rx.emoji+' '+rx.count+'</span>';
            });
        }).catch(function(){});
    }
    window.addReaction = function(msgId) {
        var picker = document.getElementById('emoji-picker');
        var h = '';
        emojis.forEach(function(e){ h += '<span style="font-size:16px;cursor:pointer;display:inline-block;padding:2px;margin:2px;border-radius:4px" onclick="reactToMsg('+msgId+',\''+e+'\')">'+e+'</span>'; });
        picker.innerHTML = h;
        picker.style.display = 'block';
    };
    window.reactToMsg = function(msgId, emoji) {
        var fd = new FormData();
        fd.append('action', 'react');
        fd.append('message_id', msgId);
        fd.append('user_id', currentUser && currentUser.userId ? currentUser.userId : 0);
        fd.append('username', currentUser ? currentUser.username : 'Guest');
        fd.append('emoji', emoji);
        fetch(apiBase, {method:'POST', body: fd}).then(function(){ document.getElementById('emoji-picker').style.display='none'; fetchMessages(); });
    };

    // === VOICE (WebRTC) ===
    var voicePeers = {}, localStream = null, pcMap = {}, signalAfter = 0, voiceOn = false;
    var stunConfig = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] };

    function signalPost(type, payload) {
        var fd = new FormData();
        fd.append('action', 'signal');
        fd.append('tenant_id', tenantId);
        fd.append('room_id', currentRoomId);
        fd.append('user_id', currentUser && currentUser.userId ? currentUser.userId : 0);
        fd.append('username', currentUser ? currentUser.username : 'Guest');
        fd.append('type', type);
        fd.append('payload', JSON.stringify(payload));
        fetch(apiBase, {method:'POST', body: fd});
    }

    window.toggleVoice = function() {
        var btn = document.getElementById('voiceBtn');
        var vcRow = document.getElementById('chatbox-vc');
        if (voiceOn) {
            voiceOn = false;
            if (localStream) { localStream.getTracks().forEach(function(t){ t.stop(); }); localStream = null; }
            Object.keys(pcMap).forEach(function(k){ if (pcMap[k]) pcMap[k].close(); });
            pcMap = {};
            signalPost('leave', {});
            btn.style.background = 'none';
            btn.style.border = '1px solid ' + accentColor + '44';
            if (audioCtx) { audioCtx.close(); audioCtx = null; analyser = null; }
            return;
        }
        navigator.mediaDevices.getUserMedia({ audio: true, video: false }).then(function(stream){
            localStream = stream;
            voiceOn = true;
            btn.style.background = accentColor + '33';
            btn.style.border = '1px solid ' + accentColor;
            startMeter();
            // Announce join
            signalPost('join', {});
            pollSignals();
        }).catch(function(e){ alert('Voice error: ' + e.message); });
    };

    function pollSignals() {
        if (!voiceOn) return;
        fetch(apiBase + '?action=get_signals&room_id=' + currentRoomId + '&user_id=' + (currentUser && currentUser.userId ? currentUser.userId : 0) + '&after=' + signalAfter).then(function(r){ return r.json(); }).then(function(signals){
            if (signals && signals.length) {
                signals.forEach(function(s){
                    signalAfter = Math.max(signalAfter, s.id);
                    if (s.type === 'offer') handleOffer(s);
                    else if (s.type === 'answer') handleAnswer(s);
                    else if (s.type === 'ice') handleIce(s);
                    else if (s.type === 'join') { if (!pcMap[s.user_id]) createPeer(s.user_id); }
                    else if (s.type === 'leave') { if (pcMap[s.user_id]) { pcMap[s.user_id].close(); delete pcMap[s.user_id]; } }
                });
            }
            setTimeout(pollSignals, 2000);
        }).catch(function(){ setTimeout(pollSignals, 3000); });
    }

    function createPeer(remoteId) {
        var pc = new RTCPeerConnection(stunConfig);
        pcMap[remoteId] = pc;
        if (localStream) localStream.getTracks().forEach(function(t){ pc.addTrack(t, localStream); });
        pc.onicecandidate = function(e){ if (e.candidate) signalPost('ice', { to: remoteId, candidate: e.candidate }); };
        pc.ontrack = function(e){ playRemoteAudio(remoteId, e.streams[0]); };
        return pc;
    }

    function playRemoteAudio(remoteId, stream) {
        var existing = document.getElementById('remote-audio-' + remoteId);
        if (!existing) {
            existing = document.createElement('audio');
            existing.id = 'remote-audio-' + remoteId;
            existing.autoplay = true;
            document.body.appendChild(existing);
        }
        existing.srcObject = stream;
        // If stream has video, show in cam view
        var hasVideo = stream.getVideoTracks && stream.getVideoTracks().length > 0;
        if (hasVideo) {
            var camView = document.getElementById('chatbox-camview');
            camView.classList.add('open');
            var v = document.getElementById('remote-video-' + remoteId);
            if (!v) {
                v = document.createElement('video');
                v.id = 'remote-video-' + remoteId;
                v.autoplay = true;
                v.playsinline = true;
                v.style.width = '80px'; v.style.height = '60px';
                camView.appendChild(v);
            }
            v.srcObject = stream;
            camMap[remoteId] = true;
            renderOnline();
        }
    }

    function handleOffer(s) {
        var pc = pcMap[s.user_id] || createPeer(s.user_id);
        var offer = JSON.parse(s.payload);
        pc.setRemoteDescription(offer).then(function(){
            return pc.createAnswer();
        }).then(function(answer){
            return pc.setLocalDescription(answer);
        }).then(function(){
            signalPost('answer', { to: s.user_id, answer: pc.localDescription });
        });
    }

    function handleAnswer(s) {
        var pc = pcMap[s.user_id];
        if (!pc) return;
        var answer = JSON.parse(s.payload);
        pc.setRemoteDescription(answer);
    }

    function handleIce(s) {
        var pc = pcMap[s.user_id];
        if (!pc) return;
        var ice = JSON.parse(s.payload);
        pc.addIceCandidate(ice.candidate);
    }

    // === CAMERA (WebRTC) ===
    var camOn = false;
    window.toggleCam = function() {
        var video = document.getElementById('local-video');
        var container = document.getElementById('chatbox-video');
        if (camOn) { stopCam(); return; }
        navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(function(stream){
            localStream = stream;
            camOn = true;
            container.style.display = 'block';
            video.srcObject = stream;
            if (currentUser) camMap[currentUser.username] = true;
            renderOnline();
            // Recreate peers to add video track
            Object.keys(pcMap).forEach(function(k){ if (pcMap[k]) pcMap[k].close(); });
            pcMap = {};
            signalPost('join', {});
        }).catch(function(e){ alert('Camera error: ' + e.message); });
    };
    window.stopCam = function() {
        camOn = false;
        if (localStream) { localStream.getTracks().forEach(function(t){ t.stop(); }); localStream = null; }
        document.getElementById('chatbox-video').style.display = 'none';
        if (currentUser) delete camMap[currentUser.username];
        renderOnline();
        Object.keys(pcMap).forEach(function(k){ if (pcMap[k]) pcMap[k].close(); });
        pcMap = {};
        signalPost('leave', {});
    };

    // === USER PROFILE CACHE ===
    var profileCache = {};
    function getUserProfile(username) {
        // Current user's profile
        if (currentUser && username === currentUser.username) return myProfile;
        return profileCache[username] || { display_name:'', avatar:'', font_style:'', font_color:'', font_size:13 };
    }
    function cacheProfiles(usernames) {
        var fresh = usernames.filter(function(u){ return u && !profileCache[u] && !(currentUser && u === currentUser.username); });
        if (!fresh.length) return;
        fresh.forEach(function(uname){
            var fd = new FormData();
            fd.append('action', 'get_profile_by_username');
            fd.append('room_id', currentRoomId);
            fd.append('username', uname);
            fetch(apiBase, {method:'POST', body: fd}).then(function(r){ return r.json(); }).then(function(p){
                if (p && !p.error) profileCache[uname] = { display_name:p.display_name||'', avatar:p.avatar||'', font_style:p.font_style||'', font_color:p.font_color||'', font_size:p.font_size||13 };
            }).catch(function(){});
        });
    }

    // === RESIZABLE ONLINE SIDEBAR ===
    var resizeHandle = document.getElementById('chatbox-resize');
    if (resizeHandle) {
        resizeHandle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            resizeHandle.classList.add('active');
            var panel = document.getElementById('chatbox-online');
            var startX = e.clientX;
            var startW = panel.offsetWidth;
            function onMove(ev) {
                var newW = startW - (ev.clientX - startX);
                if (newW < 60) newW = 60;
                if (newW > 280) newW = 280;
                panel.style.width = newW + 'px';
            }
            function onUp() {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                resizeHandle.classList.remove('active');
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    }

    // === ONLINE USERS ===
    var onlineUsers = [];
    function joinOnline() {
        var fd = new FormData();
        fd.append('action', 'join_online');
        fd.append('room_id', currentRoomId);
        fd.append('user_id', currentUser && currentUser.userId ? currentUser.userId : 0);
        fd.append('username', currentUser ? currentUser.username : 'Guest');
        fetch(apiBase, {method:'POST', body: fd});
        pollOnline();
    }
    function pollOnline() {
        fetch(apiBase + '?action=room_users&room_id=' + currentRoomId).then(function(r){ return r.json(); }).then(function(list){
            if (list && list.length) {
                onlineUsers = list;
                renderOnline();
            }
            setTimeout(pollOnline, 5000);
        }).catch(function(){ setTimeout(pollOnline, 8000); });
    }
    function renderOnline() {
        var el = document.getElementById('chatbox-online-list');
        var myName = currentUser ? currentUser.username : '';
        var h = '';
        var isStaff = currentUser && (currentUser.role === 'owner' || currentUser.role === 'admin' || currentUser.role === 'moderator');
        onlineUsers.forEach(function(u){
            var isMe = u.username === myName;
            var onCam = camMap[u.username] || (isMe && camOn);
            h += '<div class="ou" onclick="userMenu(\'' + escAttr(u.username) + '\')">' +
                '<span class="cam-dot" style="background:' + (onCam ? '#4ade80' : (isMe ? '#38bdf8' : '#64748b')) + '" title="' + (onCam ? 'On cam' : '') + '"></span>' +
                '<span style="flex:1;overflow:hidden;text-overflow:ellipsis">' + escHtml(u.username) + (isMe ? ' (you)' : '') + '</span>' +
                '</div>';
        });
        el.innerHTML = h || '<div style="padding:8px;font-size:10px;color:'+textColor+'66">No one online</div>';
    }

    // === USER CONTEXT MENU ===
    var camMap = {};
    window.userMenu = function(username) {
        closeCtxMenu();
        var myName = currentUser ? currentUser.username : '';
        var isMe = username === myName;
        var myRole = currentUser ? (currentUser.role || 'guest') : 'guest';
        var canMod = myRole === 'owner' || myRole === 'admin' || myRole === 'moderator';
        var h = '<div id="chatbox-ctxmenu">';
        h += '<div class="ctx-item" onclick="dmUser(\'' + escAttr(username) + '\')">💬 Message</div>';
        if (onCam && !isMe) h += '<div class="ctx-item" onclick="watchCam(\'' + escAttr(username) + '\')">📹 Watch cam</div>';
        if (!isMe && canMod) {
            h += '<div class="ctx-item" onclick="modAction(\'kick\',\'' + escAttr(username) + '\')">👢 Kick</div>';
            h += '<div class="ctx-item" onclick="modAction(\'ban\',\'' + escAttr(username) + '\')">🚫 Ban</div>';
            h += '<div class="ctx-item danger" onclick="modAction(\'mute\',\'' + escAttr(username) + '\')">🔇 Mute</div>';
        }
        h += '</div>';
        document.body.insertAdjacentHTML('beforeend', h);
        var menu = document.getElementById('chatbox-ctxmenu');
        menu.style.top = (event && event.pageY ? event.pageY : 100) + 'px';
        menu.style.left = (event && event.pageX ? event.pageX : 100) + 'px';
        setTimeout(function(){ document.addEventListener('click', closeCtxMenu, {once:true}); }, 50);
    };
    window.closeCtxMenu = function() { var m = document.getElementById('chatbox-ctxmenu'); if (m) m.remove(); };
    window.dmUser = function(username) {
        closeCtxMenu();
        var msg = prompt('Message ' + username + ':');
        if (!msg) return;
        var fd = new FormData();
        fd.append('action', 'send_message');
        fd.append('room_id', currentRoomId);
        fd.append('tenant_id', tenantId);
        fd.append('username', currentUser ? currentUser.username : 'Guest');
        fd.append('message', '[DM to ' + username + '] ' + msg);
        fetch(apiBase, {method:'POST', body: fd});
    };
    window.watchCam = function(username) {
        closeCtxMenu();
        // Open a video element for this user's remote stream if it exists
        var v = document.getElementById('remote-video-' + username.replace(/[^a-z0-9]/gi, ''));
        if (v) {
            document.getElementById('chatbox-camview').classList.add('open');
            var clone = v.cloneNode();
            clone.id = '';
            clone.style.width = '160px'; clone.style.height = '120px';
            document.getElementById('chatbox-camview').appendChild(clone);
            clone.srcObject = v.srcObject;
        } else { alert(username + ' is not sharing cam to you yet.'); }
    };
    window.modAction = function(action, username) {
        closeCtxMenu();
        if (!confirm(action + ' ' + username + '?')) return;
        // Send as system message
        var fd = new FormData();
        fd.append('action', 'send_message');
        fd.append('room_id', currentRoomId);
        fd.append('tenant_id', tenantId);
        fd.append('username', currentUser ? currentUser.username : 'Guest');
        fd.append('message', '[' + action + '] ' + username + ' by ' + (currentUser ? currentUser.username : 'mod'));
        fetch(apiBase, {method:'POST', body: fd});
    };
    function escAttr(s) { return (s || '').replace(/['"\\]/g, '\\$&'); }

    // === TALK / MUTE / VC METER ===
    var isMuted = false, audioCtx = null, analyser = null;
    window.toggleMute = function() {
        isMuted = !isMuted;
        var btn = document.getElementById('muteBtn');
        btn.textContent = isMuted ? '🔇' : '🔊';
        btn.classList.toggle('active', isMuted);
        if (localStream) localStream.getAudioTracks().forEach(function(t){ t.enabled = !isMuted; });
    };
    window.talkStart = function() {
        if (!voiceOn) { if (window.toggleVoice) { toggleVoice(); return; } }
        if (!localStream) return;
        if (!isMuted) localStream.getAudioTracks().forEach(function(t){ t.enabled = true; });
        var btn = document.getElementById('talkBtn');
        btn.classList.add('active');
    };
    window.talkStop = function() {
        if (!localStream) return;
        if (!isMuted) localStream.getAudioTracks().forEach(function(t){ t.enabled = false; });
        var btn = document.getElementById('talkBtn');
        btn.classList.remove('active');
    };
    function startMeter() {
        if (!localStream || audioCtx) return;
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioCtx.createAnalyser();
            analyser.fftSize = 256;
            audioCtx.createMediaStreamSource(localStream).connect(analyser);
            meterLoop();
        } catch(e) {}
    }
    function meterLoop() {
        if (!analyser) return;
        var data = new Uint8Array(analyser.frequencyBinCount);
        analyser.getByteFrequencyData(data);
        var sum = 0; for (var i = 0; i < data.length; i++) sum += data[i];
        var avg = sum / data.length;
        var pct = Math.min(100, (avg / 128) * 100);
        document.getElementById('vc-meter-fill').style.width = pct + '%';
        requestAnimationFrame(meterLoop);
    }

    function escHtml(t) { var d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML; }

        // Check if session exists
        fetch(apiBase + '?action=guest_check&tenant_id=' + tenantId).then(function(r){ return r.json(); }).then(function(d){
            if (d.logged_in) { currentUser = {username: d.username}; onLoggedIn(); }
            else if (guestEnabled) { showGuestLogin(); }
            else if (regEnabled) { showRegisterLogin(); }
            else { showGuestLogin(); }
        });
    } // end initWidget

    // Start when DOM is ready
    if (document.body) { initWidget(); }
    else { document.addEventListener('DOMContentLoaded', initWidget); }
})();
