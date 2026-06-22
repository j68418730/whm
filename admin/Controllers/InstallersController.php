<?php

namespace Admin\Controllers;

use Core\Controller;

class InstallersController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

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
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $apps = $this->getApps();
        $accounts = [];
        try { $accounts = $this->db->table('hosting_users')->get() ?: []; } catch (\Exception $e) {}

        $installs = [];
        try { $installs = $this->db->table('installer_tasks')->orderBy('created_at', 'DESC')->limit(10)->get() ?: []; } catch (\Exception $e) {}

        return $this->view('admin.installers.index', [
            'user' => $user,
            'theme_settings' => $theme_settings,
            'title' => 'One-Click Installer',
            'apps' => $apps,
            'accounts' => $accounts,
            'installs' => $installs
        ]);
    }

    public function install()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }

        $name = $this->request->post('app_name', '');
        $accountId = (int)$this->request->post('account_id', 0);
        $domain = trim($this->request->post('domain', ''));
        $dir = trim($this->request->post('directory', ''));
        $adminEmail = trim($this->request->post('admin_email', ''));
        $adminUser = trim($this->request->post('admin_user', 'admin'));
        $adminPass = trim($this->request->post('admin_pass', ''));

        if (!$name || !$domain) {
            $_SESSION['error_message'] = 'App name and domain are required.';
            $this->response->redirect('/admin/installers');
            exit;
        }

        $account = null;
        if ($accountId) {
            try { $account = $this->db->table('hosting_users')->where('id', $accountId)->first(); } catch (\Exception $e) {}
        }

        $domain = preg_replace('/[^a-z0-9.-]/', '', strtolower($domain));
        $targetDir = $dir ? "/var/www/html/{$domain}/{$dir}" : "/var/www/html/{$domain}";

        if ($account) {
            $targetDir = "/home/{$account->username}/public_html" . ($dir ? "/{$dir}" : "");
        }

        $downloadUrl = $this->getDownloadUrl($name);
        if (!$downloadUrl) {
            $_SESSION['error_message'] = "Unknown application: {$name}";
            $this->response->redirect('/admin/installers');
            exit;
        }

        @mkdir($targetDir, 0755, true);

        $zipFile = $targetDir . '/installer.zip';
        $downloadCmd = "wget -q -O " . escapeshellarg($zipFile) . " " . escapeshellarg($downloadUrl) . " 2>&1";
        $output = shell_exec($downloadCmd);

        if (!file_exists($zipFile) || filesize($zipFile) === 0) {
            $downloadCmd = "curl -sL -o " . escapeshellarg($zipFile) . " " . escapeshellarg($downloadUrl) . " 2>&1";
            $output = shell_exec($downloadCmd);
        }

        if (!file_exists($zipFile) || filesize($zipFile) === 0) {
            $_SESSION['error_message'] = "Failed to download {$name}. Please try again.";
            $this->response->redirect('/admin/installers');
            exit;
        }

        $unzipCmd = "cd " . escapeshellarg($targetDir) . " && unzip -qo " . escapeshellarg($zipFile) . " 2>&1 && rm -f " . escapeshellarg($zipFile) . " && echo OK";
        $result = shell_exec($unzipCmd);

        if (strpos($result, 'OK') !== false) {
            $itemDir = $targetDir;
            $subDirs = glob($targetDir . '/*', GLOB_ONLYDIR);
            if ($subDirs && count($subDirs) === 1) {
                $firstSub = $subDirs[0];
                $basename = basename($firstSub);
                if (is_file($firstSub . '/index.php') || is_file($firstSub . '/wp-config-sample.php')) {
                    shell_exec("cd " . escapeshellarg($targetDir) . " && mv " . escapeshellarg($basename) . "/* . 2>/dev/null && mv " . escapeshellarg($basename) . "/.[!.]* . 2>/dev/null && rmdir " . escapeshellarg($basename) . " 2>/dev/null");
                }
            }

            try {
                $pdo = $this->db->pdo();
                $pdo->exec("CREATE TABLE IF NOT EXISTS `installer_tasks` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `app_name` VARCHAR(100) NOT NULL,
                    `domain` VARCHAR(255) NOT NULL,
                    `directory` VARCHAR(255) DEFAULT NULL,
                    `account_id` INT DEFAULT NULL,
                    `status` VARCHAR(50) DEFAULT 'completed',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $this->db->table('installer_tasks')->insertGetId([
                    'app_name' => $name,
                    'domain' => $domain,
                    'directory' => $dir,
                    'account_id' => $accountId ?: null,
                    'status' => 'completed',
                ]);
            } catch (\Exception $e) {}

            $_SESSION['success_message'] = "{$name} installed successfully to {$targetDir}";
        } else {
            $_SESSION['error_message'] = "Installation failed: " . substr($result, 0, 200);
        }

        $this->response->redirect('/admin/installers');
        exit;
    }

    public function status($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->json(['error' => 'Unauthorized']);
            $this->response->send();
            exit;
        }

        $task = null;
        try { $task = $this->db->table('installer_tasks')->where('id', $id)->first(); } catch (\Exception $e) {}

        $this->response->json($task ? ['id' => $task->id, 'app' => $task->app_name, 'domain' => $task->domain, 'status' => $task->status, 'created' => $task->created_at] : ['error' => 'Not found']);
        $this->response->send();
        exit;
    }

    public function quickInstall()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }

        $accountId = (int)$this->request->post('account_id', 0);
        $appName = $this->request->post('app_name', 'WordPress');

        if (!$accountId) {
            $_SESSION['error_message'] = 'Account ID required.';
            $this->response->redirect('/admin/account');
            exit;
        }

        try {
            $account = $this->db->table('hosting_users')->where('id', $accountId)->first();
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Account not found.';
            $this->response->redirect('/admin/account');
            exit;
        }

        if (!$account) {
            $_SESSION['error_message'] = 'Account not found.';
            $this->response->redirect('/admin/account');
            exit;
        }

        $domain = $account->domain ?? "{$account->username}.planet-hosts.com";
        $targetDir = "/home/{$account->username}/public_html";
        $downloadUrl = $this->getDownloadUrl($appName);

        if (!$downloadUrl) {
            $_SESSION['error_message'] = "Unknown app: {$appName}";
            $this->response->redirect('/admin/account/show/' . $accountId);
            exit;
        }

        @mkdir($targetDir, 0755, true);

        $zipFile = $targetDir . '/installer.zip';
        shell_exec("wget -q -O " . escapeshellarg($zipFile) . " " . escapeshellarg($downloadUrl) . " 2>&1 || curl -sL -o " . escapeshellarg($zipFile) . " " . escapeshellarg($downloadUrl) . " 2>&1");

        if (file_exists($zipFile) && filesize($zipFile) > 0) {
            shell_exec("cd " . escapeshellarg($targetDir) . " && unzip -qo " . escapeshellarg($zipFile) . " 2>&1 && rm -f " . escapeshellarg($zipFile));
            $subs = glob($targetDir . '/*', GLOB_ONLYDIR);
            if ($subs && count($subs) === 1) {
                $bn = basename($subs[0]);
                if (is_file($subs[0] . '/index.php') || is_file($subs[0] . '/wp-config-sample.php')) {
                    shell_exec("cd " . escapeshellarg($targetDir) . " && mv " . escapeshellarg($bn) . "/* . 2>/dev/null && mv " . escapeshellarg($bn) . "/.[!.]* . 2>/dev/null && rmdir " . escapeshellarg($bn) . " 2>/dev/null");
                }
            }
            $_SESSION['success_message'] = "{$appName} installed to {$targetDir}";
        } else {
            $_SESSION['error_message'] = "Download failed for {$appName}";
        }

        $this->response->redirect('/admin/account/show/' . $accountId);
        exit;
    }

    private function getDownloadUrl($name)
    {
        $map = [
            'WordPress' => 'https://wordpress.org/latest.zip',
            'Joomla' => 'https://downloads.joomla.org/cms/joomla5/5-1-4/Joomla_5-1-4-Stable-Full_Package.zip',
            'Drupal' => 'https://ftp.drupal.org/files/projects/drupal-11.0.1.zip',
            'Laravel' => 'https://github.com/laravel/laravel/archive/refs/heads/master.zip',
            'phpMyAdmin' => 'https://files.phpmyadmin.net/phpMyAdmin/5.2.1/phpMyAdmin-5.2.1-all-languages.zip',
            'Nextcloud' => 'https://download.nextcloud.com/server/releases/latest.zip',
            'phpBB' => 'https://www.phpbb.com/files/release/phpBB-3.3.13.zip',
            'Moodle' => 'https://download.moodle.org/download.php/direct/stable401/moodle-latest-401.zip',
            'PrestaShop' => 'https://github.com/PrestaShop/PrestaShop/releases/download/8.1.7/prestashop_8.1.7.zip',
            'MediaWiki' => 'https://releases.wikimedia.org/mediawiki/1.42/mediawiki-1.42.1.zip',
        ];
        return $map[$name] ?? null;
    }

    private function getApps()
    {
        // Load from app_catalog table (has logo paths)
        try {
            $catalog = $this->db->table('app_catalog')->where('status', 'active')->orderBy('name', 'ASC')->get() ?: [];
            if (!empty($catalog)) {
                $apps = [];
                foreach ($catalog as $a) {
                    $logo = trim($a->logo ?? '');
                    if (!empty($logo) && file_exists(BASE_PATH . '/' . ltrim($logo, '/'))) {
                        $icon = '<img src="' . htmlspecialchars($logo) . '" style="width:48px;height:48px;border-radius:8px;object-fit:contain" alt="">';
                    } else {
                        // Check for any logo file in the app asset directory
                        $assetDir = BASE_PATH . '/theme/assets/apps/' . $a->slug;
                        $found = false;
                        if (is_dir($assetDir)) {
                            foreach (['logo.png','logo.jpg','logo.svg','logo.webp','logo.gif'] as $f) {
                                if (file_exists("$assetDir/$f")) {
                                    $logoPath = "/theme/assets/apps/{$a->slug}/$f";
                                    $icon = '<img src="' . $logoPath . '" style="width:48px;height:48px;border-radius:8px;object-fit:contain" alt="">';
                                    try { $this->db->table('app_catalog')->where('id', $a->id)->update(['logo' => $logoPath]); } catch (\Exception $e) {}
                                    $found = true;
                                    break;
                                }
                            }
                        }
                        if (!$found) $icon = '📦';
                    }
                    $apps[] = [
                        'name' => $a->name,
                        'icon' => $icon,
                        'desc' => $a->description ?: $a->name,
                        'version' => 'Latest',
                        'category' => $a->category ?: 'General',
                        'id' => $a->id,
                    ];
                }
                return $apps;
            }
        } catch (\Exception $e) {}
        // Fallback to marketplace_apps
        try {
            $marketApps = $this->db->table('marketplace_apps')->where('is_active', 1)->get() ?: [];
            if (!empty($marketApps)) {
                $apps = [];
                foreach ($marketApps as $ma) {
                    $apps[] = [
                        'name' => $ma->name,
                        'icon' => $ma->icon_url ? '<img src="' . htmlspecialchars($ma->icon_url) . '" style="width:48px;height:48px;border-radius:8px" alt="">' : '📦',
                        'desc' => $ma->description ?? $ma->name,
                        'version' => $ma->version ?? 'Latest',
                        'category' => $ma->category ?? 'General',
                        'id' => $ma->id,
                    ];
                }
                return $apps;
            }
        } catch (\Exception $e) {}
        return [];
    }
}
