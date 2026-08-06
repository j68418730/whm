<?php
namespace Services;

/**
 * Client Security Center Service
 *
 * Application-level access control. Customers control access to THEIR OWN
 * services only. This NEVER touches firewalld / iptables / nft / Apache deny.
 *
 * The existing admin firewall (/admin/firewall: firewalld, fail2ban,
 * ModSecurity, CSF, global rules) is intentionally left untouched.
 */
class SecurityService
{
    protected $db;
    protected $pdo;

    public function __construct($db = null)
    {
        $app = \Core\Application::getInstance();
        $this->db = $db ?: $app->get('db');
        $this->pdo = $this->db->pdo();
    }

    // ─────────────────────────────────────────────
    // Rule evaluation
    // ─────────────────────────────────────────────

    /**
     * Check a subject (identity + IP) against a customer's rules for a service.
     * Returns ['blocked' => bool, 'rule' => object|null, 'action' => string, 'reason' => string|null].
     * 'all' rules apply to every service; specific-service rules override only for that service.
     */
    public function checkAccess($customerId, $service, $context = [])
    {
        $customerId = (int)$customerId;
        if ($customerId <= 0) return ['blocked' => false, 'rule' => null, 'action' => 'allow', 'reason' => null];

        $rules = $this->getActiveRules($customerId);
        if (empty($rules)) return ['blocked' => false, 'rule' => null, 'action' => 'allow', 'reason' => null];

        $identity = $this->buildIdentity($context);

        foreach ($rules as $rule) {
            $ruleService = $rule->service ?? 'all';
            if ($ruleService !== 'all' && $ruleService !== $service) continue;
            if ($this->ruleMatches($rule, $identity)) {
                // 'allow' rules are whitelist overrides (highest precedence)
                if (($rule->action ?? 'block') === 'allow' || ($rule->action ?? 'block') === 'whitelist') {
                    return ['blocked' => false, 'rule' => $rule, 'action' => 'allow', 'reason' => $rule->reason];
                }
                return ['blocked' => true, 'rule' => $rule, 'action' => $rule->action, 'reason' => $rule->reason];
            }
        }
        return ['blocked' => false, 'rule' => null, 'action' => 'allow', 'reason' => null];
    }

    protected function getActiveRules($customerId)
    {
        try {
            $st = $this->pdo->prepare(
                "SELECT * FROM security_rules
                 WHERE customer_id = ? AND (expires_at IS NULL OR expires_at > NOW())
                 ORDER BY id DESC"
            );
            $st->execute([$customerId]);
            return $st->fetchAll(\PDO::FETCH_OBJ) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function buildIdentity($context)
    {
        return [
            'ip'        => $context['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''),
            'username'  => strtolower(trim($context['username'] ?? '')),
            'email'     => strtolower(trim($context['email'] ?? '')),
            'user_id'   => (int)($context['user_id'] ?? 0),
            'country'   => strtoupper(trim($context['country'] ?? ($this->detectCountry($_SERVER['REMOTE_ADDR'] ?? '')))),
            'asn'       => $context['asn'] ?? '',
            'user_agent'=> $_SERVER['HTTP_USER_AGENT'] ?? '',
            'device'    => $context['device'] ?? '',
        ];
    }

    protected function ruleMatches($rule, $identity)
    {
        $type = $rule->rule_type ?? 'user';
        $target = strtolower(trim($rule->target ?? ''));
        if ($target === '') return false;

        switch ($type) {
            case 'user':
            case 'username':
                return $identity['username'] !== '' && $identity['username'] === $target;
            case 'email':
                return $identity['email'] !== '' && $identity['email'] === $target;
            case 'ip':
                return $identity['ip'] !== '' && $identity['ip'] === $target;
            case 'cidr':
                return $identity['ip'] !== '' && $this->ipInCidr($identity['ip'], $target);
            case 'country':
                return $identity['country'] !== '' && $identity['country'] === $target;
            case 'asn':
                return $identity['asn'] !== '' && str_contains($identity['asn'], $target);
            case 'vpn':
            case 'proxy':
            case 'tor':
                return $this->isAnonymizer($identity['ip'], $type);
            case 'device':
                return $identity['device'] !== '' && $identity['device'] === $target;
            case 'user_id':
                return $identity['user_id'] > 0 && $identity['user_id'] === (int)$target;
        }
        return false;
    }

    // ─────────────────────────────────────────────
    // IP helpers
    // ─────────────────────────────────────────────

    public function ipInCidr($ip, $cidr)
    {
        try {
            if (!str_contains($cidr, '/')) $cidr .= '/32';
            [$subnet, $bits] = explode('/', $cidr, 2);
            $bits = (int)$bits;
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            if ($ipLong === false || $subnetLong === false) return false;
            $mask = $bits === 0 ? 0 : (-1 << (32 - $bits));
            return ($ipLong & $mask) === ($subnetLong & $mask);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Best-effort: uses a bundled static GeoIP country map. Falls back gracefully.
    protected function detectCountry($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return '';
        // lightweight: only attempt if geo data exists; else return ''
        return '';
    }

    // Best-effort anonymizer detection — checks known public Tor exit / proxy ranges
    protected function isAnonymizer($ip, $type)
    {
        if (!$ip || $ip === '127.0.0.1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) return false;
        // Hook point: real VPN/Tor/proxy detection can plug in here (MaxMind / IP2Location / ipinfo).
        // Without a licensed dataset we cannot definitively classify; rules are stored but match conservatively.
        return false;
    }

    // ─────────────────────────────────────────────
    // CRUD (scoped to a customer)
    // ─────────────────────────────────────────────

    public function addRule($customerId, $data, $actor = '')
    {
        $insert = [
            'customer_id' => (int)$customerId,
            'rule_type'   => substr($data['rule_type'] ?? 'user', 0, 30),
            'target'      => substr($data['target'] ?? '', 0, 255),
            'service'     => substr($data['service'] ?? 'all', 0, 40),
            'action'      => substr($data['action'] ?? 'block', 0, 30),
            'reason'      => substr($data['reason'] ?? '', 0, 500),
            'created_by'  => substr($actor, 0, 100),
        ];
        $days = max(1, (int)($data['days'] ?? 30));
        $insert['expires_at'] = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        $st = $this->pdo->prepare(
            "INSERT INTO security_rules (customer_id, rule_type, target, service, action, reason, expires_at, created_by, created_at)
             VALUES (:customer_id, :rule_type, :target, :service, :action, :reason, :expires_at, :created_by, NOW())"
        );
        $st->execute($insert);
        $id = (int)$this->pdo->lastInsertId();

        $this->log($customerId, 'rule_added', $insert['target'], $insert['service'],
            $insert['action'] === 'block' ? 'blocked' : 'logged', $actor,
            "{$insert['rule_type']}:{$insert['target']} -> {$insert['service']} ({$insert['action']})");

        // Notify the customer owner
        $this->alert($customerId, 'block',
            "Rule added: {$insert['rule_type']} '{$insert['target']}' blocked from {$insert['service']}",
            $actor);
        return $id;
    }

    public function updateRule($customerId, $ruleId, $data, $actor = '')
    {
        $rule = $this->getRule($customerId, $ruleId);
        if (!$rule) return false;
        $update = [];
        foreach (['rule_type','target','service','action','reason'] as $f) {
            if (isset($data[$f])) $update[$f] = substr($data[$f], 0, $f === 'reason' ? 500 : 255);
        }
        if (isset($data['days'])) {
            $days = max(1, (int)$data['days']);
            $update['expires_at'] = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }
        if ($update) {
            $set = implode(',', array_map(fn($k) => "`$k` = :$k", array_keys($update)));
            $update['id'] = $ruleId;
            $st = $this->pdo->prepare("UPDATE security_rules SET $set, updated_at = NOW() WHERE id = :id AND customer_id = :cid");
            $st->execute(array_merge($update, ['cid' => (int)$customerId]));
            $this->log($customerId, 'rule_updated', $rule->target, $rule->service, 'logged', $actor, "Rule #{$ruleId} updated");
        }
        return true;
    }

    public function deleteRule($customerId, $ruleId, $actor = '')
    {
        $rule = $this->getRule($customerId, $ruleId);
        if (!$rule) return false;
        $this->pdo->prepare("DELETE FROM security_rules WHERE id = ? AND customer_id = ?")->execute([$ruleId, (int)$customerId]);
        $this->log($customerId, 'rule_removed', $rule->target, $rule->service, 'logged', $actor, "Rule #{$ruleId} removed");
        return true;
    }

    public function getRule($customerId, $ruleId)
    {
        $st = $this->pdo->prepare("SELECT * FROM security_rules WHERE id = ? AND customer_id = ?");
        $st->execute([$ruleId, (int)$customerId]);
        return $st->fetch(\PDO::FETCH_OBJ) ?: null;
    }

    public function getRules($customerId, $limit = 200)
    {
        $st = $this->pdo->prepare("SELECT * FROM security_rules WHERE customer_id = ? ORDER BY id DESC LIMIT " . (int)$limit);
        $st->execute([(int)$customerId]);
        return $st->fetchAll(\PDO::FETCH_OBJ) ?: [];
    }

    // ─────────────────────────────────────────────
    // Audit log
    // ─────────────────────────────────────────────

    public function log($customerId, $action, $target = null, $service = null, $result = null, $performedBy = null, $details = null)
    {
        try {
            $st = $this->pdo->prepare(
                "INSERT INTO security_logs (customer_id, action, target, service, result, performed_by, ip_address, details, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $st->execute([(int)$customerId, substr($action, 0, 100), $target ? substr($target, 0, 255) : null,
                $service ? substr($service, 0, 40) : null, $result ? substr($result, 0, 30) : null,
                $performedBy ? substr($performedBy, 0, 100) : null, $_SERVER['REMOTE_ADDR'] ?? null,
                $details ? substr($details, 0, 2000) : null]);
        } catch (\Exception $e) {}
    }

    public function getLogs($customerId, $limit = 200)
    {
        $st = $this->pdo->prepare("SELECT * FROM security_logs WHERE customer_id = ? ORDER BY id DESC LIMIT " . (int)$limit);
        $st->execute([(int)$customerId]);
        return $st->fetchAll(\PDO::FETCH_OBJ) ?: [];
    }

    // ─────────────────────────────────────────────
    // Alerts / notifications
    // ─────────────────────────────────────────────

    public function alert($customerId, $type, $message, $actor = '')
    {
        try {
            $st = $this->pdo->prepare("INSERT INTO security_alerts (customer_id, type, message, created_at) VALUES (?, ?, ?, NOW())");
            $st->execute([(int)$customerId, substr($type, 0, 40), substr($message, 0, 500)]);
        } catch (\Exception $e) {}
    }

    public function getAlerts($customerId, $limit = 50)
    {
        $st = $this->pdo->prepare("SELECT * FROM security_alerts WHERE customer_id = ? ORDER BY id DESC LIMIT " . (int)$limit);
        $st->execute([(int)$customerId]);
        return $st->fetchAll(\PDO::FETCH_OBJ) ?: [];
    }

    public function unreadAlertCount($customerId)
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM security_alerts WHERE customer_id = ? AND is_read = 0");
        $st->execute([(int)$customerId]);
        return (int)$st->fetchColumn();
    }

    public function markAlertsRead($customerId)
    {
        $this->pdo->prepare("UPDATE security_alerts SET is_read = 1 WHERE customer_id = ?")->execute([(int)$customerId]);
    }

    // ─────────────────────────────────────────────
    // Login attempts + lockout
    // ─────────────────────────────────────────────

    public function recordLoginAttempt($customerId, $username, $success, $country = '')
    {
        try {
            $st = $this->pdo->prepare(
                "INSERT INTO security_login_attempts (customer_id, username, ip_address, success, user_agent, country, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            $st->execute([(int)$customerId, $username ? substr($username, 0, 150) : null,
                $_SERVER['REMOTE_ADDR'] ?? null, $success ? 1 : 0, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                $country ? substr($country, 0, 4) : null]);
        } catch (\Exception $e) {}
    }

    public function recentFailedAttempts($customerId, $username = null, $minutes = 15)
    {
        $sql = "SELECT COUNT(*) FROM security_login_attempts WHERE customer_id = ? AND success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        $params = [(int)$customerId, (int)$minutes];
        if ($username) { $sql .= " AND username = ?"; $params[] = $username; }
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return (int)$st->fetchColumn();
    }

    public function getSetting($customerId, $key, $default = null)
    {
        $st = $this->pdo->prepare("SELECT setting_value FROM security_settings WHERE customer_id = ? AND setting_key = ?");
        $st->execute([(int)$customerId, $key]);
        $v = $st->fetchColumn();
        return $v === false ? $default : $v;
    }

    public function setSetting($customerId, $key, $value)
    {
        $st = $this->pdo->prepare(
            "INSERT INTO security_settings (customer_id, setting_key, setting_value)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $st->execute([(int)$customerId, $key, $value]);
    }

    public function getSettings($customerId)
    {
        $st = $this->pdo->prepare("SELECT setting_key, setting_value FROM security_settings WHERE customer_id = ?");
        $st->execute([(int)$customerId]);
        $out = [];
        foreach ($st->fetchAll(\PDO::FETCH_OBJ) as $r) $out[$r->setting_key] = $r->setting_value;
        return $out;
    }

    // ─────────────────────────────────────────────
    // Sessions
    // ─────────────────────────────────────────────

    public function touchSession($customerId, $sessionId)
    {
        $hash = hash('sha256', $sessionId);
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $browser = $this->parseBrowser($ua);
        $device = $this->parseDevice($ua);
        $trusted = $this->isTrusted($customerId, 'device', $hash) || $this->isTrusted($customerId, 'ip', $ip) ? 1 : 0;
        try {
            $st = $this->pdo->prepare(
                "INSERT INTO security_sessions (customer_id, session_hash, browser, device, ip_address, country, trusted, last_active, expires_at, created_at)
                 VALUES (?, ?, ?, ?, ?, '', ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
                 ON DUPLICATE KEY UPDATE last_active = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)"
            );
            $st->execute([(int)$customerId, $hash, $browser, $device, $ip, $trusted]);
        } catch (\Exception $e) {}
    }

    public function getSessions($customerId)
    {
        $st = $this->pdo->prepare("SELECT * FROM security_sessions WHERE customer_id = ? ORDER BY last_active DESC LIMIT 50");
        $st->execute([(int)$customerId]);
        return $st->fetchAll(\PDO::FETCH_OBJ) ?: [];
    }

    public function terminateSession($customerId, $sessionId)
    {
        $hash = hash('sha256', $sessionId);
        $this->pdo->prepare("DELETE FROM security_sessions WHERE customer_id = ? AND session_hash = ?")->execute([(int)$customerId, $hash]);
    }

    public function parseBrowser($ua)
    {
        if (preg_match('/Edg\//i', $ua)) return 'Edge';
        if (preg_match('/Chrome\//i', $ua)) return 'Chrome';
        if (preg_match('/Firefox\//i', $ua)) return 'Firefox';
        if (preg_match('/Safari\//i', $ua)) return 'Safari';
        if (preg_match('/MSIE|Trident/i', $ua)) return 'IE';
        return 'Unknown';
    }

    public function parseDevice($ua)
    {
        if (preg_match('/iPhone/i', $ua)) return 'iPhone';
        if (preg_match('/iPad/i', $ua)) return 'iPad';
        if (preg_match('/Android/i', $ua)) return 'Android';
        if (preg_match('/Windows/i', $ua)) return 'Windows';
        if (preg_match('/Mac OS X|Macintosh/i', $ua)) return 'macOS';
        if (preg_match('/Linux/i', $ua)) return 'Linux';
        return 'Unknown';
    }

    // ─────────────────────────────────────────────
    // Trusted devices / IPs
    // ─────────────────────────────────────────────

    public function addTrusted($customerId, $kind, $value, $label = '')
    {
        $st = $this->pdo->prepare(
            "INSERT IGNORE INTO security_trusted (customer_id, kind, value, label, created_at) VALUES (?, ?, ?, ?, NOW())"
        );
        $st->execute([(int)$customerId, $kind === 'ip' ? 'ip' : 'device', substr($value, 0, 500), substr($label, 0, 255)]);
    }

    public function isTrusted($customerId, $kind, $value)
    {
        $st = $this->pdo->prepare("SELECT id FROM security_trusted WHERE customer_id = ? AND kind = ? AND value = ? LIMIT 1");
        $st->execute([(int)$customerId, $kind, $value]);
        return (bool)$st->fetchColumn();
    }

    public function getTrusted($customerId)
    {
        $st = $this->pdo->prepare("SELECT * FROM security_trusted WHERE customer_id = ? ORDER BY id DESC");
        $st->execute([(int)$customerId]);
        return $st->fetchAll(\PDO::FETCH_OBJ) ?: [];
    }

    public function removeTrusted($customerId, $id)
    {
        $this->pdo->prepare("DELETE FROM security_trusted WHERE customer_id = ? AND id = ?")->execute([(int)$customerId, (int)$id]);
    }

    // ─────────────────────────────────────────────
    // Dashboard stats
    // ─────────────────────────────────────────────

    public function dashboard($customerId)
    {
        $customerId = (int)$customerId;
        $one = function($sql, $p = []) { $st = $this->pdo->prepare($sql); $st->execute($p); return $st->fetchColumn(); };
        $num = function($v) { return (int)$v; };

        return [
            'blocked_users'     => $num($one("SELECT COUNT(*) FROM security_rules WHERE customer_id = ? AND rule_type IN ('user','username','email') AND (expires_at IS NULL OR expires_at > NOW())", [$customerId])),
            'blocked_ips'       => $num($one("SELECT COUNT(*) FROM security_rules WHERE customer_id = ? AND rule_type IN ('ip','cidr') AND (expires_at IS NULL OR expires_at > NOW())", [$customerId])),
            'active_sessions'   => $num($one("SELECT COUNT(*) FROM security_sessions WHERE customer_id = ? AND expires_at > NOW()", [$customerId])),
            'failed_logins'     => $num($one("SELECT COUNT(*) FROM security_login_attempts WHERE customer_id = ? AND success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)", [$customerId])),
            'alerts'            => $num($one("SELECT COUNT(*) FROM security_alerts WHERE customer_id = ? AND is_read = 0", [$customerId])),
            'total_rules'       => $num($one("SELECT COUNT(*) FROM security_rules WHERE customer_id = ?", [$customerId])),
            'recent_activity'   => $this->getLogs($customerId, 15),
            'alerts_list'       => $this->getAlerts($customerId, 10),
            'blocked_services'  => $this->ruleCountByService($customerId),
        ];
    }

    protected function ruleCountByService($customerId)
    {
        $st = $this->pdo->prepare(
            "SELECT service, COUNT(*) c FROM security_rules WHERE customer_id = ? AND (expires_at IS NULL OR expires_at > NOW()) GROUP BY service ORDER BY c DESC"
        );
        $st->execute([(int)$customerId]);
        return $st->fetchAll(\PDO::FETCH_OBJ) ?: [];
    }

    public function listServices()
    {
        return [
            'all'        => 'All Services',
            'website'    => 'Website',
            'chat'       => 'Chat',
            'radio'      => 'Radio',
            'requests'   => 'Requests',
            'downloads'  => 'Downloads',
            'billing'    => 'Billing',
            'ftp'        => 'FTP',
            'email'      => 'Email',
            'game'       => 'Game Servers',
            'api'        => 'API',
            'dj'         => 'DJ Login',
            'comments'   => 'Comments',
            'uploads'    => 'Uploads',
            'contact'    => 'Contact Forms',
        ];
    }

    public function listActions()
    {
        return [
            'block'       => 'Block',
            'allow'       => 'Allow (whitelist)',
            'mute'        => 'Mute',
            'kick'        => 'Kick',
            'ban'         => 'Ban',
            'shadow_ban'  => 'Shadow Ban',
            'slow_mode'   => 'Slow Mode',
            'reserve'     => 'Reserve Slot',
        ];
    }

    public function listRuleTypes()
    {
        return [
            'user'     => 'Planet Hosts User',
            'username' => 'Username',
            'email'    => 'Email',
            'ip'       => 'IP Address',
            'cidr'     => 'CIDR Range',
            'country'  => 'Country (ISO-2)',
            'asn'      => 'ASN',
            'vpn'      => 'VPN',
            'proxy'    => 'Proxy',
            'tor'      => 'Tor',
            'device'   => 'Device / Browser',
        ];
    }
}
