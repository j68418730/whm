<?php
namespace User\Controllers;

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

    protected function loadHosting()
    {
        if (!$this->auth->check()) return null;
        $user = $this->auth->user();
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hosting && !empty($user->id)) $hosting = $this->db->table('hosting_users')->where('id', $user->id)->first();
        if (!$hosting && !empty($user->name)) $hosting = $this->db->table('hosting_users')->where('username', $user->name)->first();
        if (!$hosting) $hosting = $this->db->table('hosting_users')->orderBy('id', 'ASC')->first();
        return $hosting;
    }

    protected function sectionView($view, $title)
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        $hosting = $this->loadHosting();
        return $this->view($view, ['user' => $this->auth->user(), 'hosting' => $hosting, 'title' => $title]);
    }

    public function hosting() { return $this->sectionView('user.sections.hosting', 'Hosting'); }
    public function email() { return $this->sectionView('user.sections.email', 'Email'); }
    public function domains() { return $this->sectionView('user.sections.domains', 'Domains'); }
    public function billing() { return $this->sectionView('user.sections.billing', 'Billing'); }
    public function support() { return $this->sectionView('user.sections.support', 'Support'); }
    public function radio() { return $this->sectionView('user.sections.radio', 'Radio'); }
    public function games() { return $this->sectionView('user.sections.games', 'Games'); }
    public function builder() { return $this->sectionView('user.sections.builder', 'Website Builder'); }
    public function chat() { return $this->sectionView('user.sections.chat', 'Live Chat'); }
}
