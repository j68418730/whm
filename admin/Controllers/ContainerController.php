<?php

namespace Admin\Controllers;

use Core\Controller;

class ContainerController extends Controller
{
    public function index()
    {
        $app = \Core\Application::getInstance();
        $auth = $app->get('auth');
        if (!$auth->check() || !$auth->isAdmin()) {
            $app->get('response')->redirect('/admin/login');
            exit;
        }

        return $this->view('admin.container.index', ['user' => $auth->user()]);
    }
}

