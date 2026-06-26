<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\HostnameManager;

class HostnameController extends Controller
{
    protected $auth, $request, $response, $db, $hostnameManager;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->hostnameManager = new HostnameManager();
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
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
        if ($r) {
            $this->db->table('automation_settings')->where('setting_key', $key)->update(['setting_value' => $value]);
        } else {
            $this->db->table('automation_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    public function index()
    {
        $this->guard();
        $currentHostname = $this->hostnameManager->getCurrentHostname();
        $savedHostname = $this->getSetting('hostname', $currentHostname);
        $panelUrl = $this->getSetting('panel_url', "http://{$currentHostname}");
        $serverIp = $this->hostnameManager->getServerIp();
        $publicIp = $this->hostnameManager->getPublicIp();
        $ns = $this->hostnameManager->getNameservers();
        $sslStatus = $this->hostnameManager->getSslStatus($savedHostname);
        $health = $this->hostnameManager->healthCheck($savedHostname);
        $adminEmail = $this->getSetting('admin_email', "admin@{$savedHostname}");

        return $this->view('admin.hostname.index', [
            'user' => $this->user(),
            'title' => 'Server Hostname',
            'theme_settings' => $this->theme(),
            'currentHostname' => $currentHostname,
            'savedHostname' => $savedHostname,
            'panelUrl' => $panelUrl,
            'serverIp' => $serverIp,
            'publicIp' => $publicIp,
            'ns1' => $ns['ns1'],
            'ns2' => $ns['ns2'],
            'sslStatus' => $sslStatus,
            'health' => $health,
            'adminEmail' => $adminEmail,
        ]);
    }

    public function save()
    {
        $this->guard();

        $hostname = trim($this->request->post('hostname', ''));
        $ns1 = trim($this->request->post('ns1', ''));
        $ns2 = trim($this->request->post('ns2', ''));
        $adminEmail = trim($this->request->post('admin_email', ''));
        $autoSsl = $this->request->post('auto_ssl', '0');

        if (empty($hostname)) {
            $_SESSION['error_message'] = 'Hostname is required.';
            $this->response->redirect('/admin/hostname');
            return;
        }

        $errors = $this->hostnameManager->validateHostname($hostname);
        if (!empty($errors)) {
            $_SESSION['validation_warnings'] = $errors;
        }

        $log = [];
        $log[] = '=== Saving hostname: ' . $hostname . ' ===';

        $log[] = '--- Updating system hostname ---';
        $log[] = $this->hostnameManager->updateSystemHostname($hostname);

        $log[] = '--- Removing old vhosts ---';
        $this->hostnameManager->removeOldPanelVhosts();

        $log[] = '--- Writing panel vhost ---';
        $vhostPath = $this->hostnameManager->writePanelVhost($hostname);
        $log[] = "Vhost: $vhostPath";

        shell_exec('apache2ctl configtest 2>/dev/null || apachectl configtest 2>/dev/null');
        shell_exec('systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null');
        $log[] = 'Apache reloaded';

        if ($autoSsl === '1') {
            $log[] = '--- Requesting SSL ---';
            $email = $adminEmail ?: "admin@{$hostname}";
            $sslOutput = $this->hostnameManager->requestSsl($hostname, $email);
            $log[] = $sslOutput ?: 'SSL request sent';

            if (file_exists("/etc/letsencrypt/live/{$hostname}/fullchain.pem")) {
                $this->hostnameManager->writePanelSslVhost($hostname);
                shell_exec('systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null');
                $log[] = 'SSL vhost written, Apache reloaded';
            } else {
                $log[] = 'SSL cert not yet available. DNS may need propagation.';
            }
        }

        $this->setSetting('hostname', $hostname);
        $this->setSetting('panel_url', "https://{$hostname}");
        $this->setSetting('admin_email', $adminEmail);
        if ($ns1) $this->setSetting('ns1', $ns1);
        if ($ns2) $this->setSetting('ns2', $ns2);

        $logDir = BASE_PATH . '/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        file_put_contents("{$logDir}/hostname-save.log", implode("\n", $log) . "\n", FILE_APPEND);

        $_SESSION['success_message'] = "Hostname saved: {$hostname}";
        if (!empty($errors)) {
            $_SESSION['success_message'] .= ' (with warnings)';
        }
        $this->response->redirect('/admin/hostname');
    }

    public function rebuild()
    {
        $this->guard();

        $hostname = trim($this->request->post('hostname', ''));
        $email = trim($this->request->post('admin_email', ''));

        if (empty($hostname)) {
            $hostname = $this->getSetting('hostname', $this->hostnameManager->getCurrentHostname());
        }
        if (empty($email)) {
            $email = $this->getSetting('admin_email', "admin@{$hostname}");
        }

        $log = $this->hostnameManager->rebuildAll($hostname, $email);

        $_SESSION['success_message'] = 'Hostname configuration rebuilt successfully.';
        $_SESSION['rebuild_log'] = $log;
        $this->response->redirect('/admin/hostname');
    }

    public function health()
    {
        if ($this->auth->check() && $this->auth->isAdmin()) {
            $hostname = $this->getSetting('hostname', $this->hostnameManager->getCurrentHostname());
            $health = $this->hostnameManager->healthCheck($hostname);
            return $this->response->json($health);
        } else {
            return $this->response->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function autossl()
    {
        $this->guard();

        $hostname = $this->getSetting('hostname', $this->hostnameManager->getCurrentHostname());
        $email = $this->getSetting('admin_email', "admin@{$hostname}");

        $output = $this->hostnameManager->requestSsl($hostname, $email);

        if (file_exists("/etc/letsencrypt/live/{$hostname}/fullchain.pem")) {
            $this->hostnameManager->writePanelSslVhost($hostname);
            shell_exec('systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null');
            $_SESSION['success_message'] = "SSL certificate issued for {$hostname}";
        } else {
            $_SESSION['error_message'] = "SSL issuance attempted. Check DNS propagation. Output: " . substr($output ?: '', 0, 500);
        }

        $this->response->redirect('/admin/hostname');
    }
}
