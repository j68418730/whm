<?php
return [
    'key' => 'recent_logins',
    'name' => 'Recent Logins',
    'description' => 'Last 5 admin logins',
    'icon' => 'bi-shield-check',
    'defaultZone' => 'side',
    'defaultSort' => 0,
    'height' => 1,
    'render' => function($uw) {
        $app = \Core\Application::getInstance();
        $db = $app->get('db');
        try {
            $rows = $db->table('login_attempts')->orderBy('created_at', 'DESC')->limit(5)->get() ?: [];
        } catch (\Exception $e) { $rows = []; }
        $html = '<table style="width:100%"><tr><th>User</th><th>IP</th><th>Time</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . htmlspecialchars($r->username ?? '?') . '</td><td>' . htmlspecialchars($r->ip_address ?? '') . '</td><td style="font-size:11px">' . ($r->created_at ?? '') . '</td></tr>';
        }
        $html .= '</table>';
        return $html;
    },
];
