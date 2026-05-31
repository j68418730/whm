<?php

namespace Admin\Controllers;

use Core\Controller;

class LicensingController extends Controller
{
    public function index()
    {
        $app = \Core\Application::getInstance();
        $auth = $app->get('auth');
        if (!$auth->check() || !$auth->isAdmin()) {
            $app->get('response')->redirect('/admin/login');
            exit;
        }

        return $this->view('admin.licensing.index', ['user' => $auth->user()]);
    }
}

