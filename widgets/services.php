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

        // Security Center open-source tools
        $securityTools = [];
        try {
            $toolsSvc = new \Services\SecurityToolsService();
            foreach ($toolsSvc->summary() as $st) {
                $securityTools[] = ['name' => $st['label'], 'active' => $st['installed'], 'sub' => $st['version']];
            }
        } catch (\Throwable $e) {}

        $html = '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">';
        foreach ($services as $svc) {
            $html .= '<div style="display:flex;align-items:center;gap:6px;padding:4px 8px;border-radius:4px;font-size:12px;background:rgba(255,255,255,.02)">';
            $html .= '<span style="width:8px;height:8px;border-radius:50%;background:' . ($svc['active'] ? '#4ade80' : '#64748b') . ';flex-shrink:0"></span>';
            $html .= '<span style="color:' . ($svc['active'] ? '#e0e0e0' : '#64748b') . '">' . htmlspecialchars($svc['name']) . '</span>';
            $html .= '</div>';
        }
        if (!empty($securityTools)) {
            $html .= '<div style="grid-column:1/-1;margin-top:6px;padding-top:6px;border-top:1px solid rgba(255,255,255,.06)">';
            $html .= '<div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">🔒 Security Center</div>';
            $html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px">';
            foreach ($securityTools as $st) {
                $html .= '<div style="display:flex;align-items:center;gap:6px;padding:3px 8px;border-radius:4px;font-size:11px;background:rgba(255,255,255,.02)">';
                $html .= '<span style="width:8px;height:8px;border-radius:50%;background:' . ($st['active'] ? '#4ade80' : '#f87171') . ';flex-shrink:0"></span>';
                $html .= '<span style="color:' . ($st['active'] ? '#c0c0c0' : '#64748b') . ';white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' . htmlspecialchars($st['name']) . '</span>';
                $html .= '</div>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div>';
        return $html;
    },
];
