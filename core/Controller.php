<?php
/**
 * Base Controller Class
 */

namespace Core;

class Controller
{
    protected $view;

    public function __construct()
    {
        $this->view = new View();
    }

    protected function view($view, $data = [])
    {
        // Assuming the view is in the user views by default for user controllers
        // In a real system, we might have a way to specify admin vs user
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