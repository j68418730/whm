<?php
$tenantId = (int)($_GET['tenant_id'] ?? 0);
if (!$tenantId) { echo 'Invalid tenant'; exit; }
?>
<!DOCTYPE html><html><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Chat</title>
<script src="http://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?php echo $tenantId; ?>"></script>
<style>body{margin:0;padding:0;background:transparent;overflow:hidden}#chatbox-widget #chatbox-toggle{display:none!important}#chatbox-panel{position:static!important;width:100%!important;height:100vh!important;border-radius:0!important;display:flex!important}</style>
</head><body>
<script>
// Auto-open in iframe mode
setTimeout(function() {
    var panel = document.getElementById('chatbox-panel');
    if (panel) panel.classList.add('open');
}, 500);
</script>
</body></html>

