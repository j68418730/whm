<?php
$room = bin2hex(random_bytes(4));
$signalrUrl = "http://45.61.59.55/hub/chat";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Voice Test — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#02050e;color:#fff;font-family:'Inter',sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:440px;width:92%;text-align:center}
h1{font-size:22px;margin-bottom:6px}h1 span{color:#008cff}
p{color:#64748b;font-size:14px;margin-bottom:20px}
.code-box{background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.2);border-radius:8px;padding:16px;font-family:monospace;font-size:22px;color:#4ade80;margin:16px 0;letter-spacing:3px;font-weight:700}
.btn{padding:12px 28px;border-radius:8px;border:none;font-weight:700;font-size:15px;cursor:pointer;transition:.3s;font-family:'Inter',sans-serif;margin:4px}
.btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 0 25px rgba(0,140,255,.3)}
.btn-danger{background:#ef4444;color:#fff}
.btn-danger:hover{background:#dc2626}
.btn-secondary{background:#333;color:#ccc}
.status{padding:10px;border-radius:8px;margin:12px 0;font-size:13px}
.status.connected{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80}
.status.waiting{background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);color:#fbbf24}
.status.error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
.vu-meter{width:100%;height:6px;background:rgba(255,255,255,.06);border-radius:3px;margin-top:12px;overflow:hidden;display:none}
.vu-meter .bar{height:100%;width:0%;background:linear-gradient(90deg,#4ade80,#facc15,#ef4444);border-radius:3px;transition:width .1s}
</style>
</head>
<body>
<div class="card">
<h1>Voice <span>Test</span></h1>
<p>Admin — Share this room code with the client</p>
<div class="code-box" id="roomCode"><?php echo $room; ?></div>
<button class="btn btn-secondary" onclick="navigator.clipboard.writeText('<?php echo $room; ?>')">📋 Copy Code</button>
<div id="status" class="status waiting">⏳ Connecting to SignalR...</div>
<div class="vu-meter" id="vuMeter"><div class="bar" id="vuBar"></div></div>
<div style="margin-top:16px">
<button id="btnStart" class="btn btn-primary" onclick="startCall()">📞 Start Voice Call</button>
<button id="btnStop" class="btn btn-danger" style="display:none" onclick="stopCall()">✕ End Call</button>
</div>
<p style="margin-top:16px;font-size:12px;color:#64748b">Open <a href="/voice/client.php?room=<?php echo $room; ?>" style="color:#008cff" target="_blank">client page</a> in another tab</p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/microsoft-signalr/8.0.0/signalr.min.js"></script>
<script>
var connection = null;
var pc = null;
var localStream = null;
var roomId = '<?php echo $room; ?>';
var statusEl = document.getElementById('status');
var btnStart = document.getElementById('btnStart');
var btnStop = document.getElementById('btnStop');
var vuBar = document.getElementById('vuBar');
var vuMeter = document.getElementById('vuMeter');

connection = new signalR.HubConnectionBuilder()
    .withUrl('<?php echo $signalrUrl; ?>')
    .withAutomaticReconnect()
    .build();

connection.on('VoiceAnswer', async function(sdp) {
    if (pc) {
        await pc.setRemoteDescription(new RTCSessionDescription({type: 'answer', sdp: sdp}));
        setStatus('connected', '🔊 Connected — voice active');
    }
});

connection.on('VoiceICECandidate', async function(candidate) {
    if (pc) {
        try { await pc.addIceCandidate(new RTCIceCandidate(JSON.parse(candidate))); } catch(e) {}
    }
});

async function startCall() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({audio: true, video: false});
        vuMeter.style.display = 'block';
        // Audio level meter
        var audioCtx = new AudioContext();
        var src = audioCtx.createMediaStreamSource(localStream);
        var analyser = audioCtx.createAnalyser();
        src.connect(analyser);
        var data = new Uint8Array(analyser.frequencyBinCount);
        function meter() {
            analyser.getByteFrequencyData(data);
            var avg = data.reduce((a,b)=>a+b,0) / data.length;
            vuBar.style.width = Math.min(100, avg * 2) + '%';
            requestAnimationFrame(meter);
        }
        meter();

        pc = new RTCPeerConnection({iceServers: [{urls: 'stun:stun.l.google.com:19302'}]});
        localStream.getTracks().forEach(t => pc.addTrack(t, localStream));

        pc.onicecandidate = function(e) {
            if (e.candidate && connection.state === 'Connected')
                connection.invoke('VoiceICECandidate', roomId, JSON.stringify(e.candidate));
        };

        pc.onconnectionstatechange = function() {
            if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed')
                stopCall();
        };

        var offer = await pc.createOffer({offerToReceiveAudio: false});
        await pc.setLocalDescription(offer);
        await connection.invoke('VoiceOffer', roomId, offer.sdp);

        btnStart.style.display = 'none';
        btnStop.style.display = 'inline-block';
        setStatus('waiting', '📞 Waiting for client to answer...');
    } catch(e) {
        setStatus('error', '❌ Microphone access denied');
    }
}

function stopCall() {
    if (pc) { pc.close(); pc = null; }
    if (localStream) { localStream.getTracks().forEach(t => t.stop()); localStream = null; }
    vuMeter.style.display = 'none';
    btnStart.style.display = 'inline-block';
    btnStop.style.display = 'none';
    setStatus('waiting', '⏸ Call ended');
}

function setStatus(cls, msg) {
    statusEl.className = 'status ' + cls;
    statusEl.textContent = msg;
}

connection.start().then(function() {
    connection.invoke('JoinVoiceRoom', roomId);
    setStatus('waiting', '🟢 Connected. Waiting for client...');
}).catch(function(e) {
    setStatus('error', '❌ SignalR: ' + (e.message || e || 'Connection failed'));
});
</script>
</body>
</html>
