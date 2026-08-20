<?php

if (!function_exists('user_menu_sections')) {
    function user_menu_sections($features = []): array
    {
        $f = (object)$features;
        $sections = [];

        $sections[] = ['label' => 'Dashboard', 'href' => '/user', 'icon' => '🏠', 'match' => ['/user']];

        $hasWeb = (int)($f->web ?? 0) || (int)($f->hosting ?? 0);
        $hasVps = (int)($f->vps ?? 0);

        if ($hasWeb || $hasVps) {
            $sections[] = ['label' => 'Hosting', 'href' => '/user/section/hosting', 'icon' => '🌐', 'match' => ['/user/services','/user/files','/user/ftp','/user/databases','/user/ssl','/user/cron','/user/usage','/user/git','/user/apps','/user/backup','/user/installer','/user/php-switcher','/user/section/hosting']];

            $sections[] = ['label' => 'Domains', 'href' => '/user/section/domains', 'icon' => '🌍', 'match' => ['/user/domains','/user/subdomains','/user/redirects','/user/section/domains']];

            if ((int)($f->email ?? 0)) {
                $sections[] = ['label' => 'Email', 'href' => '/user/section/email', 'icon' => '📧', 'match' => ['/user/email','/user/section/email']];
            }
        }

        if ((int)($f->radio ?? 0)) {
            $sections[] = ['label' => 'Radio', 'href' => '/user/radio', 'icon' => '📻', 'match' => ['/user/radio','/user/dj','/user/dj-manager','/user/dj-panel','/dj_panel.php','/user/stats','/user/public-djs','/user/section/radio']];
        }

        if ((int)($f->builder ?? 0)) {
            $sections[] = ['label' => 'Builder', 'href' => '/user/section/builder', 'icon' => '🏗️', 'match' => ['/user/websitebuilder','/user/builder','/user/section/builder']];
        }

        if ((int)($f->chat ?? 0)) {
            $sections[] = ['label' => 'Chat', 'href' => '/user/section/chat', 'icon' => '💬', 'match' => ['/user/chat','/user/section/chat']];
        }

        if ((int)($f->game ?? 0)) {
            $sections[] = ['label' => 'Games', 'href' => '/user/section/games', 'icon' => '🎮', 'match' => ['/user/games','/user/game','/user/section/games']];
        }

        $sections[] = ['label' => 'Billing', 'href' => '/user/section/billing', 'icon' => '💳', 'match' => ['/user/billing','/user/invoices','/user/section/billing']];

        $sections[] = ['label' => 'Support', 'href' => '/user/section/support', 'icon' => '🎫', 'match' => ['/user/tickets','/user/support','/user/section/support']];

        $sections[] = ['label' => 'Security', 'href' => '/user/security', 'icon' => '🛡️', 'match' => ['/user/security', '/user/ssl']];

        $sections[] = ['label' => 'Account', 'href' => '/user/profile', 'icon' => '👤', 'match' => ['/user/profile','/user/admins']];

        return $sections;
    }
}

if (!function_exists('render_user_sidebar')) {
    function render_user_sidebar(string $currentUrl, array $features = []): string
    {
        $html = '<nav class="sidebar-nav">';
        foreach (user_menu_sections($features) as $item) {
            $isActive = false;
            foreach ($item['match'] as $m) {
                if ($m && $currentUrl !== '/' && str_starts_with($currentUrl, $m)) { $isActive = true; break; }
            }
            if ($currentUrl === '/user' && $item['href'] === '/user') $isActive = true;
            $html .= '<a href="' . $item['href'] . '" class="nav-link ' . ($isActive ? 'active' : '') . '">'
                   . '<span class="icon">' . $item['icon'] . '</span> '
                   . '<span class="label">' . htmlspecialchars($item['label']) . '</span></a>';
        }
        $html .= '</nav>';
        return $html;
    }
}