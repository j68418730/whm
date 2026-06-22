<?php
// AI Website Generator — uses OpenAI to generate site from description
function aiGenerateSite($description, $apiKey) {
    $prompt = "Generate a complete single-page HTML website with embedded CSS based on this description: \"{$description}\". 
    Return ONLY valid HTML code with <style> tags included. The design should be modern, responsive, use the Inter font from Google Fonts, 
    and have a dark theme with blue accent colors (#008cff). Include navigation, hero section, features/services, and footer.";
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => 4000,
            'temperature' => 0.7,
        ]),
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) return ['error' => 'API request failed: HTTP ' . $httpCode];
    
    $data = json_decode($resp, true);
    $html = $data['choices'][0]['message']['content'] ?? null;
    if (!$html) return ['error' => 'No content generated'];
    
    // Extract HTML if wrapped in markdown code blocks
    if (preg_match('/```html?([\s\S]*?)```/', $html, $m)) $html = trim($m[1]);
    
    return ['html' => $html];
}
