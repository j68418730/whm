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
            'Dashboard' => [
                ['label' => 'Dashboard', 'href' => '/admin/dashboard', 'icon' => 'bi-speedometer2', 'match' => ['/admin', '/admin/dashboard']],
                ['label' => 'Activity Log', 'href' => '/admin/activity-log', 'icon' => 'bi-journal-text', 'match' => ['/admin/activity-log']],
                ['label' => 'System Status', 'href' => '/admin/monitoring', 'icon' => 'bi-graph-up', 'match' => ['/admin/monitoring']],
                ['label' => 'Notifications', 'href' => '/admin/notifications', 'icon' => 'bi-bell', 'match' => ['/admin/notifications']],
            ],
            'Clients & Accounts' => [
                ['label' => 'Accounts', 'href' => '/admin/account', 'icon' => 'bi-people', 'match' => ['/admin/account']],
                ['label' => 'Packages', 'href' => '/admin/packages', 'icon' => 'bi-box', 'match' => ['/admin/packages'], 'children' => [
                    ['label' => 'Package Categories', 'href' => '/admin/packages/categories', 'match' => ['/admin/packages/categories']],
                    ['label' => 'Feature Manager', 'href' => '/admin/userfeatures', 'match' => ['/admin/userfeatures']],
                ]],
                ['label' => 'Resellers', 'href' => '/admin/reseller', 'icon' => 'bi-arrow-repeat', 'match' => ['/admin/reseller']],
                ['label' => 'Clients', 'href' => '/admin/account/list', 'icon' => 'bi-person-vcard', 'match' => ['/admin/account/list']],
                ['label' => 'Members', 'href' => '/admin/account', 'icon' => 'bi-person-lines-fill', 'match' => ['/admin/account']],
                ['label' => 'User Groups', 'href' => '/admin/roles', 'icon' => 'bi-people-fill', 'match' => ['/admin/roles']],
            ],
            'Hosting Services' => [
                ['label' => 'Services', 'href' => '/admin/server', 'icon' => 'bi-hdd-network', 'match' => ['/admin/server']],
                ['label' => 'DNS', 'href' => '/admin/dns', 'icon' => 'bi-globe', 'match' => ['/admin/dns']],
                ['label' => 'Email', 'href' => '/admin/email', 'icon' => 'bi-envelope', 'match' => ['/admin/email']],
                ['label' => 'Databases', 'href' => '/admin/mysql', 'icon' => 'bi-database', 'match' => ['/admin/mysql']],
                ['label' => 'FTP', 'href' => '/admin/ftp', 'icon' => 'bi-upload', 'match' => ['/admin/ftp']],
                ['label' => 'Web Hosting', 'href' => '/admin/server', 'icon' => 'bi-window-stack', 'match' => ['/admin/server']],
                ['label' => 'VPS Hosting', 'href' => '/admin/container', 'icon' => 'bi-cpu', 'match' => ['/admin/container']],
                ['label' => 'Dedicated Servers', 'href' => '/admin/serverconfig', 'icon' => 'bi-server', 'match' => ['/admin/serverconfig', '/admin/tweak']],
                ['label' => 'SSL Certificates', 'href' => '/admin/ssl', 'icon' => 'bi-shield-check', 'match' => ['/admin/ssl']],
            ],
            'Billing & Commerce' => [
                ['label' => 'Billing', 'href' => '/admin/billing', 'icon' => 'bi-credit-card', 'match' => ['/admin/billing']],
                ['label' => 'Products', 'href' => '/admin/billing/products', 'icon' => 'bi-box-seam', 'match' => ['/admin/billing/products']],
                ['label' => 'Orders', 'href' => '/admin/billing/orders', 'icon' => 'bi-cart', 'match' => ['/admin/billing/orders']],
                ['label' => 'Invoices', 'href' => '/admin/billing/invoices', 'icon' => 'bi-file-text', 'match' => ['/admin/billing/invoices']],
                ['label' => 'Transactions', 'href' => '/admin/billing/payments', 'icon' => 'bi-cash-coin', 'match' => ['/admin/billing/payments']],
                ['label' => 'Coupons', 'href' => '/admin/billing/coupons', 'icon' => 'bi-ticket-perforated', 'match' => ['/admin/billing/coupons']],
                ['label' => 'Taxes', 'href' => '/admin/billing/taxes', 'icon' => 'bi-percent', 'match' => ['/admin/billing/taxes']],
                ['label' => 'Payment Gateways', 'href' => '/admin/gateways', 'icon' => 'bi-plug', 'match' => ['/admin/gateways']],
            ],
            'Support Center' => [
                ['label' => 'Support Center', 'href' => '/admin/support', 'icon' => 'bi-headset', 'match' => ['/admin/support']],
                ['label' => 'Live Chat', 'href' => '/admin/livechat', 'icon' => 'bi-chat-dots', 'match' => ['/admin/livechat']],
                ['label' => 'Reviews', 'href' => '/admin/reviews', 'icon' => 'bi-star', 'match' => ['/admin/reviews']],
                ['label' => 'Knowledge Base', 'href' => '/admin/support/kb', 'icon' => 'bi-book', 'match' => ['/admin/support/kb']],
                ['label' => 'Downloads', 'href' => '/admin/installers', 'icon' => 'bi-download', 'match' => ['/admin/installers']],
                ['label' => 'Announcements', 'href' => '/admin/support/announcements', 'icon' => 'bi-megaphone', 'match' => ['/admin/support/announcements']],
                ['label' => 'FAQ', 'href' => '/admin/support/kb', 'icon' => 'bi-question-circle', 'match' => ['/admin/support/kb']],
            ],
            'Radio & Streaming' => [
                ['label' => 'Radio Dashboard', 'href' => '/admin/radio_dashboard', 'icon' => 'bi-broadcast', 'match' => ['/admin/radio_dashboard']],
                ['label' => 'Streams', 'href' => '/admin/streams', 'icon' => 'bi-music-note-list', 'match' => ['/admin/streams']],
                ['label' => 'DJ Accounts', 'href' => '/admin/djs', 'icon' => 'bi-mic', 'match' => ['/admin/djs']],
                ['label' => 'AutoDJ', 'href' => '/admin/autodj', 'icon' => 'bi-robot', 'match' => ['/admin/autodj']],
                ['label' => 'Radio Settings', 'href' => '/admin/radiosettings', 'icon' => 'bi-gear', 'match' => ['/admin/radiosettings']],
            ],
            'Game Servers' => [
                ['label' => 'Game Servers', 'href' => '/admin/games', 'icon' => 'bi-controller', 'match' => ['/admin/games'], 'if' => '\Plugins\GameServers\GameServersPlugin', 'children' => [
                    ['label' => 'Game Catalog', 'href' => '/admin/games/catalog', 'match' => ['/admin/games/catalog']],
                    ['label' => 'Server Types', 'href' => '/admin/games/catalog', 'match' => ['/admin/games/catalog']],
                    ['label' => 'Game Templates', 'href' => '/admin/games/catalog', 'match' => ['/admin/games/catalog']],
                    ['label' => 'Backups', 'href' => '/admin/backup', 'match' => ['/admin/backup']],
                    ['label' => 'Server Monitoring', 'href' => '/admin/monitoring', 'match' => ['/admin/monitoring']],
                ]],
            ],
            'Website Builder' => [
                ['label' => 'Website Builder', 'href' => '/admin/websitebuilder', 'icon' => 'bi-building', 'match' => ['/admin/websitebuilder'], 'if' => '\Plugins\WebsiteBuilder\WebsiteBuilderPlugin'],
                ['label' => 'Templates', 'href' => '/admin/themes', 'icon' => 'bi-file-earmark-richtext', 'match' => ['/admin/themes']],
                ['label' => 'Themes', 'href' => '/admin/themes', 'icon' => 'bi-palette', 'match' => ['/admin/themes']],
                ['label' => 'Pages', 'href' => '/admin/websitebuilder', 'icon' => 'bi-file-earmark', 'match' => ['/admin/websitebuilder'], 'if' => '\Plugins\WebsiteBuilder\WebsiteBuilderPlugin'],
                ['label' => 'Menus', 'href' => '/admin/websitebuilder', 'icon' => 'bi-menu-button-wide', 'match' => ['/admin/websitebuilder'], 'if' => '\Plugins\WebsiteBuilder\WebsiteBuilderPlugin'],
                ['label' => 'Widgets', 'href' => '/admin/widgets', 'icon' => 'bi-grid-3x3-gap', 'match' => ['/admin/widgets']],
            ],
            'API' => [
                ['label' => 'API Keys', 'href' => '/admin/api', 'icon' => 'bi-key', 'match' => ['/admin/api']],
                ['label' => 'API Logs', 'href' => '/admin/api/logs', 'icon' => 'bi-journal-code', 'match' => ['/admin/api/logs']],
                ['label' => 'Webhooks', 'href' => '/admin/api/webhooks', 'icon' => 'bi-link-45deg', 'match' => ['/admin/api/webhooks']],
                ['label' => 'Integrations', 'href' => '/admin/api/permissions', 'icon' => 'bi-diagram-3', 'match' => ['/admin/api/permissions']],
            ],
            'Security' => [
                ['label' => 'Security Center', 'href' => '/admin/security', 'icon' => 'bi-shield-check', 'match' => ['/admin/security']],
                ['label' => 'Firewall', 'href' => '/admin/firewall', 'icon' => 'bi-fire', 'match' => ['/admin/firewall']],
                ['label' => 'Login Logs', 'href' => '/admin/activity-log', 'icon' => 'bi-journal-arrow-down', 'match' => ['/admin/activity-log']],
                ['label' => 'Audit Logs', 'href' => '/admin/activity-log', 'icon' => 'bi-journal-check', 'match' => ['/admin/activity-log']],
                ['label' => 'IP Blocking', 'href' => '/admin/ipblocker', 'icon' => 'bi-slash-circle', 'match' => ['/admin/ipblocker']],
                ['label' => '2FA Management', 'href' => '/admin/twofactor', 'icon' => 'bi-phone-lock', 'match' => ['/admin/twofactor']],
            ],
            'System' => [
                ['label' => 'Settings', 'href' => '/admin/settings', 'icon' => 'bi-gear', 'match' => ['/admin/settings']],
                ['label' => 'Theme Builder', 'href' => '/admin/theme', 'icon' => 'bi-palette2', 'match' => ['/admin/theme']],
                ['label' => 'Theme Manager', 'href' => '/admin/themes', 'icon' => 'bi-palette', 'match' => ['/admin/themes']],
                ['label' => 'Theme Settings', 'href' => '/admin/theme', 'icon' => 'bi-palette2', 'match' => ['/admin/theme']],
                ['label' => 'Plugins', 'href' => '/admin/plugins', 'icon' => 'bi-puzzle', 'match' => ['/admin/plugins']],
                ['label' => 'Licensing', 'href' => '/admin/licensing', 'icon' => 'bi-shield-lock', 'match' => ['/admin/licensing']],
                ['label' => 'Todo Board', 'href' => '/admin/todo', 'icon' => 'bi-list-check', 'match' => ['/admin/todo']],
                ['label' => 'Process Manager', 'href' => '/admin/process-manager', 'icon' => 'bi-cpu', 'match' => ['/admin/process-manager']],
                ['label' => 'Cron Jobs', 'href' => '/admin/cron', 'icon' => 'bi-clock-history', 'match' => ['/admin/cron']],
                ['label' => 'Queue Manager', 'href' => '/admin/automation', 'icon' => 'bi-lightning-charge', 'match' => ['/admin/automation']],
                ['label' => 'Filesystem', 'href' => '/admin/filesystem', 'icon' => 'bi-folder2-open', 'match' => ['/admin/filesystem']],
                ['label' => 'Backup Manager', 'href' => '/admin/backup', 'icon' => 'bi-archive', 'match' => ['/admin/backup']],
            ],
            'Administration' => [
                ['label' => 'Admins', 'href' => '/admin/admins', 'icon' => 'bi-person-gear', 'match' => ['/admin/admins']],
                ['label' => 'Roles', 'href' => '/admin/roles', 'icon' => 'bi-person-badge', 'match' => ['/admin/roles']],
                ['label' => 'Permissions', 'href' => '/admin/api/permissions', 'icon' => 'bi-lock', 'match' => ['/admin/api/permissions']],
                ['label' => 'Activity Logs', 'href' => '/admin/activity-log', 'icon' => 'bi-journal-text', 'match' => ['/admin/activity-log']],
                ['label' => 'System Logs', 'href' => '/admin/monitoring', 'icon' => 'bi-journal-richtext', 'match' => ['/admin/monitoring']],
            ],
        ];
    }
}

if (!function_exists('render_admin_menu_sections')) {
    function render_admin_menu_sections(string $currentUrl): string
    {
        $html = '';
        foreach (admin_menu_sections() as $section => $items) {
            $html .= '<div class="nav-section" data-section="' . htmlspecialchars(strtolower($section), ENT_QUOTES, 'UTF-8') . '">';
            $html .= '<div class="nav-label">' . htmlspecialchars($section, ENT_QUOTES, 'UTF-8') . '</div>';
            foreach ($items as $item) {
                if (isset($item['if']) && $item['if'] && !admin_plugin_enabled($item['if'])) {
                    continue;
                }
                $matches = $item['match'] ?? [$item['href']];
                $active = false;
                foreach ((array)$matches as $match) {
                    if ($match === '/admin') {
                        if ($currentUrl === '/admin' || $currentUrl === '/admin/' || $currentUrl === '/admin/dashboard' || $currentUrl === '/' ) {
                            $active = true;
                            break;
                        }
                        continue;
                    }
                    if ($match && str_starts_with($currentUrl, $match)) {
                        $active = true;
                        break;
                    }
                }
                $classes = 'nav-link' . ($active ? ' active' : '');
                $html .= '<a href="' . htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') . '" class="' . $classes . '">';
                if (!empty($item['icon'])) {
                    $html .= '<i class="bi ' . htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') . '"></i>';
                }
                $html .= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . '</a>';
                foreach (($item['children'] ?? []) as $child) {
                    $childMatches = $child['match'] ?? [$child['href']];
                    $childActive = false;
                    foreach ((array)$childMatches as $match) {
                        if ($match && str_starts_with($currentUrl, $match)) {
                            $childActive = true;
                            break;
                        }
                    }
                    $childClasses = 'nav-link' . ($childActive ? ' active' : '') . ' nav-child';
                    $childHref = $child['href'] ?? $item['href'];
                    $html .= '<a href="' . htmlspecialchars($childHref, ENT_QUOTES, 'UTF-8') . '" class="' . $childClasses . '" style="padding-left:30px;font-size:12px;opacity:.92">';
                    if (!empty($child['icon'])) {
                        $html .= '<i class="bi ' . htmlspecialchars($child['icon'], ENT_QUOTES, 'UTF-8') . '"></i>';
                    }
                    $html .= htmlspecialchars($child['label'], ENT_QUOTES, 'UTF-8') . '</a>';
                }
            }
            $html .= '</div>';
        }
        return $html;
    }
}
