<h2>📁 File Manager</h2>
<p style="color:#64748b;font-size:12px;margin-bottom:14px">Browse and manage files across all hosting accounts.</p>

<?php if (isset($users)): ?>
<div style="margin-bottom:12px;display:flex;gap:6px;flex-wrap:wrap">
<?php foreach ($users as $u): ?>
<a href="/admin/files?user=<?php echo urlencode($u->username); ?>" class="btn btn-sm btn-secondary">👤 <?php echo htmlspecialchars($u->username); ?></a>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div style="background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:8px;padding:20px;text-align:center;color:#64748b;font-size:13px">
Select a user above to browse their files.
</div>
