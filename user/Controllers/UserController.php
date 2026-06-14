<?php

namespace User\Controllers;

use Core\Controller;

class UserController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;
    protected $package;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function loadUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if ($this->hostingUser) {
            $this->package = $this->db->table('hosting_packages')->where('id', $this->hostingUser->package_id)->first();
        }
        return $user;
    }

    public function index()
    {
        $u = $this->loadUser();
        $streams = $this->hostingUser ? ($this->db->table('radio_streams')->where('user_id', $this->hostingUser->id)->get() ?: []) : [];
        $diskTotal = $this->package->disk_space ?? 10;
        $diskUsed = 0;
        $diskPct = 0;
        if ($this->hostingUser) {
            $dir = '/home/' . $this->hostingUser->username;
            if (is_dir($dir)) $diskUsed = round(exec("du -sk {$dir} 2>/dev/null | awk '{print \$1}'") / 1024 / 1024, 2);
            $diskPct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0;
        }
        $notifications = [];
        if ($diskPct > 90) $notifications[] = ['type' => 'warning', 'msg' => "Disk usage at {$diskPct}%"];
        return $this->view('user.dashboard', [
            'user' => $u, 'hosting' => $this->hostingUser, 'package' => $this->package,
            'streams' => $streams, 'diskUsed' => $diskUsed, 'diskTotal' => $diskTotal, 'diskPct' => $diskPct,
            'notifications' => $notifications, 'title' => 'Dashboard'
        ]);
    }

    public function services() { $u = $this->loadUser(); return $this->view('user.services', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'My Services']); }
    public function usage() { $u = $this->loadUser(); return $this->view('user.usage', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Resource Usage']); }
    public function profile() { $u = $this->loadUser(); return $this->view('user.profile', ['user' => $u, 'hosting' => $this->hostingUser, 'package' => $this->package, 'title' => 'Profile']); }
    public function security() { $u = $this->loadUser(); return $this->view('user.security', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Security']); }
    public function support() { $u = $this->loadUser(); return $this->view('user.support', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Support']); }
    public function stats() { $u = $this->loadUser(); return $this->view('user.stats', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Statistics']); }
    public function tools() { $u = $this->loadUser(); return $this->view('user.tools', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Tools']); }
}
