<?php

namespace Core;

class Application
{
    protected static $instance;
    protected $config = [];
    protected $services = [];
    protected $router;
    protected $basePath;
    protected $pluginManager;

    public function __construct($basePath, $config)
    {
        self::$instance = $this;
        $this->basePath = $basePath;
        $this->loadConfig($config);
        $this->registerCoreServices();
        $this->registerPlugins();
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

        $this->services['db'] = new \Core\Database($dbConfig);
        $this->services['request'] = new \Core\Request();
        $this->services['response'] = new \Core\Response();
        $this->services['session'] = new \Core\Session();
        $this->services['auth'] = new \Core\Auth($this->services['db'], $this->services['session']);
        $this->services['router'] = new \Core\Router($this->services['request'], $this->services['response']);
    }

    protected function registerPlugins()
    {
        $this->pluginManager = new PluginManager($this);

        $pluginConfig = $this->config['plugins'] ?? [];
        $enabled = $pluginConfig['enabled'] ?? [];

        // Load core routes first
        $this->loadCoreRoutes();

        // Then load enabled plugins
        $this->pluginManager->loadFromConfig($enabled);
    }

    protected function loadCoreRoutes()
    {
        $router = $this->services['router'];
        foreach (['core', 'user'] as $route) {
            $routesFile = $this->basePath . '/routes/' . $route . '.php';
            if (is_file($routesFile)) {
                require $routesFile;
            }
        }
    }

    protected function boot()
    {
        // Boot any legacy service providers
        foreach (($this->config['providers'] ?? []) as $provider) {
            $instance = new $provider($this);
            $instance->register();
            $instance->boot();
        }
    }

    public function get($key)
    {
        return $this->services[$key] ?? null;
    }

    public function set($key, $service)
    {
        $this->services[$key] = $service;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    public function run()
    {
        $this->get('router')->dispatch();
    }
}
