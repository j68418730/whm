<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class DjController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $djs = $this->db->table('radio_djs')->get() ?: [];
        $streams = $this->db->table('radio_streams')->get() ?: [];
        return $this->view('Plugins.Radio.Views.admin.djs.index', [
            'user' => $user, 'djs' => $djs, 'streams' => $streams,
            'djStats' => ['total_djs' => count($djs), 'active_djs' => count(array_filter($djs, fn($d) => $d->status === 'active'))],
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'DJ Accounts'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $streams = $this->db->table('radio_streams')->get() ?: [];
        return $this->view('Plugins.Radio.Views.admin.djs.index', [
            'user' => $user, 'streams' => $streams, 'showCreateForm' => true,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Create DJ'
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $username = $this->request->post('username', '');
        $password = $this->request->post('password', bin2hex(random_bytes(6)));
        
        // Insert DJ with primary stream
        $djId = $this->db->table('radio_djs')->insertGetId([
            'stream_id' => (int)$this->request->post('stream_id', 0),
            'username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $this->request->post('name', $username), 'email' => $this->request->post('email', ''),
        ]);
        
        // Store multiple stream assignments (many-to-many relationship)
        $streamIds = $this->request->post('stream_ids', []);
        if (!empty($streamIds)) {
            $assignments = [];
            foreach ($streamIds as $streamId) {
                if (!empty($streamId) && (int)$streamId > 0) {
                    $assignments[] = [
                        'dj_id' => $djId,
                        'stream_id' => (int)$streamId,
                        'assigned_by' => $this->auth->user()->id,
                    ];
                }
            }
            if (!empty($assignments)) {
                $this->db->table('radio_dj_streams')->insert($assignments);
            }
        }
        
        $_SESSION['success_message'] = "DJ $username created. Password: $password";
        $this->response->redirect('/admin/djs');
    }
    
    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $dj = $this->db->table('radio_djs')->where('id', $id)->first();
        $streams = $this->db->table('radio_streams')->get() ?: [];
        
        // Get all stream assignments for this DJ
        $assignedStreams = $this->db->table('radio_dj_streams')
            ->where('dj_id', $id)
            ->get() ?: [];
        
        return $this->view('Plugins.Radio.Views.admin.djs.index', [
            'user' => $user, 'dj' => $dj, 'streams' => $streams, 'editId' => $id,
            'assignedStreams' => $assignedStreams,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Edit DJ'
        ]);
    }
    
    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $data = ['name' => $this->request->post('name', ''), 'email' => $this->request->post('email', ''), 'status' => $this->request->post('status', 'active')];
        $pw = $this->request->post('password', '');
        if ($pw) $data['password'] = password_hash($pw, PASSWORD_DEFAULT);
        
        $this->db->table('radio_djs')->where('id', $id)->update($data);
        
        // Update stream assignments (many-to-many)
        $streamIds = $this->request->post('stream_ids', []);
        
        // Remove all existing assignments
        $this->db->table('radio_dj_streams')->where('dj_id', $id)->delete();
        
        // Insert new assignments
        if (!empty($streamIds)) {
            $assignments = [];
            foreach ($streamIds as $streamId) {
                if (!empty($streamId) && (int)$streamId > 0) {
                    $assignments[] = [
                        'dj_id' => $id,
                        'stream_id' => (int)$streamId,
                        'assigned_by' => $this->auth->user()->id,
                    ];
                }
            }
            if (!empty($assignments)) {
                $this->db->table('radio_dj_streams')->insert($assignments);
            }
        }
        
        $_SESSION['success_message'] = 'DJ updated.';
        $this->response->redirect('/admin/djs');
    }

    public function remove($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('radio_djs')->where('id', $id)->delete();
        $this->response->redirect('/admin/djs');
    }
}
