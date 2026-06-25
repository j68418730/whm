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

        $certs = $this->sslManager->getAllCertificates();
        $services = $this->db->table('ssl_services')->get() ?: [];
        $profiles = $this->sslManager->detectServices();
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
                $_SESSION['error_message'] = 'Renewal failed: ' . substr($result['output'] ?? '', 0, 300);
            }
        } else {
            $renewed = $this->sslManager->renewAll();
            $_SESSION['success_message'] = 'Renewal complete: ' . implode(', ', $renewed) ?: 'No certs needed renewal.';
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
