<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/support" class="action-card"><div class="icon">🎫</div><div class="name">Tickets</div></a>
<a href="/admin/support/kb" class="action-card"><div class="icon">📚</div><div class="name">Knowledgebase</div></a>
<a href="/admin/support/announcements" class="action-card"><div class="icon">📢</div><div class="name">Announcements</div></a>
</div>
<table><tr><th>Service</th><th>Status</th><th>Uptime</th></tr>
<?php foreach ($services as $s): ?>
<tr><td><strong><?php echo $s['name']; ?></strong></td>
<td><span class="status-badge status-<?php echo $s['status']; ?>"><?php echo $s['status']; ?></span></td>
<td style="font-size:13px;color:var(--text-secondary)"><?php echo htmlspecialchars($s['uptime'] ?: '-'); ?></td></tr>
<?php endforeach; ?></table>
