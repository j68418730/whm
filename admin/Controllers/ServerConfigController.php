<?php

namespace Admin\Controllers;

use Core\Controller;

class ServerConfigController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $hostname = trim(shell_exec("hostname 2>/dev/null") ?: 'localhost');
        $kernel = trim(shell_exec("uname -r 2>/dev/null") ?: '');
        $arch = trim(shell_exec("uname -m 2>/dev/null") ?: '');
        $timezone = trim(shell_exec("cat /etc/timezone 2>/dev/null") ?: date_default_timezone_get());
        return $this->view('admin.serverconfig.index', [
            'user' => $user, 'serverConfigStats' => [
                'hostname' => $hostname, 'kernel_version' => $kernel,
                'architecture' => $arch, 'timezone' => $timezone, 'services_enabled' => 0,
            ], 'theme_settings' => $theme_settings
        ]);
    }

    public function tweak()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $settings = $this->getTweakSettings();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.serverconfig.tweak', [
            'user' => $user, 'settings' => $settings, 'theme_settings' => $theme_settings, 'title' => 'Tweak Settings'
        ]);
    }

    public function tweakSave()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        // For now, just acknowledge the save
        $_SESSION['success_message'] = 'Settings saved. (Configuration persistence requires additional implementation)';
        $this->response->redirect('/admin/tweak');
        exit;
    }

    private function getTweakSettings()
    {
        return [
            'Compression' => [
                ['key' => 'compress_transfer', 'label' => 'Enable compression for transfers', 'type' => 'toggle', 'default' => true],
                ['key' => 'compress_interfaces', 'label' => 'Enable compression for interfaces', 'type' => 'toggle', 'default' => false],
            ],
            'Development' => [
                ['key' => 'debug_mode', 'label' => 'Debugging mode', 'type' => 'toggle', 'default' => false],
                ['key' => 'dev_features', 'label' => 'Development-related features', 'type' => 'toggle', 'default' => false],
            ],
            'Display' => [
                ['key' => 'interface_theme', 'label' => 'Interface appearance theme', 'type' => 'select', 'options' => ['dark' => 'Dark', 'light' => 'Light'], 'default' => 'dark'],
                ['key' => 'account_list_behavior', 'label' => 'Account list behavior', 'type' => 'text', 'default' => 'paginated'],
            ],
            'Domains' => [
                ['key' => 'allow_addon_domains', 'label' => 'Allow addon domains', 'type' => 'toggle', 'default' => true],
                ['key' => 'allow_subdomains', 'label' => 'Allow subdomains', 'type' => 'toggle', 'default' => true],
                ['key' => 'allow_aliases', 'label' => 'Allow aliases (parked domains)', 'type' => 'toggle', 'default' => true],
                ['key' => 'dns_automation', 'label' => 'Automatic DNS zone creation', 'type' => 'toggle', 'default' => true],
            ],
            'Logging' => [
                ['key' => 'system_logging', 'label' => 'Log system activity', 'type' => 'toggle', 'default' => true],
                ['key' => 'account_logging', 'label' => 'Log account activity', 'type' => 'toggle', 'default' => true],
                ['key' => 'log_retention_days', 'label' => 'Log retention (days)', 'type' => 'number', 'default' => 90],
            ],
            'Mail' => [
                ['key' => 'mail_delivery', 'label' => 'Enable mail delivery', 'type' => 'toggle', 'default' => true],
                ['key' => 'mailbox_creation', 'label' => 'Allow mailbox creation', 'type' => 'toggle', 'default' => true],
                ['key' => 'mail_quota_mb', 'label' => 'Default mailbox quota (MB)', 'type' => 'number', 'default' => 1000],
                ['key' => 'smtp_relay', 'label' => 'SMTP relay', 'type' => 'text', 'default' => 'localhost'],
            ],
            'Notifications' => [
                ['key' => 'server_alerts', 'label' => 'Server alert notifications', 'type' => 'toggle', 'default' => true],
                ['key' => 'admin_notifications', 'label' => 'Administrative notifications', 'type' => 'toggle', 'default' => true],
            ],
            'Packages' => [
                ['key' => 'default_package', 'label' => 'Default package for new accounts', 'type' => 'text', 'default' => 'default'],
                ['key' => 'account_creation_defaults', 'label' => 'Account creation defaults', 'type' => 'text', 'default' => 'standard'],
            ],
            'PHP' => [
                ['key' => 'php_default_version', 'label' => 'Default PHP version', 'type' => 'select', 'options' => ['8.2' => '8.2', '8.1' => '8.1', '8.0' => '8.0', '7.4' => '7.4'], 'default' => '8.2'],
                ['key' => 'php_memory_limit', 'label' => 'PHP memory limit', 'type' => 'text', 'default' => '128M'],
                ['key' => 'php_max_execution', 'label' => 'PHP max execution time', 'type' => 'number', 'default' => 30],
            ],
            'Redirection' => [
                ['key' => 'http_to_https', 'label' => 'Auto redirect HTTP to HTTPS', 'type' => 'toggle', 'default' => false],
                ['key' => 'service_urls', 'label' => 'Service URL redirects', 'type' => 'toggle', 'default' => true],
            ],
            'Security' => [
                ['key' => 'login_security', 'label' => 'Login security (max attempts)', 'type' => 'number', 'default' => 5],
                ['key' => 'cookie_validation', 'label' => 'Cookie validation', 'type' => 'toggle', 'default' => true],
                ['key' => 'password_min_length', 'label' => 'Minimum password length', 'type' => 'number', 'default' => 8],
                ['key' => 'ssl_required', 'label' => 'Require SSL for all services', 'type' => 'toggle', 'default' => false],
                ['key' => 'api_security', 'label' => 'API security keys', 'type' => 'toggle', 'default' => true],
            ],
            'Software' => [
                ['key' => 'auto_updates', 'label' => 'Automatic software updates', 'type' => 'toggle', 'default' => true],
                ['key' => 'integration_behavior', 'label' => 'Software integration behavior', 'type' => 'text', 'default' => 'auto'],
            ],
            'SQL' => [
                ['key' => 'mysql_tuning', 'label' => 'MySQL/MariaDB tuning profile', 'type' => 'select', 'options' => ['small' => 'Small (1GB RAM)', 'medium' => 'Medium (2-4GB)', 'large' => 'Large (8GB+)'], 'default' => 'small'],
                ['key' => 'db_default_charset', 'label' => 'Default database charset', 'type' => 'text', 'default' => 'utf8mb4'],
            ],
            'Stats and Logs' => [
                ['key' => 'bandwidth_processing', 'label' => 'Bandwidth processing', 'type' => 'toggle', 'default' => true],
                ['key' => 'stats_generation', 'label' => 'Statistics generation', 'type' => 'toggle', 'default' => true],
                ['key' => 'log_rotation', 'label' => 'Log rotation', 'type' => 'toggle', 'default' => true],
            ],
            'Stats Programs' => [
                ['key' => 'awstats_enabled', 'label' => 'AWStats', 'type' => 'toggle', 'default' => false],
                ['key' => 'webalizer_enabled', 'label' => 'Webalizer', 'type' => 'toggle', 'default' => false],
            ],
            'Status' => [
                ['key' => 'server_status_monitor', 'label' => 'Server status monitoring', 'type' => 'toggle', 'default' => true],
                ['key' => 'status_display_options', 'label' => 'Status display options', 'type' => 'select', 'options' => ['full' => 'Full', 'minimal' => 'Minimal'], 'default' => 'full'],
            ],
            'Support' => [
                ['key' => 'support_contact', 'label' => 'Support contact email', 'type' => 'text', 'default' => 'support@planet-hosts.com'],
                ['key' => 'support_config', 'label' => 'Support configuration', 'type' => 'text', 'default' => 'default'],
            ],
            'System' => [
                ['key' => 'quotas_enabled', 'label' => 'Enable disk quotas', 'type' => 'toggle', 'default' => false],
                ['key' => 'backup_schedule', 'label' => 'Backup schedule', 'type' => 'select', 'options' => ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'], 'default' => 'weekly'],
                ['key' => 'process_limits', 'label' => 'Process limits per account', 'type' => 'number', 'default' => 100],
                ['key' => 'dns_behavior', 'label' => 'DNS behavior', 'type' => 'text', 'default' => 'default'],
                ['key' => 'account_creation_defaults_sys', 'label' => 'Account creation system defaults', 'type' => 'text', 'default' => 'standard'],
                ['key' => 'shell_settings', 'label' => 'Shell access settings', 'type' => 'toggle', 'default' => false],
            ],
        ];
    }
}
