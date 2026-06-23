<h2>FTP Accounts</h2>
<p style="color:#64748b;margin-bottom:16px">Use these credentials to connect via any FTP client (FileZilla, WinSCP, etc.).</p>

<div class="card" style="max-width:500px">
<h3>Primary FTP Account</h3>
<div style="display:grid;grid-template-columns:140px 1fr;gap:8px;font-size:13px;margin-top:12px">
<span style="color:#64748b">Server:</span><span><code style="background:rgba(0,0,0,.4);padding:2px 8px;border-radius:4px"><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?></code></span>
<span style="color:#64748b">Port:</span><span><code style="background:rgba(0,0,0,.4);padding:2px 8px;border-radius:4px">21</code></span>
<span style="color:#64748b">Username:</span><span><code style="background:rgba(0,0,0,.4);padding:2px 8px;border-radius:4px"><?php echo htmlspecialchars($hosting->username ?? ''); ?></code></span>
<span style="color:#64748b">Password:</span><span><code style="background:rgba(0,0,0,.4);padding:2px 8px;border-radius:4px">(same as account password)</code></span>
<span style="color:#64748b">Protocol:</span><span>SFTP (port 22) or FTP (port 21)</span>
</div>
<div style="margin-top:16px;display:flex;gap:8px">
<a href="/user/files" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:8px 16px;border-radius:6px;text-decoration:none;font-size:13px">📁 Open File Manager</a>
</div>
</div>

<div class="card" style="margin-top:16px;max-width:500px">
<h4 style="color:var(--accent);margin:0 0 8px;font-size:13px">Quick Connect</h4>
<p style="font-size:12px;color:#64748b">Use these links to connect instantly:</p>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
<a href="ftp://<?php echo htmlspecialchars($hosting->username ?? ''); ?>@<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2);padding:6px 12px;border-radius:6px;text-decoration:none;font-size:12px" target="_blank">🔗 FTP Connection</a>
<a href="sftp://<?php echo htmlspecialchars($hosting->username ?? ''); ?>@<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?>" class="btn btn-sm" style="background:rgba(56,189,248,.1);color:#38bdf8;border:1px solid rgba(56,189,248,.2);padding:6px 12px;border-radius:6px;text-decoration:none;font-size:12px" target="_blank">🔗 SFTP Connection</a>
</div>
</div>
