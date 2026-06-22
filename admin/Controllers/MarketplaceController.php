<?php

namespace Admin\Controllers;

use Core\Controller;

class MarketplaceController extends Controller
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
        $apps = $this->db->table('marketplace_apps')->where('is_active', 1)->get() ?: [];
        $accounts = $this->db->table('hosting_users')->get() ?: [];
        $categories = ['CMS','E-Commerce','Forums','CRM & ERP','Cloud Storage','Helpdesk','Wiki','Development','Databases','Analytics','Marketing','Radio','AI','Project Management','Security','LMS'];
        $grouped = [];
        foreach ($categories as $cat) {
            $grouped[$cat] = array_filter($apps, function($a) use ($cat) {
                return stripos($a->category ?? '', $cat) !== false || $a->category === $cat;
            });
        }
        return $this->view('admin.marketplace.index', [
            'user' => $user, 'grouped' => $grouped, 'categories' => $categories,
            'accounts' => $accounts, 'title' => 'Marketplace'
        ]);
    }

    public function install($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $app = $this->db->table('marketplace_apps')->where('id', $id)->first();
        $accountId = (int)$this->request->post('account_id', 0);
        $account = $accountId ? $this->db->table('hosting_users')->where('id', $accountId)->first() : null;

        if (!$app) { $this->response->redirect('/admin/marketplace'); exit; }

        if ($account) {
            $username = $account->username;
            $domain = $account->domain ?: $username;
            $homeDir = "/home/{$username}";
            $publicHtml = "{$homeDir}/public_html";
        } else {
            $username = $this->request->post('username', 'app_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $app->name)));
            $homeDir = "/home/{$username}";
            $publicHtml = "{$homeDir}/public_html";
            exec("useradd -m -d {$homeDir} -s /bin/bash {$username} 2>/dev/null");
        }

        if (!is_dir($publicHtml)) mkdir($publicHtml, 0755, true);

        $domain = $account ? ($account->domain ?: $account->username . '.planet-hosts.com') : $username . '.local';
        $name = strtolower($app->name);
        $result = '';
        // Check for installer script first
        $installer = BASE_PATH . "/scripts/installers/{$name}.sh";
        if (is_file($installer)) {
            exec("bash {$installer} {$publicHtml} {$domain} 2>&1", $out, $code);
            $result = $code === 0 ? "Installed via script" : "Script failed: " . implode("\n", $out);
        } else {
            // Map app name to local zip file
            $localZip = BASE_PATH . '/appsinstall_files/' . $app->name . '.zip';
            if (!file_exists($localZip)) {
                // try lowercase name
                $localZip = BASE_PATH . '/appsinstall_files/' . ucfirst($name) . '.zip';
            }
            if (file_exists($localZip)) {
                $zipFile = $publicHtml . '/installer.zip';
                copy($localZip, $zipFile);
                if (file_exists($zipFile) && filesize($zipFile) > 0) {
                    exec("cd {$publicHtml} && unzip -qo installer.zip 2>/dev/null && rm -f installer.zip", $out, $code);
                    // Flatten single subdirectory
                    $subs = glob($publicHtml . '/*', GLOB_ONLYDIR);
                    if ($subs && count($subs) === 1) {
                        $bn = basename($subs[0]);
                        if (is_file($subs[0] . '/index.php') || is_file($subs[0] . '/wp-config-sample.php') || is_file($subs[0] . '/artisan')) {
                            exec("cd {$publicHtml} && mv {$bn}/* . 2>/dev/null && mv {$bn}/.[!.]* . 2>/dev/null && rmdir {$bn} 2>/dev/null");
                        }
                    }
                    $result = $code === 0 ? 'Installed from local package' : 'Unzip failed';
                } else {
                    $result = 'Failed to copy package';
                }
            } else {
                $result = "Auto-install not available for {$app->name}. Download manually.";
            }
        }

        if ($account) exec("chown -R {$username}:{$username} {$homeDir} 2>/dev/null");

        $target = $account ? "{$domain} (/{$username})" : "/home/{$username}";
        $_SESSION['success_message'] = "{$app->name} installed to {$target}: {$result}";
        $this->response->redirect('/admin/marketplace');
    }
}
