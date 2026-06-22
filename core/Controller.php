<?php
namespace Core;

class Controller
{
    protected $view;
    protected $session;

    public function __construct()
    {
        $this->view = new View();
        try {
            $app = Application::getInstance();
            $this->session = $app->get('session');
        } catch (\Exception $e) {
            $this->session = null;
        }
    }

    public function __init()
    {
        // Auto-validate CSRF on POST requests (override in subclasses to skip)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$this->skipCsrf()) {
            $token = $_POST['_csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            // If session doesn't have a CSRF token yet, accept the submitted one
            if (empty($_SESSION['_csrf_token']) && !empty($token)) {
                $_SESSION['_csrf_token'] = $token;
            }
            if (!$this->session || !$this->session->validateCsrfToken($token)) {
                http_response_code(419);
                echo 'CSRF token validation failed.';
                exit;
            }
        }
    }

    protected function skipCsrf()
    {
        return true; // Disabled by default - enable per-controller by returning false
    }

    protected function csrfField()
    {
        $token = $this->session ? $this->session->generateCsrfToken() : '';
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    protected function view($view, $data = [])
    {
        // Add CSRF token to all view data
        if ($this->session) {
            $data['_csrf_token'] = $this->session->generateCsrfToken();
            $data['csrfField'] = $this->csrfField();
        }
        $viewObject = new View($view);
        if (!empty($data)) {
            $viewObject->with($data);
        }
        return $viewObject->render();
    }

    protected function abort($code = 404)
    {
        http_response_code($code);
        exit;
    }

    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
}
