<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Firewalld</h3><div class="value" style="font-size:16px;color:<?php echo $fw === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $fw; ?></div></div>
<div class="stat-card"><h3>Fail2ban</h3><div class="value" style="font-size:16px;color:<?php echo $f2b === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $f2b; ?></div></div>
<div class="stat-card"><h3>ModSecurity</h3><div class="value" style="font-size:16px;color:<?php echo $modsec === 'enabled' ? '#4ade80' : '#f87171'; ?>"><?php echo $modsec; ?></div></div>
<div class="stat-card"><h3>Open Ports</h3><div class="value" style="font-size:16px"><?php echo count($openPorts) + count($openServices); ?></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

<!-- Firewalld Controls -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Firewalld</h3>
<?php if (!$fwInstalled): ?>
<p style="color:var(--text-secondary);margin-bottom:8px">firewalld is not installed.</p>
<a href="/admin/firewall/service/install/firewalld" class="btn primary">Install firewalld</a>
<?php else: ?>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<a href="/admin/firewall/service/start/firewalld" class="btn btn-sm <?php echo $fw==='active'?'secondary':'primary'; ?>"><?php echo $fw==='active'?'Restart':'Start'; ?></a>
<a href="/admin/firewall/service/stop/firewalld" class="btn btn-sm danger">Stop</a>
<a href="/admin/firewall/service/enable/firewalld" class="btn btn-sm primary">Enable on Boot</a>
<a href="/admin/firewall/service/disable/firewalld" class="btn btn-sm secondary">Disable on Boot</a>
</div>
<p style="color:var(--text-secondary);font-size:12px;margin-top:8px">Boot: <?php echo $fwEnabled; ?></p>
<?php endif; ?>
</div>

<!-- Fail2ban Controls -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Fail2ban</h3>
<?php if (!$f2bInstalled): ?>
<p style="color:var(--text-secondary);margin-bottom:8px">fail2ban is not installed.</p>
<a href="/admin/firewall/service/install/fail2ban" class="btn primary">Install fail2ban</a>
<?php else: ?>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<a href="/admin/firewall/service/start/fail2ban" class="btn btn-sm <?php echo $f2b==='active'?'secondary':'primary'; ?>"><?php echo $f2b==='active'?'Restart':'Start'; ?></a>
<a href="/admin/firewall/service/stop/fail2ban" class="btn btn-sm danger">Stop</a>
</div>
<?php endif; ?>
</div>

<!-- ModSecurity Controls -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">ModSecurity</h3>
<?php if (!$modsecInstalled): ?>
<p style="color:var(--text-secondary);margin-bottom:8px">ModSecurity is not installed.</p>
<a href="/admin/firewall/modsec/install" class="btn primary">Install ModSecurity</a>
<?php else: ?>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<a href="/admin/firewall/modsec/enable" class="btn btn-sm <?php echo $modsec==='enabled'?'secondary':'primary'; ?>"><?php echo $modsec==='enabled'?'Already Enabled':'Enable'; ?></a>
<?php if ($modsec==='enabled'): ?><a href="/admin/firewall/modsec/disable" class="btn btn-sm danger">Disable</a><?php endif; ?>
</div>
<?php endif; ?>
</div>

<!-- Quick Port Open -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Open a Port</h3>
<form method="POST" action="/admin/firewall/port/add" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div><label style="font-size:12px;color:var(--text-secondary)">Port</label><input name="port" required placeholder="8080" style="width:100px"></div>
<div><label style="font-size:12px;color:var(--text-secondary)">Protocol</label><select name="protocol"><option value="tcp">TCP</option><option value="udp">UDP</option><option value="tcp/udp">Both</option></select></div>
<button type="submit" class="btn primary btn-sm">Add</button>
</form>
</div>

<!-- Whitelist IP -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Whitelist IP</h3>
<form method="POST" action="/admin/firewall/whitelist" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div><label style="font-size:12px;color:var(--text-secondary)">IP Address</label><input name="ip" required placeholder="1.2.3.4" style="width:160px"></div>
<button type="submit" class="btn primary btn-sm">Whitelist</button>
</form>
</div>

</div>

<!-- Open Ports List -->
<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Currently Open Ports</h3>
<?php if (!empty($openPorts) || !empty($openServices)): ?>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<?php foreach ($openPorts as $p): ?>
<span style="padding:4px 10px;border-radius:5px;font-size:12px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);color:#4ade80"><?php echo htmlspecialchars($p); ?> <a href="/admin/firewall/port/remove/<?php echo urlencode($p); ?>" style="color:#f87171;text-decoration:none;margin-left:4px" onclick="return confirm('Close port?')">✕</a></span>
<?php endforeach; ?>
<?php foreach ($openServices as $s): ?>
<span style="padding:4px 10px;border-radius:5px;font-size:12px;background:rgba(96,165,250,.08);border:1px solid rgba(96,165,250,.2);color:#60a5fa"><?php echo htmlspecialchars($s); ?> (service)</span>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="color:var(--text-secondary)">No ports open (or firewalld not running).</p>
<?php endif; ?>
</div>

<!-- Blocked IPs -->
<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Blocked IPs</h3>
<table><tr><th>IP</th><th>Reason</th><th>Date</th><th></th></tr>
<?php if (!empty($blocks)): foreach ($blocks as $b): ?>
<tr><td style="font-family:monospace"><?php echo htmlspecialchars($b->ip_address); ?></td><td><?php echo htmlspecialchars($b->notes ?: '-'); ?></td><td><?php echo $b->created_at; ?></td>
<td><a href="/admin/ipblocker/delete/<?php echo $b->id; ?>" class="btn btn-sm primary">Unblock</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No blocked IPs.</td></tr>
<?php endif; ?></table>
</div>

<!-- Common Ports Reference -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Common Ports Reference</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;font-size:12px">
<?php
$ports = [
    '🌐 HTTP' => ['80','443'],
    '🔐 SSH' => ['22'],
    '📁 FTP' => ['20','21'],
    '🌍 DNS' => ['53'],
    '📧 SMTP' => ['25','465','587'],
    '📨 POP3' => ['110','995'],
    '📬 IMAP' => ['143','993'],
    '🗄️ MySQL' => ['3306'],
    '📦 Redis' => ['6379'],
    '🐳 Docker' => ['2375','2376'],
    '📡 Icecast' => ['8000','8001'],
    '🎙️ SHOUTcast' => ['11000-13999'],
    '🖥️ Panel' => ['2082','2083','2087','2096'],
    '🎮 Node' => ['3000'],
    '🔌 WS' => ['6001'],
    '⏰ NTP' => ['123'],
    '💻 RDP' => ['3389'],
    '🖥️ VNC' => ['5900-5999'],
];
foreach ($ports as $group => $items): ?>
<div style="background:rgba(255,255,255,.03);border-radius:8px;padding:10px;border:1px solid rgba(255,255,255,.04)">
<div style="font-weight:600;color:var(--accent);margin-bottom:6px;font-size:11px"><?php echo $group; ?></div>
<div style="display:flex;flex-wrap:wrap;gap:4px">
<?php foreach ($items as $p): ?>
<span style="background:rgba(0,191,255,.08);color:#60a5fa;padding:2px 6px;border-radius:4px;font-family:monospace;font-size:11px"><?php echo $p; ?></span>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
