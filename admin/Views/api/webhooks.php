<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Webhooks</h3>
<a class="btn primary" onclick="document.getElementById('hookForm').classList.toggle('hidden')">Add Webhook</a>
</div>
<div id="hookForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/api/webhooks/store">
<div class="form-group"><label>Name</label><input name="name" required placeholder="Deploy hook"></div>
<div class="form-group"><label>Payload URL</label><input name="url" required placeholder="https://example.com/hook"></div>
<div class="form-group"><label>Events</label><select name="events"><option value="all">All Events</option><option value="account.create">Account Created</option><option value="account.suspend">Account Suspended</option><option value="account.terminate">Account Terminated</option><option value="invoice.paid">Invoice Paid</option><option value="ticket.created">Ticket Created</option></select></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<table><tr><th>Name</th><th>URL</th><th>Events</th><th>Secret</th><th>Status</th><th></th></tr>
<?php if (!empty($hooks)): foreach ($hooks as $h): ?>
<tr><td><?php echo htmlspecialchars($h->name); ?></td><td style="font-size:12px"><?php echo htmlspecialchars($h->url); ?></td><td><?php echo $h->events; ?></td><td style="font-family:monospace;font-size:11px"><?php echo substr($h->secret, 0, 8); ?>...</td>
<td><span class="status-badge status-<?php echo $h->is_active ? 'active' : 'terminated'; ?>"><?php echo $h->is_active ? 'Active' : 'Inactive'; ?></span></td>
<td><a href="/admin/api/webhooks/delete/<?php echo $h->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No webhooks configured.</td></tr>
<?php endif; ?></table>
