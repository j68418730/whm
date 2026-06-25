<?php

use Core\Application;

if (!function_exists('admin_plugin_enabled')) {
    function admin_plugin_enabled(string $class): bool
    {
        try {
            $app = Application::getInstance();
            $config = $app ? $app->get('config') : null;
            $enabled = $config && method_exists($config, 'get') ? ($config->get('plugins.enabled', []) ?: []) : [];
            return in_array($class, $enabled, true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('admin_menu_sections')) {
    function admin_menu_sections(): array
    {
        return [
            ['label' => 'Dashboard', 'href' => '/admin/dashboard', 'icon' => '📊', 'match' => ['/admin', '/admin/dashboard', '/admin/activity-log', '/admin/monitoring', '/admin/notifications']],
            ['label' => 'Accounts', 'href' => '/admin/section/accounts', 'icon' => '👥', 'match' => ['/admin/account', '/admin/package', '/admin/reseller', '/admin/feature-lists', '/admin/admins', '/admin/roles']],
            ['label' => 'Hosting', 'href' => '/admin/section/hosting', 'icon' => '🌐', 'match' => ['/admin/email', '/admin/mysql', '/admin/ftp', '/admin/ssl', '/admin/backup', '/admin/cron', '/admin/server']],
            ['label' => 'Billing', 'href' => '/admin/section/billing', 'icon' => '💳', 'match' => ['/admin/billing', '/admin/gateways', '/admin/paypal']],
            ['label' => 'Support', 'href' => '/admin/section/support', 'icon' => '🎫', 'match' => ['/admin/support', '/admin/livechat', '/admin/reviews']],
            ['label' => 'Radio', 'href' => '/admin/section/radio', 'icon' => '📻', 'match' => ['/admin/radio_dashboard', '/admin/streams', '/admin/djs', '/admin/autodj', '/admin/radiosettings']],
            ['label' => 'Games', 'href' => '/admin/section/games', 'icon' => '🎮', 'match' => ['/admin/games']],
            ['label' => 'Builder', 'href' => '/admin/section/builder', 'icon' => '🏗️', 'match' => ['/admin/websitebuilder']],
            ['label' => 'Domains', 'href' => '/admin/section/domains', 'icon' => '🌍', 'match' => ['/admin/domains', '/admin/dns', '/admin/ip']],
            ['label' => 'Security', 'href' => '/admin/section/security', 'icon' => '🔒', 'match' => ['/admin/security', '/admin/firewall', '/admin/ipblocker', '/admin/twofactor']],
            ['label' => 'System', 'href' => '/admin/section/system', 'icon' => '⚙️', 'match' => ['/admin/settings', '/admin/serverconfig', '/admin/hostname', '/admin/licensing', '/admin/plugins', '/admin/installers', '/admin/todo', '/admin/process-manager', '/admin/automation', '/admin/filesystem', '/admin/themes', '/admin/theme']],
        ];
    }
}

if (!function_exists('render_admin_menu_sections')) {
    function render_admin_menu_sections(string $currentUrl): string
    {
        $html = '';
        foreach (admin_menu_sections() as $item) {
            $isActive = false;
            foreach ($item['match'] ?? [] as $m) {
                if ($m && str_starts_with($currentUrl, $m)) { $isActive = true; break; }
            }
            $html .= '<a href="' . htmlspecialchars($item['href']) . '" class="nav-link ' . ($isActive ? 'active' : '') . '">'
                   . '<span class="nav-icon">' . $item['icon'] . '</span> '
                   . '<span class="nav-label">' . htmlspecialchars($item['label']) . '</span>'
                   . '</a>';
        }
        return $html;
    }
}

if (!function_exists('admin_menu_section_active')) {
    function admin_menu_section_active(string $label): bool
    {
        $url = $_SERVER['REQUEST_URI'] ?? '';
        foreach (admin_menu_sections() as $item) {
            if ($item['label'] === $label) {
                foreach ($item['match'] ?? [] as $m) {
                    if ($m && str_starts_with($url, $m)) return true;
                }
            }
        }
        return false;
    }
}
