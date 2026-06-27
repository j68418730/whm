<?php
return [
    'key' => 'stats_bar',
    'name' => 'Statistics Bar',
    'description' => 'Accounts, tickets, and revenue summary',
    'icon' => 'bi-bar-chart',
    'defaultZone' => 'main',
    'defaultSort' => 0,
    'height' => 1,
    'render' => function($uw) {
        $d = \Core\WidgetManager::getInstance()->getData('stats') ?: [];
        $html = '<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">';
        $html .= '<div class="stat-card"><h3>Accounts</h3><div class="value">' . ($d['total_accounts'] ?? 0) . '</div><div class="label">' . ($d['active_accounts'] ?? 0) . ' active / ' . ($d['suspended_accounts'] ?? 0) . ' suspended</div></div>';
        $html .= '<div class="stat-card"><h3>Tickets</h3><div class="value" style="color:#facc15">' . ($d['open_tickets'] ?? 0) . '</div><div class="label">Open tickets</div></div>';
        $html .= '<div class="stat-card"><h3>Revenue (Month)</h3><div class="value" style="color:#4ade80">$' . number_format($d['revenue_month'] ?? 0, 2) . '</div><div class="label">' . ($d['pending_invoices'] ?? 0) . ' unpaid ($' . number_format($d['pending_invoice_total'] ?? 0, 2) . ')</div></div>';
        if (($d['paypal_balance'] ?? null) !== null) {
            $html .= '<div class="stat-card"><h3>PayPal Balance</h3><div class="value" style="color:#00d4ff">$' . number_format($d['paypal_balance'], 2) . '</div><div class="label"><i class="fab fa-paypal"></i> Available</div></div>';
        }
        $html .= '</div>';
        return $html;
    },
];
