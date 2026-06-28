<?php
namespace Core;

use Admin\Gateways\GatewayInterface;

class GatewayManager
{
    protected $db;
    protected $instances = [];

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

    public function getDefault()
    {
        return $this->db->table('gateways')->where('is_default', 1)->where('enabled', 1)->first();
    }

    public function save($data)
    {
        $isNew = empty($data['id']);
        $id = $isNew ? null : (int)$data['id'];
        if (!$isNew) unset($data['id']);

        if (isset($data['config']) && is_array($data['config'])) {
            $data['config'] = json_encode($data['config']);
        }

        if ($isNew) {
            return $this->db->table('gateways')->insertGetId($data);
        }
        $this->db->table('gateways')->where('id', $id)->update($data);
        return $id;
    }

    public function delete($id)
    {
        return $this->db->table('gateways')->where('id', $id)->delete();
    }

    public function getPlugin($name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        $class = '\\Admin\\Gateways\\' . ucfirst($name) . 'Gateway';
        $file = __DIR__ . '/../admin/Gateways/' . ucfirst($name) . 'Gateway.php';
        if (file_exists($file)) {
            require_once $file;
            if (class_exists($class)) {
                $this->instances[$name] = new $class();
                return $this->instances[$name];
            }
        }
        return null;
    }

    public function getAllPlugins()
    {
        $dir = __DIR__ . '/../admin/Gateways/';
        $plugins = [];
        if (!is_dir($dir)) return $plugins;
        foreach (glob($dir . '*Gateway.php') as $file) {
            $basename = basename($file, 'Gateway.php');
            if ($basename === 'Base' || $basename === 'Interface') continue;
            require_once $file;
            $class = '\\Admin\\Gateways\\' . $basename . 'Gateway';
            if (class_exists($class)) {
                $ref = new \ReflectionClass($class);
                if (!$ref->isAbstract() && $ref->implementsInterface('\\Admin\\Gateways\\GatewayInterface')) {
                    $plugins[] = new $class();
                }
            }
        }
        return $plugins;
    }

    public function discoverAndInstallDefaults()
    {
        $plugins = $this->getAllPlugins();
        $sortOrder = 0;
        foreach ($plugins as $plugin) {
            $name = $plugin->getName();
            $existing = $this->get($name);
            if (!$existing) {
                $this->save([
                    'name' => $name,
                    'display_name' => $plugin->getDisplayName(),
                    'enabled' => 0,
                    'test_mode' => 1,
                    'sort_order' => ++$sortOrder,
                    'config' => $plugin->getDefaultConfig(),
                ]);
            }
        }
    }

    public function testConnection($id)
    {
        $gateway = $this->getById((int)$id);
        if (!$gateway) {
            throw new \Exception('Gateway not found.');
        }
        $plugin = $this->getPlugin($gateway->name);
        if (!$plugin) {
            throw new \Exception("No plugin found for '{$gateway->name}'.");
        }
        $config = array_merge($plugin->getDefaultConfig(), json_decode($gateway->config ?? '{}', true) ?: []);
        return $plugin->testConnection($config);
    }

    public function processPayment($gatewayName, $amount, $data)
    {
        $gateway = $this->get($gatewayName);
        if (!$gateway || !$gateway->enabled) {
            throw new \Exception("Gateway '{$gatewayName}' is not available.");
        }
        $plugin = $this->getPlugin($gatewayName);
        if (!$plugin) {
            throw new \Exception("No payment plugin for '{$gatewayName}'.");
        }
        $config = array_merge($plugin->getDefaultConfig(), json_decode($gateway->config ?? '{}', true) ?: []);
        $data['test_mode'] = (bool)$gateway->test_mode;
        return $plugin->processPayment($amount, $config, $data);
    }
}
