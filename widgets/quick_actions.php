<?php
return [
    'key' => 'quick_actions',
    'name' => 'Quick Actions',
    'description' => 'Common admin tasks',
    'icon' => 'bi-lightning',
    'defaultZone' => 'main',
    'defaultSort' => 1,
    'height' => 1,
    'render' => function($uw) {
        $actions = [
            ['Create Account', '/admin/account/create', 'bi-person-plus'],
            ['View Packages', '/admin/packages', 'bi-box'],
            ['Security Center', '/admin/security', 'bi-shield-check'],
            ['Support Tickets', '/admin/support', 'bi-headset'],
            ['Billing', '/admin/billing', 'bi-credit-card'],
        ];
        $html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">';
        foreach ($actions as $a) {
            $html .= '<a href="' . $a[1] . '" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:6px;padding:10px;border-radius:8px"><i class="bi ' . $a[2] . '"></i> ' . $a[0] . '</a>';
        }
        $html .= '</div>';
        return $html;
    },
];
