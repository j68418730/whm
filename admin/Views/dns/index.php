<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<h2 style="margin:0; color:var(--accent, #008cff)">🌐 DNS Management</h2>
<a href="/admin/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i> Dashboard</a>
</div>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>DNS Zones</h3><div class="value"><?php echo $stats['total_zones'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Records</h3><div class="value"><?php echo $stats['total_records'] ?? 0; ?></div></div>
</div>

<div class="card" style="max-width:600px;margin-top:20px">
<div class="card-header" style="background:rgba(0,191,255,.1);border-bottom:1px solid rgba(0,191,255,.2)">
<h3 style="margin:0; color:var(--primary, #008cff)">Create DNS Zone</h3>
</div>
<div class="card-body">
<form method="POST" action="/admin/dns/create-zone">
<div class="form-group"><label>Domain</label><input name="domain" type="text" required placeholder="example.com"></div>
<div class="form-group"><label>Admin Email</label><input name="admin_email" type="text" value="admin@planet-hosts.com"></div>
<button type="submit" class="btn w-100" style="background:linear-gradient(135deg,var(--accent, #008cff),var(--accent-hover, #3bb8ff));border:none;border-radius:6px;padding:10px;font-weight:600">Create Zone</button>
</form>
</div>
</div>

<table class="table table-bordered align-middle mb-0">
<thead><tr><th>Domain</th><th>Records</th><th>Serial</th><th>Created</th><th>Actions</th></tr></thead>
<tbody>
<?php if (!empty($zones)): foreach ($zones as $z): ?>
<tr>
<td><strong><?php echo htmlspecialchars($z->domain); ?></strong></td>
<td style="font-size:12px;color:var(--secondary, #64748b)"><?php echo $z->record_count ?? 0; ?></td>
<td style="font-family:monospace;font-size:11px;"><?php echo $z->serial ?? '-'; ?></td>
<td><?php echo $z->created_at ?? '-'; ?></td>
<td style="white-space:nowrap">
<a href="/admin/dns/edit/<?php echo $z->id; ?>" class="btn btn-sm primary"><i class="bi bi-pencil me-1"></i> Edit</a>
<a href="/admin/dns/delete/<?php echo $z->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete zone?')">Delete</a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--secondary, #64748b)">No DNS zones yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<p class="text-muted small small">DNS zones manage domain name resolution. Each zone can contain multiple record types (A, AAAA, CNAME, MX, TXT, etc.).</p>
</div>