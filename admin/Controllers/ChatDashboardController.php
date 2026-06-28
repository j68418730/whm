<?php
namespace Admin\Controllers;

use Core\Controller;

class ChatDashboardController extends Controller
{
    protected $auth, $response;

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
            return;
        }
        $this->response->redirect('/admin/livechat');
    }
}
