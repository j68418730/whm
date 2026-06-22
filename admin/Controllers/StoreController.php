<?php
namespace Admin\Controllers;

use Core\Controller;

class StoreController extends Controller
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    protected function skipCsrf() { return true; }

    public function category($category = null)
    {
        $categories = $this->db->table('package_categories')->orderBy('sort_order', 'ASC')->get() ?: [];
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get() ?: [];

        $rawCat = $category;
        if ($rawCat) {
            $rawCat = urldecode($rawCat);
            $attempts = [
                $rawCat,
                str_replace('-', ' ', $rawCat),
                str_replace('-', '_', $rawCat),
                str_replace(['_', '-'], ' ', $rawCat),
                ucwords(str_replace(['_', '-'], ' ', $rawCat)),
                strtolower($rawCat),
                strtolower(str_replace('-', '_', $rawCat)),
                strtolower(str_replace('-', ' ', $rawCat)),
            ];
            $attempts = array_unique($attempts);
        }

        $packagesByType = [];
        foreach ($packages as $pkg) {
            $type = $pkg->type ?? 'Uncategorized';
            if (!isset($packagesByType[$type])) $packagesByType[$type] = [];
            $packagesByType[$type][] = $pkg;
        }

        // Check if this is a Game Servers category request
        $isGameServers = false;
        $gameCategories = ['game_server', 'Game Servers', 'game-servers', 'Game Servers', 'GAME_SERVER'];
        if ($rawCat) {
            foreach ($gameCategories as $gc) {
                if (in_array(strtolower($rawCat), [strtolower($gc), strtolower(str_replace(' ', '_', $gc)), strtolower(str_replace(' ', '-', $gc))])) {
                    $isGameServers = true;
                    break;
                }
            }
        }

        $gameTypes = [];
        if ($isGameServers) {
            $gameTypes = $this->db->table('game_types')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
            $currentCategory = 'Game Servers';
            $title = 'Game Servers - Planet Hosts';
        } else {
            $currentCategory = null;
            if ($rawCat) {
                foreach ($attempts as $try) {
                    if (isset($packagesByType[$try])) { $currentCategory = $try; break; }
                }
                if (!$currentCategory) {
                    $lowerTypes = array_change_key_case($packagesByType, CASE_LOWER);
                    foreach ($attempts as $try) {
                        $l = strtolower($try);
                        if (isset($lowerTypes[$l])) {
                            foreach ($packagesByType as $origKey => $v) {
                                if (strtolower($origKey) === $l) { $currentCategory = $origKey; break 2; }
                            }
                        }
                    }
                }
            }
            if (!$currentCategory && !empty($packagesByType)) {
                $currentCategory = array_key_first($packagesByType);
            }
            $title = $currentCategory ? ucwords(str_replace(['_', '-'], ' ', $currentCategory)) . ' - Planet Hosts' : 'Store - Planet Hosts';
        }

        $themeFile = BASE_PATH . '/theme/store.php';
        if (is_file($themeFile)) {
            require $themeFile;
            exit;
        }
        echo '<h1>Store</h1><p>Store page is ready.</p>';
        exit;
    }

    public function detail($id)
    {
        $id = (int)$id;
        $product = $this->db->table('hosting_packages')->where('id', $id)->first();
        if (!$product) {
            header('Location: /hosting');
            exit;
        }
        $categories = $this->db->table('package_categories')->orderBy('sort_order', 'ASC')->get() ?: [];
        $themeFile = BASE_PATH . '/theme/product.php';
        if (is_file($themeFile)) {
            require $themeFile;
            exit;
        }
        echo '<h1>Product Not Found</h1>';
        exit;
    }
}
