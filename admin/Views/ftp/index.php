<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>FTP Server</h3><div class="value" style="font-size:16px"><?php echo $ftpStats['ftp_server'] ?? 'VSFTPD'; ?></div></div>
<div class="stat-card"><h3>Status</h3><div class="value" style="font-size:16px;color:<?php echo ($ftpStats['active'] ?? '') === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $ftpStats['active'] ?? 'Unknown'; ?></div></div>
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
