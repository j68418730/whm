<h2>FTP Accounts</h2>
<p style="color:#64748b;margin-bottom:16px">Manage your FTP accounts for file access.</p>
<div class="card">
<h3>Primary FTP Account</h3>
<div style="display:grid;grid-template-columns:120px 1fr;gap:6px;font-size:13px">
<span style="color:#64748b">Server:</span><span><code><?php echo htmlspecialchars(\['HTTP_HOST'] ?? 'localhost'); ?></code></span>
<span style="color:#64748b">Username:</span><span><code><?php echo htmlspecialchars(\->username ?? ''); ?></code></span>
<span style="color:#64748b">Port:</span><span><code>21</code></span>
</div>
<a href="/user/files" class="btn btn-sm" style="margin-top:12px;display:inline-block;background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:6px 14px;border-radius:6px;text-decoration:none">📁 Open File Manager</a>
</div>
