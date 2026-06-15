<h3 style="color:var(--accent);margin-bottom:16px">Rate Limiting</h3>
<p style="color:var(--text-secondary);margin-bottom:16px">Rate limits prevent abuse by limiting requests per minute for each API key.</p>
<table><tr><th>Key Name</th><th>Rate Limit (req/min)</th><th>Status</th></tr>
<?php if (!empty($keys)): foreach ($keys as $k): ?>
<tr><td><?php echo htmlspecialchars($k->name); ?></td><td><?php echo $k->rate_limit ?? 60; ?></td>
<td><span class="status-badge status-<?php echo ($k->is_active ?? 1) ? 'active' : 'terminated'; ?>"><?php echo ($k->is_active ?? 1) ? 'Active' : 'Disabled'; ?></span></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No keys to display.</td></tr>
<?php endif; ?></table>
