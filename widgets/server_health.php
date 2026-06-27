<?php
return [
    'key' => 'server_health',
    'name' => 'Server Health',
    'description' => 'CPU, memory, and disk usage',
    'icon' => 'bi-cpu',
    'defaultZone' => 'main',
    'defaultSort' => 1,
    'height' => 1,
    'render' => function($uw) {
        $d = \Core\WidgetManager::getInstance()->getData('server') ?: [];
        $s = $d;
        $stats = [];
        @exec("free -m | awk '/Mem:/ {print \$2,\$3,\$4}' 2>/dev/null", $memOut);
        if (!empty($memOut)) {
            $parts = explode(' ', $memOut[0]);
            $stats['mem_total'] = $parts[0] ?? 0;
            $stats['mem_used'] = $parts[1] ?? 0;
            $stats['mem_pct'] = $stats['mem_total'] > 0 ? round($stats['mem_used'] / $stats['mem_total'] * 100) : 0;
        } else {
            $stats['mem_total'] = 0; $stats['mem_used'] = 0; $stats['mem_pct'] = 0;
        }
        @exec("top -bn1 | grep 'Cpu(s)' | awk '{print \$2+\$4}' 2>/dev/null", $cpuOut);
        $stats['cpu_pct'] = !empty($cpuOut) ? round((float)$cpuOut[0]) : 0;
        @exec("df -h / | awk 'NR==2 {print \$5}' 2>/dev/null", $diskOut);
        $stats['disk_pct'] = !empty($diskOut) ? (int)str_replace('%', '', $diskOut[0]) : 0;
        $ramLabel = ($s['ram'] ?? '') ?: ($stats['mem_used'] . 'MB / ' . $stats['mem_total'] . 'MB');
        $diskLabel = $s['disk'] ?? ($stats['disk_pct'] . '%');
        $hostname = $s['hostname'] ?? shell_exec('hostname 2>/dev/null') ?: 'localhost';
        $uptime = $s['uptime'] ?? shell_exec('uptime -p 2>/dev/null') ?: '';
        $load = $s['load'] ?? shell_exec('cat /proc/loadavg 2>/dev/null | awk "{print \$1\" / \"\$2\" / \"\$3}"') ?: '';
        $cpuModel = $s['cpu'] ?? shell_exec('cat /proc/cpuinfo 2>/dev/null | grep "model name" | head -1 | cut -d: -f2') ?: '';
        $html = '<div id="serverHealthWidget" style="display:grid;grid-template-columns:1fr 1fr;gap:8px">';
        $html .= '<div><span style="color:#64748b;font-size:12px">Hostname</span><div style="font-size:14px;font-weight:600">' . htmlspecialchars(trim($hostname)) . '</div></div>';
        $html .= '<div><span style="color:#64748b;font-size:12px">Uptime</span><div style="font-size:14px;font-weight:600">' . htmlspecialchars($uptime) . '</div></div>';
        $html .= '<div><span style="color:#64748b;font-size:12px">CPU</span><div style="font-size:14px">' . htmlspecialchars(trim($cpuModel)) . '</div></div>';
        $html .= '<div><span style="color:#64748b;font-size:12px">Load</span><div style="font-size:14px">' . $load . '</div></div>';
        $html .= '</div>';
        $html .= '<div style="margin-top:10px"><div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px"><span>RAM</span><span>' . $ramLabel . '</span></div>';
        $html .= '<div style="height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden"><div style="height:100%;width:' . ($stats['mem_pct']) . '%;background:linear-gradient(90deg,#008cff,#3bb8ff);border-radius:3px;transition:width 1s"></div></div></div>';
        $html .= '<div style="margin-top:8px"><div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px"><span>Disk</span><span>' . $diskLabel . '</span></div>';
        $html .= '<div style="height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden"><div style="height:100%;width:' . ($stats['disk_pct']) . '%;background:linear-gradient(90deg,#f59e0b,#ef4444);border-radius:3px;transition:width 1s"></div></div></div>';
        $html .= '<script>
setInterval(function(){var x=new XMLHttpRequest();x.open("GET","/admin/dashboard/health",true);x.onload=function(){try{var d=JSON.parse(x.responseText);var el=document.getElementById("serverHealthWidget");if(el&&d){el.innerHTML="<div style=\"display:grid;grid-template-columns:1fr 1fr;gap:8px\"><div><span style=\"color:#64748b;font-size:12px\">Hostname</span><div style=\"font-size:14px;font-weight:600\">"+d.hostname+"</div></div><div><span style=\"color:#64748b;font-size:12px\">Uptime</span><div style=\"font-size:14px;font-weight:600\">"+d.uptime+"</div></div><div><span style=\"color:#64748b;font-size:12px\">CPU</span><div style=\"font-size:14px\">"+d.cpu+"</div></div><div><span style=\"color:#64748b;font-size:12px\">Load</span><div style=\"font-size:14px\">"+d.load+"</div></div></div><div style=\"margin-top:10px\"><div style=\"display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px\"><span>RAM</span><span>"+d.ram+"</span></div><div style=\"height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden\"><div style=\"height:100%;width:"+d.ram_pct+"%;background:linear-gradient(90deg,#008cff,#3bb8ff);border-radius:3px\"></div></div></div><div style=\"margin-top:8px\"><div style=\"display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px\"><span>Disk</span><span>"+d.disk+"</span></div><div style=\"height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden\"><div style=\"height:100%;width:"+d.disk_pct+"%;background:linear-gradient(90deg,#f59e0b,#ef4444);border-radius:3px\"></div></div></div>"}}catch(e){}};x.send()},15000);
</script>';
        return $html;
    },
];
