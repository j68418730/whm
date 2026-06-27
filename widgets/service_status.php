<?php
return [
    'key' => 'service_status',
    'name' => 'Service Status',
    'description' => 'Apache, MySQL, PHP-FPM status',
    'icon' => 'bi-gear',
    'defaultZone' => 'main',
    'defaultSort' => 2,
    'height' => 1,
    'render' => function($uw) {
        $services = ['apache2', 'mysql', 'php8.2-fpm', 'icecast2'];
        $html = '<table style="width:100%"><tr><th>Service</th><th>Status</th></tr>';
        foreach ($services as $svc) {
            @exec("systemctl is-active $svc 2>/dev/null", $out, $code);
            $status = $code === 0 ? 'active' : 'inactive';
            $color = $code === 0 ? '#4ade80' : '#f87171';
            $html .= '<tr><td>' . $svc . '</td><td><span style="color:' . $color . '">● ' . $status . '</span></td></tr>';
        }
        $html .= '</table>';
        return $html;
    },
];
