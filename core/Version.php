<?php
// Version check system - release manifest based.
// Installed release comes from storage/current_release.json (written on update)
// or VERSION.json in the app tree. Available release comes from the update source.
define('PANEL_VERSION', '1.0.0-beta');
define('PANEL_VERSION_CODE', 100);
define('PANEL_VERSION_NAME', 'Ph-Whm v1 Beta');
define('PANEL_SERIAL', 'PH-' . strtoupper(substr(md5('PlanetHosts2026'), 0, 12)));

function checkVersion() {
    require_once BASE_PATH . '/core/Updates.php';
    $installed = \Core\Updates::installedManifest();
    $currentName = $installed['version'] ?? PANEL_VERSION_NAME;
    $remote = \Core\Updates::fetchRemoteManifest();
    if (\Core\Updates::isAvailable($remote)) {
        return [
            'update_available' => true,
            'current_version' => $currentName,
            'new_version' => $remote['version'] ?? 'Unknown',
            'new_version_code' => (int)($remote['version_code'] ?? 0),
            'channel' => $remote['channel'] ?? 'stable',
            'changelog' => $remote['release_notes'] ?? '',
            'download_url' => '/admin/settings/update',
        ];
    }
    return ['update_available' => false, 'current_version' => $currentName];
}

// API endpoint: /api/version
function versionApi() {
    $check = checkVersion();
    header('Content-Type: application/json');
    echo json_encode([
        'version' => PANEL_VERSION_NAME,
        'version_code' => PANEL_VERSION_CODE,
        'serial' => PANEL_SERIAL,
        'channel' => \Core\Updates::channel(),
        'update' => $check,
    ]);
    exit;
}