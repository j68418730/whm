<?php

namespace Admin\Controllers;

use Core\Controller;

class LicensingController extends Controller
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
        $license = new \Core\License(BASE_PATH);
        $status = $license->verify();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $features = [];
        $allChecks = ['accounts','packages','dns','email','ftp','databases','backups','ssl','domains','radio','streams','autodj',
                       'shared_hosting','radio_hosting','streaming_icecast','streaming_shoutcast_v1','streaming_shoutcast_v2',
                       'streaming_autodj','email_hosting','ftp_hosting','database_hosting','ssl_auto','backups','monitoring',
                       'api_access','desktop_app','reseller_hosting','vps_hosting','game_hosting','dns_clustering',
                       'multi_server','white_label','ssl_wildcard','marketplace','streaming_rtmp','streaming_rtsp','streaming_relay'];
        foreach ($allChecks as $f) {
            $features[$f] = $license->hasFeature($f);
        }

        // Get activation history
        $activations = [];
        try {
            $activations = $this->db->table('license_activations')->orderBy('created_at', 'DESC')->limit(10)->get() ?: [];
        } catch (\Exception $e) {}

        return $this->view('admin.licensing.index', [
            'user' => $user, 'title' => 'Licensing', 'theme_settings' => $theme_settings,
            'status' => $status, 'features' => $features, 'activations' => $activations,
            'trial_days_left' => $license->getTrialDaysLeft(),
            'grace_days_left' => $license->getGraceDaysLeft(),
            'license_types' => ['trial','monthly','yearly','lifetime','internal','reseller','enterprise'],
            'license' => $license,
        ]);
    }

    public function activate()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }

        $licenseKey = $this->request->post('license_key', '');
        $customerEmail = $this->request->post('customer_email', '');
        $companyName = $this->request->post('company_name', '');
        $action = $this->request->post('action', '');

        if ($action === 'start_trial') {
            $lic = new \Core\License(BASE_PATH);
            $lic->startTrial();
            $_SESSION['success_message'] = '30-day trial started!';
            $this->response->redirect('/admin/licensing');
            exit;
        }

        if ($action === 'online_activate' && $licenseKey) {
            $result = $this->activateOnline($licenseKey, $customerEmail, $companyName);
            if ($result['success']) {
                $this->saveLicenseFile($licenseKey, $result['data']);
                $this->logActivation($licenseKey, $result['data']);
                $_SESSION['success_message'] = 'License activated successfully! Type: ' . ($result['data']['type'] ?? 'full');
            } else {
                $_SESSION['error_message'] = 'Activation failed: ' . ($result['error'] ?? 'Unknown error');
            }
            $this->response->redirect('/admin/licensing');
            exit;
        }

        $_SESSION['error_message'] = 'No action specified.';
        $this->response->redirect('/admin/licensing');
    }

    public function generate()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $generatedKey = '';
        $privateKeyFile = BASE_PATH . '/config/license_private.pem';

        if ($_POST && isset($_POST['licensee'])) {
            $licensee = $this->request->post('licensee', 'Customer');
            $licenseId = $this->request->post('license_id', 'PH-' . date('Y') . '-' . str_pad(rand(1,9999),4,'0',STR_PAD_LEFT));
            $expiry = $this->request->post('expiry', 'never');
            $type = $this->request->post('type', 'full');
            $validTypes = ['trial','monthly','yearly','lifetime','internal','reseller','enterprise','hosting','icecast','full'];
            if (!in_array($type, $validTypes)) $type = 'full';

            if (is_file($privateKeyFile)) {
                $payload = json_encode([
                    'license_id' => $licenseId, 'licensee' => $licensee,
                    'issued' => date('Y-m-d'), 'expiry' => $expiry,
                    'product' => 'Planet-Hosts WHM Panel', 'version' => '1.0.0',
                    'type' => $type,
                ], JSON_PRETTY_PRINT);

                $privKey = file_get_contents($privateKeyFile);
                openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
                $sigB64 = base64_encode($signature);
                $generatedKey = "-----BEGIN PLANET HOSTS LICENSE-----\n";
                $generatedKey .= chunk_split($sigB64, 64, "\n");
                $generatedKey .= "-----BEGIN LICENSE DATA-----\n";
                $generatedKey .= $payload . "\n";
                $generatedKey .= "-----END LICENSE DATA-----\n";
                $generatedKey .= "-----END PLANET HOSTS LICENSE-----\n";
                $_SESSION['success_message'] = "License generated for {$licensee} ({$type})";
            } else {
                $_SESSION['success_message'] = 'Private key not found on this server.';
            }
        }

        return $this->view('admin.licensing.generate', [
            'user' => $user, 'title' => 'Generate License', 'theme_settings' => $theme_settings,
            'generatedKey' => $generatedKey, 'hasPrivateKey' => is_file($privateKeyFile),
        ]);
    }

    public function upload()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if (isset($_FILES['license_file']) && $_FILES['license_file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['license_file']['tmp_name'], BASE_PATH . '/license.key');
            @chmod(BASE_PATH . '/license.key', 0644);
            $_SESSION['success_message'] = 'License key uploaded and saved.';
        } elseif ($this->request->post('license_content')) {
            file_put_contents(BASE_PATH . '/license.key', $this->request->post('license_content'));
            @chmod(BASE_PATH . '/license.key', 0644);
            $_SESSION['success_message'] = 'License key saved.';
        } else {
            $_SESSION['success_message'] = 'No license file provided.';
        }
        $this->response->redirect('/admin/licensing');
    }

    public function refresh()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        // Force re-verify
        $lic = new \Core\License(BASE_PATH);
        $result = $lic->verify(true);
        if ($result['valid']) {
            $_SESSION['success_message'] = 'License re-verified successfully.';
        } else {
            $_SESSION['error_message'] = 'License verification failed: ' . ($result['error'] ?? 'Unknown error');
        }
        $this->response->redirect('/admin/licensing');
    }

    public function deactivate()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if (is_file(BASE_PATH . '/license.key')) {
            unlink(BASE_PATH . '/license.key');
        }
        // Start grace period
        $lic = new \Core\License(BASE_PATH);
        $lic->startGrace();
        $_SESSION['success_message'] = 'License deactivated. Grace period started.';
        $this->response->redirect('/admin/licensing');
    }

    public function transfer()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }

        if ($_POST && $this->request->post('license_key')) {
            $key = $this->request->post('license_key', '');
            file_put_contents(BASE_PATH . '/license.key', $key);
            @chmod(BASE_PATH . '/license.key', 0644);
            $_SESSION['success_message'] = 'License transferred and saved. Please re-verify.';
        }
        $this->response->redirect('/admin/licensing');
    }

    protected function activateOnline($licenseKey, $email, $company)
    {
        $ch = curl_init('https://license.planet-hosts.com/api/activate');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'license_key' => $licenseKey,
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'ip' => $_SERVER['SERVER_ADDR'] ?? '',
                'machine_id' => function_exists('server_hw_id') ? server_hw_id() : '',
                'hostname' => @exec('hostname') ?: '',
                'email' => $email,
                'company' => $company,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if ($data && ($data['success'] ?? false)) {
                return ['success' => true, 'data' => $data['data'] ?? $data];
            }
            return ['success' => false, 'error' => $data['error'] ?? 'Invalid response from licensing server'];
        }
        return ['success' => false, 'error' => 'Could not reach licensing server (HTTP ' . $httpCode . ')'];
    }

    protected function saveLicenseFile($licenseKey, $data)
    {
        $lic = new \Core\License(BASE_PATH);
        $payload = json_encode([
            'license_id' => $data['license_id'] ?? 'PH-' . date('Y') . '-' . rand(1000, 9999),
            'licensee' => $data['customer_name'] ?? 'Customer',
            'issued' => $data['activation_date'] ?? date('Y-m-d'),
            'expiry' => $data['expiration_date'] ?? 'never',
            'product' => 'Planet-Hosts WHM Panel',
            'version' => '1.0.0',
            'type' => $data['type'] ?? 'full',
        ], JSON_PRETTY_PRINT);

        // Static signature since we may not have private key on server
        $sig = base64_encode(hash('sha256', $payload . 'PLANET_HOSTS_ACTIVATION', true));
        $content = "-----BEGIN PLANET HOSTS LICENSE-----\n";
        $content .= chunk_split($sig, 64, "\n");
        $content .= "-----BEGIN LICENSE DATA-----\n";
        $content .= $payload . "\n";
        $content .= "-----END LICENSE DATA-----\n";
        $content .= "-----END PLANET HOSTS LICENSE-----\n";
        file_put_contents(BASE_PATH . '/license.key', $content);
        @chmod(BASE_PATH . '/license.key', 0644);
    }

    protected function logActivation($licenseKey, $data)
    {
        try {
            $this->db->table('license_activations')->insertGetId([
                'license_key' => $licenseKey,
                'license_type' => $data['type'] ?? 'full',
                'license_status' => 'active',
                'licensed_domain' => $_SERVER['HTTP_HOST'] ?? '',
                'licensed_ip' => $_SERVER['SERVER_ADDR'] ?? '',
                'server_uuid' => function_exists('server_hw_id') ? server_hw_id() : '',
                'max_accounts' => (int)($data['max_accounts'] ?? 0),
                'max_servers' => (int)($data['max_servers'] ?? 1),
                'max_streams' => (int)($data['max_streams'] ?? 0),
                'features' => json_encode($data['features'] ?? []),
                'activation_date' => date('Y-m-d H:i:s'),
                'expiration_date' => isset($data['expiration_date']) ? date('Y-m-d H:i:s', strtotime($data['expiration_date'])) : null,
                'last_validated' => date('Y-m-d H:i:s'),
                'customer_name' => $data['customer_name'] ?? '',
                'customer_email' => $data['customer_email'] ?? '',
            ]);
        } catch (\Exception $e) {}
    }
}
