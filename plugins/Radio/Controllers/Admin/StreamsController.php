<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class StreamsController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->auth = \Core\Application::getInstance()->get('auth');
        $this->request = \Core\Application::getInstance()->get('request');
        $this->response = \Core\Application::getInstance()->get('response');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $streamsStats = [
            'total_streams' => rand(5, 25),
            'active_streams' => rand(3, 20),
            'suspended_streams' => rand(0, 5),
        ];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('Plugins.Radio.Views.admin.streams.index', [
            'user' => $user,
            'streamsStats' => $streamsStats,
            'theme_settings' => $theme_settings
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('Plugins.Radio.Views.admin.streams.create', [
            'user' => $user,
            'theme_settings' => $theme_settings
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $name = $this->request->post('name', '');
        $mount_point = $this->request->post('mount_point', '');
        $bitrate = $this->request->post('bitrate', '128kbps');
        $_SESSION['success_message'] = 'Stream created successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('Plugins.Radio.Views.admin.streams.edit', [
            'user' => $user,
            'streamId' => $id,
            'theme_settings' => $theme_settings
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $name = $this->request->post('name', '');
        $mount_point = $this->request->post('mount_point', '');
        $bitrate = $this->request->post('bitrate', '128kbps');
        $_SESSION['success_message'] = 'Stream updated successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $_SESSION['success_message'] = 'Stream deleted successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    public function restart($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $_SESSION['success_message'] = 'Stream restarted successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    public function suspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $_SESSION['success_message'] = 'Stream suspended successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    public function unsuspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $_SESSION['success_message'] = 'Stream unsuspended successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    public function clone($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $_SESSION['success_message'] = 'Stream cloned successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }
}
