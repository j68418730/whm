<?php
namespace Admin\Controllers\Api;

use Core\PortManager;

class PortController
{
    protected $portManager;

    public function __construct()
    {
        $this->portManager = new PortManager();
    }

    public function allocate()
    {
        header('Content-Type: application/json');
        $serviceType = $_POST['service_type'] ?? $_GET['service_type'] ?? '';
        $customerId = $_POST['customer_id'] ?? $_GET['customer_id'] ?? null;
        $stationId = $_POST['station_id'] ?? $_GET['station_id'] ?? null;
        $preferred = $_POST['preferred'] ?? $_GET['preferred'] ?? null;

        if (!$serviceType) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'service_type is required']);
            exit;
        }

        $result = $this->portManager->allocate($serviceType, $customerId, $stationId, $preferred);
        if ($result) {
            echo json_encode(['success' => true, 'port' => $result->port_start, 'port_end' => $result->port_end]);
        } else {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'No available ports for ' . $serviceType]);
        }
        exit;
    }

    public function release($id)
    {
        header('Content-Type: application/json');
        $ok = $this->portManager->release((int)$id);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Port released']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Port not found or release failed']);
        }
        exit;
    }

    public function status($port)
    {
        header('Content-Type: application/json');
        $p = $this->portManager->getById((int)$port);
        if (!$p) {
            // Check by port number
            $db = \Core\Application::getInstance()->get('db');
            $p = $db->pdo()->prepare("SELECT * FROM stream_ports WHERE port_start=? LIMIT 1");
            $p->execute([(int)$port]);
            $p = $p->fetch(\PDO::FETCH_OBJ);
        }
        if ($p) {
            echo json_encode(['success' => true, 'port' => $p]);
        } else {
            echo json_encode(['success' => true, 'port' => null, 'message' => 'Port is free']);
        }
        exit;
    }

    public function listAll()
    {
        header('Content-Type: application/json');
        $serviceType = $_GET['service_type'] ?? '';
        $status = $_GET['status'] ?? '';
        if ($serviceType) {
            $ports = $this->portManager->getByServiceType($serviceType, $status ?: null);
        } else {
            $db = \Core\Application::getInstance()->get('db');
            $ports = $db->pdo()->query("SELECT sp.*, ss.name AS server_name FROM stream_ports sp LEFT JOIN stream_servers ss ON sp.server_id=ss.id ORDER BY sp.port_start LIMIT 500")->fetchAll(\PDO::FETCH_OBJ);
        }
        echo json_encode(['success' => true, 'ports' => $ports]);
        exit;
    }

    public function stats()
    {
        header('Content-Type: application/json');
        $stats = $this->portManager->getUsage();
        $servers = $this->portManager->getServerStats();
        echo json_encode(['success' => true, 'usage' => $stats, 'servers' => $servers]);
        exit;
    }

    public function validatePort()
    {
        header('Content-Type: application/json');
        $port = (int)($_GET['port'] ?? $_POST['port'] ?? 0);
        $serviceType = $_GET['service_type'] ?? $_POST['service_type'] ?? 'icecast';
        if (!$port) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'port is required']);
            exit;
        }
        $errors = $this->portManager->validatePort($port, $serviceType);
        echo json_encode([
            'success' => empty($errors),
            'port' => $port,
            'errors' => $errors,
            'firewall_ok' => $this->portManager->checkFirewallRule($port),
        ]);
        exit;
    }
}
