<style>
.role-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
.role-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.role-card h4{color:#0f172a;font-size:14px;font-weight:700;margin:0 0 4px}
.role-card .email{color:#64748b;font-size:12px}
.role-card .badge-role{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-super{background:#fef3c7;color:#92400e}
.badge-admin{background:#dbeafe;color:#1e40af}
.badge-support{background:#e0f2fe;color:#075985}
.badge-sales{background:#dcfce7;color:#166534}
.badge-billing{background:#f3e8ff;color:#6b21a8}
.badge-tech{background:#e0f2fe;color:#0c4a6e}
.badge-server{background:#fce7f3;color:#9d174d}
.badge-streaming{background:#ede9fe;color:#5b21b6}
.badge-game{background:#fef3c7;color:#92400e}
.badge-domain{background:#e0f2fe;color:#0c4a6e}
.badge-cpanel{background:#dbeafe;color:#1e40af}
.badge-abuse{background:#fee2e2;color:#991b1b}
.badge-dmca{background:#fce7f3;color:#9d174d}
.badge-linux{background:#e0f2fe;color:#0c4a6e}
.badge-windows{background:#dbeafe;color:#1e40af}
.badge-user{background:#f1f5f9;color:#475569}
</style>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success" style="background:#ecfdf5;color:#166534;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h2 style="margin:0;color:#0f172a">User Roles</h2>
</div>

<!-- Role Legend -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:16px">
<div style="font-size:12px;font-weight:600;color:#0f172a;margin-bottom:8px">Available Roles</div>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<span class="badge-role badge-super">Super Admin</span>
<span class="badge-role badge-admin">Admin</span>
<span class="badge-role badge-support">Support Staff</span>
<span class="badge-role badge-sales">Sales</span>
<span class="badge-role badge-billing">Billing</span>
<span class="badge-role badge-tech">Technical Support</span>
<span class="badge-role badge-server">Server Support</span>
<span class="badge-role badge-streaming">Streaming Support</span>
<span class="badge-role badge-game">Game Server Support</span>
<span class="badge-role badge-domain">Domain Support</span>
<span class="badge-role badge-cpanel">Control Panel Support</span>
<span class="badge-role badge-abuse">Abuse Department</span>
<span class="badge-role badge-dmca">DMCA / Copyright</span>
<span class="badge-role badge-linux">Linux Support</span>
<span class="badge-role badge-windows">Windows Server Support</span>
<span class="badge-role badge-user">User</span>
</div>
</div>

<h3 style="font-size:16px;color:#0f172a;margin:0 0 12px">Admins & Staff</h3>
<div class="role-grid">
<?php if (!empty($admins)): foreach ($admins as $a):
$badgeMap = [
    'super' => 'badge-super', 'admin' => 'badge-admin', 'support' => 'badge-support',
    'sales' => 'badge-sales', 'billing' => 'badge-billing', 'technical' => 'badge-tech',
    'server' => 'badge-server', 'streaming' => 'badge-streaming', 'game' => 'badge-game',
    'domain' => 'badge-domain', 'cpanel' => 'badge-cpanel', 'abuse' => 'badge-abuse',
    'dmca' => 'badge-dmca', 'linux' => 'badge-linux', 'windows' => 'badge-windows',
];
$roleLabel = $a->role ?? 'admin';
$badgeCls = $badgeMap[$roleLabel] ?? 'badge-admin';
$roleDisplay = ucfirst($roleLabel);
?>
<div class="role-card">
<div style="display:flex;justify-content:space-between;align-items:start">
<div>
<h4><?php echo htmlspecialchars($a->username ?: $a->name); ?></h4>
<div class="email"><?php echo htmlspecialchars($a->email); ?></div>
</div>
<span class="badge-role <?php echo $badgeCls; ?>"><?php echo htmlspecialchars($roleDisplay); ?></span>
</div>
<div style="font-size:11px;color:#94a3b8;margin-top:6px">Created: <?php echo $a->created_at ?? '-'; ?></div>
</div>
<?php endforeach; else: ?>
<p style="color:#64748b;grid-column:1/-1">No admin accounts.</p>
<?php endif; ?>
</div>

<h3 style="font-size:16px;color:#0f172a;margin:24px 0 12px">Hosting Users</h3>
<div class="role-grid">
<?php if (!empty($hostingUsers)): foreach ($hostingUsers as $u):
$role = $roleMap[$u->id]->role ?? 'user';
$badgeCls = $badgeMap[$role] ?? 'badge-user';
$roleDisplay = ucfirst($role);
?>
<div class="role-card">
<div style="display:flex;justify-content:space-between;align-items:start">
<div>
<h4><?php echo htmlspecialchars($u->username); ?></h4>
<div class="email"><?php echo htmlspecialchars($u->email); ?></div>
</div>
<span class="badge-role <?php echo $badgeCls; ?>"><?php echo htmlspecialchars($roleDisplay); ?></span>
</div>
<div style="font-size:11px;color:#94a3b8;margin-top:6px">Domain: <?php echo htmlspecialchars($u->domain ?: '-'); ?></div>
</div>
<?php endforeach; else: ?>
<p style="color:#64748b;grid-column:1/-1">No hosting users yet.</p>
<?php endif; ?>
</div>
