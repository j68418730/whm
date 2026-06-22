<?php
namespace Core;

class GatewayManager
{
    protected $db;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->db = $app->get('db');
    }

    public function getAll()
    {
        return $this->db->table('gateways')->orderBy('sort_order', 'ASC')->get() ?: [];
    }

    public function getEnabled()
    {
        return $this->db->table('gateways')->where('enabled', 1)->orderBy('sort_order', 'ASC')->get() ?: [];
    }

    public function get($name)
    {
        return $this->db->table('gateways')->where('name', $name)->first();
    }

    public function getById($id)
    {
        return $this->db->table('gateways')->where('id', $id)->first();
    }

    public function save($data)
    {
        if (!empty($data['id'])) {
            $id = (int)$data['id'];
            unset($data['id']);
            if (isset($data['config']) && is_array($data['config'])) {
                $data['config'] = json_encode($data['config']);
            }
            $this->db->table('gateways')->where('id', $id)->update($data);
            return $id;
        }

        if (isset($data['config']) && is_array($data['config'])) {
            $data['config'] = json_encode($data['config']);
        }
        return $this->db->table('gateways')->insertGetId($data);
    }

    public function delete($id)
    {
        return $this->db->table('gateways')->where('id', $id)->delete();
    }

    public function processPayment($gatewayName, $amount, $data)
    {
        $gateway = $this->get($gatewayName);
        if (!$gateway || !$gateway->enabled) {
            throw new \Exception("Gateway '{$gatewayName}' is not available.");
        }

        $config = json_decode($gateway->config ?? '{}', true);
        $testMode = (bool)$gateway->test_mode;

        switch ($gatewayName) {
            case 'paypal':
                return $this->processPayPal($config, $amount, $data, $testMode);
            case 'stripe':
                return $this->processStripe($config, $amount, $data, $testMode);
            case 'square':
                return $this->processSquare($config, $amount, $data, $testMode);
            case 'authorizenet':
                return $this->processAuthorizeNet($config, $amount, $data, $testMode);
            default:
                throw new \Exception("Gateway '{$gatewayName}' processing is not implemented.");
        }
    }

    protected function processPayPal($config, $amount, $data, $testMode)
    {
        $apiUrl = $testMode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $clientId = $config['client_id'] ?? '';
        $secret = $config['secret'] ?? '';

        if (empty($clientId) || empty($secret)) {
            throw new \Exception('PayPal is not configured.');
        }

        return [
            'success' => true,
            'gateway' => 'paypal',
            'transaction_id' => 'PP-' . strtoupper(bin2hex(random_bytes(8))),
            'amount' => $amount,
            'test_mode' => $testMode,
            'redirect_url' => "{$apiUrl}/checkoutnow?token=PLACEHOLDER",
        ];
    }

    protected function processStripe($config, $amount, $data, $testMode)
    {
        $secretKey = $testMode ? ($config['secret_key'] ?? '') : ($config['secret_key'] ?? '');

        if (empty($secretKey)) {
            throw new \Exception('Stripe is not configured.');
        }

        return [
            'success' => true,
            'gateway' => 'stripe',
            'transaction_id' => 'STR-' . strtoupper(bin2hex(random_bytes(8))),
            'amount' => $amount,
            'test_mode' => $testMode,
            'payment_intent_secret' => 'pi_secret_placeholder',
        ];
    }

    protected function processSquare($config, $amount, $data, $testMode)
    {
        $accessToken = $config['access_token'] ?? '';
        if (empty($accessToken)) {
            throw new \Exception('Square is not configured.');
        }

        return [
            'success' => true,
            'gateway' => 'square',
            'transaction_id' => 'SQ-' . strtoupper(bin2hex(random_bytes(8))),
            'amount' => $amount,
            'test_mode' => $testMode,
        ];
    }

    protected function processAuthorizeNet($config, $amount, $data, $testMode)
    {
        $loginId = $config['api_login_id'] ?? '';
        $tranKey = $config['transaction_key'] ?? '';

        if (empty($loginId) || empty($tranKey)) {
            throw new \Exception('Authorize.net is not configured.');
        }

        return [
            'success' => true,
            'gateway' => 'authorizenet',
            'transaction_id' => 'ANET-' . strtoupper(bin2hex(random_bytes(8))),
            'amount' => $amount,
            'test_mode' => $testMode,
        ];
    }
}
