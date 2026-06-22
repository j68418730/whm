<?php
namespace Admin\Controllers;

use Core\Controller;

class DomainController extends Controller
{
    protected $auth, $db, $request, $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->db = $app->get('db');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $domains = $this->db->table('hosting_domains')->orderBy('domain', 'ASC')->get() ?: [];
        $users = $this->db->table('hosting_users')->get() ?: [];
        $userMap = [];
        foreach ($users as $u) $userMap[$u->id] = $u;
        return $this->view('admin.domains.index', ['user' => $user, 'domains' => $domains, 'userMap' => $userMap, 'title' => 'Domain Manager']);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $domain = trim($this->request->post('domain', ''));
        $userId = (int)$this->request->post('user_id', 0);
        $isPrimary = (int)$this->request->post('is_primary', 0);
        if ($domain && $userId) {
            if ($isPrimary) $this->db->table('hosting_domains')->where('user_id', $userId)->update(['is_primary' => 0]);
            $this->db->table('hosting_domains')->insertGetId([
                'user_id' => $userId, 'domain' => $domain, 'status' => 'active', 'is_primary' => $isPrimary
            ]);
            $_SESSION['success_message'] = "Domain '{$domain}' added.";
        }
        $this->response->redirect('/admin/domains');
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('hosting_domains')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Domain deleted.';
        $this->response->redirect('/admin/domains');
    }

    public function lock($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $d = $this->db->table('hosting_domains')->where('id', (int)$id)->first();
        if ($d) {
            $new = $d->locked ? 0 : 1;
            $this->db->table('hosting_domains')->where('id', $id)->update(['locked' => $new]);
            $_SESSION['success_message'] = $new ? 'Domain locked.' : 'Domain unlocked.';
        }
        $this->response->redirect('/admin/domains');
    }

    public function ssl($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $d = $this->db->table('hosting_domains')->where('id', (int)$id)->first();
        if ($d) {
            $new = $d->ssl_enabled ? 0 : 1;
            $this->db->table('hosting_domains')->where('id', $id)->update(['ssl_enabled' => $new]);
            $_SESSION['success_message'] = $new ? 'SSL enabled.' : 'SSL disabled.';
        }
        $this->response->redirect('/admin/domains');
    }
}
