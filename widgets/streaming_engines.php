<?php
return [
    'key' => 'streaming_engines',
    'name' => 'Streaming Engines',
    'description' => 'SHOUTcast and Icecast status',
    'icon' => 'bi-broadcast',
    'defaultZone' => 'main',
    'defaultSort' => 3,
    'height' => 1,
    'render' => function($uw) {
        $d = \Core\WidgetManager::getInstance()->getData('streamEngines') ?: [];
        $sc = \Core\WidgetManager::getInstance()->getData('stationCounts') ?: [];
        $engines = $d;
        if (empty($engines)) {
            $sc2Installed = file_exists('/usr/local/shoutcast/sc_serv') || file_exists('/opt/planethosts/shoutcast/sc_serv');
            $sc1Installed = file_exists('/usr/local/shoutcast/v1/sc_serv') || file_exists('/opt/planethosts/shoutcast1/sc_serv');
            $iceInstalled = trim(shell_exec('which icecast 2>/dev/null') ?: '') !== '' || trim(shell_exec('systemctl is-active icecast2 2>/dev/null') ?: '') === 'active';
            $engines = [
                ['name' => 'SHOUTcast v2', 'installed' => $sc2Installed, 'running' => $sc2Installed && !empty(trim(shell_exec('pgrep -x sc_serv 2>/dev/null') ?: ''))],
                ['name' => 'SHOUTcast v1', 'installed' => $sc1Installed, 'running' => $sc1Installed && !empty(trim(shell_exec('pgrep -x sc_serv 2>/dev/null') ?: ''))],
                ['name' => 'Icecast', 'installed' => $iceInstalled, 'running' => trim(shell_exec('systemctl is-active icecast2 2>/dev/null') ?: '') === 'active'],
            ];
        }
        $html = '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><span style="color:#64748b;font-size:12px">' . ($sc['running'] ?? 0) . '/' . ($sc['total'] ?? 0) . ' stations active</span></div>';
        $html .= '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px">';
        foreach ($engines as $eng) {
            $html .= '<div style="background:rgba(8,16,28,.5);border:1px solid rgba(0,191,255,.08);border-radius:8px;padding:12px;text-align:center">';
            $html .= '<div style="font-size:20px;margin-bottom:4px">' . ($eng['name'] === 'SHOUTcast v2' ? '📻' : ($eng['name'] === 'SHOUTcast v1' ? '📻' : '🎵')) . '</div>';
            $html .= '<div style="font-weight:600;font-size:13px">' . htmlspecialchars($eng['name']) . '</div>';
            $html .= '<div style="font-size:11px;margin-top:4px">';
            $html .= '<span style="color:' . ($eng['installed'] ? '#4ade80' : '#f87171') . '">● ' . ($eng['installed'] ? 'Installed' : 'Missing') . '</span>';
            if ($eng['installed']) {
                $html .= ' &middot; <span style="color:' . ($eng['running'] ? '#4ade80' : '#f87171') . '">' . ($eng['running'] ? 'Running' : 'Stopped') . '</span>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div>';
        return $html;
    },
];
