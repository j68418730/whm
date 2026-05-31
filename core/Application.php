<?php
/**
 * WHM Core Application Class
 * Hosting Panel Bootstrap - Radio Hosting Integrated
 */

namespace Core;

class Application
{
    protected static $instance;
    protected $config = [];
    protected $services = [];
    protected $router;
    protected $basePath;

    public function __construct($basePath, $config)
    {
        self::$instance = $this;
        $this->basePath = $basePath;
        $this->loadConfig($config);
        $this->registerCoreServices();
        $this->boot();
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    protected function loadConfig($config)
    {
        $this->config = is_array($config) ? $config : require $config;
    }

    protected function registerCoreServices()
    {
        $this->services['config'] = new \Core\Config($this->config);

        $dbConfig = $this->config['database'] ?? [];
        if (isset($dbConfig['connections']['mysql'])) {
            $dbConfig = $dbConfig['connections']['mysql'];
        }

        // Register core services
        $this->services['db'] = new \Core\Database($dbConfig);
        $this->services['request'] = new \Core\Request();
        $this->services['response'] = new \Core\Response();
        $this->services['session'] = new \Core\Session();
        $this->services['auth'] = new \Core\Auth($this->services['db'], $this->services['session']);
        $this->services['router'] = new \Core\Router($this->services['request'], $this->services['response']);
        
        // Register radio-specific services as core components
        $this->services['radio.stream'] = new \Services\Stream\StreamManager(
            $this->services['config'],
            $this->services['db']
        );
        $this->services['radio.autodj'] = new \Services\AutoDJ\AutoDJManager(
            $this->services['config'],
            $this->services['db']
        );
        $this->services['radio.transcoding'] = new \Services\Transcoding\TranscodingManager(
            $this->services['config']
        );
    }

    protected function boot()
    {
        // Load service providers
        foreach (($this->config['providers'] ?? []) as $provider) {
            $instance = new $provider($this);
            $instance->register();
            $instance->boot();
        }
        
        // Boot radio service provider if enabled
        if (($this->config['radio']['global_enabled'] ?? false) && !in_array(\Providers\RadioServiceProvider::class, $this->config['providers'] ?? [], true)) {
            $radioProvider = new \Providers\RadioServiceProvider($this);
            $radioProvider->register();
            $radioProvider->boot();
        }
    }

    public function get($key)
    {
        return $this->services[$key] ?? null;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function run()
    {
        $this->get('router')->dispatch();
    }
}
