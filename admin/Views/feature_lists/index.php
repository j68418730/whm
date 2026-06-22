<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0"><i class="bi bi-ui-checks"></i> Feature Lists</h2>
<p style="color:#64748b;margin:4px 0 0">Manage feature sets that control what hosting accounts can do.</p>
</div>
<a href="/admin/feature-lists/create" class="btn primary"><i class="bi bi-plus-lg"></i> Create Feature List</a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px">
<?php if (empty($lists)): ?>
<div class="card" style="grid-column:1/-1;text-align:center;padding:40px">
<p style="color:#64748b">No feature lists yet. <a href="/admin/feature-lists/create">Create one</a>.</p>
</div>
<?php else: foreach ($lists as $l): ?>
<div class="card" style="padding:20px">
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px">
<h3 style="margin:0;font-size:16px;color:var(--accent)"><?php echo htmlspecialchars($l->name); ?></h3>
<div style="display:flex;gap:6px">
<a href="/admin/feature-lists/edit/<?php echo $l->id; ?>" class="btn btn-sm secondary"><i class="bi bi-pencil"></i></a>
<a href="/admin/feature-lists/delete/<?php echo $l->id; ?>" class="btn btn-sm secondary" onclick="return confirm('Delete this feature list?')"><i class="bi bi-trash"></i></a>
</div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:12px;color:#94a3b8">
<span>📧 Email: <?php echo $l->email_accounts < 0 ? '∞' : $l->email_accounts; ?></span>
<span>📁 FTP: <?php echo $l->ftp_accounts < 0 ? '∞' : $l->ftp_accounts; ?></span>
<span>🗄️ DBs: <?php echo $l->databases < 0 ? '∞' : $l->databases; ?></span>
<span>👤 DB Users: <?php echo $l->database_users < 0 ? '∞' : $l->database_users; ?></span>
<span>📋 Subdomains: <?php echo $l->subdomains < 0 ? '∞' : $l->subdomains; ?></span>
<span>📍 Parked: <?php echo $l->parked_domains < 0 ? '∞' : $l->parked_domains; ?></span>
<span>➕ Addon: <?php echo $l->addon_domains < 0 ? '∞' : $l->addon_domains; ?></span>
<span>⏰ Cron: <?php echo $l->cron_jobs ? 'Yes' : 'No'; ?></span>
<span>🔑 SSH: <?php echo $l->ssh_access ? 'Yes' : 'No'; ?></span>
<span>🔒 SSL: <?php echo $l->ssl_allowed ? 'Yes' : 'No'; ?></span>
<span>📦 Git: <?php echo $l->git_access ? 'Yes' : 'No'; ?></span>
<span>🔧 NodeJS: <?php echo $l->nodejs ? 'Yes' : 'No'; ?></span>
<span>🐍 Python: <?php echo $l->python ? 'Yes' : 'No'; ?></span>
<span>💎 Ruby: <?php echo $l->ruby ? 'Yes' : 'No'; ?></span>
<span>🖥️ Terminal: <?php echo $l->terminal ? 'Yes' : 'No'; ?></span>
<span>💾 Backups: <?php echo $l->backups ? 'Yes' : 'No'; ?></span>
</div>
<?php
$usedBy = array_filter($packages ?? [], function($p) use ($l) { return $p->feature_list_id == $l->id; });
if (!empty($usedBy)): ?>
<div style="margin-top:10px;font-size:11px;color:#64748b;border-top:1px solid rgba(255,255,255,.05);padding-top:8px">
Used by: <?php echo implode(', ', array_map(function($p) { return htmlspecialchars($p->name); }, $usedBy)); ?>
</div>
<?php endif; ?>
</div>
<?php endforeach; endif; ?>
</div>
