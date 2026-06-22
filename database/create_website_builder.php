<?php
/**
 * Website Builder Database Migration
 * Creates all tables and seeds 30+ templates
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/Config.php';
$config = require BASE_PATH . '/config/app.php';
$dbConfig = $config['database']['connections']['mysql'] ?? $config['database'] ?? [];

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ]);
    echo "Connected to database.\n";

    // Drop existing tables
    $tables = ['wb_form_entries', 'wb_forms', 'wb_comments', 'wb_blog_posts', 'wb_menus', 'wb_media', 'wb_blocks', 'wb_pages', 'wb_sites', 'wb_themes', 'wb_templates'];
    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `{$t}`");
        echo "Dropped table {$t}\n";
    }

    // Create wb_templates
    $pdo->exec("CREATE TABLE `wb_templates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `category` VARCHAR(100) DEFAULT NULL,
        `description` TEXT,
        `thumbnail` VARCHAR(255) DEFAULT NULL,
        `config` JSON DEFAULT NULL,
        `is_active` TINYINT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_templates\n";

    // Create wb_themes
    $pdo->exec("CREATE TABLE `wb_themes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `version` VARCHAR(20) DEFAULT '1.0',
        `author` VARCHAR(255) DEFAULT NULL,
        `config` JSON DEFAULT NULL,
        `folder` VARCHAR(255) DEFAULT NULL,
        `is_active` TINYINT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_themes\n";

    // Create wb_sites
    $pdo->exec("CREATE TABLE `wb_sites` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `domain` VARCHAR(255) DEFAULT NULL,
        `template_id` INT DEFAULT NULL,
        `theme_id` INT DEFAULT NULL,
        `status` ENUM('draft','published','unpublished') DEFAULT 'draft',
        `settings` JSON DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_sites\n";

    // Create wb_pages
    $pdo->exec("CREATE TABLE `wb_pages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `site_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `content` JSON DEFAULT NULL,
        `meta_title` VARCHAR(255) DEFAULT NULL,
        `meta_description` TEXT,
        `meta_keywords` TEXT,
        `og_image` VARCHAR(255) DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        `status` ENUM('draft','published') DEFAULT 'draft',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_pages\n";

    // Create wb_blocks
    $pdo->exec("CREATE TABLE `wb_blocks` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `page_id` INT NOT NULL,
        `type` VARCHAR(50) NOT NULL,
        `content` JSON DEFAULT NULL,
        `settings` JSON DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        `zone` VARCHAR(50) DEFAULT 'content'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_blocks\n";

    // Create wb_media
    $pdo->exec("CREATE TABLE `wb_media` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `site_id` INT NOT NULL,
        `user_id` INT DEFAULT NULL,
        `filename` VARCHAR(255) NOT NULL,
        `original_name` VARCHAR(255) DEFAULT NULL,
        `path` VARCHAR(500) DEFAULT NULL,
        `type` VARCHAR(100) DEFAULT NULL,
        `size` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_media\n";

    // Create wb_menus
    $pdo->exec("CREATE TABLE `wb_menus` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `site_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `location` ENUM('main','footer','sidebar') DEFAULT 'main',
        `items` JSON DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_menus\n";

    // Create wb_forms
    $pdo->exec("CREATE TABLE `wb_forms` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `site_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `fields` JSON DEFAULT NULL,
        `settings` JSON DEFAULT NULL,
        `entries_count` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_forms\n";

    // Create wb_form_entries
    $pdo->exec("CREATE TABLE `wb_form_entries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `form_id` INT NOT NULL,
        `data` JSON DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_form_entries\n";

    // Create wb_blog_posts
    $pdo->exec("CREATE TABLE `wb_blog_posts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `site_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `content` LONGTEXT,
        `excerpt` TEXT,
        `featured_image` VARCHAR(500) DEFAULT NULL,
        `category` VARCHAR(100) DEFAULT NULL,
        `tags` TEXT DEFAULT NULL,
        `author` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'draft',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_blog_posts\n";

    // Create wb_comments
    $pdo->exec("CREATE TABLE `wb_comments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `post_id` INT NOT NULL,
        `author` VARCHAR(255) DEFAULT NULL,
        `email` VARCHAR(255) DEFAULT NULL,
        `content` TEXT,
        `status` VARCHAR(50) DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created wb_comments\n";

    // Seed default themes
    $defaultThemes = [
        ['Cosmic', 'Dark cosmic theme with vibrant gradients', '1.0', 'Planet-Hosts', json_encode(['primary' => '#008cff', 'secondary' => '#00e5ff', 'bg' => '#02050e', 'card_bg' => 'rgba(8,16,28,.85)', 'text' => '#ffffff', 'accent' => '#38bdf8'])],
        ['LightCore', 'Clean light theme for professional sites', '1.0', 'Planet-Hosts', json_encode(['primary' => '#2563eb', 'secondary' => '#7c3aed', 'bg' => '#ffffff', 'card_bg' => '#f8fafc', 'text' => '#0f172a', 'accent' => '#3b82f6'])],
        ['Nature', 'Earthy green theme for nature and organic', '1.0', 'Planet-Hosts', json_encode(['primary' => '#059669', 'secondary' => '#10b981', 'bg' => '#0c0a09', 'card_bg' => 'rgba(5,150,105,.08)', 'text' => '#ffffff', 'accent' => '#34d399'])],
        ['Sunset', 'Warm orange and purple gradient theme', '1.0', 'Planet-Hosts', json_encode(['primary' => '#ea580c', 'secondary' => '#d946ef', 'bg' => '#0c0a09', 'card_bg' => 'rgba(234,88,12,.08)', 'text' => '#ffffff', 'accent' => '#f97316'])],
        ['Ocean', 'Deep blue oceanic theme', '1.0', 'Planet-Hosts', json_encode(['primary' => '#0284c7', 'secondary' => '#06b6d4', 'bg' => '#020617', 'card_bg' => 'rgba(2,132,199,.08)', 'text' => '#ffffff', 'accent' => '#38bdf8'])],
        ['Midnight', 'Dark purple theme with neon accents', '1.0', 'Planet-Hosts', json_encode(['primary' => '#7c3aed', 'secondary' => '#a855f7', 'bg' => '#09000b', 'card_bg' => 'rgba(124,58,237,.08)', 'text' => '#ffffff', 'accent' => '#c084fc'])],
    ];
    $stmt = $pdo->prepare("INSERT INTO wb_themes (name, description, version, author, config) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultThemes as $t) {
        $stmt->execute($t);
    }
    echo "Seeded " . count($defaultThemes) . " themes\n";

    // Seed 30+ templates
    $templates = [
        ['Business Pro', 'business', 'Professional business website with hero, services, team, testimonials and contact form', '/theme/assets/img/templates/business.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Home', 'url' => '#home'], ['label' => 'Services', 'url' => '#services'], ['label' => 'About', 'url' => '#about'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Welcome to {{NAME}}', 'subtitle' => 'Your trusted partner for business success and digital transformation.', 'btn_text' => 'Get Started', 'btn_url' => '#contact', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Services', 'body' => '<div class="grid-3"><div class="service-card"><h3>Web Design</h3><p>Modern responsive websites</p></div><div class="service-card"><h3>Mobile Apps</h3><p>iOS and Android applications</p></div><div class="service-card"><h3>Cloud Solutions</h3><p>Scalable cloud infrastructure</p></div></div>']],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'What Our Clients Say', 'items' => [['name' => 'John Doe', 'role' => 'CEO, TechCorp', 'text' => 'Amazing service and support!'], ['name' => 'Jane Smith', 'role' => 'Founder, StartupX', 'text' => 'Transformed our online presence.']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Contact Us', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.', 'links' => []]],
                ]],
                ['title' => 'About', 'slug' => 'about', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'About', 'url' => '/about'], ['label' => 'Services', 'url' => '/services'], ['label' => 'Contact', 'url' => '/contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'About Us', 'subtitle' => 'Learn about our story and mission.', 'btn_text' => '', 'btn_url' => '', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Story', 'body' => '<p>Founded in 2020, {{NAME}} has grown from a small startup to a leading provider of digital solutions. Our team of experienced professionals is dedicated to delivering exceptional results for our clients.</p><p>We believe in innovation, quality, and putting our clients first.</p>']],
                    ['type' => 'divider', 'zone' => 'content', 'content' => []],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => 'Mission', 'text' => 'Empower businesses with technology'], ['title' => 'Vision', 'text' => 'Be the global leader in digital solutions'], ['title' => 'Values', 'text' => 'Integrity, innovation, excellence']]]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
                ['title' => 'Services', 'slug' => 'services', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Services', 'url' => '/services'], ['label' => 'Contact', 'url' => '/contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Our Services', 'subtitle' => 'Comprehensive solutions for your business.', 'btn_text' => '', 'btn_url' => '', 'background' => 'gradient']],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Pricing Plans', 'plans' => [['name' => 'Starter', 'price' => '$29', 'features' => ['1 Website', '10 GB Storage', 'Basic Support'], 'btn_text' => 'Choose Plan'], ['name' => 'Professional', 'price' => '$79', 'features' => ['5 Websites', '50 GB Storage', 'Priority Support', 'SSL Included'], 'btn_text' => 'Choose Plan', 'featured' => true], ['name' => 'Enterprise', 'price' => '$199', 'features' => ['Unlimited Websites', '500 GB Storage', '24/7 Support', 'Custom Solutions'], 'btn_text' => 'Contact Us']]]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
                ['title' => 'Contact', 'slug' => 'contact', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Contact', 'url' => '/contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Get In Touch', 'subtitle' => 'We would love to hear from you.', 'btn_text' => '', 'btn_url' => '', 'background' => 'gradient']],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Send Us a Message', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'subject', 'message']]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => '123 Business Ave, New York, NY 10001', 'zoom' => 14]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
            'menus' => [
                ['name' => 'Main Menu', 'location' => 'main', 'items' => [['label' => 'Home', 'url' => '/'], ['label' => 'About', 'url' => '/about'], ['label' => 'Services', 'url' => '/services'], ['label' => 'Contact', 'url' => '/contact']]],
            ],
        ])],
        ['Landing Page', 'landing', 'Modern single-page landing with hero, features, CTA and newsletter', '/theme/assets/img/templates/landing.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Features', 'url' => '#features'], ['label' => 'Pricing', 'url' => '#pricing'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Launch Your Product', 'subtitle' => 'The fastest way to get your product in front of customers.', 'btn_text' => 'Get Started Free', 'btn_url' => '#cta', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Key Features', 'body' => '<div class="grid-3"><div class="feature-card"><h3>⚡ Fast</h3><p>Lightning-fast performance</p></div><div class="feature-card"><h3>🔒 Secure</h3><p>Enterprise-grade security</p></div><div class="feature-card"><h3>📊 Analytics</h3><p>Real-time insights</p></div></div>']],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Simple Pricing', 'plans' => [['name' => 'Free', 'price' => '$0', 'features' => ['Basic features', '1 project', 'Community support'], 'btn_text' => 'Get Started'], ['name' => 'Pro', 'price' => '$19', 'features' => ['All features', 'Unlimited projects', 'Priority support', 'API access'], 'btn_text' => 'Start Trial', 'featured' => true], ['name' => 'Enterprise', 'price' => '$99', 'features' => ['Everything in Pro', 'Custom integrations', 'Dedicated support', 'SLA'], 'btn_text' => 'Contact Sales']]]],
                    ['type' => 'newsletter', 'zone' => 'content', 'content' => ['title' => 'Stay Updated', 'subtitle' => 'Subscribe to our newsletter for the latest updates.', 'btn_text' => 'Subscribe']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
            'menus' => [
                ['name' => 'Main Menu', 'location' => 'main', 'items' => [['label' => 'Features', 'url' => '#features'], ['label' => 'Pricing', 'url' => '#pricing'], ['label' => 'Contact', 'url' => '#contact']]],
            ],
        ])],
        ['Radio Station', 'radio', 'Complete radio station website with player, schedule, DJ profiles, and now playing', '/theme/assets/img/templates/radio.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '📻 {{NAME}}', 'links' => [['label' => 'Listen Live', 'url' => '#listen'], ['label' => 'Schedule', 'url' => '#schedule'], ['label' => 'DJs', 'url' => '#djs'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '🎵 {{NAME}}', 'subtitle' => 'Your favorite music, 24/7.', 'btn_text' => 'Listen Live', 'btn_url' => '#listen', 'background' => 'gradient']],
                    ['type' => 'radio_player', 'zone' => 'content', 'content' => ['title' => 'Live Stream', 'stream_url' => 'http://stream.example.com:8000/live', 'stream_type' => 'audio/ogg']],
                    ['type' => 'now_playing', 'zone' => 'content', 'content' => ['title' => 'Now Playing', 'api_url' => '']],
                    ['type' => 'dj_status', 'zone' => 'content', 'content' => ['title' => 'Live DJ']],
                    ['type' => 'listener_count', 'zone' => 'content', 'content' => ['title' => 'Listeners']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Schedule', 'body' => '<div class="schedule-grid"><div class="schedule-item"><h4>Monday-Friday</h4><p>6AM-10AM: Morning Show<br>10AM-4PM: Midday Mix<br>4PM-8PM: Drive Time<br>8PM-12AM: Night Vibes</p></div><div class="schedule-item"><h4>Weekend</h4><p>8AM-12PM: Weekend Brunch<br>12PM-6PM: Weekend Warmup<br>6PM-12AM: Saturday Night</p></div></div>']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Meet Our DJs', 'body' => '<div class="dj-grid"><div class="dj-card"><h4>DJ Alpha</h4><p>House & Electronic</p></div><div class="dj-card"><h4>DJ Beatmaster</h4><p>Hip-Hop & R&B</p></div><div class="dj-card"><h4>DJ Melody</h4><p>Pop & Top 40</p></div></div>']],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Request a Song', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
            'menus' => [
                ['name' => 'Main Menu', 'location' => 'main', 'items' => [['label' => 'Listen Live', 'url' => '#listen'], ['label' => 'Schedule', 'url' => '#schedule'], ['label' => 'DJs', 'url' => '#djs']]],
            ],
        ])],
        ['Hosting Company', 'hosting', 'Web hosting company website with packages, order buttons, and server status', '/theme/assets/img/templates/hosting.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Hosting', 'url' => '#hosting'], ['label' => 'Support', 'url' => '#support'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Powerful Hosting for Your Business', 'subtitle' => 'Fast, secure, and reliable hosting solutions.', 'btn_text' => 'View Plans', 'btn_url' => '#hosting', 'background' => 'gradient']],
                    ['type' => 'hosting_packages', 'zone' => 'content', 'content' => ['title' => 'Our Hosting Plans', 'plans' => [['name' => 'Shared', 'price' => '$3.99/mo', 'features' => ['1 Website', '10 GB SSD', 'Free SSL', '1 Email'], 'btn_text' => 'Order Now'], ['name' => 'Business', 'price' => '$9.99/mo', 'features' => ['10 Websites', '50 GB SSD', 'Free SSL', 'Unlimited Email', 'Daily Backups'], 'btn_text' => 'Order Now', 'featured' => true], ['name' => 'VPS', 'price' => '$29.99/mo', 'features' => ['Unlimited Sites', '100 GB NVMe', 'Free SSL', 'Dedicated IP', 'Root Access'], 'btn_text' => 'Order Now']]]],
                    ['type' => 'server_status', 'zone' => 'content', 'content' => ['title' => 'Server Status']],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'What Our Clients Say', 'items' => [['name' => 'Sarah K.', 'role' => 'Web Developer', 'text' => 'Best hosting provider I have used!'], ['name' => 'Mike R.', 'role' => 'Blogger', 'text' => 'Incredible speed and uptime.']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Contact Support', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'subject', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
            'menus' => [
                ['name' => 'Main Menu', 'location' => 'main', 'items' => [['label' => 'Home', 'url' => '/'], ['label' => 'Hosting', 'url' => '#hosting'], ['label' => 'Support', 'url' => '#support']]],
            ],
        ])],
        ['Portfolio', 'portfolio', 'Creative portfolio for designers, photographers, and artists with gallery', '/theme/assets/img/templates/portfolio.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Work', 'url' => '#work'], ['label' => 'About', 'url' => '#about'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}}', 'subtitle' => 'Creative Designer & Developer', 'btn_text' => 'View My Work', 'btn_url' => '#work', 'background' => 'gradient']],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'My Work', 'images' => ['https://picsum.photos/seed/1/600/400', 'https://picsum.photos/seed/2/600/400', 'https://picsum.photos/seed/3/600/400', 'https://picsum.photos/seed/4/600/400', 'https://picsum.photos/seed/5/600/400', 'https://picsum.photos/seed/6/600/400']]],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'About Me', 'body' => '<p>I am a passionate designer with over 5 years of experience creating beautiful digital experiences. I specialize in web design, branding, and UI/UX design.</p>']],
                    ['type' => 'social_media', 'zone' => 'content', 'content' => ['title' => 'Follow Me', 'platforms' => ['twitter', 'instagram', 'github', 'linkedin']]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Get In Touch', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Restaurant', 'restaurant', 'Restaurant website with menu, reservations, and location', '/theme/assets/img/templates/restaurant.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Menu', 'url' => '#menu'], ['label' => 'About', 'url' => '#about'], ['label' => 'Reservations', 'url' => '#reservations'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Welcome to {{NAME}}', 'subtitle' => 'Exquisite dining experience', 'btn_text' => 'View Menu', 'btn_url' => '#menu', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Menu', 'body' => '<div class="menu-grid"><div class="menu-category"><h3>Appetizers</h3><p>Bruschetta - $12<br>Calamari - $14<br>Soup of the Day - $8</p></div><div class="menu-category"><h3>Main Courses</h3><p>Grilled Salmon - $28<br>Filet Mignon - $36<br>Pasta Primavera - $18</p></div><div class="menu-category"><h3>Desserts</h3><p>Tiramisu - $10<br>Cheesecake - $9<br>Gelato - $7</p></div></div>']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'About Us', 'body' => '<p>Established in 2015, {{NAME}} has been serving the finest cuisine made from locally sourced ingredients. Our award-winning chefs create memorable dining experiences.</p>']],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Make a Reservation', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'date', 'guests', 'message']]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => '456 Main Street, New York, NY 10001', 'zoom' => 15]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Fitness & Gym', 'fitness', 'Gym and fitness center website with classes, trainers, and membership', '/theme/assets/img/templates/fitness.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '💪 {{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Classes', 'url' => '#classes'], ['label' => 'Trainers', 'url' => '#trainers'], ['label' => 'Membership', 'url' => '#pricing'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Transform Your Body', 'subtitle' => 'Join {{NAME}} and achieve your fitness goals.', 'btn_text' => 'Start Free Trial', 'btn_url' => '#pricing', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Classes', 'body' => '<div class="grid-3"><div class="class-card"><h3>Yoga</h3><p>Mon/Wed/Fri 7AM</p></div><div class="class-card"><h3>HIIT</h3><p>Daily 6AM & 6PM</p></div><div class="class-card"><h3>Spinning</h3><p>Tue/Thu 5:30PM</p></div></div>']],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Membership Plans', 'plans' => [['name' => 'Basic', 'price' => '$29/mo', 'features' => ['Gym Access', 'Locker Room', 'Free Weights'], 'btn_text' => 'Join Now'], ['name' => 'Premium', 'price' => '$59/mo', 'features' => ['All Classes', 'Personal Trainer', 'Sauna', 'Nutrition Plan'], 'btn_text' => 'Join Now', 'featured' => true], ['name' => 'Annual', 'price' => '$299/yr', 'features' => ['Everything Premium', '2 Free PT Sessions', 'Merch Pack'], 'btn_text' => 'Join Now']]]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Blog & Magazine', 'blog', 'Modern blog and magazine layout with featured posts and categories', '/theme/assets/img/templates/blog.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Technology', 'url' => '/category/tech'], ['label' => 'Lifestyle', 'url' => '/category/lifestyle'], ['label' => 'Contact', 'url' => '/contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}}', 'subtitle' => 'Discover stories, thinking, and expertise.', 'btn_text' => 'Read Latest', 'btn_url' => '#latest', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Featured Posts', 'body' => '<div class="blog-grid"><article class="blog-card"><h3>The Future of AI</h3><p>Exploring artificial intelligence trends in 2026.</p></article><article class="blog-card"><h3>Healthy Living Tips</h3><p>10 simple habits for a better life.</p></article><article class="blog-card"><h3>Web Design Trends</h3><p>What is shaping modern web design.</p></article></div>']],
                    ['type' => 'newsletter', 'zone' => 'content', 'content' => ['title' => 'Subscribe', 'subtitle' => 'Get the latest posts delivered to your inbox.', 'btn_text' => 'Subscribe']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['E-commerce', 'ecommerce', 'Online store front with product grid, categories, and shopping cart', '/theme/assets/img/templates/ecommerce.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🛍️ {{NAME}}', 'links' => [['label' => 'Shop', 'url' => '#shop'], ['label' => 'Categories', 'url' => '#categories'], ['label' => 'Cart', 'url' => '#cart'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Summer Sale!', 'subtitle' => 'Up to 50% off on selected items.', 'btn_text' => 'Shop Now', 'btn_url' => '#shop', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Featured Products', 'body' => '<div class="product-grid"><div class="product-card"><h3>Product 1</h3><p>$29.99</p><a href="#" class="btn">Add to Cart</a></div><div class="product-card"><h3>Product 2</h3><p>$49.99</p><a href="#" class="btn">Add to Cart</a></div><div class="product-card"><h3>Product 3</h3><p>$19.99</p><a href="#" class="btn">Add to Cart</a></div></div>']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Personal Brand', 'personal', 'Personal brand and resume website for professionals', '/theme/assets/img/templates/personal.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'About', 'url' => '#about'], ['label' => 'Experience', 'url' => '#experience'], ['label' => 'Skills', 'url' => '#skills'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Hi, I am {{NAME}}', 'subtitle' => 'Full-Stack Developer & Designer', 'btn_text' => 'Download CV', 'btn_url' => '#', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'About Me', 'body' => '<p>Experienced full-stack developer with 8+ years building web applications. Passionate about clean code and great user experiences.</p>']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Experience', 'body' => '<div class="timeline"><div class="timeline-item"><h4>Senior Developer</h4><p>Tech Corp - 2022-Present</p></div><div class="timeline-item"><h4>Full-Stack Developer</h4><p>StartupX - 2019-2022</p></div><div class="timeline-item"><h4>Junior Developer</h4><p>WebCo - 2016-2019</p></div></div>']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => 'Frontend', 'text' => 'React, Vue, TypeScript'], ['title' => 'Backend', 'text' => 'Node.js, PHP, Python'], ['title' => 'DevOps', 'text' => 'Docker, AWS, CI/CD']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Get In Touch', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Event Conference', 'event', 'Conference and event website with schedule, speakers, and registration', '/theme/assets/img/templates/event.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Schedule', 'url' => '#schedule'], ['label' => 'Speakers', 'url' => '#speakers'], ['label' => 'Register', 'url' => '#register'], ['label' => 'Venue', 'url' => '#venue']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}} 2026', 'subtitle' => 'The biggest tech conference of the year.', 'btn_text' => 'Register Now', 'btn_url' => '#register', 'background' => 'gradient']],
                    ['type' => 'countdown', 'zone' => 'content', 'content' => ['title' => 'Countdown', 'target_date' => '2026-12-31 23:59:59']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Schedule', 'body' => '<div class="schedule-list"><div class="schedule-item"><time>9:00 AM</time><h4>Keynote: Future of Tech</h4></div><div class="schedule-item"><time>10:30 AM</time><h4>AI Workshop</h4></div><div class="schedule-item"><time>1:00 PM</time><h4>Panel Discussion</h4></div></div>']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Speakers', 'body' => '<div class="speaker-grid"><div class="speaker-card"><h4>Jane Doe</h4><p>CEO, TechCorp</p></div><div class="speaker-card"><h4>John Smith</h4><p>CTO, StartupX</p></div><div class="speaker-card"><h4>Dr. Alice Wang</h4><p>AI Researcher</p></div></div>']],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Tickets', 'plans' => [['name' => 'Early Bird', 'price' => '$199', 'features' => ['Full Access', 'Workshop', 'Lunch'], 'btn_text' => 'Buy Now'], ['name' => 'Standard', 'price' => '$349', 'features' => ['Full Access', 'Workshops', 'Lunch', 'After Party'], 'btn_text' => 'Buy Now', 'featured' => true], ['name' => 'VIP', 'price' => '$599', 'features' => ['Everything', 'VIP Seating', 'Meet Speakers', 'Hotel'], 'btn_text' => 'Buy Now']]]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => 'Convention Center, 1000 Event Drive', 'zoom' => 15]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['SaaS Product', 'saas', 'SaaS product website with features, demo, and pricing tiers', '/theme/assets/img/templates/saas.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Features', 'url' => '#features'], ['label' => 'Pricing', 'url' => '#pricing'], ['label' => 'Demo', 'url' => '#demo'], ['label' => 'Login', 'url' => '#login']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'The Future of Work is Here', 'subtitle' => 'Streamline your workflow with {{NAME}}.', 'btn_text' => 'Start Free Trial', 'btn_url' => '#pricing', 'background' => 'gradient']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => 'Collaboration', 'text' => 'Work together in real-time'], ['title' => 'Automation', 'text' => 'Automate repetitive tasks'], ['title' => 'Analytics', 'text' => 'Data-driven insights']]]],
                    ['type' => 'video', 'zone' => 'content', 'content' => ['title' => 'See It In Action', 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'autoplay' => false]],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'Trusted by Teams', 'items' => [['name' => 'Sarah L.', 'role' => 'Product Manager', 'text' => 'Game changer for our team!'], ['name' => 'Tom B.', 'role' => 'Developer', 'text' => 'The API is incredible.']]]],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Simple Pricing', 'plans' => [['name' => 'Starter', 'price' => '$9/mo', 'features' => ['5 Users', '10 GB Storage', 'Basic Support'], 'btn_text' => 'Start Free'], ['name' => 'Professional', 'price' => '$49/mo', 'features' => ['50 Users', '100 GB', 'Priority Support', 'API Access'], 'btn_text' => 'Start Free', 'featured' => true], ['name' => 'Enterprise', 'price' => '$199/mo', 'features' => ['Unlimited Users', '1 TB Storage', 'Dedicated Support', 'Custom Integration'], 'btn_text' => 'Contact Sales']]]],
                    ['type' => 'newsletter', 'zone' => 'content', 'content' => ['title' => 'Stay Updated', 'subtitle' => 'Product updates and tips.', 'btn_text' => 'Subscribe']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Educational', 'educational', 'Online learning platform with courses and instructors', '/theme/assets/img/templates/educational.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🎓 {{NAME}}', 'links' => [['label' => 'Courses', 'url' => '#courses'], ['label' => 'Instructors', 'url' => '#instructors'], ['label' => 'Pricing', 'url' => '#pricing'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Learn Without Limits', 'subtitle' => 'Expert-led courses to advance your career.', 'btn_text' => 'Browse Courses', 'btn_url' => '#courses', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Popular Courses', 'body' => '<div class="course-grid"><div class="course-card"><h4>Web Development</h4><p>12 weeks - $499</p></div><div class="course-card"><h4>Data Science</h4><p>10 weeks - $599</p></div><div class="course-card"><h4>UI/UX Design</h4><p>8 weeks - $399</p></div></div>']],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'Student Success', 'items' => [['name' => 'Maria G.', 'role' => 'Graduate', 'text' => 'Changed my career trajectory!'], ['name' => 'Alex K.', 'role' => 'Student', 'text' => 'Best learning platform.']]]],
                    ['type' => 'newsletter', 'zone' => 'content', 'content' => ['title' => 'Get Updates', 'subtitle' => 'New courses and promotions.', 'btn_text' => 'Subscribe']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Photography', 'photography', 'Photography portfolio with image galleries and booking', '/theme/assets/img/templates/photography.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '📷 {{NAME}}', 'links' => [['label' => 'Portfolio', 'url' => '#portfolio'], ['label' => 'About', 'url' => '#about'], ['label' => 'Pricing', 'url' => '#pricing'], ['label' => 'Book', 'url' => '#book']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}} Photography', 'subtitle' => 'Capturing moments that last forever.', 'btn_text' => 'View Portfolio', 'btn_url' => '#portfolio', 'background' => 'image']],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Portfolio', 'images' => ['https://picsum.photos/seed/p1/800/600', 'https://picsum.photos/seed/p2/800/600', 'https://picsum.photos/seed/p3/800/600', 'https://picsum.photos/seed/p4/800/600', 'https://picsum.photos/seed/p5/800/600', 'https://picsum.photos/seed/p6/800/600']]],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Packages', 'plans' => [['name' => 'Portrait', 'price' => '$199', 'features' => ['1 Hour Session', '10 Edited Photos', 'Online Gallery'], 'btn_text' => 'Book'], ['name' => 'Wedding', 'price' => '$999', 'features' => ['6 Hours', '300+ Photos', 'Online Gallery', 'Print Rights'], 'btn_text' => 'Book', 'featured' => true], ['name' => 'Event', 'price' => '$499', 'features' => ['3 Hours', '100+ Photos', 'Online Gallery'], 'btn_text' => 'Book']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Book a Session', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'date', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Gaming Community', 'gaming', 'Gaming community website with server status, forums, and team', '/theme/assets/img/templates/gaming.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🎮 {{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Servers', 'url' => '#servers'], ['label' => 'Team', 'url' => '#team'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Welcome to {{NAME}}', 'subtitle' => 'Join the ultimate gaming community.', 'btn_text' => 'Join Our Discord', 'btn_url' => '#discord', 'background' => 'gradient']],
                    ['type' => 'game_server_status', 'zone' => 'content', 'content' => ['title' => 'Game Servers', 'servers' => [['name' => 'Minecraft', 'status' => 'online', 'players' => '24/100'], ['name' => 'CS:GO', 'status' => 'online', 'players' => '12/32'], ['name' => 'Valheim', 'status' => 'offline', 'players' => '0/10']]]],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Team', 'body' => '<div class="team-grid"><div class="team-card"><h4>Admin1</h4><p>Founder</p></div><div class="team-card"><h4>Mod1</h4><p>Moderator</p></div><div class="team-card"><h4>Builder1</h4><p>Map Builder</p></div></div>']],
                    ['type' => 'discord', 'zone' => 'content', 'content' => ['title' => 'Join Discord', 'invite_code' => 'your-discord-invite', 'server_id' => '']],
                    ['type' => 'support_tickets', 'zone' => 'content', 'content' => ['title' => 'Support']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Real Estate', 'realestate', 'Real estate website with property listings and agent info', '/theme/assets/img/templates/realestate.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🏠 {{NAME}}', 'links' => [['label' => 'Properties', 'url' => '#properties'], ['label' => 'Agents', 'url' => '#agents'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Find Your Dream Home', 'subtitle' => 'Browse thousands of properties.', 'btn_text' => 'Search Properties', 'btn_url' => '#properties', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Featured Properties', 'body' => '<div class="property-grid"><div class="property-card"><h4>Modern Downtown Apt</h4><p>$450,000 - 2BR/2BA</p></div><div class="property-card"><h4>Suburban Family Home</h4><p>$650,000 - 4BR/3BA</p></div><div class="property-card"><h4>Luxury Penthouse</h4><p>$1,200,000 - 3BR/3BA</p></div></div>']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Agents', 'body' => '<div class="agent-grid"><div class="agent-card"><h4>John Smith</h4><p>Senior Agent</p></div><div class="agent-card"><h4>Jane Doe</h4><p>Luxury Specialist</p></div></div>']],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Schedule a Viewing', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'message']]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => '500 Realty Ave, New York, NY', 'zoom' => 12]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Healthcare', 'healthcare', 'Medical clinic website with services, doctors, and appointment booking', '/theme/assets/img/templates/healthcare.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🏥 {{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Services', 'url' => '#services'], ['label' => 'Doctors', 'url' => '#doctors'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Your Health Matters', 'subtitle' => 'Comprehensive healthcare for the whole family.', 'btn_text' => 'Book Appointment', 'btn_url' => '#appointment', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Services', 'body' => '<div class="grid-3"><div class="service-card"><h4>General Checkup</h4></div><div class="service-card"><h4>Dental Care</h4></div><div class="service-card"><h4>Eye Examination</h4></div></div>']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => 'Experienced Doctors', 'text' => 'Board-certified physicians'], ['title' => 'Modern Facilities', 'text' => 'Latest medical equipment'], ['title' => 'Insurance Accepted', 'text' => 'Most major plans']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Book Appointment', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'date', 'message']]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => '789 Health Blvd, Medical District', 'zoom' => 14]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Church', 'church', 'Church website with sermons, events, and donation', '/theme/assets/img/templates/church.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '⛪ {{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Sermons', 'url' => '#sermons'], ['label' => 'Events', 'url' => '#events'], ['label' => 'Give', 'url' => '#give'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Welcome to {{NAME}}', 'subtitle' => 'A place of faith, hope, and love.', 'btn_text' => 'Join Us Sunday', 'btn_url' => '#visit', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Service Times', 'body' => '<div class="times"><p>Sunday: 9:00 AM & 11:00 AM<br>Wednesday: 7:00 PM<br>Saturday: 6:00 PM</p></div>']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Upcoming Events', 'body' => '<div class="event-list"><div class="event"><h4>Community Outreach</h4><p>June 25, 2026</p></div><div class="event"><h4>Youth Group</h4><p>Every Friday 7PM</p></div></div>']],
                    ['type' => 'button', 'zone' => 'content', 'content' => ['text' => 'Give Online', 'url' => '#give', 'style' => 'primary', 'full_width' => false]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Nonprofit', 'nonprofit', 'Nonprofit organization website with mission, impact, and donate', '/theme/assets/img/templates/nonprofit.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🤝 {{NAME}}', 'links' => [['label' => 'Mission', 'url' => '#mission'], ['label' => 'Impact', 'url' => '#impact'], ['label' => 'Get Involved', 'url' => '#involved'], ['label' => 'Donate', 'url' => '#donate']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Making a Difference', 'subtitle' => 'Together we can change lives.', 'btn_text' => 'Donate Now', 'btn_url' => '#donate', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Mission', 'body' => '<p>We are dedicated to improving lives through education, healthcare, and community development programs.</p>']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => '10,000+', 'text' => 'Lives Impacted'], ['title' => '50+', 'text' => 'Communities Served'], ['title' => '200+', 'text' => 'Volunteers Active']]]],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Our Impact', 'images' => ['https://picsum.photos/seed/n1/600/400', 'https://picsum.photos/seed/n2/600/400', 'https://picsum.photos/seed/n3/600/400', 'https://picsum.photos/seed/n4/600/400']]],
                    ['type' => 'newsletter', 'zone' => 'content', 'content' => ['title' => 'Join Our Newsletter', 'subtitle' => 'Stay updated on our impact.', 'btn_text' => 'Subscribe']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Streamer', 'streamer', 'Live streamer website with Twitch integration, schedule, and donations', '/theme/assets/img/templates/streamer.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🎮 {{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Schedule', 'url' => '#schedule'], ['label' => 'About', 'url' => '#about'], ['label' => 'Donate', 'url' => '#donate']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}}', 'subtitle' => 'Live on Twitch!', 'btn_text' => 'Follow on Twitch', 'btn_url' => '#twitch', 'background' => 'gradient']],
                    ['type' => 'twitch', 'zone' => 'content', 'content' => ['title' => 'Live Stream', 'channel' => 'yourchannel', 'layout' => 'embed']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Stream Schedule', 'body' => '<div class="schedule-grid"><div class="schedule-item"><h4>Monday</h4><p>8PM - 11PM EST</p></div><div class="schedule-item"><h4>Wednesday</h4><p>8PM - 11PM EST</p></div><div class="schedule-item"><h4>Friday</h4><p>9PM - 1AM EST</p></div></div>']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'About Me', 'body' => '<p>Variety streamer playing FPS, RPG, and indie games. Come hang out!</p>']],
                    ['type' => 'button', 'zone' => 'content', 'content' => ['text' => 'Donate via PayPal', 'url' => '#donate', 'style' => 'primary', 'full_width' => false]],
                    ['type' => 'social_media', 'zone' => 'content', 'content' => ['title' => 'Follow Me', 'platforms' => ['twitch', 'twitter', 'discord', 'youtube', 'instagram']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Construction', 'construction', 'Construction company website with projects, services, and estimates', '/theme/assets/img/templates/construction.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🏗️ {{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Services', 'url' => '#services'], ['label' => 'Projects', 'url' => '#projects'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Building Your Vision', 'subtitle' => 'Quality construction since 2000.', 'btn_text' => 'Get a Quote', 'btn_url' => '#contact', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Services', 'body' => '<div class="grid-3"><div class="service-card"><h4>Residential</h4><p>Custom homes and renovations</p></div><div class="service-card"><h4>Commercial</h4><p>Office buildings and retail</p></div><div class="service-card"><h4>Industrial</h4><p>Warehouses and factories</p></div></div>']],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Our Projects', 'images' => ['https://picsum.photos/seed/c1/600/400', 'https://picsum.photos/seed/c2/600/400', 'https://picsum.photos/seed/c3/600/400', 'https://picsum.photos/seed/c4/600/400']]],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'Client Reviews', 'items' => [['name' => 'Robert M.', 'role' => 'Homeowner', 'text' => 'Excellent craftsmanship!'], ['name' => 'Lisa K.', 'role' => 'Business Owner', 'text' => 'Completed on time and budget.']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Request a Quote', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'project_type', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Agency', 'agency', 'Digital agency website with services, case studies, and team', '/theme/assets/img/templates/agency.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Work', 'url' => '#work'], ['label' => 'Services', 'url' => '#services'], ['label' => 'Team', 'url' => '#team'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'We Build Digital Excellence', 'subtitle' => 'Award-winning digital agency.', 'btn_text' => 'View Our Work', 'btn_url' => '#work', 'background' => 'gradient']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 4, 'items' => [['title' => 'Strategy', 'text' => 'Data-driven planning'], ['title' => 'Design', 'text' => 'Beautiful interfaces'], ['title' => 'Development', 'text' => 'Cutting-edge code'], ['title' => 'Marketing', 'text' => 'Growth focused']]]],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Case Studies', 'images' => ['https://picsum.photos/seed/a1/600/400', 'https://picsum.photos/seed/a2/600/400', 'https://picsum.photos/seed/a3/600/400']]],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Team', 'body' => '<div class="team-grid"><div class="team-card"><h4>Sarah Johnson</h4><p>CEO & Founder</p></div><div class="team-card"><h4>Mike Chen</h4><p>CTO</p></div><div class="team-card"><h4>Emily Davis</h4><p>Creative Director</p></div></div>']],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Start a Project', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'company', 'budget', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Music Band', 'band', 'Music band website with tour dates, music player, and merch', '/theme/assets/img/templates/band.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🎸 {{NAME}}', 'links' => [['label' => 'Music', 'url' => '#music'], ['label' => 'Tour', 'url' => '#tour'], ['label' => 'Band', 'url' => '#band'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}}', 'subtitle' => 'New album out now!', 'btn_text' => 'Listen Now', 'btn_url' => '#music', 'background' => 'gradient']],
                    ['type' => 'youtube', 'zone' => 'content', 'content' => ['title' => 'Latest Music Video', 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Tour Dates', 'body' => '<div class="tour-list"><div class="tour-date"><span>Jun 25</span><span>New York, NY</span><a href="#">Tickets</a></div><div class="tour-date"><span>Jul 10</span><span>Los Angeles, CA</span><a href="#">Tickets</a></div><div class="tour-date"><span>Jul 24</span><span>Chicago, IL</span><a href="#">Tickets</a></div></div>']],
                    ['type' => 'social_media', 'zone' => 'content', 'content' => ['title' => 'Follow Us', 'platforms' => ['spotify', 'apple-music', 'youtube', 'instagram', 'twitter']]],
                    ['type' => 'newsletter', 'zone' => 'content', 'content' => ['title' => 'Join the Fan Club', 'subtitle' => 'Exclusive content and presale codes.', 'btn_text' => 'Join']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Wedding', 'wedding', 'Wedding website with event details, gallery, and RSVP', '/theme/assets/img/templates/wedding.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '💑 {{NAME}}', 'links' => [['label' => 'Our Story', 'url' => '#story'], ['label' => 'Event', 'url' => '#event'], ['label' => 'Gallery', 'url' => '#gallery'], ['label' => 'RSVP', 'url' => '#rsvp']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}}', 'subtitle' => 'We are getting married!', 'btn_text' => 'RSVP Now', 'btn_url' => '#rsvp', 'background' => 'image']],
                    ['type' => 'countdown', 'zone' => 'content', 'content' => ['title' => 'Countdown to the Big Day', 'target_date' => '2026-09-15 15:00:00']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Story', 'body' => '<p>We met in 2018 and have been inseparable ever since. Join us as we celebrate our love!</p>']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Event Details', 'body' => '<div class="event-details"><p><strong>Ceremony:</strong> 3:00 PM<br><strong>Reception:</strong> 6:00 PM<br><strong>Venue:</strong> The Grand Ballroom<br><strong>Dress Code:</strong> Black Tie Optional</p></div>']],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Photo Gallery', 'images' => ['https://picsum.photos/seed/w1/600/400', 'https://picsum.photos/seed/w2/600/400', 'https://picsum.photos/seed/w3/600/400', 'https://picsum.photos/seed/w4/600/400']]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'RSVP', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'guests', 'message']]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => 'Grand Ballroom, 1000 Celebration Ave', 'zoom' => 15]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => 'Made with love &copy; {{YEAR}}']],
                ]],
            ],
        ])],
        ['Tech Startup', 'startup', 'Modern tech startup landing page with app showcase', '/theme/assets/img/templates/startup.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Product', 'url' => '#product'], ['label' => 'Features', 'url' => '#features'], ['label' => 'Pricing', 'url' => '#pricing'], ['label' => 'Demo', 'url' => '#demo']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'The Next Generation of Productivity', 'subtitle' => 'Built for modern teams.', 'btn_text' => 'Get Early Access', 'btn_url' => '#signup', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Why Choose Us', 'body' => '<div class="grid-3"><div class="feature-card"><h4>🚀 Lightning Fast</h4><p>Optimized for speed</p></div><div class="feature-card"><h4>🔒 Enterprise Security</h4><p>Bank-level encryption</p></div><div class="feature-card"><h4>📱 Mobile First</h4><p>Works on any device</p></div></div>']],
                    ['type' => 'video', 'zone' => 'content', 'content' => ['title' => 'Product Demo', 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'autoplay' => false]],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Pricing', 'plans' => [['name' => 'Startup', 'price' => '$19/mo', 'features' => ['10 Users', '5 GB Storage', 'Basic Analytics'], 'btn_text' => 'Start Free'], ['name' => 'Growth', 'price' => '$79/mo', 'features' => ['50 Users', '50 GB', 'Advanced Analytics', 'API Access'], 'btn_text' => 'Start Free', 'featured' => true], ['name' => 'Scale', 'price' => '$199/mo', 'features' => ['Unlimited', '500 GB', 'Custom Analytics', 'Dedicated Support'], 'btn_text' => 'Contact']]]],
                    ['type' => 'newsletter', 'zone' => 'content', 'content' => ['title' => 'Get Early Access', 'subtitle' => 'Be the first to know when we launch.', 'btn_text' => 'Notify Me']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Coffee Shop', 'coffee', 'Coffee shop website with menu, location, and online ordering', '/theme/assets/img/templates/coffee.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '☕ {{NAME}}', 'links' => [['label' => 'Menu', 'url' => '#menu'], ['label' => 'About', 'url' => '#about'], ['label' => 'Location', 'url' => '#location'], ['label' => 'Order', 'url' => '#order']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}}', 'subtitle' => 'Crafted with love, served with passion.', 'btn_text' => 'View Menu', 'btn_url' => '#menu', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Menu', 'body' => '<div class="menu-grid"><div class="menu-item"><h4>Espresso</h4><p>$3.50</p></div><div class="menu-item"><h4>Cappuccino</h4><p>$4.50</p></div><div class="menu-item"><h4>Latte</h4><p>$4.75</p></div><div class="menu-item"><h4>Cold Brew</h4><p>$4.25</p></div><div class="menu-item"><h4>Mocha</h4><p>$5.00</p></div><div class="menu-item"><h4>Pastry</h4><p>$3.00</p></div></div>']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => 'Fresh Beans', 'text' => 'Sourced globally'], ['title' => 'Expert Baristas', 'text' => 'Certified professionals'], ['title' => 'Cozy Atmosphere', 'text' => 'Free WiFi']]]],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Our Shop', 'images' => ['https://picsum.photos/seed/co1/600/400', 'https://picsum.photos/seed/co2/600/400', 'https://picsum.photos/seed/co3/600/400']]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => '321 Coffee Lane, Downtown', 'zoom' => 16]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Mobile App Landing', 'mobileapp', 'Mobile app landing page with app store buttons and screenshots', '/theme/assets/img/templates/mobileapp.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '📱 {{NAME}}', 'links' => [['label' => 'Features', 'url' => '#features'], ['label' => 'Reviews', 'url' => '#reviews'], ['label' => 'Download', 'url' => '#download']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => '{{NAME}}', 'subtitle' => 'The app that changes everything.', 'btn_text' => 'Download on App Store', 'btn_url' => '#download', 'background' => 'gradient']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => 'Fast', 'text' => 'Optimized performance'], ['title' => 'Simple', 'text' => 'Beautiful interface'], ['title' => 'Secure', 'text' => 'Your data protected']]]],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'App Screenshots', 'images' => ['https://picsum.photos/seed/m1/300/600', 'https://picsum.photos/seed/m2/300/600', 'https://picsum.photos/seed/m3/300/600']]],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'User Reviews', 'items' => [['name' => 'App Store User', 'role' => '★★★★★', 'text' => 'Best app ever!'], ['name' => 'App Store User', 'role' => '★★★★★', 'text' => 'Game changer!']]]],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Download Now', 'body' => '<div class="store-badges"><a href="#" class="store-badge">App Store</a><a href="#" class="store-badge">Google Play</a></div>']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Law Firm', 'lawfirm', 'Law firm website with practice areas, attorneys, and consultation booking', '/theme/assets/img/templates/lawfirm.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '⚖️ {{NAME}}', 'links' => [['label' => 'Home', 'url' => '/'], ['label' => 'Practice Areas', 'url' => '#areas'], ['label' => 'Attorneys', 'url' => '#attorneys'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Expert Legal Counsel', 'subtitle' => 'Protecting your rights since 1995.', 'btn_text' => 'Free Consultation', 'btn_url' => '#contact', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Practice Areas', 'body' => '<div class="grid-3"><div class="practice-card"><h4>Family Law</h4></div><div class="practice-card"><h4>Criminal Defense</h4></div><div class="practice-card"><h4>Business Law</h4></div><div class="practice-card"><h4>Real Estate</h4></div><div class="practice-card"><h4>Personal Injury</h4></div><div class="practice-card"><h4>Estate Planning</h4></div></div>']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => '50+ Years', 'text' => 'Combined experience'], ['title' => '10,000+', 'text' => 'Cases won'], ['title' => '99%', 'text' => 'Client satisfaction']]]],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'Client Testimonials', 'items' => [['name' => 'Client A.', 'role' => '','text' => 'They fought for me and won!'], ['name' => 'Client B.', 'role' => '','text' => 'Highly professional team.']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Schedule a Consultation', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'practice_area', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Day Spa', 'spa', 'Spa and wellness website with treatments, pricing, and booking', '/theme/assets/img/templates/spa.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🧖 {{NAME}}', 'links' => [['label' => 'Treatments', 'url' => '#treatments'], ['label' => 'Packages', 'url' => '#packages'], ['label' => 'Gallery', 'url' => '#gallery'], ['label' => 'Book', 'url' => '#book']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Escape to {{NAME}}', 'subtitle' => 'Relax, rejuvenate, refresh.', 'btn_text' => 'Book Now', 'btn_url' => '#book', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Treatments', 'body' => '<div class="grid-3"><div class="treatment-card"><h4>Swedish Massage</h4><p>60 min - $89</p></div><div class="treatment-card"><h4>Facial</h4><p>45 min - $79</p></div><div class="treatment-card"><h4>Body Wrap</h4><p>75 min - $119</p></div></div>']],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Spa Packages', 'plans' => [['name' => 'Quick Escape', 'price' => '$149', 'features' => ['1 Treatment', 'Sauna Access', 'Herbal Tea'], 'btn_text' => 'Book'], ['name' => 'Day Retreat', 'price' => '$299', 'features' => ['3 Treatments', 'Lunch', 'Pool Access', 'Sauna'], 'btn_text' => 'Book', 'featured' => true], ['name' => 'Couples', 'price' => '$549', 'features' => ['2 Treatments Each', 'Champagne', 'Private Suite'], 'btn_text' => 'Book']]]],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Gallery', 'images' => ['https://picsum.photos/seed/s1/600/400', 'https://picsum.photos/seed/s2/600/400', 'https://picsum.photos/seed/s3/600/400']]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Book Appointment', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'date', 'treatment', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Pet Services', 'petservices', 'Pet grooming and boarding website with services and booking', '/theme/assets/img/templates/pet.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🐾 {{NAME}}', 'links' => [['label' => 'Services', 'url' => '#services'], ['label' => 'Gallery', 'url' => '#gallery'], ['label' => 'Book', 'url' => '#book'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Pamper Your Pet at {{NAME}}', 'subtitle' => 'Professional pet grooming and care.', 'btn_text' => 'Book Appointment', 'btn_url' => '#book', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Services', 'body' => '<div class="grid-3"><div class="service-card"><h4>Full Grooming</h4><p>$55+</p></div><div class="service-card"><h4>Bath & Brush</h4><p>$35</p></div><div class="service-card"><h4>Pet Boarding</h4><p>$45/night</p></div><div class="service-card"><h4>Nail Trim</h4><p>$15</p></div><div class="service-card"><h4>Teeth Cleaning</h4><p>$25</p></div><div class="service-card"><h4>Pet Taxi</h4><p>$20</p></div></div>']],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Happy Pets', 'images' => ['https://picsum.photos/seed/pet1/600/400', 'https://picsum.photos/seed/pet2/600/400', 'https://picsum.photos/seed/pet3/600/400', 'https://picsum.photos/seed/pet4/600/400']]],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'What Pet Parents Say', 'items' => [['name' => 'Max Owner', 'role' => '', 'text' => 'My dog loves this place!'], ['name' => 'Bella Mom', 'role' => '', 'text' => 'Best grooming in town!']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Book Now', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'pet_type', 'service', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Travel Blog', 'travel', 'Travel blog with destinations, photo galleries, and trip planning', '/theme/assets/img/templates/travel.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '✈️ {{NAME}}', 'links' => [['label' => 'Destinations', 'url' => '#destinations'], ['label' => 'Blog', 'url' => '#blog'], ['label' => 'Gallery', 'url' => '#gallery'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Explore the World with {{NAME}}', 'subtitle' => 'Travel tips, guides, and inspiration.', 'btn_text' => 'Start Exploring', 'btn_url' => '#destinations', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Popular Destinations', 'body' => '<div class="destination-grid"><div class="dest-card"><h4>Paris, France</h4></div><div class="dest-card"><h4>Tokyo, Japan</h4></div><div class="dest-card"><h4>Bali, Indonesia</h4></div><div class="dest-card"><h4>Santorini, Greece</h4></div></div>']],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Travel Gallery', 'images' => ['https://picsum.photos/seed/t1/600/400', 'https://picsum.photos/seed/t2/600/400', 'https://picsum.photos/seed/t3/600/400', 'https://picsum.photos/seed/t4/600/400', 'https://picsum.photos/seed/t5/600/400', 'https://picsum.photos/seed/t6/600/400']]],
                    ['type' => 'newsletter', 'zone' => 'content', 'content' => ['title' => 'Join Our Travel Community', 'subtitle' => 'Get travel tips and exclusive deals.', 'btn_text' => 'Subscribe']],
                    ['type' => 'social_media', 'zone' => 'content', 'content' => ['title' => 'Follow Our Journey', 'platforms' => ['instagram', 'youtube', 'tiktok', 'pinterest']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Car Dealership', 'cars', 'Car dealership website with inventory, financing, and service', '/theme/assets/img/templates/cars.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '🚗 {{NAME}}', 'links' => [['label' => 'Inventory', 'url' => '#inventory'], ['label' => 'Financing', 'url' => '#financing'], ['label' => 'Service', 'url' => '#service'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Find Your Dream Car', 'subtitle' => 'Hundreds of vehicles in stock.', 'btn_text' => 'Browse Inventory', 'btn_url' => '#inventory', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Featured Vehicles', 'body' => '<div class="vehicle-grid"><div class="vehicle-card"><h4>2024 Tesla Model 3</h4><p>$45,000</p></div><div class="vehicle-card"><h4>2024 BMW X5</h4><p>$62,000</p></div><div class="vehicle-card"><h4>2024 Honda Accord</h4><p>$28,000</p></div></div>']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => '200+ Vehicles', 'text' => 'In stock'], ['title' => '0% APR', 'text' => 'Financing available'], ['title' => 'Certified', 'text' => 'Inspected & warranted']]]],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'Customer Reviews', 'items' => [['name' => 'David L.', 'role' => '', 'text' => 'Best car buying experience!'], ['name' => 'Amanda T.', 'role' => '', 'text' => 'Fair prices and great service.']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Schedule Test Drive', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'message']]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => '555 Auto Mall Drive', 'zoom' => 14]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Online Course', 'onlinecourse', 'Online course platform with curriculum and enrollment', '/theme/assets/img/templates/onlinecourse.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '📚 {{NAME}}', 'links' => [['label' => 'Curriculum', 'url' => '#curriculum'], ['label' => 'Instructor', 'url' => '#instructor'], ['label' => 'Enroll', 'url' => '#enroll'], ['label' => 'FAQ', 'url' => '#faq']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Master New Skills', 'subtitle' => 'Expert-led online course.', 'btn_text' => 'Enroll Now', 'btn_url' => '#enroll', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Course Curriculum', 'body' => '<div class="curriculum"><div class="module"><h4>Module 1: Introduction</h4><p>Getting started with the basics</p></div><div class="module"><h4>Module 2: Core Concepts</h4><p>Deep dive into fundamentals</p></div><div class="module"><h4>Module 3: Advanced Topics</h4><p>Master advanced techniques</p></div><div class="module"><h4>Module 4: Final Project</h4><p>Apply what you have learned</p></div></div>']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => '20+ Hours', 'text' => 'Video content'], ['title' => 'Quizzes', 'text' => 'Test your knowledge'], ['title' => 'Certificate', 'text' => 'Upon completion']]]],
                    ['type' => 'faq', 'zone' => 'content', 'content' => ['title' => 'Frequently Asked Questions', 'items' => [['question' => 'How long do I have access?', 'answer' => 'Lifetime access to all materials.'], ['question' => 'Is there a money-back guarantee?', 'answer' => 'Yes, 30-day full refund.'], ['question' => 'Do I need prerequisites?', 'answer' => 'No, this course is for beginners.']]]],
                    ['type' => 'order_button', 'zone' => 'content', 'content' => ['text' => 'Enroll for $199', 'url' => '#enroll', 'product_id' => '']],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Consulting', 'consulting', 'Business consulting website with services, case studies, and strategy', '/theme/assets/img/templates/consulting.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '{{NAME}}', 'links' => [['label' => 'Approach', 'url' => '#approach'], ['label' => 'Services', 'url' => '#services'], ['label' => 'Insights', 'url' => '#insights'], ['label' => 'Contact', 'url' => '#contact']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Strategic Consulting for Growth', 'subtitle' => 'Unlock your full potential.', 'btn_text' => 'Schedule a Call', 'btn_url' => '#contact', 'background' => 'gradient']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Approach', 'body' => '<div class="approach-grid"><div class="step"><h4>1. Discovery</h4><p>Understand your business</p></div><div class="step"><h4>2. Strategy</h4><p>Develop a tailored plan</p></div><div class="step"><h4>3. Execution</h4><p>Implement with precision</p></div><div class="step"><h4>4. Optimize</h4><p>Continuous improvement</p></div></div>']],
                    ['type' => 'columns', 'zone' => 'content', 'content' => ['columns' => 3, 'items' => [['title' => '200+ Clients', 'text' => 'Served worldwide'], ['title' => '95%', 'text' => 'Client retention'], ['title' => '$2B+', 'text' => 'Value delivered']]]],
                    ['type' => 'testimonials', 'zone' => 'content', 'content' => ['title' => 'Client Success', 'items' => [['name' => 'CEO, Fortune 500', 'role' => '', 'text' => 'Transformed our operations.'], ['name' => 'Founder, Startup', 'role' => '', 'text' => 'Invaluable strategic guidance.']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Let Us Talk', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'company', 'message']]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
        ['Nail Salon', 'nails', 'Nail salon website with services, gallery, and online booking', '/theme/assets/img/templates/nails.jpg', json_encode([
            'pages' => [
                ['title' => 'Home', 'slug' => 'index', 'blocks' => [
                    ['type' => 'header', 'zone' => 'header', 'content' => ['logo' => '💅 {{NAME}}', 'links' => [['label' => 'Services', 'url' => '#services'], ['label' => 'Gallery', 'url' => '#gallery'], ['label' => 'Pricing', 'url' => '#pricing'], ['label' => 'Book', 'url' => '#book']]]],
                    ['type' => 'hero', 'zone' => 'content', 'content' => ['title' => 'Beautiful Nails at {{NAME}}', 'subtitle' => 'Expert nail art and care.', 'btn_text' => 'Book Appointment', 'btn_url' => '#book', 'background' => 'image']],
                    ['type' => 'text', 'zone' => 'content', 'content' => ['title' => 'Our Services', 'body' => '<div class="grid-3"><div class="service-card"><h4>Manicure</h4><p>From $25</p></div><div class="service-card"><h4>Pedicure</h4><p>From $35</p></div><div class="service-card"><h4>Gel Nails</h4><p>From $45</p></div><div class="service-card"><h4>Acrylic</h4><p>From $50</p></div><div class="service-card"><h4>Nail Art</h4><p>From $10</p></div><div class="service-card"><h4>Waxing</h4><p>From $15</p></div></div>']],
                    ['type' => 'gallery', 'zone' => 'content', 'content' => ['title' => 'Our Work', 'images' => ['https://picsum.photos/seed/nail1/600/400', 'https://picsum.photos/seed/nail2/600/400', 'https://picsum.photos/seed/nail3/600/400', 'https://picsum.photos/seed/nail4/600/400']]],
                    ['type' => 'pricing_table', 'zone' => 'content', 'content' => ['title' => 'Price List', 'plans' => [['name' => 'Basic Manicure', 'price' => '$25', 'features' => ['Shape & File', 'Cuticle Care', 'Polish'], 'btn_text' => 'Book'], ['name' => 'Deluxe Pedicure', 'price' => '$45', 'features' => ['Soak & Scrub', 'Callus Removal', 'Massage', 'Polish'], 'btn_text' => 'Book', 'featured' => true], ['name' => 'Full Set Acrylic', 'price' => '$55', 'features' => ['Tips', 'Acrylic Application', 'Shape & File', 'Design'], 'btn_text' => 'Book']]]],
                    ['type' => 'contact_form', 'zone' => 'content', 'content' => ['title' => 'Book Appointment', 'email' => '{{EMAIL}}', 'fields' => ['name', 'email', 'phone', 'service', 'date', 'message']]],
                    ['type' => 'map', 'zone' => 'content', 'content' => ['address' => '789 Beauty Blvd, Suite 100', 'zoom' => 15]],
                    ['type' => 'footer', 'zone' => 'footer', 'content' => ['text' => '&copy; {{YEAR}} {{NAME}}. All rights reserved.']],
                ]],
            ],
        ])],
    ];

    $stmt = $pdo->prepare("INSERT INTO wb_templates (name, category, description, thumbnail, config) VALUES (?, ?, ?, ?, ?)");
    foreach ($templates as $t) {
        $stmt->execute($t);
    }
    echo "Seeded " . count($templates) . " templates\n";
    echo "\nMigration completed successfully!\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
