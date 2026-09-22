<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>FTP Server</h3><div class="value" style="font-size:16px"><?php echo $ftpStats['ftp_server'] ?? 'VSFTPD'; ?></div></div>
<div class="stat-card"><h3>Status</h3><div class="value" style="font-size:16px;color:<?php echo ($ftpStats['active'] ?? '') === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $ftpStats['active'] ?? 'Unknown'; ?></div></div>
</div>
<div class="card" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
<div>
<h3 style="color:var(--accent);margin:0 0 4px">🌐 Connect via FTP</h3>
<p style="margin:0;color:var(--text-secondary);font-size:13px"><?php echo htmlspecialchars($serverIp ?? $_SERVER['SERVER_ADDR'] ?? '15.204.114.226'); ?> · port 21 · passive ports <?php echo ($ftpStats['passive_min'] ?? 30000) . '-' . ($ftpStats['passive_max'] ?? 50000); ?></p>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="ftp://<?php echo htmlspecialchars($serverIp ?? $_SERVER['SERVER_ADDR'] ?? '15.204.114.226'); ?>" class="btn primary" style="text-decoration:none"><i class="bi bi-folder-symlink"></i> Open in File Explorer</a>
<a href="ftps://<?php echo htmlspecialchars($serverIp ?? $_SERVER['SERVER_ADDR'] ?? '15.204.114.226'); ?>" class="btn secondary" style="text-decoration:none"><i class="bi bi-shield-lock"></i> FTPS (SSL)</a>
<a href="filezilla://<?php echo htmlspecialchars($serverIp ?? $_SERVER['SERVER_ADDR'] ?? '15.204.114.226'); ?>" class="btn secondary" style="text-decoration:none" title="Opens FileZilla if installed"><i class="bi bi-transfer"></i> FileZilla</a>
</div>
</div>
<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">FTP Configuration</h3>
<table><tr><th>Setting</th><th>Value</th></tr>
<tr><td>Server</td><td>VSFTPD (vsftpd)</td></tr>
<tr><td>Anonymous FTP</td><td><?php echo ($ftpStats['anonymous_enabled'] ?? false) ? 'Enabled' : 'Disabled'; ?></td></tr>
<tr><td>Passive Ports</td><td><?php echo ($ftpStats['passive_min'] ?? 30000) . ' - ' . ($ftpStats['passive_max'] ?? 50000); ?></td></tr>
<tr><td>Config File</td><td style="font-family:monospace;font-size:12px">/etc/vsftpd.conf</td></tr>
<tr><td>Service Command</td><td style="font-family:monospace;font-size:12px">systemctl restart vsftpd</td></tr>
</table>
<div style="margin-top:12px">
<a href="/admin/serverconfig" class="btn primary">Manage Services</a>
<a href="/admin/ftp" class="btn secondary">Refresh</a>
</div>
</div>
