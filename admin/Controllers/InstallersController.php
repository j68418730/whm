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

        $localZip = $this->getLocalZip($name);
        if (!$localZip || !file_exists($localZip)) {
            $_SESSION['error_message'] = "Unknown application: {$name}";
            $this->response->redirect('/admin/installers');
            exit;
        }

        @mkdir($targetDir, 0755, true);

        $zipFile = $targetDir . '/installer.zip';
        copy($localZip, $zipFile);

        if (!file_exists($zipFile) || filesize($zipFile) === 0) {
            $_SESSION['error_message'] = "Failed to copy {$name} files. Please try again.";
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
        $localZip = $this->getLocalZip($appName);

        if (!$localZip || !file_exists($localZip)) {
            $_SESSION['error_message'] = "Unknown app: {$appName}";
            $this->response->redirect('/admin/account/show/' . $accountId);
            exit;
        }

        @mkdir($targetDir, 0755, true);

        $zipFile = $targetDir . '/installer.zip';
        copy($localZip, $zipFile);

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

    private function getLocalZip($name)
    {
        $map = [
            'WordPress' => BASE_PATH . '/appsinstall_files/WordPress.zip',
            'Joomla' => BASE_PATH . '/appsinstall_files/Joomla.zip',
            'Drupal' => BASE_PATH . '/appsinstall_files/Drupal.zip',
            'Laravel' => BASE_PATH . '/appsinstall_files/Laravel.zip',
            'phpMyAdmin' => BASE_PATH . '/appsinstall_files/phpMyAdmin.zip',
            'Nextcloud' => BASE_PATH . '/appsinstall_files/Nextcloud.zip',
            'phpBB' => BASE_PATH . '/appsinstall_files/phpBB.zip',
            'Moodle' => BASE_PATH . '/appsinstall_files/Moodle.zip',
            'PrestaShop' => BASE_PATH . '/appsinstall_files/PrestaShop.zip',
            'MediaWiki' => BASE_PATH . '/appsinstall_files/MediaWiki.zip',
            // Customer Support
            'osTicket' => BASE_PATH . '/appsinstall_files/osTicket.zip',
            'FreeScout' => BASE_PATH . '/appsinstall_files/FreeScout.zip',
            'UVdesk' => BASE_PATH . '/appsinstall_files/UVdesk.zip',
            // Analytics
            'Matomo' => BASE_PATH . '/appsinstall_files/Matomo.zip',
            // Galleries
            'Piwigo' => BASE_PATH . '/appsinstall_files/Piwigo.zip',
            'Lychee' => BASE_PATH . '/appsinstall_files/Lychee.zip',
            // Social Networking
            'HumHub' => BASE_PATH . '/appsinstall_files/HumHub.zip',
            'Elgg' => BASE_PATH . '/appsinstall_files/Elgg.zip',
            // CRM
            'SuiteCRM' => BASE_PATH . '/appsinstall_files/SuiteCRM.zip',
            'EspoCRM' => BASE_PATH . '/appsinstall_files/EspoCRM.zip',
            'Vtiger' => BASE_PATH . '/appsinstall_files/Vtiger.zip',
            // Project Management
            'Kanboard' => BASE_PATH . '/appsinstall_files/Kanboard.zip',
            'Collabtive' => BASE_PATH . '/appsinstall_files/Collabtive.zip',
            // File Management
            'eXtplorer' => BASE_PATH . '/appsinstall_files/eXtplorer.zip',
            'MonstaFTP' => BASE_PATH . '/appsinstall_files/MonstaFTP.zip',
            // RSS
            'FreshRSS' => BASE_PATH . '/appsinstall_files/FreshRSS.zip',
            // ERP
            'Dolibarr' => BASE_PATH . '/appsinstall_files/Dolibarr.zip',
            'FrontAccounting' => BASE_PATH . '/appsinstall_files/FrontAccounting.zip',
            // Calendar/Booking
            'EasyAppointments' => BASE_PATH . '/appsinstall_files/EasyAppointments.zip',
            // Music
            'Ampache' => BASE_PATH . '/appsinstall_files/Ampache.zip',
            'Castopod' => BASE_PATH . '/appsinstall_files/Castopod.zip',
            // Video
            'ClipBucket' => BASE_PATH . '/appsinstall_files/ClipBucket.zip',
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
        // Final fallback: list apps from local zip files
        $localDir = BASE_PATH . '/appsinstall_files';
        $apps = [];
        if (is_dir($localDir)) {
            $files = glob($localDir . '/*.zip');
            sort($files);
            foreach ($files as $f) {
                $name = basename($f, '.zip');
                $apps[] = [
                    'name' => $name,
                    'icon' => '📦',
                    'desc' => $name,
                    'version' => 'Latest',
                    'category' => 'General',
                    'id' => 0,
                ];
            }
        }
        return $apps;
    }
}
