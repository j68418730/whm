<?php
/**
 * Auto-Provision Account
 * Called after payment is confirmed (PayPal IPN or admin approval).
 * Creates Linux user, home directory, DNS records, etc.
 */
function autoProvision($userId, $packageId) {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

    $user = $pdo->prepare("SELECT * FROM hosting_users WHERE id = ?");
    $user->execute([$userId]);
    $user = $user->fetch(PDO::FETCH_OBJ);
    if (!$user) return false;

    $pkg = $pdo->prepare("SELECT * FROM hosting_packages WHERE id = ?");
    $pkg->execute([$packageId]);
    $pkg = $pkg->fetch(PDO::FETCH_OBJ);
    if (!$pkg) return false;

    $username = $user->username;
    $domain = $user->domain ?: ($username . '.planet-hosts.com');
    $homeDir = "/home/{$username}";

    // 1. Create Linux user
    $password = bin2hex(random_bytes(8));
    exec("useradd -m -d {$homeDir} -s /bin/bash {$username} 2>/dev/null");
    exec("echo {$username}:" . escapeshellarg($password) . " | chpasswd 2>/dev/null");
    exec("chmod 755 {$homeDir} 2>/dev/null");

    // 2. Create public_html
    $publicHtml = "{$homeDir}/public_html";
    if (!is_dir($publicHtml)) {
        mkdir($publicHtml, 0755, true);
        file_put_contents("{$publicHtml}/index.html", "<h1>Welcome to {$domain}</h1><p>Account active.</p>");
    }
    exec("chown -R {$username}:{$username} {$homeDir} 2>/dev/null");

    // 3. Create Apache vhost
    $vhost = "<VirtualHost *:80>\n"
        . "    ServerName {$domain}\n"
        . "    ServerAlias www.{$domain}\n"
        . "    DocumentRoot {$publicHtml}\n"
        . "    <Directory {$publicHtml}>\n"
        . "        Options Indexes FollowSymLinks\n"
        . "        AllowOverride All\n"
        . "        Require all granted\n"
        . "    </Directory>\n"
        . "    ErrorLog /var/log/apache2/{$username}_error.log\n"
        . "    CustomLog /var/log/apache2/{$username}_access.log combined\n"
        . "</VirtualHost>";
    file_put_contents("/etc/apache2/sites-available/{$username}.conf", $vhost);
    exec("a2ensite {$username}.conf 2>/dev/null");
    exec("systemctl reload apache2 2>/dev/null");

    // 4. Create DNS zone if domain is on our nameserver
    if (strpos($domain, 'planet-hosts.com') !== false) {
        // Simple DNS record via bind
        $namedConf = "/etc/bind/named.conf.local";
        $zoneFile = "/etc/bind/zones/{$username}.zone";
        if (!is_dir('/etc/bind/zones')) mkdir('/etc/bind/zones', 0755, true);
        $zoneContent = "\$TTL 86400\n@ IN SOA ns1.planet-hosts.com. admin.planet-hosts.com. (" . time() . " 3600 900 604800 86400)\n"
            . "@ IN NS ns1.planet-hosts.com.\n"
            . "@ IN NS ns2.planet-hosts.com.\n"
            . "@ IN A " . ($_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com') . "\n"
            . "www IN A " . ($_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com') . "\n"
            . "mail IN A " . ($_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com') . "\n"
            . "@ IN MX 10 mail.{$domain}.\n";
        file_put_contents($zoneFile, $zoneContent);
        file_put_contents($namedConf, "\nzone \"{$domain}\" { type master; file \"{$zoneFile}\"; };\n", FILE_APPEND);
        exec("systemctl reload bind9 2>/dev/null");
    }

    // 5. Create FTP directory access
    exec("usermod -a -G www-data {$username} 2>/dev/null");

    // 6. Set up Icecast radio directories if radio package
    $isRadio = stripos($pkg->type ?? '', 'icecast') !== false;
    if ($isRadio) {
        $radioDir = "{$homeDir}/radio/streams";
        if (!is_dir($radioDir)) mkdir($radioDir, 0755, true);
        $autodjDir = "{$homeDir}/radio/autodj";
        if (!is_dir($autodjDir)) mkdir($autodjDir, 0755, true);
        exec("chown -R {$username}:{$username} {$homeDir}/radio 2>/dev/null");
    }

    // 7. Update user status to active
    $pdo->prepare("UPDATE hosting_users SET status = 'active', domain = ?, package_id = ? WHERE id = ?")
        ->execute([$domain, $packageId, $userId]);

    // 8. Save password to session for display
    $_SESSION['provisioned_password'] = $password;
    $_SESSION['provisioned_username'] = $username;

    // 9. Send welcome email
    $subject = "Welcome to Planet Hosts - Your account is ready!";
    $message = "Hi {$user->name},\n\n"
        . "Your hosting account is now active!\n\n"
        . "Username: {$username}\n"
        . "Password: {$password}\n"
        . "Domain: {$domain}\n"
        . "Panel: http://{$_SERVER['SERVER_NAME'] ?? 'planet-hosts.com'}:2082/\n\n"
        . "Webmail: http://{$_SERVER['SERVER_NAME'] ?? 'planet-hosts.com'}:2096/\n\n"
        . "Thank you for choosing Planet Hosts!\n";
    @mail($user->email, $subject, $message, "From: support@planet-hosts.com\r\nReply-To: support@planet-hosts.com");

    return true;
}

