<?php
namespace Services;

/**
 * Security Center — open-source tool integration.
 * Reads status files written by install/*.sh modules and
 * provides install / scan / log / report controls.
 * Admin only. Never touches the customer firewall UI.
 */
class SecurityToolsService
{
    protected $db;
    protected $statusDir = '/var/www/radiohosting/storage/security';
    protected $logDir = '/var/log/planethosts';
    protected $installDir = '/var/www/radiohosting/install';

    // Tool registry: key => [label, binary, install script, status file, log file, scan command]
    protected $tools = [];

    public function __construct($db = null)
    {
        $app = \Core\Application::getInstance();
        $this->db = $db ?: $app->get('db');

        $this->tools = [
            'firewall' => ['label' => 'Firewall (firewalld + fail2ban)', 'binary' => 'firewall-cmd',
                'script' => '01-firewall', 'status' => 'firewall', 'log' => 'security-center',
                'scan' => 'systemctl is-active firewalld && systemctl is-active fail2ban', 'group' => 'core'],
            'clamav' => ['label' => 'Malware Scanner (ClamAV)', 'binary' => 'clamscan',
                'script' => '02-clamav', 'status' => 'clamav', 'log' => 'clamscan',
                'scan' => 'sudo /usr/local/bin/ph-clamscan /home', 'group' => 'malware'],
            'yara' => ['label' => 'Web Malware Rules (YARA)', 'binary' => 'yara',
                'script' => '03-yara', 'status' => 'yara', 'log' => 'yarascan',
                'scan' => 'sudo /usr/local/bin/ph-yarascan /home', 'group' => 'malware'],
            'trivy' => ['label' => 'Vulnerability Scanner (Trivy)', 'binary' => 'trivy',
                'script' => '04-trivy', 'status' => 'trivy', 'log' => 'trivy',
                'scan' => 'sudo /usr/local/bin/ph-trivyscan', 'group' => 'vuln'],
            'osv' => ['label' => 'OSV Scanner', 'binary' => 'osv-scanner',
                'script' => '05-osv', 'status' => 'osv', 'log' => 'osv',
                'scan' => 'sudo /usr/local/bin/ph-osvscan /home', 'group' => 'vuln'],
            'lynis' => ['label' => 'Security Audit (Lynis)', 'binary' => 'lynis',
                'script' => '06-lynis', 'status' => 'lynis', 'log' => 'lynis',
                'scan' => 'sudo /usr/local/bin/ph-lynis', 'group' => 'audit'],
            'aide' => ['label' => 'File Integrity (AIDE)', 'binary' => 'aide',
                'script' => '07-aide', 'status' => 'aide', 'log' => 'aide',
                'scan' => 'sudo /usr/local/bin/ph-aide', 'group' => 'integrity'],
            'rkhunter' => ['label' => 'Rootkit Hunter (rkhunter)', 'binary' => 'rkhunter',
                'script' => '08-rkhunter', 'status' => 'rkhunter', 'log' => 'rkhunter',
                'scan' => 'sudo /usr/local/bin/ph-rkhunter', 'group' => 'rootkit'],
            'chkrootkit' => ['label' => 'Rootkit Checker (chkrootkit)', 'binary' => 'chkrootkit',
                'script' => '09-chkrootkit', 'status' => 'chkrootkit', 'log' => 'chkrootkit',
                'scan' => 'sudo /usr/local/bin/ph-chkrootkit', 'group' => 'rootkit'],
            'logwatch' => ['label' => 'Log Analysis (Logwatch)', 'binary' => 'logwatch',
                'script' => '10-logwatch', 'status' => 'logwatch', 'log' => 'logwatch',
                'scan' => 'sudo /usr/local/bin/ph-logwatch', 'group' => 'logs'],
            'goaccess' => ['label' => 'Log Analyzer (GoAccess)', 'binary' => 'goaccess',
                'script' => '11-goaccess', 'status' => 'goaccess', 'log' => 'goaccess',
                'scan' => 'sudo /usr/local/bin/ph-goaccess', 'group' => 'logs'],
            'testssl' => ['label' => 'SSL Scanner (testssl.sh)', 'binary' => 'testssl',
                'script' => '12-testssl', 'status' => 'testssl', 'log' => 'testssl',
                'scan' => 'sudo /usr/local/bin/ph-testssl', 'group' => 'ssl'],
            'spamassassin' => ['label' => 'Email Filter (SpamAssassin)', 'binary' => 'spamc',
                'script' => '13-spamassassin', 'status' => 'spamassassin', 'log' => 'spamassassin',
                'scan' => 'sudo /usr/local/bin/ph-spamassassin', 'group' => 'email'],
            'opendkim' => ['label' => 'DKIM Signing (OpenDKIM)', 'binary' => 'opendkim',
                'script' => '14-opendkim', 'status' => 'opendkim', 'log' => 'opendkim',
                'scan' => 'systemctl is-active opendkim', 'group' => 'email'],
        ];
    }

    public function tools()
    {
        return $this->tools;
    }

    public function tool($key)
    {
        return $this->tools[$key] ?? null;
    }

    public function isInstalled($tool)
    {
        $bin = $tool['binary'] ?? '';
        if (!$bin) return false;
        $paths = ['/usr/bin/', '/usr/local/bin/', '/bin/', '/usr/sbin/'];
        foreach ($paths as $p) {
            if (is_file($p . $bin) || is_executable($p . $bin)) return true;
        }
        return trim(shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null')) !== '';
    }

    public function version($tool)
    {
        $bin = $tool['binary'] ?? '';
        if (!$bin) return '';
        $out = trim(shell_exec($bin . ' --version 2>/dev/null | head -1') ?: '');
        return $out ?: '';
    }

    public function status($tool)
    {
        $file = $this->statusDir . '/' . ($tool['status'] ?? $tool['script']) . '.status';
        if (!is_file($file)) return ['state' => 'not_installed', 'updated' => '', 'value' => ''];
        $raw = trim(file_get_contents($file));
        [$ts, $module, $state, $value] = array_pad(explode('|', $raw, 4), 4, '');
        return ['state' => $state, 'updated' => $ts, 'value' => $value];
    }

    public function logTail($tool, $lines = 200)
    {
        $log = $this->logDir . '/' . ($tool['log'] ?? $tool['script']) . '.log';
        if (!is_file($log)) return [];
        $out = shell_exec('tail -n ' . (int)$lines . ' ' . escapeshellarg($log) . ' 2>/dev/null');
        return $out ? explode("\n", trim($out)) : [];
    }

    public function runScript($tool)
    {
        $script = $this->installDir . '/' . ($tool['script'] ?? '') . '.sh';
        if (!is_file($script)) return ['success' => false, 'error' => 'Installer script not found'];
        exec('cd ' . escapeshellarg(dirname($script)) . ' && sudo bash ' . escapeshellarg(basename($script)) . ' 2>&1 >/dev/null &');
        return ['success' => true, 'message' => 'Install started in background. Check status shortly.'];
    }

    public function runScan($tool)
    {
        $cmd = $tool['scan'] ?? '';
        if (!$cmd) return ['success' => false, 'error' => 'No scan command for this tool'];
        exec($cmd . ' 2>&1 >/dev/null &');
        return ['success' => true, 'message' => 'Scan started in background.'];
    }

    public function summary()
    {
        $out = [];
        foreach ($this->tools as $key => $tool) {
            $st = $this->status($tool);
            $out[] = [
                'key' => $key,
                'label' => $tool['label'],
                'group' => $tool['group'],
                'installed' => $this->isInstalled($tool),
                'state' => $st['state'],
                'version' => $st['value'] ?: $this->version($tool),
                'updated' => $st['updated'],
            ];
        }
        return $out;
    }

    // Security score: 0-100 based on installed tools + lynis state
    public function score()
    {
        $tools = $this->summary();
        $groups = ['malware' => ['clamav', 'yara'], 'vuln' => ['trivy', 'osv'], 'integrity' => ['aide'],
                   'rootkit' => ['rkhunter', 'chkrootkit'], 'audit' => ['lynis'],
                   'ssl' => ['testssl'], 'email' => ['spamassassin', 'opendkim'], 'core' => ['firewall']];
        $installed = 0; $total = 0;
        foreach ($tools as $t) { $total++; if ($t['installed']) $installed++; }
        $base = $total > 0 ? round(($installed / $total) * 80) : 0;
        // +20 if lynis audit passed
        $lynis = $this->status($this->tools['lynis']);
        $bonus = ($lynis['state'] === 'ok') ? 20 : 0;
        return min(100, $base + $bonus);
    }

    public function lastScanHistory($limit = 20)
    {
        $out = [];
        foreach ($this->tools as $key => $tool) {
            $st = $this->status($tool);
            if ($st['updated']) {
                $out[] = ['tool' => $key, 'label' => $tool['label'], 'state' => $st['state'], 'at' => $st['updated']];
            }
        }
        usort($out, fn($a, $b) => strcmp($b['at'] ?? '', $a['at'] ?? ''));
        return array_slice($out, 0, $limit);
    }

    // ─────────────────────────────────────────────
    // Scan All (background) + results + fix
    // ─────────────────────────────────────────────

    /**
     * Launch every installed scan in the background via a single aggregator script.
     * Each ph-* scanner writes its own log + status; the aggregator writes scan-results.json.
     */
    public function runAllScans()
    {
        $script = $this->ensureScanAllScript();
        if (!$script) return ['success' => false, 'error' => 'Could not create scan-all script'];
        // Run directly (not via sudo) — the script internally calls sudo /usr/local/bin/ph-* which www-data is allowed to run
        exec('nohup bash ' . escapeshellarg($script) . ' > /dev/null 2>&1 &');
        return ['success' => true, 'message' => 'All scans started in background.'];
    }

    /**
     * Ensure the ph-scan-all aggregator script exists (created idempotently).
     * Written into storage/security (www-data writable), not /usr/local/bin.
     */
    protected function ensureScanAllScript()
    {
        if (!is_dir($this->statusDir)) @mkdir($this->statusDir, 0755, true);
        $path = $this->statusDir . '/scan-all.sh';
        $content = $this->scanAllScriptContent();
        if (@file_put_contents($path, $content) === false) return false;
        @chmod($path, 0755);
        return $path;
    }

    protected function scanAllScriptContent()
    {
        $dir = $this->logDir;
        $results = $this->statusDir . '/scan-results.json';
        $tools = [];
        foreach ($this->tools as $key => $t) {
            $cmd = $t['scan'] ?? '';
            $log = $dir . '/' . ($t['log'] ?? $key) . '.log';
            $tools[] = ['key' => $key, 'cmd' => $cmd, 'log' => $log, 'label' => $t['label']];
        }
        // Build a bash script that runs each scanner then writes scan-results.json
        $bash = "#!/bin/bash\n";
        $bash .= "RESULTS=" . escapeshellarg($results) . "\n";
        $bash .= "TS=\$(date '+%Y-%m-%d %H:%M:%S')\n";
        $bash .= "echo \"{\\\"started\\\":\\\"\$TS\\\",\\\"runs\\\":[]}\" > \$RESULTS\n";
        $bash .= "findings=\"\"\n";
        foreach ($tools as $t) {
            if (!$t['cmd']) continue;
            $bash .= "echo \"[run] {$t['key']}\" >> " . escapeshellarg($this->logDir . '/scan-all.log') . "\n";
            // scan cmd already includes sudo /usr/local/bin/ph-* (allowed for www-data)
            $bash .= "bash -c " . escapeshellarg($t['cmd']) . " >> " . escapeshellarg($t['log']) . " 2>&1 || true\n";
        }
        $bash .= "DONE=\$(date '+%Y-%m-%d %H:%M:%S')\n";
        $bash .= "echo \"{\\\"started\\\":\\\"\$TS\\\",\\\"finished\\\":\\\"\$DONE\\\"}\" > \$RESULTS\n";
        $bash .= "echo \"[done] \$DONE\" >> " . escapeshellarg($this->logDir . '/scan-all.log') . "\n";
        $bash .= "exit 0\n";
        return $bash;
    }

    /**
     * Read scan results: parse each tool log for findings and return per-tool status.
     * Returns array of ['key','label','found' => bool,'count','detail','log'].
     */
    public function scanResults()
    {
        $out = [];
        foreach ($this->tools as $key => $tool) {
            $log = $this->logDir . '/' . ($tool['log'] ?? $key) . '.log';
            $found = false; $count = 0; $detail = '';
            if (is_file($log)) {
                $tail = file_get_contents($log) ?: '';
                [$found, $count, $detail] = $this->detectFindings($key, $tail);
            }
            $out[] = [
                'key' => $key,
                'label' => $tool['label'],
                'group' => $tool['group'],
                'found' => $found,
                'count' => $count,
                'detail' => $detail,
                'log' => $log,
            ];
        }
        return $out;
    }

    /**
     * Per-tool log parsing to decide whether a scan surfaced something.
     */
    protected function detectFindings($key, $log)
    {
        switch ($key) {
            case 'clamav':
                if (preg_match_all('/FOUND|Infected files:\s*([0-9]+)/i', $log, $m)) {
                    $n = (int)end($m[1]) ?: count($m[0]);
                    return [$n > 0, $n, $n . ' infected file(s) detected'];
                }
                return [false, 0, ''];
            case 'yara':
                if (preg_match_all('/^HIT:/mi', $log, $m)) {
                    return [true, count($m[0]), count($m[0]) . ' rule match(es)'];
                }
                return [false, 0, ''];
            case 'trivy':
                if (preg_match('/Total: ([0-9]+)/i', $log, $m) && (int)$m[1] > 0) {
                    return [true, (int)$m[1], (int)$m[1] . ' vulnerabilities found'];
                }
                if (preg_match('/CRITICAL|HIGH/i', $log) && preg_match('/Vulnerability/i', $log)) {
                    return [true, 1, 'High/Critical vulnerabilities found'];
                }
                return [false, 0, ''];
            case 'osv':
                if (preg_match('/([0-9]+) vulnerabilities? found/i', $log, $m) && (int)$m[1] > 0) {
                    return [true, (int)$m[1], $m[0]];
                }
                if (preg_match('/Vulnerability found|OSV-Scanner found/i', $log)) {
                    return [true, 1, 'Vulnerabilities found'];
                }
                return [false, 0, ''];
            case 'lynis':
                if (preg_match('/hardening_index=([0-9]+)/', $log, $m) && (int)$m[1] < 60) {
                    return [true, 1, 'Lynis hardening index low: ' . (int)$m[1]];
                }
                if (preg_match('/Number of warnings|warnings found|Warnings found/i', $log)) {
                    return [true, 1, 'Lynis audit warnings present'];
                }
                return [false, 0, ''];
            case 'aide':
                if (preg_match('/difference found|Difference found|changes detected|CHANGED/i', $log)) {
                    return [true, 1, 'File integrity differences found'];
                }
                return [false, 0, ''];
            case 'rkhunter':
                if (preg_match('/Warning:/i', $log) && preg_match('/Possible|Infected|Suspect|Suspicious/i', $log)) {
                    return [true, 1, 'rkhunter warnings found'];
                }
                return [false, 0, ''];
            case 'chkrootkit':
                if (preg_match('/INFECTED|Vulnerable/i', $log)) {
                    return [true, 1, 'chkrootkit infections found'];
                }
                return [false, 0, ''];
            case 'testssl':
                if (preg_match('/Failed|Not offered|vulnerable/i', $log)) {
                    return [true, 1, 'testssl findings — review log'];
                }
                return [false, 0, ''];
            default:
                return [false, 0, ''];
        }
    }

    public function hasFindings()
    {
        foreach ($this->scanResults() as $r) if ($r['found']) return true;
        return false;
    }

    /**
     * Run a fix for a tool (e.g. quarantine for ClamAV/YARA, update for AIDE baseline).
     * Returns success + a human message.
     */
    public function runFix($tool)
    {
        $t = $this->tools[$tool] ?? null;
        if (!$t) return ['success' => false, 'error' => 'Unknown tool'];
        $key = $tool;
        switch ($key) {
            case 'clamav':
                $log = $this->logDir . '/clamscan.log';
                $qdir = '/var/www/radiohosting/storage/security/quarantine';
                if (!is_dir($qdir)) @mkdir($qdir, 0755, true);
                // Quarantine any infected paths logged by clamscan
                exec("sudo mkdir -p " . escapeshellarg($qdir) . "; grep -iE 'FOUND' " . escapeshellarg($log) . " | grep -oE '/[^ ]+' | while read -r f; do if [ -f \"\$f\" ]; then sudo mv \"\$f\" " . escapeshellarg($qdir) . "/ 2>/dev/null; fi; done", $out);
                $this->logAction('fix', $key, 'Quarantined infected files to storage/security/quarantine');
                return ['success' => true, 'message' => 'Quarantined infected files detected by ClamAV.'];
            case 'yara':
                $log = $this->logDir . '/yarascan.log';
                $qdir = '/var/www/radiohosting/storage/security/quarantine';
                if (!is_dir($qdir)) @mkdir($qdir, 0755, true);
                exec("sudo mkdir -p " . escapeshellarg($qdir) . "; grep '^HIT:' " . escapeshellarg($log) . " | grep -oE '/[^ ]+' | while read -r f; do if [ -f \"\$f\" ]; then sudo mv \"\$f\" " . escapeshellarg($qdir) . "/ 2>/dev/null; fi; done", $out);
                $this->logAction('fix', $key, 'Quarantined YARA rule matches');
                return ['success' => true, 'message' => 'Quarantined files matching YARA rules.'];
            case 'aide':
                // Refresh AIDE baseline so clean state is re-recorded
                exec('sudo aideinit --yes 2>/dev/null; if [ -f /var/lib/aide/aide.db.new.gz ]; then sudo mv /var/lib/aide/aide.db.new.gz /var/lib/aide/aide.db.gz 2>/dev/null; fi', $out);
                $this->logAction('fix', $key, 'AIDE baseline refreshed');
                return ['success' => true, 'message' => 'AIDE integrity baseline refreshed.'];
            case 'rkhunter':
                exec('sudo rkhunter --propupd 2>/dev/null', $out);
                $this->logAction('fix', $key, 'rkhunter properties updated');
                return ['success' => true, 'message' => 'rkhunter baseline properties updated.'];
            case 'chkrootkit':
                // No auto-fix; purge typical artifacts not practical. Report only.
                $this->logAction('fix', $key, 'chkrootkit — manual review required (log)', 'warn');
                return ['success' => true, 'message' => 'chkrootkit requires manual review — see log.', 'warn' => true];
            default:
                $this->logAction('fix', $key, 'Fix requested — review log', 'warn');
                return ['success' => true, 'message' => 'No automated fix for this tool — review its log.', 'warn' => true];
        }
    }

    protected function logAction($action, $tool, $msg, $level = 'info')
    {
        $line = '[' . date('Y-m-d H:i:s') . "] [$level] $action:$tool — $msg\n";
        @file_put_contents($this->logDir . '/security-center.log', $line, FILE_APPEND);
    }
}
