<?php
/**
 * GameProvision — called after a game-server order payment is verified (PayPal IPN
 * or admin approval). Creates the game_servers row, allocates a port, generates
 * install/start/stop/restart scripts from the linked game_template, kicks off the
 * SteamCMD install, creates a jailed FTP account, and emails the customer.
 *
 * Usage:
 *   require_once BASE_PATH . '/services/GameProvision.php';
 *   $result = gameProvision($orderId, $userId, $cartItem);
 */

function gameProvision($orderId, $userId, $item) {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

    // 1. Load the hosting user
    $user = $pdo->prepare("SELECT * FROM hosting_users WHERE id = ?");
    $user->execute([(int)$userId]);
    $user = $user->fetch(PDO::FETCH_OBJ);
    if (!$user) return ['error' => 'User not found'];

    // 2. Load the requested game type
    $gameId = (int)($item['game_id'] ?? 0);
    $slots = max(1, (int)($item['slots'] ?? 10));
    $game = null;
    if ($gameId) {
        $g = $pdo->prepare("SELECT * FROM game_types WHERE id = ?");
        $g->execute([$gameId]);
        $game = $g->fetch(PDO::FETCH_OBJ);
    }
    $gameName = $game->name ?? ($item['name'] ?? 'Game Server');

    // 3. Resolve template: cart template_id > package template_id > name/appid match
    require_once BASE_PATH . '/services/GameTemplateEngine.php';
    $engine = new \Services\GameTemplateEngine();
    $template = null;
    $templateId = (int)($item['template_id'] ?? 0);
    $pkg = null;
    $packageId = (int)($item['package_id'] ?? 0);
    if ($packageId) {
        $p = $pdo->prepare("SELECT * FROM game_packages WHERE id = ?");
        $p->execute([$packageId]);
        $pkg = $p->fetch(PDO::FETCH_OBJ);
        if ($pkg && (int)($pkg->template_id ?? 0)) $templateId = (int)$pkg->template_id;
    }
    if ($templateId) $template = $engine->getTemplateById($templateId);
    if (!$template && $game) {
        // Name match first: deterministic even for zero-appid templates (Minecraft etc).
        $t = $pdo->prepare("SELECT * FROM game_templates WHERE status = 'active' AND LOWER(name) = LOWER(?) LIMIT 1");
        $t->execute([$gameName]);
        $template = $t->fetch(PDO::FETCH_OBJ);
    }
    if (!$template && $game) {
        $appid = (string)($game->game_id ?? '');
        if ($appid !== '' && $appid !== '0') {
            $t = $pdo->prepare("SELECT * FROM game_templates WHERE status = 'active' AND appid = ? LIMIT 1");
            $t->execute([$appid]);
            $template = $t->fetch(PDO::FETCH_OBJ);
        }
    }
    $appid = ($template && $template->appid && $template->appid !== '0') ? $template->appid : ($game->game_id ?? '0');

    // 4. Allocate a port
    require_once BASE_PATH . '/core/PortManager.php';
    $pm = new \Core\PortManager();
    $preferred = (int)($template->game_port ?? 0) ?: (27015 + (($gameId * 97) % 500));
    $alloc = $pm->allocate('game_server', null, null, $preferred);
    if (!$alloc) $alloc = $pm->allocate('game_server', null, null);
    $port = $alloc ? (int)$alloc->port_start : ($preferred + 500);

    // 5. Create install directory
    $username = $user->username;
    $slug = preg_replace('/[^a-z0-9]/', '', strtolower($gameName)) ?: 'gameserver';
    $salt = substr(md5($username . $gameName . $port), 0, 6);
    $installRoot = "/home/{$username}/gameservers";
    $installDir = "{$installRoot}/{$slug}_" . $salt;
    @exec("sudo mkdir -p {$installDir} 2>/dev/null");
    @exec("sudo chown -R {$username}:{$username} {$installRoot} 2>/dev/null");

    // 6. Insert game_servers row
    $displayName = $gameName
        . ($pkg && $pkg->name ? ' - ' . $pkg->name : '')
        . ' (' . $slots . ' slots)';
    $rconPassword = bin2hex(random_bytes(8));
    $pdo->prepare("INSERT INTO game_servers
        (user_id, type_id, name, server_name, game_type, port, status, created_at,
         install_path, is_active, max_players, current_players, is_demo, appid,
         template_id, order_id, map_name, game_port, query_port, rcon_port, rcon_password)
        VALUES (?, ?, ?, ?, ?, ?, 'installing', NOW(), ?, 1, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            (int)$userId, $gameId, $displayName, $displayName, $gameName, (int)$port,
            $installDir, (int)$slots,
            ($appid && $appid !== '0') ? 0 : 1,
            $appid, $templateId ?: null, (int)$orderId ?: null, 'de_dust2',
            (int)($template->game_port ?? $port), (int)($template->query_port ?? $port),
            (int)($template->rcon_port ?? 27020), $rconPassword,
        ]);
    $serverId = (int)$pdo->lastInsertId();

    // 7. Generate scripts + config from template
    $serverData = [
        'server_name' => $displayName,
        'port' => $port,
        'max_players' => $slots,
        'install_path' => $installDir,
        'map_name' => 'de_dust2',
        'password' => '',
        'rcon_password' => $rconPassword,
        'rcon_port' => (int)($template->rcon_port ?? 27020),
        'query_port' => (int)($template->query_port ?? $port),
        'motd' => 'Welcome to ' . $displayName,
        'ip' => '0.0.0.0',
    ];
    $configPath = $installDir . '/server.cfg';
    if ($template) {
        $start = $engine->generateStartScript($template, $serverData);
        $stop = $engine->generateStopScript($template, $serverData);
        $restart = $engine->generateRestartScript($template, $serverData);
        $cfg = $engine->generateConfig($template, $serverData);
    } else {
        $start = "#!/bin/bash\ncd {$installDir}\necho '{$gameName} server on port {$port} (demo)'\nsleep 99999";
        $stop = "#!/bin/bash\nkill $(cat {$installDir}/server.pid 2>/dev/null) 2>/dev/null";
        $restart = "#!/bin/bash\n{$stop}\nsleep 2\n{$start}";
        $cfg = "hostname \"{$displayName}\"\nmaxplayers {$slots}\nport {$port}\nrcon_password \"{$rconPassword}\"\nmap de_dust2\n";
    }
    @file_put_contents($installDir . '/start.sh', $start);
    @file_put_contents($installDir . '/stop.sh', $stop);
    @file_put_contents($installDir . '/restart.sh', $restart);
    @file_put_contents($configPath, $cfg);
    @chmod($installDir . '/start.sh', 0755);
    @chmod($installDir . '/stop.sh', 0755);
    @chmod($installDir . '/restart.sh', 0755);

    // 8. Run the installer (SteamCMD for real games, demo marker otherwise)
    if ($appid && $appid !== '0' && $template) {
        $steamUser = 'anonymous';
        $installCmd = "steamcmd +force_install_dir {$installDir} +login {$steamUser} +app_update {$appid} validate +quit";
        @file_put_contents($installDir . '/install.sh', "#!/bin/bash\ncd {$installDir}\nexport HOME=/home/{$username}\n{$installCmd}\n");
        @chmod($installDir . '/install.sh', 0755);
        @exec("nohup bash {$installDir}/install.sh > {$installDir}/install.log 2>&1 &");
    } else {
        @file_put_contents($installDir . '/readme.txt', "Demo server installed on port {$port}.\n");
        $pdo->prepare("UPDATE game_servers SET status = 'stopped' WHERE id = ?")->execute([$serverId]);
    }

    // 9. Create jailed FTP account for the game server directory
    $ftpUser = substr($username, 0, 10) . '_gs' . $serverId;
    $ftpPass = bin2hex(random_bytes(8));
    @exec("sudo useradd -m -d {$installDir} -s /bin/bash {$ftpUser} 2>/dev/null");
    @exec("echo " . escapeshellarg($ftpUser . ':' . $ftpPass) . " | sudo chpasswd 2>/dev/null");
    @exec("sudo usermod -a -G {$ftpUser} {$username} 2>/dev/null");
    @exec("sudo chown -R {$ftpUser}:{$ftpUser} {$installDir} 2>/dev/null");
    @exec("sudo chmod -R 2775 {$installDir} 2>/dev/null");
    @mkdir('/etc/vsftpd_user_conf', 0755, true);
    @file_put_contents("/etc/vsftpd_user_conf/{$ftpUser}", "local_root={$installDir}\nwrite_enable=YES\n");
    $ftpDir = 'gameservers/' . basename($installDir);
    $pdo->prepare("INSERT INTO ftp_accounts (hosting_user_id, username, password_hash, directory, permissions, quota, ssl_enabled, is_active, created_at)
        VALUES (?, ?, ?, ?, 'read_write', 'unlimited', 1, 1, NOW())")
        ->execute([(int)$userId, $ftpUser, password_hash($ftpPass, PASSWORD_DEFAULT), $ftpDir]);
    $pdo->prepare("UPDATE game_servers SET ftp_username = ?, ftp_password = ?, config_path = ? WHERE id = ?")
        ->execute([$ftpUser, $ftpPass, $configPath, $serverId]);

    // 10. Ensure the account is marked active
    if (!($user->package_id ?? null) && $pkg) {
        $pdo->prepare("UPDATE hosting_users SET status = 'active' WHERE id = ?")->execute([(int)$userId]);
    }

    // 11. Email connection details
    $sn = $_SERVER['SERVER_NAME'] ?? 'planet-hosts.com';
    $subject = 'Your ' . $gameName . ' game server is ready!';
    $message = "Hi {$user->name},\n\n"
        . "Your game server has been provisioned!\n\n"
        . "Game: {$gameName}\n"
        . "Name: {$displayName}\n"
        . "Connect: {$sn}:{$port}\n"
        . "Slots: {$slots}\n\n"
        . "FTP Access (jailed to the game folder):\n"
        . "Host: {$sn}\n"
        . "Username: {$ftpUser}\n"
        . "Password: {$ftpPass}\n\n"
        . "Manage your server from the control panel: http://{$sn}:2082/ -> Game Servers\n\n"
        . "Thank you for choosing Planet Hosts!\n";
    @mail($user->email, $subject, $message, "From: support@planet-hosts.com\r\nReply-To: support@planet-hosts.com");

    return ['success' => true, 'server_id' => $serverId, 'ftp_username' => $ftpUser, 'ftp_password' => $ftpPass];
}