<?php
namespace Admin\Controllers;

use Core\Controller;
use Core\GatewayManager;

class GatewayController extends Controller
{
    protected $auth, $request, $response, $db, $gatewayManager;

    public function __construct()
    {
        parent::__construct();
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->gatewayManager = new GatewayManager();
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
    }

    protected function theme()
    {
        $user = $this->auth->user();
        return json_decode($user->theme_settings ?? '{}', true);
    }

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $gateways = $this->gatewayManager->getAll();
        return $this->view('admin.gateways.index', [
            'user' => $user,
            'title' => 'Payment Gateways',
            'theme_settings' => $this->theme(),
            'gateways' => $gateways,
        ]);
    }

    public function store()
    {
        $this->guard();
        $id = $this->request->post('id');
        $data = [
            'name' => $this->request->post('name', ''),
            'display_name' => $this->request->post('display_name', ''),
            'enabled' => (int)$this->request->post('enabled', 0),
            'test_mode' => (int)$this->request->post('test_mode', 0),
            'sort_order' => (int)$this->request->post('sort_order', 0),
        ];

        $configRaw = $this->request->post('config', '{}');
        $config = json_decode($configRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $_SESSION['error_message'] = 'Invalid JSON in configuration field.';
            $this->response->redirect('/admin/gateways');
            exit;
        }
        $data['config'] = json_encode($config);
        $data['id'] = $id;

        $this->gatewayManager->save($data);
        $_SESSION['success_message'] = 'Gateway saved successfully.';
        $this->response->redirect('/admin/gateways');
    }

    public function delete($id)
    {
        $this->guard();
        $this->gatewayManager->delete((int)$id);
        $_SESSION['success_message'] = 'Gateway deleted.';
        $this->response->redirect('/admin/gateways');
    }

    public function test($id)
    {
        $this->guard();
        $gateway = $this->gatewayManager->getById((int)$id);
        if (!$gateway) {
            $_SESSION['error_message'] = 'Gateway not found.';
            $this->response->redirect('/admin/gateways');
            exit;
        }

        try {
            $result = $this->gatewayManager->processPayment($gateway->name, 1.00, [
                'description' => 'Test transaction',
                'email' => 'test@example.com',
            ]);
            $_SESSION['success_message'] = 'Test successful. Transaction ID: ' . ($result['transaction_id'] ?? 'N/A');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Test failed: ' . $e->getMessage();
        }

        $this->response->redirect('/admin/gateways');
    }
}
