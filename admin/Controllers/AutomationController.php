<?php

namespace Admin\Controllers;

use Core\Controller;

class AutomationController extends Controller
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

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $settings = [];
        $rows = $this->db->table('automation_settings')->get() ?: [];
        foreach ($rows as $r) { $settings[$r->setting_key] = $r->setting_value; }
        return $this->view('admin.automation.index', [
            'user' => $user, 'title' => 'Automation', 'settings' => $settings,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function save()
    {
        $this->guard();
        $keys = [
            'auto_provision_enabled','auto_suspend_enabled','auto_suspend_days',
            'auto_terminate_enabled','auto_terminate_days',
            'email_notifications_enabled','smtp_host','smtp_port','smtp_username','smtp_password','smtp_from',
            'sms_notifications_enabled','sms_provider','sms_api_key','sms_from',
            'notify_admin_email','notify_admin_sms',
        ];
        foreach ($keys as $k) {
            $v = $this->request->post($k, '');
            $existing = $this->db->table('automation_settings')->where('setting_key', $k)->first();
            if ($existing) {
                $this->db->table('automation_settings')->where('setting_key', $k)->update(['setting_value' => $v]);
            } else {
                $this->db->table('automation_settings')->insertGetId(['setting_key' => $k, 'setting_value' => $v]);
            }
        }
        $_SESSION['success_message'] = 'Automation settings saved.';
        $this->response->redirect('/admin/automation');
    }

    public function run()
    {
        $this->guard();
        $output = $this->runAutomation();
        $_SESSION['success_message'] = nl2br(htmlspecialchars($output));
        $this->response->redirect('/admin/automation');
    }

    public function runAutomation()
    {
        $settings = [];
        $rows = $this->db->table('automation_settings')->get() ?: [];
        foreach ($rows as $r) { $settings[$r->setting_key] = $r->setting_value; }
        $log = [];

        // Auto Provision: find pending orders and create accounts
        if (!empty($settings['auto_provision_enabled'])) {
            $orders = $this->db->table('billing_orders')->where('status', 'pending')->get() ?: [];
            foreach ($orders as $o) {
                $user = $this->db->table('hosting_users')->where('id', $o->user_id)->first();
                if ($user) {
                    $this->db->table('billing_orders')->where('id', $o->id)->update(['status' => 'active']);
                    $this->db->table('hosting_users')->where('id', $o->user_id)->update(['status' => 'active']);
                    $log[] = "Provisioned account for user #{$o->user_id} (Order #{$o->id})";
                }
            }
            if (empty($orders)) $log[] = 'Auto Provision: No pending orders.';
        }

        // Auto Suspend: suspend accounts past due
        if (!empty($settings['auto_suspend_enabled'])) {
            $days = (int)($settings['auto_suspend_days'] ?? 7);
            $cutoff = date('Y-m-d', strtotime("-{$days} days"));
            $overdue = $this->db->table('invoices')->where('status', 'overdue')->get() ?: [];
            $suspended = [];
            foreach ($overdue as $inv) {
                if ($inv->due_date < $cutoff && !in_array($inv->user_id, $suspended)) {
                    $this->db->table('hosting_users')->where('id', $inv->user_id)->update(['status' => 'suspended']);
                    $suspended[] = $inv->user_id;
                    $log[] = "Suspended user #{$inv->user_id} (overdue invoice #{$inv->invoice_number})";
                }
            }
            if (empty($suspended)) $log[] = 'Auto Suspend: No accounts to suspend.';
        }

        // Auto Terminate
        if (!empty($settings['auto_terminate_enabled'])) {
            $days = (int)($settings['auto_terminate_days'] ?? 30);
            $cutoff = date('Y-m-d', strtotime("-{$days} days"));
            $suspended = $this->db->table('hosting_users')->where('status', 'suspended')->get() ?: [];
            foreach ($suspended as $u) {
                if ($u->updated_at && substr($u->updated_at, 0, 10) < $cutoff) {
                    $this->db->table('hosting_users')->where('id', $u->id)->update(['status' => 'terminated']);
                    $log[] = "Terminated user #{$u->id} ({$u->username}) - suspended > {$days} days";
                }
            }
        }

        return implode("\n", $log) ?: 'Automation run completed. No actions taken.';
    }
}
