<?php
namespace Core;

class PortManager
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO(
            'mysql:host=localhost;dbname=radiohosting;charset=utf8mb4',
            'radiouser',
            'Skylinehosting171',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }

    // ─── Allocation ───────────────────────────────────────────────

    public function allocate($serviceType, $customerId = null, $stationId = null, $preferredPort = null)
    {
        $range = $this->getRange($serviceType);
        if (!$range) return null;

        // Try preferred port
        if ($preferredPort) {
            if ($this->isPortFree($preferredPort)) {
                $this->pdo->beginTransaction();
                try {
                    $this->insertPort($range->server_id, $serviceType, $customerId, $stationId, $preferredPort, null);
                    $this->logAction(null, $range->server_id, $serviceType, $customerId, $stationId, 'allocate', null, 'assigned', "Preferred port $preferredPort");
                    $this->pdo->commit();
                    $this->addFirewallRule($preferredPort, $serviceType);
                    return (object)['port_start' => $preferredPort, 'port_end' => null];
                } catch (\Exception $e) {
                    $this->pdo->rollBack();
                    return null;
                }
            }
            return null;
        }

        // Find first available in range
        $blockSize = (int)$range->allocation_size;
        for ($port = (int)$range->start_port; $port <= (int)$range->end_port; $port += $blockSize) {
            $endPort = $blockSize > 1 ? $port + $blockSize - 1 : null;

            // Check all ports in block
            $allFree = true;
            for ($p = $port; $p < $port + $blockSize; $p++) {
                if (!$this->isPortFree($p)) { $allFree = false; break; }
            }
            if (!$allFree) continue;

            // Verify ports are actually free on the system
            $allFree = true;
            for ($p = $port; $p < $port + $blockSize; $p++) {
                $fp = @fsockopen('127.0.0.1', $p, $errno, $errstr, 0.3);
                if ($fp) {
                    if (is_resource($fp)) fclose($fp);
                    $allFree = false;
                    break;
                }
            }
            if (!$allFree) continue;

            // Lock and allocate
            $this->pdo->beginTransaction();
            try {
                $this->insertPort($range->server_id, $serviceType, $customerId, $stationId, $port, $endPort);
                $this->logAction(null, $range->server_id, $serviceType, $customerId, $stationId, 'allocate', null, 'assigned', "Allocated port $port" . ($endPort ? "-$endPort" : ''));
                $this->pdo->commit();
                $this->addFirewallRule($port, $serviceType);
                if ($endPort) $this->addFirewallRule($endPort, $serviceType);
                return (object)['port_start' => $port, 'port_end' => $endPort];
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                continue;
            }
        }

        return null;
    }

    // ─── Release ───────────────────────────────────────────────────

    public function release($portId)
    {
        $port = $this->getById($portId);
        if (!$port) return false;

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("UPDATE stream_ports SET status='available', customer_id=NULL, station_id=NULL, allocated_at=NULL, updated_at=NOW() WHERE id=?")->execute([$portId]);
            $this->logAction($portId, $port->server_id, $port->service_type, null, null, 'release', 'assigned', 'available', "Released port {$port->port_start}");
            $this->pdo->commit();
            $this->removeFirewallRule($port->port_start);
            if ($port->port_end) $this->removeFirewallRule($port->port_end);
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function releaseByStation($stationId, $serviceType = null)
    {
        $query = "SELECT * FROM stream_ports WHERE station_id=?";
        $params = [$stationId];
        if ($serviceType) { $query .= " AND service_type=?"; $params[] = $serviceType; }
        $q = $this->pdo->prepare($query);
        $q->execute($params);
        $ports = $q->fetchAll(\PDO::FETCH_OBJ);
        $ok = true;
        foreach ($ports as $p) {
            if (!$this->release($p->id)) $ok = false;
        }
        return $ok;
    }

    public function releaseByCustomer($customerId)
    {
        $q = $this->pdo->prepare("SELECT * FROM stream_ports WHERE customer_id=? AND status='assigned'");
        $q->execute([$customerId]);
        $ports = $q->fetchAll(\PDO::FETCH_OBJ);
        $ok = true;
        foreach ($ports as $p) {
            if (!$this->release($p->id)) $ok = false;
        }
        return $ok;
    }

    // ─── Queries ───────────────────────────────────────────────────

    public function getById($id)
    {
        $q = $this->pdo->prepare("SELECT sp.*, ss.name AS server_name FROM stream_ports sp LEFT JOIN stream_servers ss ON sp.server_id=ss.id WHERE sp.id=?");
        $q->execute([$id]);
        return $q->fetch(\PDO::FETCH_OBJ);
    }

    public function getByStation($stationId, $serviceType = null)
    {
        $query = "SELECT sp.*, ss.name AS server_name FROM stream_ports sp LEFT JOIN stream_servers ss ON sp.server_id=ss.id WHERE sp.station_id=?";
        $params = [$stationId];
        if ($serviceType) { $query .= " AND sp.service_type=?"; $params[] = $serviceType; }
        $q = $this->pdo->prepare($query);
        $q->execute($params);
        return $q->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getByCustomer($customerId)
    {
        $q = $this->pdo->prepare("SELECT sp.*, ss.name AS server_name FROM stream_ports sp LEFT JOIN stream_servers ss ON sp.server_id=ss.id WHERE sp.customer_id=?");
        $q->execute([$customerId]);
        return $q->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getByServiceType($serviceType, $status = null)
    {
        $query = "SELECT sp.*, ss.name AS server_name FROM stream_ports sp LEFT JOIN stream_servers ss ON sp.server_id=ss.id WHERE sp.service_type=?";
        $params = [$serviceType];
        if ($status) { $query .= " AND sp.status=?"; $params[] = $status; }
        $q = $this->pdo->prepare($query);
        $q->execute($params);
        return $q->fetchAll(\PDO::FETCH_OBJ);
    }

    public function search($term)
    {
        $q = $this->pdo->prepare("SELECT sp.*, ss.name AS server_name FROM stream_ports sp LEFT JOIN stream_servers ss ON sp.server_id=ss.id WHERE sp.port_start LIKE ? OR sp.service_type LIKE ? OR sp.port_end LIKE ? ORDER BY sp.port_start LIMIT 50");
        $like = "%$term%";
        $q->execute([$like, $like, $like]);
        return $q->fetchAll(\PDO::FETCH_OBJ);
    }

    public function findConflicts()
    {
        return $this->pdo->query("SELECT sp1.id AS id1, sp2.id AS id2, sp1.port_start, sp1.service_type AS type1, sp2.service_type AS type2, sp1.customer_id AS cust1, sp2.customer_id AS cust2, sp1.station_id AS station1, sp2.station_id AS station2, sp1.server_id FROM stream_ports sp1 JOIN stream_ports sp2 ON sp1.port_start=sp2.port_start AND sp1.id<sp2.id WHERE sp1.status='assigned' AND sp2.status='assigned' ORDER BY sp1.port_start")->fetchAll(\PDO::FETCH_OBJ);
    }

    // ─── Stats ─────────────────────────────────────────────────────

    public function getUsage()
    {
        $stats = [];
        $ranges = $this->pdo->query("SELECT * FROM port_ranges ORDER BY sort_order")->fetchAll(\PDO::FETCH_OBJ);
        foreach ($ranges as $r) {
            $total = $r->end_port - $r->start_port + 1;
            $used = $this->pdo->prepare("SELECT COUNT(*) FROM stream_ports WHERE service_type=? AND status='assigned' AND port_start BETWEEN ? AND ?");
            $used->execute([$r->service_type, $r->start_port, $r->end_port]);
            $reserved = $this->pdo->prepare("SELECT COUNT(*) FROM stream_ports WHERE service_type=? AND status='reserved' AND port_start BETWEEN ? AND ?");
            $reserved->execute([$r->service_type, $r->start_port, $r->end_port]);
            $failed = $this->pdo->prepare("SELECT COUNT(*) FROM stream_ports WHERE service_type=? AND status='failed' AND port_start BETWEEN ? AND ?");
            $failed->execute([$r->service_type, $r->start_port, $r->end_port]);
            $stats[] = (object)[
                'service_type' => $r->service_type,
                'name' => $r->name,
                'start' => $r->start_port,
                'end' => $r->end_port,
                'total' => $total,
                'used' => (int)$used->fetchColumn(),
                'reserved' => (int)$reserved->fetchColumn(),
                'failed' => (int)$failed->fetchColumn(),
                'free' => $total - (int)$used->fetchColumn() - (int)$reserved->fetchColumn() - (int)$failed->fetchColumn(),
                'internal_only' => $r->internal_only,
            ];
        }
        return $stats;
    }

    public function getServerStats()
    {
        return $this->pdo->query("SELECT ss.*, (SELECT COUNT(*) FROM stream_ports WHERE server_id=ss.id AND status='assigned') AS assigned_ports, (SELECT COUNT(*) FROM stream_ports WHERE server_id=ss.id AND status='available') AS free_ports, (SELECT COUNT(*) FROM stream_ports WHERE server_id=ss.id AND status='reserved') AS reserved_ports, (SELECT COUNT(*) FROM stream_ports WHERE server_id=ss.id) AS total_ports FROM stream_servers ss")->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getHistory($limit = 100)
    {
        return $this->pdo->query("SELECT l.*, sp.port_start, sp.service_type AS svc_type FROM port_allocation_log l LEFT JOIN stream_ports sp ON l.port_id=sp.id ORDER BY l.created_at DESC LIMIT $limit")->fetchAll(\PDO::FETCH_OBJ);
    }

    // ─── Range Management ──────────────────────────────────────────

    public function getRanges()
    {
        return $this->pdo->query("SELECT pr.*, ss.name AS server_name FROM port_ranges pr LEFT JOIN stream_servers ss ON pr.server_id=ss.id ORDER BY pr.sort_order")->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getRange($serviceType, $serverId = 1)
    {
        $q = $this->pdo->prepare("SELECT * FROM port_ranges WHERE service_type=? AND server_id=? LIMIT 1");
        $q->execute([$serviceType, $serverId]);
        return $q->fetch(\PDO::FETCH_OBJ);
    }

    public function getServers()
    {
        return $this->pdo->query("SELECT * FROM stream_servers ORDER BY name")->fetchAll(\PDO::FETCH_OBJ);
    }

    // ─── Validation ─────────────────────────────────────────────────

    public function validatePort($port, $serviceType)
    {
        $errors = [];

        // Check not in DB
        if (!$this->isPortFree($port)) {
            $errors[] = "Port $port is already allocated in database";
        }

        // Check socket is free
        $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.3);
        if ($fp) {
            if (is_resource($fp)) fclose($fp);
            $errors[] = "Port $port is in use on the server (err: $errstr)";
        }

        // Check firewall has rule
        $hasFirewall = $this->checkFirewallRule($port);
        if (!$hasFirewall) {
            $this->addFirewallRule($port, $serviceType);
        }

        return $errors;
    }

    // ─── Firewall ───────────────────────────────────────────────────

    public function addFirewallRule($port, $serviceType)
    {
        $proto = $this->getProtocol($serviceType);
        $cmd = "/usr/sbin/iptables -C INPUT -p $proto --dport $port -j ACCEPT 2>/dev/null";
        $existing = exec($cmd, $output, $code);
        if ($code !== 0) {
            exec("/usr/sbin/iptables -A INPUT -p $proto --dport $port -j ACCEPT 2>/dev/null");
            exec("/usr/sbin/iptables-save > /etc/iptables/rules.v4 2>/dev/null");
        }
        // Also add to firewalld if running
        exec("firewall-cmd --state 2>/dev/null", $fwOut, $fwCode);
        if ($fwCode === 0) {
            exec("firewall-cmd --add-port={$port}/{$proto} --permanent 2>/dev/null");
            exec("firewall-cmd --reload 2>/dev/null");
        }
        $this->logAction(null, null, $serviceType, null, null, 'firewall_add', null, null, "Firewall rule added for port $port/$proto");
    }

    public function removeFirewallRule($port)
    {
        foreach (['tcp', 'udp'] as $proto) {
            exec("/usr/sbin/iptables -D INPUT -p $proto --dport $port -j ACCEPT 2>/dev/null");
        }
        exec("/usr/sbin/iptables-save > /etc/iptables/rules.v4 2>/dev/null");
        exec("firewall-cmd --state 2>/dev/null", $fwOut, $fwCode);
        if ($fwCode === 0) {
            exec("firewall-cmd --remove-port={$port}/tcp --permanent 2>/dev/null");
            exec("firewall-cmd --remove-port={$port}/udp --permanent 2>/dev/null");
            exec("firewall-cmd --reload 2>/dev/null");
        }
        $this->logAction(null, null, null, null, null, 'firewall_remove', null, null, "Firewall rule removed for port $port");
    }

    public function checkFirewallRule($port)
    {
        exec("/usr/sbin/iptables -C INPUT -p tcp --dport $port -j ACCEPT 2>/dev/null", $out, $code);
        return $code === 0;
    }

    // ─── Internal ───────────────────────────────────────────────────

    protected function isPortFree($port)
    {
        $q = $this->pdo->prepare("SELECT id FROM stream_ports WHERE port_start=? AND status IN ('assigned','reserved')");
        $q->execute([$port]);
        return !$q->fetch();
    }

    protected function insertPort($serverId, $serviceType, $customerId, $stationId, $portStart, $portEnd)
    {
        $this->pdo->prepare("INSERT INTO stream_ports (server_id, service_type, customer_id, station_id, port_start, port_end, status, allocated_at, created_at) VALUES (?,?,?,?,?,?,'assigned',NOW(),NOW())")->execute([$serverId, $serviceType, $customerId, $stationId, $portStart, $portEnd]);
    }

    protected function logAction($portId, $serverId, $serviceType, $customerId, $stationId, $action, $oldStatus, $newStatus, $message = null)
    {
        try {
            $this->pdo->prepare("INSERT INTO port_allocation_log (port_id, server_id, service_type, customer_id, station_id, action, old_status, new_status, message, created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())")->execute([$portId, $serverId, $serviceType, $customerId, $stationId, $action, $oldStatus, $newStatus, $message]);
        } catch (\Exception $e) {}
    }

    protected function getProtocol($serviceType)
    {
        $udp = ['webrtc_media', 'rtsp'];
        return in_array($serviceType, $udp) ? 'udp' : 'tcp';
    }
}
