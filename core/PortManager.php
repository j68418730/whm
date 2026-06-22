<?php
namespace Core;

class PortManager
{
    protected $pdo;

    // Define port ranges per service type
    public static $ranges = [
        'icecast' => ['start' => 6000, 'end' => 10000],
        'game_server' => ['start' => 27000, 'end' => 28000],
        'minecraft' => ['start' => 25560, 'end' => 25660],
        'voice' => ['start' => 10000, 'end' => 20000],
    ];

    public function __construct()
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    }

    public function allocate($serviceType, $serviceId = null, $userId = null, $preferredPort = null)
    {
        $range = self::$ranges[$serviceType] ?? ['start' => 10000, 'end' => 60000];

        // Try preferred port first
        if ($preferredPort) {
            $check = $this->pdo->prepare("SELECT id FROM port_allocations WHERE port = ?");
            $check->execute([$preferredPort]);
            if (!$check->fetch()) {
                $this->pdo->prepare("INSERT INTO port_allocations (port, service_type, service_id, user_id, status) VALUES (?, ?, ?, ?, 'active')")
                    ->execute([$preferredPort, $serviceType, $serviceId, $userId]);
                return $preferredPort;
            }
        }

        // Find first available in range
        for ($port = $range['start']; $port <= $range['end']; $port++) {
            $check = $this->pdo->prepare("SELECT id FROM port_allocations WHERE port = ?");
            $check->execute([$port]);
            if (!$check->fetch()) {
                // Verify port is actually free on the system
                $fp = @fsockopen("127.0.0.1", $port, $errno, $errstr, 0.3);
                if (!$fp) {
                    $this->pdo->prepare("INSERT INTO port_allocations (port, service_type, service_id, user_id, status) VALUES (?, ?, ?, ?, 'active')")
                        ->execute([$port, $serviceType, $serviceId, $userId]);
                    return $port;
                }
                if (is_resource($fp)) fclose($fp);
            }
        }

        return null; // No ports available
    }

    public function release($port)
    {
        $this->pdo->prepare("DELETE FROM port_allocations WHERE port = ?")->execute([$port]);
    }

    public function isAvailable($port)
    {
        $check = $this->pdo->prepare("SELECT id FROM port_allocations WHERE port = ?");
        $check->execute([$port]);
        return !$check->fetch();
    }

    public function getByService($serviceType, $serviceId)
    {
        $q = $this->pdo->prepare("SELECT * FROM port_allocations WHERE service_type = ? AND service_id = ?");
        $q->execute([$serviceType, $serviceId]);
        return $q->fetch(\PDO::FETCH_OBJ);
    }

    public function listAllocated()
    {
        return $this->pdo->query("SELECT p.*, 
            CASE WHEN p.service_type = 'icecast' THEN (SELECT username FROM hosting_users WHERE id = p.user_id)
                 WHEN p.service_type = 'game_server' THEN (SELECT server_name FROM game_servers WHERE id = p.service_id)
                 ELSE NULL END as service_name
            FROM port_allocations p ORDER BY p.port")->fetchAll(\PDO::FETCH_OBJ);
    }
}
