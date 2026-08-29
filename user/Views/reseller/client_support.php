<h3 style="color:var(--accent);margin-bottom:12px">Support System</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">Tickets from <b>your own clients</b>. Client tickets route to you first; anything you escalate goes up to Planet Hosts support (two-level support).</p>
<table class="table">
<tr><th>#</th><th>Client</th><th>Subject</th><th>Priority</th><th>Status</th><th>Date</th></tr>
<?php if (!empty($tickets)): foreach ($tickets as $t): ?>
<tr>
<td><?php echo (int)$t->id; ?></td>
<td><?php echo htmlspecialchars($t->customer); ?></td>
<td><?php echo htmlspecialchars($t->subject); ?></td>
<td><?php echo htmlspecialchars($t->priority ?: '-'); ?></td>
<td><span class="status-badge status-<?php echo $t->status === 'closed' ? 'terminated' : 'active'; ?>"><?php echo htmlspecialchars($t->status); ?></span></td>
<td><?php echo $t->created_at; ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No client tickets found.</td></tr>
<?php endif; ?>
</table>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>