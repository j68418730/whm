<?php

if (!function_exists('user_menu_sections')) {
    function user_menu_sections($features = []): array
    {
        $f = (object)$features;
        $sections = [];

        $sections[] = ['label' => 'Dashboard', 'href' => '/user', 'icon' => '🏠', 'match' => ['/user']];

        if (!isset($f->web) || $f->web)
            $sections[] = ['label' => 'Hosting', 'href' => '/user/section/hosting', 'icon' => '🌐', 'match' => ['/user/services','/user/files','/user/ftp','/user/databases','/user/ssl','/user/cron','/user/usage','/user/git','/user/apps','/user/backup','/user/installer','/user/section/hosting']];

        $sections[] = ['label' => 'Domains', 'href' => '/user/section/domains', 'icon' => '🌍', 'match' => ['/user/domains','/user/subdomains','/user/redirects','/user/section/domains']];

        if (!isset($f->email_accounts) || $f->email_accounts != 0)
            $sections[] = ['label' => 'Email', 'href' => '/user/section/email', 'icon' => '📧', 'match' => ['/user/email','/user/section/email']];

        if (!empty($f->radio) || !empty($f->icecast))
            $sections[] = ['label' => 'Radio', 'href' => '/user/radio', 'icon' => '📻', 'match' => ['/user/radio','/user/dj','/user/dj-manager','/dj_panel.php','/user/stats','/user/public-djs','/user/section/radio']];

        if (!empty($f->builder))
            $sections[] = ['label' => 'Builder', 'href' => '/user/section/builder', 'icon' => '🏗️', 'match' => ['/user/websitebuilder','/user/builder','/user/section/builder']];

        if (!empty($f->chatbox) || !empty($f->livechat))
            $sections[] = ['label' => 'Chat', 'href' => '/user/section/chat', 'icon' => '💬', 'match' => ['/user/chat','/user/section/chat']];

        if (!empty($f->game))
            $sections[] = ['label' => 'Games', 'href' => '/user/section/games', 'icon' => '🎮', 'match' => ['/user/games','/user/section/games']];

        $sections[] = ['label' => 'Billing', 'href' => '/user/section/billing', 'icon' => '💳', 'match' => ['/user/billing','/user/invoices','/user/section/billing']];

        $sections[] = ['label' => 'Support', 'href' => '/user/section/support', 'icon' => '🎫', 'match' => ['/user/tickets','/user/support','/user/section/support']];

        $sections[] = ['label' => 'Account', 'href' => '/user/profile', 'icon' => '👤', 'match' => ['/user/profile','/user/security','/user/admins']];

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
