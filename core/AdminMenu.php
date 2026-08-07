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
            ['label' => 'Hosting', 'href' => '/admin/section/hosting', 'icon' => '🌐', 'match' => ['/admin/email', '/admin/mysql', '/admin/ftp', '/admin/ssl', '/admin/backup', '/admin/cron', '/admin/server', '/admin/ssl/universal']],
            ['label' => 'Billing', 'href' => '/admin/section/billing', 'icon' => '💳', 'match' => ['/admin/billing', '/admin/gateways', '/admin/paypal']],
            ['label' => 'Support', 'href' => '/admin/section/support', 'icon' => '🎫', 'match' => ['/admin/support', '/admin/livechat', '/admin/reviews']],
            ['label' => 'Radio', 'href' => '/admin/section/radio', 'icon' => '📻', 'match' => ['/admin/radio_dashboard', '/admin/streams', '/admin/djs', '/admin/autodj', '/admin/radiosettings', '/admin/streaming', '/admin/port'],
                'children' => [
                    ['label' => 'Radio Dashboard', 'href' => '/admin/radio_dashboard', 'match' => ['/admin/radio_dashboard']],
                    ['label' => 'Streams', 'href' => '/admin/streams', 'match' => ['/admin/streams']],
                    ['label' => 'DJ Accounts', 'href' => '/admin/djs', 'match' => ['/admin/djs']],
                    ['label' => 'AutoDJ', 'href' => '/admin/autodj', 'match' => ['/admin/autodj']],
                    ['label' => 'Widgets', 'href' => '/admin/radio/widgets', 'match' => ['/admin/radio/widgets']],
                    ['label' => 'Radio Settings', 'href' => '/admin/radiosettings', 'match' => ['/admin/radiosettings']],
                ]],
            ['label' => 'Games', 'href' => '/admin/section/games', 'icon' => '🎮', 'match' => ['/admin/games']],
            ['label' => 'Builder', 'href' => '/admin/section/builder', 'icon' => '🏗️', 'match' => ['/admin/websitebuilder']],
            ['label' => 'Domains', 'href' => '/admin/section/domains', 'icon' => '🌍', 'match' => ['/admin/domains', '/admin/dns', '/admin/ip']],
            ['label' => 'Chat', 'href' => '/chatbox/admin-control.php', 'icon' => '💬', 'match' => ['/admin/livechat', '/admin/chat-dashboard', '/chatbox']],
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
            $hasChildren = !empty($item['children']);
            $childHtml = '';
            if ($hasChildren) {
                foreach ($item['children'] as $child) {
                    $cActive = false;
                    foreach ($child['match'] ?? [] as $cm) {
                        if ($cm && str_starts_with($currentUrl, $cm)) { $cActive = true; $isActive = true; break; }
                    }
                    $childHtml .= '<a href="' . htmlspecialchars($child['href']) . '" class="nav-child ' . ($cActive ? 'active' : '') . '">'
                               . '<span class="nav-icon">·</span> '
                               . '<span class="nav-label">' . htmlspecialchars($child['label']) . '</span></a>';
                }
            }
            $html .= '<a href="' . htmlspecialchars($item['href']) . '" class="nav-link ' . ($isActive ? 'active' : '') . '">'
                   . '<span class="nav-icon">' . $item['icon'] . '</span> '
                   . '<span class="nav-label">' . htmlspecialchars($item['label']) . '</span></a>';
            if ($childHtml) {
                $html .= '<div class="nav-children" style="' . ($isActive ? '' : 'display:none') . '">' . $childHtml . '</div>';
            }
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
