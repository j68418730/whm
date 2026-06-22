<div class="card" style="margin-bottom:20px">
<div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
<div>
<h3 style="margin:0;color:var(--accent)">API Logs</h3>
<p style="margin:6px 0 0;color:var(--text-secondary)">If an API audit table is present, it shows up here. Otherwise this page stays ready for the log sink you choose later.</p>
</div>
<a href="/admin/api/docs" class="btn btn-sm secondary">API Docs</a>
</div>
</div>

<div class="card">
<table>
<tr><th>Time</th><th>Key</th><th>Endpoint</th><th>Status</th><th>IP</th></tr>
<?php if (!empty($logs)): foreach ($logs as $log): ?>
<tr>
<td><?php echo htmlspecialchars($log->created_at ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($log->api_key ?? $log->key_name ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td style="font-family:monospace"><?php echo htmlspecialchars($log->endpoint ?? $log->path ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($log->status_code ?? $log->status ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td style="font-family:monospace"><?php echo htmlspecialchars($log->ip_address ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;color:var(--text-secondary);padding:20px">No API log table is configured yet.</td></tr>
<?php endif; ?>
</table>
</div>
