<?php

namespace Plugins\Radio\Services;

interface StreamingDriverInterface
{
    public function getDisplayName();
    public function getBinaryPath();
    public function getVersion();
    public function isInstalled();
    public function isRunning();
    public function install($installPath = null);
    public function uninstall();
    public function createStation($userId, $data = []);
    public function deleteStation($station);
    public function startStation($station);
    public function stopStation($station);
    public function generateConfig($station);
    public function getStats($station);
    public function getLogs($station, $lines = 100);
    public function healthCheck($station);
}
