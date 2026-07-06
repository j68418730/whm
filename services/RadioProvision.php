<?php
/**
 * Radio Auto-Provision
 * Called after payment is confirmed for a radio package.
 * Creates Icecast stream, config, starts the stream.
 */
function radioProvision($userId, $packageId) {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

    $user = $pdo->prepare("SELECT * FROM hosting_users WHERE id = ?")->execute([$userId]) ? $pdo->query("SELECT * FROM hosting_users WHERE id=$userId")->fetch(PDO::FETCH_OBJ) : null;
    $stmt = $pdo->prepare("SELECT * FROM hosting_users WHERE id = ?"); $stmt->execute([$userId]); $user = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$user) return false;

    $pkg = $pdo->prepare("SELECT * FROM hosting_packages WHERE id = ?"); $pkg->execute([$packageId]); $pkg = $pkg->fetch(PDO::FETCH_OBJ);
    if (!$pkg) return false;

    // Check if user already has a stream
    $existing = $pdo->prepare("SELECT id FROM radio_streams WHERE user_id = ?"); $existing->execute([$userId]);
    if ($existing->fetch()) return false; // Already has a stream

    // Find available port
    $usedPorts = [];
    $q = $pdo->query("SELECT port FROM radio_streams");
    foreach ($q as $r) $usedPorts[(int)$r['port']] = true;

    $pkgFeats = is_string($pkg->features ?? null) ? json_decode($pkg->features, true) ?? [] : ($pkg->features ?? []);
    $sp = $pkgFeats['streaming_package'] ?? [];
    $maxList = (int)($sp['max_listeners'] ?? 0);
    $port = $maxList > 0 ? 6000 : 6000;
    for ($i = 0; $i < 1000; $i++) {
        if (!isset($usedPorts[$port + $i])) { $port = $port + $i; break; }
    }

    $password = bin2hex(random_bytes(8));
    $username = $user->username;
    $configDir = "/home/{$username}/radio/streams";
    @mkdir($configDir, 0755, true);

    $config = <<<XML
<icecast>
    <limits><clients>100</clients><sources>2</sources></limits>
    <authentication>
        <source-password>{$password}</source-password>
        <admin-user>admin</admin-user>
        <admin-password>{$password}</admin-password>
    </authentication>
    <hostname>localhost</hostname>
    <listen-socket><port>{$port}</port></listen-socket>
    <paths><basedir>/usr/share/icecast2</basedir><logdir>/var/log/icecast2</logdir>
        <webroot>/usr/share/icecast2/web</webroot><adminroot>/usr/share/icecast2/admin</adminroot>
        <alias source="/" dest="/status.xsl"/></paths>
    <logging><accesslog>access.log</accesslog><errorlog>error.log</errorlog></logging>
</icecast>
XML;

    $configFile = "{$configDir}/icecast.conf";
    file_put_contents($configFile, $config);

    // Create stream in DB
    $pdo->prepare("INSERT INTO radio_streams (user_id, server_type, port, password, config_path, status) VALUES (?, 'icecast', ?, ?, ?, 'running')")
        ->execute([$userId, $port, password_hash($password, PASSWORD_DEFAULT), $configFile]);
    $streamId = $pdo->lastInsertId();

    // Start Icecast
    exec("nohup /usr/bin/icecast -c {$configFile} > /dev/null 2>&1 & echo \$!", $out);
    $pid = (int)($out[0] ?? 0);
    if ($pid > 0) {
        $pdo->prepare("UPDATE radio_streams SET pid_file = ? WHERE id = ?")->execute(['/var/run/icecast_' . $streamId . '.pid', $streamId]);
    }

    return $streamId;
}
