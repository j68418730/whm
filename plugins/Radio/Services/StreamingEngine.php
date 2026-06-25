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
