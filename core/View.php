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
        // Set the base path for views
        $this->viewPath = rtrim(BASE_PATH, '/') . '/user/Views/' . ltrim($viewPath, '/');
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
        $viewFile = $this->viewPath . '/' . ltrim($view, '/') . '.php';

        if (!is_file($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
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
}