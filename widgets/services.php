<?php
return [
    'key' => 'services',
    'name' => 'Services',
    'description' => 'Apache, MySQL, PHP-FPM and other service status',
    'icon' => 'bi-gear',
    'defaultZone' => 'main',
    'defaultSort' => 2,
    'height' => 1,
    'render' => function($uw) {
        $serviceNames = ['apache2' => 'Apache', 'mariadb' => 'MariaDB', 'icecast2' => 'Icecast', 'postfix' => 'Postfix', 'dovecot' => 'Dovecot', 'firewalld' => 'Firewall', 'nginx' => 'Nginx'];
        $services = [];
        foreach ($serviceNames as $sName => $sLabel) {
            $active = trim(shell_exec("systemctl is-active {$sName} 2>/dev/null") ?: '') === 'active';
            $services[] = ['name' => $sLabel, 'active' => $active];
        }
        $cronActive = trim(shell_exec('systemctl is-active cron 2>/dev/null') ?: '') === 'active';
        if (!$cronActive) $cronActive = trim(shell_exec('systemctl is-active crond 2>/dev/null') ?: '') === 'active';
        $services[] = ['name' => 'Cron', 'active' => $cronActive];
        $html = '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">';
        foreach ($services as $svc) {
            $html .= '<div style="display:flex;align-items:center;gap:6px;padding:4px 8px;border-radius:4px;font-size:12px;background:rgba(255,255,255,.02)">';
            $html .= '<span style="width:8px;height:8px;border-radius:50%;background:' . ($svc['active'] ? '#4ade80' : '#64748b') . ';flex-shrink:0"></span>';
            $html .= '<span style="color:' . ($svc['active'] ? '#e0e0e0' : '#64748b') . '">' . htmlspecialchars($svc['name']) . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    },
];
