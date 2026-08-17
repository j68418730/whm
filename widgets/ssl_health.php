<?php
return [
    'key' => 'ssl_health',
    'name' => 'SSL Health',
    'description' => 'Checks SSL certificates and alerts only on broken/expiring ones',
    'icon' => 'bi-shield-lock',
    'defaultZone' => 'main',
    'defaultSort' => 3,
    'height' => 1,
    'render' => function($uw) {
        $html = '';
        $pdo = null;
        try {
            $app = \Core\Application::getInstance();
            $pdo = $app->get('db')->pdo();
        } catch (\Exception $e) {}

        $broken = [];
        $healthy = 0;
        $certDir = '/etc/letsencrypt/live';
        $domains = is_dir($certDir) ? array_values(array_diff(scandir($certDir), ['.', '..'])) : [];

        // Check ssl_services first (authoritative)
        $services = [];
        if ($pdo) {
            try { $services = $pdo->query("SELECT id, service_name, domain, port, ssl_enabled, status FROM ssl_services WHERE ssl_enabled=1")->fetchAll(\PDO::FETCH_OBJ) ?: []; } catch (\Exception $e) {}
        }

        if (!empty($services)) {
            foreach ($services as $svc) {
                $certPath = "{$certDir}/{$svc->domain}/fullchain.pem";
                if (!file_exists($certPath)) {
                    $broken[] = ['domain' => $svc->domain, 'service' => $svc->service_name, 'issue' => 'Missing cert', 'id' => $svc->id];
                    continue;
                }
                $exp = @shell_exec("openssl x509 -enddate -noout -in {$certPath} 2>/dev/null | cut -d= -f2");
                $daysLeft = $exp ? floor((strtotime(trim($exp)) - time()) / 86400) : 0;
                if ($daysLeft < 14) {
                    $broken[] = ['domain' => $svc->domain, 'service' => $svc->service_name, 'issue' => $daysLeft <= 0 ? 'Expired' : "Expires in {$daysLeft}d", 'id' => $svc->id];
                } else {
                    $healthy++;
                }
            }
        } else {
            // Fallback: check /etc/letsencrypt/live domains on port 443
            foreach ($domains as $domain) {
                $certPath = "{$certDir}/{$domain}/fullchain.pem";
                if (!file_exists($certPath)) continue;
                $exp = @shell_exec("openssl x509 -enddate -noout -in {$certPath} 2>/dev/null | cut -d= -f2");
                $daysLeft = $exp ? floor((strtotime(trim($exp)) - time()) / 86400) : 0;
                if ($daysLeft < 14) {
                    $broken[] = ['domain' => $domain, 'service' => 'apache', 'issue' => $daysLeft <= 0 ? 'Expired' : "Expires in {$daysLeft}d", 'id' => 0];
                } else {
                    $healthy++;
                }
            }
        }

        if (empty($broken) && $healthy === 0) {
            $html .= '<div style="text-align:center;color:#64748b;font-size:12px;padding:12px">No SSL certificates found to check.</div>';
        } elseif (empty($broken)) {
            $html .= '<div style="display:flex;align-items:center;gap:10px;padding:10px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.15);border-radius:8px">';
            $html .= '<span style="font-size:22px">🔒</span><div><div style="font-weight:700;color:#4ade80">All SSL certificates healthy</div><div style="font-size:11px;color:#94a3b8">' . $healthy . ' cert' . ($healthy === 1 ? '' : 's') . ' verified</div></div></div>';
        } else {
            $html .= '<div style="padding:10px;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);border-radius:8px">';
            $html .= '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px"><span style="font-size:18px">⚠️</span><span style="font-weight:700;color:#f87171">' . count($broken) . ' SSL issue' . (count($broken) === 1 ? '' : 's') . ' detected</span></div>';
            $html .= '<div style="max-height:140px;overflow-y:auto">';
            foreach ($broken as $b) {
                $html .= '<div style="display:flex;justify-content:space-between;align-items:center;padding:5px 6px;border-bottom:1px solid rgba(255,255,255,.05);font-size:12px">';
                $html .= '<span><span style="color:#f87171">●</span> <strong>' . htmlspecialchars($b['domain']) . '</strong> <span style="color:#94a3b8">(' . htmlspecialchars($b['service']) . ')</span> <span style="color:#fbbf24">' . htmlspecialchars($b['issue']) . '</span></span>';
                $html .= '<a href="/admin/ssl/universal/repair?service_id=' . (int)$b['id'] . '" style="background:rgba(56,189,248,.15);color:#38bdf8;padding:3px 8px;border-radius:5px;text-decoration:none;font-size:10px;white-space:nowrap" onclick="return confirm(\'Fix ' . htmlspecialchars($b['domain']) . '?\')">🔧 Fix</a>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '<div style="margin-top:8px"><a href="/admin/ssl/universal/fix-all" class="btn" style="display:inline-block;width:100%;text-align:center;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;padding:7px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none" onclick="return confirm(\'Run Fix All to repair all SSL issues?\')">🔧 Fix All SSL Issues</a></div>';
            $html .= '</div>';
        }

        return $html;
    },
];
