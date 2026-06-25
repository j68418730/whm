<?php

namespace Admin\Services;

class SslManager
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    // ─── Service Profiles ──────────────────────────────────

    public function getProfiles()
    {
        return [
            'apache' => [
                'name' => 'Apache',
                'config_dir' => '/etc/apache2/sites-available',
                'config_pattern' => '*.conf',
                'ssl_config_dir' => '/etc/apache2/sites-available',
                'ssl_option' => 'SSLEngine on',
                'restart_cmd' => 'systemctl reload apache2',
                'health_cmd' => 'systemctl is-active apache2',
                'detect_cmd' => 'which apache2 || which httpd',
                'cert_path' => '/etc/letsencrypt/live/{domain}/fullchain.pem',
                'key_path' => '/etc/letsencrypt/live/{domain}/privkey.pem',
                'chain_path' => '/etc/letsencrypt/live/{domain}/chain.pem',
                'default_ports' => [80, 443, 2083, 2087],
            ],
            'nginx' => [
                'name' => 'Nginx',
                'config_dir' => '/etc/nginx/sites-enabled',
                'config_pattern' => '*.conf',
                'ssl_config_dir' => '/etc/nginx/sites-enabled',
                'ssl_option' => 'ssl_certificate',
                'restart_cmd' => 'systemctl reload nginx',
                'health_cmd' => 'systemctl is-active nginx',
                'detect_cmd' => 'which nginx',
                'cert_path' => '/etc/letsencrypt/live/{domain}/fullchain.pem',
                'key_path' => '/etc/letsencrypt/live/{domain}/privkey.pem',
                'chain_path' => '/etc/letsencrypt/live/{domain}/chain.pem',
                'default_ports' => [80, 443],
            ],
            'icecast' => [
                'name' => 'Icecast',
                'config_dir' => '/etc/icecast2',
                'config_pattern' => 'icecast*.xml',
                'ssl_config_dir' => '/etc/icecast2',
                'ssl_option' => '<ssl-certificate>',
                'restart_cmd' => 'systemctl restart icecast2',
                'health_cmd' => 'systemctl is-active icecast2',
                'detect_cmd' => 'which icecast || systemctl list-units --type=service 2>/dev/null | grep icecast',
                'cert_path' => '/etc/letsencrypt/live/{domain}/fullchain.pem',
                'key_path' => '/etc/letsencrypt/live/{domain}/privkey.pem',
                'chain_path' => '/etc/letsencrypt/live/{domain}/chain.pem',
                'default_ports' => [8000, 8443],
                'ssl_modes' => ['native', 'reverse-proxy'],
            ],
            'ftp' => [
                'name' => 'FTP (FTPS)',
                'config_file' => '/etc/vsftpd/vsftpd.conf',
                'ssl_option' => 'ssl_enable=YES',
                'restart_cmd' => 'systemctl restart vsftpd',
                'health_cmd' => 'systemctl is-active vsftpd',
                'detect_cmd' => 'which vsftpd',
                'cert_path' => '/etc/letsencrypt/live/{domain}/fullchain.pem',
                'key_path' => '/etc/letsencrypt/live/{domain}/privkey.pem',
                'default_ports' => [21, 990],
            ],
            'postfix' => [
                'name' => 'Postfix (SMTP)',
                'config_file' => '/etc/postfix/main.cf',
                'ssl_option' => 'smtpd_tls_cert_file',
                'restart_cmd' => 'systemctl restart postfix',
                'health_cmd' => 'systemctl is-active postfix',
                'detect_cmd' => 'which postfix',
                'cert_path' => '/etc/letsencrypt/live/{domain}/fullchain.pem',
                'key_path' => '/etc/letsencrypt/live/{domain}/privkey.pem',
                'default_ports' => [25, 465, 587],
            ],
            'dovecot' => [
                'name' => 'Dovecot (IMAP/POP3)',
                'config_file' => '/etc/dovecot/conf.d/10-ssl.conf',
                'ssl_option' => 'ssl_cert',
                'restart_cmd' => 'systemctl restart dovecot',
                'health_cmd' => 'systemctl is-active dovecot',
                'detect_cmd' => 'which dovecot',
                'cert_path' => '/etc/letsencrypt/live/{domain}/fullchain.pem',
                'key_path' => '/etc/letsencrypt/live/{domain}/privkey.pem',
                'default_ports' => [993, 995],
            ],
            'liquidsoap' => [
                'name' => 'Liquidsoap',
                'config_dir' => '/etc/liquidsoap',
                'config_pattern' => '*.liq',
                'ssl_option' => 'ssl_certificate',
                'restart_cmd' => 'systemctl restart liquidsoap',
                'health_cmd' => 'systemctl is-active liquidsoap',
                'detect_cmd' => 'which liquidsoap',
                'cert_path' => '/etc/letsencrypt/live/{domain}/fullchain.pem',
                'key_path' => '/etc/letsencrypt/live/{domain}/privkey.pem',
                'default_ports' => [8080],
            ],
        ];
    }

    public function getProfile($type)
    {
        $profiles = $this->getProfiles();
        return $profiles[$type] ?? null;
    }

    // ─── Service Detection ──────────────────────────────────

    public function detectServices()
    {
        $services = [];

        foreach ($this->getProfiles() as $type => $profile) {
            $detected = $this->isServiceInstalled($type);
            if ($detected) {
                $services[$type] = $profile;
                $services[$type]['installed'] = true;
                $services[$type]['running'] = $this->isServiceRunning($type);
                $services[$type]['ports'] = $this->detectPorts($type);
            }
        }

        return $services;
    }

    public function isServiceInstalled($type)
    {
        $profile = $this->getProfile($type);
        if (!$profile) return false;
        $output = trim(shell_exec($profile['detect_cmd'] . ' 2>/dev/null') ?: '');
        return !empty($output);
    }

    public function isServiceRunning($type)
    {
        $profile = $this->getProfile($type);
        if (!$profile) return false;
        $output = trim(shell_exec($profile['health_cmd'] . ' 2>/dev/null') ?: '');
        return $output === 'active';
    }

    public function detectPorts($type)
    {
        $profile = $this->getProfile($type);
        if (!$profile) return [];

        $ports = $profile['default_ports'] ?? [];
        $active = [];
        foreach ($ports as $port) {
            $output = trim(shell_exec("ss -tlnp | grep ':{$port} ' 2>/dev/null") ?: '');
            if ($output) $active[] = $port;
        }
        return $active ?: $ports;
    }

    // ─── Certificate Management ─────────────────────────────

    public function requestLetsEncrypt($domain, $email = null)
    {
        if (!$email) $email = "admin@{$domain}";

        $output = shell_exec("certbot certonly --webroot -w /var/www/radiohosting/public"
            . " -d " . escapeshellarg($domain)
            . " --non-interactive --agree-tos --email " . escapeshellarg($email)
            . " 2>&1");

        $certPath = "/etc/letsencrypt/live/{$domain}/fullchain.pem";
        $success = file_exists($certPath);

        if ($success) {
            $expires = shell_exec("openssl x509 -enddate -noout -in {$certPath} 2>/dev/null | cut -d= -f2");
            $issuer = shell_exec("openssl x509 -issuer -noout -in {$certPath} 2>/dev/null | cut -d= -f2");

            $existing = $this->db->table('ssl_certs')->where('domain', $domain)->first();
            $data = [
                'certificate' => file_get_contents($certPath),
                'private_key' => file_get_contents("/etc/letsencrypt/live/{$domain}/privkey.pem"),
                'ca_chain' => @file_get_contents("/etc/letsencrypt/live/{$domain}/chain.pem"),
                'issuer' => trim($issuer ?: 'Let\'s Encrypt'),
                'expires_at' => $expires ? date('Y-m-d H:i:s', strtotime($expires)) : null,
                'status' => 'active',
                'last_renewal' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->db->table('ssl_certs')->where('domain', $domain)->update($data);
            } else {
                $data['domain'] = $domain;
                $this->db->table('ssl_certs')->insertGetId($data);
            }
        }

        $this->log('cert_request', $domain, $success ? 'success' : 'error', $output);

        return ['success' => $success, 'output' => $output];
    }

    public function renewAll()
    {
        $output = shell_exec("certbot renew --apache --non-interactive 2>&1");
        $renewed = [];

        // Check which certs were renewed
        $certs = $this->db->table('ssl_certs')->where('status', 'active')->get() ?: [];
        foreach ($certs as $cert) {
            $certPath = "/etc/letsencrypt/live/{$cert->domain}/fullchain.pem";
            if (file_exists($certPath)) {
                $expires = shell_exec("openssl x509 -enddate -noout -in {$certPath} 2>/dev/null | cut -d= -f2");
                $this->db->table('ssl_certs')->where('id', $cert->id)->update([
                    'certificate' => file_get_contents($certPath),
                    'private_key' => file_get_contents("/etc/letsencrypt/live/{$cert->domain}/privkey.pem"),
                    'expires_at' => $expires ? date('Y-m-d H:i:s', strtotime($expires)) : null,
                    'last_renewal' => date('Y-m-d H:i:s'),
                ]);
                $renewed[] = $cert->domain;
            }
        }

        $this->log('renew_all', '*', 'info', 'Renewed: ' . implode(', ', $renewed));
        return $renewed;
    }

    public function getCertificate($domain)
    {
        return $this->db->table('ssl_certs')->where('domain', $domain)->first();
    }

    public function getAllCertificates()
    {
        return $this->db->table('ssl_certs')->orderBy('created_at', 'DESC')->get() ?: [];
    }

    // ─── Service SSL Configuration ──────────────────────────

    public function configureServiceSsl($serviceType, $domain, $port = null, $mode = 'native')
    {
        $profile = $this->getProfile($serviceType);
        if (!$profile) return ['success' => false, 'error' => "Unknown service type: $serviceType"];

        $cert = $this->getCertificate($domain);
        if (!$cert) {
            $result = $this->requestLetsEncrypt($domain);
            if (!$result['success']) return ['success' => false, 'error' => 'Failed to obtain certificate: ' . ($result['output'] ?? '')];
            $cert = $this->getCertificate($domain);
        }

        $certPath = "/etc/letsencrypt/live/{$domain}/fullchain.pem";
        $keyPath = "/etc/letsencrypt/live/{$domain}/privkey.pem";

        if (!file_exists($certPath)) return ['success' => false, 'error' => 'Certificate files not found'];

        $result = ['success' => false, 'output' => ''];

        switch ($serviceType) {
            case 'apache':
                $result = $this->configureApacheSsl($domain, $port ?? 443, $certPath, $keyPath);
                break;
            case 'nginx':
                $result = $this->configureNginxSsl($domain, $port ?? 443, $certPath, $keyPath);
                break;
            case 'icecast':
                if ($mode === 'reverse-proxy') {
                    $result = $this->configureIcecastReverseProxy($domain, $port ?? 8443, $certPath, $keyPath);
                } else {
                    $result = $this->configureIcecastNativeSsl($domain, $port ?? 8443, $certPath, $keyPath);
                }
                break;
            case 'ftp':
                $result = $this->configureFtpSsl($certPath, $keyPath);
                break;
            case 'postfix':
                $result = $this->configurePostfixSsl($domain, $certPath, $keyPath);
                break;
            case 'dovecot':
                $result = $this->configureDovecotSsl($certPath, $keyPath);
                break;
        }

        if ($result['success']) {
            $serviceData = [
                'service_name' => $profile['name'],
                'service_type' => $serviceType,
                'domain' => $domain,
                'port' => $port ?? ($profile['default_ports'][1] ?? 443),
                'protocol' => 'https',
                'cert_id' => $cert->id,
                'ssl_enabled' => 1,
                'ssl_mode' => $mode,
                'status' => 'active',
                'last_verified' => date('Y-m-d H:i:s'),
            ];

            $existing = $this->db->table('ssl_services')
                ->where('service_type', $serviceType)
                ->where('domain', $domain)
                ->first();

            if ($existing) {
                $this->db->table('ssl_services')->where('id', $existing->id)->update($serviceData);
            } else {
                $this->db->table('ssl_services')->insertGetId($serviceData);
            }

            $this->log("ssl_configure", $domain, 'success', "{$profile['name']} SSL configured");
        }

        return $result;
    }

    protected function configureApacheSsl($domain, $port, $certPath, $keyPath)
    {
        $vhostDir = '/etc/apache2/sites-available';
        if (!is_dir($vhostDir)) $vhostDir = '/etc/httpd/conf.d';

        $vhostFile = "{$vhostDir}/{$domain}-ssl.conf";
        $vhost = "<VirtualHost *:{$port}>\n"
            . "    ServerName {$domain}\n"
            . "    ServerAlias www.{$domain}\n"
            . "    DocumentRoot /var/www/radiohosting/public\n"
            . "    SSLEngine on\n"
            . "    SSLCertificateFile {$certPath}\n"
            . "    SSLCertificateKeyFile {$keyPath}\n"
            . "    SSLCertificateChainFile " . dirname($certPath) . "/chain.pem\n"
            . "    <Directory /var/www/radiohosting/public>\n"
            . "        Options Indexes FollowSymLinks\n"
            . "        AllowOverride All\n"
            . "        Require all granted\n"
            . "    </Directory>\n"
            . "</VirtualHost>\n";

        file_put_contents($vhostFile, $vhost);

        if (strpos($vhostDir, 'sites-available') !== false) {
            shell_exec("a2ensite {$domain}-ssl.conf 2>/dev/null");
        }

        $test = shell_exec('apache2ctl configtest 2>&1') ?: shell_exec('apachectl configtest 2>&1') ?: '';
        if (strpos($test, 'Syntax OK') !== false) {
            shell_exec('systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null');
            return ['success' => true, 'output' => "Apache SSL vhost created for {$domain}"];
        }

        return ['success' => false, 'output' => $test];
    }

    protected function configureNginxSsl($domain, $port, $certPath, $keyPath)
    {
        $vhostDir = '/etc/nginx/sites-enabled';
        if (!is_dir($vhostDir)) $vhostDir = '/etc/nginx/conf.d';

        $vhostFile = "{$vhostDir}/{$domain}-ssl.conf";
        $vhost = "server {\n"
            . "    listen {$port} ssl http2;\n"
            . "    server_name {$domain} www.{$domain};\n"
            . "    root /var/www/radiohosting/public;\n"
            . "    ssl_certificate {$certPath};\n"
            . "    ssl_certificate_key {$keyPath};\n"
            . "    ssl_trusted_certificate " . dirname($certPath) . "/chain.pem;\n"
            . "}\n";

        file_put_contents($vhostFile, $vhost);

        $test = shell_exec('nginx -t 2>&1') ?: '';
        if (strpos($test, 'successful') !== false) {
            shell_exec('systemctl reload nginx 2>/dev/null');
            return ['success' => true, 'output' => "Nginx SSL vhost created for {$domain}"];
        }

        return ['success' => false, 'output' => $test];
    }

    protected function configureIcecastNativeSsl($domain, $port, $certPath, $keyPath)
    {
        $configDir = '/etc/icecast2';
        $configs = glob("{$configDir}/icecast*.xml");

        if (empty($configs)) return ['success' => false, 'output' => 'No Icecast configs found'];

        $results = [];
        foreach ($configs as $config) {
            $xml = file_get_contents($config);

            // Add SSL configuration inside <icecast> block
            $sslBlock = "\n    <ssl-certificate>{$certPath}</ssl-certificate>\n"
                . "    <ssl-private-key>{$keyPath}</ssl-private-key>\n";

            if (strpos($xml, '<ssl-certificate>') !== false) {
                $xml = preg_replace('/<ssl-certificate>.*?<\/ssl-certificate>/', "<ssl-certificate>{$certPath}</ssl-certificate>", $xml);
                $xml = preg_replace('/<ssl-private-key>.*?<\/ssl-private-key>/', "<ssl-private-key>{$keyPath}</ssl-private-key>", $xml);
            } else {
                $xml = preg_replace('/<\/icecast>/', $sslBlock . '</icecast>', $xml);
            }

            // Add HTTPS listen socket if not present
            if (strpos($xml, "<port>{$port}</port>") === false && $port !== 8000) {
                $listenSock = "\n    <listen-socket>\n"
                    . "        <port>{$port}</port>\n"
                    . "        <ssl>1</ssl>\n"
                    . "    </listen-socket>\n";
                $xml = preg_replace('/<\/icecast>/', $listenSock . '</icecast>', $xml);
            }

            file_put_contents($config, $xml);
            $results[] = basename($config);
        }

        shell_exec('systemctl restart icecast2 2>/dev/null');
        return ['success' => true, 'output' => 'Icecast native SSL configured: ' . implode(', ', $results)];
    }

    protected function configureIcecastReverseProxy($domain, $port, $certPath, $keyPath)
    {
        $vhostDir = '/etc/apache2/sites-available';
        if (!is_dir($vhostDir)) $vhostDir = '/etc/httpd/conf.d';

        // Detect Icecast backend port from config
        $icecastPort = 8000;
        $configs = glob('/etc/icecast2/icecast*.xml');
        if (!empty($configs)) {
            $xml = file_get_contents($configs[0]);
            preg_match('/<port>(.*?)<\/port>/', $xml, $m);
            if (!empty($m[1])) $icecastPort = (int)$m[1];
        }

        $vhostFile = "{$vhostDir}/{$domain}-stream-ssl.conf";
        $vhost = "<VirtualHost *:{$port}>\n"
            . "    ServerName {$domain}\n"
            . "    SSLEngine on\n"
            . "    SSLCertificateFile {$certPath}\n"
            . "    SSLCertificateKeyFile {$keyPath}\n\n"
            . "    # Proxy Icecast HTTP backend\n"
            . "    ProxyPreserveHost On\n"
            . "    ProxyPass / http://127.0.0.1:{$icecastPort}/\n"
            . "    ProxyPassReverse / http://127.0.0.1:{$icecastPort}/\n\n"
            . "    <Proxy *>\n"
            . "        Require all granted\n"
            . "    </Proxy>\n"
            . "</VirtualHost>\n";

        file_put_contents($vhostFile, $vhost);

        if (strpos($vhostDir, 'sites-available') !== false) {
            shell_exec("a2ensite {$domain}-stream-ssl.conf 2>/dev/null");
        }

        // Enable proxy modules
        shell_exec('a2enmod proxy proxy_http proxy_balancer 2>/dev/null');

        $test = shell_exec('apache2ctl configtest 2>&1') ?: shell_exec('apachectl configtest 2>&1') ?: '';
        if (strpos($test, 'Syntax OK') !== false) {
            shell_exec('systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null');
            return ['success' => true, 'output' => "Icecast reverse-proxy SSL configured on port {$port}"];
        }

        return ['success' => false, 'output' => $test];
    }

    protected function configureFtpSsl($certPath, $keyPath)
    {
        $configFile = '/etc/vsftpd/vsftpd.conf';
        if (!file_exists($configFile)) return ['success' => false, 'output' => 'vsftpd config not found'];

        $content = file_get_contents($configFile);
        $content = preg_replace('/^ssl_enable=.*/m', 'ssl_enable=YES', $content);
        $content = preg_replace('/^rsa_cert_file=.*/m', "rsa_cert_file={$certPath}", $content);
        $content = preg_replace('/^rsa_private_key_file=.*/m', "rsa_private_key_file={$keyPath}", $content);

        if (strpos($content, 'ssl_enable=') === false) $content .= "\nssl_enable=YES\n";
        if (strpos($content, 'rsa_cert_file=') === false) $content .= "\nrsa_cert_file={$certPath}\n";
        if (strpos($content, 'rsa_private_key_file=') === false) $content .= "\nrsa_private_key_file={$keyPath}\n";

        file_put_contents($configFile, $content);
        shell_exec('systemctl restart vsftpd 2>/dev/null');
        return ['success' => true, 'output' => 'FTPS configured'];
    }

    protected function configurePostfixSsl($domain, $certPath, $keyPath)
    {
        $configFile = '/etc/postfix/main.cf';
        if (!file_exists($configFile)) return ['success' => false, 'output' => 'Postfix config not found'];

        $content = file_get_contents($configFile);
        $lines = [
            "smtpd_tls_cert_file = {$certPath}",
            "smtpd_tls_key_file = {$keyPath}",
            "smtpd_tls_CAfile = " . dirname($certPath) . "/chain.pem",
            "smtpd_tls_security_level = may",
            "smtp_tls_security_level = may",
            "smtpd_tls_auth_only = no",
        ];

        foreach ($lines as $line) {
            $key = explode('=', $line)[0];
            $content = preg_replace("/^{$key}\s*=.*/m", $line, $content);
            if (strpos($content, $key) === false) $content .= "\n{$line}";
        }

        file_put_contents($configFile, $content);
        shell_exec('systemctl restart postfix 2>/dev/null');
        return ['success' => true, 'output' => 'Postfix SSL configured'];
    }

    protected function configureDovecotSsl($certPath, $keyPath)
    {
        $configFile = '/etc/dovecot/conf.d/10-ssl.conf';
        if (!file_exists($configFile)) return ['success' => false, 'output' => 'Dovecot SSL config not found'];

        $content = file_get_contents($configFile);
        $content = preg_replace('/^ssl\s*=.*/m', 'ssl = required', $content);
        $content = preg_replace('/^ssl_cert\s*=.*/m', "ssl_cert = <{$certPath}", $content);
        $content = preg_replace('/^ssl_key\s*=.*/m', "ssl_key = <{$keyPath}", $content);

        if (strpos($content, 'ssl = required') === false) $content = "ssl = required\n" . $content;
        if (strpos($content, 'ssl_cert =') === false) $content .= "\nssl_cert = <{$certPath}\n";
        if (strpos($content, 'ssl_key =') === false) $content .= "\nssl_key = <{$keyPath}\n";

        file_put_contents($configFile, $content);
        shell_exec('systemctl restart dovecot 2>/dev/null');
        return ['success' => true, 'output' => 'Dovecot SSL configured'];
    }

    // ─── Health Checks ──────────────────────────────────────

    public function checkServiceSsl($serviceType, $domain, $port = 443)
    {
        $profile = $this->getProfile($serviceType);
        $checks = ['service' => $serviceType, 'domain' => $domain, 'port' => $port];

        // Check cert file exists
        $certPath = "/etc/letsencrypt/live/{$domain}/fullchain.pem";
        $checks['cert_exists'] = file_exists($certPath);

        // Check cert expiry
        if ($checks['cert_exists']) {
            $expires = shell_exec("openssl x509 -enddate -noout -in {$certPath} 2>/dev/null | cut -d= -f2");
            $checks['expires'] = trim($expires ?: '');
            $checks['days_left'] = $expires ? max(0, floor((strtotime($expires) - time()) / 86400)) : 0;
            $checks['valid'] = $checks['days_left'] > 0;
        } else {
            $checks['expires'] = null;
            $checks['days_left'] = 0;
            $checks['valid'] = false;
        }

        // Check TLS handshake
        $handshake = shell_exec("echo | openssl s_client -connect 127.0.0.1:{$port} -servername {$domain} 2>/dev/null | openssl x509 -noout -subject 2>/dev/null");
        $checks['tls_handshake'] = !empty($handshake);

        // Check service running
        $checks['service_running'] = $this->isServiceRunning($serviceType);

        // Check hostname match
        if ($checks['cert_exists']) {
            $cn = shell_exec("openssl x509 -in {$certPath} -noout -subject 2>/dev/null | grep -o 'CN = [^,]*' | cut -d= -f2");
            $checks['hostname_match'] = trim($cn ?: '') === $domain;
        } else {
            $checks['hostname_match'] = false;
        }

        return $checks;
    }

    public function scanAllServices()
    {
        $results = [];
        $services = $this->db->table('ssl_services')->where('ssl_enabled', 1)->get() ?: [];

        foreach ($services as $svc) {
            $check = $this->checkServiceSsl($svc->service_type, $svc->domain, $svc->port);
            $check['service_name'] = $svc->service_name;
            $check['service_id'] = $svc->id;
            $check['ssl_mode'] = $svc->ssl_mode;

            $status = 'ok';
            if (!$check['cert_exists']) $status = 'missing_cert';
            elseif (!$check['tls_handshake']) $status = 'handshake_failed';
            elseif (!$check['service_running']) $status = 'service_down';
            elseif ($check['days_left'] < 7) $status = 'expiring_soon';
            elseif (!$check['hostname_match']) $status = 'hostname_mismatch';

            $check['status'] = $status;
            $results[] = $check;
        }

        return $results;
    }

    public function autoRepair($serviceId)
    {
        $svc = $this->db->table('ssl_services')->where('id', $serviceId)->first();
        if (!$svc) return ['success' => false, 'error' => 'Service not found'];

        $this->log('auto_repair', $svc->domain, 'started', "Repairing {$svc->service_type}");

        // Re-issue cert if missing
        $certPath = "/etc/letsencrypt/live/{$svc->domain}/fullchain.pem";
        if (!file_exists($certPath)) {
            $result = $this->requestLetsEncrypt($svc->domain);
            if (!$result['success']) {
                $this->log('auto_repair', $svc->domain, 'failed', 'Cert issue failed');
                return ['success' => false, 'error' => 'Certificate issuance failed'];
            }
        }

        // Re-configure service
        $result = $this->configureServiceSsl($svc->service_type, $svc->domain, $svc->port, $svc->ssl_mode);

        $this->db->table('ssl_services')->where('id', $serviceId)->update([
            'status' => $result['success'] ? 'active' : 'error',
            'last_verified' => date('Y-m-d H:i:s'),
            'last_error' => $result['success'] ? null : ($result['output'] ?? ''),
        ]);

        $this->log('auto_repair', $svc->domain, $result['success'] ? 'success' : 'failed', $result['output'] ?? '');

        return $result;
    }

    // ─── Port Scanning ──────────────────────────────────────

    public function scanListeningPorts()
    {
        $output = shell_exec('ss -tlnp 2>/dev/null | tail -n +2') ?: '';
        $ports = [];
        foreach (explode("\n", trim($output)) as $line) {
            if (preg_match('/:(\d+)\s/', $line, $m)) {
                $port = (int)$m[1];
                $protocol = strpos($line, 'tcp6') !== false ? 'tcp6' : 'tcp';
                $ports[] = ['port' => $port, 'protocol' => $protocol];
            }
        }
        return $ports;
    }

    public function identifyServiceOnPort($port)
    {
        $signatures = [
            80 => 'apache', 443 => 'apache',
            2082 => 'apache', 2083 => 'apache', 2087 => 'apache',
            8000 => 'icecast', 8443 => 'icecast',
            21 => 'ftp', 990 => 'ftp',
            25 => 'postfix', 465 => 'postfix', 587 => 'postfix',
            993 => 'dovecot', 995 => 'dovecot',
            3306 => 'mariadb', 6379 => 'redis',
        ];
        return $signatures[$port] ?? 'unknown';
    }

    // ─── Logging ────────────────────────────────────────────

    public function log($action, $domain, $status, $message = '')
    {
        try {
            $this->db->table('ssl_log')->insertGetId([
                'action' => $action,
                'domain' => $domain,
                'status' => $status,
                'message' => substr($message, 0, 500),
            ]);
        } catch (\Exception $e) {
            // Silently fail - log table may not exist yet
        }
    }

    public function getLogs($limit = 50)
    {
        try {
            return $this->db->table('ssl_log')->orderBy('created_at', 'DESC')->limit($limit)->get() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─── SSO / API SSL ──────────────────────────────────────

    public function configurePanelApiSsl($domain)
    {
        // Panel API uses same Apache vhost - just ensure cert exists
        $certPath = "/etc/letsencrypt/live/{$domain}/fullchain.pem";
        if (!file_exists($certPath)) {
            return $this->requestLetsEncrypt($domain);
        }
        return ['success' => true, 'output' => 'Certificate already exists'];
    }

    public function configureWebSocketSsl($domain, $port)
    {
        // For WebSocket, use Nginx reverse proxy
        return $this->configureNginxSsl($domain, $port,
            "/etc/letsencrypt/live/{$domain}/fullchain.pem",
            "/etc/letsencrypt/live/{$domain}/privkey.pem"
        );
    }
}
