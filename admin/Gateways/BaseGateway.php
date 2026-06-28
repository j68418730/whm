<?php
namespace Admin\Gateways;

abstract class BaseGateway implements GatewayInterface
{
    protected $name;
    protected $displayName;

    public function __construct()
    {
        $this->name = '';
        $this->displayName = '';
    }

    public function getName() { return $this->name; }
    public function getDisplayName() { return $this->displayName; }

    public function validateConfig($config)
    {
        $fields = $this->getConfigFields();
        $missing = [];
        foreach ($fields as $key => $meta) {
            if (!empty($meta['required']) && empty($config[$key])) {
                $missing[] = $meta['label'] ?? $key;
            }
        }
        return $missing;
    }

    public function processRefund($transactionId, $amount, $config)
    {
        throw new \Exception(get_class($this) . ' does not support refunds yet.');
    }

    public function verifyWebhook($payload, $headers, $config)
    {
        throw new \Exception(get_class($this) . ' does not support webhooks yet.');
    }
}
