<?php

namespace Admin\Controllers;

use Core\Controller;

class SetupController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        parent::__construct();
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function __init()
    {
        // Skip CSRF for setup wizard
    }

    protected function steps()
    {
        return [
            1 => 'License Agreement',
            2 => 'System Requirements',
            3 => 'License Activation',
            4 => 'Company Information',
            5 => 'Primary Domain',
            6 => 'Server Configuration',
            7 => 'Database Configuration',
            8 => 'Administrator Account',
            9 => 'Nameserver Configuration',
            10 => 'Web Hosting Configuration',
            11 => 'Mail Server Configuration',
            12 => 'Streaming Configuration',
            13 => 'SSL Configuration',
            14 => 'Firewall Configuration',
            15 => 'Security Configuration',
            16 => 'Backup Configuration',
            17 => 'Payment Gateways',
            18 => 'API Configuration',
            19 => 'Service Validation',
            20 => 'Install Summary',
            21 => 'Complete',
        ];
    }

    public function index()
    {
        if (is_file(BASE_PATH . '/config/install.lock')) {
            $this->response->redirect('/admin/dashboard');
            exit;
        }
        $this->initSession();
        $currentStep = $_SESSION['setup_step'] ?? 1;
        $this->response->redirect('/setup/' . $currentStep);
    }

    public function step($step = 1)
    {
        if (is_file(BASE_PATH . '/config/install.lock')) {
            $this->response->redirect('/admin/dashboard');
            exit;
        }
        $step = (int)$step;
        $steps = $this->steps();
        if ($step < 1) $step = 1;
        if ($step > count($steps)) $step = count($steps);

        $this->initSession();
        $_SESSION['setup_step'] = $step;

        $method = 'step' . $step;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->renderStep($step, ['content' => '<p>Step ' . $step . ' not implemented.</p>']);
    }

    public function postStep($step = 1)
    {
        if (is_file(BASE_PATH . '/config/install.lock')) {
            $this->response->redirect('/admin/dashboard');
            exit;
        }
        $step = (int)$step;
        $this->initSession();

        $method = 'saveStep' . $step;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        $this->goToStep($step + 1);
    }

    protected function initSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['setup_data'])) {
            $_SESSION['setup_data'] = [];
        }
        if (!isset($_SESSION['setup_step'])) {
            $_SESSION['setup_step'] = 1;
        }
    }

    protected function goToStep($step)
    {
        $steps = $this->steps();
        if ($step > count($steps)) {
            $step = count($steps);
        }
        $_SESSION['setup_step'] = $step;
        $this->response->redirect('/setup/' . $step);
    }

    protected function renderStep($step, $data = [])
    {
        $steps = $this->steps();
        $sd = $_SESSION['setup_data'] ?? [];
        $stepCount = count($steps);
        $title = $steps[$step] ?? 'Setup Step ' . $step;
        $progress = round(($step / $stepCount) * 100);

        return $this->view('admin.setup.step', array_merge([
            'setup_step' => $step,
            'setup_title' => $title,
            'setup_steps' => $steps,
            'setup_data' => $sd,
            'setup_progress' => $progress,
            'setup_step_count' => $stepCount,
            'user' => null,
            'title' => 'Setup Wizard - ' . $title,
            'theme_settings' => [],
        ], $data));
    }

    protected function sd($key, $default = '')
    {
        $d = $_SESSION['setup_data'] ?? [];
        return $d[$key] ?? $default;
    }

    protected function setSd($key, $value)
    {
        $_SESSION['setup_data'][$key] = $value;
    }

    // ─── STEP 1: LICENSE AGREEMENT ───

    protected function step1()
    {
        return $this->renderStep(1, [
            'accepted' => $this->sd('license_accepted', false),
        ]);
    }

    protected function saveStep1()
    {
        $accept = $this->request->post('accept', '');
        if ($accept !== 'yes') {
            $_SESSION['error_message'] = 'You must accept the license agreement to continue.';
            $this->response->redirect('/setup/1');
            exit;
        }
        $this->setSd('license_accepted', true);
        $this->setSd('license_accepted_at', date('Y-m-d H:i:s'));
        $this->goToStep(2);
    }

    // ─── STEP 2: SYSTEM REQUIREMENTS ───

    protected function step2()
    {
        $checks = $this->checkRequirements();
        return $this->renderStep(2, ['checks' => $checks]);
    }

    protected function checkRequirements()
    {
        $checks = [];

        // OS
        $os = php_uname('s') . ' ' . php_uname('r');
        $isLinux = stripos(PHP_OS, 'Linux') === 0;
        $checks['os'] = ['label' => 'Operating System', 'value' => $os, 'status' => $isLinux ? 'pass' : 'fail', 'critical' => true];

        // CPU Architecture
        $arch = php_uname('m');
        $checks['arch'] = ['label' => 'CPU Architecture', 'value' => $arch, 'status' => in_array($arch, ['x86_64', 'amd64']) ? 'pass' : 'warning', 'critical' => false];

        // RAM
        $memTotal = 0;
        if (is_file('/proc/meminfo')) {
            $memInfo = file_get_contents('/proc/meminfo');
            if (preg_match('/MemTotal:\s+(\d+)\s+kB/', $memInfo, $m)) {
                $memTotal = round($m[1] / 1024 / 1024, 1);
            }
        }
        $checks['ram'] = ['label' => 'Available RAM', 'value' => $memTotal ? $memTotal . ' GB' : 'Unknown', 'status' => $memTotal === 0 ? 'warning' : ($memTotal >= 1 ? 'pass' : 'fail'), 'critical' => $memTotal < 1];

        // Disk Space
        $diskFree = disk_free_space(BASE_PATH);
        $diskGb = $diskFree ? round($diskFree / 1024 / 1024 / 1024, 1) : 0;
        $checks['disk'] = ['label' => 'Available Disk', 'value' => $diskGb . ' GB', 'status' => $diskGb >= 10 ? 'pass' : ($diskGb >= 5 ? 'warning' : 'fail'), 'critical' => $diskGb < 5];

        // Internet
        $inet = @fsockopen('8.8.8.8', 53, $eno, $estr, 2);
        $checks['inet'] = ['label' => 'Internet Connectivity', 'value' => $inet ? 'Connected' : 'No Connection', 'status' => $inet ? 'pass' : 'fail', 'critical' => true];
        if ($inet) fclose($inet);

        // Root Access
        $isRoot = (function_exists('exec') && trim(@exec('id -u')) === '0');
        $checks['root'] = ['label' => 'Root Access', 'value' => $isRoot ? 'Yes' : 'No (sudo recommended)', 'status' => $isRoot ? 'pass' : 'warning', 'critical' => false];

        // Sudo
        $hasSudo = (function_exists('exec') && trim(@exec('which sudo 2>/dev/null')) !== '');
        $checks['sudo'] = ['label' => 'Sudo Available', 'value' => $hasSudo ? 'Yes' : 'No', 'status' => $hasSudo ? 'pass' : 'warning', 'critical' => false];

        // Apache/Nginx
        $apache = @exec('which apache2 2>/dev/null') ?: @exec('which httpd 2>/dev/null');
        $nginx = @exec('which nginx 2>/dev/null');
        $hasWeb = $apache || $nginx;
        $checks['webserver'] = ['label' => 'Web Server', 'value' => $apache ? 'Apache' : ($nginx ? 'Nginx' : 'Not Found'), 'status' => $hasWeb ? 'pass' : 'fail', 'critical' => true];

        // PHP
        $phpVer = phpversion();
        $checks['php'] = ['label' => 'PHP', 'value' => $phpVer, 'status' => version_compare($phpVer, '8.1', '>=') ? 'pass' : 'fail', 'critical' => true];

        // MariaDB/MySQL
        $mysql = @exec('which mariadb 2>/dev/null') ?: @exec('which mysql 2>/dev/null');
        $checks['mysql'] = ['label' => 'MariaDB / MySQL', 'value' => $mysql ? 'Installed' : 'Not Found', 'status' => $mysql ? 'pass' : 'fail', 'critical' => true];

        // Bind9
        $bind = @exec('which named 2>/dev/null') ?: @exec('which bind9 2>/dev/null');
        $checks['bind'] = ['label' => 'Bind9 (DNS)', 'value' => $bind ? 'Installed' : 'Not Found', 'status' => $bind ? 'pass' : 'warning', 'critical' => false];

        // Certbot
        $certbot = @exec('which certbot 2>/dev/null');
        $checks['certbot'] = ['label' => 'Certbot (SSL)', 'value' => $certbot ? 'Installed' : 'Not Found', 'status' => $certbot ? 'pass' : 'warning', 'critical' => false];

        // SSH
        $ssh = @exec('which sshd 2>/dev/null') ?: @exec('which ssh 2>/dev/null');
        $checks['ssh'] = ['label' => 'SSH Server', 'value' => $ssh ? 'Installed' : 'Not Found', 'status' => $ssh ? 'pass' : 'fail', 'critical' => true];

        // Cron
        $cron = @exec('which cron 2>/dev/null') ?: @exec('which crond 2>/dev/null');
        $checks['cron'] = ['label' => 'Cron Daemon', 'value' => $cron ? 'Installed' : 'Not Found', 'status' => $cron ? 'pass' : 'warning', 'critical' => false];

        // Firewall
        $fw = @exec('which firewalld 2>/dev/null') ?: @exec('which iptables 2>/dev/null') ?: @exec('which ufw 2>/dev/null');
        $checks['firewall'] = ['label' => 'Firewall', 'value' => $fw ? 'Installed' : 'Not Found', 'status' => $fw ? 'pass' : 'warning', 'critical' => false];

        // Redis
        $redis = @exec('which redis-server 2>/dev/null');
        $hasRedisExt = extension_loaded('redis');
        $checks['redis'] = ['label' => 'Redis', 'value' => ($redis ? 'Server' : '') . ($redis && $hasRedisExt ? ' + ' : '') . ($hasRedisExt ? 'Extension' : '') ?: 'Not Found', 'status' => ($redis || $hasRedisExt) ? 'pass' : 'warning', 'critical' => false];

        // Fail2Ban
        $f2b = @exec('which fail2ban-client 2>/dev/null');
        $checks['fail2ban'] = ['label' => 'Fail2Ban', 'value' => $f2b ? 'Installed' : 'Not Found', 'status' => $f2b ? 'pass' : 'warning', 'critical' => false];

        return $checks;
    }

    protected function saveStep2()
    {
        $checks = $this->checkRequirements();
        $hasFail = false;
        foreach ($checks as $k => $c) {
            if ($c['status'] === 'fail' && ($c['critical'] ?? false)) {
                $hasFail = true;
            }
        }
        $this->setSd('requirements', $checks);
        if ($hasFail) {
            $action = $this->request->post('action', '');
            if ($action !== 'force') {
                $_SESSION['error_message'] = 'Critical requirements not met. Please resolve them or force continue.';
                $this->response->redirect('/setup/2');
                exit;
            }
        }
        $this->goToStep(3);
    }

    // ─── STEP 3: LICENSE ACTIVATION ───

    protected function step3()
    {
        $licenseInfo = $this->sd('license', []);
        return $this->renderStep(3, [
            'license' => $licenseInfo,
            'license_key' => $this->sd('license_key', ''),
            'has_local_key' => is_file(BASE_PATH . '/license.key'),
        ]);
    }

    protected function saveStep3()
    {
        $mode = $this->request->post('license_mode', '');
        $licenseKey = $this->request->post('license_key', '');
        $customerEmail = $this->request->post('customer_email', '');
        $companyName = $this->request->post('company_name', '');
        $trialMode = $this->request->post('trial_mode', '');

        if ($mode === 'trial' || $trialMode === 'yes') {
            $this->setSd('license_key', 'TRIAL-' . strtoupper(bin2hex(random_bytes(8))));
            $this->setSd('license', [
                'type' => 'trial',
                'status' => 'active',
                'expires' => date('Y-m-d', strtotime('+30 days')),
                'features' => ['shared_hosting', 'radio_hosting', 'streaming_icecast', 'streaming_shoutcast_v1', 'streaming_shoutcast_v2', 'streaming_autodj', 'email_hosting', 'ftp_hosting', 'database_hosting', 'ssl_auto', 'backups', 'monitoring', 'api_access', 'desktop_app'],
                'max_accounts' => 5,
                'max_streams' => 10,
                'customer_name' => $companyName ?: 'Trial User',
                'customer_email' => $customerEmail ?: '',
            ]);
            $_SESSION['success_message'] = '30-day trial license activated!';
            $this->goToStep(4);
            exit;
        }

        if ($mode === 'online' && $licenseKey) {
            $result = $this->activateOnline($licenseKey, $customerEmail, $companyName);
            if ($result['success']) {
                $this->setSd('license_key', $licenseKey);
                $this->setSd('license', $result['data']);
                $this->saveLicenseFile($licenseKey, $result['data']);
                $_SESSION['success_message'] = 'License activated successfully!';
                $this->goToStep(4);
                exit;
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'License activation failed.';
                $this->response->redirect('/setup/3');
                exit;
            }
        }

        if ($mode === 'upload') {
            if (isset($_FILES['license_file']) && $_FILES['license_file']['error'] === UPLOAD_ERR_OK) {
                $content = file_get_contents($_FILES['license_file']['tmp_name']);
                file_put_contents(BASE_PATH . '/license.key', $content);
                @chmod(BASE_PATH . '/license.key', 0644);
            } elseif ($this->request->post('license_content')) {
                file_put_contents(BASE_PATH . '/license.key', $this->request->post('license_content'));
                @chmod(BASE_PATH . '/license.key', 0644);
            }
            $lic = new \Core\License(BASE_PATH);
            $result = $lic->verify(true);
            if ($result['valid']) {
                $this->setSd('license_key', 'UPLOADED');
                $this->setSd('license', $result['data']);
                $_SESSION['success_message'] = 'License file uploaded and verified!';
                $this->goToStep(4);
                exit;
            } else {
                $_SESSION['error_message'] = 'Invalid license file: ' . ($result['error'] ?? 'verification failed');
                $this->response->redirect('/setup/3');
                exit;
            }
        }

        $_SESSION['error_message'] = 'Please provide a license key or choose trial mode.';
        $this->response->redirect('/setup/3');
    }

    protected function activateOnline($licenseKey, $email, $company)
    {
        $ch = curl_init('https://license.planet-hosts.com/api/activate');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'license_key' => $licenseKey,
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'ip' => $_SERVER['SERVER_ADDR'] ?? '',
                'machine_id' => function_exists('server_hw_id') ? server_hw_id() : '',
                'hostname' => @exec('hostname') ?: '',
                'email' => $email,
                'company' => $company,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if ($data && ($data['success'] ?? false)) {
                return ['success' => true, 'data' => $data['data'] ?? $data];
            }
            return ['success' => false, 'error' => $data['error'] ?? 'Invalid response from licensing server'];
        }
        return ['success' => false, 'error' => 'Could not reach licensing server (HTTP ' . $httpCode . ')'];
    }

    protected function saveLicenseFile($licenseKey, $data)
    {
        $payload = json_encode([
            'license_id' => $data['license_id'] ?? 'LIC-' . date('Y') . '-' . rand(1000, 9999),
            'licensee' => $data['customer_name'] ?? 'Customer',
            'issued' => $data['activation_date'] ?? date('Y-m-d'),
            'expiry' => $data['expiration_date'] ?? 'never',
            'product' => 'Planet-Hosts WHM Panel',
            'version' => '1.0.0',
            'type' => $data['type'] ?? 'full',
        ], JSON_PRETTY_PRINT);
        $sig = base64_encode(hash('sha256', $payload . 'PLANET_HOSTS_STATIC_SIG', true));
        $content = "-----BEGIN PLANET HOSTS LICENSE-----\n";
        $content .= chunk_split($sig, 64, "\n");
        $content .= "-----BEGIN LICENSE DATA-----\n";
        $content .= $payload . "\n";
        $content .= "-----END LICENSE DATA-----\n";
        $content .= "-----END PLANET HOSTS LICENSE-----\n";
        file_put_contents(BASE_PATH . '/license.key', $content);
        @chmod(BASE_PATH . '/license.key', 0644);
    }

    // ─── STEP 4: COMPANY INFORMATION ───

    protected function step4()
    {
        return $this->renderStep(4, [
            'company_name' => $this->sd('company_name', ''),
            'company_website' => $this->sd('company_website', ''),
            'support_email' => $this->sd('support_email', ''),
            'billing_email' => $this->sd('billing_email', ''),
            'abuse_email' => $this->sd('abuse_email', ''),
            'noc_email' => $this->sd('noc_email', ''),
        ]);
    }

    protected function saveStep4()
    {
        $this->setSd('company_name', $this->request->post('company_name', ''));
        $this->setSd('company_website', $this->request->post('company_website', ''));
        $this->setSd('support_email', $this->request->post('support_email', ''));
        $this->setSd('billing_email', $this->request->post('billing_email', ''));
        $this->setSd('abuse_email', $this->request->post('abuse_email', ''));
        $this->setSd('noc_email', $this->request->post('noc_email', ''));

        if ($this->request->files('company_logo') && $this->request->files('company_logo')['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/storage/branding';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($this->request->files('company_logo')['tmp_name'], $uploadDir . '/logo.png');
        }

        $this->goToStep(5);
    }

    // ─── STEP 5: PRIMARY DOMAIN ───

    protected function step5()
    {
        $domain = $this->sd('primary_domain', '');
        if (!$domain) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $domain = preg_replace('/^[^.]*\./', '', $host);
        }
        $subdomains = $this->sd('subdomains', []);
        if (empty($subdomains) && $domain) {
            $subdomains = $this->generateSubdomains($domain);
        }
        return $this->renderStep(5, [
            'primary_domain' => $domain,
            'subdomains' => $subdomains,
        ]);
    }

    protected function generateSubdomains($domain)
    {
        $prefixes = ['panel', 'clients', 'api', 'mail', 'webmail', 'ftp', 'support', 'status', 'cdn', 'downloads', 'billing', 'radio'];
        $subs = [];
        foreach ($prefixes as $p) {
            $subs[$p] = $p . '.' . $domain;
        }
        return $subs;
    }

    protected function saveStep5()
    {
        $domain = $this->request->post('primary_domain', '');
        $domain = strtolower(trim($domain));
        $this->setSd('primary_domain', $domain);

        $subs = [];
        $prefixes = ['panel', 'clients', 'api', 'mail', 'webmail', 'ftp', 'support', 'status', 'cdn', 'downloads', 'billing', 'radio'];
        foreach ($prefixes as $p) {
            $val = $this->request->post('subdomain_' . $p, '');
            if ($val) $subs[$p] = $val;
        }
        if (empty($subs) && $domain) {
            $subs = $this->generateSubdomains($domain);
        }
        $this->setSd('subdomains', $subs);

        if (!$domain) {
            $_SESSION['error_message'] = 'Primary domain is required.';
            $this->response->redirect('/setup/5');
            exit;
        }
        $this->goToStep(6);
    }

    // ─── STEP 6: SERVER CONFIGURATION ───

    protected function step6()
    {
        $hostname = $this->sd('server_hostname', gethostname());
        $ip = $this->sd('server_ip', $_SERVER['SERVER_ADDR'] ?? '');
        if (!$ip) {
            $ip = @exec('hostname -I 2>/dev/null') ?: @exec('curl -s https://api.ipify.org 2>/dev/null') ?: '15.204.114.226';
            $ip = trim(explode(' ', $ip)[0]);
        }
        return $this->renderStep(6, [
            'server_hostname' => $hostname,
            'server_ip' => $ip,
            'server_timezone' => $this->sd('server_timezone', date_default_timezone_get()),
            'server_country' => $this->sd('server_country', 'US'),
            'datacenter_name' => $this->sd('datacenter_name', 'Skyline Hosting'),
            'server_role' => $this->sd('server_role', 'master'),
        ]);
    }

    protected function saveStep6()
    {
        $this->setSd('server_hostname', $this->request->post('server_hostname', ''));
        $this->setSd('server_ip', $this->request->post('server_ip', ''));
        $this->setSd('server_timezone', $this->request->post('server_timezone', 'UTC'));
        $this->setSd('server_country', $this->request->post('server_country', 'US'));
        $this->setSd('datacenter_name', $this->request->post('datacenter_name', ''));
        $this->setSd('server_role', $this->request->post('server_role', 'master'));
        $this->goToStep(7);
    }

    // ─── STEP 7: DATABASE CONFIGURATION ───

    protected function step7()
    {
        return $this->renderStep(7, [
            'db_host' => $this->sd('db_host', env('DB_HOST', 'localhost')),
            'db_port' => $this->sd('db_port', env('DB_PORT', '3306')),
            'db_name' => $this->sd('db_name', env('DB_NAME', 'radiohosting')),
            'db_user' => $this->sd('db_user', env('DB_USERNAME', 'radiouser')),
            'db_pass' => $this->sd('db_pass', env('DB_PASSWORD', '')),
            'db_connection_ok' => $this->sd('db_connection_ok', false),
        ]);
    }

    protected function saveStep7()
    {
        $host = $this->request->post('db_host', 'localhost');
        $port = $this->request->post('db_port', '3306');
        $name = $this->request->post('db_name', 'radiohosting');
        $user = $this->request->post('db_user', 'radiouser');
        $pass = $this->request->post('db_pass', '');

        $this->setSd('db_host', $host);
        $this->setSd('db_port', $port);
        $this->setSd('db_name', $name);
        $this->setSd('db_user', $user);
        $this->setSd('db_pass', $pass);

        $action = $this->request->post('action', 'test');
        if ($action === 'test') {
            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
                $pdo = new \PDO($dsn, $user, $pass, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 5,
                ]);
                $this->setSd('db_connection_ok', true);
                $_SESSION['success_message'] = 'Database connection successful!';
            } catch (\Exception $e) {
                $this->setSd('db_connection_ok', false);
                $_SESSION['error_message'] = 'Connection failed: ' . $e->getMessage();
                $this->response->redirect('/setup/7');
                exit;
            }
            $this->response->redirect('/setup/7');
            exit;
        }

        $this->setSd('db_connection_ok', true);
        $this->goToStep(8);
    }

    // ─── STEP 8: ADMINISTRATOR ACCOUNT ───

    protected function step8()
    {
        return $this->renderStep(8, [
            'admin_username' => $this->sd('admin_username', 'root'),
            'admin_email' => $this->sd('admin_email', ''),
            'admin_phone' => $this->sd('admin_phone', ''),
        ]);
    }

    protected function saveStep8()
    {
        $username = $this->request->post('admin_username', '');
        $password = $this->request->post('admin_password', '');
        $confirm = $this->request->post('admin_password_confirm', '');
        $email = $this->request->post('admin_email', '');
        $phone = $this->request->post('admin_phone', '');
        $twofactor = $this->request->post('admin_twofactor', '0');

        if (!$username) {
            $_SESSION['error_message'] = 'Username is required.';
            $this->response->redirect('/setup/8');
            exit;
        }
        if (!$password || strlen($password) < 8) {
            $_SESSION['error_message'] = 'Password must be at least 8 characters.';
            $this->response->redirect('/setup/8');
            exit;
        }
        if ($password !== $confirm) {
            $_SESSION['error_message'] = 'Passwords do not match.';
            $this->response->redirect('/setup/8');
            exit;
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            $_SESSION['error_message'] = 'Password must contain uppercase, lowercase, and number.';
            $this->response->redirect('/setup/8');
            exit;
        }

        $this->setSd('admin_username', $username);
        $this->setSd('admin_password', password_hash($password, PASSWORD_DEFAULT));
        $this->setSd('admin_password_raw', $password);
        $this->setSd('admin_email', $email);
        $this->setSd('admin_phone', $phone);
        $this->setSd('admin_twofactor', $twofactor);
        $this->goToStep(9);
    }

    // ─── STEP 9: NAMESERVER CONFIGURATION ───

    protected function step9()
    {
        $domain = $this->sd('primary_domain', 'planet-hosts.com');
        return $this->renderStep(9, [
            'ns1' => $this->sd('ns1', 'ns1.' . $domain),
            'ns2' => $this->sd('ns2', 'ns2.' . $domain),
        ]);
    }

    protected function saveStep9()
    {
        $this->setSd('ns1', $this->request->post('ns1', ''));
        $this->setSd('ns2', $this->request->post('ns2', ''));
        $this->goToStep(10);
    }

    // ─── STEP 10: WEB HOSTING CONFIGURATION ───

    protected function step10()
    {
        return $this->renderStep(10, [
            'web_server' => $this->sd('web_server', 'apache'),
            'default_php' => $this->sd('default_php', phpversion()),
            'default_docroot' => $this->sd('default_docroot', '/var/www'),
            'http2' => $this->sd('http2', '1'),
            'compression' => $this->sd('compression', '1'),
            'security_headers' => $this->sd('security_headers', '1'),
            'php_fpm' => $this->sd('php_fpm', '1'),
            'default_disk_quota' => $this->sd('default_disk_quota', '1024'),
            'default_bandwidth' => $this->sd('default_bandwidth', '10240'),
            'default_cpu' => $this->sd('default_cpu', '100'),
            'default_ram' => $this->sd('default_ram', '512'),
        ]);
    }

    protected function saveStep10()
    {
        foreach (['web_server','default_php','default_docroot','http2','compression','security_headers','php_fpm','default_disk_quota','default_bandwidth','default_cpu','default_ram'] as $k) {
            $this->setSd($k, $this->request->post($k, ''));
        }
        $this->goToStep(11);
    }

    // ─── STEP 11: MAIL SERVER CONFIGURATION ───

    protected function step11()
    {
        return $this->renderStep(11, [
            'enable_smtp' => $this->sd('enable_smtp', '1'),
            'enable_imap' => $this->sd('enable_imap', '1'),
            'enable_pop3' => $this->sd('enable_pop3', '1'),
            'enable_dkim' => $this->sd('enable_dkim', '1'),
            'enable_spf' => $this->sd('enable_spf', '1'),
            'enable_dmarc' => $this->sd('enable_dmarc', '1'),
        ]);
    }

    protected function saveStep11()
    {
        foreach (['enable_smtp','enable_imap','enable_pop3','enable_dkim','enable_spf','enable_dmarc'] as $k) {
            $this->setSd($k, $this->request->post($k, '0'));
        }
        $this->goToStep(12);
    }

    // ─── STEP 12: STREAMING CONFIGURATION ───

    protected function step12()
    {
        return $this->renderStep(12, [
            'streaming_shoutcast_v1' => $this->sd('streaming_shoutcast_v1', '1'),
            'streaming_shoutcast_v2' => $this->sd('streaming_shoutcast_v2', '1'),
            'streaming_icecast' => $this->sd('streaming_icecast', '1'),
            'streaming_autodj' => $this->sd('streaming_autodj', '1'),
            'streaming_rtmp' => $this->sd('streaming_rtmp', '0'),
            'streaming_rtsp' => $this->sd('streaming_rtsp', '0'),
            'streaming_relay' => $this->sd('streaming_relay', '0'),
        ]);
    }

    protected function saveStep12()
    {
        foreach (['streaming_shoutcast_v1','streaming_shoutcast_v2','streaming_icecast','streaming_autodj','streaming_rtmp','streaming_rtsp','streaming_relay'] as $k) {
            $this->setSd($k, $this->request->post($k, '0'));
        }
        $this->goToStep(13);
    }

    // ─── STEP 13: SSL CONFIGURATION ───

    protected function step13()
    {
        return $this->renderStep(13, [
            'letsencrypt_email' => $this->sd('letsencrypt_email', $this->sd('admin_email', '')),
            'auto_renewal' => $this->sd('auto_renewal', '1'),
            'wildcard_support' => $this->sd('wildcard_support', '0'),
        ]);
    }

    protected function saveStep13()
    {
        $this->setSd('letsencrypt_email', $this->request->post('letsencrypt_email', ''));
        $this->setSd('auto_renewal', $this->request->post('auto_renewal', '0'));
        $this->setSd('wildcard_support', $this->request->post('wildcard_support', '0'));
        $this->goToStep(14);
    }

    // ─── STEP 14: FIREWALL CONFIGURATION ───

    protected function step14()
    {
        return $this->renderStep(14, [
            'firewall_engine' => $this->sd('firewall_engine', 'iptables'),
            'ssh_port' => $this->sd('ssh_port', '22'),
        ]);
    }

    protected function saveStep14()
    {
        $this->setSd('firewall_engine', $this->request->post('firewall_engine', 'iptables'));
        $this->setSd('ssh_port', $this->request->post('ssh_port', '22'));
        $this->goToStep(15);
    }

    // ─── STEP 15: SECURITY CONFIGURATION ───

    protected function step15()
    {
        return $this->renderStep(15, [
            'enable_fail2ban' => $this->sd('enable_fail2ban', '1'),
            'enable_modsecurity' => $this->sd('enable_modsecurity', '1'),
            'enable_malware_scan' => $this->sd('enable_malware_scan', '1'),
            'enable_bruteforce' => $this->sd('enable_bruteforce', '1'),
            'enable_ssh_restrict' => $this->sd('enable_ssh_restrict', '1'),
            'enable_account_isolation' => $this->sd('enable_account_isolation', '1'),
        ]);
    }

    protected function saveStep15()
    {
        foreach (['enable_fail2ban','enable_modsecurity','enable_malware_scan','enable_bruteforce','enable_ssh_restrict','enable_account_isolation'] as $k) {
            $this->setSd($k, $this->request->post($k, '0'));
        }
        $this->goToStep(16);
    }

    // ─── STEP 16: BACKUP CONFIGURATION ───

    protected function step16()
    {
        return $this->renderStep(16, [
            'backup_location' => $this->sd('backup_location', '/var/backups/planethosts'),
            'backup_daily' => $this->sd('backup_daily', '1'),
            'backup_weekly' => $this->sd('backup_weekly', '1'),
            'backup_monthly' => $this->sd('backup_monthly', '1'),
            'backup_retention_daily' => $this->sd('backup_retention_daily', '7'),
            'backup_retention_weekly' => $this->sd('backup_retention_weekly', '4'),
            'backup_retention_monthly' => $this->sd('backup_retention_monthly', '3'),
        ]);
    }

    protected function saveStep16()
    {
        foreach (['backup_location','backup_daily','backup_weekly','backup_monthly','backup_retention_daily','backup_retention_weekly','backup_retention_monthly'] as $k) {
            $this->setSd($k, $this->request->post($k, ''));
        }
        $this->goToStep(17);
    }

    // ─── STEP 17: PAYMENT GATEWAYS ───

    protected function step17()
    {
        return $this->renderStep(17, [
            'enable_paypal' => $this->sd('enable_paypal', '0'),
            'paypal_client_id' => $this->sd('paypal_client_id', ''),
            'paypal_secret' => $this->sd('paypal_secret', ''),
            'enable_stripe' => $this->sd('enable_stripe', '0'),
            'stripe_publishable_key' => $this->sd('stripe_publishable_key', ''),
            'stripe_secret_key' => $this->sd('stripe_secret_key', ''),
            'enable_square' => $this->sd('enable_square', '0'),
            'square_access_token' => $this->sd('square_access_token', ''),
        ]);
    }

    protected function saveStep17()
    {
        foreach (['enable_paypal','paypal_client_id','paypal_secret','enable_stripe','stripe_publishable_key','stripe_secret_key','enable_square','square_access_token'] as $k) {
            $this->setSd($k, $this->request->post($k, ''));
        }
        $this->goToStep(18);
    }

    // ─── STEP 18: API CONFIGURATION ───

    protected function step18()
    {
        return $this->renderStep(18, [
            'api_key' => $this->sd('api_key', 'ph_' . bin2hex(random_bytes(16))),
            'api_secret' => $this->sd('api_secret', bin2hex(random_bytes(32))),
            'api_rate_limit' => $this->sd('api_rate_limit', '60'),
            'api_logging' => $this->sd('api_logging', '1'),
        ]);
    }

    protected function saveStep18()
    {
        $this->setSd('api_key', $this->request->post('api_key', ''));
        $this->setSd('api_secret', $this->request->post('api_secret', ''));
        $this->setSd('api_rate_limit', $this->request->post('api_rate_limit', '60'));
        $this->setSd('api_logging', $this->request->post('api_logging', '0'));
        $this->goToStep(19);
    }

    // ─── STEP 19: SERVICE VALIDATION ───

    protected function step19()
    {
        $results = $this->validateServices();
        return $this->renderStep(19, ['validation' => $results]);
    }

    protected function validateServices()
    {
        $results = [];

        // DB
        try {
            $app = \Core\Application::getInstance();
            $db = $app->get('db');
            $db->table('setup_settings')->first();
            $results['database'] = ['label' => 'Database', 'status' => 'pass', 'message' => 'Connected'];
        } catch (\Exception $e) {
            $results['database'] = ['label' => 'Database', 'status' => 'fail', 'message' => $e->getMessage()];
        }

        // Apache
        $apacheUp = @exec('systemctl is-active apache2 2>/dev/null') === 'active';
        $results['apache'] = ['label' => 'Apache', 'status' => $apacheUp ? 'pass' : 'warning', 'message' => $apacheUp ? 'Running' : 'Not running'];

        // DNS
        $namedUp = @exec('systemctl is-active named 2>/dev/null') === 'active';
        $results['dns'] = ['label' => 'DNS (Bind9)', 'status' => $namedUp ? 'pass' : 'warning', 'message' => $namedUp ? 'Running' : 'Not running'];

        // PHP-FPM
        $fpmUp = @exec('systemctl is-active php' . substr(phpversion(), 0, 3) . '-fpm 2>/dev/null') === 'active';
        $results['php_fpm'] = ['label' => 'PHP-FPM', 'status' => $fpmUp ? 'pass' : 'warning', 'message' => $fpmUp ? 'Running' : 'Not running'];

        // SSL
        $certbotExists = @exec('which certbot 2>/dev/null');
        $results['ssl'] = ['label' => 'SSL (Certbot)', 'status' => $certbotExists ? 'pass' : 'warning', 'message' => $certbotExists ? 'Available' : 'Not installed'];

        // Mail
        $postfixUp = @exec('systemctl is-active postfix 2>/dev/null') === 'active';
        $results['mail'] = ['label' => 'Mail (Postfix)', 'status' => $postfixUp ? 'pass' : 'warning', 'message' => $postfixUp ? 'Running' : 'Not running'];

        // Firewall
        $fwUp = @exec('systemctl is-active firewalld 2>/dev/null') === 'active';
        $results['firewall'] = ['label' => 'Firewall', 'status' => $fwUp ? 'pass' : 'warning', 'message' => $fwUp ? 'Running' : 'Not running'];

        // Streaming
        $icecastUp = @exec('systemctl is-active icecast2 2>/dev/null') === 'active';
        $shoutcastUp = @exec('systemctl is-active shoutcast 2>/dev/null') === 'active';
        $streaming = [];
        if ($icecastUp) $streaming[] = 'Icecast';
        if ($shoutcastUp) $streaming[] = 'SHOUTcast';
        $results['streaming'] = ['label' => 'Streaming Services', 'status' => !empty($streaming) ? 'pass' : 'info', 'message' => !empty($streaming) ? implode(', ', $streaming) : 'Not configured'];

        return $results;
    }

    protected function saveStep19()
    {
        $this->setSd('validation', $this->validateServices());
        $this->goToStep(20);
    }

    // ─── STEP 20: INSTALL SUMMARY ───

    protected function step20()
    {
        $sd = $_SESSION['setup_data'] ?? [];
        return $this->renderStep(20, ['sd' => $sd]);
    }

    protected function saveStep20()
    {
        $action = $this->request->post('action', '');
        if ($action === 'install') {
            $result = $this->finalizeInstallation();
            if ($result['success']) {
                $this->goToStep(21);
            } else {
                $_SESSION['error_message'] = 'Installation failed: ' . ($result['error'] ?? 'Unknown error');
                $this->response->redirect('/setup/20');
            }
        } else {
            $this->goToStep(21);
        }
    }

    protected function finalizeInstallation()
    {
        try {
            $sd = $_SESSION['setup_data'] ?? [];

            // Save all settings to DB
            if ($this->db) {
                $settingsMap = [
                    'company_name', 'company_website', 'support_email', 'billing_email', 'abuse_email', 'noc_email',
                    'primary_domain', 'server_hostname', 'server_ip', 'server_timezone', 'server_country', 'datacenter_name',
                    'server_role', 'ns1', 'ns2', 'web_server', 'default_php', 'default_docroot',
                    'http2', 'compression', 'security_headers', 'php_fpm',
                    'default_disk_quota', 'default_bandwidth', 'default_cpu', 'default_ram',
                    'letsencrypt_email', 'auto_renewal', 'wildcard_support',
                    'firewall_engine', 'ssh_port',
                    'backup_location', 'backup_daily', 'backup_weekly', 'backup_monthly',
                    'backup_retention_daily', 'backup_retention_weekly', 'backup_retention_monthly',
                    'api_key', 'api_secret', 'api_rate_limit', 'api_logging',
                    'enable_smtp', 'enable_imap', 'enable_pop3', 'enable_dkim', 'enable_spf', 'enable_dmarc',
                    'streaming_shoutcast_v1', 'streaming_shoutcast_v2', 'streaming_icecast',
                    'streaming_autodj', 'streaming_rtmp', 'streaming_rtsp', 'streaming_relay',
                    'enable_fail2ban', 'enable_modsecurity', 'enable_malware_scan',
                    'enable_bruteforce', 'enable_ssh_restrict', 'enable_account_isolation',
                ];

                foreach ($settingsMap as $key) {
                    if (isset($sd[$key])) {
                        $existing = $this->db->table('setup_settings')->where('setting_key', $key)->first();
                        $val = is_array($sd[$key]) ? json_encode($sd[$key]) : $sd[$key];
                        if ($existing) {
                            $this->db->table('setup_settings')->where('setting_key', $key)->update(['setting_value' => $val]);
                        } else {
                            $this->db->table('setup_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $val]);
                        }
                    }
                }

                // Save subdomains as JSON
                if (!empty($sd['subdomains'])) {
                    $existing = $this->db->table('setup_settings')->where('setting_key', 'subdomains')->first();
                    if ($existing) {
                        $this->db->table('setup_settings')->where('setting_key', 'subdomains')->update(['setting_value' => json_encode($sd['subdomains'])]);
                    } else {
                        $this->db->table('setup_settings')->insertGetId(['setting_key' => 'subdomains', 'setting_value' => json_encode($sd['subdomains'])]);
                    }
                }

                // Create/update admin user
                if (!empty($sd['admin_username']) && !empty($sd['admin_password'])) {
                    $existing = $this->db->table('admins')->where('username', $sd['admin_username'])->first();
                    if ($existing) {
                        $this->db->table('admins')->where('id', $existing->id)->update([
                            'password' => $sd['admin_password'],
                            'email' => $sd['admin_email'] ?? '',
                            'phone' => $sd['admin_phone'] ?? '',
                        ]);
                    } else {
                        $this->db->table('admins')->insertGetId([
                            'username' => $sd['admin_username'],
                            'password' => $sd['admin_password'],
                            'email' => $sd['admin_email'] ?? '',
                            'phone' => $sd['admin_phone'] ?? '',
                            'role' => 'admin',
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                // Save gateway settings
                if ($sd['enable_paypal'] ?? '0' === '1') {
                    $this->saveSetting('paypal_client_id', $sd['paypal_client_id'] ?? '');
                    $this->saveSetting('paypal_secret', $sd['paypal_secret'] ?? '');
                }
                if ($sd['enable_stripe'] ?? '0' === '1') {
                    $this->saveSetting('stripe_publishable_key', $sd['stripe_publishable_key'] ?? '');
                    $this->saveSetting('stripe_secret_key', $sd['stripe_secret_key'] ?? '');
                }
                if ($sd['enable_square'] ?? '0' === '1') {
                    $this->saveSetting('square_access_token', $sd['square_access_token'] ?? '');
                }
            }

            // Run hostname setup
            $hostname = $sd['server_hostname'] ?? '';
            if ($hostname) {
                @exec('sudo hostnamectl set-hostname ' . escapeshellarg($hostname) . ' 2>/dev/null');
                $hostsContent = file_get_contents('/etc/hosts');
                $ip = $sd['server_ip'] ?? '127.0.0.1';
                if (!str_contains($hostsContent, $hostname)) {
                    file_put_contents('/etc/hosts', $ip . "\t" . $hostname . "\n" . $hostsContent);
                }
            }

            // Set timezone
            $tz = $sd['server_timezone'] ?? '';
            if ($tz) {
                @exec('sudo timedatectl set-timezone ' . escapeshellarg($tz) . ' 2>/dev/null');
            }

            // Set backup dir
            $backupDir = $sd['backup_location'] ?? '/var/backups/planethosts';
            if (!is_dir($backupDir)) {
                @mkdir($backupDir, 0755, true);
            }

            // Save API key
            if (!empty($sd['api_key']) && $this->db) {
                $existing = $this->db->table('api_keys')->where('name', 'Master API Key')->first();
                $keyHash = hash('sha256', $sd['api_key']);
                if ($existing) {
                    $this->db->table('api_keys')->where('id', $existing->id)->update(['key_hash' => $keyHash]);
                } else {
                    $this->db->table('api_keys')->insertGetId([
                        'name' => 'Master API Key',
                        'key_hash' => $keyHash,
                        'permissions' => 'admin',
                        'rate_limit' => (int)($sd['api_rate_limit'] ?? 60),
                        'is_active' => 1,
                        'user_id' => 0,
                        'user_type' => 'root',
                    ]);
                }
            }

            $this->setSd('install_completed', date('Y-m-d H:i:s'));
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function saveSetting($key, $value)
    {
        try {
            $existing = $this->db->table('automation_settings')->where('setting_key', $key)->first();
            if ($existing) {
                $this->db->table('automation_settings')->where('setting_key', $key)->update(['setting_value' => $value]);
            } else {
                $this->db->table('automation_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $value]);
            }
        } catch (\Exception $e) {}
    }

    // ─── STEP 21: COMPLETE ───

    protected function step21()
    {
        // Create install lock
        $lockFile = BASE_PATH . '/config/install.lock';
        if (!is_file($lockFile)) {
            file_put_contents($lockFile, date('Y-m-d H:i:s') . "\n" . json_encode([
                'version' => '1.0.0',
                'installed_at' => date('Y-m-d H:i:s'),
                'install_method' => 'setup_wizard',
                'hostname' => gethostname(),
            ], JSON_PRETTY_PRINT));
            @chmod($lockFile, 0644);
        }

        $this->setSd('setup_complete', true);
        return $this->renderStep(21, []);
    }

    // ─── SKIP CSRF FOR SETUP ───

    protected function skipCsrf()
    {
        return true;
    }
}
