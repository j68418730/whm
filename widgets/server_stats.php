<?php
return [
    'key' => 'server_stats',
    'name' => 'Server Statistics',
    'description' => 'CPU, memory, and disk usage',
    'icon' => 'bi-cpu',
    'defaultZone' => 'main',
    'defaultSort' => 0,
    'height' => 1,
    'render' => function($uw) {
        $stats = [];
        @exec("free -m | awk '/Mem:/ {print \$2,\$3,\$4}' 2>/dev/null", $memOut);
        if (!empty($memOut)) {
            $parts = explode(' ', $memOut[0]);
            $stats['mem_total'] = $parts[0] ?? 0;
            $stats['mem_used'] = $parts[1] ?? 0;
            $stats['mem_free'] = $parts[2] ?? 0;
            $stats['mem_pct'] = $stats['mem_total'] > 0 ? round($stats['mem_used'] / $stats['mem_total'] * 100) : 0;
        }
        @exec("top -bn1 | grep 'Cpu(s)' | awk '{print \$2+\$4}' 2>/dev/null", $cpuOut);
        $stats['cpu_pct'] = !empty($cpuOut) ? round((float)$cpuOut[0]) : 0;
        @exec("df -h / | awk 'NR==2 {print \$5}' 2>/dev/null", $diskOut);
        $stats['disk_pct'] = !empty($diskOut) ? (int)str_replace('%', '', $diskOut[0]) : 0;
        $html = '<div class="stats-grid" style="grid-template-columns:1fr 1fr 1fr">';
        $html .= '<div class="stat-card"><div class="label">CPU</div><div class="value" style="font-size:24px">' . $stats['cpu_pct'] . '%</div><div class="label">Utilization</div></div>';
        $html .= '<div class="stat-card"><div class="label">Memory</div><div class="value" style="font-size:24px">' . $stats['mem_pct'] . '%</div><div class="label">' . ($stats['mem_used'] ?? 0) . 'MB / ' . ($stats['mem_total'] ?? 0) . 'MB</div></div>';
        $html .= '<div class="stat-card"><div class="label">Disk</div><div class="value" style="font-size:24px">' . $stats['disk_pct'] . '%</div><div class="label">Root partition</div></div>';
        $html .= '</div>';
        return $html;
    },
];
