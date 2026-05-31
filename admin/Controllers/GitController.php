<?php

namespace Admin\Controllers;

use Core\Controller;

class GitController extends Controller
{
    public function index()
    {
        $app = \Core\Application::getInstance();
        $auth = $app->get('auth');
        if (!$auth->check() || !$auth->isAdmin()) {
            $app->get('response')->redirect('/admin/login');
            exit;
        }

        return $this->view('admin.git.index', ['user' => $auth->user()]);
    }
}

