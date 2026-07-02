<?php

namespace Admin\Controllers;

use Core\Controller;

class SettingsController extends Controller
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

    protected function user() { return $this->auth->user(); }
    protected function theme() { return json_decode($this->user()->theme_settings ?? '{}', true); }

    protected function getSetting($key, $default = '')
    {
        $r = $this->db->table('automation_settings')->where('setting_key', $key)->first();
        return $r ? $r->setting_value : $default;
    }

    protected function setSetting($key, $value)
    {
        $r = $this->db->table('automation_settings')->where('setting_key', $key)->first();
        if ($r) $this->db->table('automation_settings')->where('setting_key', $key)->update(['setting_value' => $value]);
        else $this->db->table('automation_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $value]);
    }

    public function general()
    {
        $this->guard();
        return $this->view('admin.settings.general', [
            'user' => $this->user(), 'title' => 'General Settings', 'theme_settings' => $this->theme(),
            'hostname' => $this->getSetting('hostname', gethostname()),
            'timezone' => $this->getSetting('timezone', date_default_timezone_get()),
            'language' => $this->getSetting('language', 'en'),
        ]);
    }

    public function generalSave()
    {
        $this->guard();
        $this->setSetting('hostname', $this->request->post('hostname', ''));
        $this->setSetting('timezone', $this->request->post('timezone', ''));
        $this->setSetting('language', $this->request->post('language', 'en'));
        if ($this->request->post('timezone', '')) date_default_timezone_set($this->request->post('timezone', ''));
        $_SESSION['success_message'] = 'General settings saved.';
        $this->response->redirect('/admin/settings/general');
    }

    public function company()
    {
        $this->guard();
        return $this->view('admin.settings.company', [
            'user' => $this->user(), 'title' => 'Company Settings', 'theme_settings' => $this->theme(),
            'company_name' => $this->getSetting('company_name', 'Planet-Hosts'),
            'company_email' => $this->getSetting('company_email', 'admin@planet-hosts.com'),
            'company_phone' => $this->getSetting('company_phone', ''),
            'company_address' => $this->getSetting('company_address', ''),
            'company_website' => $this->getSetting('company_website', 'https://planet-hosts.com'),
        ]);
    }

    public function companySave()
    {
        $this->guard();
        foreach (['company_name','company_email','company_phone','company_address','company_website'] as $k) {
            $this->setSetting($k, $this->request->post($k, ''));
        }
        $_SESSION['success_message'] = 'Company settings saved.';
        $this->response->redirect('/admin/settings/company');
    }

    public function smtp()
    {
        $this->guard();
        return $this->view('admin.settings.smtp', [
            'user' => $this->user(), 'title' => 'SMTP Settings', 'theme_settings' => $this->theme(),
            'smtp_host' => $this->getSetting('smtp_host', ''),
            'smtp_port' => $this->getSetting('smtp_port', '587'),
            'smtp_username' => $this->getSetting('smtp_username', ''),
            'smtp_password' => $this->getSetting('smtp_password', ''),
            'smtp_from' => $this->getSetting('smtp_from', 'noreply@planet-hosts.com'),
            'smtp_encryption' => $this->getSetting('smtp_encryption', 'tls'),
            'smtp_enabled' => $this->getSetting('smtp_enabled', '0'),
        ]);
    }

    public function smtpSave()
    {
        $this->guard();
        foreach (['smtp_host','smtp_port','smtp_username','smtp_password','smtp_from','smtp_encryption','smtp_enabled'] as $k) {
            $this->setSetting($k, $this->request->post($k, ''));
        }
        $_SESSION['success_message'] = 'SMTP settings saved.';
        $this->response->redirect('/admin/settings/smtp');
    }

    public function security()
    {
        $this->guard();
        return $this->view('admin.settings.security', [
            'user' => $this->user(), 'title' => 'Security Settings', 'theme_settings' => $this->theme(),
            'min_password_length' => $this->getSetting('min_password_length', '8'),
            'max_login_attempts' => $this->getSetting('max_login_attempts', '5'),
            'session_timeout' => $this->getSetting('session_timeout', '30'),
            'require_ssl' => $this->getSetting('require_ssl', '0'),
            'twofactor_required' => $this->getSetting('twofactor_required', '0'),
            'notify_admin_email' => $this->getSetting('notify_admin_email', 'admin@planet-hosts.com'),
        ]);
    }

    public function securitySave()
    {
        $this->guard();
        foreach (['min_password_length','max_login_attempts','session_timeout','require_ssl','twofactor_required','notify_admin_email'] as $k) {
            $this->setSetting($k, $this->request->post($k, ''));
        }
        $_SESSION['success_message'] = 'Security settings saved.';
        $this->response->redirect('/admin/settings/security');
    }

    public function api()
    {
        $this->guard();
        $keys = $this->db->table('api_keys')->orderBy('created_at', 'DESC')->get() ?: [];
        $admins = $this->db->table('admins')->get() ?: [];
        $hostingUsers = $this->db->table('hosting_users')->get() ?: [];
        $roles = $this->db->table('user_roles')->get() ?: [];
        $roleMap = [];
        foreach ($roles as $r) $roleMap[$r->user_id] = $r->role;
        return $this->view('admin.settings.api', [
            'user' => $this->user(), 'title' => 'API Settings', 'theme_settings' => $this->theme(),
            'api_enabled' => $this->getSetting('api_enabled', '1'),
            'api_rate_limit_default' => $this->getSetting('api_rate_limit_default', '60'),
            'api_debug_mode' => $this->getSetting('api_debug_mode', '0'),
            'openai_api_key' => $this->getSetting('openai_api_key', ''),
            'keys' => $keys, 'admins' => $admins, 'hostingUsers' => $hostingUsers, 'roleMap' => $roleMap,
        ]);
    }

    public function apiSave()
    {
        $this->guard();
        $mode = $this->request->post('mode', 'settings');
        if ($mode === 'settings') {
            foreach (['api_enabled','api_rate_limit_default','api_debug_mode','openai_api_key'] as $k) {
                $this->setSetting($k, $this->request->post($k, ''));
            }
            // Generate free full-access key
            $keyName = $this->request->post('new_key_name', '');
            if ($keyName) {
                $raw = 'ph_' . bin2hex(random_bytes(16));
                $this->db->table('api_keys')->insertGetId([
                    'name' => $name, 'key_hash' => hash('sha256', $raw),
                    'permissions' => 'admin', 'rate_limit' => 120, 'is_active' => 1,
                ]);
                $_SESSION['success_message'] = "Key created!";
                $_SESSION['generated_key'] = $raw;
            } else {
                $_SESSION['success_message'] = 'API settings saved.';
            }
        } elseif ($mode === 'desktop') {
            $userId = $this->request->post('user_id', '');
            $userType = $this->request->post('user_type', 'admin');
            $name = $this->request->post('desktop_key_name', 'Desktop API Key');
            $permissions = $this->request->post('desktop_permissions', 'admin');
            $rateLimit = (int)$this->request->post('desktop_rate_limit', 120);
            if ($userId === '') {
                $_SESSION['error_message'] = 'Please select a user.';
                $this->response->redirect('/admin/settings/api');
                return;
            }
            $raw = 'ph_' . bin2hex(random_bytes(16));
            $this->db->table('api_keys')->insertGetId([
                'name' => $name, 'key_hash' => hash('sha256', $raw),
                'permissions' => $permissions, 'rate_limit' => $rateLimit, 'is_active' => 1,
                'user_id' => (int)$userId, 'user_type' => $userType,
            ]);
            $_SESSION['success_message'] = "Desktop API key created for user #{$userId}!";
            $_SESSION['generated_key'] = $raw;
        } elseif ($mode === 'root') {
            $raw = 'ph_' . bin2hex(random_bytes(16));
            $this->db->table('api_keys')->insertGetId([
                'name' => 'Root API Key', 'key_hash' => hash('sha256', $raw),
                'permissions' => 'admin', 'rate_limit' => 0, 'is_active' => 1,
                'user_id' => 0, 'user_type' => 'root',
            ]);
            $_SESSION['success_message'] = "Root API key created!";
            $_SESSION['generated_key'] = $raw;
        }
        $this->response->redirect('/admin/settings/api');
    }

    public function localization()
    {
        $this->guard();
        return $this->view('admin.settings.localization', [
            'user' => $this->user(), 'title' => 'Localization', 'theme_settings' => $this->theme(),
            'language' => $this->getSetting('language', 'en'),
            'date_format' => $this->getSetting('date_format', 'Y-m-d'),
            'time_format' => $this->getSetting('time_format', 'H:i:s'),
            'currency' => $this->getSetting('currency', 'USD'),
            'currency_symbol' => $this->getSetting('currency_symbol', '$'),
            'timezone' => $this->getSetting('timezone', 'UTC'),
        ]);
    }

    public function localizationSave()
    {
        $this->guard();
        foreach (['language','date_format','time_format','currency','currency_symbol','timezone'] as $k) {
            $this->setSetting($k, $this->request->post($k, ''));
        }
        $_SESSION['success_message'] = 'Localization settings saved.';
        $this->response->redirect('/admin/settings/localization');
    }

    public function index()
    {
        $this->guard();
        return $this->view('admin.settings.index', [
            'user' => $this->user(), 'title' => 'Admin Settings', 'theme_settings' => $this->theme(),
        ]);
    }
}
