<h3 style="color:var(--accent);margin:0 0 8px"><i class="bi bi-bell"></i> Alerts</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">
Notifications for your reseller account — invoices, new clients/orders, quota warnings, and messages from Planet Hosts.
</p>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php
    $typeIcon = ['info' => 'ℹ️', 'warning' => '⚠️', 'success' => '✅', 'danger' => '⛔'];
    $typeColor = ['info' => '#38bdf8', 'warning' => '#facc15', 'success' => '#4ade80', 'danger' => '#f87171'];
    $sourceLabel = ['quota' => 'Quota', 'invoice' => 'Invoice', 'client' => 'Client', 'order' => 'Order', 'admin' => 'Support'];
?>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px">
<span style="font-size:12px;color:var(--text_muted,#94a3b8)"><?php echo count($alerts); ?> alert<?php echo count($alerts) !== 1 ? 's' : ''; ?> · <?php echo $unread; ?> unread</span>
<?php if ($unread > 0): ?>
<a href="/reseller/alerts/read-all" class="btn btn-sm btn-secondary" style="padding:5px 14px;font-size:12px"><i class="bi bi-check2-all"></i> Mark all read</a>
<?php endif; ?>
</div>

<?php if (empty($alerts)): ?>
<div class="card" style="padding:30px;text-align:center">
<div style="font-size:34px;margin-bottom:10px">🔔</div>
<div style="color:var(--text_muted,#94a3b8);font-size:14px">No alerts right now. You're all caught up.</div>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:10px">
<?php foreach ($alerts as $a): $t = $a['type'] ?? 'info'; $src = $a['source'] ?? ''; $unread = (($src === 'admin' || $src === 'admin_client') && empty($a['is_read'])); ?>
<div class="card" style="margin:0;padding:14px 16px;display:flex;align-items:flex-start;gap:12px;<?php echo ($a['source'] ?? '') === 'quota' ? 'border:1px solid ' . ($typeColor[$t] ?? '#999') . ';background:rgba(248,113,113,.06);' : ''; ?><?php echo $unread ? 'border-left:3px solid ' . ($typeColor[$t] ?? '#999') . ';' : ''; ?>">
<div style="font-size:20px;line-height:1"><?php echo $typeIcon[$t] ?? 'ℹ️'; ?></div>
<div style="flex:1;min-width:0">
<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
<span style="color:<?php echo $typeColor[$t] ?? '#999'; ?>;font-weight:700;font-size:13px"><?php echo htmlspecialchars($a['title']); ?></span>
<?php if ($unread): ?><span style="font-size:9px;background:rgba(56,189,248,.15);color:#38bdf8;padding:1px 7px;border-radius:99px">NEW</span><?php endif; ?>
</div>
<?php if (!empty($a['message'])): ?>
<div style="color:var(--text_muted,#94a3b8);font-size:13px;margin-top:3px"><?php echo $a['message']; ?></div>
<?php endif; ?>
<div style="display:flex;align-items:center;gap:12px;margin-top:8px;flex-wrap:wrap">
<span style="font-size:11px;color:#64748b"><?php echo ($sourceLabel[$a['source']] ?? 'System'); ?> · <?php echo date('M j, Y g:i A', strtotime($a['created_at'])); ?></span>
<?php if (!empty($a['link'])): ?>
<a href="<?php echo $a['link']; ?>" style="font-size:12px;color:var(--primary,#008cff);text-decoration:none">View →</a>
<?php endif; ?>
<?php if ($src === 'admin' && $unread): ?>
<a href="/reseller/alerts/read/<?php echo preg_replace('/[^0-9]/', '', $a['id']); ?>" style="font-size:12px;color:var(--text_muted,#94a3b8);text-decoration:none">Mark read</a>
<?php elseif ($src === 'admin_client' && $unread): ?>
<a href="/reseller/alerts/read-user/<?php echo (int)$a['user_alert_id']; ?>" style="font-size:12px;color:var(--text_muted,#94a3b8);text-decoration:none">Mark read</a>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
