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

        // Register default widgets
        $wm->register('server_stats', 'Server Statistics', 'CPU, memory, and disk usage', 'bi-cpu', function($uw) {
            $app = Application::getInstance();
            $db = $app->get('db');
            $pdo = $db->pdo();
            $stats = [];
            @exec("free -m | awk '/Mem:/ {print \$2,\$3,\$4}' 2>/dev/null", $memOut);
            if (!empty($memOut)) {
                $parts = explode(' ', $memOut[0]);
                $stats['mem_total'] = $parts[0] ?? 0;
                $stats['mem_used'] = $parts[1] ?? 0;
                $stats['mem_free'] = $parts[2] ?? 0;
                $stats['mem_pct'] = $stats['mem_total'] > 0 ? round($stats['mem_used'] / $stats['mem_total'] * 100) : 0;
            }
            @exec("top -bn1 | grep 'Cpu(s)' | awk '{print \$2+$4}' 2>/dev/null", $cpuOut);
            $stats['cpu_pct'] = !empty($cpuOut) ? round((float)$cpuOut[0]) : 0;
            @exec("df -h / | awk 'NR==2 {print \$5}' 2>/dev/null", $diskOut);
            $stats['disk_pct'] = !empty($diskOut) ? (int)str_replace('%', '', $diskOut[0]) : 0;
            $html = '<div class="stats-grid" style="grid-template-columns:1fr 1fr 1fr">';
            $html .= '<div class="stat-card"><div class="label">CPU</div><div class="value" style="font-size:24px">' . $stats['cpu_pct'] . '%</div><div class="label">Utilization</div></div>';
            $html .= '<div class="stat-card"><div class="label">Memory</div><div class="value" style="font-size:24px">' . $stats['mem_pct'] . '%</div><div class="label">' . ($stats['mem_used'] ?? 0) . 'MB / ' . ($stats['mem_total'] ?? 0) . 'MB</div></div>';
            $html .= '<div class="stat-card"><div class="label">Disk</div><div class="value" style="font-size:24px">' . $stats['disk_pct'] . '%</div><div class="label">Root partition</div></div>';
            $html .= '</div>';
            return $html;
        });

        $wm->register('quick_actions', 'Quick Actions', 'Common admin tasks', 'bi-lightning', function($uw) {
            $actions = [
                ['Create Account', '/admin/account/create', 'bi-person-plus'],
                ['View Packages', '/admin/packages', 'bi-box'],
                ['Security Center', '/admin/security', 'bi-shield-check'],
                ['Support Tickets', '/admin/support', 'bi-headset'],
                ['Billing', '/admin/billing', 'bi-credit-card'],
            ];
            $html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">';
            foreach ($actions as $a) {
                $html .= '<a href="' . $a[1] . '" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:6px;padding:10px;border-radius:8px"><i class="bi ' . $a[2] . '"></i> ' . $a[0] . '</a>';
            }
            $html .= '</div>';
            return $html;
        });

        $wm->register('service_status', 'Service Status', 'Apache, MySQL, PHP-FPM status', 'bi-gear', function($uw) {
            $services = ['apache2', 'mysql', 'php8.2-fpm', 'icecast2'];
            $html = '<table><tr><th>Service</th><th>Status</th></tr>';
            foreach ($services as $svc) {
                @exec("systemctl is-active $svc 2>/dev/null", $out, $code);
                $status = $code === 0 ? 'active' : 'inactive';
                $color = $code === 0 ? '#4ade80' : '#f87171';
                $html .= '<tr><td>' . $svc . '</td><td><span style="color:' . $color . '">● ' . $status . '</span></td></tr>';
            }
            $html .= '</table>';
            return $html;
        });

        $wm->register('recent_logins', 'Recent Logins', 'Last 5 admin logins', 'bi-shield-check', function($uw) {
            $app = Application::getInstance();
            $db = $app->get('db');
            try {
                $rows = $db->table('login_attempts')->orderBy('created_at', 'DESC')->limit(5)->get() ?: [];
            } catch (\Exception $e) { $rows = []; }
            $html = '<table><tr><th>User</th><th>IP</th><th>Time</th></tr>';
            foreach ($rows as $r) {
                $html .= '<tr><td>' . htmlspecialchars($r->username ?? '?') . '</td><td>' . htmlspecialchars($r->ip_address ?? '') . '</td><td style="font-size:11px">' . ($r->created_at ?? '') . '</td></tr>';
            }
            $html .= '</table>';
            return $html;
        });

        $wm->register('revenue', 'Revenue Overview', 'Monthly billing summary', 'bi-currency-dollar', function($uw) {
            $app = Application::getInstance();
            $db = $app->get('db');
            $total = $db->table('payments')->where('status', 'completed')->value('SUM(amount)') ?: 0;
            $month = $db->table('payments')->where('status', 'completed')->where('created_at', '>=', date('Y-m-01'))->value('SUM(amount)') ?: 0;
            $pending = $db->table('invoices')->where('status', 'pending')->value('SUM(total)') ?: 0;
            $html = '<div class="stats-grid" style="grid-template-columns:1fr 1fr 1fr">';
            $html .= '<div class="stat-card"><div class="label">Total Revenue</div><div class="value" style="font-size:20px">$' . number_format($total, 2) . '</div></div>';
            $html .= '<div class="stat-card"><div class="label">This Month</div><div class="value" style="font-size:20px">$' . number_format($month, 2) . '</div></div>';
            $html .= '<div class="stat-card"><div class="label">Pending</div><div class="value" style="font-size:20px">$' . number_format($pending, 2) . '</div></div>';
            $html .= '</div>';
            return $html;
        });

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

        // Create widget table if needed
        try {
            $pdo = $this->services['db']->pdo();
            $pdo->exec("CREATE TABLE IF NOT EXISTS user_widgets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                widget_key VARCHAR(100) NOT NULL,
                zone VARCHAR(50) NOT NULL DEFAULT 'main',
                sort_order INT NOT NULL DEFAULT 0,
                settings JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY user_widget (user_id, widget_key)
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
