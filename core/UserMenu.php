<?php
/**
 * Feature-aware client menu for Planet-Hosts
 * Every menu item is gated by package feature permissions.
 */

if (!function_exists('user_menu_items')) {
    function user_menu_items($features): array
    {
        $f = (object)$features;

        $items = [];

        // Dashboard - always shown
        $items[] = ['label' => 'Dashboard', 'href' => '/user', 'icon' => '🏠', 'section' => 'home'];

        // Hosting - if web hosting enabled
        if (!isset($f->web) || $f->web) {
            $items[] = ['label' => 'My Services', 'href' => '/user/services', 'icon' => '🖥', 'section' => 'hosting'];
            $items[] = ['label' => 'File Manager', 'href' => '/user/files', 'icon' => '📁', 'section' => 'hosting'];
            if (!isset($f->ftp_accounts) || $f->ftp_accounts != 0)
                $items[] = ['label' => 'FTP Accounts', 'href' => '/user/ftp', 'icon' => '📤', 'section' => 'hosting'];
            if (!isset($f->databases) || $f->databases != 0)
                $items[] = ['label' => 'Databases', 'href' => '/user/databases', 'icon' => '🗄️', 'section' => 'hosting'];
            if (!isset($f->databases) || $f->databases != 0)
                $items[] = ['label' => 'phpMyAdmin', 'href' => '/pma_autologin.php', 'icon' => '🐘', 'section' => 'hosting', 'external' => true];
            if (!isset($f->ssl_allowed) || $f->ssl_allowed)
                $items[] = ['label' => 'SSL Certificates', 'href' => '/user/ssl', 'icon' => '🔒', 'section' => 'hosting'];
            if (!isset($f->cron_jobs) || $f->cron_jobs)
                $items[] = ['label' => 'Cron Jobs', 'href' => '/user/cron', 'icon' => '⏰', 'section' => 'hosting'];
            $items[] = ['label' => 'Resource Usage', 'href' => '/user/usage', 'icon' => '📊', 'section' => 'hosting'];
            if (!isset($f->git_access) || $f->git_access)
                $items[] = ['label' => 'Git Deployments', 'href' => '/user/git', 'icon' => '🔀', 'section' => 'hosting'];
            if (!isset($f->nodejs) || $f->nodejs)
                $items[] = ['label' => 'Node.js Apps', 'href' => '/user/apps/node', 'icon' => '🟢', 'section' => 'hosting'];
            if (!isset($f->python) || $f->python)
                $items[] = ['label' => 'Python Apps', 'href' => '/user/apps/python', 'icon' => '🐍', 'section' => 'hosting'];
            if (!isset($f->terminal) || $f->terminal)
                $items[] = ['label' => 'Terminal', 'href' => '/user/terminal', 'icon' => '💻', 'section' => 'hosting'];
            if (!isset($f->backups) || $f->backups)
                $items[] = ['label' => 'Backups', 'href' => '/user/backup', 'icon' => '💾', 'section' => 'hosting'];
        }

        // Domains
        $items[] = ['label' => 'My Domains', 'href' => '/user/domains', 'icon' => '🌍', 'section' => 'domains'];
        $items[] = ['label' => 'Subdomains', 'href' => '/user/subdomains', 'icon' => '🔗', 'section' => 'domains'];
        $items[] = ['label' => 'Redirects', 'href' => '/user/redirects', 'icon' => '↪️', 'section' => 'domains'];

        // Email - if email accounts enabled
        if (!isset($f->email_accounts) || $f->email_accounts != 0) {
            $items[] = ['label' => 'Email Accounts', 'href' => '/user/email', 'icon' => '📧', 'section' => 'email'];
            $items[] = ['label' => 'Webmail', 'href' => '/webmail_autologin.php', 'icon' => '📨', 'section' => 'email', 'external' => true];
        }

        // Radio
        if (!empty($f->radio) || !empty($f->icecast)) {
            $items[] = ['label' => 'Radio Dashboard', 'href' => '/user/dj-manager', 'icon' => '📻', 'section' => 'radio'];
            $items[] = ['label' => 'Streams', 'href' => '/user/dj-manager', 'icon' => '🎵', 'section' => 'radio'];
            $items[] = ['label' => 'AutoDJ', 'href' => '/user/dj-manager', 'icon' => '🤖', 'section' => 'radio'];
            $items[] = ['label' => 'DJ Accounts', 'href' => '/user/dj-manager', 'icon' => '🎤', 'section' => 'radio'];
            $items[] = ['label' => 'Song Requests', 'href' => '/user/dj-manager', 'icon' => '📝', 'section' => 'radio'];
            $items[] = ['label' => 'Listener Stats', 'href' => '/user/stats', 'icon' => '📈', 'section' => 'radio'];
        }

        // Games
        if (!empty($f->game)) {
            $items[] = ['label' => 'Game Servers', 'href' => '/user/games', 'icon' => '🎮', 'section' => 'games'];
            $items[] = ['label' => 'Console', 'href' => '/user/games', 'icon' => '🖥️', 'section' => 'games'];
            $items[] = ['label' => 'File Manager', 'href' => '/user/files', 'icon' => '📁', 'section' => 'games'];
            $items[] = ['label' => 'Backups', 'href' => '/user/backup', 'icon' => '💾', 'section' => 'games'];
        }

        // Website Builder
        if (!empty($f->builder)) {
            $items[] = ['label' => 'My Websites', 'href' => '/user/websitebuilder', 'icon' => '🏗️', 'section' => 'builder'];
            $items[] = ['label' => 'Themes', 'href' => '/user/websitebuilder/themes', 'icon' => '🎨', 'section' => 'builder'];
            $items[] = ['label' => 'SEO Tools', 'href' => '/user/websitebuilder/seo', 'icon' => '📈', 'section' => 'builder'];
        }

        // Live Chat
        if (!empty($f->livechat)) {
            $items[] = ['label' => 'Dashboard', 'href' => '/user/chat', 'icon' => '💬', 'section' => 'chat'];
            $items[] = ['label' => 'Operators', 'href' => '/chatbox/admin.php?action=operators', 'icon' => '👥', 'section' => 'chat', 'external' => true];
            $items[] = ['label' => 'History', 'href' => '/chatbox/admin.php?action=history', 'icon' => '📋', 'section' => 'chat', 'external' => true];
            $items[] = ['label' => 'Departments', 'href' => '/chatbox/admin.php?action=departments', 'icon' => '🏢', 'section' => 'chat', 'external' => true];
            $items[] = ['label' => 'Embed Widget', 'href' => '/chatbox/admin.php?action=widget', 'icon' => '🔌', 'section' => 'chat', 'external' => true];
        }

        // Billing - always shown
        $items[] = ['label' => 'Billing', 'href' => '/user/billing', 'icon' => '💳', 'section' => 'billing'];
        $items[] = ['label' => 'Invoices', 'href' => '/user/invoices', 'icon' => '📄', 'section' => 'billing'];

        // Support - always shown
        $items[] = ['label' => 'Tickets', 'href' => '/user/tickets', 'icon' => '🎫', 'section' => 'support'];
        $items[] = ['label' => 'K. Base', 'href' => '/user/support', 'icon' => '📚', 'section' => 'support'];
        $items[] = ['label' => 'Live Chat', 'href' => '/livechat', 'icon' => '💬', 'section' => 'support', 'external' => true];

        // Account - always shown
        $items[] = ['label' => 'Profile', 'href' => '/user/profile', 'icon' => '👤', 'section' => 'account'];
        $items[] = ['label' => 'Security', 'href' => '/user/security', 'icon' => '🔐', 'section' => 'account'];

        return $items;
    }
}

if (!function_exists('render_user_sidebar')) {
    function render_user_sidebar(array $items, string $currentUrl): string
    {
        $html = '';
        $currentSection = '';
        // Determine which section is active
        foreach ($items as $item) {
            $match = $item['href'];
            if ($currentUrl === $match || ($match !== '/user' && str_starts_with($currentUrl, $match))) {
                $currentSection = $item['section'];
            }
        }
        $html = '<nav class="sidebar-nav">';
        $lastSection = '';
        foreach ($items as $item) {
            if ($item['section'] !== $lastSection) {
                if ($lastSection !== '') $html .= '</div>';
                $html .= '<div class="nav-section">';
                $html .= '<div class="nav-label">' . htmlspecialchars(ucfirst($item['section'])) . '</div>';
                $lastSection = $item['section'];
            }
            $active = $item['section'] === $currentSection ? 'active' : '';
            $target = !empty($item['external']) ? ' target="_blank"' : '';
            $html .= '<a href="' . $item['href'] . '" class="nav-link ' . $active . '"' . $target . '>'
                   . '<span class="icon">' . $item['icon'] . '</span> '
                   . '<span class="label">' . htmlspecialchars($item['label']) . '</span></a>';
        }
        if ($lastSection !== '') $html .= '</div>';
        $html .= '</nav>';
        return $html;
    }
}
