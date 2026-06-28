<?php
namespace Admin\Gateways;

class PayPalGateway extends BaseGateway
{
    public function __construct()
    {
        $this->name = 'paypal';
        $this->displayName = 'PayPal';
    }

    public function getDefaultConfig()
    {
        return [
            'sandbox_client_id' => '',
            'sandbox_secret' => '',
            'live_client_id' => '',
            'live_secret' => '',
            'merchant_id' => '',
            'brand_name' => '',
            'invoice_prefix' => 'INV-',
            'webhook_id' => '',
        ];
    }

    public function getConfigFields()
    {
        return [
            'sandbox_client_id' => ['label' => 'Sandbox Client ID', 'type' => 'text', 'required' => false, 'section' => 'sandbox'],
            'sandbox_secret' => ['label' => 'Sandbox Secret', 'type' => 'password', 'required' => false, 'section' => 'sandbox'],
            'live_client_id' => ['label' => 'Live Client ID', 'type' => 'text', 'required' => false, 'section' => 'live'],
            'live_secret' => ['label' => 'Live Secret', 'type' => 'password', 'required' => false, 'section' => 'live'],
            'merchant_id' => ['label' => 'Merchant ID', 'type' => 'text', 'required' => false, 'section' => 'general'],
            'brand_name' => ['label' => 'Brand Name', 'type' => 'text', 'required' => false, 'section' => 'general'],
            'invoice_prefix' => ['label' => 'Invoice Prefix', 'type' => 'text', 'required' => false, 'section' => 'general'],
            'webhook_id' => ['label' => 'Webhook ID', 'type' => 'text', 'required' => false, 'section' => 'webhook'],
        ];
    }

    public function getAccessToken($config, $testMode)
    {
        $clientId = $testMode ? ($config['sandbox_client_id'] ?? '') : ($config['live_client_id'] ?? '');
        $secret = $testMode ? ($config['sandbox_secret'] ?? '') : ($config['live_secret'] ?? '');

        if (empty($clientId) || empty($secret)) {
            throw new \Exception('PayPal ' . ($testMode ? 'sandbox' : 'live') . ' credentials are not configured.');
        }

        $url = $testMode
            ? 'https://api-m.sandbox.paypal.com/v1/oauth2/token'
            : 'https://api-m.paypal.com/v1/oauth2/token';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $clientId . ':' . $secret,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('PayPal connection failed: ' . $error);
        }

        $data = json_decode($response, true);
        if ($httpCode !== 200 || empty($data['access_token'])) {
            $msg = $data['error_description'] ?? ($data['error'] ?? 'Unknown error');
            throw new \Exception('PayPal auth failed (' . $httpCode . '): ' . $msg);
        }

        return $data['access_token'];
    }

    public function testConnection($config)
    {
        $sandboxOk = false;
        $liveOk = false;
        $messages = [];

        // Test sandbox
        if (!empty($config['sandbox_client_id']) && !empty($config['sandbox_secret'])) {
            try {
                $token = $this->getAccessToken($config, true);
                $sandboxOk = !empty($token);
                $messages[] = 'Sandbox: Connected';
            } catch (\Exception $e) {
                $messages[] = 'Sandbox: ' . $e->getMessage();
            }
        } else {
            $messages[] = 'Sandbox: Not configured';
        }

        // Test live
        if (!empty($config['live_client_id']) && !empty($config['live_secret'])) {
            try {
                $token = $this->getAccessToken($config, false);
                $liveOk = !empty($token);
                $messages[] = 'Live: Connected';
            } catch (\Exception $e) {
                $messages[] = 'Live: ' . $e->getMessage();
            }
        } else {
            $messages[] = 'Live: Not configured';
        }

        return [
            'success' => $sandboxOk || $liveOk,
            'sandbox_ok' => $sandboxOk,
            'live_ok' => $liveOk,
            'messages' => $messages,
        ];
    }

    public function processPayment($amount, $config, $data)
    {
        $testMode = !empty($data['test_mode']);
        $token = $this->getAccessToken($config, $testMode);

        $apiUrl = $testMode
            ? 'https://api-m.sandbox.paypal.com/v2/checkout/orders'
            : 'https://api-m.paypal.com/v2/checkout/orders';

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $data['currency'] ?? 'USD',
                    'value' => number_format($amount, 2, '.', ''),
                ],
                'description' => $data['description'] ?? '',
            ]],
            'application_context' => [
                'brand_name' => $config['brand_name'] ?: 'Planet Hosts',
                'return_url' => $data['return_url'] ?? '',
                'cancel_url' => $data['cancel_url'] ?? '',
            ],
        ];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => json_encode($orderData),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            $err = json_decode($response, true);
            $msg = $err['message'] ?? 'Payment creation failed';
            throw new \Exception('PayPal order error (' . $httpCode . '): ' . $msg);
        }

        $order = json_decode($response, true);
        $approvalUrl = '';
        foreach ($order['links'] ?? [] as $link) {
            if ($link['rel'] === 'approve') {
                $approvalUrl = $link['href'];
                break;
            }
        }

        return [
            'success' => true,
            'gateway' => 'paypal',
            'transaction_id' => $order['id'],
            'amount' => $amount,
            'test_mode' => $testMode,
            'redirect_url' => $approvalUrl,
            'status' => $order['status'],
            'raw_order' => $order,
        ];
    }

    public function verifyWebhook($payload, $headers, $config)
    {
        if (empty($config['webhook_id'])) {
            throw new \Exception('Webhook ID is not configured.');
        }

        $testMode = !empty($headers['TEST_MODE'] ?? false);
        $apiUrl = $testMode
            ? 'https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature'
            : 'https://api-m.paypal.com/v1/notifications/verify-webhook-signature';

        $token = $this->getAccessToken($config, $testMode);

        $verificationData = [
            'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
            'cert_url' => $headers['PAYPAL-CERT-URL'] ?? '',
            'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
            'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
            'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
            'webhook_id' => $config['webhook_id'],
            'webhook_event' => json_decode($payload, true),
        ];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => json_encode($verificationData),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Webhook verification failed (' . $httpCode . ')');
        }

        $result = json_decode($response, true);
        return ($result['verification_status'] ?? '') === 'SUCCESS';
    }
}
