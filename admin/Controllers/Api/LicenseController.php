<?php

namespace Admin\Controllers\Api;

use Core\Controller;
use Core\Request;
use Core\Response;

class LicenseController extends Controller
{
    protected $db;
    protected $request;
    protected $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
    }

public function validate()
    {
        try {
            $this->response->setHeader('Content-Type', 'application/json');
        
        $licenseKey = $this->request->post('license_key', '');
        $serverIp = $this->request->post('server_ip', '');
        $domain = $this->request->post('domain', '');
        $machineId = $this->request->post('machine_id', '');
        
        if (!$licenseKey) {
            echo json_encode(['success' => false, 'error' => 'License key required']);
            return;
        }

        // Check local database first
        $product = $this->db->table('billing_products')
            ->where('license_key', $licenseKey)
            ->first();
            
        if (!$product) {
            // Check external licensing server
            $result = $this->validateExternal($licenseKey, $serverIp, $domain);
            echo json_encode($result);
            return;
        }

        // Check if product is active
        if (!$product->is_active) {
            echo json_encode(['success' => false, 'error' => 'Product is inactive']);
            return;
        }

        // Generate account key if not exists
        $accountKey = $this->generateAccountKey($product->id);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_type' => $product->type,
                'price' => $product->price,
                'billing_cycle' => $product->billing_cycle,
                'account_key' => $accountKey,
                'features' => $this->getFeaturesForType($product->type),
            ]
        ]);
    }

    public function generateKey()
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $productId = (int)$this->request->post('product_id', 0);
        $accountId = (int)$this->request->post('account_id', 0);
        
        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Product ID required']);
            return;
        }

        $product = $this->db->table('billing_products')->where('id', $productId)->first();
        if (!$product) {
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            return;
        }

        $accountKey = $this->generateAccountKey($product->id);
        
        // Store in account_licenses if account provided
        if ($accountId) {
            $this->db->table('account_licenses')->insert([
                'account_id' => $accountId,
                'package_id' => $product->package_id ?? 0,
                'product_key' => $product->license_key ?? '',
                'account_key' => $accountKey,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'account_key' => $accountKey,
                'product_id' => $product->id,
            ]
        ]);
    }

    public function checkIp()
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $ip = $this->request->get('ip', '');
        if (!$ip) {
            $ip = $_SERVER['SERVER_ADDR'] ?? '';
        }
        
        // Check if IP is registered with planet-hosts.com
        // This would query the licensing server
        echo json_encode([
            'success' => true,
            'ip' => $ip,
            'registered' => false,
        ]);
    }

    protected function validateExternal($licenseKey, $serverIp, $domain)
    {
        $ch = curl_init('https://license.planet-hosts.com/api/validate');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'license_key' => $licenseKey,
                'domain' => $domain,
                'ip' => $serverIp,
                'machine_id' => '',
                'hostname' => gethostname(),
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
            return ['success' => false, 'error' => $data['error'] ?? 'Invalid response'];
        }
        return ['success' => false, 'error' => 'Could not reach licensing server'];
    }

    protected function generateAccountKey($productId)
    {
        // Check if already exists
        $existing = $this->db->table('account_licenses')
            ->where('product_key', $productId)
            ->first();
        if ($existing && $existing->account_key) {
            return $existing->account_key;
        }

        $key = 'PH-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(16)));
        
        // Store
        $this->db->table('account_licenses')->insert([
            'package_id' => 0,
            'product_key' => '',
            'account_key' => $key,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $key;
    }

    protected function getFeaturesForType($type)
    {
        $all = ['accounts','packages','dns','email','ftp','databases','backups','ssl','domains','radio','streams','autodj',
                'shared_hosting','radio_hosting','streaming_icecast','streaming_shoutcast_v1','streaming_shoutcast_v2','streaming_autodj',
                'email_hosting','ftp_hosting','database_hosting','ssl_auto','backups','monitoring','api_access','desktop_app'];

        switch ($type) {
            case 'hosting':
                return array_merge($all, ['ssl_wildcard','marketplace']);
            case 'icecast':
                return ['radio','streams','autodj','streaming_icecast','streaming_shoutcast_v1','streaming_shoutcast_v2','streaming_autodj','ssl_auto','monitoring','api_access','desktop_app'];
            case 'full':
            case 'lifetime':
                return array_merge($all, ['reseller_hosting','ssl_wildcard','marketplace','white_label']);
            case 'reseller':
                return array_merge($all, ['reseller_hosting','white_label','ssl_wildcard','marketplace']);
            case 'enterprise':
                return array_merge($all, ['reseller_hosting','vps_hosting','game_hosting','dns_clustering','multi_server','white_label','ssl_wildcard','marketplace','streaming_rtmp','streaming_rtsp','streaming_relay']);
            default:
                return $all;
        }
    }
}