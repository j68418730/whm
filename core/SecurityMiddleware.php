<?php
namespace Core;

/**
 * Client Security Center Middleware
 *
 * Application-level access control evaluated on relevant requests.
 * Runs BEFORE routing dispatches to the requested action.
 *
 * IMPORTANT: This is an application-layer check only. It NEVER invokes
 * firewall-cmd / iptables / nft and NEVER edits /etc/firewalld, /etc/sysconfig,
 * /etc/apache2 or /etc/httpd. The admin firewall remains untouched.
 *
 * Only ONE relevant customer is evaluated per request:
 *   - Owner of the hosting account / user session
 *   - The streaming station's owner when hitting radio/DJ/widget endpoints
 *   - Chat tenant owner for chat endpoints
 */
class SecurityMiddleware
{
    public static function handle()
    {
        try {
            $app = \Core\Application::getInstance();
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

            // Determine the customer + service for this request
            [$customerId, $service, $context] = self::resolveContext($app, $path, $method, $ip, $ua);
            if (!$customerId) return;

            $svc = new \Services\SecurityService($app->get('db'));

            // Touch the session tracker (only for authenticated / logged-in customer activity)
            if (!empty($context['session_track'])) {
                try { $svc->touchSession($customerId, session_id() ?: md5($ip . $ua)); } catch (\Exception $e) {}
            }

            // Evaluate rules
            $result = $svc->checkAccess($customerId, $service, $context);

            if (!empty($result['blocked'])) {
                $blockTarget = !empty($context['username']) ? $context['username'] : $ip;
                $svc->log($customerId, 'access_blocked', $blockTarget, $service, 'blocked', 'middleware',
                    $service . ' access blocked for ' . $blockTarget . ' (' . ($result['reason'] ?: $result['action']) . ')');
                self::deny($result, $service);
            }
        } catch (\Throwable $e) {
            // Fail-open on middleware errors (do not break the site)
        }
    }

    protected static function resolveContext($app, $path, $method, $ip, $ua)
    {
        $db = $app->get('db');
        $context = ['ip' => $ip, 'username' => '', 'email' => '', 'user_id' => 0, 'session_track' => false];
        $customerId = 0;
        $service = 'all';

        // Session user (hosting account owner)
        $user = $app->get('auth')->user();
        if ($user && !empty($user->id)) {
            $hosting = $db->table('hosting_users')->where('id', $user->id)->first();
            if (!$hosting) $hosting = $db->table('hosting_users')->where('email', $user->email ?? '')->first();
            if ($hosting) {
                $customerId = (int)$hosting->id;
                $context['username'] = $hosting->username ?? '';
                $context['email'] = $hosting->email ?? '';
                $context['user_id'] = (int)$hosting->id;
                $context['session_track'] = true;
            }
        }

        // No session → try to infer customer from the path (radio/DJ/chat/widget/connector)
        if (!$customerId) {
            if (preg_match('#^/(radio|dj_panel|connector|radio/widgets|radio/embed|chat|chatbox|api/radio)#', $path)) {
                $stationId = self::pathStationId($path);
                if ($stationId > 0) {
                    $st = $db->table('streaming_stations')->where('id', $stationId)->first();
                    if ($st) {
                        $customerId = (int)$st->user_id;
                        $service = 'radio';
                        if ($st->user_id) {
                            $h = $db->table('hosting_users')->where('id', $st->user_id)->first();
                            if ($h) { $context['username'] = $h->username ?? ''; $context['email'] = $h->email ?? ''; $context['user_id'] = (int)$h->id; }
                        }
                    }
                }
                // chatbox path: infer tenant → hosting user
                if (!$customerId && preg_match('#^/chatbox#', $path)) {
                    $tenantId = (int)($_GET['tenant_id'] ?? 0);
                    if ($tenantId) {
                        $t = $db->table('chatbox_tenants')->where('id', $tenantId)->first();
                        if ($t) { $customerId = (int)$t->hosting_user_id; $service = 'chat'; }
                    }
                }
                if (!$customerId && str_starts_with($path, '/chat/')) {
                    $slug = trim(substr($path, 6), '/');
                    if ($slug) {
                        $r = $db->table('chatbox_rooms')->where('slug', $slug)->first();
                        if ($r) {
                            $t = $db->table('chatbox_tenants')->where('id', $r->tenant_id)->first();
                            if ($t) { $customerId = (int)$t->hosting_user_id; $service = 'chat'; }
                        }
                    }
                }
            }
        }

        // Map the path to a service bucket when we know the customer
        if ($customerId) {
            $service = self::mapService($path);
        }

        return [$customerId, $service, $context];
    }

    protected static function mapService($path)
    {
        if (str_starts_with($path, '/user/radio') || str_starts_with($path, '/radio') || str_starts_with($path, '/dj_panel') || str_starts_with($path, '/connector')) {
            return str_contains($path, 'request') ? 'requests' : 'radio';
        }
        if (str_starts_with($path, '/chat') || str_starts_with($path, '/chatbox')) return 'chat';
        if (str_starts_with($path, '/user/websites') || str_starts_with($path, '/user/websitebuilder') || str_starts_with($path, '/user/domains')) return 'website';
        if (str_starts_with($path, '/user/ftp')) return 'ftp';
        if (str_starts_with($path, '/user/email') || str_starts_with($path, '/user/webmail')) return 'email';
        if (str_starts_with($path, '/user/billing') || str_starts_with($path, '/cart')) return 'billing';
        if (str_starts_with($path, '/user/games') || str_starts_with($path, '/game')) return 'game';
        if (str_starts_with($path, '/api/')) return 'api';
        if (str_starts_with($path, '/user/download')) return 'downloads';
        if (str_starts_with($path, '/user/radio/playlist')) return 'downloads';
        return 'all';
    }

    protected static function pathStationId($path)
    {
        if (preg_match('#/(station|stream|widgets)/?(\d+)#', $path, $m)) return (int)$m[2];
        if (preg_match('#[?&]stream(?:[_=]id)?=(\d+)#', $_SERVER['REQUEST_URI'] ?? '', $m)) return (int)$m[1];
        if (preg_match('#[?&]station(?:[_=]id)?=(\d+)#', $_SERVER['REQUEST_URI'] ?? '', $m)) return (int)$m[1];
        return 0;
    }

    protected static function deny($result, $service)
    {
        $code = 403;
        $action = $result['action'] ?? 'block';
        // Non-block actions (mute/kick/slow) are handled by the consuming service, not HTTP denial
        if (in_array($action, ['mute', 'kick', 'slow_mode', 'shadow_ban'])) return;

        if ($action === 'ban') $code = 403;
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        $reason = htmlspecialchars($result['reason'] ?: 'Access restricted by the site owner.');
        echo '<!DOCTYPE html><html><head><title>Access Denied</title><meta name="viewport" content="width=device-width,initial-scale=1"><style>'
            . 'body{margin:0;padding:40px;font-family:Inter,system-ui,sans-serif;background:#0a0e1a;color:#e0e0e0;display:flex;justify-content:center;align-items:center;min-height:100vh}'
            . '.card{max-width:420px;text-align:center;background:#0d1526;border:1px solid rgba(248,113,113,.2);border-radius:14px;padding:36px}'
            . 'h1{color:#f87171;margin:0 0 8px;font-size:22px}'
            . 'p{color:#94a3b8;font-size:13px;line-height:1.6}'
            . '.badge{display:inline-block;margin-top:14px;padding:6px 16px;border-radius:20px;background:rgba(248,113,113,.1);color:#f87171;font-size:11px;font-weight:600}</style></head><body>'
            . '<div class="card"><div style="font-size:44px;margin-bottom:8px">🔒</div><h1>Access Denied</h1>'
            . '<p>You have been restricted from this service by the site owner.</p>'
            . ($reason !== 'Access restricted by the site owner.' ? '<p style="color:#fbbf24;font-size:12px">' . $reason . '</p>' : '')
            . '<div class="badge">Security Center</div></div></body></html>';
        exit;
    }
}
