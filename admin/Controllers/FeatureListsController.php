<?php

namespace Admin\Controllers;

use Core\Controller;

class FeatureListsController extends Controller
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
        $lists = $this->db->table('feature_lists')->orderBy('name', 'ASC')->get() ?: [];
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get() ?: [];
        return $this->view('admin.feature_lists.index', [
            'user' => $user,
            'lists' => $lists,
            'packages' => $packages,
            'title' => 'Feature Lists'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.feature_lists.create', [
            'user' => $user,
            'title' => 'Create Feature List'
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = trim($this->request->post('name', ''));
        if (!$name) {
            $_SESSION['error_message'] = 'Name is required.';
            $this->response->redirect('/admin/feature-lists/create');
            exit;
        }
        $this->db->table('feature_lists')->insertGetId([
            'name' => $name,
            'email_accounts' => (int)$this->request->post('email_accounts', -1),
            'ftp_accounts' => (int)$this->request->post('ftp_accounts', -1),
            'databases' => (int)$this->request->post('databases', -1),
            'database_users' => (int)$this->request->post('database_users', -1),
            'subdomains' => (int)$this->request->post('subdomains', -1),
            'parked_domains' => (int)$this->request->post('parked_domains', -1),
            'addon_domains' => (int)$this->request->post('addon_domains', -1),
            'cron_jobs' => (int)$this->request->post('cron_jobs', 0),
            'ssh_access' => (int)$this->request->post('ssh_access', 0),
            'ssl_allowed' => (int)$this->request->post('ssl_allowed', 0),
            'git_access' => (int)$this->request->post('git_access', 0),
            'nodejs' => (int)$this->request->post('nodejs', 0),
            'python' => (int)$this->request->post('python', 0),
            'ruby' => (int)$this->request->post('ruby', 0),
            'terminal' => (int)$this->request->post('terminal', 0),
            'backups' => (int)$this->request->post('backups', 0),
            'installer' => (int)$this->request->post('installer', 0),
            'chatbox' => (int)$this->request->post('chatbox', 0),
            'chatbox_voice' => (int)$this->request->post('chatbox_voice', 0),
            'chatbox_video' => (int)$this->request->post('chatbox_video', 0),
            'game' => (int)$this->request->post('game', 0),
            'radio' => (int)$this->request->post('radio', 0),
            'shoutcast' => (int)$this->request->post('shoutcast', 0),
            'dj_panel' => (int)$this->request->post('dj_panel', 0),
            'builder' => (int)$this->request->post('builder', 0),
            'ai_website_builder' => (int)$this->request->post('ai_website_builder', 0),
            'ai_assistant' => (int)$this->request->post('ai_assistant', 0),
            'plugin_marketplace' => (int)$this->request->post('plugin_marketplace', 0),
            'api_access' => (int)$this->request->post('api_access', 0),
            'webhooks' => (int)$this->request->post('webhooks', 0),
            'streaming_enabled' => (int)$this->request->post('streaming_enabled', 0),
            'shoutcast_v1' => (int)$this->request->post('shoutcast_v1', 0),
            'shoutcast_v2' => (int)$this->request->post('shoutcast_v2', 0),
            'icecast_enabled' => (int)$this->request->post('icecast_enabled', 0),
            'max_stations' => (int)$this->request->post('max_stations', 0),
            'max_djs' => (int)$this->request->post('max_djs', 0),
            'max_listeners' => (int)$this->request->post('max_listeners', 0),
            'max_bitrate' => (int)$this->request->post('max_bitrate', 0),
            'autodj' => (int)$this->request->post('autodj', 0),
            'ssl_streaming' => (int)$this->request->post('ssl_streaming', 0),
            'playlist_storage' => (int)$this->request->post('playlist_storage', 0),
            'statistics' => (int)$this->request->post('statistics', 0),
            'recording' => (int)$this->request->post('recording', 0),
            'song_requests' => (int)$this->request->post('song_requests', 0),
            'game_servers_enabled' => (int)$this->request->post('game_servers_enabled', 0),
            'max_game_servers' => (int)$this->request->post('max_game_servers', 0),
            'steamcmd' => (int)$this->request->post('steamcmd', 0),
            'workshop' => (int)$this->request->post('workshop', 0),
            'mod_support' => (int)$this->request->post('mod_support', 0),
            'scheduled_restarts' => (int)$this->request->post('scheduled_restarts', 0),
            'automatic_updates' => (int)$this->request->post('automatic_updates', 0),
            'game_backups' => (int)$this->request->post('game_backups', 0),
            'vps_enabled' => (int)$this->request->post('vps_enabled', 0),
            'vcpu' => (int)$this->request->post('vcpu', 0),
            'ram' => (int)$this->request->post('ram', 0),
            'vps_storage' => (int)$this->request->post('vps_storage', 0),
            'vps_bandwidth' => (int)$this->request->post('vps_bandwidth', 0),
            'snapshots' => (int)$this->request->post('snapshots', 0),
            'iso_mount' => (int)$this->request->post('iso_mount', 0),
            'vps_backups' => (int)$this->request->post('vps_backups', 0),
            'ipv4' => (int)$this->request->post('ipv4', 0),
            'ipv6' => (int)$this->request->post('ipv6', 0),
        ]);
        $_SESSION['success_message'] = "Feature list '{$name}' created.";
        $this->response->redirect('/admin/feature-lists');
        exit;
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $list = $this->db->table('feature_lists')->where('id', $id)->first();
        if (!$list) { $this->response->redirect('/admin/feature-lists'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.feature_lists.edit', [
            'user' => $user, 'list' => $list,
            'title' => 'Edit Feature List'
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $list = $this->db->table('feature_lists')->where('id', $id)->first();
        if (!$list) { $this->response->redirect('/admin/feature-lists'); exit; }
        $name = trim($this->request->post('name', ''));
        if (!$name) {
            $_SESSION['error_message'] = 'Name is required.';
            $this->response->redirect('/admin/feature-lists/edit/' . $id);
            exit;
        }
        $this->db->table('feature_lists')->where('id', $id)->update([
            'name' => $name,
            'email_accounts' => (int)$this->request->post('email_accounts', -1),
            'ftp_accounts' => (int)$this->request->post('ftp_accounts', -1),
            'databases' => (int)$this->request->post('databases', -1),
            'database_users' => (int)$this->request->post('database_users', -1),
            'subdomains' => (int)$this->request->post('subdomains', -1),
            'parked_domains' => (int)$this->request->post('parked_domains', -1),
            'addon_domains' => (int)$this->request->post('addon_domains', -1),
            'cron_jobs' => (int)$this->request->post('cron_jobs', 0),
            'ssh_access' => (int)$this->request->post('ssh_access', 0),
            'ssl_allowed' => (int)$this->request->post('ssl_allowed', 0),
            'git_access' => (int)$this->request->post('git_access', 0),
            'nodejs' => (int)$this->request->post('nodejs', 0),
            'python' => (int)$this->request->post('python', 0),
            'ruby' => (int)$this->request->post('ruby', 0),
            'terminal' => (int)$this->request->post('terminal', 0),
            'backups' => (int)$this->request->post('backups', 0),
            'installer' => (int)$this->request->post('installer', 0),
            'chatbox' => (int)$this->request->post('chatbox', 0),
            'chatbox_voice' => (int)$this->request->post('chatbox_voice', 0),
            'chatbox_video' => (int)$this->request->post('chatbox_video', 0),
            'game' => (int)$this->request->post('game', 0),
            'radio' => (int)$this->request->post('radio', 0),
            'shoutcast' => (int)$this->request->post('shoutcast', 0),
            'dj_panel' => (int)$this->request->post('dj_panel', 0),
            'builder' => (int)$this->request->post('builder', 0),
            'ai_website_builder' => (int)$this->request->post('ai_website_builder', 0),
            'ai_assistant' => (int)$this->request->post('ai_assistant', 0),
            'plugin_marketplace' => (int)$this->request->post('plugin_marketplace', 0),
            'api_access' => (int)$this->request->post('api_access', 0),
            'webhooks' => (int)$this->request->post('webhooks', 0),
            'streaming_enabled' => (int)$this->request->post('streaming_enabled', 0),
            'shoutcast_v1' => (int)$this->request->post('shoutcast_v1', 0),
            'shoutcast_v2' => (int)$this->request->post('shoutcast_v2', 0),
            'icecast_enabled' => (int)$this->request->post('icecast_enabled', 0),
            'max_stations' => (int)$this->request->post('max_stations', 0),
            'max_djs' => (int)$this->request->post('max_djs', 0),
            'max_listeners' => (int)$this->request->post('max_listeners', 0),
            'max_bitrate' => (int)$this->request->post('max_bitrate', 0),
            'autodj' => (int)$this->request->post('autodj', 0),
            'ssl_streaming' => (int)$this->request->post('ssl_streaming', 0),
            'playlist_storage' => (int)$this->request->post('playlist_storage', 0),
            'statistics' => (int)$this->request->post('statistics', 0),
            'recording' => (int)$this->request->post('recording', 0),
            'song_requests' => (int)$this->request->post('song_requests', 0),
            'game_servers_enabled' => (int)$this->request->post('game_servers_enabled', 0),
            'max_game_servers' => (int)$this->request->post('max_game_servers', 0),
            'steamcmd' => (int)$this->request->post('steamcmd', 0),
            'workshop' => (int)$this->request->post('workshop', 0),
            'mod_support' => (int)$this->request->post('mod_support', 0),
            'scheduled_restarts' => (int)$this->request->post('scheduled_restarts', 0),
            'automatic_updates' => (int)$this->request->post('automatic_updates', 0),
            'game_backups' => (int)$this->request->post('game_backups', 0),
            'vps_enabled' => (int)$this->request->post('vps_enabled', 0),
            'vcpu' => (int)$this->request->post('vcpu', 0),
            'ram' => (int)$this->request->post('ram', 0),
            'vps_storage' => (int)$this->request->post('vps_storage', 0),
            'vps_bandwidth' => (int)$this->request->post('vps_bandwidth', 0),
            'snapshots' => (int)$this->request->post('snapshots', 0),
            'iso_mount' => (int)$this->request->post('iso_mount', 0),
            'vps_backups' => (int)$this->request->post('vps_backups', 0),
            'ipv4' => (int)$this->request->post('ipv4', 0),
            'ipv6' => (int)$this->request->post('ipv6', 0),
            'is_active' => (int)$this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = "Feature list '{$name}' updated.";
        $this->response->redirect('/admin/feature-lists');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $list = $this->db->table('feature_lists')->where('id', $id)->first();
        if (!$list) { $this->response->redirect('/admin/feature-lists'); exit; }
        $packages = $this->db->table('hosting_packages')->where('feature_list_id', $id)->where('is_active', 1)->get() ?: [];
        if (!empty($packages)) {
            $_SESSION['error_message'] = "Cannot delete: feature list is used by " . count($packages) . " package(s).";
            $this->response->redirect('/admin/feature-lists');
            exit;
        }
        $this->db->table('feature_lists')->where('id', $id)->delete();
        $_SESSION['success_message'] = "Feature list deleted.";
        $this->response->redirect('/admin/feature-lists');
        exit;
    }
}