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
        // Initialize widget system
        $this->initWidgets();
    }

    protected function initWidgets()
    {
        require_once $this->basePath . '/core/Widget.php';
        require_once $this->basePath . '/core/WidgetManager.php';
        $wm = WidgetManager::getInstance();
        $wm->setDb($this->services['db']);

        // Load widgets from /widgets/ folder
        $widgetsPath = $this->basePath . DIRECTORY_SEPARATOR . 'widgets';
        if (is_dir($widgetsPath)) {
            $wm->loadFromFolder($widgetsPath);
        }

        // Ensure commonly-used tables exist
        try {
            $pdo = $this->services['db']->pdo();
            $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100), ip_address VARCHAR(45),
                success TINYINT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
                id INT AUTO_INCREMENT PRIMARY KEY, invoice_id INT, amount DECIMAL(10,2),
                status VARCHAR(50) DEFAULT 'completed', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, target_id INT,
                action VARCHAR(100), details TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
                id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, total DECIMAL(10,2),
                status VARCHAR(50) DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}

        // Create widget tables if needed
        try {
            $pdo = $this->services['db']->pdo();
            $pdo->exec("CREATE TABLE IF NOT EXISTS user_widgets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                widget_key VARCHAR(100) NOT NULL,
                zone VARCHAR(50) NOT NULL DEFAULT 'main',
                sort_order INT NOT NULL DEFAULT 0,
                width INT NOT NULL DEFAULT 1,
                collapsed TINYINT NOT NULL DEFAULT 0,
                hidden TINYINT NOT NULL DEFAULT 0,
                pinned TINYINT NOT NULL DEFAULT 0,
                layout_name VARCHAR(100) NOT NULL DEFAULT 'default',
                settings JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY user_widget (user_id, widget_key, layout_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("CREATE TABLE IF NOT EXISTS widget_layouts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                layout_name VARCHAR(100) NOT NULL,
                snapshot JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY user_layout (user_id, layout_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("CREATE TABLE IF NOT EXISTS custom_widgets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                widget_key VARCHAR(100) NOT NULL,
                name VARCHAR(200) NOT NULL,
                widget_type VARCHAR(50) NOT NULL DEFAULT 'html',
                icon VARCHAR(50) DEFAULT 'bi-box',
                config JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY user_widget_key (user_id, widget_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
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
