<?php
/**
 * PayPal REST API v2 helpers (OAuth client_credentials + Orders API).
 * Reads credentials from automation_settings:
 *   sandbox: paypal_client_id / paypal_secret
 *   live:    paypal_live_client_id / paypal_live_secret
 *   mode:    paypal_mode (sandbox|live)
 */

function paypal_setting($key, $default = '')
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
            foreach ($pdo->query("SELECT setting_key, setting_value FROM automation_settings") as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Exception $e) {}
    }
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

function paypal_mode()
{
    return paypal_setting('paypal_mode', 'sandbox');
}

function paypal_is_enabled()
{
    return paypal_setting('paypal_enabled', '0') === '1';
}

function paypal_creds($mode = null)
{
    $mode = $mode ?: paypal_mode();
    if ($mode === 'live') {
        return [paypal_setting('paypal_live_client_id'), paypal_setting('paypal_live_secret')];
    }
    return [paypal_setting('paypal_client_id'), paypal_setting('paypal_secret')];
}

function paypal_api_base($mode = null)
{
    $mode = $mode ?: paypal_mode();
    return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
}

function paypal_http($method, $url, $headers = [], $body = null)
{
    $ch = curl_init($url);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
    }
    curl_setopt($ch, CURLOPT_POST, $method === 'POST');
    curl_setopt($ch, CURLOPT_HTTPGET, $method === 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$response, $status];
}

function paypal_get_token($mode = null)
{
    $mode = $mode ?: paypal_mode();
    list($clientId, $secret) = paypal_creds($mode);
    if (!$clientId || !$secret) return null;
    list($body, $status) = paypal_http(
        'POST',
        paypal_api_base($mode) . '/v1/oauth2/token',
        [
            'Authorization: Basic ' . base64_encode($clientId . ':' . $secret),
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ],
        'grant_type=client_credentials'
    );
    $json = json_decode((string)$body, true);
    return ($json['access_token'] ?? null);
}

function paypal_create_order($mode, $amount, $reference, $returnUrl, $cancelUrl, $description = '')
{
    $token = paypal_get_token($mode);
    if (!$token) return null;
    $payload = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => $reference,
            'description' => substr($description ?: 'Planet Hosts order', 0, 90),
            'amount' => [
                'currency_code' => 'USD',
                'value' => number_format((float)$amount, 2, '.', ''),
            ],
        ]],
        'application_context' => [
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'shipping_preference' => 'NO_SHIPPING',
            'user_action' => 'PAY_NOW',
        ],
    ];
    list($body, $status) = paypal_http(
        'POST',
        paypal_api_base($mode) . '/v2/checkout/orders',
        ['Content-Type: application/json', 'Accept: application/json', 'Authorization: Bearer ' . $token],
        $payload
    );
    return json_decode((string)$body, true);
}

function paypal_capture_order($mode, $paypalOrderId)
{
    $token = paypal_get_token($mode);
    if (!$token) return null;
    list($body, $status) = paypal_http(
        'POST',
        paypal_api_base($mode) . '/v2/checkout/orders/' . rawurlencode($paypalOrderId) . '/capture',
        ['Content-Type: application/json', 'Accept: application/json', 'Authorization: Bearer ' . $token],
        '{}'
    );
    return json_decode((string)$body, true);
}

function paypal_approve_url($orderJson)
{
    if (!$orderJson || !isset($orderJson['links'])) return null;
    foreach ($orderJson['links'] as $link) {
        $rel = $link['rel'] ?? '';
        if ($rel === 'approve' || $rel === 'payer-action') return $link['href'] ?? null;
    }
    return null;
}