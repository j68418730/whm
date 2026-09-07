<?php

namespace Admin\Services;

/**
 * CartThemes — storefront theme registry for the Planet Hosts shopping cart.
 * Powers /store, /store/theme.css and /store/embed.js.
 */
class CartThemes
{
    protected static array $themes = [
        'planethosts' => [
            'id' => 'planethosts', 'name' => 'Planet Hosts (Brand)', 'description' => 'Official Planet Hosts storefront — deep navy with brand blue accents.',
            'logo_a' => 'Planet', 'logo_b' => 'Hosts', 'header_sub' => 'Premium Hosting & Radio Streaming',
            'icons' => 'fa', 'footer' => '© 2026 Planet Hosts. All rights reserved.', 'bootstrap' => false,
            'palette' => ['bg' => '#0a0e1a', 'card' => 'rgba(15,23,42,.65)', 'border' => 'rgba(56,189,248,.15)', 'text' => '#e2e8f0', 'muted' => '#94a3b8', 'accent' => '#008cff', 'accent2' => '#38bdf8', 'header_bg' => 'linear-gradient(135deg,rgba(0,140,255,.15),rgba(56,189,248,.05))', 'btn_text' => '#fff'],
        ],
        'midnight' => [
            'id' => 'midnight', 'name' => 'Midnight', 'description' => 'Near-black storefront with violet accents — sleek and modern.',
            'logo_a' => 'Midnight', 'logo_b' => 'Host', 'header_sub' => 'Hosting after dark',
            'icons' => 'bi', 'footer' => '© 2026 Midnight Host. All rights reserved.', 'bootstrap' => false,
            'palette' => ['bg' => '#0b0710', 'card' => 'rgba(30,20,45,.7)', 'border' => 'rgba(168,85,247,.18)', 'text' => '#ece9f5', 'muted' => '#a29bb5', 'accent' => '#a855f7', 'accent2' => '#c084fc', 'header_bg' => 'linear-gradient(135deg,rgba(168,85,247,.18),rgba(88,28,135,.08))', 'btn_text' => '#fff'],
        ],
        'clean' => [
            'id' => 'clean', 'name' => 'Clean Light', 'description' => 'Bright, minimal light theme for white-labeled sites.',
            'logo_a' => 'Clean', 'logo_b' => 'Cart', 'header_sub' => 'Simple, fast hosting',
            'icons' => 'emoji', 'footer' => '© 2026 Clean Cart. All rights reserved.', 'bootstrap' => false,
            'palette' => ['bg' => '#f5f7fb', 'card' => '#ffffff', 'border' => '#dbe4f0', 'text' => '#1e293b', 'muted' => '#64748b', 'accent' => '#2563eb', 'accent2' => '#3b82f6', 'header_bg' => 'linear-gradient(135deg,#eff6ff,#f5f7fb)', 'btn_text' => '#fff'],
        ],
        'aurora' => [
            'id' => 'aurora', 'name' => 'Aurora', 'description' => 'Gradient-heavy theme with teal/pink aurora tones.',
            'logo_a' => 'Aurora', 'logo_b' => 'Store', 'header_sub' => 'Powered by the northern lights',
            'icons' => 'emoji', 'footer' => '© 2026 Aurora Store. All rights reserved.', 'bootstrap' => false,
            'palette' => ['bg' => '#04121a', 'card' => 'rgba(6,38,48,.7)', 'border' => 'rgba(45,212,191,.2)', 'text' => '#e0fbff', 'muted' => '#8fb8bd', 'accent' => '#2dd4bf', 'accent2' => '#f472b6', 'header_bg' => 'linear-gradient(135deg,rgba(45,212,191,.15),rgba(244,114,182,.08))', 'btn_text' => '#04212a'],
        ],
    ];

    public static function all(): array
    {
        return array_map(fn($t) => $t + ['footer' => $t['footer']], array_values(self::$themes));
    }

    public static function get($id): array
    {
        $id = strtolower((string)$id);
        return self::$themes[$id] ?? self::$themes['planethosts'];
    }

    public static function icons($id): string
    {
        return self::get($id)['icons'] ?? '';
    }

    public static function isBootstrap($id): bool
    {
        return (bool)(self::get($id)['bootstrap'] ?? false);
    }

    public static function defaultFooter($id): string
    {
        return self::get($id)['footer'] ?? '';
    }

    public static function fullCss($id): string
    {
        $p = self::get($id)['palette'] ?? self::get('planethosts')['palette'];
        $light = $id === 'clean';
        $shadow = $light ? '0 1px 3px rgba(15,23,42,.08)' : '0 4px 20px rgba(0,0,0,.25)';
        return <<<CSS
.ph-store.phc-{$id}{
  --phc-bg:{$p['bg']};--phc-card:{$p['card']};--phc-border:{$p['border']};--phc-text:{$p['text']};--phc-muted:{$p['muted']};--phc-accent:{$p['accent']};--phc-accent2:{$p['accent2']};
  font-family:Inter,system-ui,-apple-system,sans-serif;color:var(--phc-text);background:var(--phc-bg);padding:0;margin:0;min-height:100vh;box-sizing:border-box;
}
.ph-store.phc-{$id} *,.ph-store.phc-{$id} *::before,.ph-store.phc-{$id} *::after{box-sizing:border-box}
.phc-{$id} .phc-header{display:flex;justify-content:space-between;align-items:center;padding:18px 24px;background:{$p['header_bg']};border-bottom:1px solid var(--phc-border);flex-wrap:wrap;gap:10px}
.phc-{$id} .phc-brand{display:flex;align-items:center;gap:12px}
.phc-{$id} .phc-logo{height:40px;width:auto;border-radius:6px}
.phc-{$id} .phc-logo-text{font-size:20px;font-weight:800;letter-spacing:-.5px;color:var(--phc-text)}
.phc-{$id} .phc-logo-text b{color:var(--phc-accent)}
.phc-{$id} .phc-header-sub{font-size:11px;color:var(--phc-muted);margin-top:2px}
.phc-{$id} .phc-cart-badge{font-size:12px;font-weight:600;color:var(--phc-accent);background:color-mix(in srgb,var(--phc-accent) 12%,transparent);border:1px solid var(--phc-border);padding:6px 14px;border-radius:99px}
.phc-{$id} .phc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;padding:24px}
.phc-{$id} .phc-product{background:var(--phc-card);border:1px solid var(--phc-border);border-radius:14px;padding:18px;transition:.18s;box-shadow:{$shadow}}
.phc-{$id} .phc-product:hover{border-color:var(--phc-accent);transform:translateY(-2px)}
.phc-{$id} .phc-name{font-size:15px;font-weight:700;color:var(--phc-text)}
.phc-{$id} .phc-desc{font-size:12px;color:var(--phc-muted);margin:6px 0 10px;line-height:1.5;min-height:18px}
.phc-{$id} .phc-price{font-size:20px;font-weight:800;color:var(--phc-text);margin-bottom:12px}
.phc-{$id} .phc-price small{font-size:11px;color:var(--phc-muted);font-weight:500}
.phc-{$id} .phc-btn{width:100%;padding:10px 16px;border:none;border-radius:9px;background:var(--phc-accent);color:{$p['btn_text']};font-size:13px;font-weight:700;cursor:pointer;transition:.15s}
.phc-{$id} .phc-btn:hover{background:var(--phc-accent2)}
.phc-{$id} .phc-cart{max-width:860px;margin:0 auto 24px;background:var(--phc-card);border:1px solid var(--phc-border);border-radius:14px;padding:18px 22px;box-shadow:{$shadow}}
.phc-{$id} .phc-cart h4{margin:0 0 12px;font-size:15px;color:var(--phc-text)}
.phc-{$id} .phc-item{display:flex;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px solid var(--phc-border);font-size:13px;color:var(--phc-text)}
.phc-{$id} .phc-item:last-child{border-bottom:none}
.phc-{$id} .phc-total{margin-top:12px;padding-top:12px;border-top:1px solid var(--phc-border);font-size:16px;font-weight:800;color:var(--phc-text);text-align:right}
.phc-{$id} .phc-footer{margin:0 24px 14px;padding:14px;border-radius:10px;background:var(--phc-card);border:1px solid var(--phc-border);font-size:12px;color:var(--phc-muted);text-align:center}
.phc-{$id} .phc-foot{text-align:center;font-size:10px;color:var(--phc-muted);opacity:.7;padding:0 0 18px}
.phc-{$id} .phc-empty{color:var(--phc-muted);font-size:12px;font-style:italic;padding:6px 0}
@media(max-width:640px){.phc-{$id} .phc-grid{grid-template-columns:1fr;padding:16px}.phc-{$id} .phc-header{padding:14px 16px}}
CSS;
    }
}
