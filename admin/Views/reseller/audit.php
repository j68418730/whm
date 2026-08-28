<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
<h3 style="margin:0">Audit Log — <?php echo htmlspecialchars($reseller->company_name); ?></h3>
<a href="/admin/reseller/show/<?php echo $reseller->id; ?>" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<div class="card">
<?php if (!empty($logs)): ?>
<table style="font-size:13px">
<tr><th>When</th><th>Action</th><th>Resource</th><th>By</th><th>IP</th><th>Details</th></tr>
<?php foreach ($logs as $l): ?>
<tr>
<td style="white-space:nowrap"><?php echo htmlspecialchars($l->created_at ?? ''); ?></td>
<td><code><?php echo htmlspecialchars($l->action); ?></code></td>
<td><?php echo $l->resource_type ? htmlspecialchars($l->resource_type . '#' . $l->resource_id) : '—'; ?></td>
<td><?php echo htmlspecialchars($l->staff_email ?? '-'); ?></td>
<td><?php echo htmlspecialchars($l->ip_address ?? '-'); ?></td>
<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo htmlspecialchars($l->details ?? ''); ?>"><?php echo htmlspecialchars(substr((string)($l->details ?? ''),0,90)); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?><p style="color:#64748b;font-size:13px">No audit entries yet.</p><?php endif; ?>
</div>