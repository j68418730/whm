<div class="card" style="margin-bottom:20px">
<div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
<div>
<h3 style="margin:0;color:var(--accent)">Widget Manager</h3>
<p style="margin:6px 0 0;color:var(--text-secondary)">The dashboard already supports drag and drop widgets. This page lists what is available and what is installed.</p>
</div>
<a href="/admin/dashboard" class="btn btn-sm secondary">Open Dashboard Layout</a>
</div>
</div>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Available Widgets</h3><div class="value"><?php echo count($all_widgets ?? []); ?></div></div>
<div class="stat-card"><h3>Installed Widgets</h3><div class="value"><?php echo count($user_widgets ?? []); ?></div></div>
<div class="stat-card"><h3>Main Zone</h3><div class="value"><?php echo count(array_filter($user_widgets ?? [], fn($w) => ($w['zone'] ?? '') === 'main')); ?></div></div>
<div class="stat-card"><h3>Side Zone</h3><div class="value"><?php echo count(array_filter($user_widgets ?? [], fn($w) => ($w['zone'] ?? '') === 'side')); ?></div></div>
</div>

<div class="card" style="margin-bottom:20px">
<h3 style="margin-bottom:12px;color:var(--accent)">Available Widgets</h3>
<table>
<tr><th>Widget</th><th>Description</th><th>Icon</th></tr>
<?php if (!empty($all_widgets)): foreach ($all_widgets as $widget): ?>
<tr>
<td><?php echo htmlspecialchars($widget->getName(), ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($widget->getDescription(), ENT_QUOTES, 'UTF-8'); ?></td>
<td><i class="bi <?php echo htmlspecialchars($widget->getIcon(), ENT_QUOTES, 'UTF-8'); ?>"></i></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="3" style="text-align:center;color:var(--text-secondary);padding:20px">No widgets registered.</td></tr>
<?php endif; ?>
</table>
</div>

<div class="card">
<h3 style="margin-bottom:12px;color:var(--accent)">Installed Widgets</h3>
<table>
<tr><th>Widget Key</th><th>Zone</th><th>Sort</th></tr>
<?php if (!empty($user_widgets)): foreach ($user_widgets as $widget): ?>
<tr>
<td style="font-family:monospace"><?php echo htmlspecialchars($widget['widget_key'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($widget['zone'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo (int)($widget['sort_order'] ?? 0); ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="3" style="text-align:center;color:var(--text-secondary);padding:20px">No widgets installed yet.</td></tr>
<?php endif; ?>
</table>
</div>
