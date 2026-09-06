<?php
require_once __DIR__ . '/../../core/ServerCreds.php';
// Remote Desktop Customer Page - connects via WebRTC
$sessionCode = $_GET['code'] ?? '';
$otp = $_GET['otp'] ?? '';

if (!$sessionCode) {
    http_response_code(400);
    die('Session code required');
}

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', \db_user(), \db_pass());

// Verify session
$stmt = $pdo->prepare("SELECT * FROM remote_sessions WHERE session_code = ? AND otp = ? AND status IN ('verified','connecting','connected') AND expires_at > NOW()");
$stmt->execute([$sessionCode, $otp]);
$session = $stmt->fetch(PDO::FETCH_OBJ);

if (!$session) {
    http_response_code(403);
    die('Invalid or expired session');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planet Hosts - Remote Desktop</title>
    <link rel="stylesheet" href="/theme/assets/css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #000;
            font-family: Inter, sans-serif;
            overflow: hidden;
        }
        .bg {
            position: fixed;
            inset: 0;
            background: linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);
            background-size: cover;
            z-index: -2;
        }
        .card {
            background: rgba(8,16,28,.95);
            border: 1px solid rgba(0,191,255,.12);
            border-radius: 16px;
            padding: 24px;
            max-width: 100vw;
            max-height: 100vh;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        h1 { color: #fff; font-size: 18px; margin: 0 0 4px; }
        h1 span { color: #0A84FF; }
        .status { color: #64748b; font-size: 13px; margin: 0 0 16px; }
        .remote-view {
            flex: 1;
            background: #000;
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        .remote-view canvas {
            width: 100%;
            height: 100%;
            display: block;
        }
        .toolbar {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 8px;
            z-index: 10;
        }
        .btn {
            padding: 8px 16px;
            background: linear-gradient(135deg,#008cff,#3bb8ff);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-danger { background: linear-gradient(135deg,#ef4444,#dc2626); }
        .btn-secondary { background: #333; color: #ccc; }
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-connecting { background: rgba(250,204,21,.15); color: #facc15; }
        .status-connected { background: rgba(74,222,128,.15); color: #4ade80; }
        .status-disconnected { background: rgba(248,113,113,.15); color: #f87171; }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
        .loading { display: flex; justify-content: center; align-items: center; height: 100%; color: #64748b; }
        .hidden { display: none !important; }
    </style>
</head>
<body>
<div class="bg"></div>
<div class="card">
    <h1>PLANET-<span>HOSTS</span></h1>
    <p class="status" id="statusText">
        <span class="status-indicator status-connecting" id="statusIndicator">
            <span class="dot"></span> <span id="statusLabel">Connecting...</span>
        </span>
    </p>

    <div class="remote-view" id="remoteView">
        <div class="loading" id="loadingScreen">Initializing connection...</div>
        <canvas id="remoteCanvas" class="hidden"></canvas>
        <div class="toolbar">
            <button class="btn btn-secondary" id="fullscreenBtn" title="Fullscreen">⛶</button>
            <button class="btn btn-danger" id="disconnectBtn" title="End Session">✕ End</button>
        </div>
    </div>
</div>

<script>
const SESSION_CODE = '<?php echo htmlspecialchars($sessionCode); ?>';
const OTP = '<?php echo htmlspecialchars($otp); ?>';
const SESSION_ID = '<?php echo (int)$session->id; ?>';
const WS_URL = 'wss://remote.planet-hosts.com:8083/';

let ws = null;
let pc = null;
let canvas = document.getElementById('remoteCanvas');
let ctx = canvas.getContext('2d');
let animationFrame = null;
let isConnected = false;

const statusIndicator = document.getElementById('statusIndicator');
const statusLabel = document.getElementById('statusLabel');
const loadingScreen = document.getElementById('loadingScreen');
const remoteView = document.getElementById('remoteView');
const fullscreenBtn = document.getElementById('fullscreenBtn');
const disconnectBtn = document.getElementById('disconnectBtn');

function setStatus(label, className) {
    statusLabel.textContent = label;
    statusIndicator.className = 'status-indicator ' + className;
}

async function init() {
    // Connect to signaling server
    ws = new WebSocket(WS_URL);
    ws.binaryType = 'arraybuffer';

    ws.onopen = () => {
        console.log('Signaling connected');
        // Authenticate as customer
        ws.send(JSON.stringify({
            type: 'auth',
            role: 'customer',
            session_code: SESSION_CODE,
            otp: OTP
        }));
    };

    ws.onmessage = (event) => {
        try {
            const msg = JSON.parse(event.data);
            handleSignalingMessage(msg);
        } catch (e) {
            console.error('Message parse error:', e);
        }
    };

    ws.onclose = () => {
        if (isConnected) {
            setStatus('Disconnected', 'status-disconnected');
            isConnected = false;
        } else {
            setStatus('Connection lost', 'status-disconnected');
        }
        setTimeout(init, 3000);
    };

    ws.onerror = (err) => {
        console.error('WebSocket error:', err);
        setStatus('Connection error', 'status-disconnected');
    };
}

function handleSignalingMessage(msg) {
    switch (msg.type) {
        case 'auth_ok':
            console.log('Authenticated as customer');
            ws.send(JSON.stringify({ type: 'join', session_id: SESSION_ID }));
            break;

        case 'waiting_for_peer':
            setStatus('Waiting for support agent...', 'status-connecting');
            break;

        case 'peer_connected':
            setStatus('Support agent connected', 'status-connected');
            startWebRTC();
            break;

        case 'peer_disconnected':
            setStatus('Support agent disconnected', 'status-disconnected');
            stopWebRTC();
            break;

        case 'offer':
            handleOffer(msg.offer);
            break;

        case 'answer':
            handleAnswer(msg.answer);
            break;

        case 'candidate':
            handleCandidate(msg.candidate);
            break;

        case 'auth_error':
            setStatus('Auth failed: ' + msg.message, 'status-disconnected');
            break;

        case 'error':
            console.error('Server error:', msg.message);
            break;
    }
}

async function startWebRTC() {
    const config = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ]
    };

    pc = new RTCPeerConnection(config);

    pc.ontrack = (event) => {
        console.log('Remote track received');
        const stream = event.streams[0];
        if (stream) {
            const video = document.createElement('video');
            video.srcObject = stream;
            video.autoplay = true;
            video.playsInline = true;
            video.style.width = '100%';
            video.style.height = '100%';
            video.style.objectFit = 'contain';
            loadingScreen.classList.add('hidden');
            remoteView.innerHTML = '';
            remoteView.appendChild(video);
            video.play().catch(console.error);
            isConnected = true;
            setStatus('Connected - Remote desktop active', 'status-connected');
        }
    };

    pc.onicecandidate = (event) => {
        if (event.candidate) {
            ws.send(JSON.stringify({
                type: 'candidate',
                candidate: event.candidate.toJSON()
            }));
        }
    };

    pc.onconnectionstatechange = () => {
        console.log('Connection state:', pc.connectionState);
        if (pc.connectionState === 'connected') {
            setStatus('Connected - Remote desktop active', 'status-connected');
        } else if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed') {
            setStatus('Connection lost', 'status-disconnected');
        }
    };

    // Create offer
    const offer = await pc.createOffer({
        offerToReceiveVideo: true,
        offerToReceiveAudio: false
    });
    await pc.setLocalDescription(offer);

    ws.send(JSON.stringify({
        type: 'offer',
        offer: offer.toJSON()
    }));
}

function handleOffer(offer) {
    pc.setRemoteDescription(new RTCSessionDescription(offer))
        .then(() => pc.createAnswer())
        .then(answer => pc.setLocalDescription(answer))
        .then(() => {
            ws.send(JSON.stringify({
                type: 'answer',
                answer: pc.localDescription.toJSON()
            }));
        })
        .catch(console.error);
}

function handleAnswer(answer) {
    pc.setRemoteDescription(new RTCSessionDescription(answer)).catch(console.error);
}

function handleCandidate(candidate) {
    pc.addIceCandidate(new RTCIceCandidate(candidate)).catch(console.error);
}

function stopWebRTC() {
    if (pc) {
        pc.close();
        pc = null;
    }
    isConnected = false;
    loadingScreen.classList.remove('hidden');
    remoteView.innerHTML = '';
    loadingScreen.textContent = 'Disconnected';
}

disconnectBtn.addEventListener('click', () => {
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ type: 'disconnect' }));
    }
    stopWebRTC();
    ws?.close();
    setStatus('Session ended', 'status-disconnected');
});

fullscreenBtn.addEventListener('click', () => {
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        remoteView.requestFullscreen().catch(console.error);
    }
});

// Handle window resize
window.addEventListener('resize', () => {
    if (canvas && canvas.width) {
        canvas.width = remoteView.clientWidth;
        canvas.height = remoteView.clientHeight;
    }
});

// Start
init();
</script>
</body>
</html>