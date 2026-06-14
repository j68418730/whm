<?php
$userName = htmlspecialchars($user->name ?? 'Administrator', ENT_QUOTES, 'UTF-8');
$stats = array_merge(['total_accounts' => 0, 'active_accounts' => 0, 'total_packages' => 0, 'active_packages' => 0, 'total_resellers' => 0], $stats ?? []);
$addons = $addons ?? [];
$moduleGroups = [
    'Account Functions' => [
        ['Create a New Account', '/admin/account'],['List Accounts', '/admin/account'],['Suspend / Unsuspend Accounts', '/admin/account'],['Feature Manager', '/admin/userfeatures'],
    ],
    'Reseller Center' => [
        ['Manage Resellers', '/admin/reseller'],['Assign Account Ownership', '/admin/reseller'],['Reseller Privileges', '/admin/reseller'],['Branding', '/admin/branding'],
    ],
    'Packages & Billing' => [
        ['Package Manager', '/admin/packages'],['Upgrade / Downgrade Accounts', '/admin/packages'],['API Access', '/admin/api'],['Licensing', '/admin/licensing'],
    ],
    'Server Configuration' => [
        ['Server Overview', '/admin/server'],['Apache Configuration', '/admin/apache'],['PHP Management', '/admin/php'],['MySQL Databases', '/admin/mysql'],
    ],
    'Security & Operations' => [
        ['SSL/TLS', '/admin/ssl'],['Security Center', '/admin/security'],['Backup System', '/admin/backup'],['Monitoring', '/admin/monitoring'],
    ],
    'Developer Tools' => [
        ['Terminal', '/admin/terminal'],['Git Deployment', '/admin/git'],['Containers', '/admin/container'],['Installers', '/admin/installers'],
    ],
];
?>
<div class="stats-grid">
<div class="stat-card"><h3>Hosting Accounts</h3><div class="value"><?php echo $stats['total_accounts']; ?></div><div class="label"><?php echo $stats['active_accounts']; ?> active</div></div>
<div class="stat-card"><h3>Packages</h3><div class="value"><?php echo $stats['total_packages']; ?></div><div class="label"><?php echo $stats['active_packages']; ?> active</div></div>
<div class="stat-card"><h3>Resellers</h3><div class="value"><?php echo $stats['total_resellers']; ?></div><div class="label">Delegated account owners</div></div>
<div class="stat-card"><h3>Panel Version</h3><div class="value" style="font-size:20px"><?php echo PHP_VERSION; ?></div><div class="label">PHP <?php echo PHP_SAPI; ?></div></div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px">
<?php foreach ($moduleGroups as $group => $links): ?>
<div class="card">
<h3 style="color:var(--accent);font-size:15px;margin-bottom:12px"><?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?></h3>
<?php foreach ($links as $link): ?>
<a href="<?php echo htmlspecialchars($link[1], ENT_QUOTES, 'UTF-8'); ?>" style="display:block;padding:8px 12px;border-radius:6px;color:var(--text-table);text-decoration:none;font-size:14px;margin-bottom:2px;transition:.15s"><?php echo htmlspecialchars($link[0], ENT_QUOTES, 'UTF-8'); ?></a>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
