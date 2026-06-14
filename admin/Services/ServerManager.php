<?php

namespace Admin\Services;

class ServerManager
{
    public function getStats()
    {
        return [
            'cpu_load' => $this->getCpuLoad(),
            'cpu_cores' => $this->getCpuCores(),
            'ram_usage' => $this->getRamUsage(),
            'ram_total' => $this->getRamTotal(),
            'disk_usage' => $this->getDiskUsage(),
            'disk_total' => $this->getDiskTotal(),
            'uptime' => $this->getUptime(),
            'load_average' => $this->getLoadAverage(),
            'active_accounts' => $this->getActiveAccounts(),
            'service_status' => $this->getServiceStatus(),
            'hostname' => $this->getHostname(),
            'kernel' => $this->getKernel(),
            'os' => $this->getOs(),
            'cpu_model' => $this->getCpuModel(),
        ];
    }

    public function getCpuLoad()
    {
        $load = sys_getloadavg();
        return round($load[0], 1);
    }

    public function getCpuCores()
    {
        $cores = 1;
        if (is_file('/proc/cpuinfo')) {
            $cores = (int)shell_exec("grep -c ^processor /proc/cpuinfo 2>/dev/null") ?: 1;
        }
        return $cores;
    }

    public function getCpuModel()
    {
        if (is_file('/proc/cpuinfo')) {
            $model = shell_exec("grep -m1 'model name' /proc/cpuinfo 2>/dev/null | cut -d: -f2 | xargs");
            return $model ?: 'Unknown';
        }
        return 'Unknown';
    }

    public function getRamUsage()
    {
        if (is_file('/proc/meminfo')) {
            $total = (int)shell_exec("grep MemTotal /proc/meminfo 2>/dev/null | awk '{print \$2}'") ?: 1;
            $avail = (int)shell_exec("grep MemAvailable /proc/meminfo 2>/dev/null | awk '{print \$2}'") ?: 0;
            $used = $total - $avail;
            return round(($used / $total) * 100, 1);
        }
        return 0;
    }

    public function getRamTotal()
    {
        if (is_file('/proc/meminfo')) {
            $kb = (int)shell_exec("grep MemTotal /proc/meminfo 2>/dev/null | awk '{print \$2}'") ?: 0;
            return round($kb / 1024 / 1024, 1);
        }
        return 0;
    }

    public function getDiskUsage()
    {
        $df = shell_exec("df / 2>/dev/null | tail -1 | awk '{print \$5}'") ?: '0%';
        return (int)str_replace('%', '', trim($df));
    }

    public function getDiskTotal()
    {
        $gb = shell_exec("df -h / 2>/dev/null | tail -1 | awk '{print \$2}'") ?: '0G';
        return trim($gb);
    }

    public function getUptime()
    {
        if (is_file('/proc/uptime')) {
            $seconds = (float)file_get_contents('/proc/uptime');
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $mins = floor(($seconds % 3600) / 60);
            return "{$days}d {$hours}h {$mins}m";
        }
        return 'Unknown';
    }

    public function getLoadAverage()
    {
        if (is_file('/proc/loadavg')) {
            $parts = explode(' ', file_get_contents('/proc/loadavg'));
            return [
                '1min' => $parts[0] ?? '0',
                '5min' => $parts[1] ?? '0',
                '15min' => $parts[2] ?? '0',
            ];
        }
        return ['1min' => '0', '5min' => '0', '15min' => '0'];
    }

    public function getHostname()
    {
        return trim(shell_exec("hostname 2>/dev/null") ?: 'localhost');
    }

    public function getKernel()
    {
        return trim(shell_exec("uname -r 2>/dev/null") ?: 'Unknown');
    }

    public function getOs()
    {
        $os = 'Linux';
        if (is_file('/etc/os-release')) {
            $os = trim(shell_exec("grep PRETTY_NAME /etc/os-release 2>/dev/null | cut -d= -f2 | tr -d '\"'") ?: 'Linux');
        }
        return $os;
    }

    public function getServiceStatus()
    {
        $services = ['apache2', 'httpd', 'mariadb', 'mysql', 'exim', 'postfix', 'pure-ftpd', 'vsftpd', 'named', 'bind9', 'ssh', 'icecast'];
        $status = [];
        foreach ($services as $svc) {
            $output = shell_exec("systemctl is-active {$svc} 2>/dev/null");
            if ($output !== null) {
                $status[$svc] = trim($output);
            }
        }
        return $status ?: ['httpd' => 'unknown', 'mariadb' => 'unknown'];
    }

    public function getActiveAccounts()
    {
        $app = \Core\Application::getInstance();
        $db = $app->get('db');
        return $db->table('hosting_users')->where('status', 'active')->value('COUNT(*)') ?? 0;
    }

    public function restartService($name)
    {
        shell_exec("systemctl restart {$name} 2>/dev/null >/dev/null &");
        return true;
    }

    public function stopService($name)
    {
        shell_exec("systemctl stop {$name} 2>/dev/null >/dev/null &");
        return true;
    }

    public function startService($name)
    {
        shell_exec("systemctl start {$name} 2>/dev/null >/dev/null &");
        return true;
    }
}
