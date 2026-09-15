<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<h2 style="margin:0; color:var(--accent, #008cff)">⚙️ Server Configuration</h2>
<a href="/admin/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i> Dashboard</a>
</div>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>Hostname</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($hostname); ?></div></div>
<div class="stat-card"><h3>Server IP</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($serverIp); ?></div></div>
<div class="stat-card"><h3>OS</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($os); ?></div></div>
<div class="stat-card"><h3>Kernel</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars($kernel); ?></div></div>
<div class="stat-card"><h3>Services</h3><div class="value" style="font-size:14px"><?php echo count($activeServices) . '/' . count($allServices); ?></div></div>
</div>
</div>

<div class="table-responsive">
<table class="table table-bordered align-middle mb-0">
<thead><tr><th>Setting</th><th>Value</th><th>Category</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($serverConfig as $setting): ?>
<tr>
<td style="width:300px;"><strong><?php echo htmlspecialchars($setting['name']); ?></strong> <span style="color:var(--secondary, #64748b);font-size:11px">(id: <?php echo $setting['id']; ?>)</span></td>
<td style="width:200px;"><kbd style="background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px;font-size:12px;color:#fff"><?php echo htmlspecialchars($setting['value']); ?></kbd></td>
<td style="width:150px;"><span style="background:rgba(0,191,255,.1);padding:2px 4px;border-radius:4px;font-size:10px;color:var(--primary, #008cff)"><?php echo htmlspecialchars($setting['category']); ?></span></td>
<td style="width:100px">
<form method="POST" style="display:inline" action="/admin/serverconfig/toggle/<?php echo $setting['id']; ?>">
<input type="hidden" name="enabled" value="<?php echo $setting['enabled'] ? '1' : '0'; ?>">
<button class="btn btn-sm btn-primary" style="padding:2px 6px;font-size:10px">Toggle</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="mt-4">
<p class="text-muted small">514 server configuration settings across categories: PHP, Mail, Security, Streaming, DNS, and more. Use toggle buttons to enable/disable individual settings.</p>
</div>
</div>