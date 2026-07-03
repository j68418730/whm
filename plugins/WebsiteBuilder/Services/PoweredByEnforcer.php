<?php
namespace Plugins\WebsiteBuilder\Services;

class PoweredByEnforcer {
    private static $logoUrl = 'https://planethosts.com/assets/poweredby.png';

    public static function enforce(string $html, int $siteId): string {
        $footer = sprintf(
            '<div style="text-align:center;padding:12px 0;margin-top:30px;border-top:1px solid rgba(255,255,255,.06);font-size:11px;color:#64748b">
                <a href="https://planethosts.com" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;color:#94a3b8;text-decoration:none">
                    <img src="%s" alt="Planet Hosts" style="height:18px;vertical-align:middle"> Powered by Planet Hosts
                </a>
            </div>',
            self::$logoUrl
        );
        return str_ireplace('</body>', $footer . '</body>', $html);
    }
}
