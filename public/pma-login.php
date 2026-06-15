<?php
/**
 * phpMyAdmin Auto-Login Gateway
 * Reads current session and logs into phpMyAdmin with the right credentials
 */
$scriptDir = dirname(__DIR__);
define('BASE_PATH', $scriptDir);
require $scriptDir . '/core/Session.php';
$session = new \Core\Session();
require $scriptDir . '/core/helpers.php';

// Load .env
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            putenv(trim($k) . '=' . trim($v));
        }
    }
}

// Determine credentials
$user = $_SESSION['user'] ?? null;
$isAdmin = $_SESSION['is_admin'] ?? false;

if ($isAdmin) {
    $dbUser = getenv('DB_USERNAME') ?: 'radiouser';
    $dbPass = getenv('DB_PASSWORD') ?: 'Skylinehosting171';
    $dbName = '';
} else {
    // For regular users - use their database credentials
    $dbUser = $user['username'] ?? 'radiouser';
    $dbPass = $user['db_password'] ?? '';
    $dbName = $user['username'] ? $user['username'] . '_db' : '';
}

// phpMyAdmin URL with auto-login via POST
$pmaUrl = '/phpmyadmin/index.php';

// Auto-submit login form
?>
<!DOCTYPE html>
<html><head><title>Redirecting to phpMyAdmin...</title></head>
<body>
<form id="pmaForm" method="POST" action="<?php echo $pmaUrl; ?>">
<input type="hidden" name="pma_username" value="<?php echo htmlspecialchars($dbUser); ?>">
<input type="hidden" name="pma_password" value="<?php echo htmlspecialchars($dbPass); ?>">
<input type="hidden" name="server" value="1">
<input type="hidden" name="lang" value="en">
<?php if ($dbName): ?>
<input type="hidden" name="db" value="<?php echo htmlspecialchars($dbName); ?>">
<?php endif; ?>
</form>
<script>document.getElementById('pmaForm').submit();</script>
<p>Redirecting to phpMyAdmin...</p>
</body>
</html>
