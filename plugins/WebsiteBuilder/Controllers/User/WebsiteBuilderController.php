<?php
namespace Plugins\WebsiteBuilder\Controllers\User;

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

    protected function requireUser()
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/?login');
            exit;
        }
    }

    protected function getUser()
    {
        $this->requireUser();
        $user = $this->auth->user();
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hosting) $hosting = $this->db->table('hosting_users')->where('username', $user->name)->first();
        return $hosting ?: $user;
    }

    protected function getSite($siteId)
    {
        $hosting = $this->getUser();
        $userId = is_object($hosting) && isset($hosting->id) ? $hosting->id : $hosting->id ?? 0;
        $site = $this->db->table('wb_sites')->where('id', (int)$siteId)->where('user_id', $userId)->first();
        if (!$site) {
            $_SESSION['error_message'] = 'Site not found.';
            $this->response->redirect('/user/websites');
            exit;
        }
        return $site;
    }

    public function index()
    {
        $hosting = $this->getUser();
        $userId = is_object($hosting) && isset($hosting->id) ? $hosting->id : $hosting->id ?? 0;
        $sites = $this->db->table('wb_sites')->where('user_id', $userId)->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.user.index', [
            'user' => $this->auth->user(), 'hosting' => $hosting,
            'sites' => $sites, 'title' => 'My Websites',
        ]);
    }

    public function create()
    {
        $hosting = $this->getUser();
        $templates = $this->db->table('wb_templates')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.user.create', [
            'user' => $this->auth->user(), 'hosting' => $hosting,
            'templates' => $templates, 'title' => 'Create Website',
        ]);
    }

    public function store()
    {
        $hosting = $this->getUser();
        $userId = is_object($hosting) && isset($hosting->id) ? $hosting->id : $hosting->id ?? 0;
        $name = $this->request->post('name', 'My Website');
        $domain = $this->request->post('domain', '');
        $templateId = (int)$this->request->post('template_id', 0);

        require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
        $engine = new \Services\WebsiteBuilderEngine();

        if (!$templateId) {
            // Get first available template
            $first = $this->db->table('wb_templates')->where('is_active', 1)->first();
            $templateId = $first ? $first->id : 0;
        }

        $siteId = $engine->createSite($userId, $name, $domain, $templateId);
        $_SESSION['success_message'] = 'Website created!';
        $this->response->redirect('/user/websites/' . $siteId);
    }

    public function dashboard($siteId)
    {
        $site = $this->getSite($siteId);
        $pages = $this->db->table('wb_pages')->where('site_id', $site->id)->orderBy('sort_order', 'ASC')->get() ?: [];
        $totalMedia = count($this->db->table('wb_media')->where('site_id', $site->id)->get() ?: []);
        $totalForms = count($this->db->table('wb_forms')->where('site_id', $site->id)->get() ?: []);
        $totalBlog = count($this->db->table('wb_blog_posts')->where('site_id', $site->id)->get() ?: []);
        return $this->view('Plugins.WebsiteBuilder.Views.user.dashboard', [
            'user' => $this->auth->user(), 'hosting' => $this->getUser(),
            'site' => $site, 'pages' => $pages,
            'totalMedia' => $totalMedia, 'totalForms' => $totalForms, 'totalBlog' => $totalBlog,
            'title' => $site->name,
        ]);
    }

    public function editor($siteId, $pageId)
    {
        $site = $this->getSite($siteId);
        require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
        $engine = new \Services\WebsiteBuilderEngine();
        $page = $engine->getPage((int)$pageId);
        if (!$page || $page->site_id != $site->id) {
            $_SESSION['error_message'] = 'Page not found.';
            $this->response->redirect('/user/websites/' . $siteId);
            exit;
        }
        $blockTypes = $engine->getBlockTypes();
        $categorized = [];
        foreach ($blockTypes as $key => $bt) {
            $cat = $bt['category'] ?? 'other';
            $categorized[$cat][$key] = $bt;
        }
        return $this->view('Plugins.WebsiteBuilder.Views.user.editor', [
            'user' => $this->auth->user(), 'hosting' => $this->getUser(),
            'site' => $site, 'page' => $page, 'blockTypes' => $blockTypes,
            'categorized' => $categorized, 'title' => 'Edit: ' . $page->title,
        ]);
    }

    public function savePage()
    {
        $this->requireUser();
        $siteId = (int)($this->request->post('site_id', 0));
        $pageId = (int)($this->request->post('page_id', 0));
        $blocks = $this->request->post('blocks', '[]');

        $site = $this->db->table('wb_sites')->where('id', $siteId)->first();
        if (!$site) { http_response_code(404); echo json_encode(['error' => 'Site not found']); exit; }

        $hosting = $this->getUser();
        $userId = is_object($hosting) && isset($hosting->id) ? $hosting->id : 0;
        if ($site->user_id != $userId) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }

        require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
        $engine = new \Services\WebsiteBuilderEngine();
        $blocksArr = json_decode($blocks, true) ?: [];
        $engine->saveBlocks($pageId, $blocksArr);

        // Save page content/title
        $title = $this->request->post('title', '');
        if ($title) {
            $this->db->table('wb_pages')->where('id', $pageId)->update([
                'title' => $title,
                'content' => json_encode($blocksArr),
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Page saved!']);
        exit;
    }

    public function preview($siteId, $pageId)
    {
        $site = $this->getSite($siteId);
        require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
        $engine = new \Services\WebsiteBuilderEngine();
        echo $engine->renderPage((int)$pageId);
        exit;
    }

    public function publish($siteId)
    {
        $this->getSite($siteId);
        require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
        $engine = new \Services\WebsiteBuilderEngine();
        $engine->publishSite((int)$siteId);
        $_SESSION['success_message'] = 'Site published!';
        $this->response->redirect('/user/websites/' . $siteId);
    }

    public function unpublish($siteId)
    {
        $this->getSite($siteId);
        require_once BASE_PATH . '/services/WebsiteBuilderEngine.php';
        $engine = new \Services\WebsiteBuilderEngine();
        $engine->unpublishSite((int)$siteId);
        $_SESSION['success_message'] = 'Site unpublished.';
        $this->response->redirect('/user/websites/' . $siteId);
    }

    public function settings($siteId)
    {
        $site = $this->getSite($siteId);
        return $this->view('Plugins.WebsiteBuilder.Views.user.settings', [
            'user' => $this->auth->user(), 'hosting' => $this->getUser(),
            'site' => $site, 'title' => 'Settings: ' . $site->name,
        ]);
    }

    public function settingsSave($siteId)
    {
        $site = $this->getSite($siteId);
        $name = $this->request->post('name', $site->name);
        $domain = $this->request->post('domain', $site->domain ?? '');
        $metaTitle = $this->request->post('meta_title', '');
        $metaDesc = $this->request->post('meta_description', '');
        $metaKeywords = $this->request->post('meta_keywords', '');
        $this->db->table('wb_sites')->where('id', $site->id)->update([
            'name' => $name,
            'domain' => $domain,
        ]);
        if ($metaTitle) {
            $this->db->table('wb_pages')->where('site_id', $site->id)->update([
                'meta_title' => $metaTitle,
                'meta_description' => $metaDesc,
                'meta_keywords' => $metaKeywords,
            ]);
        }
        $_SESSION['success_message'] = 'Settings saved.';
        $this->response->redirect('/user/websites/' . $siteId . '/settings');
    }

    public function media($siteId)
    {
        $site = $this->getSite($siteId);
        $files = $this->db->table('wb_media')->where('site_id', $site->id)->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.user.media', [
            'user' => $this->auth->user(), 'hosting' => $this->getUser(),
            'site' => $site, 'files' => $files, 'title' => 'Media: ' . $site->name,
        ]);
    }

    public function mediaUpload($siteId)
    {
        $site = $this->getSite($siteId);
        $hosting = $this->getUser();
        $userId = is_object($hosting) && isset($hosting->id) ? $hosting->id : 0;

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/storage/websitebuilder/' . $siteId . '/';
            @mkdir($uploadDir, 0755, true);
            $origName = $_FILES['file']['name'];
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $dest = $uploadDir . $filename;
            move_uploaded_file($_FILES['file']['tmp_name'], $dest);

            $this->db->table('wb_media')->insertGetId([
                'site_id' => $site->id,
                'user_id' => $userId,
                'filename' => $filename,
                'original_name' => $origName,
                'path' => '/storage/websitebuilder/' . $siteId . '/' . $filename,
                'type' => $_FILES['file']['type'],
                'size' => $_FILES['file']['size'],
            ]);
            $_SESSION['success_message'] = 'File uploaded.';
        }
        $this->response->redirect('/user/websites/' . $siteId . '/media');
    }

    public function mediaDelete($id)
    {
        $this->requireUser();
        $file = $this->db->table('wb_media')->where('id', (int)$id)->first();
        if ($file) {
            $site = $this->db->table('wb_sites')->where('id', $file->site_id)->first();
            if ($site) {
                $hosting = $this->getUser();
                $userId = is_object($hosting) && isset($hosting->id) ? $hosting->id : 0;
                if ($site->user_id == $userId) {
                    $path = BASE_PATH . $file->path;
                    if (file_exists($path)) @unlink($path);
                    $this->db->table('wb_media')->where('id', $file->id)->delete();
                    echo json_encode(['success' => true]);
                    exit;
                }
            }
        }
        echo json_encode(['success' => false, 'error' => 'File not found']);
        exit;
    }

    public function menus($siteId)
    {
        $site = $this->getSite($siteId);
        $menus = $this->db->table('wb_menus')->where('site_id', $site->id)->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.user.menus', [
            'user' => $this->auth->user(), 'hosting' => $this->getUser(),
            'site' => $site, 'menus' => $menus, 'title' => 'Menus: ' . $site->name,
        ]);
    }

    public function menuSave($siteId)
    {
        $site = $this->getSite($siteId);
        $menusData = $this->request->post('menus', '[]');
        $menusArr = json_decode($menusData, true) ?: [];

        $this->db->table('wb_menus')->where('site_id', $site->id)->delete();
        foreach ($menusArr as $m) {
            $this->db->table('wb_menus')->insertGetId([
                'site_id' => $site->id,
                'name' => $m['name'] ?? 'Menu',
                'location' => $m['location'] ?? 'main',
                'items' => json_encode($m['items'] ?? []),
            ]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    public function forms($siteId)
    {
        $site = $this->getSite($siteId);
        $forms = $this->db->table('wb_forms')->where('site_id', $site->id)->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.user.forms', [
            'user' => $this->auth->user(), 'hosting' => $this->getUser(),
            'site' => $site, 'forms' => $forms, 'title' => 'Forms: ' . $site->name,
        ]);
    }

    public function formStore($siteId)
    {
        $site = $this->getSite($siteId);
        $name = $this->request->post('name', 'New Form');
        $fields = $this->request->post('fields', '[]');
        $settings = $this->request->post('settings', '{}');
        $this->db->table('wb_forms')->insertGetId([
            'site_id' => $site->id,
            'name' => $name,
            'fields' => is_string($fields) ? $fields : json_encode($fields),
            'settings' => is_string($settings) ? $settings : json_encode($settings),
        ]);
        $_SESSION['success_message'] = 'Form created.';
        $this->response->redirect('/user/websites/' . $siteId . '/forms');
    }

    public function formEntries($formId)
    {
        $this->requireUser();
        $form = $this->db->table('wb_forms')->where('id', (int)$formId)->first();
        if (!$form) { $_SESSION['error_message'] = 'Form not found.'; $this->response->redirect('/user/websites'); exit; }
        $site = $this->db->table('wb_sites')->where('id', $form->site_id)->first();
        if (!$site) { $this->response->redirect('/user/websites'); exit; }
        $hosting = $this->getUser();
        $userId = is_object($hosting) && isset($hosting->id) ? $hosting->id : 0;
        if ($site->user_id != $userId) { $this->response->redirect('/user/websites'); exit; }

        $entries = $this->db->table('wb_form_entries')->where('form_id', $form->id)->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.user.forms', [
            'user' => $this->auth->user(), 'hosting' => $hosting,
            'site' => $site, 'forms' => [$form], 'entries' => $entries, 'viewEntries' => true,
            'title' => 'Form Entries: ' . $form->name,
        ]);
    }

    public function blog($siteId)
    {
        $site = $this->getSite($siteId);
        $posts = $this->db->table('wb_blog_posts')->where('site_id', $site->id)->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('Plugins.WebsiteBuilder.Views.user.blog', [
            'user' => $this->auth->user(), 'hosting' => $this->getUser(),
            'site' => $site, 'posts' => $posts, 'title' => 'Blog: ' . $site->name,
        ]);
    }

    public function blogStore($siteId)
    {
        $site = $this->getSite($siteId);
        $title = $this->request->post('title', 'Untitled');
        $content = $this->request->post('content', '');
        $excerpt = $this->request->post('excerpt', '');
        $category = $this->request->post('category', '');
        $status = $this->request->post('status', 'draft');
        $slug = $this->request->post('slug', strtolower(str_replace(' ', '-', $title)));
        $postId = (int)$this->request->post('post_id', 0);

        $hosting = $this->getUser();
        $author = $hosting->username ?? $this->auth->user()->name ?? 'Author';

        if ($postId) {
            $this->db->table('wb_blog_posts')->where('id', $postId)->where('site_id', $site->id)->update([
                'title' => $title, 'content' => $content, 'excerpt' => $excerpt,
                'category' => $category, 'status' => $status, 'slug' => $slug,
            ]);
        } else {
            $this->db->table('wb_blog_posts')->insertGetId([
                'site_id' => $site->id, 'title' => $title, 'slug' => $slug,
                'content' => $content, 'excerpt' => $excerpt, 'category' => $category,
                'author' => $author, 'status' => $status,
            ]);
        }
        $_SESSION['success_message'] = 'Blog post saved.';
        $this->response->redirect('/user/websites/' . $siteId . '/blog');
    }

    public function blogDelete($postId)
    {
        $this->requireUser();
        $post = $this->db->table('wb_blog_posts')->where('id', (int)$postId)->first();
        if ($post) {
            $site = $this->db->table('wb_sites')->where('id', $post->site_id)->first();
            if ($site) {
                $hosting = $this->getUser();
                $userId = is_object($hosting) && isset($hosting->id) ? $hosting->id : 0;
                if ($site->user_id == $userId) {
                    $this->db->table('wb_blog_posts')->where('id', $post->id)->delete();
                    $this->db->table('wb_comments')->where('post_id', $post->id)->delete();
                }
            }
        }
        $_SESSION['success_message'] = 'Post deleted.';
        $this->response->redirect('/user/websites/' . ($post->site_id ?? 0) . '/blog');
    }

    public function theme($siteId)
    {
        $site = $this->getSite($siteId);
        $themes = $this->db->table('wb_themes')->get() ?: [];
        $currentTheme = null;
        if ($site->theme_id) {
            $currentTheme = $this->db->table('wb_themes')->where('id', $site->theme_id)->first();
        }
        return $this->view('Plugins.WebsiteBuilder.Views.user.theme', [
            'user' => $this->auth->user(), 'hosting' => $this->getUser(),
            'site' => $site, 'themes' => $themes, 'currentTheme' => $currentTheme,
            'title' => 'Theme: ' . $site->name,
        ]);
    }

    public function themeSave($siteId)
    {
        $site = $this->getSite($siteId);
        $themeId = (int)$this->request->post('theme_id', 0);
        $this->db->table('wb_sites')->where('id', $site->id)->update(['theme_id' => $themeId]);
        $_SESSION['success_message'] = 'Theme updated.';
        $this->response->redirect('/user/websites/' . $siteId . '/theme');
    }
}
