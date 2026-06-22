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

    protected function requireAdmin()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $totalSites = count($this->db->table('wb_sites')->get() ?: []);
        $totalPages = count($this->db->table('wb_pages')->get() ?: []);
        $totalTemplates = count($this->db->table('wb_templates')->get() ?: []);
        $totalThemes = count($this->db->table('wb_themes')->get() ?: []);
        $recentSites = $this->db->table('wb_sites')->orderBy('created_at', 'DESC')->limit(5)->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.admin.index', [
            'user' => $this->auth->user(),
            'totalSites' => $totalSites, 'totalPages' => $totalPages,
            'totalTemplates' => $totalTemplates, 'totalThemes' => $totalThemes,
            'recentSites' => $recentSites, 'title' => 'Website Builder Dashboard',
        ]);
    }

    public function sites()
    {
        $this->requireAdmin();
        $sites = $this->db->table('wb_sites')->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.admin.sites', [
            'user' => $this->auth->user(), 'sites' => $sites, 'title' => 'All Websites',
        ]);
    }

    public function siteShow($id)
    {
        $this->requireAdmin();
        $site = $this->db->table('wb_sites')->where('id', (int)$id)->first();
        if (!$site) { $_SESSION['error_message'] = 'Site not found'; $this->response->redirect('/admin/websitebuilder/sites'); exit; }
        $pages = $this->db->table('wb_pages')->where('site_id', $site->id)->orderBy('sort_order', 'ASC')->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.admin.site_show', [
            'user' => $this->auth->user(), 'site' => $site, 'pages' => $pages, 'title' => 'Site: ' . $site->name,
        ]);
    }

    public function siteDelete($id)
    {
        $this->requireAdmin();
        $site = $this->db->table('wb_sites')->where('id', (int)$id)->first();
        if ($site) {
            $pages = $this->db->table('wb_pages')->where('site_id', $site->id)->get() ?: [];
            foreach ($pages as $p) {
                $this->db->table('wb_blocks')->where('page_id', $p->id)->delete();
            }
            $this->db->table('wb_pages')->where('site_id', $site->id)->delete();
            $this->db->table('wb_menus')->where('site_id', $site->id)->delete();
            $this->db->table('wb_media')->where('site_id', $site->id)->delete();
            $forms = $this->db->table('wb_forms')->where('site_id', $site->id)->get() ?: [];
            foreach ($forms as $f) {
                $this->db->table('wb_form_entries')->where('form_id', $f->id)->delete();
            }
            $this->db->table('wb_forms')->where('site_id', $site->id)->delete();
            $this->db->table('wb_blog_posts')->where('site_id', $site->id)->delete();
            $this->db->table('wb_sites')->where('id', $site->id)->delete();
            $_SESSION['success_message'] = 'Site deleted.';
        }
        $this->response->redirect('/admin/websitebuilder/sites');
    }

    public function templates()
    {
        $this->requireAdmin();
        $templates = $this->db->table('wb_templates')->orderBy('name', 'ASC')->get() ?: [];
        $categories = [];
        foreach ($templates as $t) {
            if ($t->category && !in_array($t->category, $categories)) $categories[] = $t->category;
        }
        return $this->view('Plugins.WebsiteBuilder.Views.admin.templates', [
            'user' => $this->auth->user(), 'templates' => $templates, 'categories' => $categories, 'title' => 'Templates',
        ]);
    }

    public function templateStore()
    {
        $this->requireAdmin();
        $data = [
            'name' => $this->request->post('name', ''),
            'category' => $this->request->post('category', 'custom'),
            'description' => $this->request->post('description', ''),
            'thumbnail' => $this->request->post('thumbnail', ''),
            'config' => $this->request->post('config', '{}'),
            'is_active' => 1,
        ];
        $id = $this->db->table('wb_templates')->insertGetId($data);
        $_SESSION['success_message'] = 'Template saved.';
        $this->response->redirect('/admin/websitebuilder/templates');
    }

    public function templateDelete($id)
    {
        $this->requireAdmin();
        $this->db->table('wb_templates')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Template deleted.';
        $this->response->redirect('/admin/websitebuilder/templates');
    }

    public function templateImport()
    {
        $this->requireAdmin();
        $json = $this->request->post('json_data', '');
        if ($json) {
            $data = json_decode($json, true);
            if ($data) {
                require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
                $engine = new \Services\WebsiteBuilderEngine();
                $engine->importTemplate($data);
                $_SESSION['success_message'] = 'Template imported.';
            } else {
                $_SESSION['error_message'] = 'Invalid JSON data.';
            }
        }
        $this->response->redirect('/admin/websitebuilder/templates');
    }

    public function templateExport($id)
    {
        $this->requireAdmin();
        require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
        $engine = new \Services\WebsiteBuilderEngine();
        $data = $engine->exportTemplate((int)$id);
        if ($data) {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="template_' . $id . '.json"');
            echo json_encode($data, JSON_PRETTY_PRINT);
            exit;
        }
        $_SESSION['error_message'] = 'Template not found.';
        $this->response->redirect('/admin/websitebuilder/templates');
    }

    public function themes()
    {
        $this->requireAdmin();
        $themes = $this->db->table('wb_themes')->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.admin.themes', [
            'user' => $this->auth->user(), 'themes' => $themes, 'title' => 'Themes',
        ]);
    }

    public function themeStore()
    {
        $this->requireAdmin();
        $data = [
            'name' => $this->request->post('name', 'Custom Theme'),
            'description' => $this->request->post('description', ''),
            'version' => $this->request->post('version', '1.0'),
            'author' => $this->request->post('author', ''),
            'config' => $this->request->post('config', '{}'),
            'is_active' => 1,
        ];
        $this->db->table('wb_themes')->insertGetId($data);
        $_SESSION['success_message'] = 'Theme saved.';
        $this->response->redirect('/admin/websitebuilder/themes');
    }

    public function themeDelete($id)
    {
        $this->requireAdmin();
        $this->db->table('wb_themes')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Theme deleted.';
        $this->response->redirect('/admin/websitebuilder/themes');
    }

    public function settings()
    {
        $this->requireAdmin();
        return $this->view('Plugins.WebsiteBuilder.Views.admin.settings', [
            'user' => $this->auth->user(), 'title' => 'Builder Settings',
        ]);
    }

    public function blockTypes()
    {
        $this->requireAdmin();
        require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
        $engine = new \Services\WebsiteBuilderEngine();
        $types = $engine->getBlockTypes();
        header('Content-Type: application/json');
        echo json_encode($types);
        exit;
    }
}
