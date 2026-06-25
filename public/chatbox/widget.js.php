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
    var logoUrl = <?php echo json_encode($tenant->logo_url ?? ''); ?>;
    var guestEnabled = <?php echo $tenant->guest_enabled ? 'true' : 'false'; ?>;
    var regEnabled = <?php echo $tenant->registration_enabled ? 'true' : 'false'; ?>;
    var signalrUrl = 'http://planet-hosts.com/hub/chatbox';
    var rooms = <?php echo json_encode($roomsList); ?>;
    var playerHtml = <?php echo json_encode($tenant->player_html ?? ''); ?>;
    var currentTheme = <?php echo json_encode($tenant->theme ?? 'default'); ?>;
    var customCss = <?php echo json_encode($tenant->custom_css ?? ''); ?>;

    // Theme definitions
    var themes = {
        'default': { accent: '#008cff', bg: '#0a0e1a', text: '#ffffff', input: 'rgba(0,0,0,.2)', msg_bg: 'rgba(255,255,255,.06)', msg_own: 'rgba(0,140,255,.15)', border: 'rgba(255,255,255,.1)' },
        'blue': { accent: '#0066ff', bg: '#001433', text: '#ffffff', input: 'rgba(0,0,0,.3)', msg_bg: 'rgba(255,255,255,.05)', msg_own: 'rgba(0,102,255,.15)', border: 'rgba(0,102,255,.2)' },
        'black': { accent: '#333333', bg: '#000000', text: '#e0e0e0', input: 'rgba(255,255,255,.05)', msg_bg: 'rgba(255,255,255,.04)', msg_own: 'rgba(255,255,255,.08)', border: 'rgba(255,255,255,.06)' },
        'white': { accent: '#0066cc', bg: '#f5f5f5', text: '#222222', input: 'rgba(0,0,0,.05)', msg_bg: 'rgba(0,0,0,.04)', msg_own: 'rgba(0,102,204,.1)', border: 'rgba(0,0,0,.1)' },
        'gray': { accent: '#666666', bg: '#1a1a1a', text: '#cccccc', input: 'rgba(255,255,255,.06)', msg_bg: 'rgba(255,255,255,.04)', msg_own: 'rgba(255,255,255,.08)', border: 'rgba(255,255,255,.08)' },
        'transparent': { accent: '#008cff', bg: 'transparent', text: '#ffffff', input: 'rgba(255,255,255,.1)', msg_bg: 'rgba(255,255,255,.06)', msg_own: 'rgba(0,140,255,.15)', border: 'rgba(255,255,255,.12)' },
        'neon': { accent: '#00ff88', bg: '#001a0a', text: '#00ff88', input: 'rgba(0,255,136,.05)', msg_bg: 'rgba(0,255,136,.04)', msg_own: 'rgba(0,255,136,.12)', border: 'rgba(0,255,136,.2)' },
        'gaming': { accent: '#ff6600', bg: '#0d0d0d', text: '#ff9933', input: 'rgba(255,102,0,.05)', msg_bg: 'rgba(255,102,0,.04)', msg_own: 'rgba(255,102,0,.12)', border: 'rgba(255,102,0,.2)' },
        'hacker': { accent: '#00ff00', bg: '#000a00', text: '#00ff00', input: 'rgba(0,255,0,.05)', msg_bg: 'rgba(0,255,0,.04)', msg_own: 'rgba(0,255,0,.1)', border: 'rgba(0,255,0,.15)' },
        'matrix': { accent: '#00cc41', bg: '#000502', text: '#00cc41', input: 'rgba(0,204,65,.05)', msg_bg: 'rgba(0,204,65,.04)', msg_own: 'rgba(0,204,65,.1)', border: 'rgba(0,204,65,.15)' },
        'discord': { accent: '#5865f2', bg: '#313338', text: '#dbdee1', input: 'rgba(0,0,0,.3)', msg_bg: 'rgba(255,255,255,.04)', msg_own: 'rgba(88,101,242,.15)', border: 'rgba(255,255,255,.06)' },
        'twitch': { accent: '#9146ff', bg: '#0e0e10', text: '#efeff1', input: 'rgba(255,255,255,.05)', msg_bg: 'rgba(255,255,255,.04)', msg_own: 'rgba(145,70,255,.15)', border: 'rgba(255,255,255,.06)' },
        'retro': { accent: '#00ff00', bg: '#000080', text: '#00ff00', input: 'rgba(0,255,0,.1)', msg_bg: 'rgba(0,255,0,.05)', msg_own: 'rgba(0,255,0,.15)', border: 'rgba(0,255,0,.2)' },
        'cyberpunk': { accent: '#f0f', bg: '#0a0014', text: '#fff', input: 'rgba(255,0,255,.05)', msg_bg: 'rgba(255,0,255,.04)', msg_own: 'rgba(255,0,255,.12)', border: 'rgba(255,0,255,.2)' },
        'purple': { accent: '#a855f7', bg: '#0a0014', text: '#e9d5ff', input: 'rgba(168,85,247,.05)', msg_bg: 'rgba(168,85,247,.04)', msg_own: 'rgba(168,85,247,.12)', border: 'rgba(168,85,247,.15)' },
        'red': { accent: '#ef4444', bg: '#140000', text: '#fca5a5', input: 'rgba(239,68,68,.05)', msg_bg: 'rgba(239,68,68,.04)', msg_own: 'rgba(239,68,68,.12)', border: 'rgba(239,68,68,.15)' },
        'gold': { accent: '#f59e0b', bg: '#0a0800', text: '#fbbf24', input: 'rgba(245,158,11,.05)', msg_bg: 'rgba(245,158,11,.04)', msg_own: 'rgba(245,158,11,.12)', border: 'rgba(245,158,11,.15)' },
        'spectre': { accent: '#7c3aed', bg: '#0d001a', text: '#c4b5fd', input: 'rgba(124,58,237,.05)', msg_bg: 'rgba(124,58,237,.04)', msg_own: 'rgba(124,58,237,.12)', border: 'rgba(124,58,237,.15)' },
    };
    // Apply theme
    if (themes[currentTheme]) {
        var t = themes[currentTheme];
        accentColor = t.accent;
        bgColor = t.bg;
        textColor = t.text;
    }

    // Create widget container
    var container = document.createElement('div');
    container.id = 'chatbox-widget';
    if (customCss) {
        var styleTag = document.createElement('style');
        styleTag.textContent = customCss;
        document.head.appendChild(styleTag);
    }
    container.innerHTML = `
        <style>
            #chatbox-widget * { margin:0; padding:0; box-sizing:border-box; font-family:${fontFamily}; }
            #chatbox-toggle {
                position:fixed; bottom:20px; right:20px; z-index:999999;
                width:56px; height:56px; border-radius:50%;
                background:${accentColor}; color:#fff; border:none;
                cursor:pointer; box-shadow:0 4px 20px rgba(0,0,0,.3);
                display:flex; align-items:center; justify-content:center;
                font-size:24px; transition:transform .2s;
            }
            #chatbox-toggle:hover { transform:scale(1.1); }
            #chatbox-panel {
                position:fixed; bottom:84px; right:20px; z-index:999998;
                width:360px; height:500px; border-radius:12px;
                background:${bgColor}; color:${textColor};
                box-shadow:0 4px 30px rgba(0,0,0,.4);
                display:none; flex-direction:column; overflow:hidden;
                border:1px solid rgba(255,255,255,.1);
            }
            #chatbox-panel.open { display:flex; }
            .cb-header {
                padding:14px 16px; background:${accentColor}; color:#fff;
                display:flex; justify-content:space-between; align-items:center;
                font-weight:600; font-size:14px;
            }
            .cb-header button { background:none; border:none; color:#fff; cursor:pointer; font-size:18px; }
            .cb-msgs { flex:1; overflow-y:auto; padding:12px; }
            .cb-msg { margin-bottom:10px; display:flex; gap:8px; align-items:start; }
            .cb-msg .bubble {
                display:inline-block; padding:8px 12px; border-radius:12px;
                font-size:13px; line-height:1.5; max-width:85%; word-break:break-word;
            }
            .cb-msg .meta { font-size:10px; color:rgba(255,255,255,.4); margin-top:2px; }
            .cb-input { display:flex; gap:8px; padding:10px 12px; border-top:1px solid rgba(255,255,255,.08); }
            .cb-input { display:flex; gap:6px; padding:10px 12px; border-top:1px solid rgba(255,255,255,.08); flex-wrap:wrap; }
            .cb-input .cb-input-row { display:flex; gap:6px; flex:1; width:100%; }
            .cb-input input {
                flex:1; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,.1);
                background:rgba(0,0,0,.2); color:${textColor}; outline:none; font-size:13px; min-width:0;
            }
            .cb-input button {
                padding:8px 14px; border-radius:8px; border:none;
                background:${accentColor}; color:#fff; cursor:pointer; font-size:13px; white-space:nowrap;
            }
            .cb-voice-btn {
                padding:4px 10px; border-radius:16px; border:1px solid rgba(255,255,255,.15);
                background:transparent; color:${textColor}; cursor:pointer; font-size:11px;
            }
            .cb-online { font-size:11px; color:rgba(255,255,255,.5); padding:4px 12px; }
            .cb-join { padding:24px; text-align:center; }
            .cb-join input { width:100%; padding:10px; margin-bottom:8px; border-radius:8px; border:1px solid rgba(255,255,255,.1); background:rgba(0,0,0,.2); color:#fff; outline:none; }
            .cb-join button { width:100%; padding:10px; border-radius:8px; border:none; background:${accentColor}; color:#fff; cursor:pointer; font-weight:600; }
            .cb-room-select { padding:8px 12px; display:flex; gap:6px; overflow-x:auto; }
            .cb-room-select button {
                padding:4px 12px; border-radius:16px; border:1px solid rgba(255,255,255,.15);
                background:transparent; color:${textColor}; cursor:pointer; font-size:12px; white-space:nowrap;
            }
            .cb-room-select button.active { background:${accentColor}; border-color:${accentColor}; color:#fff; }
            .cb-msg-img { max-width:200px; border-radius:8px; cursor:pointer; }
            .cb-msg.system { justify-content:center; }
            .cb-msg.system .bubble { background:rgba(255,255,255,.05); font-size:11px; color:rgba(255,255,255,.4); }
            .cb-player { padding:8px 12px; border-bottom:1px solid rgba(255,255,255,.08); }
            .cb-player iframe { width:100%; max-height:180px; border-radius:8px; border:none; }
            .cb-player audio { width:100%; border-radius:6px; }
        </style>
        <button id="chatbox-toggle">💬</button>
        <div id="chatbox-panel">
            <div class="cb-header">
                <span id="cb-title">${widgetTitle}</span>
                <button onclick="document.getElementById('chatbox-panel').classList.remove('open')">✕</button>
            </div>
            <div class="cb-player" id="cb-player" style="display:none"></div>
            <div class="cb-room-select" id="cb-rooms"></div>
            <div class="cb-online" id="cb-online">0 online</div>
            <div id="cb-join" class="cb-join"></div>
            <div class="cb-msgs" id="cb-msgs" style="display:none"></div>
            <div class="cb-input" id="cb-input" style="display:none">
                <div class="cb-input-row">
                    <input id="cb-msg-input" placeholder="Type a message..." onkeydown="if(event.key==='Enter')cbSendMsg()">
                    <button onclick="cbSendMsg()">Send</button>
                </div>
                <div style="display:flex;gap:4px;width:100%">
                    <button id="cb-voice-btn" class="cb-voice-btn" onclick="cbToggleVoice()">🎤 Voice</button>
                    <button class="cb-voice-btn" onclick="cbToggleEmojiPicker()">😊 Emoji</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(container);

    var connection = null;
    var currentUser = null;
    var currentRoom = rooms.length > 0 ? rooms[0].id : null;
    var chatboxSignalrUrl = signalrUrl;

    // Toggle
    document.getElementById('chatbox-toggle').onclick = function() {
        var panel = document.getElementById('chatbox-panel');
        panel.classList.toggle('open');
        if (panel.classList.contains('open') && !currentUser) showJoinForm();
    };

    function showJoinForm() {
        var div = document.getElementById('cb-join');
        div.style.display = 'block';
        document.getElementById('cb-msgs').style.display = 'none';
        document.getElementById('cb-input').style.display = 'none';
        var html = '';
        if (guestEnabled) {
            html += '<input id="cb-guest-name" placeholder="Nickname" maxlength="20"><button onclick="cbJoinAsGuest()">Join as Guest</button>';
        }
        if (regEnabled) {
            html += '<hr style="margin:12px 0;border-color:rgba(255,255,255,.08)"><input id="cb-reg-user" placeholder="Username"><input id="cb-reg-pass" type="password" placeholder="Password"><button onclick="cbLogin()">Login</button>';
            html += '<br><br><button onclick="cbShowRegister()" style="font-size:12px;background:transparent;color:' + accentColor + '">Create Account</button>';
        }
        div.innerHTML = html;
    }

    window.cbJoinAsGuest = function() {
        var name = document.getElementById('cb-guest-name').value.trim() || 'Guest_' + Math.random().toString(36).substr(2,4);
        currentUser = { username: name, displayName: name, role: 'guest', userId: 0 };
        connectSignalR();
        document.getElementById('cb-join').style.display = 'none';
        document.getElementById('cb-msgs').style.display = 'block';
        document.getElementById('cb-input').style.display = 'flex';
    };

    function connectSignalR() {
        var script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/microsoft-signalr/8.0.0/signalr.min.js';
        script.onload = function() {
            connection = new signalR.HubConnectionBuilder()
                .withUrl(chatboxSignalrUrl)
                .withAutomaticReconnect()
                .build();

            connection.on('NewMessage', function(msg) { addMessage(msg); });
            connection.on('UserJoined', function(u) { addSystemMsg(u.displayName + ' joined'); });
            connection.on('UserLeft', function(u) { addSystemMsg(u.username + ' left'); });
            connection.on('OnlineUsers', function(users) {
                document.getElementById('cb-online').textContent = users.length + ' online';
            });
            connection.on('UserBanned', function(data) {
                if (data.username === currentUser.username) {
                    alert('You have been banned: ' + (data.reason || 'No reason'));
                    location.reload();
                }
            });
            connection.on('MessageDeleted', function(data) {
                var el = document.querySelector('[data-msg-id="' + data.messageId + '"]');
                if (el) el.remove();
            });
            connection.on('MessageReacted', function(data) { addSystemMsg(data.username + ' reacted with ' + data.emoji); });
            connection.on('PrivateMessage', function(data) {
                alert('💬 Private message from ' + data.from + ': ' + data.message);
                addSystemMsg('Private from ' + data.from + ': ' + data.message);
            });
            connection.on('ModeratorAction', function(data) {
                addSystemMsg('🔨 ' + data.by + ' ' + data.action + ' ' + data.target + (data.reason ? ' (' + data.reason + ')' : ''));
                if ((data.action === 'ban' || data.action === 'kick') && data.target === (currentUser ? currentUser.username : '')) {
                    alert('You have been ' + data.action + (data.reason ? ': ' + data.reason : ''));
                    if (data.action === 'ban') setTimeout(function() { location.reload(); }, 2000);
                }
            });

            connection.start().then(function() {
                if (currentRoom) {
                    connection.invoke('JoinRoom', tenantId.toString(), currentRoom.toString(),
                        currentUser.username, currentUser.displayName, currentUser.role, currentUser.userId);
                }
                renderRooms();
                setupVoiceHandlers();
                if (playerHtml) {
                    var playerDiv = document.getElementById('cb-player');
                    playerDiv.innerHTML = playerHtml;
                    playerDiv.style.display = 'block';
                }
            });
        };
        document.head.appendChild(script);
    }

    function renderRooms() {
        var div = document.getElementById('cb-rooms');
        div.innerHTML = '';
        rooms.forEach(function(r) {
            var btn = document.createElement('button');
            btn.textContent = r.name;
            btn.className = r.id === currentRoom ? 'active' : '';
            btn.onclick = function() { switchRoom(r.id); };
            div.appendChild(btn);
        });
    }

    function switchRoom(roomId) {
        if (connection && connection.state === 'Connected') {
            connection.invoke('LeaveRoom', tenantId.toString(), currentRoom.toString());
        }
        currentRoom = roomId;
        document.getElementById('cb-msgs').innerHTML = '';
        if (connection && connection.state === 'Connected') {
            connection.invoke('JoinRoom', tenantId.toString(), roomId.toString(),
                currentUser.username, currentUser.displayName, currentUser.role, currentUser.userId);
        }
        renderRooms();
    }

    window.cbSendMsg = function() {
        var input = document.getElementById('cb-msg-input');
        var msg = input.value.trim();
        if (!msg || !connection || !currentRoom) return;
        connection.invoke('SendMessage', tenantId.toString(), currentRoom.toString(), msg, 'text', '');
        input.value = '';
    };

    function addMessage(msg) {
        var div = document.getElementById('cb-msgs');
        var el = document.createElement('div');
        el.className = 'cb-msg' + (msg.messageType === 'system' ? ' system' : '');
        el.setAttribute('data-msg-id', msg.id);
        var isMine = msg.username === (currentUser ? currentUser.username : '');
        var roleBadge = '';
        if (msg.role === 'owner') roleBadge = '<span style="color:#facc15;font-size:10px;margin-left:4px" title="Owner">👑</span>';
        else if (msg.role === 'admin') roleBadge = '<span style="color:#ef4444;font-size:10px;margin-left:4px" title="Admin">🛡️</span>';
        else if (msg.role === 'mod') roleBadge = '<span style="color:#38bdf8;font-size:10px;margin-left:4px" title="Mod">⚔️</span>';

        if (msg.messageType === 'system') {
            el.innerHTML = '<div class="bubble" style="font-size:11px;color:rgba(255,255,255,.4);background:transparent;text-align:center;width:100%">' + msg.message + '</div>';
        } else {
            el.innerHTML = '<div style="display:flex;flex-direction:column;width:100%">' +
                '<div style="font-size:10px;color:' + (accentColor || '#008cff') + ';margin-bottom:2px">' +
                (msg.displayName || msg.username) + roleBadge +
                ' <span style="color:#64748b;font-size:9px">' + (msg.createdAt ? new Date(msg.createdAt).toLocaleTimeString() : '') + '</span></div>' +
                '<div class="bubble">' + (msg.message || '').replace(/</g,'&lt;') + '</div>' +
                '<div style="display:flex;gap:4px;margin-top:2px;font-size:10px">' +
                '<span onclick="cbReactToMessage(' + msg.id + ',\'👍\')" style="cursor:pointer;color:#64748b">👍</span>' +
                '<span onclick="cbReactToMessage(' + msg.id + ',\'❤️\')" style="cursor:pointer;color:#64748b">❤️</span>' +
                '<span onclick="cbReactToMessage(' + msg.id + ',\'😂\')" style="cursor:pointer;color:#64748b">😂</span>' +
                (!isMine ? '<span onclick="cbPrivateMessage(\'' + (msg.username||'') + '\')" style="cursor:pointer;color:#64748b" title="Private reply">💬</span>' : '') +
                (currentUser && (currentUser.role === 'mod' || currentUser.role === 'admin' || currentUser.role === 'owner') ?
                    '<span onclick="cbDeleteMessage(' + msg.id + ')" style="cursor:pointer;color:#f87171" title="Delete">🗑</span>' +
                    '<span onclick="cbKickUser(\'' + (msg.username||'') + '\')" style="cursor:pointer;color:#facc15" title="Kick">👢</span>' +
                    '<span onclick="cbBanUser(\'' + (msg.username||'') + '\')" style="cursor:pointer;color:#ef4444" title="Ban">🚫</span>' +
                    '<span onclick="cbMuteUser(\'' + (msg.username||'') + '\')" style="cursor:pointer;color:#f59e0b" title="Mute">🔇</span>'
                : '') +
                '</div></div>';
        }
        div.appendChild(el);
        div.scrollTop = div.scrollHeight;
    }

    function addSystemMsg(text) {
        addMessage({ id: Date.now(), message: text, messageType: 'system', username: '' });
    }

    window.cbLogin = function() {
        var user = document.getElementById('cb-reg-user').value.trim();
        var pass = document.getElementById('cb-reg-pass').value.trim();
        if (!user || !pass) return;
        var x = new XMLHttpRequest();
        x.open('POST', '/chatbox/api.php?action=login', true);
        x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        x.onload = function() {
            try {
                var r = JSON.parse(x.responseText);
                if (r.success) {
                    currentUser = { username: r.username, displayName: r.displayName || r.username, role: r.role, userId: r.userId };
                    connectSignalR();
                    document.getElementById('cb-join').style.display = 'none';
                    document.getElementById('cb-msgs').style.display = 'block';
                    document.getElementById('cb-input').style.display = 'flex';
                } else { alert(r.error || 'Login failed'); }
            } catch(e) { alert('Login error'); }
        };
        x.send('tenant_id=' + tenantId + '&username=' + encodeURIComponent(user) + '&password=' + encodeURIComponent(pass));
    };

    window.cbShowRegister = function() {
        var div = document.getElementById('cb-join');
        div.innerHTML = '<h3 style="margin-bottom:12px;font-size:14px">Create Account</h3>'
            + '<input id="cb-reg-user2" placeholder="Username (3+ chars)">'
            + '<input id="cb-reg-pass2" type="password" placeholder="Password">'
            + '<input id="cb-reg-email" placeholder="Email (optional)">'
            + '<button onclick="cbRegister()">Register</button>'
            + '<br><br><button onclick="showJoinForm()" style="font-size:12px;background:transparent;color:' + accentColor + '">Back</button>';
    };

    // ─── Emoji Picker ───
    var emojiList = ['😊','😂','❤️','👍','🎉','😍','🤔','👋','🔥','💀','😎','🙏','💯','⭐','🎶','😢','😡','🥳','🤯','😱','🤗','🤩','😈','👽','💩','🎸','🥺','😴','🤠','👾'];

    window.cbToggleEmojiPicker = function() {
        var picker = document.getElementById('cb-emoji-picker');
        if (picker) { picker.remove(); return; }
        picker = document.createElement('div');
        picker.id = 'cb-emoji-picker';
        picker.style.cssText = 'position:absolute;bottom:100px;left:12px;background:' + bgColor + ';border:1px solid ' + accentColor + ';border-radius:10px;padding:10px;display:grid;grid-template-columns:repeat(8,1fr);gap:4px;z-index:999;box-shadow:0 5px 20px rgba(0,0,0,.5)';
        emojiList.forEach(function(e) {
            var s = document.createElement('span');
            s.textContent = e;
            s.style.cssText = 'cursor:pointer;font-size:22px;padding:4px;text-align:center;border-radius:4px';
            s.onclick = function() { document.getElementById('cb-msg-input').value += e; document.getElementById('cb-msg-input').focus(); picker.remove(); };
            picker.appendChild(s);
        });
        document.getElementById('cb-input').appendChild(picker);
    };

    // ─── Message Actions (reply, react, report, delete) ───
    window.cbReactToMessage = function(msgId, emoji) {
        if (connection && connection.state === 'Connected')
            connection.invoke('ReactToMessage', tenantId.toString(), currentRoom.toString(), msgId, emoji);
    };

    window.cbDeleteMessage = function(msgId) {
        if (connection && connection.state === 'Connected')
            connection.invoke('DeleteMessage', tenantId.toString(), currentRoom.toString(), msgId);
    };

    window.cbReportMessage = function(msgId) {
        var reason = prompt('Reason for reporting this message?');
        if (reason && connection && connection.state === 'Connected')
            connection.invoke('ModeratorAction', tenantId.toString(), currentRoom.toString(), 'report', msgId.toString(), reason);
    };

    window.cbPrivateMessage = function(username) {
        var msg = prompt('Private message to ' + username + ':');
        if (msg && connection && connection.state === 'Connected')
            connection.invoke('SendPrivateMessage', tenantId.toString(), username, msg);
    };

    window.cbKickUser = function(username) { if (confirm('Kick ' + username + '?')) connection.invoke('ModeratorAction', tenantId.toString(), currentRoom.toString(), 'kick', username, ''); };
    window.cbBanUser = function(username) { var r = prompt('Ban reason for ' + username + ':'); if (r) connection.invoke('ModeratorAction', tenantId.toString(), currentRoom.toString(), 'ban', username, r); };
    window.cbMuteUser = function(username) { var r = prompt('Mute reason for ' + username + ':'); if (r) connection.invoke('ModeratorAction', tenantId.toString(), currentRoom.toString(), 'mute', username, r); };

    // ─── Voice Call ───
    var localStream = null;
    var pc = null;
    var voiceActive = false;

    window.cbToggleVoice = function() {
        if (voiceActive) { cbStopVoice(); return; }
        if (!connection || connection.state !== 'Connected') return;
        if (!playerHtml && voiceEnabled !== '1' && voiceEnabled !== true) {
            // Check tenant voice_enabled from the page context
        }
        navigator.mediaDevices.getUserMedia({audio: true, video: false}).then(function(stream) {
            localStream = stream;
            pc = new RTCPeerConnection({iceServers: [{urls: 'stun:stun.l.google.com:19302'}]});
            stream.getTracks().forEach(function(t) { pc.addTrack(t, localStream); });

            pc.onicecandidate = function(e) {
                if (e.candidate && connection.state === 'Connected')
                    connection.invoke('VoiceICECandidate', tenantId.toString(), currentRoom.toString(), JSON.stringify(e.candidate));
            };

            pc.ontrack = function(e) {
                var audio = document.createElement('audio');
                audio.srcObject = e.streams[0];
                audio.autoplay = true;
                audio.style.display = 'none';
                document.body.appendChild(audio);
            };

            pc.createOffer().then(function(offer) {
                pc.setLocalDescription(offer);
                connection.invoke('VoiceOffer', tenantId.toString(), currentRoom.toString(), offer.sdp);
            });

            voiceActive = true;
            document.getElementById('cb-voice-btn').textContent = '🔊 Hang Up';
            document.getElementById('cb-voice-btn').style.background = '#ef4444';
            addSystemMsg('Voice call started...');
        }).catch(function() { addSystemMsg('Microphone access denied'); });
    };

    function cbStopVoice() {
        if (pc) { pc.close(); pc = null; }
        if (localStream) { localStream.getTracks().forEach(function(t) { t.stop(); }); localStream = null; }
        voiceActive = false;
        var btn = document.getElementById('cb-voice-btn');
        if (btn) { btn.textContent = '🎤 Voice'; btn.style.background = ''; }
        addSystemMsg('Voice call ended');
    }

    // Handle incoming voice signaling
    // These are set up in connectSignalR after connection is established
    function setupVoiceHandlers() {
        if (!connection) return;
        connection.off('VoiceOffer');
        connection.off('VoiceAnswer');
        connection.off('VoiceICECandidate');

        connection.on('VoiceOffer', async function(sdp) {
            if (!localStream) {
                try {
                    localStream = await navigator.mediaDevices.getUserMedia({audio: true, video: false});
                } catch(e) { return; }
            }
            pc = new RTCPeerConnection({iceServers: [{urls: 'stun:stun.l.google.com:19302'}]});
            localStream.getTracks().forEach(function(t) { pc.addTrack(t, localStream); });

            pc.onicecandidate = function(e) {
                if (e.candidate && connection.state === 'Connected')
                    connection.invoke('VoiceICECandidate', tenantId.toString(), currentRoom.toString(), JSON.stringify(e.candidate));
            };

            pc.ontrack = function(e) {
                var audio = document.createElement('audio');
                audio.srcObject = e.streams[0];
                audio.autoplay = true;
                audio.style.display = 'none';
                document.body.appendChild(audio);
            };

            await pc.setRemoteDescription(new RTCSessionDescription({type: 'offer', sdp: sdp}));
            var answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await connection.invoke('VoiceAnswer', tenantId.toString(), currentRoom.toString(), answer.sdp);

            voiceActive = true;
            var btn = document.getElementById('cb-voice-btn');
            if (btn) { btn.textContent = '🔊 Hang Up'; btn.style.background = '#ef4444'; }
            addSystemMsg('Voice call connected');
        });

        connection.on('VoiceAnswer', async function(sdp) {
            if (pc) {
                await pc.setRemoteDescription(new RTCSessionDescription({type: 'answer', sdp: sdp}));
                addSystemMsg('Voice call connected');
            }
        });

        connection.on('VoiceICECandidate', async function(candidate) {
            if (pc) {
                try { await pc.addIceCandidate(new RTCIceCandidate(JSON.parse(candidate))); } catch(e) {}
            }
        });
    }

    window.cbRegister = function() {
        var user = document.getElementById('cb-reg-user2').value.trim();
        var pass = document.getElementById('cb-reg-pass2').value.trim();
        var email = document.getElementById('cb-reg-email').value.trim();
        if (!user || user.length < 3 || !pass || pass.length < 4) { alert('Username (3+ chars) and password required'); return; }
        var x = new XMLHttpRequest();
        x.open('POST', '/chatbox/api.php?action=register', true);
        x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        x.onload = function() {
            try {
                var r = JSON.parse(x.responseText);
                if (r.success) {
                    currentUser = { username: r.username, displayName: r.username, role: r.role, userId: 0 };
                    alert('Account created! You are now logged in.');
                    connectSignalR();
                    document.getElementById('cb-join').style.display = 'none';
                    document.getElementById('cb-msgs').style.display = 'block';
                    document.getElementById('cb-input').style.display = 'flex';
                } else { alert(r.error || 'Registration failed'); }
            } catch(e) { alert('Error'); }
        };
        x.send('tenant_id=' + tenantId + '&username=' + encodeURIComponent(user) + '&password=' + encodeURIComponent(pass) + '&email=' + encodeURIComponent(email));
    };
})();

