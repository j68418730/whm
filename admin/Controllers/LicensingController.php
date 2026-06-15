<?php

namespace Admin\Controllers;

use Core\Controller;

class LicensingController extends Controller
{
    protected $auth, $request, $response;

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
        $license = new \Core\License(BASE_PATH);
        $status = $license->verify();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        
        // Check which features are available
        $features = [];
        $allChecks = ['accounts','packages','dns','email','ftp','databases','backups','ssl','domains','radio','streams','autodj'];
        foreach ($allChecks as $f) {
            $features[$f] = $license->hasFeature($f);
        }
        
        return $this->view('admin.licensing.index', [
            'user' => $user, 'title' => 'Licensing', 'theme_settings' => $theme_settings,
            'status' => $status, 'features' => $features,
        ]);
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
            $licenseId = $this->request->post('license_id', 'LICS-' . date('Y') . '-' . str_pad(rand(1,9999),4,'0',STR_PAD_LEFT));
            $expiry = $this->request->post('expiry', 'never');
            $type = $this->request->post('type', 'full');
            if (!in_array($type, ['hosting','icecast','full'])) $type = 'full';

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
}
