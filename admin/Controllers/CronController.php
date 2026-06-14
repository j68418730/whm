<?php

namespace Admin\Controllers;

use Core\Controller;

class CronController extends Controller
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
        $crons = $this->db->table('cron_jobs')->get() ?: [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.cron.index', [
            'user' => $user, 'crons' => $crons,
            'theme_settings' => $theme_settings, 'title' => 'Cron Manager'
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('cron_jobs')->insertGetId([
            'user_id' => 0, 'minute' => $this->request->post('minute', '*'),
            'hour' => $this->request->post('hour', '*'), 'day' => $this->request->post('day', '*'),
            'month' => $this->request->post('month', '*'), 'weekday' => $this->request->post('weekday', '*'),
            'command' => $this->request->post('command', ''),
        ]);
        $_SESSION['success_message'] = 'Cron job created.';
        $this->response->redirect('/admin/cron');
        exit;
    }

    public function destroy($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('cron_jobs')->where('id', $id)->delete();
        $this->response->redirect('/admin/cron');
        exit;
    }
}
