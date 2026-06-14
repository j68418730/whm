<?php

namespace Admin\Controllers;

use Core\Controller;

class MarketplaceController extends Controller
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
        $apps = $this->db->table('marketplace_apps')->where('is_active', 1)->get() ?: [];
        $categories = ['CMS','E-Commerce','Forums','CRM & ERP','Cloud Storage','Helpdesk','Wiki','Development','Databases','Analytics','Marketing','Radio','AI','Project Management','Security','LMS'];
        $grouped = [];
        foreach ($categories as $cat) {
            $grouped[$cat] = array_filter($apps, function($a) use ($cat) {
                return stripos($a->category ?? '', $cat) !== false || $a->category === $cat;
            });
        }
        return $this->view('admin.marketplace.index', [
            'user' => $user, 'grouped' => $grouped, 'categories' => $categories,
            'title' => 'Marketplace'
        ]);
    }

    public function install($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $app = $this->db->table('marketplace_apps')->where('id', $id)->first();
        if (!$app) { $this->response->redirect('/admin/marketplace'); exit; }

        $username = $this->request->post('username', 'app_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $app->name)));
        $homeDir = "/home/{$username}";
        $publicHtml = "{$homeDir}/public_html";

        // Create user and directory
        exec("useradd -m -d {$homeDir} -s /bin/bash {$username} 2>/dev/null");
        mkdir($publicHtml, 0755, true);

        // Install based on type
        $name = strtolower($app->name);
        $installed = '';
        if (strpos($name, 'wordpress') !== false) {
            exec("cd {$publicHtml} && wp core download --allow-root 2>/dev/null", $out, $code);
            $installed = $code === 0 ? 'WordPress downloaded' : 'Failed';
        } elseif (strpos($name, 'laravel') !== false) {
            exec("cd {$homeDir} && composer create-project laravel/laravel public_html --no-interaction 2>/dev/null", $out, $code);
            $installed = $code === 0 ? 'Laravel installed' : 'Failed';
        } elseif (strpos($name, 'nextcloud') !== false) {
            exec("cd {$publicHtml} && wget -q https://download.nextcloud.com/server/releases/latest.zip && unzip -q latest.zip && mv nextcloud/* . && rm -rf nextcloud latest.zip 2>/dev/null", $out, $code);
            $installed = $code === 0 ? 'NextCloud downloaded' : 'Failed';
        } else {
            // Generic: create index page
            file_put_contents("{$publicHtml}/index.html", "<html><head><title>{$app->name}</title><style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f4f6f8}</style></head><body><h1>{$app->name}</h1><p>Installed by Planet Hosts Marketplace</p></body></html>");
            $installed = 'Created placeholder';
        }

        exec("chown -R {$username}:{$username} {$homeDir} 2>/dev/null");
        $_SESSION['success_message'] = "{$app->name}: {$installed}";
        $this->response->redirect('/admin/marketplace');
        exit;
    }
}
