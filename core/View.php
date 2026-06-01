<?php
/**
 * View Class
 * Loads and renders template files
 */

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
            // Only apply the legacy path rewrite if this isn't already a plugin path
            if (!str_contains($viewFile, '/Views/')) {
                $viewFile = preg_replace('#/(admin|user)/#i', '/$1/Views/', $viewFile, 1);
            }
        }

        if (!is_file($viewFile)) {
            return $this->renderFallback($viewFile);
        }

        // Extract the data to local variables
        extract($this->data, EXTR_SKIP);

        // Start output buffering
        ob_start();

        // Include the view file
        require $viewFile;

        // Get the contents of the buffer
        $content = ob_get_clean();

        return $content;
    }

    protected function renderFallback($viewFile)
    {
        $module = basename(dirname($viewFile));
        $title = ucwords(str_replace(['_', '-'], ' ', $module));

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' - Spectre WHM</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="whm-body">
    <main class="whm-shell">
        <aside class="whm-sidebar">
            <div class="brand"><span class="brand-mark">S</span><div><strong>Spectre WHM</strong><small>Hosting + Radio</small></div></div>
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/account">Account Functions</a>
            <a href="/admin/reseller">Reseller Center</a>
            <a href="/admin/packages">Packages</a>
            <a href="/admin/streams">Radio Streams</a>
            <a href="/admin/radio_dashboard">Radio Dashboard</a>
            <a href="/admin/server">Server Overview</a>
        </aside>
        <section class="whm-content">
            <div class="module-header">
                <span class="eyebrow">WHM Module</span>
                <h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>
                <p>This module is wired into the panel navigation and ready for deeper controls.</p>
            </div>
            <div class="card-grid">
                <article class="module-card"><h3>Status</h3><p>Route and controller are available.</p></article>
                <article class="module-card"><h3>Next Build</h3><p>Add forms, tables, actions, and service integrations for this WHM area.</p></article>
            </div>
        </section>
    </main>
</body>
</html>';
    }
}
