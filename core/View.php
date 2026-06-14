<?php

namespace Core;

class View
{
    protected $viewPath;
    protected $data = [];

    public function __construct($viewPath = '')
    {
        $this->viewPath = rtrim(BASE_PATH, '/');
        if ($viewPath) {
            $this->viewPath .= '/' . str_replace('.', '/', trim($viewPath, '/'));
        }
    }

    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function render($view = '')
    {
        $suffix = $view ? '/' . str_replace('.', '/', ltrim($view, '/')) : '';
        $viewFile = $this->viewPath . $suffix . '.php';

        if (!is_file($viewFile)) {
            if (!str_contains($viewFile, '/Views/')) {
                $viewFile = preg_replace('#/(admin|user)/#i', '/$1/Views/', $viewFile, 1);
            }
        }

        if (!is_file($viewFile)) {
            return $this->renderFallback($viewFile);
        }

        extract($this->data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $isAdmin = str_contains($viewFile, '/admin/') || str_contains($viewFile, '\admin\\');
        $isUser = str_contains($viewFile, '/user/') || str_contains($viewFile, '\user\\');

        if ($isUser && !$isAdmin) {
            $layoutFile = BASE_PATH . '/theme/user_layout.php';
            if (is_file($layoutFile)) {
                $title = $this->data['title'] ?? 'Dashboard';
                $user = $this->data['user'] ?? null;
                $hosting = $this->data['hosting'] ?? null;
                ob_start();
                require $layoutFile;
                return ob_get_clean();
            }
        }

        if ($isAdmin) {
            // Strip outer HTML from admin views and wrap in admin layout
            $bodyContent = $content;
            if (preg_match('/<body[^>]*>(.*)<\/body>/si', $content, $m)) {
                $bodyContent = $m[1];
            }
            $layoutFile = BASE_PATH . '/theme/admin_layout.php';
            if (is_file($layoutFile)) {
                $title = $this->data['title'] ?? 'Dashboard';
                $user = $this->data['user'] ?? null;
                ob_start();
                require $layoutFile;
                return ob_get_clean();
            }
            return $bodyContent;
        }

        // Non-admin views: inject background or wrap in theme layout
        if (str_contains($content, '<body')) {
            $bgInject = "\n".'<div class="bg-overlay"></div>'."\n".'<link rel="stylesheet" href="/theme/assets/css/style.css">'."\n".'<style>body{background:#000!important}.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}</style>';
            $content = str_replace('<head>', "<head>$bgInject", $content);
        } else {
            $layoutFile = BASE_PATH . '/theme/layout.php';
            if (is_file($layoutFile)) {
                $title = $this->data['title'] ?? 'Planet Hosts';
                $loggedIn = $this->data['loggedIn'] ?? ($this->data['user'] ?? null ? true : false);
                $user = $this->data['user'] ?? null;
                ob_start();
                require $layoutFile;
                $content = ob_get_clean();
            }
        }

        return $content;
    }

    protected function renderFallback($viewFile)
    {
        $module = basename(dirname($viewFile));
        $title = ucwords(str_replace(['_', '-'], ' ', $module));
        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' - Planet Hosts</title><link rel="stylesheet" href="/theme/assets/css/style.css"><style>body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:800px;margin:auto}h1{color:#0A84FF}p{color:#94a3b8}a{color:#00BFFF}</style></head><body><div class="bg-overlay"></div><div class="card"><h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1><p>This module is ready for content.</p><a href="/admin/dashboard">&larr; Back</a></div></body></html>';
    }
}
