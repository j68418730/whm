<?php
$file = __DIR__ . '/widgets.json';
$data = json_decode(file_get_contents($file), true);
$widgets = $data['widgets'] ?? [];
$maxWidgets = 30;

// Handle delete single
if (isset($_GET['del'])) {
    $idx = (int)$_GET['del'];
    if (isset($widgets[$idx])) {
        array_splice($widgets, $idx, 1);
        file_put_contents($file, json_encode(['widgets' => $widgets], JSON_PRETTY_PRINT));
        header('Location: edit.php?saved=1');
        exit;
    }
}

// Handle save
if ($_POST && isset($_POST['widgets'])) {
    $widgets = [];
    foreach ($_POST['widgets'] as $code) {
        $code = trim($code);
        if ($code !== '') $widgets[] = $code;
    }
    file_put_contents($file, json_encode(['widgets' => $widgets], JSON_PRETTY_PRINT));
    header('Location: edit.php?saved=1');
    exit;
}

$saved = isset($_GET['saved']);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit Widgets — Repair</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#02050e;color:#e2e8f0;min-height:100vh}
.header{background:rgba(15,23,42,.85);border-bottom:1px solid rgba(56,189,248,.08);padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
.header h1{font-size:18px;font-weight:800;background:linear-gradient(135deg,#e2e8f0,#94a3b8);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.header h1 span{-webkit-text-fill-color:#008cff}
.header a{color:#38bdf8;text-decoration:none;font-size:13px;font-weight:600;padding:6px 14px;border-radius:8px;background:rgba(56,189,248,.1);transition:.2s}
.header a:hover{background:rgba(56,189,248,.2)}
.container{max-width:900px;margin:0 auto;padding:24px}
.info{background:rgba(56,189,248,.06);border:1px solid rgba(56,189,248,.1);border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#94a3b8;line-height:1.6}
.info strong{color:#e0e0e0}
.saved{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.15);border-radius:8px;padding:8px 12px;color:#4ade80;font-size:13px;margin-bottom:12px;text-align:center}
.w-item{background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:10px;padding:12px;margin-bottom:10px}
.w-item .hdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px}
.w-item .hdr .num{font-size:11px;color:#64748b;font-weight:600}
.w-item .hdr .del{color:#f87171;cursor:pointer;font-size:12px;text-decoration:none}
.w-item textarea{width:100%;padding:6px 8px;border-radius:6px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.2);color:#e2e8f0;font-size:12px;outline:none;font-family:monospace;resize:vertical;min-height:40px;box-sizing:border-box}
.w-item textarea:focus{border-color:rgba(56,189,248,.3)}
.add-btn{display:block;width:100%;padding:10px;border:1px dashed rgba(56,189,248,.2);border-radius:10px;background:transparent;color:#38bdf8;font-size:13px;cursor:pointer;transition:.2s;text-align:center;margin-bottom:10px;font-family:inherit}
.add-btn:hover{background:rgba(56,189,248,.04);border-color:rgba(56,189,248,.35)}
.btn-row{display:flex;gap:8px;margin-top:12px}
.btn{padding:10px 24px;border-radius:8px;border:none;font-size:13px;font-weight:700;cursor:pointer;transition:.2s;font-family:inherit}
.btn-primary{background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;flex:1}
.btn-primary:hover{transform:translateY(-2px)}
.btn-secondary{background:rgba(255,255,255,.06);color:#94a3b8}
.btn-secondary:hover{background:rgba(255,255,255,.1)}
footer{text-align:center;padding:20px;font-size:11px;color:#475569}
</style>
</head><body>
<div class="header"><h1>&#x270f;&#xfe0f; <span>Edit</span> Widgets</h1><a href="index.php">&#x1f446; View</a></div>
<div class="container">
<?php if ($saved): ?><div class="saved">&#x2705; Widgets saved! <a href="index.php" style="color:#4ade80;font-weight:600">View them &rarr;</a></div><?php endif; ?>
<div class="info">
<strong>&#x1f4cb;</strong> Paste your widget embed codes below (&#x3c;iframe&#x3e;, &#x3c;script&#x3e;, etc.). Up to <strong><?=$maxWidgets?></strong> widgets. Each one will be displayed on the index page in a card to verify it works.
</div>
<form method="POST" id="wf">
<div id="widget-list">
<?php for ($i = 0; $i < max(count($widgets), 1); $i++): ?>
<div class="w-item">
<div class="hdr"><span class="num">#<?=$i+1?></span>
<?php if ($i > 0): ?><a href="#" class="del" onclick="var p=this.closest('.w-item');p.parentNode.removeChild(p);return false">&#x2715; Remove</a><?php endif; ?>
</div>
<textarea name="widgets[]" rows="2" placeholder="Paste iframe/script embed code here..."><?=htmlspecialchars($widgets[$i] ?? '')?></textarea>
</div>
<?php endfor; ?>
</div>
<button type="button" class="add-btn" onclick="addWidget()">+ Add Widget</button>
<div class="btn-row">
<button type="submit" class="btn btn-primary">&#x1f4be; Save All Widgets</button>
<button type="button" class="btn btn-secondary" onclick="location.href='index.php'">Cancel</button>
</div>
</form>
</div>
<footer>Repair Tool — Planet Hosts</footer>
<script>
var wc = <?=count($widgets)?>;
function addWidget(){
  wc++; var html='<div class="w-item"><div class="hdr"><span class="num">#'+wc+'</span><a href="#" class="del" onclick="var p=this.closest(\'.w-item\');p.parentNode.removeChild(p);return false">&#x2715; Remove</a></div><textarea name="widgets[]" rows="2" placeholder="Paste iframe/script embed code here..."></textarea></div>';
  document.getElementById('widget-list').insertAdjacentHTML('beforeend', html);
}
</script>
</body></html>
