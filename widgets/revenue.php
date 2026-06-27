<?php
return [
    'key' => 'revenue',
    'name' => 'Revenue Overview',
    'description' => 'Monthly billing summary',
    'icon' => 'bi-currency-dollar',
    'defaultZone' => 'side',
    'defaultSort' => 1,
    'height' => 1,
    'render' => function($uw) {
        $app = \Core\Application::getInstance();
        $db = $app->get('db');
        $total = $db->table('payments')->where('status', 'completed')->value('SUM(amount)') ?: 0;
        $month = $db->table('payments')->where('status', 'completed')->where('created_at', '>=', date('Y-m-01'))->value('SUM(amount)') ?: 0;
        $pending = $db->table('invoices')->where('status', 'pending')->value('SUM(total)') ?: 0;
        $html = '<div class="stats-grid" style="grid-template-columns:1fr 1fr 1fr">';
        $html .= '<div class="stat-card"><div class="label">Total Revenue</div><div class="value" style="font-size:20px">$' . number_format($total, 2) . '</div></div>';
        $html .= '<div class="stat-card"><div class="label">This Month</div><div class="value" style="font-size:20px">$' . number_format($month, 2) . '</div></div>';
        $html .= '<div class="stat-card"><div class="label">Pending</div><div class="value" style="font-size:20px">$' . number_format($pending, 2) . '</div></div>';
        $html .= '</div>';
        return $html;
    },
];
