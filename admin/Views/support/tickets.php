<div class="card" style="margin-bottom:16px"><h3 style="color:var(--accent)">Support Tickets</h3>
<p style="color:var(--text-secondary);font-size:13px"><?php echo count($tickets ?? []); ?> total tickets</p></div>
<table><tr><th>#</th><th>Subject</th><th>Department</th><th>Status</th><th>Date</th><th></th></tr>
<?php if (!empty($tickets)): foreach ($tickets as $t): ?>
<tr><td><?php echo $t->id; ?></td><td><?php echo htmlspecialchars($t->subject); ?></td><td><?php echo htmlspecialchars($t->department); ?></td>
<td><span class="status-badge status-<?php echo $t->status === 'closed' ? 'terminated' : ($t->status === 'answered' ? 'active' : ''); ?>"><?php echo $t->status; ?></span></td>
<td><?php echo $t->created_at; ?></td><td><a href="/admin/support/tickets/<?php echo $t->id; ?>" class="btn btn-sm primary">View</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No tickets yet.</td></tr>
<?php endif; ?></table>
