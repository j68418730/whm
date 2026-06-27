<?php
return [
    'key' => 'hostname_status',
    'name' => 'Hostname & SSL',
    'description' => 'Hostname, SSL, DNS, nameservers, server IPs',
    'icon' => 'bi-globe',
    'defaultZone' => 'main',
    'defaultSort' => 4,
    'height' => 1,
    'render' => function($uw) {
        $srv = \Core\WidgetManager::getInstance()->getData('server') ?: [];
        $ns1 = \Core\WidgetManager::getInstance()->getData('ns1') ?: 'ns1.planet-hosts.com';
        $ns2 = \Core\WidgetManager::getInstance()->getData('ns2') ?: 'ns2.planet-hosts.com';
        $primaryDomain = \Core\WidgetManager::getInstance()->getData('primaryDomain') ?: 'planet-hosts.com';
        $serverIps = \Core\WidgetManager::getInstance()->getData('serverIps') ?: [];
        $hostname = $srv['hostname'] ?? shell_exec('hostname 2>/dev/null') ?: 'localhost';
        $publicIp = $srv['public_ip'] ?? '45.61.59.55';

        $html = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px" id="hostnameStatusWidget">';
        $html .= '<div><span style="color:#64748b;font-size:12px">Hostname</span><div style="font-size:14px;font-weight:600">' . htmlspecialchars(trim($hostname)) . '</div></div>';
        $html .= '<div><span style="color:#64748b;font-size:12px">Public IP</span><div style="font-size:14px">' . htmlspecialchars($publicIp) . '</div></div>';
        $html .= '<div><span style="color:#64748b;font-size:12px">SSL</span><div style="font-size:14px"><span style="color:#64748b" id="sslStatusWidget">Checking...</span></div></div>';
        $html .= '<div><span style="color:#64748b;font-size:12px">DNS</span><div style="font-size:14px"><span style="color:#64748b" id="dnsStatusWidget">Checking...</span></div></div>';
        $html .= '<div><span style="color:#64748b;font-size:12px">Panel URL</span><div style="font-size:14px"><a href="https://' . htmlspecialchars(trim($hostname)) . '" style="color:var(--accent);text-decoration:none" target="_blank">https://' . htmlspecialchars(trim($hostname)) . ' <i class="fas fa-external-link-alt" style="font-size:10px"></i></a></div></div>';
        $html .= '</div>';

        $html .= '<div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,.06)">';
        $html .= '<div style="font-weight:600;font-size:13px;margin-bottom:8px">Primary Domain</div>';
        $html .= '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px">';
        $html .= '<div><span style="color:#64748b;font-size:11px">Domain</span><div style="font-size:14px">' . htmlspecialchars($primaryDomain) . '</div></div>';
        $html .= '<div><span style="color:#64748b;font-size:11px">Nameserver 1</span><div style="font-size:13px">' . htmlspecialchars($ns1) . '</div></div>';
        $html .= '<div><span style="color:#64748b;font-size:11px">Nameserver 2</span><div style="font-size:13px">' . htmlspecialchars($ns2) . '</div></div>';
        $html .= '</div></div>';

        if (!empty($serverIps)) {
            $html .= '<div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,.06)">';
            $html .= '<div style="font-weight:600;font-size:13px;margin-bottom:8px">Server IPs</div>';
            foreach ($serverIps as $sip) {
                $ip = $sip['ip'] ?? '';
                if (empty($ip)) continue;
                $html .= '<div style="background:rgba(8,16,28,.4);border-radius:6px;padding:10px;margin-bottom:6px">';
                $html .= '<div style="font-weight:600;font-size:13px;color:#0A84FF;margin-bottom:4px">' . htmlspecialchars($ip) . '</div>';
                $html .= '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:6px;font-size:11px">';
                $html .= '<div><span style="color:#64748b">NS1</span><br>' . htmlspecialchars($sip['ns1'] ?? $ns1) . '</div>';
                $html .= '<div><span style="color:#64748b">NS2</span><br>' . htmlspecialchars($sip['ns2'] ?? $ns2) . '</div>';
                $html .= '<div><span style="color:#64748b">Panel URL</span><br><a href="https://planet-hosts.com:2087/" style="color:#0A84FF;text-decoration:none">planet-hosts.com:2087</a></div>';
                $html .= '</div></div>';
            }
            $html .= '</div>';
        }

        $html .= '<script>
fetch("/admin/hostname/health").then(function(r){return r.json()}).then(function(d){
    var sslEl=document.getElementById("sslStatusWidget");
    if(sslEl&&d.ssl_status){var c=d.ssl_status==="valid"?"#4ade80":"#f87171";sslEl.innerHTML="<span style=\"color:"+c+"\">"+(d.ssl_status==="valid"?"Valid ("+d.ssl_days_left+" days)":"Missing")+"</span>"}
    var dnsEl=document.getElementById("dnsStatusWidget");
    if(dnsEl&&d.dns_resolves!==undefined){var c=d.dns_resolves?"#4ade80":"#f87171";dnsEl.innerHTML="<span style=\"color:"+c+"\">"+(d.dns_resolves?"Resolves ("+d.resolved_ip+")":"Not resolving")+"</span>"}
}).catch(function(){});
</script>';

        return $html;
    },
];
