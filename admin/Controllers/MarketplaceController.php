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
        } elseif (strpos($name, 'wordpress') !== false) {
            exec("cd {$publicHtml} && wget -q https://wordpress.org/latest.zip -O wp.zip && unzip -q wp.zip && mv wordpress/* . && rm -rf wordpress wp.zip 2>/dev/null", $out, $code);
            $result = $code === 0 ? 'WordPress downloaded' : 'Failed';
        } elseif (strpos($name, 'laravel') !== false) {
            exec("cd {$homeDir} && composer create-project laravel/laravel public_html --no-interaction 2>/dev/null", $out, $code);
            $result = $code === 0 ? 'Laravel installed' : 'Composer not found';
        } elseif (strpos($name, 'joomla') !== false) {
            exec("cd {$publicHtml} && wget -q https://downloads.joomla.org/cms/joomla5/5-1-4/Joomla_5-1-4-Stable-Full_Package.zip -O joomla.zip && unzip -q joomla.zip && rm joomla.zip 2>/dev/null", $out, $code);
            $result = $code === 0 ? 'Joomla downloaded' : 'Failed';
        } elseif (strpos($name, 'drupal') !== false) {
            exec("cd {$publicHtml} && wget -q https://ftp.drupal.org/files/projects/drupal-11.0.1.zip -O drupal.zip && unzip -q drupal.zip && mv drupal-*/* . && rm -rf drupal-* drupal.zip 2>/dev/null", $out, $code);
            $result = $code === 0 ? 'Drupal downloaded' : 'Failed';
        } elseif (strpos($name, 'nextcloud') !== false) {
            exec("cd {$publicHtml} && wget -q https://download.nextcloud.com/server/releases/latest.zip -O nc.zip && unzip -q nc.zip && mv nextcloud/* . && rm -rf nextcloud nc.zip 2>/dev/null", $out, $code);
            $result = $code === 0 ? 'NextCloud downloaded' : 'Failed';
        } elseif (strpos($name, 'phpmyadmin') !== false) {
            exec("cd {$publicHtml} && wget -q https://files.phpmyadmin.net/phpMyAdmin/5.2.1/phpMyAdmin-5.2.1-all-languages.zip -O pma.zip && unzip -q pma.zip && mv phpMyAdmin-*/* . && rm -rf phpMyAdmin-* pma.zip 2>/dev/null", $out, $code);
            $result = $code === 0 ? 'phpMyAdmin downloaded' : 'Failed';
        } else {
            $result = "Auto-install not available for {$app->name}. Download manually.";
        }

        if ($account) exec("chown -R {$username}:{$username} {$homeDir} 2>/dev/null");

        $target = $account ? "{$domain} (/{$username})" : "/home/{$username}";
        $_SESSION['success_message'] = "{$app->name} installed to {$target}: {$result}";
        $this->response->redirect('/admin/marketplace');
    }
}
