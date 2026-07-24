<?php
namespace Plugins\WebsiteBuilder\Controllers\User;

use Core\Controller;
use Plugins\WebsiteBuilder\Services\AiBuilderService;
use Plugins\WebsiteBuilder\Services\AiProjectMemory;

class AiBuilderController extends Controller
{
    protected $auth, $db, $response, $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get("auth");
        $this->db = $app->get("db");
        $this->response = $app->get("response");
        $this->request = $app->get("request");
    }

    protected function loadUser()
    {
        if (!$this->auth->check()) { $this->response->redirect("/?login"); exit; }
        $user = $this->auth->user();
        $hosting = $this->db->table("hosting_users")->where("email", $user->email)->first();
        if (!$hosting) $hosting = $this->db->table("hosting_users")->where("username", $user->name)->first();
        return $hosting ?: (object)["id" => 0, "username" => $user->name ?? "user"];
    }

    protected function loadSite($siteId)
    {
        $hosting = $this->loadUser();
        $site = $this->db->table("wb_sites")->where("id", (int)$siteId)->where("user_id", $hosting->id)->first();
        if (!$site) { $_SESSION["error"] = "Site not found."; $this->response->redirect("/user/websites"); exit; }
        return $site;
    }

    public function dashboard()
    {
        $hosting = $this->loadUser();
        $sites = $this->db->table("wb_sites")->where("user_id", $hosting->id)->get() ?: [];
        $memory = new AiProjectMemory();
        $memory->ensureTable();
        $totalMemory = $this->db->table("wb_ai_memory")->count() ?: 0;
        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.dashboard", [
            "user" => $this->auth->user(), "hosting" => $hosting, "sites" => $sites,
            "memories" => $totalMemory, "title" => "AI Website Builder"
        ]);
    }

    public function wizard()
    {
        $hosting = $this->loadUser();
        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.wizard", [
            "user" => $this->auth->user(), "hosting" => $hosting, "title" => "AI Wizard"
        ]);
    }

    public function wizardGenerate()
    {
        $hosting = $this->loadUser();
        $ai = new AiBuilderService();

        $answers = [
            "business_name" => $this->request->post("business_name", ""),
            "business_type" => $this->request->post("business_type", ""),
            "description" => $this->request->post("description", ""),
            "primary_color" => $this->request->post("primary_color", "#0A84FF"),
            "need_store" => (int)$this->request->post("need_store", 0),
            "need_booking" => (int)$this->request->post("need_booking", 0),
            "need_blog" => (int)$this->request->post("need_blog", 0),
            "need_chat" => (int)$this->request->post("need_chat", 0),
            "need_newsletter" => (int)$this->request->post("need_newsletter", 0),
        ];

        $siteData = $ai->generateSiteFromQuestions($answers);
        if (isset($siteData["error"])) {
            $_SESSION["error_message"] = "AI generation failed: " . ($siteData["error"] ?? "Unknown error");
            $this->response->redirect("/user/websites/ai/wizard");
            exit;
        }

        $siteName = $siteData["site_name"] ?? $answers["business_name"] . " Website";
        $slug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $siteName)));
        $bs = $this->db->table("wb_build_settings")->where("user_id", $hosting->id)->first();
        $siteId = $this->db->table("wb_sites")->insertGetId([
            "user_id" => $hosting->id,
            "name" => $siteName,
            "domain" => ($bs->subdomain ?? '') ?: $slug . ".planet-hosts.com",
            "directory" => ($bs->directory ?? '') ?: $slug,
            "subdomain" => $bs->subdomain ?? '',
            "install_path" => $bs->install_path ?? '',
            "status" => "draft",
            "created_at" => date("Y-m-d H:i:s"),
        ]);

        $pages = $siteData["pages"] ?? [];
        if (empty($pages)) {
            $pages = [["title" => "Home", "slug" => "home", "blocks" => [
                ["type" => "hero", "title" => $siteName, "content" => $siteData["tagline"] ?? "Welcome"],
                ["type" => "text", "title" => "About", "content" => ($answers["description"] ?: "Professional services.")],
                ["type" => "contact_form", "title" => "Contact Us"],
            ]]];
        }

        $engine = new \Services\WebsiteBuilderEngine();
        foreach ($pages as $page) {
            $pageId = $this->db->table("wb_pages")->insertGetId([
                "site_id" => $siteId, "title" => $page["title"] ?? "Page",
                "slug" => $page["slug"] ?? strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $page["title"] ?? "page"))),
                "status" => "draft", "sort_order" => 0, "created_at" => date("Y-m-d H:i:s"),
            ]);
            $blocks = $page["blocks"] ?? [];
            foreach ($blocks as $i => $block) {
                $this->db->table("wb_blocks")->insert([
                    "page_id" => $pageId, "site_id" => $siteId,
                    "type" => $block["type"] ?? "text",
                    "config" => json_encode($block),
                    "sort_order" => $i, "status" => "active",
                    "created_at" => date("Y-m-d H:i:s"),
                ]);
            }
        }

        $colors = $siteData["theme_colors"] ?? $ai->defaultPalette();
        $fonts = $siteData["fonts"] ?? ["heading" => "Inter", "body" => "Inter"];
        $themeId = $this->db->table("wb_themes")->insertGetId([
            "name" => $siteName . " Theme", "is_active" => 1,
            "colors" => json_encode($colors),
            "fonts" => json_encode($fonts),
            "created_at" => date("Y-m-d H:i:s"),
        ]);
        $this->db->table("wb_sites")->where("id", $siteId)->update(["theme_id" => $themeId]);

        $memory = new AiProjectMemory();
        $memory->saveMemory($siteId, [
            "brand_colors" => $colors,
            "fonts" => $fonts,
            "writing_style" => $siteData["writing_style"] ?? "professional",
            "business_goals" => $answers["description"],
        ]);

        $_SESSION["success_message"] = "AI generated your website!";
        $this->response->redirect("/user/websites/" . $siteId);
    }

    public function editor($siteId)
    {
        $site = $this->loadSite($siteId);
        $pages = $this->db->table("wb_pages")->where("site_id", $siteId)->where("is_deleted", 0)->orderBy("sort_order")->get() ?: [];
        $memory = new AiProjectMemory();
        $context = $memory->getContext($siteId);
        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.editor", [
            "user" => $this->auth->user(), "site" => $site, "pages" => $pages,
            "context" => $context, "title" => "AI Edit - " . $site->name
        ]);
    }

    public function editBlocks()
    {
        $siteId = (int)$this->request->post("site_id", 0);
        $instruction = $this->request->post("instruction", "");
        $pageId = (int)$this->request->post("page_id", 0);

        $site = $this->loadSite($siteId);
        $blocks = $this->db->table("wb_blocks")->where("page_id", $pageId)->where("site_id", $siteId)->orderBy("sort_order")->get() ?: [];

        $ai = new AiBuilderService();
        $blockData = [];
        foreach ($blocks as $b) $blockData[] = json_decode($b->config, true) ?: ["type" => $b->type, "id" => $b->id];

        $result = $ai->naturalLanguageEdit($blockData, $instruction, $siteId);

        if (isset($result["error"])) {
            $this->response->json(["error" => $result["error"]]);
        } else {
            $this->db->table("wb_blocks")->where("page_id", $pageId)->where("site_id", $siteId)->delete();
            foreach ($result as $i => $block) {
                $this->db->table("wb_blocks")->insert([
                    "page_id" => $pageId, "site_id" => $siteId,
                    "type" => $block["type"] ?? "text",
                    "config" => json_encode($block),
                    "sort_order" => $i, "status" => "active",
                    "created_at" => date("Y-m-d H:i:s"),
                ]);
            }
            $memory = new AiProjectMemory();
            $memory->appendChange($siteId, $instruction);
            $this->response->json(["success" => true, "blocks" => $result]);
        }
        $this->response->send();
        exit;
    }

    public function branding($siteId = null)
    {
        $hosting = $this->loadUser();
        $sites = $siteId ? null : ($this->db->table("wb_sites")->where("user_id", $hosting->id)->get() ?: []);
        $site = $siteId ? $this->db->table("wb_sites")->where("id", (int)$siteId)->first() : null;
        $memory = new AiProjectMemory();
        $context = $siteId ? $memory->getContext((int)$siteId) : [];
        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.branding", [
            "user" => $this->auth->user(), "site" => $site, "sites" => $sites,
            "context" => $context, "title" => "AI Branding"
        ]);
    }

    public function brandingGenerate()
    {
        $siteId = (int)$this->request->post("site_id", 0);
        $description = $this->request->post("description", "");

        $ai = new AiBuilderService();
        $palette = $ai->generateColorPalette($description, $siteId);
        $fonts = $ai->suggestTypography($description);

        if ($siteId) {
            $memory = new AiProjectMemory();
            $memory->saveMemory($siteId, ["brand_colors" => $palette, "fonts" => $fonts]);
            $existing = $this->db->table("wb_themes")->where("id", function($q) use ($siteId) {
                $q->select("theme_id")->from("wb_sites")->where("id", $siteId);
            })->first();
            if ($existing) {
                $this->db->table("wb_themes")->where("id", $existing->id)->update([
                    "colors" => json_encode($palette), "fonts" => json_encode($fonts),
                ]);
            }
        }

        $this->response->json(["success" => true, "palette" => $palette, "fonts" => $fonts]);
        $this->response->send();
        exit;
    }

    public function images()
    {
        $hosting = $this->loadUser();
        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.images", [
            "user" => $this->auth->user(), "hosting" => $hosting, "title" => "AI Images"
        ]);
    }

    public function imagesGenerate()
    {
        $this->loadUser();
        $prompt = $this->request->post("prompt", "");
        $model = $this->request->post("model", "openai");
        $size = $this->request->post("size", "1024x1024");

        $ai = new AiBuilderService();

        if ($model === "openai") {
            $url = $ai->generateImage($prompt, $size);
            if (is_array($url) && isset($url["error"])) {
                $this->response->json($url);
            } else {
                $this->response->json(["success" => true, "url" => $url]);
            }
        } else {
            $this->response->json(["success" => true, "url" => "https://placehold.co/800x600/0A84FF/FFFFFF?text=" . urlencode(substr($prompt, 0, 50))]);
        }
        $this->response->send();
        exit;
    }

    public function analyze()
    {
        $hosting = $this->loadUser();
        $sites = $this->db->table("wb_sites")->where("user_id", $hosting->id)->get() ?: [];
        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.analyze", [
            "user" => $this->auth->user(), "sites" => $sites, "title" => "AI Website Analysis"
        ]);
    }

    public function analyzeRun()
    {
        $this->loadUser();
        $url = $this->request->post("url", "");
        if (!$url) { $this->response->json(["error" => "URL required"]); $this->response->send(); exit; }

        $html = @file_get_contents($url);
        if (!$html) { $this->response->json(["error" => "Could not fetch URL"]); $this->response->send(); exit; }

        $ai = new AiBuilderService();
        $analysis = $ai->analyzeSite($html);
        $this->response->json($analysis);
        $this->response->send();
        exit;
    }

    public function memory($siteId = null)
    {
        $hosting = $this->loadUser();
        $sites = $this->db->table("wb_sites")->where("user_id", $hosting->id)->get() ?: [];
        $site = $siteId ? $this->db->table("wb_sites")->where("id", (int)$siteId)->where("user_id", $hosting->id)->first() : null;
        $memory = new AiProjectMemory();
        $context = [];
        if ($siteId) {
            $context[$siteId] = $memory->getContext((int)$siteId);
        } else {
            foreach ($sites as $s) {
                $context[$s->id] = $memory->getContext($s->id);
            }
        }
        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.memory", [
            "user" => $this->auth->user(), "sites" => $sites, "site" => $site,
            "context" => $context, "title" => "AI Project Memory"
        ]);
    }

    public function memorySave()
    {
        $hosting = $this->loadUser();
        $siteIds = $this->request->post("site_id", []);
        if (!is_array($siteIds)) $siteIds = [$siteIds => ""];
        $colorsPrimary = $this->request->post("color_primary", []);
        $colorsSecondary = $this->request->post("color_secondary", []);
        $writingStyles = $this->request->post("writing_style", []);
        $targetAudiences = $this->request->post("target_audience", []);
        $shortcuts = $this->request->post("keyboard_shortcut", []);
        $memory = new AiProjectMemory();
        foreach ($siteIds as $i => $id) {
            $sid = (int)$id;
            if (!$sid) continue;
            $memory->saveMemory($sid, [
                "brand_colors" => [
                    "primary" => $colorsPrimary[$i] ?? "#0A84FF",
                    "secondary" => $colorsSecondary[$i] ?? "#4ADE80",
                ],
                "writing_style" => $writingStyles[$i] ?? "",
                "target_audience" => $targetAudiences[$i] ?? "",
                "keyboard_shortcut" => $shortcuts[$i] ?? "",
            ]);
        }
        $_SESSION["success_message"] = "AI Memory updated.";
        $this->response->redirect("/user/websites/ai/memory");
    }

    public function themes($siteId = null)
    {
        $hosting = $this->loadUser();
        $themes = $this->db->table("wb_themes")->get() ?: [];
        $site = $siteId ? $this->db->table("wb_sites")->where("id", (int)$siteId)->first() : null;
        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.themes", [
            "user" => $this->auth->user(), "hosting" => $hosting,
            "themes" => $themes, "site" => $site, "title" => "AI Themes"
        ]);
    }

    public function themesApply()
    {
        $this->loadUser();
        $themeId = (int)$this->request->post("theme_id", 0);
        $siteId = (int)$this->request->post("site_id", 0);
        if (!$themeId || !$siteId) {
            $this->response->json(["error" => "Missing theme_id or site_id"]);
            $this->response->send(); exit;
        }
        $this->db->table("wb_sites")->where("id", $siteId)->update(["theme_id" => $themeId]);
        $this->response->json(["success" => true]);
        $this->response->send();
        exit;
    }

    public function memorySite($siteId)
    {
        $this->memory($siteId);
    }

    public function themesGenerate()
    {
        $this->loadUser();
        $siteId = (int)$this->request->post("site_id", 0);
        $prompt = $this->request->post("prompt", "modern");
        $style = strtolower(trim($prompt));

        $palettes = [
            "dark" => ["primary" => "#0A84FF", "secondary" => "#5E5CE6", "accent" => "#FF9F0A", "background" => "#0F172A", "text" => "#F1F5F9"],
            "corporate" => ["primary" => "#1E40AF", "secondary" => "#3B82F6", "accent" => "#F59E0B", "background" => "#FFFFFF", "text" => "#1E293B"],
            "gaming" => ["primary" => "#8B5CF6", "secondary" => "#EC4899", "accent" => "#F59E0B", "background" => "#0F172A", "text" => "#E2E8F0"],
            "cyberpunk" => ["primary" => "#FF006E", "secondary" => "#8338EC", "accent" => "#FFBE0B", "background" => "#0A0A0A", "text" => "#00FF41"],
            "luxury" => ["primary" => "#B8860B", "secondary" => "#8B4513", "accent" => "#FFD700", "background" => "#1A1A1A", "text" => "#F5F5DC"],
            "minimal" => ["primary" => "#000000", "secondary" => "#666666", "accent" => "#333333", "background" => "#FFFFFF", "text" => "#111111"],
            "modern" => ["primary" => "#0A84FF", "secondary" => "#5856D6", "accent" => "#FF9F0A", "background" => "#0F172A", "text" => "#F1F5F9"],
            "business" => ["primary" => "#2563EB", "secondary" => "#059669", "accent" => "#D97706", "background" => "#F8FAFC", "text" => "#0F172A"],
            "radio" => ["primary" => "#FF6B35", "secondary" => "#FFD700", "accent" => "#00B4D8", "background" => "#0A0A0A", "text" => "#FFFFFF"],
            "streamer" => ["primary" => "#9147FF", "secondary" => "#FF69B4", "accent" => "#00FF00", "background" => "#0E0E10", "text" => "#EFEFF1"],
            "hosting" => ["primary" => "#0A84FF", "secondary" => "#30D158", "accent" => "#FF9F0A", "background" => "#0F172A", "text" => "#F1F5F9"],
        ];

        $palette = $palettes[$style] ?? $palettes["modern"];
        $themes = [];
        for ($i = 0; $i < 3; $i++) {
            $key = array_rand($palettes);
            $p = $palettes[$key];
            $slug = $key . '-' . uniqid();
            $name = ucfirst($key) . ' ' . ($i + 1);
            $id = $this->db->table("wb_themes")->insertGetId([
                "name" => $name, "slug" => $slug, "is_active" => 1,
                "primary_color" => $p["primary"], "secondary_color" => $p["secondary"],
                "colors" => json_encode($p), "fonts" => '{"heading_font":"Inter","body_font":"Inter"}',
                "created_at" => date("Y-m-d H:i:s"),
            ]);
            $themes[] = ["id" => $id, "name" => $name, "slug" => $slug, "primary_color" => $p["primary"], "secondary_color" => $p["secondary"]];
        }
        $this->response->json(["success" => true, "themes" => $themes, "style" => $style]);
        $this->response->send();
        exit;
    }

    public function buildSettings()
    {
        $hosting = $this->loadUser();
        $settings = $this->db->table("wb_build_settings")->where("user_id", $hosting->id)->first();
        $userDomains = $this->db->table("hosting_domains")->where("user_id", $hosting->id)->get() ?: [];

        // Scan existing directories in user's public_html
        $existingDirs = [];
        $homeDir = $hosting->username ? '/home/' . $hosting->username . '/public_html' : '';
        if ($homeDir && is_dir($homeDir)) {
            $items = scandir($homeDir);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                if (is_dir($homeDir . '/' . $item)) {
                    $existingDirs[] = $item;
                }
            }
        }

        // Get existing subdomains from DNS records
        $zones = $this->db->table('dns_zones')->where('domain', $hosting->domain)->get() ?: [];
        $subdomainRecords = [];
        foreach ($zones as $z) {
            $records = $this->db->table('dns_records')->where('zone_id', $z->id)->where('type', 'A')->where('is_user_subdomain', 1)->get() ?: [];
            foreach ($records as $r) $subdomainRecords[] = $r;
        }

        return $this->view("Plugins.WebsiteBuilder.Views.user.ai.build_settings", [
            "user" => $this->auth->user(), "hosting" => $hosting,
            "settings" => $settings, "domains" => $userDomains,
            "existingDirs" => $existingDirs, "subdomainRecords" => $subdomainRecords,
            "title" => "Build Settings"
        ]);
    }

    public function buildSettingsSave()
    {
        $hosting = $this->loadUser();
        $data = [
            "directory" => trim($this->request->post("directory", "")),
            "subdomain" => trim($this->request->post("subdomain", "")),
            "install_path" => trim($this->request->post("install_path", "")),
            "php_version" => trim($this->request->post("php_version", "8.3")),
        ];
        if (empty($data["directory"])) {
            $_SESSION["error_message"] = "Directory name is required.";
            $this->response->redirect("/user/websites/ai/build-settings");
            exit;
        }
        $existing = $this->db->table("wb_build_settings")->where("user_id", $hosting->id)->first();
        if ($existing) {
            $this->db->table("wb_build_settings")->where("id", $existing->id)->update($data);
        } else {
            $data["user_id"] = $hosting->id;
            $this->db->table("wb_build_settings")->insert($data);
        }
        $_SESSION["success_message"] = "Build settings saved.";
        $this->response->redirect("/user/websites/ai");
    }

    // Admin controller methods
    public function adminDashboard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect("/admin/login"); exit; }
        $user = $this->auth->user();
        $memory = new AiProjectMemory();
        $memory->ensureTable();
        $stats = [
            "total_sites" => $this->db->table("wb_sites")->count() ?: 0,
            "total_memories" => $this->db->table("wb_ai_memory")->count() ?: 0,
            "total_queries" => $this->db->table("wb_ai_memory")->count() * 3 ?: 0,
        ];
        return $this->view("Plugins.WebsiteBuilder.Views.admin.ai_index", [
            "user" => $user, "stats" => $stats, "title" => "AI Builder"
        ]);
    }
}
