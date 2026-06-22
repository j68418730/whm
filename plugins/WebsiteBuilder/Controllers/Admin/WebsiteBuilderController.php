<?php
namespace Plugins\WebsiteBuilder\Controllers\Admin;

use Core\Controller;

class WebsiteBuilderController extends Controller
{
    protected $auth, $db, $response, $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->db = $app->get('db');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $templates = $this->getTemplates();
        return $this->view('Plugins.WebsiteBuilder.Views.admin.websitebuilder.index', [
            'user' => $user, 'templates' => $templates, 'title' => 'Website Builder'
        ]);
    }

    public function preview($template)
    {
        $templates = $this->getTemplates();
        $tpl = $templates[$template] ?? null;
        if (!$tpl) { echo 'Template not found'; exit; }
        echo '<!DOCTYPE html><html><head><title>' . $tpl['name'] . ' - Preview</title><meta name="viewport" content="width=device-width,initial-scale=1.0">';
        echo '<style>body{margin:0;font-family:sans-serif}iframe{width:100%;height:100vh;border:none}</style></head>';
        echo '<body><div style="padding:20px;background:#02050e;color:#fff;text-align:center;border-bottom:1px solid rgba(255,255,255,.1)">';
        echo '<h2>' . $tpl['name'] . '</h2><p style="color:#94a3b8">' . $tpl['desc'] . '</p>';
        echo '<a href="' . $tpl['preview'] . '" target="_blank" style="color:#38bdf8">Open Live Preview →</a></div>';
        echo '<iframe src="' . $tpl['preview'] . '"></iframe></body></html>';
        exit;
    }

    public function aiGenerate()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $description = $this->request->post('description', '');
        if (!$description) { $_SESSION['error_message'] = 'Description required.'; $this->response->redirect('/admin/websitebuilder'); exit; }

        require_once BASE_PATH . '/services/AiSiteGenerator.php';
        $apiKey = $this->getSetting('openai_api_key', '');
        if (!$apiKey) { $_SESSION['error_message'] = 'OpenAI API key not configured. Add it in Settings.'; $this->response->redirect('/admin/websitebuilder'); exit; }

        $result = aiGenerateSite($description, $apiKey);
        if (isset($result['error'])) { $_SESSION['error_message'] = 'AI Error: ' . $result['error']; $this->response->redirect('/admin/websitebuilder'); exit; }

        // Save to user's directory
        $user = $this->auth->user();
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        $dir = $hosting ? "/home/{$hosting->username}/public_html/aisite" : '/tmp/aisite';
        @mkdir($dir, 0755, true);
        file_put_contents($dir . '/index.html', $result['html']);

        $_SESSION['success_message'] = "AI website generated! Files saved to {$dir}/";
        $this->response->redirect('/admin/websitebuilder');
    }

    protected function getSetting($key, $default = '')
    {
        $r = $this->db->table('automation_settings')->where('setting_key', $key)->first();
        return $r ? $r->setting_value : $default;
    }

    public function generate()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $template = $this->request->post('template', '');
        $businessName = $this->request->post('name', 'My Website');
        $templates = $this->getTemplates();
        $tpl = $templates[$template] ?? $templates['business'];

        // Create user's website directory
        $user = $this->auth->user();
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        $dir = $hosting ? "/home/{$hosting->username}/public_html/sitebuilder" : '/tmp/sitebuilder';
        @mkdir($dir, 0755, true);

        // Generate site from template
        $html = str_replace(
            ['{{NAME}}', '{{YEAR}}', '{{EMAIL}}'],
            [htmlspecialchars($businessName), date('Y'), $user->email ?? 'admin@planet-hosts.com'],
            $tpl['html']
        );
        file_put_contents($dir . '/index.html', $html);
        file_put_contents($dir . '/style.css', $tpl['css']);

        $_SESSION['success_message'] = "Website '{$businessName}' created from '{$tpl['name']}' template.";
        $this->response->redirect('/admin/websitebuilder');
    }

    private function getTemplates()
    {
        return [
            'business' => [
                'name' => 'Business Pro',
                'desc' => 'Professional business website with hero, services, team, contact',
                'icon' => '🏢',
                'preview' => '/theme/assets/img/templates/business.jpg',
                'html' => '<!DOCTYPE html><html><head><title>{{NAME}}</title><link rel="stylesheet" href="style.css"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head><body><header><div class="logo">{{NAME}}</div><nav><a href="#home">Home</a><a href="#services">Services</a><a href="#contact">Contact</a></nav></header><section id="home"><h1>Welcome to {{NAME}}</h1><p>Your trusted partner for success.</p></section><section id="services"><h2>Our Services</h2><div class="grid"><div class="card">🌐 Web Design</div><div class="card">📱 Mobile Apps</div><div class="card">☁ Cloud Solutions</div></div></section><section id="contact"><h2>Contact Us</h2><p>Email: {{EMAIL}}</p></section><footer>&copy; {{YEAR}} {{NAME}}</footer></body></html>',
                'css' => '*{margin:0;padding:0;box-sizing:border-box}body{font-family:Inter,sans-serif;color:#fff;background:#02050e}header{padding:20px;display:flex;justify-content:space-between;background:rgba(8,16,28,.9);border-bottom:1px solid rgba(0,191,255,.1)}.logo{font-size:24px;font-weight:800;color:#008cff}nav a{color:#94a3b8;text-decoration:none;margin-left:20px}section{padding:80px 40px;text-align:center}h1{font-size:48px;margin-bottom:20px}h2{font-size:32px;margin-bottom:30px;color:#008cff}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;max-width:800px;margin:auto}.card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:40px;font-size:18px}footer{padding:30px;text-align:center;color:#64748b}'
            ],
            'landing' => [
                'name' => 'Landing Page',
                'desc' => 'Single-page landing with hero, features, CTA',
                'icon' => '🚀',
                'preview' => '/theme/assets/img/templates/landing.jpg',
                'html' => '<!DOCTYPE html><html><head><title>{{NAME}}</title><link rel="stylesheet" href="style.css"></head><body><section class="hero"><h1>{{NAME}}</h1><p>Launch your product today.</p><a href="#" class="btn">Get Started</a></section><section class="features"><h2>Features</h2><div class="grid"><div class="card">⚡ Fast</div><div class="card">🔒 Secure</div><div class="card">📊 Analytics</div></div></section><footer>&copy; {{YEAR}} {{NAME}}</footer></body></html>',
                'css' => '*{margin:0;padding:0;box-sizing:border-box}body{font-family:Inter,sans-serif;color:#fff;background:#02050e;text-align:center}.hero{padding:120px 20px;background:linear-gradient(135deg,rgba(0,100,255,.1),transparent)}h1{font-size:56px;margin-bottom:16px;background:linear-gradient(135deg,#008cff,#00e5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}p{color:#94a3b8;font-size:18px;margin-bottom:30px}.btn{padding:14px 32px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border-radius:8px;text-decoration:none;display:inline-block;font-weight:700}.features{padding:80px 20px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;max-width:600px;margin:auto}.card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:40px}footer{padding:30px;color:#64748b}'
            ],
            'radio' => [
                'name' => 'Radio Station',
                'desc' => 'For online radio stations — player, DJs, schedule',
                'icon' => '📻',
                'preview' => '/theme/assets/img/templates/radio.jpg',
                'html' => '<!DOCTYPE html><html><head><title>{{NAME}} — Live Radio</title><link rel="stylesheet" href="style.css"></head><body><header><div class="logo">📻 {{NAME}}</div><nav><a href="#listen">Listen Live</a><a href="#djs">DJs</a><a href="#schedule">Schedule</a></nav></header><section id="listen"><h1>🎵 Live Radio</h1><audio controls><source src="http://45.61.59.55:6000/stream.ogg" type="audio/ogg"></audio></section><footer>&copy; {{YEAR}} {{NAME}}</footer></body></html>',
                'css' => '*{margin:0;padding:0;box-sizing:border-box}body{font-family:Inter,sans-serif;color:#fff;background:#02050e;text-align:center}header{padding:20px;display:flex;justify-content:space-between;background:rgba(8,16,28,.9);border-bottom:1px solid rgba(0,191,255,.1)}.logo{font-size:20px;font-weight:800;color:#008cff}nav a{color:#94a3b8;text-decoration:none;margin-left:20px}section{padding:80px 20px}h1{font-size:36px;margin-bottom:20px}audio{width:100%;max-width:400px;margin:auto}footer{padding:30px;color:#64748b}'
            ],
        ];
    }
}
