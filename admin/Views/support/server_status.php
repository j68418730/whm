<style>
.ss-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-top:20px}
.ss-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:24px;transition:.25s;position:relative;overflow:hidden}
.ss-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:14px 14px 0 0;transition:.25s}
.ss-card.running::before{background:linear-gradient(90deg,#22c55e,#4ade80)}
.ss-card.stopped::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.ss-card:hover{transform:translateY(-2px);border-color:rgba(0,191,255,.2);box-shadow:0 8px 32px rgba(0,0,0,.3)}
.ss-card.running:hover{box-shadow:0 8px 32px rgba(34,197,94,.1)}
.ss-card.stopped:hover{box-shadow:0 8px 32px rgba(239,68,68,.1)}
.ss-header{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.ss-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.ss-card.running .ss-icon{background:rgba(34,197,94,.12);color:#4ade80}
.ss-card.stopped .ss-icon{background:rgba(239,68,68,.12);color:#f87171}
.ss-name{font-size:16px;font-weight:700;color:#f1f5f9}
.ss-service{font-size:11px;color:#64748b;margin-top:2px;font-family:monospace}
.ss-details{display:flex;flex-direction:column;gap:10px}
.ss-row{display:flex;justify-content:space-between;align-items:center}
.ss-label{font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.ss-value{font-size:13px;font-weight:600}
.ss-card.running .ss-value{color:#4ade80}
.ss-card.stopped .ss-value{color:#f87171}
.ss-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
.ss-card.running .ss-badge{background:rgba(34,197,94,.12);color:#4ade80}
.ss-card.stopped .ss-badge{background:rgba(239,68,68,.12);color:#f87171}
.ss-dot{width:8px;height:8px;border-radius:50%}
.ss-card.running .ss-dot{background:#4ade80;box-shadow:0 0 8px rgba(34,197,94,.5);animation:pulse-green 2s infinite}
.ss-card.stopped .ss-dot{background:#f87171;box-shadow:0 0 8px rgba(239,68,68,.5)}
@keyframes pulse-green{0%,100%{opacity:1}50%{opacity:.5}}
.ss-uptime{font-size:12px;color:#94a3b8;line-height:1.5}
.ss-summary{display:flex;gap:20px;margin-bottom:24px;flex-wrap:wrap}
.ss-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:16px 24px;display:flex;align-items:center;gap:12px}
.ss-stat-num{font-size:28px;font-weight:800}
.ss-stat-label{font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.ss-stat.running .ss-stat-num{color:#4ade80}
.ss-stat.stopped .ss-stat-num{color:#f87171}
.ss-stat.total .ss-stat-num{color:var(--accent,#0ea5e9)}
</style>



<h2 style="margin-bottom:6px">Server Status</h2>
<p style="color:#64748b;margin-bottom:20px;font-size:13px">Real-time monitoring of all server services.</p>

<?php
$running = 0; $stopped = 0;
foreach ($services as $s) {
    if ($s['status'] === 'running') $running++;
    else $stopped++;
}
$total = count($services);
?>

<div class="ss-summary">
  <div class="ss-stat total"><div><div class="ss-stat-num"><?php echo $total; ?></div><div class="ss-stat-label">Total Services</div></div></div>
  <div class="ss-stat running"><div><div class="ss-stat-num"><?php echo $running; ?></div><div class="ss-stat-label">Running</div></div></div>
  <div class="ss-stat stopped"><div><div class="ss-stat-num"><?php echo $stopped; ?></div><div class="ss-stat-label">Stopped</div></div></div>
</div>

<div class="ss-grid">
<?php
$icons = [
    'Apache' => '🌐', 'MariaDB' => '🗄️', 'Postfix' => '📧',
    'Dovecot' => '📬', 'VSFTPD' => '📁', 'Bind9' => '🔗',
    'Icecast' => '🎵', 'SSH' => '🔒', 'Docker' => '🐳',
];
foreach ($services as $s):
    $icon = $icons[$s['name']] ?? '⚙️';
    $cls = $s['status'] === 'running' ? 'running' : 'stopped';
?>
<div class="ss-card <?php echo $cls; ?>">
  <div class="ss-header">
    <div class="ss-icon"><?php echo $icon; ?></div>
    <div>
      <div class="ss-name"><?php echo $s['name']; ?></div>
      <div class="ss-service"><?php echo $s['service']; ?></div>
    </div>
  </div>
  <div class="ss-details">
    <div class="ss-row">
      <span class="ss-label">Status</span>
      <span class="ss-badge"><span class="ss-dot"></span><?php echo ucfirst($s['status']); ?></span>
    </div>
    <?php if ($s['uptime']): ?>
    <div class="ss-row" style="flex-direction:column;align-items:flex-start;gap:4px">
      <span class="ss-label">Active Since</span>
      <span class="ss-uptime"><?php echo htmlspecialchars($s['uptime']); ?></span>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
</div>
