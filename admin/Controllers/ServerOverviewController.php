<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\ServerManager;

class ServerOverviewController extends Controller
{
    protected $auth;
    protected $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        $user = $this->auth->user();
        $manager = new ServerManager();
        $serverStats = $manager->getStats();

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.server.index', [
            'user' => $user,
            'serverStats' => $serverStats,
            'theme_settings' => $theme_settings
        ]);
    }
}
