<div class="stats-grid" style="margin-bottom:24px">
<div class="stat-card"><h3>SSL Certs</h3><div class="value"><?php echo $sslCount ?? 0; ?></div></div>
<div class="stat-card"><h3>IP Blocks</h3><div class="value"><?php echo $blockCount ?? 0; ?></div></div>
<div class="stat-card"><h3>2FA Users</h3><div class="value"><?php echo $twoFactorUsers ?? 0; ?></div></div>
<div class="stat-card"><h3>Login Attempts</h3><div class="value"><?php echo $loginAttempts['total'] ?? 0; ?></div></div>
</div>

<div class="card" style="padding:0;overflow:hidden">
<table style="margin:0">
<tr><th style="padding:14px 20px;font-size:13px;background:rgba(0,191,255,.04);border-bottom:1px solid rgba(255,255,255,.06)" colspan="2">Security Tools</th></tr>
<tr><td style="padding:0" colspan="2">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
<a href="/admin/ssl" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">🔒</span><div><strong style="color:#fff">SSL/TLS</strong><br><span style="font-size:12px;color:var(--text-secondary)">Manage certificates</span></div></a>
<a href="/admin/ssl/autossl" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">🔄</span><div><strong style="color:#fff">AutoSSL</strong><br><span style="font-size:12px;color:var(--text-secondary)">Let's Encrypt setup</span></div></a>
<a href="/admin/twofactor" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">🔐</span><div><strong style="color:#fff">Two-Factor Auth</strong><br><span style="font-size:12px;color:var(--text-secondary)">2FA configuration</span></div></a>
<a href="/admin/ipblocker" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">🌐</span><div><strong style="color:#fff">IP Blocker</strong><br><span style="font-size:12px;color:var(--text-secondary)">Block/unblock IPs</span></div></a>
<a href="/admin/roles" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">👥</span><div><strong style="color:#fff">User Roles</strong><br><span style="font-size:12px;color:var(--text-secondary)">Role & permission management</span></div></a>
<a href="/admin/settings/security" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">⚙️</span><div><strong style="color:#fff">Security Settings</strong><br><span style="font-size:12px;color:var(--text-secondary)">Password policy, session config</span></div></a>
<a href="/admin/network" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">🖧</span><div><strong style="color:#fff">IP & Nameservers</strong><br><span style="font-size:12px;color:var(--text-secondary)">Server IPs, nameserver config</span></div></a>
<a href="/admin/firewall" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">🛡️</span><div><strong style="color:#fff">Firewall Manager</strong><br><span style="font-size:12px;color:var(--text-secondary)">Ports, IP whitelist, fail2ban, modsecurity</span></div></a>
</div>
</td></tr></table>
</div>

<div class="card" style="margin-top:20px"><h3 style="color:var(--accent);margin-bottom:12px">Service Status</h3>
<table><tr><th>Service</th><th>Status</th><th></th></tr>
<tr><td>Firewall (firewalld)</td><td><span class="status-badge status-<?php echo $firewall === 'active' ? 'active' : 'terminated'; ?>"><?php echo $firewall; ?></span></td>
<td><?php if ($firewall !== 'active'): ?><a href="/admin/firewall" class="btn btn-sm primary">Install/Manage</a><?php endif; ?></td></tr>
<tr><td>Fail2ban</td><td><span class="status-badge status-<?php echo $fail2ban === 'active' ? 'active' : 'terminated'; ?>"><?php echo $fail2ban; ?></span></td>
<td><?php if ($fail2ban !== 'active'): ?><a href="/admin/firewall" class="btn btn-sm primary">Install/Manage</a><?php endif; ?></td></tr>
<tr><td>ModSecurity</td><td><span class="status-badge status-<?php echo $modsec === 'enabled' ? 'active' : 'terminated'; ?>"><?php echo $modsec; ?></span></td>
<td><?php if ($modsec !== 'enabled'): ?><a href="/admin/firewall" class="btn btn-sm primary">Install/Manage</a><?php endif; ?></td></tr>
<tr><td>SSL Certificates</td><td><span class="status-badge status-<?php echo $sslCount > 0 ? 'active' : 'terminated'; ?>"><?php echo $sslCount > 0 ? "$sslCount installed" : 'None'; ?></span></td><td><a href="/admin/ssl" class="btn btn-sm secondary">Manage</a></td></tr>
<tr><td>Two-Factor Auth</td><td><span class="status-badge status-<?php echo $twoFactorUsers > 0 ? 'active' : 'terminated'; ?>"><?php echo $twoFactorUsers > 0 ? "$twoFactorUsers users" : 'Disabled'; ?></span></td><td><a href="/admin/twofactor" class="btn btn-sm secondary">Manage</a></td></tr>
<tr><td>IP Blocker</td><td><span class="status-badge status-<?php echo $blockCount > 0 ? 'active' : 'terminated'; ?>"><?php echo $blockCount > 0 ? "$blockCount IPs blocked" : 'No blocks'; ?></span></td><td><a href="/admin/ipblocker" class="btn btn-sm secondary">Manage</a></td></tr>
</table></div>

<div class="card" style="margin-top:16px"><h3 style="color:var(--accent);margin-bottom:12px">Login Security</h3>
<table><tr><th>Metric</th><th>Value</th></tr>
<tr><td>Recent Login Attempts</td><td><?php echo $loginAttempts['total'] ?? 0; ?></td></tr>
<tr><td>Unique IPs</td><td><?php echo $loginAttempts['unique_ips'] ?? 0; ?></td></tr>
<?php if (!empty($loginAttempts['top_ips'])): ?>
<tr><td colspan="2" style="padding:0"><table style="margin:0"><tr><th style="color:var(--text-secondary);font-size:12px">Top IPs</th><th style="color:var(--text-secondary);font-size:12px">Count</th></tr>
<?php arsort($loginAttempts['top_ips']); $i=0; foreach (array_slice($loginAttempts['top_ips'], 0, 5) as $ip => $cnt): if (++$i > 5) break; ?>
<tr><td style="font-family:monospace;font-size:12px"><?php echo htmlspecialchars($ip); ?></td><td><?php echo $cnt; ?></td></tr>
<?php endforeach; ?></table></td></tr>
<?php endif; ?>
</table></div>
