<?php
namespace Admin\Controllers;

use Core\Controller;

class SupportStatusController extends Controller
{
    protected $auth, $response, $request, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
        $this->db = $app->get('db');
    }

    public function get()
    {
        // Return the current admin's personal status
        $status = 'online';
        if ($this->auth->check() && $this->auth->isAdmin()) {
            $adminId = $this->auth->user()->id;
            $key = "support_status_admin_{$adminId}";
            try {
                $row = $this->db->table('automation_settings')->where('setting_key', $key)->first();
                if ($row) $status = $row->setting_value;
            } catch (\Exception $e) {}
        }

        $this->response->json(['status' => $status]);
        $this->response->send();
        exit;
    }

    public function publicStatus()
    {
        $statuses = [];
        try {
            $rows = $this->db->table('automation_settings')->get() ?: [];
            foreach ($rows as $r) {
                if (str_starts_with($r->setting_key, 'support_status_admin_')) {
                    $statuses[] = $r->setting_value;
                }
            }
        } catch (\Exception $e) {}

        $agg = 'offline';
        if (in_array('online', $statuses)) $agg = 'online';
        elseif (in_array('away', $statuses)) $agg = 'away';

        $images = [];
        try {
            $imgSettings = $this->db->table('automation_settings')->where('setting_key', 'chat_image_online')->orWhere('setting_key', 'chat_image_offline')->orWhere('setting_key', 'chat_image_away')->get() ?: [];
            foreach ($imgSettings as $s) {
                $key = str_replace('chat_image_', '', $s->setting_key);
                if ($s->setting_value) $images[$key] = '/' . $s->setting_value;
            }
        } catch (\Exception $e) {}

        $this->response->json(['status' => $agg, 'images' => $images]);
        $this->response->send();
        exit;
    }

    public function set()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $status = $this->request->post('status', 'online');
        if (!in_array($status, ['online', 'away', 'offline'])) $status = 'online';
        $adminId = $this->auth->user()->id;
        $key = "support_status_admin_{$adminId}";
        try {
            $existing = $this->db->table('automation_settings')->where('setting_key', $key)->first();
            if ($existing) {
                $this->db->table('automation_settings')->where('setting_key', $key)->update(['setting_value' => $status]);
            } else {
                $this->db->table('automation_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $status]);
            }
        } catch (\Exception $e) {}
        $this->response->json(['ok' => true, 'status' => $status]);
        $this->response->send();
        exit;
    }
}
