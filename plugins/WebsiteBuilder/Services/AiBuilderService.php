<?php
namespace Plugins\WebsiteBuilder\Services;

class AiBuilderService
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get("db");
    }

    public function getApiKey()
    {
        $row = $this->db->table("automation_settings")->where("setting_key", "openai_api_key")->first();
        return $row ? $row->setting_value : "";
    }

    public function chat($messages, $model = "gpt-4o-mini", $temperature = 0.7)
    {
        $key = $this->getApiKey();
        if (!$key) return ["error" => "OpenAI API key not configured."];

        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $key", "Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode(["model" => $model, "messages" => $messages, "temperature" => $temperature]),
            CURLOPT_TIMEOUT => 120,
        ]);
        $res = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http !== 200) return ["error" => "API error: $http - " . substr($res, 0, 200)];
        $data = json_decode($res, true);
        return $data["choices"][0]["message"]["content"] ?? "No response";
    }

    public function generateImage($prompt, $size = "1024x1024")
    {
        $key = $this->getApiKey();
        if (!$key) return ["error" => "OpenAI API key not configured."];

        $ch = curl_init("https://api.openai.com/v1/images/generations");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $key", "Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode(["prompt" => $prompt, "n" => 1, "size" => $size]),
            CURLOPT_TIMEOUT => 60,
        ]);
        $res = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http !== 200) return ["error" => "Image API error: $http"];
        $data = json_decode($res, true);
        return $data["data"][0]["url"] ?? null;
    }

    public function suggestBlocks($description, $siteId)
    {
        $memory = new AiProjectMemory();
        $ctx = $memory->getContext($siteId);

        $messages = [
            ["role" => "system", "content" => "You are an AI website builder assistant. Suggest blocks for a page based on the user's description. Return a JSON array of block objects, each with: type (hero, text, image, gallery, pricing, testimonials, faq, contact_form, map, video, features, cta, stats, team, blog, logo_cloud, timeline, countdown), title, content (markdown or text). Up to 6 blocks. Respond with ONLY valid JSON."],
            ["role" => "user", "content" => "Site context: " . json_encode($ctx) . "\n\nUser request: " . $description],
        ];
        $result = $this->chat($messages);
        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $result);
        return json_decode($result, true) ?: ["error" => "Failed to parse AI response", "raw" => $result];
    }

    public function generateColorPalette($description, $siteId = null)
    {
        $memory = $siteId ? new AiProjectMemory() : null;
        $ctx = $memory ? $memory->getContext($siteId) : [];

        $messages = [
            ["role" => "system", "content" => "You are an AI branding expert. Generate a color palette for a website. Return ONLY a JSON object with: primary, secondary, accent, background, text, success, warning, error. Each value is a hex color string. Respond with ONLY valid JSON."],
            ["role" => "user", "content" => "Context: " . json_encode($ctx) . "\n\nDescription: " . $description],
        ];
        $result = $this->chat($messages, "gpt-4o-mini", 0.5);
        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $result);
        return json_decode($result, true) ?: $this->defaultPalette();
    }

    public function defaultPalette()
    {
        return ["primary" => "#0A84FF", "secondary" => "#5856D6", "accent" => "#FF9F0A", "background" => "#0F172A", "text" => "#F1F5F9", "success" => "#30D158", "warning" => "#FFD60A", "error" => "#FF453A"];
    }

    public function suggestTypography($description)
    {
        $messages = [
            ["role" => "system", "content" => "You are an AI typography expert. Suggest font pairings for a website. Return ONLY a JSON object with: heading_font (Google Font name), body_font (Google Font name), sizes (h1-h6 sizes in rem). Respond with ONLY valid JSON."],
            ["role" => "user", "content" => "Description: " . $description],
        ];
        $result = $this->chat($messages, "gpt-4o-mini", 0.5);
        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $result);
        return json_decode($result, true) ?: ["heading_font" => "Inter", "body_font" => "Inter", "sizes" => ["h1" => "2.5rem", "h2" => "2rem", "h3" => "1.75rem", "h4" => "1.5rem", "h5" => "1.25rem", "h6" => "1rem"]];
    }

    public function analyzeSite($html)
    {
        $messages = [
            ["role" => "system", "content" => "You are an AI website analyst. Analyze the provided HTML and suggest improvements for SEO, accessibility, performance, design, mobile responsiveness, and content. Return a JSON object with: score (0-100), seo (array of issues), accessibility (array), performance (array), design (array), content (array), recommendations (array of actionable suggestions). Each issue has: severity (high/medium/low), title, description. Respond with ONLY valid JSON."],
            ["role" => "user", "content" => "HTML: " . substr($html, 0, 50000)],
        ];
        $result = $this->chat($messages, "gpt-4o-mini", 0.3);
        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $result);
        return json_decode($result, true) ?: ["error" => "Failed to parse analysis"];
    }

    public function naturalLanguageEdit($blocks, $instruction, $siteId = null)
    {
        $memory = $siteId ? new AiProjectMemory() : null;
        $ctx = $memory ? $memory->getContext($siteId) : [];

        $messages = [
            ["role" => "system", "content" => "You are an AI website editor. Modify the blocks array according to the user's instruction. Return ONLY the modified blocks JSON array. Keep all fields intact unless the instruction changes them. Add new blocks if needed. Respond with ONLY valid JSON."],
            ["role" => "user", "content" => "Context: " . json_encode($ctx) . "\n\nCurrent blocks: " . json_encode($blocks) . "\n\nInstruction: " . $instruction],
        ];
        $result = $this->chat($messages);
        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $result);
        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : ["error" => "Failed to parse edit", "raw" => $result];
    }

    public function generateSiteFromQuestions($answers)
    {
        $messages = [
            ["role" => "system", "content" => "You are an AI website generator. Generate a complete website structure based on user answers. Return ONLY a JSON object with: site_name, tagline, pages (array of objects with: title, slug, blocks array), navigation (array of nav items with: label, slug, children array), theme_colors (object with primary, secondary, accent, background, text), fonts (object with heading, body), footer_text, seo (object with meta_description, meta_keywords). Each block has: type (hero, text, image, gallery, pricing, testimonials, faq, contact_form, map, video, features, cta, stats, team, blog, logo_cloud, timeline, countdown), title, content, settings (object with layout, style, animation). For a complete site suggest 5-7 pages. Respond with ONLY valid JSON."],
            ["role" => "user", "content" => "Answers: " . json_encode($answers)],
        ];
        $result = $this->chat($messages, "gpt-4o-mini", 0.5);
        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $result);
        return json_decode($result, true) ?: ["error" => "Failed to generate site"];
    }

    public function generateFaviconPrompt($name, $industry)
    {
        return "Modern minimal favicon for \"$name\" - a $industry company. Simple flat design, single icon, transparent background, vector style, 64x64, brand colors.";
    }
}
