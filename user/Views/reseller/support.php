<h3 style="color:var(--accent);margin-bottom:12px">Support Tickets</h3>
<table><tr><th>#</th><th>Subject</th><th>Status</th><th>Date</th></tr>
<?php if (!empty($tickets)): foreach ($tickets as $t): ?>
<tr><td><?php echo $t->id; ?></td><td><?php echo htmlspecialchars($t->subject); ?></td>
<td><span class="status-badge status-<?php echo $t->status === 'closed' ? 'terminated' : 'active'; ?>"><?php echo $t->status; ?></span></td>
<td><?php echo $t->created_at; ?></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No tickets found.</td></tr>
<?php endif; ?></table>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>
