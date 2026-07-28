<?php
// Room page — /chat/{slug}
$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    // Try to extract slug from URL path
    $path = $_SERVER['REQUEST_URI'] ?? '';
    $parts = explode('/', trim(parse_url($path, PHP_URL_PATH) ?? '', '/'));
    // parts[0] should be 'chat', parts[1] should be the slug
    if (($parts[0] ?? '') === 'chat' && !empty($parts[1])) {
        $slug = $parts[1];
    }
}
$slug = preg_replace('/[^a-z0-9-]/', '', $slug);
if (!$slug) { http_response_code(404); echo '<h1>Room not found</h1>'; exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $pdo->prepare("SELECT r.*, t.id as tenant_id FROM chatbox_rooms r JOIN chatbox_tenants t ON t.id = r.tenant_id WHERE r.slug = ? AND r.is_active = 1");
$q->execute([$slug]);
$room = $q->fetch(PDO::FETCH_OBJ);
if (!$room) { http_response_code(404); echo '<h1>Room not found</h1>'; exit; }

$tenantId = (int)$room->tenant_id;
$roomName = htmlspecialchars($room->name ?? 'Chat Room');
$accent = htmlspecialchars($room->color ?? '#008cff');
$icon = htmlspecialchars($room->icon ?? '💬');
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?=$roomName?> — Chat</title>
<meta property="og:title" content="<?=$roomName?>">
<meta property="og:description" content="<?=htmlspecialchars($room->description ?? 'Join the conversation')?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:<?=$accent?>08;color:#e2e8f0;min-height:100vh;display:flex;flex-direction:column}
.header{background:linear-gradient(135deg,<?=$accent?>15,<?=$accent?>08);border-bottom:1px solid <?=$accent?>22;padding:16px 24px;display:flex;align-items:center;gap:12px}
.header .icon{width:40px;height:40px;border-radius:12px;background:<?=$accent?>;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff}
.header .info h1{font-size:18px;font-weight:700;color:#e0e0e0;margin:0}
.header .info p{font-size:11px;color:#94a3b8;margin:2px 0 0}
.container{flex:1;display:flex;align-items:center;justify-content:center;padding:20px}
.widget-wrap{width:100%;max-width:400px}
.widget-card{background:rgba(15,23,42,.6);border:1px solid <?=$accent?>22;border-radius:16px;padding:24px;text-align:center}
.widget-card .big-icon{font-size:48px;margin-bottom:8px}
.widget-card h2{font-size:16px;font-weight:700;color:#e0e0e0;margin-bottom:4px}
.widget-card p{font-size:12px;color:#94a3b8;margin-bottom:16px}
.widget-card .btn{display:inline-block;padding:10px 28px;border-radius:10px;background:<?=$accent?>;color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:.15s}
.widget-card .btn:hover{transform:translateY(-2px);opacity:.9}
.widget-card .btn-embed{display:inline-block;margin-top:8px;padding:6px 16px;border-radius:8px;background:<?=$accent?>15;color:<?=$accent?>;border:none;font-size:11px;cursor:pointer;text-decoration:none}
.embed-code{background:rgba(0,0,0,.4);border:1px solid <?=$accent?>22;border-radius:8px;padding:10px;font-family:monospace;font-size:10px;color:#4ade80;word-break:break-all;margin:8px 0;text-align:left;user-select:all}
</style>
</head><body>
<div class="header">
<div class="icon"><?=$icon?></div>
<div class="info"><h1><?=$roomName?></h1><p><?=htmlspecialchars($room->description ?? '')?></p></div>
</div>
<div class="container">
<div class="widget-wrap">
<div class="widget-card">
<div class="big-icon">💬</div>
<h2><?=$roomName?></h2>
<p>Join the conversation or embed this room on your website.</p>
<a class="btn" href="/chatbox/embed.php?tenant_id=<?=$tenantId?>" target="_blank">Open Chat</a>
<a class="btn-embed" href="javascript:void(0)" onclick="showEmbed()">📋 Get Embed Code</a>
<div id="embedBox" style="display:none">
<div class="embed-code">&lt;script src="https://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?=$tenantId?>"&gt;&lt;/script&gt;</div>
<button class="btn-embed" onclick="copyEmbed()">📋 Copy Widget Code</button>
</div>
</div>
</div>
</div>
<script>
function showEmbed(){document.getElementById('embedBox').style.display='block';}
function copyEmbed(){
navigator.clipboard.writeText('<script src="https://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?=$tenantId?>"><\/script>');
event.target.textContent='✅ Copied!';
setTimeout(function(){event.target.textContent='📋 Copy Widget Code';},2000);
}
</script>
</body></html>
