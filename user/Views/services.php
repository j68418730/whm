<div class="card"><h3 style="color:var(--accent)">My Services</h3>
<p style="color:var(--text-secondary);margin-top:8px">Your active hosting and radio services.</p>
<div class="page-grid" style="margin-top:16px">
<?php
$pkgType = '';
if (isset($hosting) && $hosting->package_id) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
        $s = $pdo->prepare("SELECT type FROM hosting_packages WHERE id = ?");
        $s->execute([$hosting->package_id]);
        $pkgType = $s->fetchColumn() ?: '';
    } catch (Exception $e) {}
}
$hasWeb = stripos($pkgType, 'web') !== false;
$hasRadio = stripos($pkgType, 'icecast') !== false;
$hasVps = stripos($pkgType, 'vps') !== false;
$hasReseller = stripos($pkgType, 'reseller') !== false;

if ($hasWeb || $hasReseller || (!$hasRadio && !$hasVps)):
?><a href="/user/services/web" class="action-card"><div class="icon">🌐</div><div class="name">Web Hosting</div></a><?php endif; ?>
<?php if ($hasRadio || $hasReseller): ?><a href="/user/services/radio" class="action-card"><div class="icon">📡</div><div class="name">Radio Hosting</div></a><?php endif; ?>
<?php if ($hasVps || $hasReseller): ?><a href="/user/services/vps" class="action-card"><div class="icon">🖥</div><div class="name">VPS</div></a><?php endif; ?>
<a href="/user/services/domains" class="action-card"><div class="icon">🌍</div><div class="name">Domains</div></a>
</div></div>
