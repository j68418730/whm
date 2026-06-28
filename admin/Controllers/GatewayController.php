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
        $this->gatewayManager->discoverAndInstallDefaults();
        $gateways = $this->gatewayManager->getAll();
        $plugins = $this->gatewayManager->getAllPlugins();
        $pluginMap = [];
        foreach ($plugins as $p) {
            $pluginMap[$p->getName()] = $p;
        }
        return $this->view('admin.gateways.index', [
            'user' => $user,
            'title' => 'Payment Gateways',
            'theme_settings' => $this->theme(),
            'gateways' => $gateways,
            'pluginMap' => $pluginMap,
        ]);
    }

    public function store()
    {
        $this->guard();
        $id = $this->request->post('id');
        $name = $this->request->post('name', '');
        $displayName = $this->request->post('display_name', '');
        $enabled = (int)$this->request->post('enabled', 0);
        $testMode = (int)$this->request->post('test_mode', 0);
        $sortOrder = (int)$this->request->post('sort_order', 0);
        $description = $this->request->post('description', '');
        $merchantId = $this->request->post('merchant_id', '');
        $brandName = $this->request->post('brand_name', '');
        $invoicePrefix = $this->request->post('invoice_prefix', '');
        $currencies = $this->request->post('supported_currencies', 'USD');
        $minAmount = (float)$this->request->post('min_amount', 0);
        $maxAmount = (float)$this->request->post('max_amount', 0);
        $processingFee = (float)$this->request->post('processing_fee', 0);
        $feeType = $this->request->post('fee_type', 'fixed');
        $webhookUrl = $this->request->post('webhook_url', '');
        $webhookSecret = $this->request->post('webhook_secret', '');
        $successUrl = $this->request->post('success_url', '');
        $cancelUrl = $this->request->post('cancel_url', '');
        $isDefault = (int)$this->request->post('is_default', 0);
        $sandboxClientId = $this->request->post('sandbox_client_id', '');
        $sandboxSecret = $this->request->post('sandbox_secret', '');
        $liveClientId = $this->request->post('live_client_id', '');
        $liveSecret = $this->request->post('live_secret', '');

        // Build config from individual fields + extra JSON config
        $plugin = $this->gatewayManager->getPlugin($name);
        $defaultConfig = $plugin ? $plugin->getDefaultConfig() : [];
        $existing = $id ? $this->gatewayManager->getById((int)$id) : null;
        $existingConfig = $existing ? (json_decode($existing->config ?? '{}', true) ?: []) : [];

        $extraRaw = $this->request->post('extra_config', '{}');
        $extraConfig = json_decode($extraRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $extraConfig = [];
        }

        $config = array_merge($defaultConfig, $existingConfig, $extraConfig);
        // Override with explicit fields
        $config['sandbox_client_id'] = $sandboxClientId;
        $config['sandbox_secret'] = $sandboxSecret;
        $config['live_client_id'] = $liveClientId;
        $config['live_secret'] = $liveSecret;
        $config['merchant_id'] = $merchantId;
        $config['brand_name'] = $brandName;
        $config['invoice_prefix'] = $invoicePrefix;
        $config['webhook_id'] = $this->request->post('webhook_id', '');
        // Clean empty values
        foreach ($config as $k => $v) {
            if ($v === null) $config[$k] = '';
        }

        $data = [
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
            'enabled' => $enabled,
            'test_mode' => $testMode,
            'sort_order' => $sortOrder,
            'merchant_id' => $merchantId,
            'brand_name' => $brandName,
            'invoice_prefix' => $invoicePrefix,
            'supported_currencies' => $currencies,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'processing_fee' => $processingFee,
            'fee_type' => $feeType,
            'webhook_url' => $webhookUrl,
            'webhook_secret' => $webhookSecret,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'is_default' => $isDefault,
            'sandbox_client_id' => $sandboxClientId,
            'sandbox_secret' => $sandboxSecret,
            'live_client_id' => $liveClientId,
            'live_secret' => $liveSecret,
            'config' => $config,
        ];

        if ($id) $data['id'] = (int)$id;
        if ($isDefault) {
            $this->db->pdo()->exec("UPDATE gateways SET is_default = 0");
        }

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

    public function toggle($id)
    {
        $this->guard();
        $gw = $this->gatewayManager->getById((int)$id);
        if ($gw) {
            $this->gatewayManager->save(['id' => $gw->id, 'enabled' => $gw->enabled ? 0 : 1]);
            $_SESSION['success_message'] = ($gw->enabled ? 'Disabled' : 'Enabled') . ' ' . $gw->display_name;
        }
        $this->response->redirect('/admin/gateways');
    }

    public function toggleTest($id)
    {
        $this->guard();
        $gw = $this->gatewayManager->getById((int)$id);
        if ($gw) {
            $this->gatewayManager->save(['id' => $gw->id, 'test_mode' => $gw->test_mode ? 0 : 1]);
            $_SESSION['success_message'] = ($gw->test_mode ? 'Live' : 'Test') . ' mode for ' . $gw->display_name;
        }
        $this->response->redirect('/admin/gateways');
    }

    public function test($id)
    {
        $this->guard();
        try {
            $result = $this->gatewayManager->testConnection((int)$id);
            $msg = implode('; ', $result['messages'] ?? []);
            if (!empty($result['success'])) {
                $_SESSION['success_message'] = 'Connection test completed: ' . $msg;
            } else {
                $_SESSION['error_message'] = 'Connection test failed: ' . $msg;
            }
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Test error: ' . $e->getMessage();
        }
        $this->response->redirect('/admin/gateways');
    }

    public function verifyWebhook($id)
    {
        $this->guard();
        $gw = $this->gatewayManager->getById((int)$id);
        if (!$gw) {
            $_SESSION['error_message'] = 'Gateway not found.';
            $this->response->redirect('/admin/gateways');
            return;
        }
        $plugin = $this->gatewayManager->getPlugin($gw->name);
        if (!$plugin || !method_exists($plugin, 'verifyWebhook')) {
            $_SESSION['error_message'] = 'Webhook verification not supported for this gateway.';
            $this->response->redirect('/admin/gateways');
            return;
        }
        $_SESSION['success_message'] = 'Webhook endpoint: ' . rtrim(\Core\Settings::get('site_url', ''), '/') . '/webhook/' . $gw->name;
        $this->response->redirect('/admin/gateways');
    }
}
