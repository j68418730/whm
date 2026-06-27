<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\SslManager;

class UniversalSslController extends Controller
{
    protected $auth, $request, $response, $db, $sslManager;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->sslManager = new SslManager();
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login'); exit;
        }
    }

    protected function user() { return $this->auth->user(); }
    protected function theme() { return json_decode($this->user()->theme_settings ?? '{}', true); }

    public function index()
    {
        $this->guard();

        // Ensure auto_renew column exists
        try { $this->db->pdo()->exec("ALTER TABLE ssl_certs ADD COLUMN auto_renew TINYINT(1) DEFAULT 1 AFTER status"); } catch (\Exception $e) {}

        // Auto-populate cert expiry from filesystem for any missing
        $certs = $this->sslManager->getAllCertificates();
        foreach ($certs as $c) {
            $needsUpdate = false;
            if (!$c->expires_at || $c->expires_at === 'N/A') {
                $certPath = "/etc/letsencrypt/live/{$c->domain}/fullchain.pem";
                if (file_exists($certPath)) {
                    $expires = shell_exec("openssl x509 -enddate -noout -in {$certPath} 2>/dev/null | cut -d= -f2");
                    if ($expires) {
                        $c->expires_at = date('Y-m-d H:i:s', strtotime(trim($expires)));
                        $needsUpdate = true;
                    }
                }
            }
            if ($c->auto_renew === null) { $c->auto_renew = 1; $needsUpdate = true; }
            if ($needsUpdate) {
                try {
                    $this->db->table('ssl_certs')->where('id', $c->id)->update([
                        'expires_at' => $c->expires_at,
                        'auto_renew' => $c->auto_renew,
                    ]);
                } catch (\Exception $e) {}
            }
        }

        // Auto-detect services from running services + existing certs
        $services = $this->db->table('ssl_services')->get() ?: [];
        $profiles = $this->sslManager->detectServices();
        $detectedDomains = [];
        foreach ($certs as $c) $detectedDomains[] = $c->domain;

        // Auto-create services for detected services that have certs but no service record
        foreach ($profiles as $type => $p) {
            if (!($p['running'] ?? false)) continue;
            foreach ($detectedDomains as $domain) {
                $exists = false;
                foreach ($services as $s) { if ($s->service_type === $type && $s->domain === $domain) { $exists = true; break; } }
                if (!$exists) {
                    $port = $p['default_ports'][1] ?? 443;
                    try {
                        $this->db->table('ssl_services')->insertGetId([
                            'service_name' => $p['name'],
                            'service_type' => $type,
                            'domain' => $domain,
                            'port' => $port,
                            'ssl_enabled' => 1,
                            'status' => 'active',
                            'last_verified' => date('Y-m-d H:i:s'),
                        ]);
                    } catch (\Exception $e) {}
                }
            }
        }
        $services = $this->db->table('ssl_services')->get() ?: [];

        $logs = $this->sslManager->getLogs(20);
        $health = $this->sslManager->scanAllServices();
        $ports = $this->sslManager->scanListeningPorts();

        // Count stats
        $totalServices = count($services);
        $activeSsl = count(array_filter($services, fn($s) => $s->ssl_enabled == 1));
        $expiringSoon = 0;
        foreach ($certs as $c) {
            if ($c->expires_at && strtotime($c->expires_at) < time() + 86400 * 30) $expiringSoon++;
        }

        return $this->view('admin.ssl.universal', [
            'user' => $this->user(),
            'title' => 'Universal SSL Manager',
            'theme_settings' => $this->theme(),
            'certs' => $certs,
            'services' => $services,
            'profiles' => $profiles,
            'logs' => $logs,
            'health' => $health,
            'ports' => $ports,
            'totalServices' => $totalServices,
            'activeSsl' => $activeSsl,
            'expiringSoon' => $expiringSoon,
        ]);
    }

    public function configure()
    {
        $this->guard();

        $serviceType = $this->request->post('service_type', '');
        $domain = $this->request->post('domain', '');
        $port = (int)$this->request->post('port', 0);
        $sslMode = $this->request->post('ssl_mode', 'native');
        $email = $this->request->post('email', "admin@{$domain}");

        if (empty($domain)) {
            $_SESSION['error_message'] = 'Domain is required.';
            $this->response->redirect('/admin/ssl/universal');
            return;
        }

        // Get or create certificate first
        $cert = $this->sslManager->getCertificate($domain);
        if (!$cert) {
            $result = $this->sslManager->requestLetsEncrypt($domain, $email);
            if (!$result['success']) {
                $_SESSION['error_message'] = 'Failed to obtain certificate: ' . substr($result['output'] ?? '', 0, 300);
                $this->response->redirect('/admin/ssl/universal');
                return;
            }
        }

        // Configure the service
        $result = $this->sslManager->configureServiceSsl($serviceType, $domain, $port ?: null, $sslMode);

        if ($result['success']) {
            $_SESSION['success_message'] = "SSL configured for {$serviceType} on {$domain}";
        } else {
            $_SESSION['error_message'] = 'Configuration failed: ' . substr($result['output'] ?? '', 0, 300);
        }

        $this->response->redirect('/admin/ssl/universal');
    }

    public function renew()
    {
        $this->guard();

        $domain = $this->request->get('domain', '');
        if ($domain) {
            $result = $this->sslManager->requestLetsEncrypt($domain);
            if ($result['success']) {
                $_SESSION['success_message'] = "Certificate renewed for {$domain}";
            } else {
                $msg = substr($result['output'] ?? '', 0, 300);
                if (strpos($msg, 'certbot') === false && strpos($msg, 'command not found') !== false) {
                    $msg = 'certbot not installed. Run: apt install certbot python3-certbot-apache';
                }
                $_SESSION['error_message'] = 'Renewal failed: ' . $msg;
            }
        } else {
            $renewed = $this->sslManager->renewAll();
            if (!empty($renewed)) {
                $_SESSION['success_message'] = 'Renewed: ' . implode(', ', $renewed);
            } else {
                $_SESSION['success_message'] = 'No certificates needed renewal.';
            }
        }

        $this->response->redirect('/admin/ssl/universal');
    }

    public function toggleAutoRenew()
    {
        $this->guard();
        $domain = $this->request->post('domain', '');
        $enabled = (int)$this->request->post('enabled', 0);
        if ($domain) {
            try {
                $this->db->table('ssl_certs')->where('domain', $domain)->update(['auto_renew' => $enabled]);
                $this->sslManager->log('auto_renew_toggle', $domain, $enabled ? 'enabled' : 'disabled', '');
                $_SESSION['success_message'] = "Auto-renew " . ($enabled ? 'enabled' : 'disabled') . " for {$domain}";
            } catch (\Exception $e) {
                $_SESSION['error_message'] = 'Failed to update auto-renew setting.';
            }
        }
        $this->response->redirect('/admin/ssl/universal');
    }

    public function repair()
    {
        $this->guard();

        $serviceId = (int)$this->request->get('service_id', 0);
        if (!$serviceId) {
            $_SESSION['error_message'] = 'Service ID required.';
            $this->response->redirect('/admin/ssl/universal');
            return;
        }

        $result = $this->sslManager->autoRepair($serviceId);

        if ($result['success']) {
            $_SESSION['success_message'] = 'Service repaired successfully.';
        } else {
            $_SESSION['error_message'] = 'Repair failed: ' . ($result['error'] ?? substr($result['output'] ?? '', 0, 300));
        }

        $this->response->redirect('/admin/ssl/universal');
    }

    public function health()
    {
        $this->guard();

        $health = $this->sslManager->scanAllServices();
        $this->response->json([
            'services' => $health,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    public function scanPorts()
    {
        $this->guard();

        $ports = $this->sslManager->scanListeningPorts();
        $identified = [];
        foreach ($ports as $p) {
            $serviceType = $this->sslManager->identifyServiceOnPort($p['port']);
            if ($serviceType !== 'unknown') {
                $identified[] = [
                    'port' => $p['port'],
                    'service' => $serviceType,
                    'ssl_supported' => in_array($p['port'], [443, 2083, 2087, 8443, 990, 465, 993, 995]),
                ];
            }
        }

        $this->response->json(['ports' => $identified]);
    }

    public function deleteService()
    {
        $this->guard();

        $id = (int)$this->request->get('id', 0);
        if ($id) {
            $this->db->table('ssl_services')->where('id', $id)->delete();
            $_SESSION['success_message'] = 'SSL service record removed.';
        }

        $this->response->redirect('/admin/ssl/universal');
    }

    public function cron()
    {
        // Hourly cron: renew expiring certs, check services, auto-repair
        $log = [];

        // Renew certificates
        $renewed = $this->sslManager->renewAll();
        if (!empty($renewed)) $log[] = 'Renewed: ' . implode(', ', $renewed);

        // Scan all services
        $health = $this->sslManager->scanAllServices();
        foreach ($health as $h) {
            if ($h['status'] === 'missing_cert' || $h['status'] === 'handshake_failed') {
                $result = $this->sslManager->autoRepair($h['service_id']);
                $log[] = "Repair {$h['service_name']}: " . ($result['success'] ? 'OK' : 'FAILED');
            }
        }

        $this->sslManager->log('cron_run', '*', 'info', implode('; ', $log));
        echo "Cron complete: " . implode("\n", $log);
    }
}
