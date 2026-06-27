<?php
return [
    'key' => 'recent_activity',
    'name' => 'Recent Activity',
    'description' => 'Recent orders, accounts, and open tickets',
    'icon' => 'bi-clock-history',
    'defaultZone' => 'main',
    'defaultSort' => 5,
    'height' => 1,
    'render' => function($uw) {
        $recentOrders = \Core\WidgetManager::getInstance()->getData('recentOrders') ?: [];
        $recentAccounts = \Core\WidgetManager::getInstance()->getData('recentAccounts') ?: [];
        $recentTickets = \Core\WidgetManager::getInstance()->getData('recentTickets') ?: [];
        $html = '';
        if (!empty($recentOrders)) {
            $html .= '<div style="font-size:12px;color:#64748b;margin-bottom:6px">Recent Orders</div>';
            foreach ($recentOrders as $ord) {
                $html .= '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">';
                $html .= '<span>Order #' . $ord->id . ' — $' . number_format($ord->total ?? 0, 2) . '</span>';
                $html .= '<span style="color:#64748b;font-size:11px">' . htmlspecialchars($ord->status ?? '') . '</span></div>';
            }
        }
        if (!empty($recentAccounts)) {
            $html .= '<div style="font-size:12px;color:#64748b;margin-top:10px;margin-bottom:6px">New Accounts</div>';
            foreach ($recentAccounts as $a) {
                $html .= '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">';
                $html .= '<span>' . htmlspecialchars($a->username ?? $a->email ?? '') . '</span>';
                $html .= '<span style="color:#64748b;font-size:11px">' . htmlspecialchars($a->status ?? '') . ' — ' . htmlspecialchars($a->plan_type ?? '') . '</span></div>';
            }
        }
        if (!empty($recentTickets)) {
            $html .= '<div style="font-size:12px;color:#64748b;margin-top:10px;margin-bottom:6px">Open Tickets</div>';
            foreach ($recentTickets as $t) {
                $html .= '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">';
                $html .= '<span>#' . $t->id . ' — ' . htmlspecialchars(substr($t->subject ?? '', 0, 40)) . '</span>';
                $html .= '<a href="/admin/support/tickets" style="color:var(--accent);font-size:11px;text-decoration:none">View</a></div>';
            }
        }
        if (empty($recentOrders) && empty($recentAccounts) && empty($recentTickets)) {
            $html .= '<p style="color:#64748b;font-size:13px">No recent activity yet.</p>';
        }
        return $html;
    },
];
