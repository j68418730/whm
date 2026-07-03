<?php
namespace Plugins\WebsiteBuilder\Services;

class AiProjectMemory
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get("db");
    }

    public function ensureTable()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS wb_ai_memory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            site_id INT NOT NULL,
            brand_colors TEXT,
            fonts TEXT,
            logo_url VARCHAR(500),
            page_structure TEXT,
            products_services TEXT,
            writing_style TEXT,
            business_goals TEXT,
            preferred_layouts TEXT,
            previous_changes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY site_id (site_id),
            FOREIGN KEY (site_id) REFERENCES wb_sites(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function getContext($siteId)
    {
        $siteId = (int)$siteId;
        if (!$siteId) return [];

        $memory = $this->db->table("wb_ai_memory")->where("site_id", $siteId)->first();
        $site = $this->db->table("wb_sites")->where("id", $siteId)->first();

        $ctx = [
            "site_name" => $site->name ?? "",
            "site_domain" => $site->domain ?? "",
            "pages" => $this->db->table("wb_pages")->where("site_id", $siteId)->where("is_deleted", 0)->get() ?: [],
        ];

        if ($memory) {
            $ctx["brand_colors"] = json_decode($memory->brand_colors, true) ?? [];
            $ctx["fonts"] = json_decode($memory->fonts, true) ?? [];
            $ctx["logo_url"] = $memory->logo_url ?? "";
            $ctx["writing_style"] = $memory->writing_style ?? "";
            $ctx["business_goals"] = $memory->business_goals ?? "";
        }
        return $ctx;
    }

    public function saveMemory($siteId, $data)
    {
        $siteId = (int)$siteId;
        $this->ensureTable();
        $existing = $this->db->table("wb_ai_memory")->where("site_id", $siteId)->first();

        $record = [
            "brand_colors" => isset($data["brand_colors"]) ? (is_string($data["brand_colors"]) ? $data["brand_colors"] : json_encode($data["brand_colors"])) : null,
            "fonts" => isset($data["fonts"]) ? (is_string($data["fonts"]) ? $data["fonts"] : json_encode($data["fonts"])) : null,
            "logo_url" => $data["logo_url"] ?? null,
            "page_structure" => isset($data["page_structure"]) ? (is_string($data["page_structure"]) ? $data["page_structure"] : json_encode($data["page_structure"])) : null,
            "products_services" => $data["products_services"] ?? null,
            "writing_style" => $data["writing_style"] ?? null,
            "business_goals" => $data["business_goals"] ?? null,
            "preferred_layouts" => $data["preferred_layouts"] ?? null,
            "previous_changes" => $data["previous_changes"] ?? null,
        ];

        if ($existing) {
            $this->db->table("wb_ai_memory")->where("site_id", $siteId)->update($record);
        } else {
            $record["site_id"] = $siteId;
            $this->db->table("wb_ai_memory")->insert($record);
        }
    }

    public function appendChange($siteId, $change)
    {
        $memory = $this->db->table("wb_ai_memory")->where("site_id", (int)$siteId)->first();
        $changes = $memory ? $memory->previous_changes : "";
        $changes = trim($changes . "\n- [" . date("Y-m-d H:i") . "] " . $change);
        $this->db->table("wb_ai_memory")->where("site_id", (int)$siteId)->update(["previous_changes" => $changes]);
    }
}
