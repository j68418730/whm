<?php

namespace Admin\Controllers;

use Core\Controller;

class DashboardController extends Controller
{
    protected $auth;
    protected $response;
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        $user = $this->auth->user();
        $accounts = $this->db->table('hosting_users')->get() ?: [];
        $packages = $this->db->table('hosting_packages')->get() ?: [];
        $resellers = $this->db->table('resellers')->get() ?: [];

        $activeAccounts = count(array_filter($accounts, function($a) { return $a->status === 'active'; }));
        $activePackages = count(array_filter($packages, function($p) { return ($p->is_active ?? 0) == 1; }));

        $pluginManager = \Core\Application::getInstance()->getPluginManager();
        $addons = $pluginManager ? $pluginManager->loadedMetadata() : [];

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        return $this->view('admin.dashboard.index', [
            'user' => $user,
            'stats' => [
                'total_accounts' => count($accounts),
                'active_accounts' => $activeAccounts,
                'total_packages' => count($packages),
                'active_packages' => $activePackages,
                'total_resellers' => count($resellers),
            ],
            'addons' => $addons,
            'theme_settings' => $theme_settings
        ]);
    }
}
