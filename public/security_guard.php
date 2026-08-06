<?php
/**
 * Security Center — standalone guard for direct-PHP entry points
 * (radio widgets, stream proxy, connector, chatbox, chat).
 *
 * Applies application-level rules only. NEVER touches Linux firewall.
 * Include at the top of public PHP files that bypass the front controller.
 *
 * Usage: require_once __DIR__ . '/../security_guard.php';
 * ($service is set by the including file, e.g. 'radio', 'chat', 'requests', 'api')
 */
if (!defined('SECURITY_GUARD_LOADED')) {
    define('SECURITY_GUARD_LOADED', true);

    function security_guard_run($service)
    {
        try {
            // Autoload minimal classes if the app isn't already bootstrapped
            $base = defined('BASE_PATH') ? BASE_PATH : (dirname(__DIR__));
            $env = $base . '/.env';
            if (is_file($env)) {
                foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) continue;
                    if (str_contains($line, '=')) { [$k, $v] = explode('=', $line, 2); putenv(trim($k) . '=' . trim($v)); }
                }
            }
            require_once $base . '/core/helpers.php';
            $classes = ['Application','Config','Database','Request','Response','Router','Auth','Controller','View','Session','ServiceProvider','Plugin','PluginManager'];
            foreach ($classes as $c) { $f = $base . '/core/' . $c . '.php'; if (is_file($f)) require_once $f; }
            spl_autoload_register(function ($class) use ($base) {
                $r = str_replace('\\', '/', $class) . '.php';
                $f = $base . '/' . $r;
                if (is_file($f)) { require $f; return; }
                $p = explode('/', $r); $p[0] = strtolower($p[0]);
                $l = $base . '/' . implode('/', $p);
                if (is_file($l)) { require $l; }
            });
            $config = require $base . '/config/app.php';
            $config['database'] = require $base . '/config/database.php';
            $config['plugins'] = require $base . '/config/plugins.php';
            $app = new \Core\Application($base, $config);
            $db = $app->get('db');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            // Infer customer from path station/tenant (mirrors SecurityMiddleware)
            $customerId = 0;
            $context = ['ip' => $ip, 'username' => '', 'email' => '', 'user_id' => 0];
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $path = parse_url($uri, PHP_URL_PATH) ?: '';
            $stationId = 0;
            if (preg_match('#[?&]stream(?:[_=]id)?=(\d+)#', $uri, $m)) $stationId = (int)$m[1];
            if (preg_match('#[?&]station(?:[_=]id)?=(\d+)#', $uri, $m) && !$stationId) $stationId = (int)$m[1];
            if (preg_match('#/station/(\d+)#', $path, $m) && !$stationId) $stationId = (int)$m[1];
            if ($stationId > 0) {
                $st = $db->table('streaming_stations')->where('id', $stationId)->first();
                if ($st) {
                    $customerId = (int)$st->user_id;
                    $h = $db->table('hosting_users')->where('id', $st->user_id)->first();
                    if ($h) { $context['username'] = $h->username ?? ''; $context['email'] = $h->email ?? ''; $context['user_id'] = (int)$h->id; }
                }
            }
            if (!$customerId && str_starts_with($path, '/chatbox')) {
                $tid = (int)($_GET['tenant_id'] ?? 0);
                if ($tid) { $t = $db->table('chatbox_tenants')->where('id', $tid)->first(); if ($t) $customerId = (int)$t->hosting_user_id; }
            }
            if (!$customerId && preg_match('#^/chat/#', $path)) {
                $slug = trim(substr($path, 6), '/');
                if ($slug) { $r = $db->table('chatbox_rooms')->where('slug', $slug)->first(); if ($r) { $t = $db->table('chatbox_tenants')->where('id', $r->tenant_id)->first(); if ($t) $customerId = (int)$t->hosting_user_id; } }
            }
            if (!$customerId) return;

            $svc = new \Services\SecurityService($db);
            $result = $svc->checkAccess($customerId, $service, $context);
            if (!empty($result['blocked']) && in_array($result['action'], ['block','ban'])) {
                $svc->log($customerId, 'access_blocked', $context['username'] ?: $ip, $service, 'blocked', 'middleware',
                    $service . ' blocked for ' . ($context['username'] ?: $ip) . ' (' . ($result['reason'] ?: $result['action']) . ')');
                http_response_code(403);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'Access denied by Security Center.';
                exit;
            }
        } catch (\Throwable $e) {
            // Fail-open on guard errors
        }
    }
}
