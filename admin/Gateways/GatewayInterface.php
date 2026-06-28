<?php
namespace Admin\Gateways;

interface GatewayInterface
{
    public function getName();
    public function getDisplayName();
    public function getDefaultConfig();
    public function getConfigFields();
    public function validateConfig($config);
    public function testConnection($config);
    public function processPayment($amount, $config, $data);
    public function processRefund($transactionId, $amount, $config);
    public function verifyWebhook($payload, $headers, $config);
}
