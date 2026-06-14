<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>SSL Certificates</h3><div class="value"><?php echo $sslCount ?? 0; ?></div><div class="label"><a href="/admin/ssl" style="color:var(--accent)">Manage SSL</a></div></div>
<div class="stat-card"><h3>IP Blocks</h3><div class="value"><?php echo $blockCount ?? 0; ?></div><div class="label">Blocked addresses</div></div>
<div class="stat-card"><h3>2FA Users</h3><div class="value"><?php echo $twoFactorUsers ?? 0; ?></div><div class="label"><a href="/admin/twofactor" style="color:var(--accent)">Manage 2FA</a></div></div>
<div class="stat-card"><h3>Password Policy</h3><div class="value" style="font-size:16px">Standard</div><div class="label">Min 8 chars</div></div>
</div>

<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/ssl" class="action-card"><div class="icon">🔒</div><div class="name">SSL/TLS</div></a>
<a href="/admin/ssl/autossl" class="action-card"><div class="icon">🔄</div><div class="name">AutoSSL</div></a>
<a href="/admin/twofactor" class="action-card"><div class="icon">🔐</div><div class="name">Two-Factor Auth</div></a>
<a href="/admin/roles" class="action-card"><div class="icon">👥</div><div class="name">User Roles</div></a>
</div>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Security Overview</h3>
<p style="color:var(--text-secondary);margin-bottom:8px">The server is running with standard security measures. Consider enabling the following:</p>
<ul style="color:var(--text-secondary);line-height:2;padding-left:20px">
<li><span style="color:<?php echo $blockCount > 0 ? '#4ade80' : '#f87171'; ?>"><?php echo $blockCount > 0 ? '✓' : '✗'; ?></span> IP Blocker — <?php echo $blockCount > 0 ? "$blockCount IPs blocked" : 'No IPs blocked yet'; ?></li>
<li><span style="color:<?php echo $sslCount > 0 ? '#4ade80' : '#f87171'; ?>"><?php echo $sslCount > 0 ? '✓' : '✗'; ?></span> SSL Certificates — <?php echo $sslCount > 0 ? "$sslCount installed" : 'None installed'; ?></li>
<li><span style="color:<?php echo $twoFactorUsers > 0 ? '#4ade80' : '#f87171'; ?>"><?php echo $twoFactorUsers > 0 ? '✓' : '✗'; ?></span> Two-Factor Auth — <?php echo $twoFactorUsers > 0 ? "$twoFactorUsers users enabled" : 'Not enabled on any user'; ?></li>
<li><span style="color:var(--text-secondary)">—</span> Firewall — Manage via system firewall (ufw/iptables)</li>
</ul>
</div>
