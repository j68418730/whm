<?php
namespace Core;

class ThemeEngine
{
    protected static $instance;
    protected $adminTheme = null;
    protected $publicTheme = null;
    protected $adminPath = '';
    protected $publicPath = '';

    public static function getInstance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function __construct()
    {
        $this->adminPath = BASE_PATH . '/themes/admin';
        $this->publicPath = BASE_PATH . '/themes/public';
    }

    public function getAdminTheme($name = null)
    {
        if (!$name) $name = $this->getActiveAdminTheme();
        if ($this->adminTheme && $this->adminTheme['name'] === $name) return $this->adminTheme;
        $this->adminTheme = $this->loadTheme($this->adminPath, $name);
        return $this->adminTheme;
    }

    public function getPublicTheme($name = null)
    {
        if (!$name) $name = $this->getActivePublicTheme();
        $this->publicTheme = $this->loadTheme($this->publicPath, $name);
        return $this->publicTheme;
    }

    public function loadTheme($basePath, $name)
    {
        $themeDir = $basePath . '/' . $name;
        $themeFile = $themeDir . '/theme.json';
        if (!is_dir($themeDir) || !is_file($themeFile)) return null;
        $config = json_decode(file_get_contents($themeFile), true);
        if (!$config) return null;
        $config['dir'] = $themeDir;
        $config['url'] = '/themes/' . basename($basePath) . '/' . $name;
        $config['name_key'] = $name;
        return $config;
    }

    public function getActiveAdminTheme()
    {
        $stored = $this->getSetting('admin_theme', 'default');
        return $stored ?: 'default';
    }

    public function getActivePublicTheme()
    {
        $stored = $this->getSetting('public_theme', 'default');
        return $stored ?: 'default';
    }

    public function setActiveAdminTheme($name)
    {
        $this->setSetting('admin_theme', $name);
    }

    public function setActivePublicTheme($name)
    {
        $this->setSetting('public_theme', $name);
    }

    public function listThemes($type = 'admin')
    {
        $path = $type === 'admin' ? $this->adminPath : $this->publicPath;
        $themes = [];
        if (!is_dir($path)) return $themes;
        foreach (scandir($path) as $dir) {
            if ($dir[0] === '.') continue;
            $theme = $this->loadTheme($path, $dir);
            if ($theme) $themes[$dir] = $theme;
        }
        return $themes;
    }

    public function renderAdminLayout($content, $data = [])
    {
        $theme = $this->getAdminTheme();
        $themeUrl = $theme['url'] ?? '/themes/admin/default';
        extract($data);
        ob_start();
        require $this->adminPath . '/' . ($theme['name_key'] ?? 'default') . '/layout.php';
        return ob_get_clean();
    }

    public function renderPublicLayout($content, $data = [])
    {
        $theme = $this->getPublicTheme();
        $themeUrl = $theme['url'] ?? '/themes/public/default';
        extract($data);
        ob_start();
        $layoutFile = $this->publicPath . '/' . ($theme['name_key'] ?? 'default') . '/layout.php';
        if (!is_file($layoutFile)) $layoutFile = $this->publicPath . '/default/layout.php';
        if (is_file($layoutFile)) require $layoutFile;
        else echo $content;
        return ob_get_clean();
    }

    public function assetUrl($path, $type = 'admin')
    {
        $theme = $type === 'admin' ? $this->getAdminTheme() : $this->getPublicTheme();
        return ($theme['url'] ?? '/themes/admin/default') . '/' . ltrim($path, '/');
    }

    protected function getSetting($key, $default = '')
    {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
            $q = $pdo->prepare("SELECT setting_value FROM automation_settings WHERE setting_key = ?");
            $q->execute([$key]);
            return $q->fetchColumn() ?: $default;
        } catch (\Exception $e) { return $default; }
    }

    protected function setSetting($key, $value)
    {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
            $pdo->prepare("INSERT INTO automation_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")
                ->execute([$key, $value]);
        } catch (\Exception $e) {}
    }

    public function getThemeCss($type = 'admin')
    {
        $theme = $type === 'admin' ? $this->getAdminTheme() : $this->getPublicTheme();
        $css = '';
        if ($theme && isset($theme['colors'])) {
            $css .= ':root {';
            foreach ($theme['colors'] as $key => $val) {
                $css .= "--{$key}: {$val};";
            }
            foreach (($theme['fonts'] ?? []) as $key => $val) {
                $css .= "--font-{$key}: {$val};";
            }
            $css .= '}';
        }
        return $css;
    }

    public function copyDir($src, $dst)
    {
        @mkdir($dst, 0755, true);
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file[0] === '.') continue;
            $s = $src . '/' . $file;
            $d = $dst . '/' . $file;
            is_dir($s) ? $this->copyDir($s, $d) : copy($s, $d);
        }
        closedir($dir);
    }

    public function removeDir($dir)
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item[0] === '.') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }
}
