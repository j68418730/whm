<?php

namespace Admin\Services;

class HostnameManager
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function getCurrentHostname()
    {
        return trim(shell_exec('hostname 2>/dev/null') ?: 'localhost');
    }

    public function getServerIp()
    {
        return trim(shell_exec('hostname -I 2>/dev/null') ?: ($_SERVER['SERVER_ADDR'] ?? '127.0.0.1'));
    }

    public function getPublicIp()
    {
        $ip = trim(shell_exec('curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null') ?: '');
        return $ip ?: $this->getServerIp();
    }

    public function validateHostname($hostname)
    {
        $errors = [];

        if (empty($hostname)) {
            $errors[] = 'Hostname cannot be empty.';
            return $errors;
        }

        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $hostname)) {
            $errors[] = 'Hostname must be a valid FQDN (e.g. server.example.com).';
        }

        if (preg_match('/^localhost/i', $hostname)) {
            $errors[] = 'Hostname cannot be localhost.';
        }

        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            $errors[] = 'Hostname must be a domain name, not an IP address.';
        }

        $resolved = gethostbyname($hostname);
        if ($resolved === $hostname) {
            $errors[] = "Warning: Hostname '$hostname' does not resolve via DNS. SSL issuance may fail.";
        } else {
            $serverIp = $this->getPublicIp();
            if ($resolved !== $serverIp && $serverIp !== '127.0.0.1') {
                $errors[] = "Warning: '$hostname' resolves to $resolved, but this server IP is $serverIp.";
            }
        }

        return $errors;
    }

    public function updateSystemHostname($hostname)
    {
        $output = [];
        $output[] = shell_exec("hostnamectl set-hostname " . escapeshellarg($hostname) . " 2>&1");
        $output[] = file_put_contents('/etc/hostname', $hostname . "\n") ? '/etc/hostname updated' : '/etc/hostname FAILED';

        $hostsContent = file_get_contents('/etc/hosts');
        $lines = explode("\n", $hostsContent);
        $found = false;
        foreach ($lines as &$line) {
            $line = trim($line);
            if (preg_match('/^127\.0\.1\.1\s+/', $line) || preg_match('/^' . preg_quote($this->getServerIp(), '/') . '\s+/', $line)) {
                $parts = preg_split('/\s+/', $line);
                $parts[0] = $this->getServerIp();
                $parts[1] = $hostname;
                if (!in_array($hostname . '.' . explode('.', $hostname, 2)[1] ?? '', $parts)) {
                    $parts[] = $hostname;
                }
                $line = implode("\t", $parts);
                $found = true;
            }
        }
        if (!$found) {
            $lines[] = $this->getServerIp() . "\t" . $hostname . "\t" . $hostname;
        }
        file_put_contents('/etc/hosts', implode("\n", $lines));
        $output[] = '/etc/hosts updated';

        $verify = trim(shell_exec('hostname 2>/dev/null'));
        $verifyF = trim(shell_exec('hostname -f 2>/dev/null'));
        $output[] = "hostname: $verify";
        $output[] = "hostname -f: $verifyF";

        return implode("\n", $output);
    }

    public function generatePanelVhost($hostname)
    {
        $panelDir = BASE_PATH . '/public';
        $vhost = "<VirtualHost *:80>\n"
            . "    ServerAdmin webmaster@{$hostname}\n"
            . "    DocumentRoot {$panelDir}\n"
            . "    ServerName {$hostname}\n"
            . "    ServerAlias www.{$hostname} {$this->getServerIp()}\n"
            . "    <Directory {$panelDir}>\n"
            . "        Options Indexes FollowSymLinks\n"
            . "        AllowOverride All\n"
            . "        Require all granted\n"
            . "        DirectoryIndex index.php index.html\n"
            . "    </Directory>\n"
            . "    ErrorLog /var/log/httpd/panel_error.log\n"
            . "    CustomLog /var/log/httpd/panel_access.log combined\n"
            . "</VirtualHost>\n";

        return $vhost;
    }

    public function generatePanelSslVhost($hostname)
    {
        $panelDir = BASE_PATH . '/public';
        $certDir = "/etc/letsencrypt/live/{$hostname}";
        $vhost = "<VirtualHost *:443>\n"
            . "    ServerAdmin webmaster@{$hostname}\n"
            . "    DocumentRoot {$panelDir}\n"
            . "    ServerName {$hostname}\n"
            . "    ServerAlias www.{$hostname} {$this->getServerIp()}\n"
            . "    <Directory {$panelDir}>\n"
            . "        Options Indexes FollowSymLinks\n"
            . "        AllowOverride All\n"
            . "        Require all granted\n"
            . "    </Directory>\n"
            . "    SSLEngine on\n"
            . "    SSLCertificateFile {$certDir}/fullchain.pem\n"
            . "    SSLCertificateKeyFile {$certDir}/privkey.pem\n"
            . "    Include /etc/letsencrypt/options-ssl-apache.conf\n"
            . "    ErrorLog /var/log/httpd/panel_ssl_error.log\n"
            . "    CustomLog /var/log/httpd/panel_ssl_access.log combined\n"
            . "</VirtualHost>\n";

        return $vhost;
    }

    public function writePanelVhost($hostname)
    {
        $path = '/etc/apache2/sites-available/planethosts-panel.conf';
        if (!is_dir(dirname($path))) {
            $path = '/etc/httpd/conf.d/planethosts-panel.conf';
        }
        $content = $this->generatePanelVhost($hostname);
        file_put_contents($path, $content);

        if (file_exists('/etc/apache2/sites-available/planethosts-panel.conf')) {
            shell_exec('a2ensite planethosts-panel.conf 2>/dev/null');
        }

        return $path;
    }

    public function writePanelSslVhost($hostname)
    {
        $certDir = "/etc/letsencrypt/live/{$hostname}";
        if (!file_exists("{$certDir}/fullchain.pem")) {
            return null;
        }

        $path = '/etc/apache2/sites-available/planethosts-panel-ssl.conf';
        if (!is_dir(dirname($path))) {
            $path = '/etc/httpd/conf.d/planethosts-panel-ssl.conf';
        }
        $content = $this->generatePanelSslVhost($hostname);
        file_put_contents($path, $content);

        if (file_exists('/etc/apache2/sites-available/planethosts-panel-ssl.conf')) {
            shell_exec('a2ensite planethosts-panel-ssl.conf 2>/dev/null');
        }

        return $path;
    }

    public function removeOldPanelVhosts()
    {
        $files = [
            '/etc/apache2/sites-available/radiohosting.conf',
            '/etc/httpd/conf.d/radiohosting.conf',
            '/etc/apache2/sites-available/planethosts-panel.conf',
            '/etc/httpd/conf.d/planethosts-panel.conf',
            '/etc/apache2/sites-available/planethosts-panel-ssl.conf',
            '/etc/httpd/conf.d/planethosts-panel-ssl.conf',
        ];
        foreach ($files as $f) {
            if (file_exists($f)) @unlink($f);
        }
    }

    public function requestSsl($hostname, $email = null)
    {
        if (!$email) {
            $email = "admin@{$hostname}";
        }

        $output = shell_exec("certbot --apache --non-interactive --agree-tos --email " . escapeshellarg($email)
            . " -d " . escapeshellarg($hostname)
            . " -d www.{$hostname} 2>&1");

        return $output;
    }

    public function getSslStatus($hostname)
    {
        $certDir = "/etc/letsencrypt/live/{$hostname}";
        if (!file_exists("{$certDir}/fullchain.pem")) {
            return [
                'status' => 'missing',
                'expires' => null,
                'days_left' => 0,
                'issuer' => null,
            ];
        }

        $expires = shell_exec("openssl x509 -enddate -noout -in {$certDir}/fullchain.pem 2>/dev/null | cut -d= -f2");
        $issuer = shell_exec("openssl x509 -issuer -noout -in {$certDir}/fullchain.pem 2>/dev/null | cut -d= -f2");

        $expires = trim($expires ?: '');
        $issuer = trim($issuer ?: '');

        $daysLeft = 0;
        if ($expires) {
            $expTime = strtotime($expires);
            $daysLeft = max(0, floor(($expTime - time()) / 86400));
        }

        return [
            'status' => $expires ? 'valid' : 'missing',
            'expires' => $expires,
            'days_left' => $daysLeft,
            'issuer' => $issuer,
        ];
    }

    public function healthCheck($hostname)
    {
        $health = [];

        $currentHostname = $this->getCurrentHostname();
        $health['system_hostname'] = $currentHostname;
        $health['hostname_match'] = ($currentHostname === $hostname);

        $resolved = gethostbyname($hostname);
        $health['dns_resolves'] = ($resolved !== $hostname);
        $health['resolved_ip'] = $resolved;

        $serverIp = $this->getPublicIp();
        $health['dns_points_here'] = ($resolved === $serverIp || $resolved === $hostname);

        $apacheRunning = trim(shell_exec('systemctl is-active apache2 2>/dev/null') ?: '');
        if (!$apacheRunning || $apacheRunning === 'inactive') {
            $apacheRunning = trim(shell_exec('systemctl is-active httpd 2>/dev/null') ?: '');
        }
        $health['apache_running'] = ($apacheRunning === 'active');

        $vhostFile = '/etc/apache2/sites-available/planethosts-panel.conf';
        if (!file_exists($vhostFile)) {
            $vhostFile = '/etc/httpd/conf.d/planethosts-panel.conf';
        }
        $health['vhost_exists'] = file_exists($vhostFile);

        if ($health['vhost_exists']) {
            $vhostContent = file_get_contents($vhostFile);
            $health['vhost_has_servername'] = (strpos($vhostContent, "ServerName {$hostname}") !== false);
        } else {
            $health['vhost_has_servername'] = false;
        }

        $ssl = $this->getSslStatus($hostname);
        $health['ssl_status'] = $ssl['status'];
        $health['ssl_days_left'] = $ssl['days_left'];

        $httpsTest = @file_get_contents("https://{$hostname}/", false, stream_context_create(['http' => ['timeout' => 5]]));
        $health['https_works'] = ($httpsTest !== false);

        $httpTest = @file_get_contents("http://{$hostname}/", false, stream_context_create(['http' => ['timeout' => 5]]));
        $health['http_works'] = ($httpTest !== false);

        return $health;
    }

    public function rebuildAll($hostname, $email = null)
    {
        $log = [];

        $log[] = '=== Hostname Rebuild ===';
        $log[] = 'Target: ' . $hostname;

        $log[] = '--- Updating system hostname ---';
        $log[] = $this->updateSystemHostname($hostname);

        $log[] = '--- Removing old vhosts ---';
        $this->removeOldPanelVhosts();
        $log[] = 'Old vhosts removed';

        $log[] = '--- Writing panel vhost ---';
        $vhostPath = $this->writePanelVhost($hostname);
        $log[] = "Vhost written: $vhostPath";

        $log[] = '--- Testing Apache config ---';
        $testResult = shell_exec('apache2ctl configtest 2>&1') ?: shell_exec('apachectl configtest 2>&1') ?: '';
        $log[] = trim($testResult);

        $log[] = '--- Reloading Apache ---';
        shell_exec('systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null');
        $log[] = 'Apache reloaded';

        $log[] = '--- Requesting SSL certificate ---';
        $sslOutput = $this->requestSsl($hostname, $email);
        $log[] = $sslOutput ?: 'SSL request completed';

        if (file_exists("/etc/letsencrypt/live/{$hostname}/fullchain.pem")) {
            $log[] = '--- Writing SSL vhost ---';
            $sslVhostPath = $this->writePanelSslVhost($hostname);
            $log[] = "SSL vhost written: " . ($sslVhostPath ?: 'skipped');
            shell_exec('systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null');
            $log[] = 'Apache reloaded for SSL';
        } else {
            $log[] = 'SSL certificate not yet issued (DNS may not be propagated). Retry later.';
        }

        $log[] = '--- Saving to panel settings ---';
        $existing = $this->db->table('automation_settings')->where('setting_key', 'hostname')->first();
        if ($existing) {
            $this->db->table('automation_settings')->where('setting_key', 'hostname')->update(['setting_value' => $hostname]);
        } else {
            $this->db->table('automation_settings')->insertGetId(['setting_key' => 'hostname', 'setting_value' => $hostname]);
        }
        $existingUrl = $this->db->table('automation_settings')->where('setting_key', 'panel_url')->first();
        if ($existingUrl) {
            $this->db->table('automation_settings')->where('setting_key', 'panel_url')->update(['setting_value' => "https://{$hostname}"]);
        } else {
            $this->db->table('automation_settings')->insertGetId(['setting_key' => 'panel_url', 'setting_value' => "https://{$hostname}"]);
        }
        $log[] = 'Settings saved';

        $log[] = '=== Rebuild complete ===';

        $logStr = implode("\n", $log);
        $logDir = BASE_PATH . '/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        file_put_contents("{$logDir}/hostname-rebuild.log", $logStr . "\n", FILE_APPEND);

        return $log;
    }

    public function getNameservers()
    {
        $ns1 = $this->db->table('automation_settings')->where('setting_key', 'ns1')->first();
        $ns2 = $this->db->table('automation_settings')->where('setting_key', 'ns2')->first();
        return [
            'ns1' => $ns1 ? $ns1->setting_value : 'ns1.planet-hosts.com',
            'ns2' => $ns2 ? $ns2->setting_value : 'ns2.planet-hosts.com',
        ];
    }
}
