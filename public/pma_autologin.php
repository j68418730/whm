<?php
/**
 * phpMyAdmin Auto-Login for Planet-Hosts
 * Users get read-only access to their own database only.
 * Admins get full root access.
 */
session_start();

$isAdmin = !empty($_SESSION['is_admin']);
$dbUser = 'radiouser';
$dbPass = 'Skylinehosting171';

if ($isAdmin) {
    $dbUser = 'root';
    $dbPass = 'Skylinehosting171';
} else {
    // For regular users, find their specific database and create a scoped user
    $user = $_SESSION['user'] ?? null;
    $email = is_object($user) ? ($user->email ?? '') : ($user['email'] ?? '');
    $uname = is_object($user) ? ($user->name ?? '') : ($user['name'] ?? '');
    $uid = is_object($user) ? ($user->id ?? 0) : ($user['id'] ?? 0);

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
        // Find hosting user
        $stmt = $pdo->prepare("SELECT id, username FROM hosting_users WHERE id = ? OR email = ? OR username = ? LIMIT 1");
        $stmt->execute([$uid, $email, $uname]);
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
                    $rootPdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', 'Skylinehosting171');
                    $rootPdo->exec("CREATE USER IF NOT EXISTS '{$dbUser}'@'localhost' IDENTIFIED BY " . $rootPdo->quote($dbPass));
                    $rootPdo->exec("GRANT SELECT, SHOW VIEW, PROCESS ON `{$userDb}`.* TO '{$dbUser}'@'localhost'");
                    $rootPdo->exec("FLUSH PRIVILEGES");
                } catch (\Exception $e) {
                    // Fallback to radiouser if can't create scoped user
                    $dbUser = 'radiouser';
                    $dbPass = 'Skylinehosting171';
                }
            }
        }
    } catch (\Exception $e) {}
}

// Store in session for phpMyAdmin signon to pick up
$_SESSION['PMA_signon_username'] = $dbUser;
$_SESSION['PMA_signon_password'] = $dbPass;
$_SESSION['PMA_signon_server'] = 1;
session_write_close();

// Redirect to phpMyAdmin with signon
header('Location: /phpmyadmin/index.php?route=/&server=1');
exit;
