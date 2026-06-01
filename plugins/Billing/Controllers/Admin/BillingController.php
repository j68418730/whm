<?php

namespace Plugins\Billing\Controllers\Admin;

use Core\Controller;

class BillingController extends Controller
{
    public function index()
    {
        $app = \Core\Application::getInstance();
        $auth = $app->get('auth');
        if (!$auth->check() || !$auth->isAdmin()) {
            $app->get('response')->redirect('/admin/login');
            exit;
        }
        return '<h1>Billing</h1><p>Billing plugin loaded. Enable in plugins config to activate.</p>';
    }
}
