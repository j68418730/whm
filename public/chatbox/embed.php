<?php
$tenantId = (int)($_GET['tenant_id'] ?? 0);
if (!$tenantId) { echo 'Invalid tenant'; exit; }
$room = trim($_GET['room'] ?? '');
$roomParam = $room !== '' ? '&room=' . urlencode($room) : '';
?>
<!DOCTYPE html><html><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Chat</title>
<script src="https://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?php echo $tenantId . $roomParam; ?>"></script>
<style>
body{margin:0;padding:0;background:transparent;overflow:hidden;width:100%;height:100%}
html,body{width:100%;height:100%}
#chatbox-widget{position:fixed!important;top:0!important;left:0!important;right:0!important;bottom:0!important;width:100vw!important;height:100vh!important;margin:0!important;padding:0!important;display:flex!important}
#chatbox-widget #chatbox-toggle{display:none!important}
#chatbox-panel{position:absolute!important;top:0!important;left:0!important;right:0!important;bottom:0!important;width:100%!important;height:100%!important;max-width:none!important;max-height:none!important;border-radius:0!important;margin:0!important}
#chatbox-panel.closed{display:flex!important}
</style>
</head><body>
<script>
// Auto-open in iframe mode — retry until widget loads
(function tryOpen(){
    var panel = document.getElementById('chatbox-panel');
    if (panel) { panel.classList.remove('closed'); panel.classList.add('open'); }
    else { setTimeout(tryOpen, 300); }
})();
</script>
</body></html>

