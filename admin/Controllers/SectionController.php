<?php
namespace Admin\Controllers;

use Core\Controller;

class SectionController extends Controller
{
    protected $auth, $response, $request, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
        $this->db = $app->get('db');
    }

    public function accounts()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.accounts', [
            'user' => $user,
            'total_accounts' => $this->db->table('hosting_users')->count() ?: 0,
            'total_packages' => $this->db->table('hosting_packages')->count() ?: 0,
            'total_resellers' => $this->db->table('resellers')->count() ?: 0,
            'total_admins' => $this->db->table('admins')->count() ?: 0,
            'title' => 'Accounts',
        ]);
    }

    public function hosting()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.hosting', ['user' => $user, 'title' => 'Hosting']);
    }

    public function billing()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.billing', ['user' => $user, 'title' => 'Billing']);
    }

    public function support()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.support', ['user' => $user, 'title' => 'Support']);
    }

    public function radio()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.radio', ['user' => $user, 'title' => 'Radio Hosting']);
    }

    public function games()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.games', ['user' => $user, 'title' => 'Game Servers']);
    }

    public function builder()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.builder', ['user' => $user, 'title' => 'Website Builder']);
    }

    public function domains()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.domains', ['user' => $user, 'title' => 'Domains']);
    }

    public function security()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.security', ['user' => $user, 'title' => 'Security']);
    }

    public function system()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.system', ['user' => $user, 'title' => 'System']);
    }
}
