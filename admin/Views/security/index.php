<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>SSL Certs</h3><div class="value"><?php echo $sslCount ?? 0; ?></div><div class="label"><a href="/admin/ssl" style="color:var(--accent)">Manage</a></div></div>
<div class="stat-card"><h3>IP Blocks</h3><div class="value"><?php echo $blockCount ?? 0; ?></div><div class="label">Blocked addresses</div></div>
<div class="stat-card"><h3>2FA Users</h3><div class="value"><?php echo $twoFactorUsers ?? 0; ?></div><div class="label"><a href="/admin/twofactor" style="color:var(--accent)">Manage</a></div></div>
<div class="stat-card"><h3>Firewall</h3><div class="value" style="font-size:16px;color:<?php echo $firewall === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $firewall; ?></div></div>
<div class="stat-card"><h3>Fail2ban</h3><div class="value" style="font-size:16px;color:<?php echo $fail2ban === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $fail2ban; ?></div></div>
</div>

<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/ssl" class="action-card"><div class="icon">🔒</div><div class="name">SSL/TLS</div></a>
<a href="/admin/ssl/autossl" class="action-card"><div class="icon">🔄</div><div class="name">AutoSSL</div></a>
<a href="/admin/twofactor" class="action-card"><div class="icon">🔐</div><div class="name">Two-Factor Auth</div></a>
<a href="/admin/roles" class="action-card"><div class="icon">👥</div><div class="name">User Roles</div></a>
<a href="/admin/settings/security" class="action-card"><div class="icon">⚙️</div><div class="name">Security Settings</div></a>
<a href="/admin/network" class="action-card"><div class="icon">🌐</div><div class="name">IP Blocker</div></a>
</div>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Security Overview</h3>
<ul style="color:var(--text-secondary);line-height:2.2;padding-left:20px">
<li><span style="color:<?php echo $sslCount > 0 ? '#4ade80' : '#f87171'; ?>"><?php echo $sslCount > 0 ? '✓' : '✗'; ?></span> SSL Certificates — <?php echo $sslCount > 0 ? "$sslCount installed" : 'None'; ?></li>
<li><span style="color:<?php echo $twoFactorUsers > 0 ? '#4ade80' : '#f87171'; ?>"><?php echo $twoFactorUsers > 0 ? '✓' : '✗'; ?></span> Two-Factor Auth — <?php echo $twoFactorUsers > 0 ? "$twoFactorUsers users" : 'Not enabled'; ?></li>
<li><span style="color:<?php echo $firewall === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $firewall === 'active' ? '✓' : '✗'; ?></span> Firewall (firewalld) — <?php echo $firewall; ?></li>
<li><span style="color:<?php echo $fail2ban === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $fail2ban === 'active' ? '✓' : '✗'; ?></span> Fail2ban — <?php echo $fail2ban; ?></li>
<li><span style="color:<?php echo $blockCount > 0 ? '#4ade80' : '#f87171'; ?>"><?php echo $blockCount > 0 ? '✓' : '✗'; ?></span> IP Blocker — <?php echo $blockCount > 0 ? "$blockCount IPs" : 'None'; ?></li>
</ul>
</div>
