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
    /**
     * Reseller-style admin menu: clean groups (nav-label), one icon per link,
     * no duplicates, no inline style hacks.
     * item: ['label','href','icon' (bi class),'match' (active prefixes),'ext' (opens external)]
     */
    function admin_menu_sections(): array
    {
        return [
            ['group' => 'Main', 'items' => [
                ['label' => 'Dashboard', 'href' => '/admin/dashboard', 'icon' => 'bi-speedometer2', 'match' => ['/admin/dashboard', '/admin/activity-log', '/admin/monitoring', '/admin/notifications']],
                ['label' => 'Server Overview', 'href' => '/admin/server', 'icon' => 'bi-server', 'match' => ['/admin/server']],
                ['label' => 'Server Health', 'href' => '/admin/server/health', 'icon' => 'bi-heart-pulse', 'match' => ['/admin/server/health']],
            ]],
            ['group' => 'Accounts', 'items' => [
                ['label' => 'Account Functions', 'href' => '/admin/account', 'icon' => 'bi-people', 'match' => ['/admin/account']],
                ['label' => 'Packages', 'href' => '/admin/packages', 'icon' => 'bi-box-seam', 'match' => ['/admin/packages']],
                ['label' => 'Resellers', 'href' => '/admin/reseller', 'icon' => 'bi-diagram-3', 'match' => ['/admin/reseller']],
                ['label' => 'Feature Manager', 'href' => '/admin/userfeatures', 'icon' => 'bi-list-check', 'match' => ['/admin/userfeatures']],
                ['label' => 'Admins', 'href' => '/admin/admins', 'icon' => 'bi-person-badge', 'match' => ['/admin/admins']],
            ]],
            ['group' => 'Hosting', 'items' => [
                ['label' => 'DNS Zones', 'href' => '/admin/dns', 'icon' => 'bi-globe2', 'match' => ['/admin/dns']],
                ['label' => 'Email', 'href' => '/admin/email', 'icon' => 'bi-envelope', 'match' => ['/admin/email']],
                ['label' => 'Webmail', 'href' => 'https://planet-hosts.com:2097/', 'icon' => 'bi-envelope-open', 'match' => [], 'ext' => true],
                ['label' => 'Databases', 'href' => '/admin/mysql', 'icon' => 'bi-database', 'match' => ['/admin/mysql']],
                ['label' => 'FTP', 'href' => '/admin/ftp', 'icon' => 'bi-folder2', 'match' => ['/admin/ftp']],
                ['label' => 'IP Management', 'href' => '/admin/ip', 'icon' => 'bi-ethernet', 'match' => ['/admin/ip']],
                ['label' => 'Backups', 'href' => '/admin/backup', 'icon' => 'bi-cloud-arrow-down', 'match' => ['/admin/backup']],
                ['label' => 'One-Click Installer', 'href' => '/admin/installers', 'icon' => 'bi-download', 'match' => ['/admin/installers']],
            ]],
            ['group' => 'Billing', 'items' => [
                ['label' => 'Billing', 'href' => '/admin/billing', 'icon' => 'bi-cash-stack', 'match' => ['/admin/billing']],
                ['label' => 'Payment Gateways', 'href' => '/admin/gateways', 'icon' => 'bi-credit-card-2-front', 'match' => ['/admin/gateways']],
                ['label' => 'PayPal', 'href' => '/admin/paypal', 'icon' => 'bi-paypal', 'match' => ['/admin/paypal']],
            ]],
            ['group' => 'Support & Chat', 'items' => [
                ['label' => 'Support Center', 'href' => '/admin/support', 'icon' => 'bi-life-preserver', 'match' => ['/admin/support']],
                ['label' => 'Live Chat', 'href' => '/admin/livechat', 'icon' => 'bi-chat-dots', 'match' => ['/admin/livechat']],
                ['label' => 'Chat Dashboard', 'href' => '/admin/chat-dashboard', 'icon' => 'bi-chat-square-text', 'match' => ['/admin/chat-dashboard']],
                ['label' => 'Chat Admin', 'href' => '/chatbox/admin.php', 'icon' => 'bi-box-arrow-up-right', 'match' => ['/chatbox'], 'ext' => true],
                ['label' => 'Reviews', 'href' => '/admin/reviews', 'icon' => 'bi-star', 'match' => ['/admin/reviews']],
            ]],
            ['group' => 'Radio', 'items' => [
                ['label' => 'Radio Dashboard', 'href' => '/admin/radio_dashboard', 'icon' => 'bi-broadcast', 'match' => ['/admin/radio_dashboard']],
                ['label' => 'Streams', 'href' => '/admin/streams', 'icon' => 'bi-music-note-beamed', 'match' => ['/admin/streams']],
                ['label' => 'DJ Accounts', 'href' => '/admin/djs', 'icon' => 'bi-mic', 'match' => ['/admin/djs']],
                ['label' => 'DJ Ports', 'href' => '/admin/dj/ports', 'icon' => 'bi-plug', 'match' => ['/admin/dj/ports']],
                ['label' => 'DJ History', 'href' => '/admin/dj/connections', 'icon' => 'bi-clock-history', 'match' => ['/admin/dj/connections']],
                ['label' => 'AutoDJ', 'href' => '/admin/autodj', 'icon' => 'bi-disc', 'match' => ['/admin/autodj']],
                ['label' => 'Radio Downloads', 'href' => '/admin/radio/downloads', 'icon' => 'bi-cloud-download', 'match' => ['/admin/radio/downloads']],
                ['label' => 'Radio Settings', 'href' => '/admin/radiosettings', 'icon' => 'bi-gear', 'match' => ['/admin/radiosettings']],
            ]],
            ['group' => 'Games', 'items' => [
                ['label' => 'Game Servers', 'href' => '/admin/games', 'icon' => 'bi-controller', 'match' => ['/admin/games']],
            ]],
            ['group' => 'Builder', 'items' => [
                ['label' => 'Website Builder', 'href' => '/admin/websitebuilder', 'icon' => 'bi-window-sidebar', 'match' => ['/admin/websitebuilder']],
            ], 'require_plugin' => '\\Plugins\\WebsiteBuilder\\WebsiteBuilderPlugin'],
            ['group' => 'Security', 'items' => [
                ['label' => 'Security Center', 'href' => '/admin/security', 'icon' => 'bi-shield-lock', 'match' => ['/admin/security', '/admin/firewall', '/admin/ipblocker', '/admin/twofactor']],
            ]],
            ['group' => 'Domains', 'items' => [
                ['label' => 'Domains & DNS', 'href' => '/admin/section/domains', 'icon' => 'bi-globe-americas', 'match' => ['/admin/domains', '/admin/dns', '/admin/ip', '/admin/section/domains']],
            ]],
            ['group' => 'System', 'items' => [
                ['label' => 'Server Config', 'href' => '/admin/serverconfig', 'icon' => 'bi-sliders', 'match' => ['/admin/serverconfig']],
                ['label' => 'Tweak Settings', 'href' => '/admin/tweak', 'icon' => 'bi-clipboard-data', 'match' => ['/admin/tweak']],
                ['label' => 'Apache', 'href' => '/admin/apache', 'icon' => 'bi-hdd-network', 'match' => ['/admin/apache']],
                ['label' => 'PHP Manager', 'href' => '/admin/php', 'icon' => 'bi-filetype-php', 'match' => ['/admin/php']],
                ['label' => 'PHP Version', 'href' => '/admin/php-switcher', 'icon' => 'bi-arrow-left-right', 'match' => ['/admin/php-switcher']],
                ['label' => 'Process Manager', 'href' => '/admin/process-manager', 'icon' => 'bi-cpu', 'match' => ['/admin/process-manager']],
                ['label' => 'Terminal', 'href' => '/admin/server/terminal', 'icon' => 'bi-terminal', 'match' => ['/admin/server/terminal', '/admin/terminal']],
                ['label' => 'Cron', 'href' => '/admin/cron', 'icon' => 'bi-clock', 'match' => ['/admin/cron']],
                ['label' => 'Automation', 'href' => '/admin/automation', 'icon' => 'bi-robot', 'match' => ['/admin/automation']],
                ['label' => 'Plugins', 'href' => '/admin/plugins', 'icon' => 'bi-puzzle', 'match' => ['/admin/plugins']],
                ['label' => 'Theme', 'href' => '/admin/theme', 'icon' => 'bi-palette', 'match' => ['/admin/theme', '/admin/themes']],
                ['label' => 'Settings', 'href' => '/admin/settings', 'icon' => 'bi-gear', 'match' => ['/admin/settings']],
                ['label' => 'Licensing', 'href' => '/admin/licensing', 'icon' => 'bi-award', 'match' => ['/admin/licensing']],
                ['label' => 'ToDo List', 'href' => '/admin/todo', 'icon' => 'bi-check2-square', 'match' => ['/admin/todo']],
            ]],
            ['group' => 'API', 'items' => [
                ['label' => 'API Keys', 'href' => '/admin/api', 'icon' => 'bi-key', 'match' => ['/admin/api']],
                ['label' => 'Webhooks', 'href' => '/admin/api/webhooks', 'icon' => 'bi-webhook', 'match' => ['/admin/api/webhooks']],
                ['label' => 'API Docs', 'href' => '/admin/api/docs', 'icon' => 'bi-book', 'match' => ['/admin/api/docs']],
            ]],
        ];
    }
}

if (!function_exists('render_admin_menu_sections')) {
    function render_admin_menu_sections(string $currentUrl): string
    {
        $html = '';
        foreach (admin_menu_sections() as $section) {
            if (!empty($section['require_plugin']) && !class_exists($section['require_plugin'])) continue;
            $groupHtml = '';
            $groupActive = false;
            foreach (($section['items'] ?? []) as $item) {
                $active = false;
                foreach ($item['match'] ?? [] as $m) {
                    if ($m && str_starts_with($currentUrl, $m)) { $active = true; break; }
                }
                if ($active) $groupActive = true;
                $ext = !empty($item['ext']);
                $groupHtml .= '<a href="' . htmlspecialchars($item['href']) . '"'
                    . ($ext ? ' target="_blank"' : '')
                    . ' class="nav-link ' . ($active ? 'active' : '') . '">'
                    . '<i class="bi ' . htmlspecialchars($item['icon']) . '"></i> '
                    . htmlspecialchars($item['label']) . ($ext ? ' <span class="nav-ext">↗</span>' : '') . '</a>';
            }
            $html .= '<div class="nav-section' . ($groupActive ? ' open' : '') . '">'
                   . '<div class="nav-label">' . htmlspecialchars($section['group']) . '</div>'
                   . $groupHtml
                   . '</div>';
        }
        return $html;
    }
}

if (!function_exists('admin_menu_section_active')) {
    function admin_menu_section_active(string $label): bool
    {
        $url = $_SERVER['REQUEST_URI'] ?? '';
        foreach (admin_menu_sections() as $section) {
            if (($section['group'] ?? '') !== $label) continue;
            foreach (($section['items'] ?? []) as $item) {
                foreach ($item['match'] ?? [] as $m) {
                    if ($m && str_starts_with($url, $m)) return true;
                }
            }
        }
        return false;
    }
}
