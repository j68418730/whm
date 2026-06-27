<?php
namespace Core;

class WidgetManager
{
    protected static $instance;
    protected $widgets = [];
    protected $db;
    protected $userId = 0;
    protected $sharedData = [];

    public static function getInstance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function setDb($db) { $this->db = $db; return $this; }
    public function setUserId($id) { $this->userId = (int)$id; return $this; }

    public function setData($key, $value) { $this->sharedData[$key] = $value; return $this; }
    public function getData($key = null) { return $key ? ($this->sharedData[$key] ?? null) : $this->sharedData; }

    public function register($key, $name, $description = '', $icon = 'bi-box', $renderCallback = null)
    {
        $this->widgets[$key] = new Widget($key, $name, $description, $icon, $renderCallback);
        return $this->widgets[$key];
    }

    public function loadFromFolder($folderPath)
    {
        if (!is_dir($folderPath)) return;
        $files = glob(rtrim($folderPath, '/\\') . DIRECTORY_SEPARATOR . '*.php');
        sort($files);
        foreach ($files as $file) {
            $config = require $file;
            if (!is_array($config) || !isset($config['key'], $config['name'], $config['render'])) continue;
            $w = $this->register($config['key'], $config['name'], $config['description'] ?? '', $config['icon'] ?? 'bi-box', $config['render']);
            if (isset($config['defaultZone'])) $w->setDefaultZone($config['defaultZone']);
            if (isset($config['defaultSort'])) $w->setDefaultSort($config['defaultSort']);
            if (isset($config['height'])) $w->setHeight($config['height']);
        }
    }

    public function getWidget($key)
    {
        return $this->widgets[$key] ?? null;
    }

    public function getAllWidgets()
    {
        return $this->widgets;
    }

    public function getUserWidgets($userId = null, $layoutName = null)
    {
        if ($userId) $this->setUserId($userId);
        if (!$this->db || !$this->userId) return [];

        $q = $this->db->table('user_widgets')
            ->where('user_id', $this->userId);

        if ($layoutName) {
            $q = $q->where('layout_name', $layoutName);
        } else {
            $q = $q->where('layout_name', 'default');
        }

        $rows = $q->orderBy('zone', 'ASC')->orderBy('sort_order', 'ASC')->get() ?: [];

        $userWidgets = [];
        foreach ($rows as $r) {
            $userWidgets[] = [
                'id' => $r->id,
                'widget_key' => $r->widget_key,
                'zone' => $r->zone,
                'sort_order' => $r->sort_order,
                'width' => (int)($r->width ?? 1),
                'collapsed' => (int)($r->collapsed ?? 0),
                'hidden' => (int)($r->hidden ?? 0),
                'pinned' => (int)($r->pinned ?? 0),
                'layout_name' => $r->layout_name ?? 'default',
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
            ->where('layout_name', 'default')
            ->get() ?: [];

        $existingKeys = array_map(fn($r) => $r->widget_key, $existing);

        $defaults = [
            ['stats_bar', 'main', 0],
            ['server_health', 'main', 1],
            ['services', 'main', 2],
            ['streaming_engines', 'main', 3],
            ['hostname_status', 'main', 4],
            ['quick_actions', 'side', 0],
            ['recent_activity', 'side', 1],
            ['recent_logins', 'side', 2],
            ['revenue', 'side', 3],
        ];

        foreach ($defaults as $d) {
            if (!in_array($d[0], $existingKeys)) {
                $this->db->table('user_widgets')->insertGetId([
                    'user_id' => $this->userId,
                    'widget_key' => $d[0],
                    'zone' => $d[1],
                    'sort_order' => $d[2],
                    'width' => 1,
                    'collapsed' => 0,
                    'hidden' => 0,
                    'pinned' => 0,
                    'layout_name' => 'default',
                    'settings' => '{}',
                ]);
            }
        }
    }

    // ---- State toggles ----

    public function toggleCollapse($userId, $widgetId)
    {
        $row = $this->db->table('user_widgets')
            ->where('id', $widgetId)->where('user_id', $userId)->first();
        if (!$row) return false;
        $new = $row->collapsed ? 0 : 1;
        $this->db->table('user_widgets')->where('id', $widgetId)->update(['collapsed' => $new]);
        return ['id' => $widgetId, 'collapsed' => $new];
    }

    public function setCollapsed($userId, $widgetId, $state)
    {
        $this->db->table('user_widgets')
            ->where('id', $widgetId)->where('user_id', $userId)
            ->update(['collapsed' => $state ? 1 : 0]);
    }

    public function togglePin($userId, $widgetId)
    {
        $row = $this->db->table('user_widgets')
            ->where('id', $widgetId)->where('user_id', $userId)->first();
        if (!$row) return false;
        $new = $row->pinned ? 0 : 1;
        $this->db->table('user_widgets')->where('id', $widgetId)->update(['pinned' => $new]);
        return ['id' => $widgetId, 'pinned' => $new];
    }

    public function toggleHide($userId, $widgetId)
    {
        $row = $this->db->table('user_widgets')
            ->where('id', $widgetId)->where('user_id', $userId)->first();
        if (!$row) return false;
        $new = $row->hidden ? 0 : 1;
        $this->db->table('user_widgets')->where('id', $widgetId)->update(['hidden' => $new]);
        return ['id' => $widgetId, 'hidden' => $new];
    }

    public function setWidth($userId, $widgetId, $width)
    {
        $w = max(1, min(3, (int)$width));
        $this->db->table('user_widgets')
            ->where('id', $widgetId)->where('user_id', $userId)
            ->update(['width' => $w]);
        return ['id' => $widgetId, 'width' => $w];
    }

    public function getLayouts($userId)
    {
        $rows = $this->db->table('widget_layouts')
            ->where('user_id', $userId)
            ->orderBy('layout_name', 'ASC')
            ->get() ?: [];
        $layouts = [];
        foreach ($rows as $r) {
            $layouts[] = $r->layout_name;
        }
        // Always include default
        if (!in_array('default', $layouts)) array_unshift($layouts, 'default');
        return $layouts;
    }

    public function saveLayoutSnapshot($userId, $layoutName)
    {
        $widgets = $this->getUserWidgets($userId, $layoutName);
        $snapshot = json_encode($widgets);
        $existing = $this->db->table('widget_layouts')
            ->where('user_id', $userId)->where('layout_name', $layoutName)->first();
        if ($existing) {
            $this->db->table('widget_layouts')
                ->where('id', $existing->id)->update(['snapshot' => $snapshot]);
        } else {
            $this->db->table('widget_layouts')->insertGetId([
                'user_id' => $userId,
                'layout_name' => $layoutName,
                'snapshot' => $snapshot,
            ]);
        }
    }

    public function loadLayoutSnapshot($userId, $layoutName)
    {
        $existing = $this->db->table('widget_layouts')
            ->where('user_id', $userId)->where('layout_name', $layoutName)->first();
        if (!$existing || !$existing->snapshot) return null;
        return json_decode($existing->snapshot, true) ?: null;
    }

    public function applyLayoutSnapshot($userId, $layoutName)
    {
        $snapshot = $this->loadLayoutSnapshot($userId, $layoutName);
        if (!$snapshot) return false;

        // Delete current widgets for this layout
        $this->db->table('user_widgets')
            ->where('user_id', $userId)->where('layout_name', $layoutName)->delete();

        // Re-insert from snapshot
        foreach ($snapshot as $uw) {
            $this->db->table('user_widgets')->insertGetId([
                'user_id' => $userId,
                'widget_key' => $uw['widget_key'],
                'zone' => $uw['zone'] ?? 'main',
                'sort_order' => $uw['sort_order'] ?? 0,
                'width' => $uw['width'] ?? 1,
                'collapsed' => $uw['collapsed'] ?? 0,
                'hidden' => $uw['hidden'] ?? 0,
                'pinned' => $uw['pinned'] ?? 0,
                'layout_name' => $layoutName,
                'settings' => json_encode($uw['settings'] ?? new \stdClass()),
            ]);
        }
        return true;
    }

    public function renameLayout($userId, $oldName, $newName)
    {
        if (empty($newName)) return false;
        $this->db->table('user_widgets')
            ->where('user_id', $userId)->where('layout_name', $oldName)
            ->update(['layout_name' => $newName]);
        $existing = $this->db->table('widget_layouts')
            ->where('user_id', $userId)->where('layout_name', $oldName)->first();
        if ($existing) {
            $this->db->table('widget_layouts')
                ->where('id', $existing->id)->update(['layout_name' => $newName]);
        }
        return true;
    }

    public function deleteLayout($userId, $layoutName)
    {
        if ($layoutName === 'default') return false;
        $this->db->table('user_widgets')
            ->where('user_id', $userId)->where('layout_name', $layoutName)->delete();
        $this->db->table('widget_layouts')
            ->where('user_id', $userId)->where('layout_name', $layoutName)->delete();
        return true;
    }

    public function exportLayout($userId, $layoutName)
    {
        $snapshot = $this->loadLayoutSnapshot($userId, $layoutName);
        if (!$snapshot) {
            // Snapshot doesn't exist but widgets might — create one
            $widgets = $this->getUserWidgets($userId, $layoutName);
            if (empty($widgets)) return null;
            $snapshot = $widgets;
        }
        return json_encode([
            'layout_name' => $layoutName,
            'version' => 1,
            'widgets' => $snapshot,
        ], JSON_PRETTY_PRINT);
    }

    public function importLayout($userId, $jsonData)
    {
        $data = json_decode($jsonData, true);
        if (!$data || !isset($data['widgets'])) return false;
        $layoutName = $data['layout_name'] ?? 'imported_' . time();
        $this->db->table('user_widgets')
            ->where('user_id', $userId)->where('layout_name', $layoutName)->delete();
        foreach ($data['widgets'] as $uw) {
            $this->db->table('user_widgets')->insertGetId([
                'user_id' => $userId,
                'widget_key' => $uw['widget_key'],
                'zone' => $uw['zone'] ?? 'main',
                'sort_order' => $uw['sort_order'] ?? 0,
                'width' => $uw['width'] ?? 1,
                'collapsed' => $uw['collapsed'] ?? 0,
                'hidden' => $uw['hidden'] ?? 0,
                'pinned' => $uw['pinned'] ?? 0,
                'layout_name' => $layoutName,
                'settings' => json_encode($uw['settings'] ?? new \stdClass()),
            ]);
        }
        // Save snapshot
        $this->saveLayoutSnapshot($userId, $layoutName);
        return $layoutName;
    }

    public function resetLayout($userId, $layoutName = 'default')
    {
        $this->db->table('user_widgets')
            ->where('user_id', $userId)->where('layout_name', $layoutName)->delete();
        $this->ensureDefaults($userId);
    }

    public function saveLayout($userId, $layout)
    {
        foreach ($layout as $item) {
            $id = (int)($item['id'] ?? 0);
            $zone = $item['zone'] ?? 'main';
            $sort = (int)($item['sort_order'] ?? 0);
            $width = (int)($item['width'] ?? 1);
            if ($id) {
                $this->db->table('user_widgets')
                    ->where('id', $id)->where('user_id', $userId)
                    ->update(['zone' => $zone, 'sort_order' => $sort, 'width' => $width]);
            }
        }
    }

    public function renderZone($zone, $userWidgets = null)
    {
        $html = '';
        if ($userWidgets === null) $userWidgets = $this->getUserWidgets();

        $zoneWidgets = array_filter($userWidgets, fn($w) => $w['zone'] === $zone && !$w['hidden']);
        usort($zoneWidgets, fn($a, $b) => $a['sort_order'] - $b['sort_order']);

        foreach ($zoneWidgets as $uw) {
            $widget = $this->getWidget($uw['widget_key']);
            if (!$widget) continue;

            $collapsedCls = $uw['collapsed'] ? ' widget-collapsed' : '';
            $pinnedAttr = $uw['pinned'] ? ' data-pinned="1"' : '';
            $widthCls = ' widget-w' . $uw['width'];

            $html .= '<div class="widget-item' . $collapsedCls . $widthCls . '" data-widget-id="' . $uw['id'] . '" data-widget-key="' . $uw['widget_key'] . '"' . $pinnedAttr . ' draggable="' . ($uw['pinned'] ? 'false' : 'true') . '">';
            $html .= '<div class="widget-header"><span class="widget-handle"><i class="bi bi-grip-vertical"></i></span>';
            $html .= '<span class="widget-title"><i class="bi ' . $widget->getIcon() . '"></i> ' . htmlspecialchars($widget->getName()) . '</span>';
            $html .= '<span class="widget-actions">';
            $html .= '<button class="widget-collapse-btn btn-icon" title="' . ($uw['collapsed'] ? 'Expand' : 'Collapse') . '"><i class="bi ' . ($uw['collapsed'] ? 'bi-plus' : 'bi-dash') . '"></i></button>';
            $html .= '<button class="widget-pin-btn btn-icon" title="' . ($uw['pinned'] ? 'Unpin' : 'Pin') . '" style="color:' . ($uw['pinned'] ? '#facc15' : '#64748b') . '"><i class="bi bi-pin' . ($uw['pinned'] ? '-fill' : '') . '"></i></button>';
            $html .= '<button class="widget-hide-btn btn-icon" title="Hide"><i class="bi bi-eye-slash"></i></button>';
            $html .= '<button class="widget-remove btn-icon" title="Remove" data-key="' . $uw['widget_key'] . '"><i class="bi bi-x"></i></button>';
            $html .= '</span></div>';
            $html .= '<div class="widget-body"' . ($uw['collapsed'] ? ' style="display:none"' : '') . '>' . $widget->render($uw) . '</div>';
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

    public function addWidget($userId, $widgetKey, $zone = 'main', $layoutName = 'default')
    {
        $maxSort = $this->db->table('user_widgets')
            ->where('user_id', $userId)
            ->where('zone', $zone)
            ->where('layout_name', $layoutName)
            ->value('MAX(sort_order)') ?? 0;
        $this->db->table('user_widgets')->insertGetId([
            'user_id' => $userId,
            'widget_key' => $widgetKey,
            'zone' => $zone,
            'sort_order' => $maxSort + 1,
            'width' => 1,
            'collapsed' => 0,
            'hidden' => 0,
            'pinned' => 0,
            'layout_name' => $layoutName,
            'settings' => '{}',
        ]);
    }

    // ---- Custom Widget Builder ----

    public function createCustomWidget($userId, $key, $name, $type, $config)
    {
        $this->db->table('custom_widgets')->insertGetId([
            'user_id' => $userId,
            'widget_key' => $key,
            'name' => $name,
            'widget_type' => $type,
            'icon' => $config['icon'] ?? 'bi-box',
            'config' => json_encode($config),
        ]);
        // Register as a real widget
        $this->register($key, $name, 'Custom ' . $type . ' widget', $config['icon'] ?? 'bi-box', function($uw) use ($key, $type, $config) {
            return self::renderCustomWidget($key, $type, $config, $uw);
        });
    }

    public function updateCustomWidget($userId, $key, $name, $type, $config)
    {
        $this->db->table('custom_widgets')
            ->where('widget_key', $key)->where('user_id', $userId)
            ->update([
                'name' => $name,
                'widget_type' => $type,
                'icon' => $config['icon'] ?? 'bi-box',
                'config' => json_encode($config),
            ]);
        // Re-register
        unset($this->widgets[$key]);
        $this->register($key, $name, 'Custom ' . $type . ' widget', $config['icon'] ?? 'bi-box', function($uw) use ($key, $type, $config) {
            return self::renderCustomWidget($key, $type, $config, $uw);
        });
    }

    public function deleteCustomWidget($userId, $key)
    {
        $this->db->table('custom_widgets')
            ->where('widget_key', $key)->where('user_id', $userId)->delete();
        unset($this->widgets[$key]);
    }

    public function getCustomWidgets($userId = null)
    {
        $q = $this->db->table('custom_widgets');
        if ($userId) $q = $q->where('user_id', $userId);
        return $q->get() ?: [];
    }

    public function loadCustomWidgets($userId = null)
    {
        $rows = $this->getCustomWidgets($userId);
        foreach ($rows as $r) {
            $config = json_decode($r->config ?? '{}', true) ?: [];
            $this->register($r->widget_key, $r->name, 'Custom ' . $r->widget_type . ' widget', $r->icon ?? 'bi-box', function($uw) use ($r, $config) {
                return self::renderCustomWidget($r->widget_key, $r->widget_type, $config, $uw);
            });
        }
    }

    protected static function renderCustomWidget($key, $type, $config, $uw)
    {
        $wm = self::getInstance();
        $title = $config['title'] ?? ($uw['settings']['title'] ?? 'Widget');
        $content = $config['content'] ?? '';
        $refresh = (int)($config['refresh_interval'] ?? 0);
        $db = $wm->db;

        switch ($type) {
            case 'stat':
                $value = $config['value'] ?? '0';
                $label = $config['label'] ?? '';
                $color = $config['color'] ?? '#008cff';
                return '<div style="text-align:center;padding:10px"><div style="font-size:32px;font-weight:700;color:' . $color . '">' . htmlspecialchars($value) . '</div><div style="color:#64748b;font-size:12px">' . htmlspecialchars($label) . '</div></div>';

            case 'html':
                return '<div>' . ($config['html'] ?? '') . '</div>';

            case 'markdown':
                $md = $config['markdown'] ?? '';
                return '<div style="white-space:pre-wrap;font-size:12px;color:#ccc">' . htmlspecialchars($md) . '</div>';

            case 'table':
                $headers = $config['headers'] ?? [];
                $rows = $config['rows'] ?? [];
                $h = '<table style="width:100%"><tr>';
                foreach ($headers as $head) $h .= '<th>' . htmlspecialchars($head) . '</th>';
                $h .= '</tr>';
                foreach ($rows as $row) {
                    $h .= '<tr>';
                    foreach ($row as $cell) $h .= '<td>' . htmlspecialchars($cell) . '</td>';
                    $h .= '</tr>';
                }
                $h .= '</table>';
                return $h;

            case 'list':
                $items = $config['items'] ?? [];
                $h = '<ul style="margin:0;padding-left:16px">';
                foreach ($items as $item) $h .= '<li style="font-size:12px;margin-bottom:4px">' . htmlspecialchars($item) . '</li>';
                $h .= '</ul>';
                return $h;

            case 'progress':
                $pct = min(100, max(0, (int)($config['percentage'] ?? 0)));
                $clr = $config['color'] ?? '#008cff';
                $lbl = $config['label'] ?? '';
                return '<div style="margin:6px 0"><div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px"><span>' . htmlspecialchars($lbl) . '</span><span>' . $pct . '%</span></div><div style="height:8px;background:rgba(255,255,255,.06);border-radius:4px;overflow:hidden"><div style="height:100%;width:' . $pct . '%;background:' . $clr . ';border-radius:4px;transition:width .5s"></div></div></div>';

            case 'status':
                $items = $config['items'] ?? [];
                $h = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px">';
                foreach ($items as $item) {
                    $on = !empty($item['active']);
                    $h .= '<div style="display:flex;align-items:center;gap:6px;padding:4px 8px;border-radius:4px;font-size:12px;background:rgba(255,255,255,.02)">';
                    $h .= '<span style="width:8px;height:8px;border-radius:50%;background:' . ($on ? '#4ade80' : '#64748b') . '"></span>';
                    $h .= '<span>' . htmlspecialchars($item['label'] ?? '') . '</span></div>';
                }
                $h .= '</div>';
                return $h;

            case 'url':
                $url = $config['url'] ?? '';
                $height = (int)($config['iframe_height'] ?? 300);
                return '<iframe src="' . htmlspecialchars($url) . '" style="width:100%;height:' . $height . 'px;border:none;border-radius:6px"></iframe>';

            case 'sql':
                if (!$db) return '<p style="color:#f87171;font-size:12px">No database connection</p>';
                try {
                    $stmt = $db->pdo()->query($config['query'] ?? 'SELECT 1');
                    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (empty($results)) return '<p style="color:#64748b;font-size:12px">No results</p>';
                    $h = '<table style="width:100%"><tr>';
                    foreach (array_keys($results[0]) as $col) $h .= '<th>' . htmlspecialchars($col) . '</th>';
                    $h .= '</tr>';
                    foreach ($results as $row) {
                        $h .= '<tr>';
                        foreach ($row as $cell) $h .= '<td style="font-size:11px">' . htmlspecialchars(substr((string)$cell, 0, 50)) . '</td>';
                        $h .= '</tr>';
                    }
                    $h .= '</table>';
                    return $h;
                } catch (\Exception $e) {
                    return '<p style="color:#f87171;font-size:12px">Query error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }

            case 'rss':
                $feedUrl = $config['feed_url'] ?? '';
                if (!$feedUrl) return '<p style="color:#64748b;font-size:12px">No RSS URL configured</p>';
                $cacheKey = 'rss_' . md5($feedUrl);
                $xml = $wm->getData($cacheKey);
                if (!$xml) {
                    $xml = @file_get_contents($feedUrl);
                    if ($xml) $wm->setData($cacheKey, $xml);
                }
                if (!$xml) return '<p style="color:#f87171;font-size:12px">Failed to fetch RSS feed</p>';
                $feed = @simplexml_load_string($xml);
                if (!$feed) return '<p style="color:#f87171;font-size:12px">Invalid RSS feed</p>';
                $limit = (int)($config['item_limit'] ?? 5);
                $h = '<div style="font-size:12px">';
                $i = 0;
                foreach ($feed->channel->item as $item) {
                    if ($i++ >= $limit) break;
                    $link = (string)($item->link ?? '#');
                    $title = (string)($item->title ?? '');
                    $h .= '<div style="padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04)"><a href="' . htmlspecialchars($link) . '" target="_blank" style="color:var(--accent);text-decoration:none">' . htmlspecialchars($title) . '</a></div>';
                }
                $h .= '</div>';
                return $h;

            case 'iframe':
                $url = $config['url'] ?? '';
                $height = (int)($config['height'] ?? 300);
                return '<iframe src="' . htmlspecialchars($url) . '" style="width:100%;height:' . $height . 'px;border:none;border-radius:6px"></iframe>';

            default:
                return '<p style="color:#64748b">Widget type "' . htmlspecialchars($type) . '" not supported.</p>';
        }
    }
}
