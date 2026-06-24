<?php

namespace User\Controllers;

use Core\Controller;

class DjPanelController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function requireUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$this->hostingUser) { $this->response->redirect('/?login'); exit; }
        return $user;
    }

    public function index()
    {
        $this->requireUser();
        $userId = $this->hostingUser->id;

        $djs = $this->db->table('dj_accounts')->where('client_id', $userId)->get() ?: [];
        $schedules = $this->db->table('schedule')->where('client_id', $userId)->get() ?: [];
        $requests = $this->db->table('requests')->where('user_id', $userId)->get() ?: [];
        $settings = $this->db->table('settings')->where('client_id', $userId)->first();
        $artists = $this->db->table('inhouse_artists')->where('client_id', $userId)->get() ?: [];
        $ads = $this->db->table('advertisements')->where('client_id', $userId)->get() ?: [];

        return $this->view('user.dj_panel.index', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'djs' => $djs, 'schedules' => $schedules, 'requests' => $requests,
            'settings' => $settings, 'artists' => $artists, 'ads' => $ads,
            'title' => 'DJ Panel System',
        ]);
    }

    public function djs()
    {
        $this->requireUser();
        $djs = $this->db->table('dj_accounts')->where('client_id', $this->hostingUser->id)->get() ?: [];
        return $this->view('user.dj_panel.djs', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'djs' => $djs, 'title' => 'DJ Accounts',
        ]);
    }

    public function createDj()
    {
        $this->requireUser();
        $username = $this->request->post('username', '');
        $password = $this->request->post('password', '');
        $djName = $this->request->post('dj_name', '');
        $email = $this->request->post('email', '');
        $role = $this->request->post('role', 'dj');
        $userId = $this->hostingUser->id;

        if ($username && $password) {
            $existing = $this->db->table('dj_accounts')->where('dj_username', $username)->first();
            if ($existing) {
                $_SESSION['error'] = 'DJ username already exists.';
            } else {
                $this->db->table('dj_accounts')->insertGetId([
                    'dj_username' => $username,
                    'dj_password' => password_hash($password, PASSWORD_DEFAULT),
                    'client_id' => $userId,
                    'role' => $role,
                    'dj_name' => $djName,
                    'email' => $email,
                ]);
                // Also create about_dj entry
                $this->db->table('about_dj')->insertGetId([
                    'dj_username' => $username, 'dj_name' => $djName,
                    'email' => $email, 'client_id' => $userId,
                ]);
                $_SESSION['success'] = "DJ '{$username}' created.";
            }
        }
        $this->response->redirect('/user/radio/dj-panel/djs');
    }

    public function deleteDj($id)
    {
        $this->requireUser();
        $dj = $this->db->table('dj_accounts')->where('id', $id)->where('client_id', $this->hostingUser->id)->first();
        if ($dj) {
            $this->db->table('dj_accounts')->where('id', $id)->delete();
            $this->db->table('about_dj')->where('dj_username', $dj->dj_username)->delete();
            $this->db->table('schedule')->where('dj_username', $dj->dj_username)->delete();
            $_SESSION['success'] = "DJ deleted.";
        }
        $this->response->redirect('/user/radio/dj-panel/djs');
    }

    public function schedule()
    {
        $this->requireUser();
        $schedules = $this->db->table('schedule')->where('client_id', $this->hostingUser->id)->get() ?: [];
        $djs = $this->db->table('dj_accounts')->where('client_id', $this->hostingUser->id)->get() ?: [];
        return $this->view('user.dj_panel.schedule', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'schedules' => $schedules, 'djs' => $djs, 'title' => 'DJ Schedule',
        ]);
    }

    public function addSchedule()
    {
        $this->requireUser();
        $djUsername = $this->request->post('dj_username', '');
        $date = $this->request->post('date', '');
        $timeSlot = $this->request->post('time_slot', '');
        if ($djUsername && $date && $timeSlot) {
            $this->db->table('schedule')->insertGetId([
                'client_id' => $this->hostingUser->id,
                'dj_username' => $djUsername,
                'scheduled_date' => $date,
                'time_slot' => $timeSlot,
            ]);
            $_SESSION['success'] = 'Schedule added.';
        }
        $this->response->redirect('/user/radio/dj-panel/schedule');
    }

    public function deleteSchedule($id)
    {
        $this->requireUser();
        $this->db->table('schedule')->where('id', $id)->where('client_id', $this->hostingUser->id)->delete();
        $this->response->redirect('/user/radio/dj-panel/schedule');
    }

    public function requests()
    {
        $this->requireUser();
        $requests = $this->db->table('requests')->where('user_id', $this->hostingUser->id)->get() ?: [];
        return $this->view('user.dj_panel.requests', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'requests' => $requests, 'title' => 'Song Requests',
        ]);
    }

    public function deleteRequest($id)
    {
        $this->requireUser();
        $this->db->table('requests')->where('id', $id)->where('user_id', $this->hostingUser->id)->delete();
        $this->response->redirect('/user/radio/dj-panel/requests');
    }

    public function settings()
    {
        $this->requireUser();
        $settings = $this->db->table('settings')->where('client_id', $this->hostingUser->id)->first();
        $djs = $this->db->table('dj_accounts')->where('client_id', $this->hostingUser->id)->get() ?: [];
        return $this->view('user.dj_panel.settings', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'settings' => $settings, 'djs' => $djs, 'title' => 'Station Settings',
        ]);
    }

    public function saveSettings()
    {
        $this->requireUser();
        $userId = $this->hostingUser->id;
        $data = [
            'client_id' => $userId,
            'hostname' => $this->request->post('hostname', 'planet-hosts.info'),
            'port' => (int)$this->request->post('port', 8000),
            'domain_url' => $this->request->post('domain_url', ''),
            'banner_url' => $this->request->post('banner_url', ''),
            'online_color' => $this->request->post('online_color', '#00ff00'),
            'offline_color' => $this->request->post('offline_color', '#ff0000'),
            'admin_password' => $this->request->post('admin_password', ''),
            'station_password' => $this->request->post('station_password', ''),
        ];
        if ($this->request->post('site_name')) $data['site_name'] = $this->request->post('site_name');
        if ($this->request->post('custom_css')) $data['custom_css'] = $this->request->post('custom_css');
        if ($this->request->post('cbox_code')) $data['cbox_code'] = $this->request->post('cbox_code');

        $existing = $this->db->table('settings')->where('client_id', $userId)->first();
        if ($existing) {
            $this->db->table('settings')->where('id', $existing->id)->update($data);
        } else {
            $data['user_id'] = $userId;
            $this->db->table('settings')->insertGetId($data);
        }
        $_SESSION['success'] = 'Settings saved.';
        $this->response->redirect('/user/radio/dj-panel/settings');
    }

    public function artists()
    {
        $this->requireUser();
        $artists = $this->db->table('inhouse_artists')->where('client_id', $this->hostingUser->id)->get() ?: [];
        return $this->view('user.dj_panel.artists', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'artists' => $artists, 'title' => 'In-House Artists',
        ]);
    }

    public function addArtist()
    {
        $this->requireUser();
        $this->db->table('inhouse_artists')->insertGetId([
            'client_id' => $this->hostingUser->id,
            'name' => $this->request->post('name', ''),
            'website_url' => $this->request->post('website_url', ''),
        ]);
        $_SESSION['success'] = 'Artist added.';
        $this->response->redirect('/user/radio/dj-panel/artists');
    }

    public function deleteArtist($id)
    {
        $this->requireUser();
        $this->db->table('inhouse_artists')->where('id', $id)->where('client_id', $this->hostingUser->id)->delete();
        $this->response->redirect('/user/radio/dj-panel/artists');
    }

    public function ads()
    {
        $this->requireUser();
        $ads = $this->db->table('advertisements')->where('client_id', $this->hostingUser->id)->get() ?: [];
        return $this->view('user.dj_panel.ads', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'ads' => $ads, 'title' => 'Advertisements',
        ]);
    }

    public function createAd()
    {
        $this->requireUser();
        $this->db->table('advertisements')->insertGetId([
            'client_id' => $this->hostingUser->id,
            'name' => $this->request->post('name', ''),
            'banner_url' => $this->request->post('banner_url', ''),
            'link' => $this->request->post('link', ''),
        ]);
        $_SESSION['success'] = 'Advertisement added.';
        $this->response->redirect('/user/radio/dj-panel/ads');
    }

    public function deleteAd($id)
    {
        $this->requireUser();
        $this->db->table('advertisements')->where('id', $id)->where('client_id', $this->hostingUser->id)->delete();
        $this->response->redirect('/user/radio/dj-panel/ads');
    }
}
