<?php

namespace Admin\Controllers;

use Core\Controller;

class FeatureListsController extends Controller
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
        $lists = $this->db->table('feature_lists')->orderBy('name', 'ASC')->get() ?: [];
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get() ?: [];
        return $this->view('admin.feature_lists.index', [
            'user' => $user,
            'lists' => $lists,
            'packages' => $packages,
            'title' => 'Feature Lists'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.feature_lists.create', [
            'user' => $user,
            'title' => 'Create Feature List'
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = trim($this->request->post('name', ''));
        if (!$name) {
            $_SESSION['error_message'] = 'Name is required.';
            $this->response->redirect('/admin/feature-lists/create');
            exit;
        }
        $this->db->table('feature_lists')->insertGetId([
            'name' => $name,
            'email_accounts' => (int)$this->request->post('email_accounts', -1),
            'ftp_accounts' => (int)$this->request->post('ftp_accounts', -1),
            'databases' => (int)$this->request->post('databases', -1),
            'database_users' => (int)$this->request->post('database_users', -1),
            'subdomains' => (int)$this->request->post('subdomains', -1),
            'parked_domains' => (int)$this->request->post('parked_domains', -1),
            'addon_domains' => (int)$this->request->post('addon_domains', -1),
            'cron_jobs' => (int)$this->request->post('cron_jobs', 1),
            'ssh_access' => (int)$this->request->post('ssh_access', 0),
            'ssl_allowed' => (int)$this->request->post('ssl_allowed', 1),
            'git_access' => (int)$this->request->post('git_access', 1),
            'nodejs' => (int)$this->request->post('nodejs', 0),
            'python' => (int)$this->request->post('python', 0),
            'ruby' => (int)$this->request->post('ruby', 0),
            'terminal' => (int)$this->request->post('terminal', 0),
            'backups' => (int)$this->request->post('backups', 1),
            'installer' => (int)$this->request->post('installer', 1),
        ]);
        $_SESSION['success_message'] = "Feature list '{$name}' created.";
        $this->response->redirect('/admin/feature-lists');
        exit;
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $list = $this->db->table('feature_lists')->where('id', $id)->first();
        if (!$list) { $this->response->redirect('/admin/feature-lists'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.feature_lists.edit', [
            'user' => $user, 'list' => $list,
            'title' => 'Edit Feature List'
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $list = $this->db->table('feature_lists')->where('id', $id)->first();
        if (!$list) { $this->response->redirect('/admin/feature-lists'); exit; }
        $name = trim($this->request->post('name', ''));
        if (!$name) {
            $_SESSION['error_message'] = 'Name is required.';
            $this->response->redirect('/admin/feature-lists/edit/' . $id);
            exit;
        }
        $this->db->table('feature_lists')->where('id', $id)->update([
            'name' => $name,
            'email_accounts' => (int)$this->request->post('email_accounts', -1),
            'ftp_accounts' => (int)$this->request->post('ftp_accounts', -1),
            'databases' => (int)$this->request->post('databases', -1),
            'database_users' => (int)$this->request->post('database_users', -1),
            'subdomains' => (int)$this->request->post('subdomains', -1),
            'parked_domains' => (int)$this->request->post('parked_domains', -1),
            'addon_domains' => (int)$this->request->post('addon_domains', -1),
            'cron_jobs' => (int)$this->request->post('cron_jobs', 1),
            'ssh_access' => (int)$this->request->post('ssh_access', 0),
            'ssl_allowed' => (int)$this->request->post('ssl_allowed', 1),
            'git_access' => (int)$this->request->post('git_access', 1),
            'nodejs' => (int)$this->request->post('nodejs', 0),
            'python' => (int)$this->request->post('python', 0),
            'ruby' => (int)$this->request->post('ruby', 0),
            'terminal' => (int)$this->request->post('terminal', 0),
            'backups' => (int)$this->request->post('backups', 1),
            'installer' => (int)$this->request->post('installer', 1),
            'is_active' => (int)$this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = "Feature list '{$name}' updated.";
        $this->response->redirect('/admin/feature-lists');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $list = $this->db->table('feature_lists')->where('id', $id)->first();
        if (!$list) { $this->response->redirect('/admin/feature-lists'); exit; }
        $packages = $this->db->table('hosting_packages')->where('feature_list_id', $id)->where('is_active', 1)->get() ?: [];
        if (!empty($packages)) {
            $_SESSION['error_message'] = "Cannot delete: feature list is used by " . count($packages) . " package(s).";
            $this->response->redirect('/admin/feature-lists');
            exit;
        }
        $this->db->table('feature_lists')->where('id', $id)->delete();
        $_SESSION['success_message'] = "Feature list deleted.";
        $this->response->redirect('/admin/feature-lists');
        exit;
    }
}
