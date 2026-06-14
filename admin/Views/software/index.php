<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Node.js</h3><div class="value" style="font-size:18px"><?php echo htmlspecialchars($node); ?></div><div class="label">npm <?php echo htmlspecialchars($npm); ?></div></div>
<div class="stat-card"><h3>Python</h3><div class="value" style="font-size:18px"><?php echo htmlspecialchars($python); ?></div><div class="label">pip <?php echo htmlspecialchars($pip); ?></div></div>
<div class="stat-card"><h3>Composer</h3><div class="value" style="font-size:18px"><?php echo htmlspecialchars($composer); ?></div></div>
<div class="stat-card"><h3>Git</h3><div class="value" style="font-size:18px"><?php echo htmlspecialchars($git); ?></div></div>
</div>

<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/installers" class="action-card"><div class="icon">📦</div><div class="name">One-Click Installer</div></a>
<a href="/admin/php" class="action-card"><div class="icon">🐘</div><div class="name">PHP Manager</div></a>
<a href="/admin/marketplace" class="action-card"><div class="icon">🏪</div><div class="name">Application Marketplace</div></a>
<a href="/admin/container" class="action-card"><div class="icon">🐳</div><div class="name">Docker Manager</div></a>
</div>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Node.js Applications</h3>
<p style="color:var(--text-secondary);margin-bottom:12px">Create and manage Node.js applications with automatic process management.</p>
<table><tr><th>App</th><th>Status</th><th>Port</th><th>Actions</th></tr>
<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No Node.js applications yet. Use the terminal to create one.</td></tr>
</table></div>

<div class="card" style="margin-top:16px"><h3 style="color:var(--accent);margin-bottom:12px">Python Applications</h3>
<p style="color:var(--text-secondary);margin-bottom:12px">Create and manage Python applications (Django, Flask, etc.) with virtualenv support.</p>
<table><tr><th>App</th><th>Status</th><th>Port</th><th>Actions</th></tr>
<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No Python applications yet. Use the terminal to create one.</td></tr>
</table></div>
