<style>
.srv{--a:#1683ff;--a2:#0b65d8;--line:#17385b;--card:linear-gradient(145deg,#0b1f39,#08192d);--muted:#8ea8c5;--text:#e8f1ff}
.srv .page-head{display:flex;align-items:center;justify-content:space-between;margin:0 0 22px;flex-wrap:wrap;gap:12px}
.srv .page-title{display:flex;gap:14px;align-items:center}
.srv .page-title .ti{font-size:34px;color:#a9c9ee}
.srv .page-title h1{font-size:25px;color:var(--text);font-weight:750;margin:0}
.srv .page-title p{color:var(--muted);font-size:13px;margin:2px 0 0}
.srv .panel{background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 25px rgba(0,0,0,.13);margin-bottom:18px}
.srv .stats{display:grid;grid-template-columns:repeat(6,1fr);gap:13px}
.srv .stat{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:18px;min-height:120px;position:relative;overflow:hidden}
.srv .stat .st-ic{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;font-size:20px;color:#fff;margin-bottom:12px}
.srv .stat strong{font-size:22px;color:var(--text);display:block}
.srv .stat small{display:block;color:var(--muted);margin-top:4px;font-size:11px;text-transform:uppercase;letter-spacing:.5px}
.srv .stat .val{color:#d1dfef;font-size:12px;margin-top:6px}
.srv .ic-blue{background:#1267bb}.srv .ic-green{background:#16d17e}.srv .ic-yellow{background:#f4ac15}.srv .ic-purple{background:#6338bd}.srv .ic-cyan{background:#0a91ae}.srv .ic-red{background:#ed3d4e}
.srv .bar{height:8px;background:#233e5d;border-radius:8px;overflow:hidden;margin-top:12px}
.srv .bar span{display:block;height:100%;border-radius:8px}
.srv .p-head{padding:14px 18px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:#e8f1ff;display:flex;align-items:center;gap:9px}
.srv .p-head i{color:var(--a)}
.srv .p-body{padding:16px 18px}
.srv .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.srv .info-table{width:100%;border-collapse:collapse}
.srv .info-table td{padding:9px 12px;font-size:13px;border-bottom:1px solid #183655;color:#d4e1ef}
.srv .info-table td:first-child{color:var(--muted);font-weight:600;width:190px;text-transform:uppercase;font-size:11px;letter-spacing:.4px}
.srv .info-table tr:last-child td{border-bottom:none}
.srv table.svc{width:100%;border-collapse:collapse}
.srv table.svc thead th{background:#102847;color:#c4d5e9;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:11px;text-align:left;border-bottom:1px solid #1c3d60}
.srv table.svc tbody td{padding:11px;font-size:12px;border-bottom:1px solid #183655;color:#d4e1ef;vertical-align:middle}
.srv table.svc tbody tr:hover{background:#0c2340}
.srv .svc-ic{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:#102847;border:1px solid #1c3d60;color:#38bdf8;font-size:15px;margin-right:9px;vertical-align:middle}
.srv .st-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px;vertical-align:middle}
.srv .st-dot.ok{background:#16d987;box-shadow:0 0 8px rgba(22,217,135,.4)}
.srv .st-dot.down{background:#ff4757}
.srv .st-dot.stopped{background:#f4ac15}
.srv .badge-s{display:inline-block;padding:4px 10px;border-radius:14px;font-size:11px;font-weight:700}
.srv .badge-s.ok{background:rgba(22,217,135,.15);color:#16d987}
.srv .badge-s.down{background:rgba(255,71,87,.15);color:#ff4757}
.srv .badge-s.stopped{background:rgba(244,172,21,.15);color:#f4ac15}
.srv .btn-mini{display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;border:1px solid #1c3d60;background:#102847;color:#b8cae0;transition:.1s}
.srv .btn-mini:hover{background:#173b64;color:#fff}
.srv .btn-mini.b{background:#137ce4;border-color:#137ce4;color:#fff}
.srv .btn-mini.g{background:#11bda2;border-color:#11bda2;color:#fff}
.srv .btn-mini.y{background:#e5a20d;border-color:#e5a20d;color:#fff}
.srv .form-row{display:flex;gap:10px;align-items:end;flex-wrap:wrap}
.srv .form-group{flex:1;min-width:180px}
.srv .form-group label{display:block;font-size:11px;color:var(--muted);font-weight:600;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px}
.srv .form-group input{width:100%;height:40px;background:#0b2543;border:1px solid #24496f;color:#b8cae0;border-radius:7px;padding:0 13px;font-size:13px;outline:none}
.srv .form-group input:focus{border-color:var(--a);box-shadow:0 0 0 2px rgba(22,131,255,.18)}
.srv .btn-submit{background:var(--a);border:0;border-radius:7px;color:#fff;font-weight:650;height:40px;padding:0 18px;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;gap:7px}
.srv .btn-submit:hover{background:var(--a2)}
.srv .tips{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.srv .tip{border:1px solid #193b60;background:#0c2340;border-radius:8px;padding:13px 15px}
.srv .tip i{color:#16d889;margin-right:8px}
.srv .tip strong{font-size:12px;color:#e8f1ff}
.srv .tip p{font-size:11px;color:#8ea8c5;margin:6px 0 0}
@media(max-width:1100px){.srv .stats{grid-template-columns:repeat(3,1fr)}.srv .grid2{grid-template-columns:1fr}.srv .tips{grid-template-columns:1fr}}
@media(max-width:700px){.srv .stats{grid-template-columns:repeat(2,1fr)}}
</style>

<div class="srv">
<div class="page-head">
<div class="page-title">
<i class="bi bi-server title-icon ti"></i>
<div><h1>Server Overview</h1><p>Monitor resources, services, and system information for this node.</p></div>
</div>
<a href="/admin/server/health" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:7px"><i class="bi bi-heart-pulse"></i> Server Health</a>
</div>

<?php
$ss = $serverStats ?? [];
$ramUsed = (float)($ss['ram_usage'] ?? 0);
$ramTotal = (float)($ss['ram_total'] ?? 1);
$ramPct = $ramTotal > 0 ? min(100, round($ramUsed / $ramTotal * 100)) : 0;
$diskUsed = (float)($ss['disk_usage'] ?? 0);
$diskTotal = (float)($ss['disk_total'] ?? 1);
$diskPct = $diskTotal > 0 ? min(100, round($diskUsed / $diskTotal * 100)) : 0;
$load1 = $ss['load_average'][0] ?? $ss['cpu_load'] ?? 0;
$cores = (int)($ss['cpu_cores'] ?? 1);
function srvSize($g){ if($g > 1024) return number_format($g/1024,2).' TB'; return number_format($g,1).' GB'; }
function srvRamFmt($mb){ if($mb > 1024) return number_format($mb/1024,1).' GB'; return number_format($mb,0).' MB'; }
$svcStatus = is_array($ss['service_status'] ?? null) ? $ss['service_status'] : [];
$services = $services ?? [];
?>
<div class="stats">
<div class="stat"><div class="st-ic ic-blue"><i class="bi bi-cpu"></i></div><strong><?php echo $load1; ?></strong><small>CPU Load</small><div class="val"><?php echo $cores; ?> cores · <?php echo htmlspecialchars($ss['cpu_model'] ?? 'N/A'); ?></div></div>
<div class="stat"><div class="st-ic ic-green"><i class="bi bi-memory"></i></div><strong><?php echo srvRamFmt($ramUsed); ?></strong><small>Memory</small><div class="val">of <?php echo srvRamFmt($ramTotal); ?></div><div class="bar"><span style="width:<?php echo $ramPct; ?>%;background:#16d17e"></span></div></div>
<div class="stat"><div class="st-ic ic-yellow"><i class="bi bi-hdd"></i></div><strong><?php echo srvSize($diskUsed); ?></strong><small>Disk</small><div class="val">of <?php echo srvSize($diskTotal); ?></div><div class="bar"><span style="width:<?php echo $diskPct; ?>%;background:#f4ac15"></span></div></div>
<div class="stat"><div class="st-ic ic-cyan"><i class="bi bi-clock-history"></i></div><strong><?php echo htmlspecialchars($ss['uptime'] ?? '—'); ?></strong><small>Uptime</small><div class="val">since boot</div></div>
<div class="stat"><div class="st-ic ic-purple"><i class="bi bi-person-fill"></i></div><strong><?php echo (int)($ss['active_accounts'] ?? 0); ?></strong><small>Active Accounts</small><div class="val">hosting customers</div></div>
<div class="stat"><div class="st-ic ic-red"><i class="bi bi-activity"></i></div><strong><?php echo $cores; ?></strong><small>CPU Cores</small><div class="val"><?php echo htmlspecialchars($ss['os'] ?? 'N/A'); ?></div></div>
</div>

<div class="panel" style="margin-top:18px">
<div class="p-head"><i class="bi bi-gear"></i> System Information</div>
<div class="p-body">
<div class="grid2">
<table class="info-table">
<tr><td>Hostname</td><td><?php echo htmlspecialchars($ss['hostname'] ?? 'N/A'); ?></td></tr>
<tr><td>Operating System</td><td><?php echo htmlspecialchars($ss['os'] ?? 'N/A'); ?></td></tr>
<tr><td>Kernel</td><td><?php echo htmlspecialchars($ss['kernel'] ?? 'N/A'); ?></td></tr>
<tr><td>CPU Model</td><td><?php echo htmlspecialchars($ss['cpu_model'] ?? 'N/A'); ?></td></tr>
</table>
<table class="info-table">
<tr><td>CPU Cores</td><td><?php echo (int)$cores; ?></td></tr>
<tr><td>Load Average</td><td><?php echo htmlspecialchars(implode(' / ', array_map(fn($v)=>round((float)$v,2), (array)($ss['load_average'] ?? []))) ?: $load1); ?></td></tr>
<tr><td>Memory</td><td><?php echo srvRamFmt($ramUsed); ?> used of <?php echo srvRamFmt($ramTotal); ?> (<?php echo $ramPct; ?>%)</td></tr>
<tr><td>Disk</td><td><?php echo srvSize($diskUsed); ?> used of <?php echo srvSize($diskTotal); ?> (<?php echo $diskPct; ?>%)</td></tr>
</table>
</div>
</div>
</div>

<div class="panel">
<div class="p-head"><i class="bi bi-toggles"></i> Server Configuration</div>
<div class="p-body">
<?php
$md = '';
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='main_domain' LIMIT 1");
    if ($q) { $v = $q->fetchColumn(); if ($v) $md = $v; }
} catch (\Exception $e) {}
?>
<div class="grid2">
<form method="POST" action="/admin/serverconfig/hostname" class="form-row">
<div class="form-group"><label>System Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($ss['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Set Hostname</button>
</form>
<form method="POST" action="/admin/serverconfig/main-domain" class="form-row">
<div class="form-group"><label>Main Domain</label><input name="main_domain" value="<?php echo htmlspecialchars($md, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example.com"></div>
<button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Set Main Domain</button>
</form>
</div>
<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--line);display:flex;gap:10px;flex-wrap:wrap">
<a href="/admin/serverconfig" class="btn-mini"><i class="bi bi-gear"></i> Full Server Config</a>
<a href="/admin/tweak" class="btn-mini"><i class="bi bi-sliders"></i> Tweak Settings</a>
<a href="/admin/process-manager" class="btn-mini"><i class="bi bi-diagram-3"></i> Process Manager</a>
<a href="/admin/server/terminal" class="btn-mini b"><i class="bi bi-terminal"></i> Terminal</a>
</div>
</div>
</div>

<div class="panel">
<div class="p-head"><i class="bi bi-heart-pulse"></i> Services <span style="margin-left:auto;font-size:11px;color:var(--muted)"><?php echo count($services); ?> services detected</span></div>
<div class="p-body">
<?php if (!empty($services)): ?>
<table class="svc">
<thead><tr><th>Service</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($services as $svc):
    $name = $svc['label'];
    $status = $svc['status'];
    $unit = $svc['unit'];
    $st = is_string($status) ? strtolower($status) : 'unknown';
    $ok = in_array($st, ['active','running','ok']);
    $cls = $ok ? 'ok' : ($st === 'stopped' || $st === 'inactive' ? 'stopped' : 'down');
    $ic = in_array(strtolower($name), ['web server','nginx']) ? 'globe2' : (strtolower($name) === 'database' ? 'database' : (strtolower($name) === 'mail' ? 'envelope' : (str_contains(strtolower($name),'shoutcast') || str_contains(strtolower($name),'icecast') ? 'broadcast' : (strtolower($name) === 'dns' ? 'globe2' : (strtolower($name) === 'ftp' ? 'folder2' : 'box')))));
?>
<tr>
<td><span class="svc-ic"><i class="bi bi-<?php echo $ic; ?>"></i></span><?php echo htmlspecialchars($name); ?></td>
<td><span class="badge-s <?php echo $cls; ?>"><span class="st-dot <?php echo $cls; ?>"></span><?php echo htmlspecialchars(ucfirst((string)$status)); ?></span><?php if ($unit): ?><span style="color:#5d7897;font-size:10px;margin-left:8px"><?php echo htmlspecialchars($unit); ?></span><?php endif; ?></td>
<td>
<?php if ($unit): ?>
<a href="/admin/service/restart/<?php echo urlencode($unit); ?>" class="btn-mini y" onclick="return confirm('Restart <?php echo htmlspecialchars($name); ?>?')"><i class="bi bi-arrow-clockwise"></i> Restart</a>
<?php if ($ok): ?><a href="/admin/service/stop/<?php echo urlencode($unit); ?>" class="btn-mini" onclick="return confirm('Stop <?php echo htmlspecialchars($name); ?>?')"><i class="bi bi-stop"></i></a>
<?php else: ?><a href="/admin/service/start/<?php echo urlencode($unit); ?>" class="btn-mini g"><i class="bi bi-play"></i></a><?php endif; ?>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:var(--muted);font-size:13px;margin:0">No service status available.</p>
<?php endif; ?>
</div>
</div>

<div class="panel">
<div class="p-head"><i class="bi bi-lightbulb"></i> Quick Info</div>
<div class="p-body">
<div class="tips">
<div class="tip"><i class="bi bi-check-circle-fill"></i><strong>Server Health</strong><p>Run a full health check on all services and resources.</p></div>
<div class="tip"><i class="bi bi-check-circle-fill"></i><strong>Terminal</strong><p>Open a real PTY shell for direct server administration.</p></div>
<div class="tip"><i class="bi bi-check-circle-fill"></i><strong>Resources</strong><p>Monitor CPU, RAM, disk and uptime at a glance.</p></div>
</div>
</div>
</div>
</div>