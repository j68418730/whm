<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent)">Ticket #<?php echo $ticket->id; ?>: <?php echo htmlspecialchars($ticket->subject); ?></h3>
<p style="color:var(--text-secondary);font-size:13px">Department: <?php echo htmlspecialchars($ticket->department); ?> &middot; Status: <span class="status-badge status-<?php echo $ticket->status === 'closed' ? 'terminated' : 'active'; ?>"><?php echo $ticket->status; ?></span></p>
</div>
<div class="card" style="margin-bottom:16px;background:rgba(0,191,255,.03)">
<p style="color:var(--text-secondary);font-size:12px"><?php echo $ticket->created_at; ?></p>
<p style="margin-top:8px"><?php echo nl2br(htmlspecialchars($ticket->message ?? '')); ?></p>
</div>
<?php foreach ($replies as $r): ?>
<div class="card" style="margin-bottom:12px;background:rgba(255,255,255,.02)">
<p style="color:var(--text-secondary);font-size:12px"><?php echo $r->created_at; ?> &middot; <?php echo $r->admin_id ? 'Staff' : 'You'; ?></p>
<p style="margin-top:8px"><?php echo nl2br(htmlspecialchars($r->message ?? '')); ?></p>
</div>
<?php endforeach; ?>
<?php if ($ticket->status !== 'closed'): ?>
<div class="card" style="max-width:500px">
<form method="POST" action="/user/tickets/reply/<?php echo $ticket->id; ?>">
<h4 style="color:var(--accent);margin-bottom:8px">Reply</h4>
<div class="form-group"><textarea name="message" rows="3" required></textarea></div>
<button type="submit" class="btn primary">Post Reply</button>
<a href="/user/tickets/close/<?php echo $ticket->id; ?>" class="btn btn-sm danger" style="float:right">Close Ticket</a>
</form></div>
<?php endif; ?>
<a href="/user/tickets" class="btn secondary" style="margin-top:8px">&larr; Back to Tickets</a>
