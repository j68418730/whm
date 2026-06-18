<h2 style="margin-bottom:16px">📝 Customer Reviews</h2>
<table>
<tr><th>Name</th><th>Rating</th><th>Review</th><th>Date</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($reviews as $r): ?>
<tr>
<td><?php echo htmlspecialchars($r->name); ?></td>
<td style="color:#facc15;font-size:16px"><?php echo str_repeat('★', (int)$r->rating) . str_repeat('☆', 5 - (int)$r->rating); ?></td>
<td><?php echo htmlspecialchars(substr($r->text ?? '', 0, 80)); ?>...</td>
<td><?php echo date('M j, Y', strtotime($r->created_at)); ?></td>
<td><?php echo $r->approved ? '<span style="color:#4ade80">Approved</span>' : '<span style="color:#facc15">Pending</span>'; ?></td>
<td style="display:flex;gap:4px">
<?php if (!$r->approved): ?><a href="/admin/reviews/approve/<?php echo $r->id; ?>" class="btn btn-sm primary">✓ Approve</a><?php endif; ?>
<a href="/admin/reviews/delete/<?php echo $r->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Delete?')">🗑</a>
</td>
</tr>
<?php endforeach; ?>
</table>
