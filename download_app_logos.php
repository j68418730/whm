<?php
// App Logo Downloader
// Searches and downloads app logos from various sources
// Run: php download_app_logos.php

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$apps = $pdo->query("SELECT name, slug FROM app_catalog ORDER BY name")->fetchAll(PDO::FETCH_OBJ);
$assetDir = '/var/www/radiohosting/theme/assets/apps';

// Logo sources to try in order
$sources = [
    // CDN Logos
    'https://cdn.jsdelivr.net/npm/@programming-languages-logos/{slug}@0.0.0/src/{slug}/{slug}_256x256.png',
    'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/{slug}.png',
    'https://cdn.jsdelivr.net/gh/Louis3797/awesome-linux-tiny-linux-logo/{slug}.png',
    
    // Direct download from common sources
    function($name, $slug) {
        $iconSites = [
            "https://raw.githubusercontent.com/docker-library/docs/master/{$slug}/logo.png",
            "https://raw.githubusercontent.com/walkxcode/dashboard-icons/master/png/{$slug}.png",
            "https://icons.duckduckgo.com/ip3/{$slug}.org.ico",
            "https://www.google.com/s2/favicons?domain={$slug}.org&sz=64",
            "https://cdn.svgporn.com/logos/{$slug}.svg",
        ];
        return $iconSites;
    },
];

$downloaded = 0;
$failed = 0;

foreach ($apps as $app) {
    $slug = $app->slug;
    $appDir = "$assetDir/$slug";
    $logoPath = "$appDir/logo.png";
    
    if (file_exists($logoPath) && filesize($logoPath) > 100) {
        continue; // Already have a logo
    }
    
    $urls = [];
    
    // Generate URL variations
    $nameLower = strtolower($app->name);
    $nameClean = preg_replace('/[^a-z0-9]/', '', $nameLower);
    $nameDash = preg_replace('/[^a-z0-9-]/', '-', $nameLower);
    
    // Common CDN patterns
    $urls[] = "https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/{$slug}.png";
    $urls[] = "https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/{$nameDash}.png";
    $urls[] = "https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/{$nameClean}.png";
    
    // Simple icon services
    $urls[] = "https://icons.duckduckgo.com/ip3/{$nameDash}.org.ico";
    $urls[] = "https://www.google.com/s2/favicons?domain={$nameDash}.org&sz=64";
    $urls[] = "https://www.google.com/s2/favicons?domain={$nameClean}.com&sz=64";
    
    // Specific app URLs
    $specificMap = [
        'wordpress' => 'https://raw.githubusercontent.com/docker-library/docs/7e5e4a2ac1e150dd17a181229b63a482ee9b1f76/wordpress/logo.png',
        'joomla' => 'https://raw.githubusercontent.com/docker-library/docs/c630620294b2c1d6553f62cd1e8205e09ff51c22/joomla/logo.png',
        'drupal' => 'https://www.drupal.org/files/druplicon-small.png',
        'nextcloud' => 'https://raw.githubusercontent.com/nextcloud/promo/master/Logo/Nextcloud%20Logo%20-%20Square.png',
        'laravel' => 'https://raw.githubusercontent.com/laravel/art/master/laravel-logo.png',
        'phpmyadmin' => 'https://raw.githubusercontent.com/phpmyadmin/phpmyadmin/master/themes/pmahomme/img/logo_right.png',
        'moodle' => 'https://raw.githubusercontent.com/moodle/moodle/master/pix/moodlelogo.png',
        'mediawiki' => 'https://raw.githubusercontent.com/wikimedia/mediawiki/master/resources/assets/mediawiki.png',
        'prestashop' => 'https://raw.githubusercontent.com/PrestaShop/PrestaShop/develop/logo.png',
        'phpbb' => 'https://raw.githubusercontent.com/phpbb/phpbb/master/phpBB/images/logo.png',
    ];
    if (isset($specificMap[$slug])) {
        array_unshift($urls, $specificMap[$slug]);
    }
    
    // Try each URL
    $success = false;
    foreach ($urls as $url) {
        if (empty($url)) continue;
        $ctx = stream_context_create(['http' => ['timeout' => 5, 'user_agent' => 'Mozilla/5.0 (compatible; PlanetHosts/1.0)']]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data && strlen($data) > 200) {
            $ext = 'png';
            $mime = @getimagesizefromstring($data);
            if ($mime) {
                $map = [1=>'gif', 2=>'jpg', 3=>'png', 6=>'bmp', 18=>'webp'];
                $ext = $map[$mime[2]] ?? 'png';
            }
            file_put_contents("$appDir/logo.$ext", $data);
            if ($ext !== 'png') copy("$appDir/logo.$ext", $logoPath);
            $pdo->prepare("UPDATE app_catalog SET logo=? WHERE slug=?")->execute(["/theme/assets/apps/$slug/logo.$ext", $slug]);
            echo "  + {$app->name}: logo.$ext\n";
            $downloaded++;
            $success = true;
            break;
        }
    }
    
    // Try internet search if direct URLs failed
    if (!$success) {
        echo "  ~ {$app->name}: searching internet... ";
        $searchUrls = [];
        $query = urlencode($app->name . ' logo');
        
        // DuckDuckGo image search
        $searchUrls[] = "https://duckduckgo.com/i.js?q={$query}&o=json";
        // Google Custom Search (if API key available)
        $apiKey = getenv('GOOGLE_API_KEY') ?: '';
        $cx = getenv('GOOGLE_CX') ?: '';
        if ($apiKey && $cx) {
            $searchUrls[] = "https://www.googleapis.com/customsearch/v1?key={$apiKey}&cx={$cx}&q={$query}&searchType=image&num=1";
        }
        
        $foundSearch = false;
        foreach ($searchUrls as $searchUrl) {
            $ctx = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'Mozilla/5.0']]);
            $json = @file_get_contents($searchUrl, false, $ctx);
            if ($json) {
                $data = @json_decode($json, true);
                if ($data) {
                    // DuckDuckGo format
                    $results = $data['results'] ?? $data['items'] ?? [];
                    if (!empty($results)) {
                        $imgUrl = $results[0]['image'] ?? $results[0]['link'] ?? '';
                        if ($imgUrl) {
                            $imgData = @file_get_contents($imgUrl, false, $ctx);
                            if ($imgData && strlen($imgData) > 500) {
                                $ext = 'png';
                                $mime = @getimagesizefromstring($imgData);
                                if ($mime) {
                                    $map = [1=>'gif',2=>'jpg',3=>'png',6=>'bmp',18=>'webp'];
                                    $ext = $map[$mime[2]] ?? 'png';
                                }
                                file_put_contents("$appDir/logo.$ext", $imgData);
                                if ($ext !== 'png') copy("$appDir/logo.$ext", $logoPath);
                                $pdo->prepare("UPDATE app_catalog SET logo=? WHERE slug=?")->execute(["/theme/assets/apps/$slug/logo.$ext", $slug]);
                                echo "found from search!\n";
                                $downloaded++;
                                $foundSearch = true;
                                $success = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
        if (!$foundSearch) echo "no results.\n";
    }
    
    if (!$success) {
        // Generate a placeholder logo with text
        $text = strtoupper(substr($app->name, 0, 2));
        $colors = ['0A84FF','4ade80','a78bfa','fbbf24','f87171','34d399','38bdf8','fb923c','e879f9','c084fc'];
        $color = $colors[crc32($slug) % count($colors)];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128"><rect width="128" height="128" rx="16" fill="#'.$color.'"/><text x="64" y="72" font-family="Inter,sans-serif" font-size="48" font-weight="700" fill="#fff" text-anchor="middle">'.$text.'</text></svg>';
        file_put_contents("$appDir/logo.svg", $svg);
        file_put_contents($logoPath, $svg); // fallback
        $pdo->prepare("UPDATE app_catalog SET logo=? WHERE slug=?")->execute(["/theme/assets/apps/$slug/logo.svg", $slug]);
        echo "  - {$app->name}: placeholder generated\n";
        $failed++;
    }
}

echo "\nDownloaded: $downloaded, Placeholders: $failed, Total: " . count($apps) . "\n";
