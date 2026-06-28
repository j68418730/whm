<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent);margin:0">Support Tickets</h3>
<span style="font-size:13px;color:var(--text-secondary)"><?php echo count($tickets ?? []); ?> total</span>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:12px">
<?php if (!empty($tickets)): foreach ($tickets as $t): ?>
<div class="card" style="margin-bottom:0;padding:16px;position:relative">
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px">
<strong style="font-size:14px">#<?php echo $t->id; ?>: <?php echo htmlspecialchars($t->subject); ?></strong>
<span class="status-badge status-<?php echo $t->status === 'closed' ? 'terminated' : ($t->status === 'answered' ? 'active' : ''); ?>" style="font-size:10px"><?php echo $t->status; ?></span>
</div>
<div style="font-size:12px;color:#94a3b8;margin-bottom:4px"><?php echo htmlspecialchars($t->department); ?> · <?php echo $t->created_at; ?></div>
<div style="font-size:12px;color:var(--text-secondary);margin-bottom:10px"><?php echo htmlspecialchars(substr($t->message ?? '', 0, 120)); ?><?php echo strlen($t->message ?? '') > 120 ? '...' : ''; ?></div>
<div style="display:flex;gap:4px;flex-wrap:wrap">
<a href="/admin/support/tickets/<?php echo $t->id; ?>" class="btn btn-sm primary">👁 View & Reply</a>
<?php if ($t->status !== 'closed'): ?>
<a href="/admin/support/tickets/close/<?php echo $t->id; ?>" class="btn btn-sm secondary" style="color:#facc15">✕ Close</a>
<?php endif; ?>
<a href="/admin/support/tickets/delete/<?php echo $t->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete ticket #<?php echo $t->id; ?>?')">🗑 Delete</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:30px;grid-column:1/-1;color:#64748b">No tickets yet.</div>
<?php endif; ?>
</div>