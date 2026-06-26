<?php

namespace Plugins\Radio\Services;

class StreamingEngine
{
    protected $db;
    protected $drivers = [];
    protected static $instance = null;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
        $this->registerDefaultDrivers();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function registerDefaultDrivers()
    {
        $this->registerDriver('icecast', new IcecastDriver($this->db));
        $this->registerDriver('shoutcast', new ShoutcastDriver($this->db));
        // SHOUTcast v1 — only registers if binary is placed at /opt/planethosts/shoutcast1/sc_serv
        if (file_exists('/opt/planethosts/shoutcast1/sc_serv')) {
            $this->registerDriver('shoutcast1', new ShoutcastV1Driver($this->db));
        }
    }

    public function registerDriver($name, $driver)
    {
        $this->drivers[$name] = $driver;
    }

    public function driver($name)
    {
        if (!isset($this->drivers[$name])) {
            throw new \Exception("Streaming engine '{$name}' not registered");
        }
        return $this->drivers[$name];
    }

    public function getAvailableDrivers()
    {
        $available = [];
        foreach ($this->drivers as $name => $driver) {
            $available[$name] = [
                'name' => $driver->getDisplayName(),
                'installed' => $driver->isInstalled(),
                'version' => $driver->getVersion(),
            ];
        }
        return $available;
    }

    public function createStation($userId, $engine, $data = [])
    {
        $driver = $this->driver($engine);
        return $driver->createStation($userId, $data);
    }

    public function startStation($stationId)
    {
        $station = $this->getStation($stationId);
        $driver = $this->driver($station->engine);
        return $driver->startStation($station);
    }

    public function stopStation($stationId)
    {
        $station = $this->getStation($stationId);
        $driver = $this->driver($station->engine);
        return $driver->stopStation($station);
    }

    public function restartStation($stationId)
    {
        $this->stopStation($stationId);
        sleep(1);
        return $this->startStation($stationId);
    }

    public function deleteStation($stationId)
    {
        $station = $this->getStation($stationId);
        $driver = $this->driver($station->engine);
        return $driver->deleteStation($station);
    }

    public function getStation($stationId)
    {
        return $this->db->table('streaming_stations')->where('id', $stationId)->first();
    }

    public function getUserStations($userId)
    {
        return $this->db->table('streaming_stations')->where('user_id', $userId)->get() ?: [];
    }

    public function getAllStations()
    {
        return $this->db->table('streaming_stations')->orderBy('created_at', 'DESC')->get() ?: [];
    }

    public function getStationStats($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return null;
        $driver = $this->driver($station->engine);
        return $driver->getStats($station);
    }

    public function getStationLogs($stationId, $lines = 100)
    {
        $station = $this->getStation($stationId);
        if (!$station) return null;
        $driver = $this->driver($station->engine);
        return $driver->getLogs($station, $lines);
    }

    public function healthCheck($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return null;
        $driver = $this->driver($station->engine);
        return $driver->healthCheck($station);
    }

    public function installEngine($engine, $installPath = null)
    {
        $driver = $this->driver($engine);
        return $driver->install($installPath);
    }

    public function updateEngine($engine)
    {
        if (method_exists($this->driver($engine), 'update')) return $this->driver($engine)->update();
        return ['success' => false, 'error' => 'Update not supported'];
    }

    public function repairEngine($engine)
    {
        if (method_exists($this->driver($engine), 'repair')) return $this->driver($engine)->repair();
        return ['success' => false, 'error' => 'Repair not supported'];
    }

    public function getEngineStatus($engine)
    {
        $driver = $this->driver($engine);
        return [
            'installed' => $driver->isInstalled(),
            'version' => $driver->getVersion(),
            'running' => $driver->isRunning(),
            'binary' => $driver->getBinaryPath(),
        ];
    }

    // ─── Extended station operations ───

    public function suspendStation($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        return $this->driver($station->engine)->suspendStation($stationId);
    }

    public function resumeStation($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        return $this->driver($station->engine)->resumeStation($stationId);
    }

    public function cloneStation($stationId, $newName = null)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        if (!method_exists($this->driver($station->engine), 'cloneStation')) return ['success' => false, 'error' => 'Clone not supported'];
        return $this->driver($station->engine)->cloneStation($stationId, $newName);
    }

    public function renameStation($stationId, $newName)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        if (!method_exists($this->driver($station->engine), 'renameStation')) return ['success' => false, 'error' => 'Rename not supported'];
        return $this->driver($station->engine)->renameStation($stationId, $newName);
    }

    public function backupStation($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        if (!method_exists($this->driver($station->engine), 'backupStation')) return ['success' => false, 'error' => 'Backup not supported'];
        return $this->driver($station->engine)->backupStation($stationId);
    }

    public function restoreStation($stationId, $backupFile)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        if (!method_exists($this->driver($station->engine), 'restoreStation')) return ['success' => false, 'error' => 'Restore not supported'];
        return $this->driver($station->engine)->restoreStation($stationId, $backupFile);
    }

    public function generateStationSsl($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        if (!method_exists($this->driver($station->engine), 'generateSsl')) return ['success' => false, 'error' => 'SSL not supported'];
        return $this->driver($station->engine)->generateSsl($stationId);
    }

    public function configureAutodj($stationId, $type = 'liquidsoap')
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        if (!method_exists($this->driver($station->engine), 'configureAutodj')) return ['success' => false, 'error' => 'AutoDJ not supported'];
        return $this->driver($station->engine)->configureAutodj($stationId, $type);
    }

    public function getMonitoringData($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return null;
        if (!method_exists($this->driver($station->engine), 'getMonitoringData')) return ['running'=>false];
        return $this->driver($station->engine)->getMonitoringData($stationId);
    }

    public function autoRestartFailed()
    {
        $result = ['icecast' => 0, 'shoutcast' => 0];
        if (isset($this->drivers['shoutcast']) && method_exists($this->drivers['shoutcast'], 'autoRestartFailed'))
            $result['shoutcast'] = $this->drivers['shoutcast']->autoRestartFailed();
        return $result;
    }

    // ─── Station limit checks via packages ───

    public function getUserStationLimit($userId)
    {
        $user = $this->db->table('hosting_users')->where('id', $userId)->first();
        if (!$user || !$user->package_id) return 0;
        $package = $this->db->table('hosting_packages')->where('id', $user->package_id)->first();
        if (!$package) return 0;
        return $package->listener_limit > 0 ? $package->listener_limit : 1;
    }

    public function userCanCreateStation($userId, $engine = null)
    {
        $user = $this->db->table('hosting_users')->where('id', $userId)->first();
        if (!$user) return false;

        if ($engine) {
            $driver = $this->driver($engine);
            if (!$driver->isInstalled()) return false;
        }

        $existing = $this->db->table('streaming_stations')->where('user_id', $userId)->count();
        $maxStations = 999;
        if ($user->package_id) {
            $pkg = $this->db->table('hosting_packages')->where('id', $user->package_id)->first();
            if ($pkg) $maxStations = $pkg->listener_limit > 0 ? $pkg->listener_limit : 999;
        }
        return $existing < $maxStations;
    }
}
