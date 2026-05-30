<?php
/**
 * Request Class
 * Simplified HTTP request wrapper
 */

namespace Core;

class Request
{
    public function get($key, $default = null)
    {
        return $_GET[$key] ?? $_POST[$key] ?? $default;
    }

    public function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public function query($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public function all()
    {
        return array_merge($_GET, $_POST);
    }

    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isMethod($method)
    {
        return strtoupper($this->method()) === strtoupper($method);
    }

    public function path()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        // Remove query string
        if (false !== $pos = strpos($path, '?')) {
            $path = substr($path, 0, $pos);
        }
        return rtrim($path, '/');
    }

    public function ip()
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}