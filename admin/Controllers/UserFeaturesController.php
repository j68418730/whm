<?php

namespace Admin\Controllers;

use Core\Controller;

class UserFeaturesController extends Controller
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
        $packages = $this->db->table('hosting_packages')->get() ?: [];
        $features = ['email','ftp','cron','ssh','ssl','databases','dns','git'];
        $featureLabels = ['email'=>'Email Accounts','ftp'=>'FTP Accounts','cron'=>'Cron Jobs','ssh'=>'SSH Access','ssl'=>'SSL/TLS','databases'=>'Databases','dns'=>'DNS Management','git'=>'Git Deployment'];
        return $this->view('admin.userfeatures.index', [
            'user' => $user, 'title' => 'Feature Manager', 'packages' => $packages,
            'features' => $features, 'featureLabels' => $featureLabels,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }
}
