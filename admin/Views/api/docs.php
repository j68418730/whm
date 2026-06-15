<h3 style="color:var(--accent);margin-bottom:16px">API Documentation</h3>
<div class="card" style="margin-bottom:16px">
<h4 style="color:var(--accent);margin-bottom:8px">Authentication</h4>
<p style="color:var(--text-secondary);font-size:13px">Include your API key in the <code style="background:rgba(0,0,0,.3);padding:2px 6px;border-radius:3px">Authorization: Bearer YOUR_KEY</code> header. Generate keys from the <a href="/admin/api" style="color:var(--accent)">API Keys page</a>.</p>
</div>
<div class="card" style="margin-bottom:16px">
<h4 style="color:var(--accent);margin-bottom:8px">Rate Limiting</h4>
<p style="color:var(--text-secondary);font-size:13px">Rate limits are configured per key. Default: 60 requests/minute. Exceeded limits return HTTP 429.</p>
</div>
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Endpoints</h4>
<table><tr><th>Method</th><th>Path</th><th>Description</th><th>Auth</th></tr>
<?php foreach ($endpoints as $e): ?>
<tr><td><span class="status-badge" style="background:rgba(74,222,128,.12);color:#4ade80"><?php echo $e['method']; ?></span></td>
<td style="font-family:monospace;font-size:12px"><?php echo $e['path']; ?></td>
<td><?php echo $e['desc']; ?></td>
<td><span style="font-size:11px;color:var(--text-secondary)"><?php echo $e['auth']; ?></span></td></tr>
<?php endforeach; ?></table>
</div>
