<?php
namespace Core;

class WidgetManager
{
    protected static $instance;
    protected $widgets = [];
    protected $db;
    protected $userId = 0;

    public static function getInstance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function setDb($db) { $this->db = $db; return $this; }
    public function setUserId($id) { $this->userId = (int)$id; return $this; }

    public function register($key, $name, $description = '', $icon = 'bi-box', $renderCallback = null)
    {
        $this->widgets[$key] = new Widget($key, $name, $description, $icon, $renderCallback);
        return $this->widgets[$key];
    }

    public function getWidget($key)
    {
        return $this->widgets[$key] ?? null;
    }

    public function getAllWidgets()
    {
        return $this->widgets;
    }

    public function getUserWidgets($userId = null)
    {
        if ($userId) $this->setUserId($userId);
        if (!$this->db || !$this->userId) return [];

        $rows = $this->db->table('user_widgets')
            ->where('user_id', $this->userId)
            ->orderBy('zone', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->get() ?: [];

        $userWidgets = [];
        foreach ($rows as $r) {
            $userWidgets[] = [
                'id' => $r->id,
                'widget_key' => $r->widget_key,
                'zone' => $r->zone,
                'sort_order' => $r->sort_order,
                'settings' => json_decode($r->settings ?? '{}', true),
            ];
        }
        return $userWidgets;
    }

    public function ensureDefaults($userId = null)
    {
        if ($userId) $this->setUserId($userId);
        if (!$this->db || !$this->userId) return;

        $existing = $this->db->table('user_widgets')
            ->where('user_id', $this->userId)
            ->get() ?: [];

        $existingKeys = array_map(fn($r) => $r->widget_key, $existing);

        $defaults = [
            ['server_stats', 'main', 0],
            ['quick_actions', 'main', 1],
            ['service_status', 'main', 2],
            ['recent_logins', 'side', 0],
            ['revenue', 'side', 1],
        ];

        $sort = 0;
        foreach ($defaults as $d) {
            if (!in_array($d[0], $existingKeys)) {
                $this->db->table('user_widgets')->insertGetId([
                    'user_id' => $this->userId,
                    'widget_key' => $d[0],
                    'zone' => $d[1],
                    'sort_order' => $d[2] ?? $sort++,
                    'settings' => '{}',
                ]);
            }
        }
    }

    public function saveLayout($userId, $layout)
    {
        // $layout = [['id' => 1, 'zone' => 'main', 'sort_order' => 0], ...]
        foreach ($layout as $item) {
            $id = (int)($item['id'] ?? 0);
            $zone = $item['zone'] ?? 'main';
            $sort = (int)($item['sort_order'] ?? 0);
            if ($id) {
                $this->db->table('user_widgets')
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->update(['zone' => $zone, 'sort_order' => $sort]);
            }
        }
    }

    public function renderZone($zone, $userWidgets = null)
    {
        $html = '';
        if ($userWidgets === null) $userWidgets = $this->getUserWidgets();

        $zoneWidgets = array_filter($userWidgets, fn($w) => $w['zone'] === $zone);
        usort($zoneWidgets, fn($a, $b) => $a['sort_order'] - $b['sort_order']);

        foreach ($zoneWidgets as $uw) {
            $widget = $this->getWidget($uw['widget_key']);
            if (!$widget) continue;
            $html .= '<div class="widget-item" data-widget-id="' . $uw['id'] . '" data-widget-key="' . $uw['widget_key'] . '" draggable="true">';
            $html .= '<div class="widget-header"><span class="widget-handle"><i class="bi bi-grip-vertical"></i></span>';
            $html .= '<span class="widget-title"><i class="bi ' . $widget->getIcon() . '"></i> ' . htmlspecialchars($widget->getName()) . '</span>';
            $html .= '<span class="widget-actions">';
            $html .= '<button class="widget-toggle btn-icon" title="Minimize"><i class="bi bi-dash"></i></button>';
            $html .= '<button class="widget-remove btn-icon" title="Remove" data-key="' . $uw['widget_key'] . '"><i class="bi bi-x"></i></button>';
            $html .= '</span></div>';
            $html .= '<div class="widget-body">' . $widget->render($uw) . '</div>';
            $html .= '</div>';
        }
        return $html;
    }

    public function removeWidget($userId, $widgetKey)
    {
        $this->db->table('user_widgets')
            ->where('user_id', $userId)
            ->where('widget_key', $widgetKey)
            ->delete();
    }

    public function addWidget($userId, $widgetKey, $zone = 'main')
    {
        $maxSort = $this->db->table('user_widgets')
            ->where('user_id', $userId)
            ->where('zone', $zone)
            ->value('MAX(sort_order)') ?? 0;
        $this->db->table('user_widgets')->insertGetId([
            'user_id' => $userId,
            'widget_key' => $widgetKey,
            'zone' => $zone,
            'sort_order' => $maxSort + 1,
            'settings' => '{}',
        ]);
    }
}
