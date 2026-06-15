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
