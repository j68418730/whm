<?php

namespace Plugins\WebsiteBuilder\Controllers\Admin;

use Core\Controller;

class WebsiteBuilderController extends Controller
{
    public function index()
    {
        $app = \Core\Application::getInstance();
        $auth = $app->get('auth');
        if (!$auth->check() || !$auth->isAdmin()) {
            $app->get('response')->redirect('/admin/login');
            exit;
        }
        return '<h1>Website Builder</h1><p>Website Builder plugin loaded. Enable in plugins config to activate.</p>';
    }
}
