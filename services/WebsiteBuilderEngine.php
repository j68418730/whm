<?php
/**
 * Website Builder Engine - Core rendering engine
 */

namespace Services;

class WebsiteBuilderEngine
{
    protected $db;
    protected $templateService;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
        $this->templateService = new WebsiteBuilderTemplate();
    }

    public function createSite($userId, $name, $domain, $templateId)
    {
        $siteId = $this->db->table('wb_sites')->insertGetId([
            'user_id' => $userId,
            'name' => $name,
            'domain' => $domain,
            'template_id' => $templateId,
            'status' => 'draft',
            'settings' => json_encode(['site_name' => $name, 'domain' => $domain]),
        ]);
        $this->templateService->generateSiteFromTemplate($templateId, $name, $siteId);
        return $siteId;
    }

    public function getSite($id)
    {
        $site = $this->db->table('wb_sites')->where('id', (int)$id)->first();
        if ($site && $site->settings) {
            $site->settings_arr = json_decode($site->settings, true) ?: [];
        }
        return $site;
    }

    public function getUserSites($userId)
    {
        return $this->db->table('wb_sites')->where('user_id', (int)$userId)->orderBy('created_at', 'DESC')->get() ?: [];
    }

    public function createPage($siteId, $title, $slug, $templateId = null)
    {
        $pageId = $this->db->table('wb_pages')->insertGetId([
            'site_id' => (int)$siteId,
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'sort_order' => 0,
        ]);
        if ($templateId) {
            $tmpl = $this->db->table('wb_templates')->where('id', (int)$templateId)->first();
            if ($tmpl && $tmpl->config) {
                $config = json_decode($tmpl->config, true);
                if (isset($config['pages'][0]['blocks'])) {
                    foreach ($config['pages'][0]['blocks'] as $i => $block) {
                        $this->db->table('wb_blocks')->insertGetId([
                            'page_id' => $pageId,
                            'type' => $block['type'],
                            'content' => json_encode($block['content'] ?? []),
                            'settings' => json_encode($block['settings'] ?? []),
                            'sort_order' => $i,
                            'zone' => $block['zone'] ?? 'content',
                        ]);
                    }
                }
            }
        }
        return $pageId;
    }

    public function getPage($id)
    {
        $page = $this->db->table('wb_pages')->where('id', (int)$id)->first();
        if ($page) {
            $blocks = $this->db->table('wb_blocks')->where('page_id', $page->id)->orderBy('sort_order', 'ASC')->get() ?: [];
            foreach ($blocks as $b) {
                $b->content = json_decode($b->content ?? '{}', true);
                $b->settings_arr = json_decode($b->settings ?? '{}', true);
            }
            $page->blocks = $blocks;
            $page->meta = [
                'title' => $page->meta_title ?: $page->title,
                'description' => $page->meta_description,
                'keywords' => $page->meta_keywords,
                'og_image' => $page->og_image,
            ];
        }
        return $page;
    }

    public function getSitePages($siteId)
    {
        return $this->db->table('wb_pages')->where('site_id', (int)$siteId)->orderBy('sort_order', 'ASC')->get() ?: [];
    }

    public function saveBlocks($pageId, $blocks)
    {
        $this->db->table('wb_blocks')->where('page_id', (int)$pageId)->delete();
        foreach ($blocks as $i => $block) {
            $this->db->table('wb_blocks')->insertGetId([
                'page_id' => (int)$pageId,
                'type' => $block['type'] ?? 'text',
                'content' => json_encode($block['content'] ?? []),
                'settings' => json_encode($block['settings'] ?? []),
                'sort_order' => $i,
                'zone' => $block['zone'] ?? 'content',
            ]);
        }
    }

    public function renderPage($pageId)
    {
        $page = $this->getPage($pageId);
        if (!$page) return '';
        $site = $this->getSite($page->site_id);
        $theme = $this->getSiteTheme($page->site_id);
        $html = '<!DOCTYPE html><html lang="en"><head>';
        $html .= '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">';
        $html .= '<title>' . htmlspecialchars($page->meta_title ?: $page->title) . '</title>';
        if ($page->meta_description) $html .= '<meta name="description" content="' . htmlspecialchars($page->meta_description) . '">';
        if ($page->meta_keywords) $html .= '<meta name="keywords" content="' . htmlspecialchars($page->meta_keywords) . '">';
        if ($page->og_image) $html .= '<meta property="og:image" content="' . htmlspecialchars($page->og_image) . '">';
        $html .= '<style>' . $this->getThemeCss($theme) . '</style>';
        $html .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';
        $html .= '</head><body>';
        $zones = ['header', 'content', 'footer'];
        foreach ($zones as $zone) {
            $zoneBlocks = array_filter($page->blocks, fn($b) => ($b->zone ?? 'content') === $zone);
            if ($zone === 'header' && empty($zoneBlocks)) continue;
            $html .= '<div class="wb-zone wb-zone-' . $zone . '">';
            foreach ($zoneBlocks as $block) {
                $html .= $this->renderBlock($block);
            }
            $html .= '</div>';
        }
        $html .= '</body></html>';
        return $html;
    }

    public function renderBlock($block)
    {
        $type = $block->type ?? 'text';
        $content = $block->content ?? [];
        $settings = $block->settings_arr ?? [];
        $methodName = 'render' . str_replace('_', '', ucwords($type, '_'));
        if (method_exists($this, $methodName)) {
            return $this->$methodName($content, $settings);
        }
        return $this->renderText($content, $settings);
    }

    protected function getSiteTheme($siteId)
    {
        $site = $this->db->table('wb_sites')->where('id', (int)$siteId)->first();
        if ($site && $site->theme_id) {
            return $this->db->table('wb_themes')->where('id', $site->theme_id)->first();
        }
        return $this->db->table('wb_themes')->where('is_active', 1)->first();
    }

    protected function getThemeCss($theme)
    {
        if (!$theme || !$theme->config) return $this->defaultCss();
        $cfg = json_decode($theme->config, true);
        if (!$cfg) return $this->defaultCss();
        $css = ':root{';
        $css .= '--wb-primary:' . ($cfg['primary'] ?? '#008cff') . ';';
        $css .= '--wb-secondary:' . ($cfg['secondary'] ?? '#00e5ff') . ';';
        $css .= '--wb-bg:' . ($cfg['bg'] ?? '#02050e') . ';';
        $css .= '--wb-card-bg:' . ($cfg['card_bg'] ?? 'rgba(8,16,28,.85)') . ';';
        $css .= '--wb-text:' . ($cfg['text'] ?? '#ffffff') . ';';
        $css .= '--wb-accent:' . ($cfg['accent'] ?? '#38bdf8') . ';}';
        $css .= $this->defaultCss();
        return $css;
    }

    protected function defaultCss()
    {
        return '
*{margin:0;padding:0;box-sizing:border-box}body{font-family:Inter,sans-serif;background:var(--wb-bg,#02050e);color:var(--wb-text,#fff);line-height:1.6}
.wb-zone{width:100%}
.wb-zone-header{position:sticky;top:0;z-index:100}
.wb-zone-footer{margin-top:40px}
.wb-container{max-width:1200px;margin:0 auto;padding:0 20px}
.wb-section{padding:60px 20px}
.wb-hero{padding:100px 20px;text-align:center}
.wb-hero.gradient{background:linear-gradient(135deg,var(--wb-primary),var(--wb-secondary))}
.wb-hero h1{font-size:48px;margin-bottom:16px}
.wb-hero p{font-size:18px;opacity:.9;margin-bottom:24px;max-width:600px;margin-left:auto;margin-right:auto}
.wb-btn{display:inline-block;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;transition:.3s;border:none;cursor:pointer}
.wb-btn-primary{background:var(--wb-primary,#008cff);color:#fff}
.wb-btn-secondary{background:var(--wb-secondary,#00e5ff);color:#000}
.wb-btn-outline{border:2px solid var(--wb-primary,#008cff);color:var(--wb-primary,#008cff);background:transparent}
.wb-btn:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(0,140,255,.3)}
.wb-text-block{padding:60px 20px;max-width:800px;margin:0 auto}
.wb-text-block h2{font-size:32px;margin-bottom:20px;color:var(--wb-accent,#38bdf8)}
.wb-text-block p{margin-bottom:16px;color:#94a3b8}
.wb-grid{display:grid;gap:20px;max-width:1200px;margin:0 auto;padding:20px}
.wb-grid-2{grid-template-columns:repeat(2,1fr)}
.wb-grid-3{grid-template-columns:repeat(3,1fr)}
.wb-grid-4{grid-template-columns:repeat(4,1fr)}
.wb-card{background:var(--wb-card-bg,rgba(8,16,28,.85));border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:30px;text-align:center}
.wb-card h3,.wb-card h4{margin-bottom:8px;color:#fff}
.wb-card p{color:#94a3b8;font-size:13px}
.wb-pricing{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;max-width:1100px;margin:0 auto;padding:40px 20px}
.wb-pricing-card{background:var(--wb-card-bg,rgba(8,16,28,.85));border:1px solid rgba(0,191,255,.1);border-radius:16px;padding:40px 30px;text-align:center;transition:.3s;position:relative}
.wb-pricing-card.featured{border-color:var(--wb-primary,#008cff);transform:scale(1.05)}
.wb-pricing-card .price{font-size:42px;font-weight:800;margin:16px 0}
.wb-pricing-card ul{list-style:none;padding:0;margin-bottom:20px}
.wb-pricing-card ul li{padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06);color:#94a3b8}
.wb-gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px;padding:20px;max-width:1200px;margin:0 auto}
.wb-gallery img{width:100%;height:200px;object-fit:cover;border-radius:8px;transition:.3s}
.wb-gallery img:hover{transform:scale(1.05)}
.wb-testimonials{max-width:800px;margin:0 auto;padding:40px 20px}
.wb-testimonial{background:var(--wb-card-bg);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:24px;margin-bottom:16px}
.wb-testimonial .quote{font-style:italic;margin-bottom:12px;color:#94a3b8}
.wb-testimonial .author{font-weight:600}
.wb-testimonial .role{font-size:12px;color:#64748b}
.wb-form{max-width:600px;margin:0 auto;padding:40px 20px}
.wb-form input,.wb-form textarea,.wb-form select{width:100%;padding:12px 16px;margin-bottom:12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-family:inherit}
.wb-form label{display:block;margin-bottom:4px;color:#94a3b8;font-size:13px}
.wb-form .wb-btn{width:100%}
.wb-header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:rgba(8,16,28,.95);border-bottom:1px solid rgba(0,191,255,.1)}
.wb-header .logo{font-size:20px;font-weight:800}
.wb-header nav a{color:#94a3b8;text-decoration:none;margin-left:20px;font-size:14px;transition:.2s}
.wb-header nav a:hover{color:var(--wb-accent,#38bdf8)}
.wb-footer{text-align:center;padding:30px 20px;color:#64748b;font-size:13px;border-top:1px solid rgba(255,255,255,.06)}
.wb-image-block{text-align:center;padding:20px}
.wb-image-block img{max-width:100%;border-radius:8px}
.wb-image-block .caption{margin-top:8px;color:#64748b;font-size:13px}
.wb-video-block{max-width:800px;margin:0 auto;padding:20px;text-align:center}
.wb-video-block iframe,.wb-video-block video{width:100%;border-radius:12px;border:none}
.wb-columns{display:grid;gap:20px;max-width:1000px;margin:0 auto;padding:40px 20px}
.wb-column{background:var(--wb-card-bg);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:24px;text-align:center}
.wb-column h3{margin-bottom:8px;color:var(--wb-accent,#38bdf8)}
.wb-column p{color:#94a3b8;font-size:13px}
.wb-countdown{text-align:center;padding:60px 20px}
.wb-countdown .timer{display:flex;justify-content:center;gap:20px;margin-top:20px}
.wb-countdown .timer div{background:var(--wb-card-bg);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:20px 30px;min-width:80px}
.wb-countdown .timer .num{font-size:36px;font-weight:800}
.wb-countdown .timer .label{font-size:11px;color:#64748b;text-transform:uppercase}
.wb-faq{max-width:700px;margin:0 auto;padding:40px 20px}
.wb-faq-item{border-bottom:1px solid rgba(255,255,255,.06);padding:16px 0}
.wb-faq-item .q{font-weight:600;cursor:pointer}
.wb-faq-item .a{padding-top:12px;color:#94a3b8;display:none}
.wb-social{text-align:center;padding:40px 20px}
.wb-social .icons{display:flex;justify-content:center;gap:16px;margin-top:16px}
.wb-social .icons a{width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:var(--wb-card-bg);border-radius:50%;color:var(--wb-accent);font-size:20px;text-decoration:none;transition:.3s}
.wb-social .icons a:hover{background:var(--wb-primary);color:#fff;transform:translateY(-3px)}
.wb-spacer{width:100%}
.wb-divider{max-width:200px;margin:0 auto;border:0;height:1px}
.wb-divider.solid{border-top:1px solid rgba(255,255,255,.1)}
.wb-divider.dashed{border-top:1px dashed rgba(255,255,255,.1)}
.wb-divider.dotted{border-top:1px dotted rgba(255,255,255,.1)}
.wb-newsletter{text-align:center;padding:60px 20px;background:var(--wb-card-bg);border-top:1px solid rgba(0,191,255,.1);border-bottom:1px solid rgba(0,191,255,.1)}
.wb-newsletter .form{display:flex;max-width:400px;margin:20px auto 0;gap:8px}
.wb-newsletter .form input{flex:1;padding:12px 16px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff}
.wb-player{max-width:400px;margin:0 auto;padding:20px;text-align:center}
.wb-player audio{width:100%;border-radius:8px}
@media(max-width:768px){.wb-grid-2,.wb-grid-3,.wb-grid-4{grid-template-columns:1fr}.wb-hero h1{font-size:32px}.wb-header{flex-direction:column;gap:12px}.wb-header nav a{margin:0 8px}.wb-countdown .timer{flex-wrap:wrap}}
';
    }

    public function getBlockTypes()
    {
        return [
            'header' => ['name' => 'Header', 'icon' => 'fa-solid fa-bars', 'category' => 'structure', 'fields' => [
                ['key' => 'logo', 'label' => 'Logo Text', 'type' => 'text', 'default' => 'My Site'],
                ['key' => 'links', 'label' => 'Navigation Links', 'type' => 'repeater', 'fields' => [['key' => 'label', 'label' => 'Label', 'type' => 'text'], ['key' => 'url', 'label' => 'URL', 'type' => 'text']]],
            ]],
            'hero' => ['name' => 'Hero', 'icon' => 'fa-solid fa-display', 'category' => 'content', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Welcome'],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'default' => ''],
                ['key' => 'btn_text', 'label' => 'Button Text', 'type' => 'text', 'default' => ''],
                ['key' => 'btn_url', 'label' => 'Button URL', 'type' => 'text', 'default' => ''],
                ['key' => 'background', 'label' => 'Background', 'type' => 'select', 'options' => ['gradient' => 'Gradient','image' => 'Image','solid' => 'Solid'], 'default' => 'gradient'],
            ]],
            'text' => ['name' => 'Text', 'icon' => 'fa-solid fa-paragraph', 'category' => 'content', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => ''],
                ['key' => 'body', 'label' => 'Content', 'type' => 'richtext', 'default' => ''],
            ]],
            'image' => ['name' => 'Image', 'icon' => 'fa-solid fa-image', 'category' => 'media', 'fields' => [
                ['key' => 'src', 'label' => 'Image URL', 'type' => 'text', 'default' => ''],
                ['key' => 'alt', 'label' => 'Alt Text', 'type' => 'text', 'default' => ''],
                ['key' => 'caption', 'label' => 'Caption', 'type' => 'text', 'default' => ''],
            ]],
            'gallery' => ['name' => 'Gallery', 'icon' => 'fa-solid fa-images', 'category' => 'media', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Gallery'],
                ['key' => 'images', 'label' => 'Images', 'type' => 'repeater', 'fields' => [['key' => 'url', 'label' => 'Image URL', 'type' => 'text']]],
            ]],
            'video' => ['name' => 'Video', 'icon' => 'fa-solid fa-video', 'category' => 'media', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => ''],
                ['key' => 'url', 'label' => 'Video URL', 'type' => 'text', 'default' => ''],
                ['key' => 'autoplay', 'label' => 'Autoplay', 'type' => 'boolean', 'default' => false],
            ]],
            'button' => ['name' => 'Button', 'icon' => 'fa-solid fa-hand-pointer', 'category' => 'components', 'fields' => [
                ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'default' => 'Click'],
                ['key' => 'url', 'label' => 'URL', 'type' => 'text', 'default' => '#'],
                ['key' => 'style', 'label' => 'Style', 'type' => 'select', 'options' => ['primary' => 'Primary','secondary' => 'Secondary','outline' => 'Outline'], 'default' => 'primary'],
            ]],
            'pricing_table' => ['name' => 'Pricing Table', 'icon' => 'fa-solid fa-table-list', 'category' => 'components', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Pricing'],
                ['key' => 'plans', 'label' => 'Plans', 'type' => 'repeater', 'fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['key' => 'price', 'label' => 'Price', 'type' => 'text'],
                    ['key' => 'features', 'label' => 'Features', 'type' => 'list'],
                    ['key' => 'btn_text', 'label' => 'Button', 'type' => 'text'],
                    ['key' => 'featured', 'label' => 'Featured', 'type' => 'boolean'],
                ]],
            ]],
            'testimonials' => ['name' => 'Testimonials', 'icon' => 'fa-solid fa-quote-right', 'category' => 'components', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Testimonials'],
                ['key' => 'items', 'label' => 'Items', 'type' => 'repeater', 'fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['key' => 'role', 'label' => 'Role', 'type' => 'text'],
                    ['key' => 'text', 'label' => 'Quote', 'type' => 'textarea'],
                ]],
            ]],
            'contact_form' => ['name' => 'Contact Form', 'icon' => 'fa-solid fa-envelope', 'category' => 'components', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Contact'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'text', 'default' => ''],
                ['key' => 'fields', 'label' => 'Fields', 'type' => 'multiselect', 'options' => ['name'=>'Name','email'=>'Email','subject'=>'Subject','phone'=>'Phone','message'=>'Message'], 'default' => ['name','email','message']],
            ]],
            'faq' => ['name' => 'FAQ', 'icon' => 'fa-solid fa-question-circle', 'category' => 'components', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'FAQ'],
                ['key' => 'items', 'label' => 'Items', 'type' => 'repeater', 'fields' => [
                    ['key' => 'question', 'label' => 'Question', 'type' => 'text'],
                    ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea'],
                ]],
            ]],
            'map' => ['name' => 'Map', 'icon' => 'fa-solid fa-map-location-dot', 'category' => 'components', 'fields' => [
                ['key' => 'address', 'label' => 'Address', 'type' => 'text', 'default' => ''],
                ['key' => 'zoom', 'label' => 'Zoom', 'type' => 'number', 'default' => 14],
            ]],
            'countdown' => ['name' => 'Countdown', 'icon' => 'fa-solid fa-stopwatch', 'category' => 'components', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Countdown'],
                ['key' => 'target_date', 'label' => 'Target', 'type' => 'datetime', 'default' => '2026-12-31 23:59:59'],
            ]],
            'newsletter' => ['name' => 'Newsletter', 'icon' => 'fa-solid fa-newspaper', 'category' => 'components', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Subscribe'],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'default' => ''],
                ['key' => 'btn_text', 'label' => 'Button', 'type' => 'text', 'default' => 'Subscribe'],
            ]],
            'hosting_packages' => ['name' => 'Hosting Packages', 'icon' => 'fa-solid fa-server', 'category' => 'components', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Hosting'],
                ['key' => 'plans', 'label' => 'Plans', 'type' => 'repeater', 'fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['key' => 'price', 'label' => 'Price', 'type' => 'text'],
                    ['key' => 'features', 'label' => 'Features', 'type' => 'list'],
                    ['key' => 'btn_text', 'label' => 'Button', 'type' => 'text'],
                    ['key' => 'featured', 'label' => 'Featured', 'type' => 'boolean'],
                ]],
            ]],
            'order_button' => ['name' => 'Order Button', 'icon' => 'fa-solid fa-cart-plus', 'category' => 'components', 'fields' => [
                ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'default' => 'Order'],
                ['key' => 'url', 'label' => 'URL', 'type' => 'text', 'default' => '#'],
                ['key' => 'product_id', 'label' => 'Product ID', 'type' => 'text', 'default' => ''],
            ]],
            'live_chat' => ['name' => 'Live Chat', 'icon' => 'fa-solid fa-comment-dots', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Chat'],
            ]],
            'radio_player' => ['name' => 'Radio Player', 'icon' => 'fa-solid fa-radio', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Live Stream'],
                ['key' => 'stream_url', 'label' => 'Stream URL', 'type' => 'text', 'default' => ''],
                ['key' => 'stream_type', 'label' => 'Type', 'type' => 'select', 'options' => ['audio/ogg'=>'OGG','audio/mpeg'=>'MP3','audio/aac'=>'AAC'], 'default' => 'audio/ogg'],
            ]],
            'now_playing' => ['name' => 'Now Playing', 'icon' => 'fa-solid fa-music', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Now Playing'],
                ['key' => 'api_url', 'label' => 'API URL', 'type' => 'text', 'default' => ''],
            ]],
            'dj_status' => ['name' => 'DJ Status', 'icon' => 'fa-solid fa-microphone', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Live DJ'],
            ]],
            'listener_count' => ['name' => 'Listener Count', 'icon' => 'fa-solid fa-headphones', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Listeners'],
            ]],
            'server_status' => ['name' => 'Server Status', 'icon' => 'fa-solid fa-heart-pulse', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Server Status'],
            ]],
            'game_server_status' => ['name' => 'Game Server Status', 'icon' => 'fa-solid fa-gamepad', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Servers'],
                ['key' => 'servers', 'label' => 'Servers', 'type' => 'repeater', 'fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['online'=>'Online','offline'=>'Offline']],
                    ['key' => 'players', 'label' => 'Players', 'type' => 'text'],
                ]],
            ]],
            'support_tickets' => ['name' => 'Support Tickets', 'icon' => 'fa-solid fa-ticket', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Support'],
            ]],
            'custom_html' => ['name' => 'Custom HTML', 'icon' => 'fa-solid fa-code', 'category' => 'advanced', 'fields' => [
                ['key' => 'html', 'label' => 'HTML', 'type' => 'code', 'default' => ''],
            ]],
            'divider' => ['name' => 'Divider', 'icon' => 'fa-solid fa-divide', 'category' => 'structure', 'fields' => [
                ['key' => 'style', 'label' => 'Style', 'type' => 'select', 'options' => ['solid'=>'Solid','dashed'=>'Dashed','dotted'=>'Dotted'], 'default' => 'solid'],
            ]],
            'columns' => ['name' => 'Columns', 'icon' => 'fa-solid fa-columns', 'category' => 'structure', 'fields' => [
                ['key' => 'columns', 'label' => 'Count', 'type' => 'number', 'default' => 2],
                ['key' => 'items', 'label' => 'Content', 'type' => 'repeater', 'fields' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                    ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                ]],
            ]],
            'spacer' => ['name' => 'Spacer', 'icon' => 'fa-solid fa-arrows-v', 'category' => 'structure', 'fields' => [
                ['key' => 'height', 'label' => 'Height (px)', 'type' => 'number', 'default' => 40],
            ]],
            'social_media' => ['name' => 'Social Media', 'icon' => 'fa-solid fa-share-nodes', 'category' => 'components', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Follow'],
                ['key' => 'platforms', 'label' => 'Platforms', 'type' => 'multiselect', 'options' => ['facebook'=>'Facebook','twitter'=>'Twitter','instagram'=>'Instagram','youtube'=>'YouTube','linkedin'=>'LinkedIn','github'=>'GitHub','twitch'=>'Twitch','discord'=>'Discord','tiktok'=>'TikTok','spotify'=>'Spotify','pinterest'=>'Pinterest'], 'default' => ['facebook','twitter','instagram']],
            ]],
            'youtube' => ['name' => 'YouTube', 'icon' => 'fa-brands fa-youtube', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Latest Video'],
                ['key' => 'url', 'label' => 'Video URL', 'type' => 'text', 'default' => ''],
            ]],
            'twitch' => ['name' => 'Twitch', 'icon' => 'fa-brands fa-twitch', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Live Stream'],
                ['key' => 'channel', 'label' => 'Channel', 'type' => 'text', 'default' => ''],
                ['key' => 'layout', 'label' => 'Layout', 'type' => 'select', 'options' => ['embed'=>'Embed','chat'=>'With Chat'], 'default' => 'embed'],
            ]],
            'discord' => ['name' => 'Discord', 'icon' => 'fa-brands fa-discord', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Join Discord'],
                ['key' => 'invite_code', 'label' => 'Invite Code', 'type' => 'text', 'default' => ''],
                ['key' => 'server_id', 'label' => 'Server ID', 'type' => 'text', 'default' => ''],
            ]],
            'weather' => ['name' => 'Weather', 'icon' => 'fa-solid fa-cloud-sun', 'category' => 'integrations', 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Weather'],
                ['key' => 'location', 'label' => 'Location', 'type' => 'text', 'default' => 'New York'],
                ['key' => 'unit', 'label' => 'Unit', 'type' => 'select', 'options' => ['celsius'=>'°C','fahrenheit'=>'°F'], 'default' => 'celsius'],
            ]],
        ];
    }

    public function publishSite($siteId)
    {
        $this->db->table('wb_sites')->where('id', (int)$siteId)->update(['status' => 'published']);
        $this->generateSitemap($siteId);
        $this->generateRobots($siteId);
    }

    public function unpublishSite($siteId)
    {
        $this->db->table('wb_sites')->where('id', (int)$siteId)->update(['status' => 'unpublished']);
    }

    public function importTemplate($data)
    {
        return $this->db->table('wb_templates')->insertGetId([
            'name' => $data['name'] ?? 'Imported',
            'category' => $data['category'] ?? 'imported',
            'description' => $data['description'] ?? '',
            'thumbnail' => $data['thumbnail'] ?? '',
            'config' => is_string($data['config'] ?? '{}') ? $data['config'] : json_encode($data['config'] ?? []),
            'is_active' => 1,
        ]);
    }

    public function exportTemplate($templateId)
    {
        $tmpl = $this->db->table('wb_templates')->where('id', (int)$templateId)->first();
        if (!$tmpl) return null;
        return [
            'name' => $tmpl->name,
            'category' => $tmpl->category,
            'description' => $tmpl->description,
            'config' => json_decode($tmpl->config, true),
            'exported_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function generateSitemap($siteId)
    {
        $site = $this->getSite($siteId);
        if (!$site) return '';
        $pages = $this->getSitePages($siteId);
        $domain = $site->domain ?: 'localhost';
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($pages as $page) {
            $xml .= '<url><loc>http://' . htmlspecialchars($domain) . '/' . htmlspecialchars($page->slug) . '</loc>';
            $xml .= '<lastmod>' . date('c', strtotime($page->created_at)) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq><priority>0.8</priority></url>';
        }
        $xml .= '</urlset>';
        $path = BASE_PATH . '/storage/sitemaps/site_' . $siteId . '.xml';
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $xml);
        return $xml;
    }

    public function generateRobots($siteId)
    {
        $site = $this->getSite($siteId);
        if (!$site) return '';
        $domain = $site->domain ?: 'localhost';
        $robots = "User-agent: *\nAllow: /\nSitemap: http://{$domain}/sitemap.xml\n";
        $path = BASE_PATH . '/storage/robots/site_' . $siteId . '.txt';
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $robots);
        return $robots;
    }

    protected function renderHeader($content, $settings)
    {
        $logo = htmlspecialchars($content['logo'] ?? 'My Site');
        $links = $content['links'] ?? [];
        $html = '<header class="wb-header"><div class="logo" style="color:var(--wb-accent)">' . $logo . '</div><nav>';
        foreach ($links as $link) {
            $url = htmlspecialchars($link['url'] ?? '#');
            $label = htmlspecialchars($link['label'] ?? 'Link');
            $html .= '<a href="' . $url . '">' . $label . '</a>';
        }
        $html .= '</nav></header>';
        return $html;
    }

    protected function renderHero($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Welcome');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $btnText = htmlspecialchars($content['btn_text'] ?? '');
        $btnUrl = htmlspecialchars($content['btn_url'] ?? '#');
        $bg = $content['background'] ?? 'gradient';
        $html = '<section class="wb-hero ' . $bg . '"><div class="wb-container">';
        $html .= '<h1>' . $title . '</h1>';
        if ($subtitle) $html .= '<p>' . $subtitle . '</p>';
        if ($btnText) $html .= '<a href="' . $btnUrl . '" class="wb-btn wb-btn-primary">' . $btnText . '</a>';
        $html .= '</div></section>';
        return $html;
    }

    protected function renderText($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $body = $content['body'] ?? '';
        $html = '<div class="wb-text-block">';
        if ($title) $html .= '<h2>' . $title . '</h2>';
        $html .= '<div>' . $body . '</div>';
        $html .= '</div>';
        return $html;
    }

    protected function renderImage($content, $settings)
    {
        $src = htmlspecialchars($content['src'] ?? '');
        $alt = htmlspecialchars($content['alt'] ?? '');
        $caption = htmlspecialchars($content['caption'] ?? '');
        if (!$src) return '';
        $html = '<div class="wb-image-block"><img src="' . $src . '" alt="' . $alt . '">';
        if ($caption) $html .= '<div class="caption">' . $caption . '</div>';
        $html .= '</div>';
        return $html;
    }

    protected function renderGallery($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Gallery');
        $images = $content['images'] ?? [];
        $html = '<div class="wb-section"><h2 style="text-align:center;margin-bottom:20px;color:var(--wb-accent)">' . $title . '</h2>';
        $html .= '<div class="wb-gallery">';
        foreach ($images as $img) {
            $url = is_string($img) ? $img : ($img['url'] ?? '');
            if ($url) $html .= '<img src="' . htmlspecialchars($url) . '" alt="">';
        }
        $html .= '</div></div>';
        return $html;
    }

    protected function renderVideo($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $url = $content['url'] ?? '';
        $html = '<div class="wb-video-block">';
        if ($title) $html .= '<h2 style="margin-bottom:16px;color:var(--wb-accent)">' . $title . '</h2>';
        if (str_contains($url, 'youtube') || str_contains($url, 'youtu.be')) {
            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m);
            $id = $m[1] ?? '';
            if ($id) $html .= '<iframe src="https://www.youtube.com/embed/' . $id . '" allowfullscreen></iframe>';
        } elseif (str_contains($url, 'vimeo')) {
            preg_match('/vimeo\.com\/(\d+)/', $url, $m);
            $id = $m[1] ?? '';
            if ($id) $html .= '<iframe src="https://player.vimeo.com/video/' . $id . '" allowfullscreen></iframe>';
        } else {
            $html .= '<video src="' . htmlspecialchars($url) . '" controls style="width:100%;border-radius:12px"></video>';
        }
        $html .= '</div>';
        return $html;
    }

    protected function renderButton($content, $settings)
    {
        $text = htmlspecialchars($content['text'] ?? 'Click');
        $url = htmlspecialchars($content['url'] ?? '#');
        $style = $content['style'] ?? 'primary';
        return '<div style="text-align:center;padding:20px"><a href="' . $url . '" class="wb-btn wb-btn-' . $style . '">' . $text . '</a></div>';
    }

    protected function renderPricingTable($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Pricing');
        $plans = $content['plans'] ?? [];
        $html = '<div class="wb-section"><h2 style="text-align:center;margin-bottom:20px;color:var(--wb-accent)">' . $title . '</h2><div class="wb-pricing">';
        foreach ($plans as $plan) {
            $featured = !empty($plan['featured']);
            $html .= '<div class="wb-pricing-card' . ($featured ? ' featured' : '') . '">';
            $html .= '<h3>' . htmlspecialchars($plan['name'] ?? '') . '</h3>';
            $html .= '<div class="price">' . htmlspecialchars($plan['price'] ?? '') . '</div><ul>';
            foreach (($plan['features'] ?? []) as $f) $html .= '<li>' . htmlspecialchars($f) . '</li>';
            $html .= '</ul><a href="#" class="wb-btn wb-btn-primary">' . htmlspecialchars($plan['btn_text'] ?? 'Choose') . '</a></div>';
        }
        $html .= '</div></div>';
        return $html;
    }

    protected function renderTestimonials($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Testimonials');
        $items = $content['items'] ?? [];
        $html = '<div class="wb-section"><h2 style="text-align:center;margin-bottom:20px;color:var(--wb-accent)">' . $title . '</h2><div class="wb-testimonials">';
        foreach ($items as $item) {
            $html .= '<div class="wb-testimonial"><div class="quote">"' . htmlspecialchars($item['text'] ?? '') . '"</div>';
            $html .= '<div class="author">' . htmlspecialchars($item['name'] ?? '') . '</div>';
            if (!empty($item['role'])) $html .= '<div class="role">' . htmlspecialchars($item['role']) . '</div>';
            $html .= '</div>';
        }
        $html .= '</div></div>';
        return $html;
    }

    protected function renderContactForm($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Contact');
        $email = htmlspecialchars($content['email'] ?? '');
        $fields = $content['fields'] ?? ['name','email','message'];
        $html = '<div class="wb-form"><h2 style="text-align:center;margin-bottom:20px;color:var(--wb-accent)">' . $title . '</h2>';
        $html .= '<form method="post" action="' . ($email ? '/wb/contact' : '#') . '">';
        foreach ($fields as $f) {
            $label = ucfirst($f);
            $html .= '<label>' . $label . '</label>';
            if ($f === 'message') $html .= '<textarea name="' . $f . '" placeholder="' . $label . '" rows="4"></textarea>';
            else $html .= '<input type="' . ($f === 'email' ? 'email' : 'text') . '" name="' . $f . '" placeholder="' . $label . '">';
        }
        $html .= '<button type="submit" class="wb-btn wb-btn-primary">Send Message</button></form></div>';
        return $html;
    }

    protected function renderFaq($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'FAQ');
        $items = $content['items'] ?? [];
        $html = '<div class="wb-section"><h2 style="text-align:center;margin-bottom:20px;color:var(--wb-accent)">' . $title . '</h2><div class="wb-faq">';
        foreach ($items as $item) {
            $q = htmlspecialchars($item['question'] ?? '');
            $a = htmlspecialchars($item['answer'] ?? '');
            $html .= '<div class="wb-faq-item"><div class="q" onclick="var p=this.nextElementSibling;p.style.display=p.style.display==\'block\'?\'none\':\'block\'">' . $q . ' <span style="float:right">+</span></div>';
            $html .= '<div class="a" style="display:none">' . $a . '</div></div>';
        }
        $html .= '</div></div>';
        return $html;
    }

    protected function renderMap($content, $settings)
    {
        $address = htmlspecialchars($content['address'] ?? '');
        $zoom = (int)($content['zoom'] ?? 14);
        if (!$address) return '';
        return '<div class="wb-section" style="text-align:center"><iframe width="100%" height="350" style="border:0;border-radius:12px" loading="lazy" src="https://maps.google.com/maps?q=' . urlencode($address) . '&z=' . $zoom . '&output=embed"></iframe></div>';
    }

    protected function renderCountdown($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Countdown');
        $target = htmlspecialchars($content['target_date'] ?? '2026-12-31 23:59:59');
        $html = '<div class="wb-countdown"><h2 style="color:var(--wb-accent)">' . $title . '</h2>';
        $html .= '<div class="timer" data-target="' . $target . '">';
        $html .= '<div><div class="num" id="cd-days">00</div><div class="label">Days</div></div>';
        $html .= '<div><div class="num" id="cd-hours">00</div><div class="label">Hours</div></div>';
        $html .= '<div><div class="num" id="cd-mins">00</div><div class="label">Minutes</div></div>';
        $html .= '<div><div class="num" id="cd-secs">00</div><div class="label">Seconds</div></div>';
        $html .= '</div></div>';
        $html .= '<script>var t=new Date("' . $target . '").getTime();setInterval(function(){var n=new Date().getTime(),o=t-n,r=Math.floor(o/864e5),i=Math.floor(o%864e5/36e5),s=Math.floor(o%36e5/6e4),a=Math.floor(o%6e4/1e3);document.getElementById("cd-days").textContent=String(r).padStart(2,"0");document.getElementById("cd-hours").textContent=String(i).padStart(2,"0");document.getElementById("cd-mins").textContent=String(s).padStart(2,"0");document.getElementById("cd-secs").textContent=String(a).padStart(2,"0")},1e3);</script>';
        return $html;
    }

    protected function renderNewsletter($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Subscribe');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $btnText = htmlspecialchars($content['btn_text'] ?? 'Subscribe');
        $html = '<div class="wb-newsletter"><h2>' . $title . '</h2>';
        if ($subtitle) $html .= '<p>' . $subtitle . '</p>';
        $html .= '<div class="form"><input type="email" placeholder="Your email"><button class="wb-btn wb-btn-primary">' . $btnText . '</button></div></div>';
        return $html;
    }

    protected function renderHostingPackages($content, $settings) { return $this->renderPricingTable($content, $settings); }
    protected function renderOrderButton($content, $settings) { return $this->renderButton($content, $settings); }

    protected function renderLiveChat($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Chat');
        return '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent);margin-bottom:16px">' . $title . '</h2><p style="color:#94a3b8">Chat is available during business hours.</p><button class="wb-btn wb-btn-primary" onclick="alert(\'Chat widget loading\')">Start Chat</button></div>';
    }

    protected function renderRadioPlayer($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Live Stream');
        $streamUrl = htmlspecialchars($content['stream_url'] ?? '');
        $streamType = $content['stream_type'] ?? 'audio/ogg';
        return '<div class="wb-player"><h3 style="text-align:center;margin-bottom:12px;color:var(--wb-accent)">' . $title . '</h3><audio controls><source src="' . $streamUrl . '" type="' . $streamType . '"></audio></div>';
    }

    protected function renderNowPlaying($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Now Playing');
        return '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent)">' . $title . '</h2><div id="wb-nowplaying" style="font-size:18px;margin-top:8px;color:#94a3b8">Loading stream data...</div></div>';
    }

    protected function renderDjStatus($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Live DJ');
        return '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent)">' . $title . '</h2><div style="font-size:16px;margin-top:8px"><span style="color:#4ade80">● Live</span> DJ on air</div></div>';
    }

    protected function renderListenerCount($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Listeners');
        return '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent)">' . $title . '</h2><div style="font-size:42px;font-weight:800;color:var(--wb-primary)">0</div></div>';
    }

    protected function renderServerStatus($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Server Status');
        $html = '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent);margin-bottom:20px">' . $title . '</h2>';
        $html .= '<div class="wb-grid wb-grid-3" style="max-width:600px;margin:0 auto">';
        foreach (['Web Server'=>'online','Database'=>'online','Cache'=>'online'] as $name => $status) {
            $color = '#4ade80';
            $html .= '<div class="wb-card"><div style="font-size:24px;color:' . $color . '">●</div><h4>' . $name . '</h4><p>' . ucfirst($status) . '</p></div>';
        }
        $html .= '</div></div>';
        return $html;
    }

    protected function renderGameServerStatus($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Game Servers');
        $servers = $content['servers'] ?? [];
        $html = '<div class="wb-section"><h2 style="text-align:center;margin-bottom:20px;color:var(--wb-accent)">' . $title . '</h2>';
        $html .= '<div class="wb-grid wb-grid-3" style="max-width:800px;margin:0 auto">';
        foreach ($servers as $s) {
            $color = ($s['status'] ?? 'offline') === 'online' ? '#4ade80' : '#f87171';
            $html .= '<div class="wb-card"><div style="font-size:24px;color:' . $color . '">●</div><h4>' . htmlspecialchars($s['name'] ?? '') . '</h4><p>' . htmlspecialchars($s['players'] ?? '0/0') . '</p></div>';
        }
        $html .= '</div></div>';
        return $html;
    }

    protected function renderSupportTickets($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Support');
        return '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent);margin-bottom:16px">' . $title . '</h2><p style="color:#94a3b8;margin-bottom:16px">Need help? Open a ticket.</p><a href="/support" class="wb-btn wb-btn-primary">Open Ticket</a></div>';
    }

    protected function renderCustomHtml($content, $settings) { return $content['html'] ?? ''; }

    protected function renderDivider($content, $settings)
    {
        $style = $content['style'] ?? 'solid';
        return '<hr class="wb-divider ' . $style . '">';
    }

    protected function renderColumns($content, $settings)
    {
        $colCount = min((int)($content['columns'] ?? 2), 4);
        $items = $content['items'] ?? [];
        $html = '<div class="wb-columns wb-grid-' . $colCount . '">';
        foreach ($items as $item) {
            $html .= '<div class="wb-column">';
            if (!empty($item['title'])) $html .= '<h3>' . htmlspecialchars($item['title']) . '</h3>';
            if (!empty($item['text'])) $html .= '<p>' . htmlspecialchars($item['text']) . '</p>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    protected function renderSpacer($content, $settings)
    {
        $height = (int)($content['height'] ?? 40);
        return '<div class="wb-spacer" style="height:' . $height . 'px"></div>';
    }

    protected function renderSocialMedia($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Follow');
        $platforms = $content['platforms'] ?? ['facebook','twitter','instagram'];
        $icons = ['facebook'=>'fa-brands fa-facebook','twitter'=>'fa-brands fa-twitter','instagram'=>'fa-brands fa-instagram','youtube'=>'fa-brands fa-youtube','linkedin'=>'fa-brands fa-linkedin','github'=>'fa-brands fa-github','twitch'=>'fa-brands fa-twitch','discord'=>'fa-brands fa-discord','tiktok'=>'fa-brands fa-tiktok','spotify'=>'fa-brands fa-spotify','pinterest'=>'fa-brands fa-pinterest'];
        $html = '<div class="wb-social"><h2 style="color:var(--wb-accent)">' . $title . '</h2><div class="icons">';
        foreach ($platforms as $p) {
            $icon = $icons[$p] ?? 'fa-solid fa-link';
            $html .= '<a href="#" title="' . ucfirst($p) . '"><i class="' . $icon . '"></i></a>';
        }
        $html .= '</div></div>';
        return $html;
    }

    protected function renderYoutube($content, $settings) { return $this->renderVideo($content, $settings); }

    protected function renderTwitch($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Live Stream');
        $channel = htmlspecialchars($content['channel'] ?? '');
        if (!$channel) return '<div class="wb-section" style="text-align:center"><p>Configure Twitch channel</p></div>';
        $parent = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent);margin-bottom:16px">' . $title . '</h2><iframe src="https://player.twitch.tv/?channel=' . $channel . '&parent=' . $parent . '" height="400" width="100%" style="border:none;border-radius:12px;max-width:800px" allowfullscreen></iframe></div>';
    }

    protected function renderDiscord($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Join Discord');
        $invite = htmlspecialchars($content['invite_code'] ?? '');
        return '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent);margin-bottom:16px">' . $title . '</h2><a href="https://discord.gg/' . $invite . '" class="wb-btn wb-btn-primary" target="_blank"><i class="fa-brands fa-discord"></i> Join Discord</a></div>';
    }

    protected function renderWeather($content, $settings)
    {
        $title = htmlspecialchars($content['title'] ?? 'Weather');
        $location = htmlspecialchars($content['location'] ?? 'New York');
        return '<div class="wb-section" style="text-align:center"><h2 style="color:var(--wb-accent);margin-bottom:16px">' . $title . '</h2><div id="wb-weather" style="font-size:18px;color:#94a3b8">Weather for ' . $location . ' loading...</div></div>';
    }
}
