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

    public function hosting()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        return $this->view('user.sections.hosting', ['title' => 'Hosting']);
    }

    public function email()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        return $this->view('user.sections.email', ['title' => 'Email']);
    }

    public function domains()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        return $this->view('user.sections.domains', ['title' => 'Domains']);
    }

    public function billing()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        return $this->view('user.sections.billing', ['title' => 'Billing']);
    }

    public function support()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        return $this->view('user.sections.support', ['title' => 'Support']);
    }

    public function radio()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        return $this->view('user.sections.radio', ['title' => 'Radio']);
    }

    public function games()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        return $this->view('user.sections.games', ['title' => 'Games']);
    }

    public function builder()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        return $this->view('user.sections.builder', ['title' => 'Website Builder']);
    }
}
