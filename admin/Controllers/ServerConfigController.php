<?php

namespace Admin\Controllers;

use Core\Controller;

class ServerConfigController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $hostname = trim(shell_exec('hostname 2>/dev/null') ?: 'localhost');
        $serverIp = trim(shell_exec('hostname -I 2>/dev/null') ?: $_SERVER['SERVER_ADDR'] ?? '127.0.0.1');
        $os = trim(shell_exec('cat /etc/os-release 2>/dev/null | grep "^PRETTY_NAME" | cut -d= -f2') ?: 'Linux');
        $kernel = trim(shell_exec('uname -r 2>/dev/null') ?: '');
        $uptime = trim(shell_exec('uptime -p 2>/dev/null') ?: '');
        $rootPass = \db_root_pass();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.serverconfig.index', [
            'user' => $user, 'title' => 'Server Configuration',
            'hostname' => $hostname, 'serverIp' => $serverIp, 'os' => $os,
            'kernel' => $kernel, 'uptime' => $uptime, 'rootPass' => $rootPass,
            'theme_settings' => $theme_settings,
        ]);
    }

    public function updateHostname()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $newHostname = $this->request->post('hostname', '');
        if ($newHostname) {
            shell_exec("hostnamectl set-hostname " . escapeshellarg($newHostname) . " 2>&1");
            file_put_contents('/etc/hostname', $newHostname);
            $_SESSION['success_message'] = "Hostname changed to {$newHostname}";
        }
        $this->response->redirect('/admin/serverconfig');
    }

    public function updateRootPass()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $newPass = $this->request->post('root_password', '');
        $type = $this->request->post('type', 'mysql');
        if ($newPass) {
            if ($type === 'mysql' || $type === 'both') {
                try {
                    $pdo = new \PDO('mysql:host=localhost;charset=utf8mb4', 'root', '');
                    $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY " . $pdo->quote($newPass));
                    $pdo->exec("FLUSH PRIVILEGES");
                } catch (\Exception $e) {
                    try {
                        $pdo2 = new \PDO('mysql:host=localhost;charset=utf8mb4', 'root', 'rootpassword');
                        $pdo2->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY " . $pdo2->quote($newPass));
                        $pdo2->exec("FLUSH PRIVILEGES");
                    } catch (\Exception $e2) {}
                }
                // Update .env
                $env = file_get_contents(BASE_PATH . '/.env');
                $env = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$newPass}", $env);
                file_put_contents(BASE_PATH . '/.env', $env);
            }
            if ($type === 'system' || $type === 'both') {
                exec('echo root:' . escapeshellarg($newPass) . ' | chpasswd 2>&1');
            }
            $_SESSION['success_message'] = "Password updated for {$type}.";
        }
        $this->response->redirect('/admin/serverconfig');
    }

    public function processManager()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.server.process_manager', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'Process Manager'
        ]);
    }

    public function setupPorts()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $domain = $this->request->post('domain', 'planet-hosts.com');
        $serverIp = $_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com';
        $adminPort = $this->request->post('admin_port', '2087');
        $resellerPort = $this->request->post('reseller_port', '2086');
        $userPort = $this->request->post('user_port', '2082');
        $webmailPort = $this->request->post('webmail_port', '2096');

        // Validate ports
        $ports = [$adminPort, $resellerPort, $userPort, $webmailPort];
        foreach ($ports as $i => $p) {
            if (!is_numeric($p) || $p < 1 || $p > 65535) {
                $_SESSION['error_message'] = "Invalid port: {$p}";
                $this->response->redirect('/admin/serverconfig'); exit;
            }
            $ports[$i] = (int)$p;
        }

        // Add Listen directives
        $conf = "/etc/apache2/ports.conf";
        foreach ($ports as $p) {
            $check = @shell_exec("grep -c 'Listen {$p}' " . escapeshellarg($conf) . " 2>/dev/null") ?: 0;
            if (trim($check) == '0') {
                file_put_contents($conf, "\nListen {$p}\n", FILE_APPEND);
            }
        }

        // Open ports in firewalld
        foreach ($ports as $p) {
            shell_exec("firewall-cmd --permanent --add-port={$p}/tcp 2>/dev/null");
        }
        shell_exec("firewall-cmd --reload 2>/dev/null");

        // Create vhosts
        $panelDir = BASE_PATH . '/public';
        $adminVhost = "<VirtualHost *:{$adminPort}>\n    DocumentRoot {$panelDir}\n    ServerName {$domain}\n    ServerAlias *:{$adminPort}\n    <Directory {$panelDir}>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>";
        $resellerVhost = "<VirtualHost *:{$resellerPort}>\n    DocumentRoot {$panelDir}\n    ServerName {$domain}\n    ServerAlias *:{$resellerPort}\n    <Directory {$panelDir}>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>";
        $userVhost = "<VirtualHost *:{$userPort}>\n    DocumentRoot {$panelDir}\n    ServerName {$domain}\n    ServerAlias *:{$userPort}\n    <Directory {$panelDir}>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>";
        $webmailVhost = "<VirtualHost *:{$webmailPort}>\n    DocumentRoot /var/www/html\n    ServerName {$domain}\n    ServerAlias *:{$webmailPort}\n</VirtualHost>";

        $vhostFile = "/etc/apache2/sites-available/radiohosting.conf";
        $existing = file_get_contents($vhostFile) ?: '';
        $existing .= "\n{$adminVhost}\n{$resellerVhost}\n{$userVhost}\n{$webmailVhost}\n";
        file_put_contents($vhostFile, $existing);

        shell_exec('systemctl restart apache2 2>&1');
        $_SESSION['success_message'] = "Ports configured: Admin {$adminPort}, Reseller {$resellerPort}, User {$userPort}, Webmail {$webmailPort}";
        $this->response->redirect('/admin/serverconfig');
    }

    public function serviceStart($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl unmask {$name} 2>/dev/null; systemctl start {$name} 2>&1");
        $this->response->redirect('/admin/serverconfig');
    }
    public function serviceStop($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl stop {$name} 2>&1");
        $this->response->redirect('/admin/serverconfig');
    }
    public function serviceRestart($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl restart {$name} 2>&1");
        $this->response->redirect('/admin/serverconfig');
    }

    public function tweak()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $cat = $this->request->get('cat', 'all');
        $q = trim((string)($this->request->get('q', '') ?? ''));
        $filter = (string)($this->request->get('filter', 'all') ?? 'all');
        $resolved = \Core\TweakEngine::resolved();
        $categories = \Core\TweakEngine::categories();
        $score = \Core\TweakEngine::securityScore();
        $history = \Core\TweakEngine::history(100);
        $thisCat = $categories[$cat] ?? null;
        $settings = [];
        foreach ($resolved as $s) {
            $def = $s['definition'];
            if ($cat !== 'all' && $cat !== 'history' && $def['cat'] !== $cat) continue;
            if ($q !== '') {
                $hay = strtolower($def['key'] . ' ' . $def['label'] . ' ' . ($def['desc'] ?? '') . ' ' . strtolower($categories[$def['cat']]['name'] ?? ''));
                if (strpos($hay, strtolower($q)) === false) continue;
            }
            $state = $s['source'] === 'custom' ? 'custom' : ($s['definition']['type'] === 'info' ? 'live' : 'default');
            if ($filter === 'custom' && $state !== 'custom') continue;
            if ($filter === 'default' && $state !== 'default') continue;
            if ($filter === 'live' && $state !== 'live') continue;
            if ($filter === 'requires_restart' && empty($def['restart'])) continue;
            if ($filter === 'high_risk' && ($def['risk'] ?? '') !== 'high') continue;
            if ($filter === 'security' && ($def['cat'] ?? '') !== 'security' && stripos($def['label'], 'secur') === false) continue;
            $settings[] = $s + ['state' => $state];
        }
        return $this->view('admin.serverconfig.tweak', [
            'user' => $user, 'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Tweak Settings',
            'cat' => $cat, 'q' => $q, 'filter' => $filter, 'thisCat' => $thisCat,
            'settings' => $settings, 'categories' => $categories, 'score' => $score, 'history' => $history,
        ]);
    }

    public function tweakSave()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $keys = (array)($this->request->post('tweak', []) ?? []);
        $confirmHighRisk = (string)($this->request->post('confirm_high_risk', '') ?? '') === '1';
        $reason = (string)($this->request->post('reason', '') ?? '');
        $applied = 0; $errors = []; $highRiskPending = []; $needsRestart = [];
        foreach ($keys as $key => $val) {
            if (is_array($val)) $val = implode(',', $val);
            $def = \Core\TweakEngine::find((string)$key);
            if (!$def || in_array($def['type'], ['info', 'link'], true)) continue;
            $cur = \Core\TweakEngine::value((string)$key);
            if ((string)$cur === (string)$val) continue;
            $isHigh = ($def['risk'] ?? '') === 'high';
            if ($isHigh && !$confirmHighRisk) { $highRiskPending[] = $def['label']; continue; }
            $r = \Core\TweakEngine::set((string)$key, (string)$val, $reason ?: 'Panel save');
            if ($r['ok']) { $applied++; if (!empty($r['restart'])) $needsRestart[] = $def['label']; }
            else { $errors[] = $def['label'] . ': ' . $r['message']; }
        }
        if ($highRiskPending) {
            $_SESSION['tweaks_high_risk'] = $highRiskPending;
            $_SESSION['error_message'] = 'High-risk change(s) require confirmation: ' . implode(', ', $highRiskPending) . ' — tick "Confirm high-risk changes" and save again.';
        }
        if ($applied > 0) {
            $msg = "Saved $applied setting(s).";
            if ($needsRestart) $msg .= ' Restart required for: ' . implode(', ', array_slice($needsRestart, 0, 5)) . (count($needsRestart) > 5 ? '…' : '');
            $_SESSION['success_message'] = $msg;
        }
        if ($errors) $_SESSION['error_message'] = trim(($_SESSION['error_message'] ?? '') . ' Errors: ' . implode('; ', $errors));
        $back = '/admin/tweak';
        if ($this->request->post('cat', '')) $back .= '?cat=' . urlencode((string)$this->request->post('cat', ''));
        $this->response->redirect($back);
    }

    public function tweakReset()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $key = (string)($this->request->get('key', '') ?? '');
        $r = \Core\TweakEngine::reset($key);
        $_SESSION[$r['ok'] ? 'success_message' : 'error_message'] = $r['message'];
        $this->response->redirect('/admin/tweak' . ($this->request->get('cat', '') ? '?cat=' . urlencode((string)$this->request->get('cat', '')) : ''));
    }

    public function tweakExport()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="planet-hosts-tweaks-' . date('Ymd_His') . '.json"');
        echo \Core\TweakEngine::export();
        exit;
    }

    public function tweakImport()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $raw = (string)($this->request->post('tweaks_json', '') ?? '');
        $confirm = (string)($this->request->post('confirm', '') ?? '') === '1';
        if ($raw === '') {
            $_SESSION['error_message'] = 'Paste a tweaks JSON export.';
            $this->response->redirect('/admin/tweak?cat=all');
        }
        $r = \Core\TweakEngine::import($raw, $confirm);
        if (!$confirm && !empty($r['preview'])) {
            $_SESSION['tweaks_import_preview'] = ['json' => $raw, 'changes' => array_slice($r['preview'], 0, 100), 'total' => count($r['preview'])];
            $_SESSION['success_message'] = 'Preview: ' . $r['message'] . ' Review below, back up the current config (Export), then confirm.';
        } elseif ($r['ok']) {
            $_SESSION['success_message'] = $r['message'];
        } else {
            $_SESSION['error_message'] = $r['message'];
        }
        $this->response->redirect('/admin/tweak?cat=all');
    }

    public function tweakProfile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $profileId = (string)($this->request->get('id', '') ?? '');
        $confirm = (string)($this->request->get('confirm', '') ?? '') === '1';
        $profiles = \Core\TweakEngine::profiles();
        if (!isset($profiles[$profileId])) {
            $_SESSION['error_message'] = 'Unknown profile.';
            $this->response->redirect('/admin/tweak?cat=all');
        }
        if (!$confirm) {
            $changes = [];
            foreach ($profiles[$profileId]['settings'] as $k => $v) {
                $def = \Core\TweakEngine::find((string)$k);
                if (!$def) continue;
                $changes[] = ['label' => $def['label'], 'old' => \Core\TweakEngine::value((string)$k), 'new' => $v, 'risk' => $def['risk'] ?? 'normal'];
            }
            $_SESSION['tweaks_profile_preview'] = ['id' => $profileId, 'name' => $profiles[$profileId]['name'], 'changes' => $changes];
            $_SESSION['success_message'] = 'Profile preview: ' . $profiles[$profileId]['name'] . ' (' . count($changes) . ' changes). Click "Apply" to confirm.';
            $this->response->redirect('/admin/tweak?cat=all');
        }
        $applied = 0;
        foreach ($profiles[$profileId]['settings'] as $k => $v) {
            if (\Core\TweakEngine::find((string)$k)) {
                $r = \Core\TweakEngine::set((string)$k, (string)$v, 'Profile: ' . $profiles[$profileId]['name']);
                if ($r['ok']) $applied++;
            }
        }
        $_SESSION['success_message'] = "Profile '{$profiles[$profileId]['name']}' applied ($applied settings).";
        $this->response->redirect('/admin/tweak?cat=all');
    }
}

