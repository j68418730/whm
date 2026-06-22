<?php

namespace Admin\Controllers;

use Core\Controller;

class ActivityLogController extends Controller
{
    protected $auth, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();

        $loginAttempts = [];
        try {
            $loginAttempts = $this->db->table('login_attempts')->orderBy('created_at', 'DESC')->limit(50)->get() ?: [];
        } catch (\Throwable $e) {
            $loginAttempts = [];
        }

        $cronJobs = [];
        try {
            $cronJobs = $this->db->table('cron_jobs')->orderBy('id', 'DESC')->limit(20)->get() ?: [];
        } catch (\Throwable $e) {
            $cronJobs = [];
        }

        $failedLogins = count(array_filter($loginAttempts, fn($row) => empty($row->success)));
        $successfulLogins = count(array_filter($loginAttempts, fn($row) => !empty($row->success)));

        return $this->view('admin.activity_log.index', [
            'user' => $user,
            'title' => 'Activity Log',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
            'loginAttempts' => $loginAttempts,
            'cronJobs' => $cronJobs,
            'failedLogins' => $failedLogins,
            'successfulLogins' => $successfulLogins,
        ]);
    }
}
