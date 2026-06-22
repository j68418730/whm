<?php
// Version check system
// Returns current version info. Run daily via cron to check for updates.
define('PANEL_VERSION', '1.0.0-beta');
define('PANEL_VERSION_CODE', 1);
define('PANEL_VERSION_NAME', 'v1 Beta');
define('PANEL_SERIAL', 'PH-' . strtoupper(substr(md5('PlanetHosts2026'), 0, 12)));

function checkVersion() {
    $current = PANEL_VERSION_CODE;
    $updateUrl = 'https://raw.githubusercontent.com/j68418730/whm/main/VERSION';
    $remote = @file_get_contents($updateUrl, false, stream_context_create(['http' => ['timeout' => 5]]));
    if ($remote) {
        $data = json_decode($remote, true);
        if ($data && isset($data['version_code']) && $data['version_code'] > $current) {
            return [
                'update_available' => true,
                'current_version' => PANEL_VERSION_NAME,
                'new_version' => $data['version_name'] ?? 'Unknown',
                'new_version_code' => $data['version_code'],
                'changelog' => $data['changelog'] ?? '',
                'download_url' => $data['download_url'] ?? '/',
            ];
        }
    }
    return ['update_available' => false, 'current_version' => PANEL_VERSION_NAME];
}

// API endpoint: /api/version
function versionApi() {
    $check = checkVersion();
    header('Content-Type: application/json');
    echo json_encode([
        'version' => PANEL_VERSION_NAME,
        'version_code' => PANEL_VERSION_CODE,
        'serial' => PANEL_SERIAL,
        'update' => $check,
    ]);
    exit;
}
