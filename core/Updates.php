<?php
namespace Core;

/**
 * Planet Hosts release/update helper.
 * Single source of truth for installed vs released version detection.
 */
class Updates
{
    const DEFAULT_SOURCE = 'https://raw.githubusercontent.com/j68418730/whm/master/VERSION.json';

    public static function env(string $key, string $default = ''): string
    {
        $v = getenv($key);
        if ($v !== false && $v !== '') return $v;
        $f = BASE_PATH . '/.env';
        if (is_file($f)) {
            foreach (file($f, FILE_IGNORE_NEW_LINES) as $line) {
                if (preg_match('/^' . preg_quote($key, '/') . '=(.*)$/', trim($line), $m)) {
                    $val = trim($m[1], " \t\r\n'\"");
                    if ($val !== '') return $val;
                }
            }
        }
        return $default;
    }

    public static function updateSource(): string
    {
        return self::env('UPDATE_SOURCE', self::DEFAULT_SOURCE);
    }

    public static function channel(): string
    {
        return self::env('UPDATE_CHANNEL', 'stable');
    }

    public static function manifestPath(): string
    {
        return BASE_PATH . '/VERSION.json';
    }

    public static function statePath(): string
    {
        return BASE_PATH . '/storage/current_release.json';
    }

    public static function alertPath(): string
    {
        return BASE_PATH . '/storage/update_available.json';
    }

    /** Installed release: storage state first (post-update), then VERSION.json in tree, then null. */
    public static function installedManifest(): ?array
    {
        foreach ([self::statePath(), self::manifestPath()] as $p) {
            if (is_file($p)) {
                $d = json_decode(@file_get_contents($p), true);
                if (is_array($d)) return $d;
            }
        }
        return null;
    }

    public static function installedVersionCode(): int
    {
        $m = self::installedManifest();
        if ($m && isset($m['version_code'])) return (int)$m['version_code'];
        return defined('PANEL_VERSION_CODE') ? (int)PANEL_VERSION_CODE : 0;
    }

    /** Fetch the release manifest from the configured update source. */
    public static function fetchRemoteManifest(int $timeout = 8): ?array
    {
        $ctx = stream_context_create([
            'http' => ['timeout' => $timeout, 'user_agent' => 'PlanetHosts-Updater/1.0'],
        ]);
        $raw = @file_get_contents(self::updateSource(), false, $ctx);
        if ($raw) {
            $d = json_decode($raw, true);
            if (is_array($d)) return $d;
        }
        return null;
    }

    public static function isAvailable(?array $remote): bool
    {
        if (!$remote || !isset($remote['version_code'])) return false;
        return (int)$remote['version_code'] > self::installedVersionCode();
    }

    public static function versionLabel(?array $m): string
    {
        if (!$m) return 'unknown';
        return $m['version'] ?? ('(' . ((int)($m['version_code'] ?? 0)) . ')');
    }

    /**
     * Refresh storage/update_available.json. Used by the cron check and the
     * "Check for Updates" endpoint so the dashboard banner stays in sync.
     * Returns the state array that was persisted.
     */
    public static function refreshAlertState(int $timeout = 8): array
    {
        $installed = self::installedManifest();
        $remote = self::fetchRemoteManifest($timeout);
        $avail = self::isAvailable($remote);
        $state = [
            'available' => $avail,
            'behind' => $avail ? 1 : 0,
            'current' => self::versionLabel($installed),
            'current_code' => self::installedVersionCode(),
            'latest' => $remote ? self::versionLabel($remote) : 'unknown',
            'latest_code' => $remote ? (int)($remote['version_code'] ?? 0) : 0,
            'channel' => $remote['channel'] ?? ('unreachable'),
            'checked_at' => date('c'),
        ];
        @file_put_contents(self::alertPath(), json_encode($state));
        return $state;
    }
}