<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

$username = 'callspectre';
$password = 'Blast2001';
$email = 'callspectre@planet-hosts.com';
$name = 'Call Spectre';

// 1. Create or update hosting user
$existing = $p->prepare("SELECT id FROM hosting_users WHERE username = ?");
$existing->execute([$username]);
$user = $existing->fetch(PDO::FETCH_OBJ);

if ($user) {
    $userId = $user->id;
    $p->prepare("UPDATE hosting_users SET password_hash = ?, first_name = ?, email = ?, status = 'active', reseller_id = 1 WHERE id = ?")
        ->execute([password_hash($password, PASSWORD_DEFAULT), $name, $email, $userId]);
    echo "USER_UPDATED id=$userId\n";
} else {
    $p->prepare("INSERT INTO hosting_users (username, email, password_hash, first_name, status, reseller_id, created_at) VALUES (?, ?, ?, ?, 'active', 1, NOW())")
        ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $name]);
    $userId = $p->lastInsertId();
    echo "USER_CREATED id=$userId\n";
}

// 2. Assign a radio-enabled package (use first icecast package)
$pkg = $p->prepare("SELECT id FROM hosting_packages WHERE type = 'icecast' AND is_active = 1 LIMIT 1");
$pkg->execute();
$pkgId = $pkg->fetchColumn();

if ($pkgId) {
    $p->prepare("UPDATE hosting_users SET package_id = ? WHERE id = ?")->execute([$pkgId, $userId]);
    echo "PACKAGE_ASSIGNED id=$pkgId\n";
}

// 3. Create Icecast stream
$port = 6000;
// Find available port
$maxPort = $p->query("SELECT MAX(port) FROM radio_streams")->fetchColumn();
if ($maxPort >= 6000) $port = $maxPort + 1;
if ($port > 10000) $port = 6000;

$streamPass = bin2hex(random_bytes(8));
$configDir = "/home/{$username}/radio/streams";
@mkdir($configDir, 0755, true);

// Create Icecast config
$config = <<<XML
<icecast>
    <limits>
        <clients>100</clients>
        <sources>2</sources>
        <threadpool>5</threadpool>
        <queue-size>524288</queue-size>
        <client-timeout>30</client-timeout>
        <header-timeout>15</header-timeout>
        <source-timeout>10</source-timeout>
        <burst-on-connect>1</burst-on-connect>
        <burst-size>65535</burst-size>
    </limits>
    <authentication>
        <source-password>{$streamPass}</source-password>
        <admin-user>admin</admin-user>
        <admin-password>{$streamPass}</admin-password>
    </authentication>
    <hostname>localhost</hostname>
    <listen-socket>
        <port>{$port}</port>
    </listen-socket>
    <fileserve>1</fileserve>
    <paths>
        <basedir>/usr/share/icecast2</basedir>
        <logdir>/var/log/icecast2</logdir>
        <webroot>/usr/share/icecast2/web</webroot>
        <adminroot>/usr/share/icecast2/admin</adminroot>
        <alias source="/" dest="/status.xsl"/>
    </paths>
    <logging>
        <accesslog>access.log</accesslog>
        <errorlog>error.log</errorlog>
        <loglevel>3</loglevel>
        <logsize>10000</logsize>
    </logging>
    <security>
        <chroot>0</chroot>
        <changeowner>
            <user>nobody</user>
            <group>nogroup</group>
        </changeowner>
    </security>
</icecast>
XML;

$configFile = "{$configDir}/icecast.conf";
file_put_contents($configFile, $config);

$streamId = $p->prepare("SELECT id FROM radio_streams WHERE user_id = ? AND port = ?");
$streamId->execute([(int)$userId, (int)$port]);
$existingStream = $streamId->fetchColumn();

if ($existingStream) {
    $p->prepare("UPDATE radio_streams SET config_path = ?, password = ?, status = 'stopped' WHERE id = ?")
        ->execute([$configFile, password_hash($streamPass, PASSWORD_DEFAULT), $existingStream]);
    echo "STREAM_UPDATED id=$existingStream port=$port\n";
} else {
    $p->prepare("INSERT INTO radio_streams (user_id, server_type, port, password, config_path, status) VALUES (?, 'icecast', ?, ?, ?, 'stopped')")
        ->execute([$userId, $port, password_hash($streamPass, PASSWORD_DEFAULT), $configFile]);
    $existingStream = $p->lastInsertId();
    echo "STREAM_CREATED id=$existingStream port=$port\n";
}

// 4. Create DJ account
$djUser = 'spectre_dj';
$djPass = 'Blast2001';
$djCheck = $p->prepare("SELECT id FROM radio_djs WHERE username = ? AND stream_id = ?");
$djCheck->execute([$djUser, $existingStream]);
if (!$djCheck->fetchColumn()) {
    $p->prepare("INSERT INTO radio_djs (stream_id, username, password, name, status) VALUES (?, ?, ?, 'Call Spectre', 'active')")
        ->execute([$existingStream, $djUser, password_hash($djPass, PASSWORD_DEFAULT)]);
    echo "DJ_CREATED username=$djUser\n";
}

// 5. Create Chatbox Tenant with voice enabled
$ctCheck = $p->prepare("SELECT id FROM chatbox_tenants WHERE hosting_user_id = ?");
$ctCheck->execute([$userId]);
$tenantId = $ctCheck->fetchColumn();

if (!$tenantId) {
    $p->prepare("INSERT INTO chatbox_tenants (hosting_user_id, name, widget_title, widget_color, guest_enabled, registration_enabled, voice_enabled) VALUES (?, 'Call Spectre Chat', 'Spectre Chat', '#008cff', 1, 1, 1)")
        ->execute([$userId]);
    $tenantId = $p->lastInsertId();
    // Add default rooms
    $p->prepare("INSERT INTO chatbox_rooms (tenant_id, name, type) VALUES (?, 'General', 'public'), (?, 'Support', 'public'), (?, 'VIP', 'private')")
        ->execute([$tenantId, $tenantId, $tenantId]);
    echo "CHATBOX_TENANT_CREATED id=$tenantId\n";
} else {
    $p->prepare("UPDATE chatbox_tenants SET voice_enabled = 1, guest_enabled = 1, registration_enabled = 1 WHERE id = ?")
        ->execute([$tenantId]);
    echo "CHATBOX_UPDATED voice_enabled\n";
}

// Create chat admin
$chatAdminCheck = $p->prepare("SELECT id FROM chatbox_users WHERE tenant_id = ? AND username = 'spectre_admin'");
$chatAdminCheck->execute([$tenantId]);
if (!$chatAdminCheck->fetchColumn()) {
    $p->prepare("INSERT INTO chatbox_users (tenant_id, username, password_hash, display_name, role, email) VALUES (?, 'spectre_admin', ?, 'Call Spectre', 'owner', ?)")
        ->execute([$tenantId, password_hash('Blast2001', PASSWORD_DEFAULT), $email]);
    echo "CHAT_ADMIN_CREATED\n";
}

echo "\n=== ACCOUNT READY ===\n";
echo "All IDs - User:$userId Stream:$existingStream Tenant:$tenantId\n";
