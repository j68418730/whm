<?php
/**
 * Router Class
 * Simple router for handling routes
 */

namespace Core;

class Router
{
    protected $routes = [];
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch()
    {
        $uri = $this->request->path();
        $method = $this->request->method();

        if (isset($this->routes[$method][$uri])) {
            return $this->callAction($this->routes[$method][$uri]);
        }

        // Try to match with parameters (simplified)
        foreach ($this->routes[$method] as $route => $action) {
            if (preg_match('#^' . $route . '$#', $uri, $matches)) {
                array_shift($matches); // Remove the full match
                return $this->callAction($action, $matches);
            }
        }

        // If no route found, show 404
        $this->response->setStatusCode(404);
        $this->response->setContent('404 - Not Found');
        $this->response->send();
    }

    protected function callAction($action, $parameters = [])
    {
        // $action is in the format "Controller@method"
        if (strpos($action, '@') === false) {
            throw new \Exception("Invalid action format: {$action}");
        }

        [$controllerClass, $method] = explode('@', $action, 2);

        // Check if the controller class exists
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller class not found: {$controllerClass}");
        }

        // Create controller instance
        $controller = new $controllerClass();

        // Check if the method exists
        if (!method_exists($controller, $method)) {
            throw new \Exception("Method not found in controller: {$method}");
        }

        // Call the method with parameters
        return call_user_func_array([$controller, $method], $parameters);
    }
}