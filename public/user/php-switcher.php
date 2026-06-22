<?php
$scriptName = $_SERVER['SCRIPT_FILENAME'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_ends_with($scriptName, '/public/user/php-switcher.php') && !str_contains($requestUri, '/user/php-switcher.php')) {
    header('Location: /user/php-switcher');
    exit;
}
if (!isset($hosting) || !$hosting) { echo 'No account'; exit; }

$versions = [];
exec('ls /usr/bin/php* 2>/dev/null', $out);
foreach ($out as $p) { if (preg_match('/php(\d+\.\d+)$/', $p, $m)) $versions[] = $m[1]; }
if (empty($versions)) $versions = ['8.2'];
$currentVersion = $hosting->php_version ?: '8.2';

if ($_POST && isset($_POST['version'])) {
    $newVer = $_POST['version'];
    if (in_array($newVer, $versions)) {
        $pdo->prepare("UPDATE hosting_users SET php_version = ? WHERE id = ?")->execute([$newVer, $hosting->id]);
        if ($hosting->domain) {
            $vhostFile = "/etc/apache2/sites-available/{$hosting->domain}.conf";
            if (file_exists($vhostFile)) {
                $content = file_get_contents($vhostFile);
                $content = preg_replace('/SetHandler .*php.*-fpm.*/', "SetHandler \"proxy:unix:/run/php/php{$newVer}-fpm.sock|fcgi://localhost\"", $content);
                file_put_contents($vhostFile, $content);
                exec("systemctl reload apache2 2>/dev/null");
            }
        }
        $success = "PHP version changed to {$newVer}.";
        $currentVersion = $newVer;
    }
}
?>
<div class="card">
<h3>PHP Version Selector</h3>
<p style="color:var(--text_muted);font-size:13px">Current: <strong>PHP <?php echo $currentVersion; ?></strong></p>
<p style="color:var(--text_muted);font-size:12px;margin-bottom:14px">Select your preferred PHP version below. Changes affect your website.</p>

<?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<form method="POST">
<div style="display:flex;gap:10px;flex-wrap:wrap">
<?php foreach ($versions as $v): ?>
<button type="submit" name="version" value="<?php echo $v; ?>" style="padding:14px 24px;border-radius:10px;border:2px solid <?php echo $v === $currentVersion ? 'var(--primary,#008cff)' : 'rgba(255,255,255,.08)'; ?>;cursor:pointer;text-align:center;background:<?php echo $v === $currentVersion ? 'rgba(0,140,255,.1)' : 'rgba(0,0,0,.2)'; ?>;color:var(--text,#e0e0e0);min-width:100px;font-family:inherit">
<div style="font-size:22px;font-weight:800"><?php echo $v; ?></div>
<div style="font-size:11px;color:var(--text_muted);margin-top:4px">PHP <?php echo $v; ?></div>
</button>
<?php endforeach; ?>
</div>
</form>

<div style="margin-top:14px;padding:10px;background:rgba(250,204,21,.06);border-radius:8px;font-size:12px;color:var(--text_muted)">
&#9888; Changing PHP version may break your site if your code uses version-specific features.
</div>
</div>
