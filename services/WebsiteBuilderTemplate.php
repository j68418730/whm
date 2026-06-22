<?php
/**
 * Website Builder Template System
 * Handles template loading, theme parsing, and page generation
 */

namespace Services;

class WebsiteBuilderTemplate
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function getTemplates($category = null)
    {
        $q = $this->db->table('wb_templates')->where('is_active', 1);
        if ($category) {
            $q->where('category', $category);
        }
        return $q->orderBy('name', 'ASC')->get() ?: [];
    }

    public function getTemplate($id)
    {
        return $this->db->table('wb_templates')->where('id', (int)$id)->first();
    }

    public function installTemplate($data)
    {
        $config = $data['config'] ?? '{}';
        if (is_array($config)) $config = json_encode($config);
        return $this->db->table('wb_templates')->insertGetId([
            'name' => $data['name'] ?? 'Imported Template',
            'category' => $data['category'] ?? 'imported',
            'description' => $data['description'] ?? '',
            'thumbnail' => $data['thumbnail'] ?? '',
            'config' => $config,
            'is_active' => 1,
        ]);
    }

    public function generateSiteFromTemplate($templateId, $siteName, $siteId)
    {
        $tmpl = $this->db->table('wb_templates')->where('id', (int)$templateId)->first();
        if (!$tmpl || !$tmpl->config) return;

        $config = json_decode($tmpl->config, true);
        if (!$config || !isset($config['pages'])) return;

        $theme = $this->db->table('wb_themes')->where('is_active', 1)->first();
        if ($theme) {
            $this->db->table('wb_sites')->where('id', $siteId)->update(['theme_id' => $theme->id]);
        }

        foreach ($config['pages'] as $sortOrder => $page) {
            $title = str_replace('{{NAME}}', $siteName, $page['title'] ?? 'Page');
            $slug = $page['slug'] ?? 'page-' . ($sortOrder + 1);

            $pageId = $this->db->table('wb_pages')->insertGetId([
                'site_id' => $siteId,
                'title' => $title,
                'slug' => $slug,
                'status' => 'draft',
                'sort_order' => $sortOrder,
                'meta_title' => $title,
                'meta_description' => str_replace('{{NAME}}', $siteName, $page['description'] ?? ''),
            ]);

            if (isset($page['blocks'])) {
                foreach ($page['blocks'] as $i => $block) {
                    $content = $this->replacePlaceholders($block['content'] ?? [], $siteName);
                    $this->db->table('wb_blocks')->insertGetId([
                        'page_id' => $pageId,
                        'type' => $block['type'] ?? 'text',
                        'content' => json_encode($content),
                        'settings' => json_encode($block['settings'] ?? []),
                        'sort_order' => $i,
                        'zone' => $block['zone'] ?? 'content',
                    ]);
                }
            }
        }

        if (isset($config['menus'])) {
            foreach ($config['menus'] as $menu) {
                $this->db->table('wb_menus')->insertGetId([
                    'site_id' => $siteId,
                    'name' => $menu['name'] ?? 'Main Menu',
                    'location' => $menu['location'] ?? 'main',
                    'items' => json_encode($menu['items'] ?? []),
                ]);
            }
        }
    }

    public function getCategories()
    {
        $rows = $this->db->table('wb_templates')->where('is_active', 1)->get() ?: [];
        $cats = [];
        foreach ($rows as $r) {
            if ($r->category && !in_array($r->category, $cats)) {
                $cats[] = $r->category;
            }
        }
        sort($cats);
        return $cats;
    }

    protected function replacePlaceholders($content, $siteName)
    {
        if (is_string($content)) {
            return str_replace(['{{NAME}}', '{{YEAR}}', '{{EMAIL}}'], [$siteName, date('Y'), 'admin@example.com'], $content);
        }
        if (is_array($content)) {
            foreach ($content as $key => $val) {
                $content[$key] = $this->replacePlaceholders($val, $siteName);
            }
        }
        return $content;
    }
}
