<?php
require_once __DIR__ . '/../core/ServerCreds.php';
/**
 * phpMyAdmin Auto-Login for Planet-Hosts
 * Only panel-authenticated sessions are granted access (signon auth).
 * Anonymous visitors are redirected to the panel login page.
 */
session_start();

$isAdmin = !empty($_SESSION['is_admin']);
$user    = $_SESSION['user'] ?? null;
$userId  = is_object($user) ? ($user->id ?? 0) : ($user['id'] ?? 0);

// No valid panel session -> login page, never wide-open access.
if (!$isAdmin && empty($userId)) {
    $port = (int)($_SERVER['SERVER_PORT'] ?? 0);
    $login = match ($port) {
        2086 => '/portal_reseller.php',
        2083 => '/portal_user.php',
        2082 => '/portal_user.php',
        default => '/admin/login',
    };
    header('Location: ' . $login);
    exit;
}

$dbUser = \db_user();
$dbPass = \db_pass();

if ($isAdmin) {
    $dbUser = \db_root_user();
    $dbPass = \db_root_pass();
} else {
    // For regular users, find their specific database and create a scoped user
    $email = is_object($user) ? ($user->email ?? '') : ($user['email'] ?? '');
    $uname = is_object($user) ? ($user->name ?? '') : ($user['name'] ?? '');

    try {
        $pdo = db_pdo();
        // Find hosting user
        $stmt = $pdo->prepare("SELECT id, username FROM hosting_users WHERE id = ? OR email = ? OR username = ? LIMIT 1");
        $stmt->execute([$userId, $email, $uname]);
        $hosting = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$hosting) {
            $stmt2 = $pdo->query("SELECT id, username FROM hosting_users ORDER BY id ASC LIMIT 1");
            $hosting = $stmt2->fetch(PDO::FETCH_OBJ);
        }
        if ($hosting) {
            $prefix = $hosting->username . '_';
            // Find user's first database
            $dbStmt = $pdo->query("SHOW DATABASES");
            $userDb = '';
            while ($row = $dbStmt->fetch(PDO::FETCH_NUM)) {
                if (str_starts_with($row[0], $prefix) && $row[0] !== 'Database') {
                    $userDb = $row[0];
                    break;
                }
            }
            if ($userDb) {
                $dbUser = $hosting->username . '_pma';
                $dbPass = bin2hex(random_bytes(12));
                // Create a dedicated PMA user with SELECT access only to this DB
                try {
                    $rootPdo = db_root_pdo();
                    $rootPdo->exec("CREATE USER IF NOT EXISTS '{$dbUser}'@'localhost' IDENTIFIED BY " . $rootPdo->quote($dbPass));
                    $rootPdo->exec("GRANT SELECT, SHOW VIEW, PROCESS ON `{$userDb}`.* TO '{$dbUser}'@'localhost'");
                    $rootPdo->exec("FLUSH PRIVILEGES");
                } catch (\Exception $e) {
                    // Fallback to radiouser if can't create scoped user
                    $dbUser = \db_user();
                    $dbPass = \db_pass();
                }
            }
        }
    } catch (\Exception $e) {}
}

// Store in session for phpMyAdmin signon to pick up
// (new key names used by phpMyAdmin 5.2+, old keys kept for safety)
$_SESSION['PMA_single_signon_user'] = $dbUser;
$_SESSION['PMA_single_signon_password'] = $dbPass;
$_SESSION['PMA_single_signon_host'] = 'localhost';
$_SESSION['PMA_signon_username'] = $dbUser;
$_SESSION['PMA_signon_password'] = $dbPass;
$_SESSION['PMA_signon_server'] = 1;
session_write_close();

// Redirect back to phpMyAdmin with signon
$dest = $_GET['destination'] ?? '/phpmyadmin/index.php?route=/&server=1';
header('Location: ' . (str_starts_with($dest, '/') ? $dest : '/phpmyadmin/index.php?route=/&server=1'));
exit;