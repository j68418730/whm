<?php
$data = json_decode(file_get_contents(__DIR__ . '/widgets.json'), true);
$widgets = $data['widgets'] ?? [];
$total = count($widgets);
$cols = min(max($total, 1), 3);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Widget Tester — Repair</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#02050e;color:#e2e8f0;min-height:100vh}
.header{background:rgba(15,23,42,.85);border-bottom:1px solid rgba(56,189,248,.08);padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
.header h1{font-size:18px;font-weight:800;background:linear-gradient(135deg,#e2e8f0,#94a3b8);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.header h1 span{-webkit-text-fill-color:#008cff}
.header a{color:#38bdf8;text-decoration:none;font-size:13px;font-weight:600;padding:6px 14px;border-radius:8px;background:rgba(56,189,248,.1);transition:.2s}
.header a:hover{background:rgba(56,189,248,.2)}
.container{max-width:1400px;margin:0 auto;padding:24px}
.stats{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.stat{background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:12px;padding:14px 20px;text-align:center;min-width:120px}
.stat .num{font-size:24px;font-weight:800;color:#38bdf8}
.stat .lbl{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-top:2px}
.grid{display:grid;grid-template-columns:repeat(<?=$cols?>,1fr);gap:16px}
.card{background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:14px;padding:16px;overflow:hidden}
.card .num-badge{font-size:10px;color:#64748b;font-weight:600;margin-bottom:6px;display:flex;justify-content:space-between}
.card .num-badge .del{color:#f87171;cursor:pointer;text-decoration:none;font-size:11px}
.card .w-wrap{overflow:hidden;border-radius:8px;background:rgba(0,0,0,.2);padding:8px;min-height:50px}
.empty{text-align:center;padding:60px 20px;color:#64748b;font-size:14px;grid-column:1/-1}
.empty a{color:#38bdf8}
footer{text-align:center;padding:20px;font-size:11px;color:#475569}
@media(max-width:768px){.grid{grid-template-columns:1fr}}
</style>
</head><body>
<div class="header"><h1>&#x1f6e0; <span>Widget</span> Tester</h1><a href="edit.php">&#x270f;&#xfe0f; Edit Widgets</a></div>
<div class="container">
<div class="stats">
<div class="stat"><div class="num"><?=$total?></div><div class="lbl">Widgets</div></div>
<div class="stat"><div class="num"><?=$cols?> col</div><div class="lbl">Layout</div></div>
</div>
<div class="grid">
<?php if (empty($widgets)): ?>
<div class="empty">No widgets yet. <a href="edit.php">Add some widget codes</a> to test them.</div>
<?php else: ?>
<?php foreach ($widgets as $i => $w): ?>
<div class="card">
<div class="num-badge"><span>#<?=$i+1?></span></div>
<div class="w-wrap"><?=$w?></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>
<footer>Repair Tool — Planet Hosts</footer>
</body></html>
