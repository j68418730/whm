<div class="card" style="margin-bottom:16px"><h3 style="color:var(--accent)">Support Tickets</h3></div>
<?php if (!empty($tickets)): ?>
<table><tr><th>#</th><th>Subject</th><th>Department</th><th>Status</th><th>Date</th><th></th></tr>
<?php foreach ($tickets as $t): ?>
<tr>
<td><?php echo $t->id; ?></td>
<td><?php echo htmlspecialchars($t->subject); ?></td>
<td><?php echo htmlspecialchars($t->department); ?></td>
<td><span class="status-badge status-<?php echo $t->status === 'closed' ? 'terminated' : 'active'; ?>"><?php echo $t->status; ?></span></td>
<td><?php echo $t->created_at; ?></td>
<td><a href="/user/tickets/<?php echo $t->id; ?>" class="btn btn-sm primary">View</a></td>
</tr>
<?php endforeach; ?></table>
<?php else: ?>
<div class="card"><p style="color:var(--text-secondary)">No support tickets yet. <a href="/user/support" style="color:var(--accent)">Open a ticket</a></p></div>
<?php endif; ?>
