<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent)">Ticket #<?php echo $ticket->id; ?>: <?php echo htmlspecialchars($ticket->subject ?? ''); ?></h3>
<p style="color:var(--text-secondary);font-size:13px">Department: <?php echo htmlspecialchars($ticket->department ?? ''); ?> &middot; Status: <span class="status-badge status-<?php echo $ticket->status === 'closed' ? 'terminated' : 'active'; ?>"><?php echo $ticket->status; ?></span></p>
</div>
<div class="card" style="margin-bottom:12px;background:rgba(0,191,255,.03)"><p style="color:var(--text-secondary);font-size:12px;margin-bottom:4px">User opened on <?php echo $ticket->created_at; ?></p><p><?php echo nl2br(htmlspecialchars($ticket->message ?? '')); ?></p></div>
<?php foreach ($replies as $r): ?>
<div class="card" style="margin-bottom:12px;background:<?php echo $r->admin_id ? 'rgba(74,222,128,.04)' : 'rgba(255,255,255,.02)'; ?>">
<p style="color:var(--text-secondary);font-size:12px;margin-bottom:4px"><?php echo $r->created_at; ?> &middot; <?php echo $r->admin_id ? 'Staff' : 'User'; ?></p>
<p><?php echo nl2br(htmlspecialchars($r->message ?? '')); ?></p></div>
<?php endforeach; ?>
<?php if ($ticket->status !== 'closed'): ?>
<div class="card" style="max-width:500px;margin-top:16px">
<form method="POST" action="/admin/support/tickets/reply/<?php echo $ticket->id; ?>">
<h4 style="color:var(--accent);margin-bottom:8px">Post Reply</h4>
<div class="form-group"><textarea name="message" rows="3" required></textarea></div>
<button type="submit" class="btn primary">Reply</button>
<a href="/admin/support/tickets/close/<?php echo $ticket->id; ?>" class="btn btn-sm danger" style="float:right">Close Ticket</a>
</form></div>
<?php endif; ?>
<a href="/admin/support/tickets" class="btn secondary" style="margin-top:8px">&larr; Back</a>
