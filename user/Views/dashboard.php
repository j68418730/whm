<div class="stats-grid">
<div class="stat-card"><h3>Services</h3><div class="value">0</div></div>
<div class="stat-card"><h3>Open Tickets</h3><div class="value">0</div></div>
<div class="stat-card"><h3>Invoices</h3><div class="value">0</div></div>
<div class="stat-card"><h3>Disk Usage</h3><div class="value" style="font-size:20px"><?php echo $diskUsed ?? 0; ?> GB</div></div>
<?php if (!$isRadio && $emailAllowed): ?>
<div class="stat-card"><h3>Email Accounts</h3><div class="value"><?php echo $emailCount; ?></div></div>
<?php endif; ?>
</div>
<?php if (!empty($notifications)): foreach ($notifications as $n): ?>
<div class="alert alert-error" style="margin-bottom:12px;<?php echo $n['type'] === 'warning' ? 'background:rgba(250,204,21,.08);border-color:rgba(250,204,21,.2);color:#facc15' : ''; ?>"><?php echo htmlspecialchars($n['msg']); ?></div>
<?php endforeach; endif; ?>
<div class="page-grid" style="margin-bottom:20px">
<a href="/user/services" class="action-card"><div class="icon">🖥</div><div class="name">My Services</div></a>
<a href="/user/files" class="action-card"><div class="icon">📁</div><div class="name">File Manager</div></a>
<a href="/user/usage" class="action-card"><div class="icon">📊</div><div class="name">Resource Usage</div></a>
<a href="/user/tickets" class="action-card"><div class="icon">🎫</div><div class="name">Support Tickets</div></a>
<a href="/user/invoices" class="action-card"><div class="icon">💰</div><div class="name">Invoices</div></a>
<?php if (!$isRadio && $emailAllowed): ?>
<a href="/user/email" class="action-card"><div class="icon">📧</div><div class="name">Email</div></a>
<?php endif; ?>
<a href="/user/profile" class="action-card"><div class="icon">👤</div><div class="name">Profile</div></a>
<a href="/user/security" class="action-card"><div class="icon">🔒</div><div class="name">Security</div></a>
</div>

<?php if (!empty($streams)): ?>
<div class="card" style="padding:14px 20px;margin-bottom:12px">
<h3 style="color:var(--accent);margin-bottom:10px"><?php echo $isRadio ? '📻 Radio Streams' : 'Streams'; ?></h3>
<?php foreach ($streams as $s): ?>
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<div><strong><?php echo htmlspecialchars($s->server_type ?? 'Stream'); ?></strong> port <?php echo $s->port; ?> <span class="status-badge status-<?php echo $s->status === 'running' ? 'active' : 'terminated'; ?>"><?php echo $s->status; ?></span></div>
<div style="display:flex;gap:4px">
<a href="/user/start/<?php echo $s->id; ?>" class="btn btn-sm primary">▶</a>
<a href="/user/stop/<?php echo $s->id; ?>" class="btn btn-sm danger">⏹</a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!$isRadio && $emailAllowed): ?>
<div class="card"><h3 style="color:var(--accent);margin-bottom:8px">📧 Email Overview</h3>
<p style="color:var(--text-secondary);font-size:13px"><?php echo $emailCount; ?> email account(s) on your domain. <a href="/user/email" style="color:var(--accent)">Manage</a> &middot; <a href="http://45.61.59.55:2096/" target="_blank" style="color:var(--accent)">Webmail</a></p>
</div>
<?php endif; ?>
