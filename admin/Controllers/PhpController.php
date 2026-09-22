<?php

namespace Admin\Controllers;

use Core\Controller;

class PhpController extends Controller
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

    public function switcher()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $versions = [];
        exec('ls /usr/bin/php* 2>/dev/null', $out);
        foreach ($out as $p) {
            if (preg_match('/php(\d+\.\d+)$/', $p, $m)) $versions[] = $m[1];
        }
        if (empty($versions)) $versions = ['8.2'];
        $domains = $this->db->table('hosting_users')->where('status', 'active')->get() ?: [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.php.switcher', [
            'user' => $user, 'versions' => $versions, 'domains' => $domains,
            'theme_settings' => $theme_settings, 'title' => 'PHP Version Switcher'
        ]);
    }

    public function switcherPost()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $domain = $this->request->post('domain', '');
        $version = $this->request->post('version', '');
        if ($domain && $version) {
            $this->db->table('hosting_users')->where('domain', $domain)->update(['php_version' => $version]);
            $vhostFile = "/etc/apache2/sites-available/{$domain}.conf";
            if (file_exists($vhostFile)) {
                $content = file_get_contents($vhostFile);
                $content = preg_replace('/SetHandler .*php.*-fpm.*/', "SetHandler \"proxy:unix:/run/php/php{$version}-fpm.sock|fcgi://localhost\"", $content);
                file_put_contents($vhostFile, $content);
            }
            exec("systemctl reload apache2 2>/dev/null");
            $_SESSION['success_message'] = "PHP version for {$domain} set to {$version}.";
        }
        $this->response->redirect('/admin/php-switcher');
        exit;
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $loaded = get_loaded_extensions();
        $allExts = ['bcmath','bz2','calendar','ctype','curl','date','dom','exif','fileinfo','filter','ftp','gd','gettext','gmp','hash','iconv','imagick','imap','intl','json','ldap','libxml','mbstring','mysqli','mysqlnd','opcache','openssl','pcntl','pcre','PDO','pdo_mysql','pdo_sqlite','pear','phar','posix','pspell','readline','redis','reflection','session','shmop','SimpleXML','soap','sockets','sodium','SPL','sqlite3','standard','sysvmsg','sysvsem','sysvshm','tokenizer','wddx','xml','xmlreader','xmlwriter','xsl','Zend OPcache','zip','zlib'];
        $avail = array_diff($allExts, $loaded);
        $domains = $this->db->table('hosting_users')->where('status', 'active')->get() ?: [];
        // Get per-domain extensions from db
        $domainExts = [];
        try {
            $rows = $this->db->table('php_domain_extensions')->get() ?: [];
            foreach ($rows as $r) $domainExts[$r->domain][] = $r->extension;
        } catch (\Exception $e) {}

        // Detect ALL installed PHP versions + their loaded extensions
        $versions = [];
        $versionBin = $this->request->get('version', '');
        exec('ls /usr/bin/php* 2>/dev/null', $bins);
        foreach ($bins as $b) {
            if (preg_match('#/php([0-9]+\.[0-9]+)$#', $b, $m)) {
                $v = $m[1];
                $verOut = @shell_exec(escapeshellarg($b) . ' -r "echo PHP_VERSION;" 2>/dev/null');
                $extOut = @shell_exec(escapeshellarg($b) . ' -r "echo implode(\",\", get_loaded_extensions());" 2>/dev/null');
                $exts = $extOut ? explode(',', $extOut) : [];
                $versions[$v] = [
                    'binary' => $b,
                    'php_version' => $verOut ?: $v,
                    'loaded' => $exts,
                    'available' => array_diff($allExts, $exts),
                ];
            }
        }
        ksort($versions);
        // Current selected version (default: the CLI default php)
        $selVersion = $versionBin ?: (trim((string)@shell_exec('php -r "echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;" 2>/dev/null')) ?: PHP_VERSION);
        $selBin = $versions[$selVersion]['binary'] ?? '/usr/bin/php';

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.php.index', [
            'user' => $user, 'loaded' => $loaded, 'available' => $avail,
            'domains' => $domains, 'domainExts' => $domainExts, 'allExts' => $allExts,
            'versions' => $versions, 'selected_version' => $selVersion, 'selected_binary' => $selBin,
            'phpStats' => ['enabled_extensions' => count($loaded), 'php_versions' => count($versions), 'available_versions' => explode('.', PHP_VERSION)],
            'theme_settings' => $theme_settings, 'title' => 'PHP Manager'
        ]);
    }

    public function extensions()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $loaded = get_loaded_extensions();
        $allExts = ['bcmath','bz2','calendar','ctype','curl','date','dom','exif','fileinfo','filter','ftp','gd','gettext','gmp','hash','iconv','imagick','imap','intl','json','ldap','libxml','mbstring','mysqli','mysqlnd','opcache','openssl','pcntl','pcre','PDO','pdo_mysql','pdo_sqlite','pear','phar','posix','pspell','readline','redis','reflection','session','shmop','SimpleXML','soap','sockets','sodium','SPL','sqlite3','standard','sysvmsg','sysvsem','sysvshm','tokenizer','wddx','xml','xmlreader','xmlwriter','xsl','Zend OPcache','zip','zlib'];
        $avail = array_diff($allExts, $loaded);
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.php.index', [
            'user' => $user, 'loaded' => $loaded, 'available' => $avail,
            'theme_settings' => $theme_settings, 'title' => 'PHP Extensions'
        ]);
    }

    public function config()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $ini = ini_get_all();
        $settings = [];
        $keys = ['memory_limit','max_execution_time','max_input_time','upload_max_filesize','post_max_size','max_input_vars','date.timezone','display_errors','error_reporting','allow_url_fopen','session.gc_maxlifetime','session.cookie_httponly','session.cookie_secure'];
        foreach ($keys as $k) {
            if (isset($ini[$k])) $settings[$k] = $ini[$k]['local_value'];
        }
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.php.index', [
            'user' => $user, 'phpConfig' => $settings,
            'theme_settings' => $theme_settings, 'title' => 'PHP Config'
        ]);
    }

    public function install($ext, $version = null)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $ext = basename($ext);
        $version = preg_replace('/[^0-9.]/', '', (string)$version);
        // Version-specific package: php8.2-mbstring, php7.4-xml, etc. Fall back to php-{ext}
        $pkg = $version ? "php{$version}-{$ext}" : "php-{$ext}";
        $out = shell_exec("apt-get install -y " . escapeshellarg($pkg) . " 2>&1");
        if (!$out || str_contains($out, 'E: Unable to locate package') || str_contains($out, 'Unable to locate')) {
            // Try generic php-{ext} as fallback
            $out2 = shell_exec("apt-get install -y php-{$ext} 2>&1");
            $out = ($out ?? '') . "\n[fallback]\n" . ($out2 ?? '');
        }
        // Enable the extension in the version's FPM ini if it exists
        $iniGlob = glob("/etc/php/{$version}/fpm/conf.d/*.ini");
        $_SESSION['success_message'] = "Installing {$pkg}" . ($version ? " (PHP {$version})" : '') . "… " . (str_contains($out, 'Setting up') || !str_contains($out, 'Unable to') ? 'Complete.' : 'See output.');
        $this->response->redirect('/admin/php?version=' . urlencode($version));
        exit;
    }

    public function domainExtPost()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $domain = $this->request->post('domain', '');
        $exts = $this->request->post('extensions', []);
        if (!$domain) { $_SESSION['error_message'] = 'Domain required.'; $this->response->redirect('/admin/php'); exit; }
        try {
            $this->db->pdo()->prepare("DELETE FROM php_domain_extensions WHERE domain = ?")->execute([$domain]);
            foreach ($exts as $e) {
                $e = basename(trim($e));
                if ($e) $this->db->table('php_domain_extensions')->insertGetId(['domain' => $domain, 'extension' => $e]);
            }
            // Update PHP-FPM pool config for this domain
            $poolFile = "/etc/php/*/fpm/pool.d/{$domain}.conf";
            $poolContent = "; PHP extensions for {$domain}\n";
            foreach ($exts as $e) {
                $e = basename(trim($e));
                if ($e) $poolContent .= "php_admin_value[extension] = {$e}.so\n";
            }
            exec("mkdir -p /etc/php/*/fpm/pool.d/ 2>/dev/null");
            file_put_contents("/etc/php/*/fpm/pool.d/{$domain}-ext.conf", $poolContent);
            exec("systemctl reload php*-fpm 2>/dev/null");
            $_SESSION['success_message'] = "Per-domain PHP extensions updated for {$domain}.";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to save domain extensions.';
        }
        $this->response->redirect('/admin/php');
        exit;
    }
}