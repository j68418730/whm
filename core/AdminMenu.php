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
            '📊 DASHBOARD' => [
                ['label' => 'Dashboard', 'href' => '/admin/dashboard', 'icon' => 'bi-speedometer2', 'match' => ['/admin', '/admin/dashboard']],
                ['label' => 'Activity Feed', 'href' => '/admin/activity-log', 'icon' => 'bi-journal-text', 'match' => ['/admin/activity-log']],
                ['label' => 'System Status', 'href' => '/admin/monitoring', 'icon' => 'bi-graph-up', 'match' => ['/admin/monitoring']],
                ['label' => 'Notifications', 'href' => '/admin/notifications', 'icon' => 'bi-bell', 'match' => ['/admin/notifications']],
            ],
            '👥 ACCOUNT MANAGEMENT' => [
                ['label' => 'Accounts', 'href' => '/admin/account', 'icon' => 'bi-people', 'match' => ['/admin/account', '/admin/account/create', '/admin/account/show', '/admin/account/edit']],
                ['label' => 'Clients', 'href' => '/admin/account/list', 'icon' => 'bi-person-vcard', 'match' => ['/admin/account/list']],
                ['label' => 'Resellers', 'href' => '/admin/reseller', 'icon' => 'bi-arrow-repeat', 'match' => ['/admin/reseller']],
                ['label' => 'User Groups', 'href' => '/admin/roles', 'icon' => 'bi-people-fill', 'match' => ['/admin/roles']],
                ['label' => 'Packages', 'href' => '/admin/packages', 'icon' => 'bi-box', 'match' => ['/admin/packages'], 'children' => [
                    ['label' => 'Package Categories', 'href' => '/admin/packages/categories', 'match' => ['/admin/packages/categories']],
                    ['label' => 'Feature Manager', 'href' => '/admin/userfeatures', 'match' => ['/admin/userfeatures']],
                ]],
            ],
            '🌐 HOSTING SERVICES' => [
                ['label' => 'Services', 'href' => '/admin/server', 'icon' => 'bi-hdd-network', 'match' => ['/admin/server']],
                ['label' => 'DNS Manager', 'href' => '/admin/dns', 'icon' => 'bi-globe', 'match' => ['/admin/dns']],
                ['label' => 'Domain Manager', 'href' => '/admin/domains', 'icon' => 'bi-globe2', 'match' => ['/admin/domains']],
                ['label' => 'Nameservers', 'href' => '/admin/dns/nameservers', 'icon' => 'bi-diagram-2', 'match' => ['/admin/dns/nameservers']],
                ['label' => 'Email', 'href' => '/admin/email', 'icon' => 'bi-envelope', 'match' => ['/admin/email']],
                ['label' => 'Databases', 'href' => '/admin/mysql', 'icon' => 'bi-database', 'match' => ['/admin/mysql']],
                ['label' => 'FTP', 'href' => '/admin/ftp', 'icon' => 'bi-upload', 'match' => ['/admin/ftp']],
                ['label' => 'SSL Certificates', 'href' => '/admin/ssl', 'icon' => 'bi-shield-check', 'match' => ['/admin/ssl']],
                ['label' => 'Backups', 'href' => '/admin/backup', 'icon' => 'bi-archive', 'match' => ['/admin/backup']],
                ['label' => 'Cron Jobs', 'href' => '/admin/cron', 'icon' => 'bi-clock-history', 'match' => ['/admin/cron']],
                ['label' => 'Web Terminal', 'href' => '/admin/server/terminal', 'icon' => 'bi-terminal', 'match' => ['/admin/server/terminal']],
                ['label' => 'Resource Usage', 'href' => '/admin/server', 'icon' => 'bi-bar-chart', 'match' => ['/admin/server']],
            ],
            '💳 BILLING & COMMERCE' => [
                ['label' => 'Billing Dashboard', 'href' => '/admin/billing', 'icon' => 'bi-credit-card', 'match' => ['/admin/billing']],
                ['label' => 'Products', 'href' => '/admin/billing/products', 'icon' => 'bi-box-seam', 'match' => ['/admin/billing/products']],
                ['label' => 'Orders', 'href' => '/admin/billing/orders', 'icon' => 'bi-cart', 'match' => ['/admin/billing/orders']],
                ['label' => 'Invoices', 'href' => '/admin/billing/invoices', 'icon' => 'bi-file-text', 'match' => ['/admin/billing/invoices']],
                ['label' => 'Transactions', 'href' => '/admin/billing/payments', 'icon' => 'bi-cash-coin', 'match' => ['/admin/billing/payments']],
                ['label' => 'Credits', 'href' => '/admin/billing/credits', 'icon' => 'bi-wallet2', 'match' => ['/admin/billing/credits']],
                ['label' => 'Coupons', 'href' => '/admin/billing/coupons', 'icon' => 'bi-ticket-perforated', 'match' => ['/admin/billing/coupons']],
                ['label' => 'Taxes', 'href' => '/admin/billing/taxes', 'icon' => 'bi-percent', 'match' => ['/admin/billing/taxes']],
                ['label' => 'Refunds', 'href' => '/admin/billing/refunds', 'icon' => 'bi-arrow-return-left', 'match' => ['/admin/billing/refunds']],
                ['label' => 'Payment Gateways', 'href' => '/admin/gateways', 'icon' => 'bi-plug', 'match' => ['/admin/gateways']],
            ],
            '🎫 SUPPORT CENTER' => [
                ['label' => 'Support Center', 'href' => '/admin/support', 'icon' => 'bi-headset', 'match' => ['/admin/support']],
                ['label' => 'Tickets', 'href' => '/admin/support/tickets', 'icon' => 'bi-ticket', 'match' => ['/admin/support/tickets']],
                ['label' => 'Live Chat', 'href' => '/admin/livechat', 'icon' => 'bi-chat-dots', 'match' => ['/admin/livechat']],
                ['label' => 'Chat Dashboard', 'href' => '/admin/chat-dashboard', 'icon' => 'bi-chat-square-dots', 'match' => ['/admin/chat-dashboard']],
                ['label' => 'Knowledge Base', 'href' => '/admin/support/kb', 'icon' => 'bi-book', 'match' => ['/admin/support/kb']],
                ['label' => 'Announcements', 'href' => '/admin/support/announcements', 'icon' => 'bi-megaphone', 'match' => ['/admin/support/announcements']],
                ['label' => 'Reviews', 'href' => '/admin/reviews', 'icon' => 'bi-star', 'match' => ['/admin/reviews']],
                ['label' => 'Server Status', 'href' => '/admin/support/status', 'icon' => 'bi-heart-pulse', 'match' => ['/admin/support/status']],
            ],
            '📻 RADIO HOSTING' => [
                ['label' => 'Radio Dashboard', 'href' => '/admin/radio_dashboard', 'icon' => 'bi-broadcast', 'match' => ['/admin/radio_dashboard']],
                ['label' => 'Streams', 'href' => '/admin/streams', 'icon' => 'bi-music-note-list', 'match' => ['/admin/streams']],
                ['label' => 'DJ Accounts', 'href' => '/admin/djs', 'icon' => 'bi-mic', 'match' => ['/admin/djs']],
                ['label' => 'AutoDJ', 'href' => '/admin/autodj', 'icon' => 'bi-robot', 'match' => ['/admin/autodj']],
                ['label' => 'Radio Widgets', 'href' => '/admin/radio_dashboard', 'icon' => 'bi-window-stack', 'match' => ['/admin/radio_dashboard']],
                ['label' => 'Radio Settings', 'href' => '/admin/radiosettings', 'icon' => 'bi-gear', 'match' => ['/admin/radiosettings']],
            ],
            '🎮 GAME SERVERS' => [
                ['label' => 'Game Servers', 'href' => '/admin/games', 'icon' => 'bi-controller', 'match' => ['/admin/games']],
                ['label' => 'Slot Pricing', 'href' => '/admin/games/pricing', 'icon' => 'bi-cash-stack', 'match' => ['/admin/games/pricing']],
                ['label' => 'Packages', 'href' => '/admin/games/packages', 'icon' => 'bi-box-seam', 'match' => ['/admin/games/packages']],
                ['label' => 'Game Templates', 'href' => '/admin/games/templates', 'icon' => 'bi-file-earmark-zip', 'match' => ['/admin/games/templates']],
                ['label' => 'Settings', 'href' => '/admin/games/settings', 'icon' => 'bi-gear', 'match' => ['/admin/games/settings']],
            ],
            '🏗 WEBSITE BUILDER' => [
                ['label' => 'Dashboard', 'href' => '/admin/websitebuilder', 'icon' => 'bi-speedometer2', 'match' => ['/admin/websitebuilder'], 'if' => '\Plugins\WebsiteBuilder\WebsiteBuilderPlugin'],
                ['label' => 'Sites', 'href' => '/admin/websitebuilder/sites', 'icon' => 'bi-globe', 'match' => ['/admin/websitebuilder/sites'], 'if' => '\Plugins\WebsiteBuilder\WebsiteBuilderPlugin'],
                ['label' => 'Templates', 'href' => '/admin/websitebuilder/templates', 'icon' => 'bi-file-earmark-zip', 'match' => ['/admin/websitebuilder/templates'], 'if' => '\Plugins\WebsiteBuilder\WebsiteBuilderPlugin'],
                ['label' => 'Themes', 'href' => '/admin/websitebuilder/themes', 'icon' => 'bi-palette', 'match' => ['/admin/websitebuilder/themes'], 'if' => '\Plugins\WebsiteBuilder\WebsiteBuilderPlugin'],
            ],
            '🎨 APPEARANCE' => [
                ['label' => 'Theme Manager', 'href' => '/admin/themes', 'icon' => 'bi-palette', 'match' => ['/admin/themes']],
                ['label' => 'Theme Builder', 'href' => '/admin/theme', 'icon' => 'bi-palette2', 'match' => ['/admin/theme']],
            ],
            '🛒 STORE FRONT' => [
                ['label' => 'Hosting Packages', 'href' => '/admin/packages', 'icon' => 'bi-box', 'match' => ['/admin/packages']],
                ['label' => 'Billing Products', 'href' => '/admin/billing/products', 'icon' => 'bi-box-seam', 'match' => ['/admin/billing/products']],
                ['label' => 'Game Packages', 'href' => '/admin/games/packages', 'icon' => 'bi-controller', 'match' => ['/admin/games/packages']],
                ['label' => 'Payment Gateways', 'href' => '/admin/gateways', 'icon' => 'bi-plug', 'match' => ['/admin/gateways']],
                ['label' => 'Domain Check', 'href' => '/domain_check.php', 'icon' => 'bi-search', 'match' => ['/domain_check.php']],
                ['label' => 'Game Servers', 'href' => '/game-servers.php', 'icon' => 'bi-controller', 'match' => ['/game-servers.php']],
            ],
            '🔌 API & INTEGRATIONS' => [
                ['label' => 'API Keys', 'href' => '/admin/api', 'icon' => 'bi-key', 'match' => ['/admin/api']],
                ['label' => 'API Logs', 'href' => '/admin/api/logs', 'icon' => 'bi-journal-code', 'match' => ['/admin/api/logs']],
                ['label' => 'Webhooks', 'href' => '/admin/api/webhooks', 'icon' => 'bi-link-45deg', 'match' => ['/admin/api/webhooks']],
                ['label' => 'Integrations', 'href' => '/admin/api/permissions', 'icon' => 'bi-diagram-3', 'match' => ['/admin/api/permissions']],
            ],
            '🔒 SECURITY' => [
                ['label' => 'Security Center', 'href' => '/admin/security', 'icon' => 'bi-shield-check', 'match' => ['/admin/security']],
                ['label' => 'Firewall', 'href' => '/admin/firewall', 'icon' => 'bi-fire', 'match' => ['/admin/firewall']],
                ['label' => 'IP Blocking', 'href' => '/admin/ipblocker', 'icon' => 'bi-slash-circle', 'match' => ['/admin/ipblocker']],
                ['label' => '2FA Management', 'href' => '/admin/twofactor', 'icon' => 'bi-phone-lock', 'match' => ['/admin/twofactor']],
            ],
            '⚙️ SYSTEM' => [
                ['label' => 'Settings', 'href' => '/admin/settings', 'icon' => 'bi-gear', 'match' => ['/admin/settings']],
                ['label' => 'General', 'href' => '/admin/settings/general', 'icon' => 'bi-sliders', 'match' => ['/admin/settings/general']],
                ['label' => 'Company', 'href' => '/admin/settings/company', 'icon' => 'bi-building', 'match' => ['/admin/settings/company']],
                ['label' => 'SMTP', 'href' => '/admin/settings/smtp', 'icon' => 'bi-envelope-paper', 'match' => ['/admin/settings/smtp']],
                ['label' => 'Security', 'href' => '/admin/settings/security', 'icon' => 'bi-shield-lock', 'match' => ['/admin/settings/security']],
                ['label' => 'API', 'href' => '/admin/settings/api', 'icon' => 'bi-key', 'match' => ['/admin/settings/api']],
                ['label' => 'Localization', 'href' => '/admin/settings/localization', 'icon' => 'bi-translate', 'match' => ['/admin/settings/localization']],
                ['label' => 'Server Config', 'href' => '/admin/serverconfig', 'icon' => 'bi-server', 'match' => ['/admin/serverconfig']],
                ['label' => 'IP Management', 'href' => '/admin/ip', 'icon' => 'bi-ethernet', 'match' => ['/admin/ip']],
                ['label' => 'Licensing', 'href' => '/admin/licensing', 'icon' => 'bi-shield-lock', 'match' => ['/admin/licensing']],
                ['label' => 'Plugins', 'href' => '/admin/plugins', 'icon' => 'bi-puzzle', 'match' => ['/admin/plugins']],
                ['label' => 'One-Click Installer', 'href' => '/admin/installers', 'icon' => 'bi-download', 'match' => ['/admin/installers']],
                ['label' => 'Todo Board', 'href' => '/admin/todo', 'icon' => 'bi-list-check', 'match' => ['/admin/todo']],
                ['label' => 'Process Manager', 'href' => '/admin/process-manager', 'icon' => 'bi-cpu', 'match' => ['/admin/process-manager']],
                ['label' => 'Cron Jobs', 'href' => '/admin/cron', 'icon' => 'bi-clock-history', 'match' => ['/admin/cron']],
                ['label' => 'Queue Manager', 'href' => '/admin/automation', 'icon' => 'bi-lightning-charge', 'match' => ['/admin/automation']],
                ['label' => 'Filesystem', 'href' => '/admin/filesystem', 'icon' => 'bi-folder2-open', 'match' => ['/admin/filesystem']],
                ['label' => 'Backup Manager', 'href' => '/admin/backup', 'icon' => 'bi-archive', 'match' => ['/admin/backup']],
            ],
            '👑 ADMINISTRATION' => [
                ['label' => 'Admins', 'href' => '/admin/admins', 'icon' => 'bi-person-gear', 'match' => ['/admin/admins']],
                ['label' => 'Roles', 'href' => '/admin/roles', 'icon' => 'bi-person-badge', 'match' => ['/admin/roles']],
                ['label' => 'Activity Logs', 'href' => '/admin/activity-log', 'icon' => 'bi-journal-text', 'match' => ['/admin/activity-log']],
            ],
        ];
    }
}

if (!function_exists('render_admin_menu_sections')) {
    function render_admin_menu_sections(string $currentUrl): string
    {
        $html = '';
        foreach (admin_menu_sections() as $section => $items) {
            $sectionSlug = strtolower(preg_replace('/[^a-z0-9\s-]/', '', strip_tags($section)));
            $sectionSlug = trim(preg_replace('/\s+/', '-', $sectionSlug));
            $html .= '<div class="nav-section" data-section="' . htmlspecialchars($sectionSlug, ENT_QUOTES, 'UTF-8') . '">';
            $html .= '<div class="nav-label">' . htmlspecialchars($section, ENT_QUOTES, 'UTF-8') . '</div>';
            foreach ($items as $item) {
                if (isset($item['if']) && $item['if'] && !admin_plugin_enabled($item['if'])) {
                    continue;
                }
                $matches = $item['match'] ?? [$item['href']];
                $active = false;
                foreach ((array)$matches as $match) {
                    if ($match === '/admin') {
                        if (in_array($currentUrl, ['/admin', '/admin/', '/admin/dashboard', '/'])) {
                            $active = true; break;
                        }
                        continue;
                    }
                    if ($match && str_starts_with($currentUrl, $match)) {
                        $active = true; break;
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
                            $childActive = true; break;
                        }
                    }
                    $childClasses = 'nav-link' . ($childActive ? ' active' : '') . ' nav-child';
                    $html .= '<a href="' . htmlspecialchars($child['href'] ?? $item['href'], ENT_QUOTES, 'UTF-8') . '" class="' . $childClasses . '" style="padding-left:30px;font-size:12px;opacity:.92">';
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
