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
}
