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
        return $_POST[$key] ?? $this->json($key) ?? $default;
    }

    public function json($key = null)
    {
        static $parsed = null;
        if ($parsed === null) {
            $raw = file_get_contents('php://input');
            $parsed = $raw ? (json_decode($raw, true) ?? []) : [];
        }
        return $key === null ? $parsed : ($parsed[$key] ?? null);
    }

    public function query($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public function all()
    {
        return array_merge($_GET, $_POST, $this->json());
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

    public function header($key, $default = null)
    {
        static $headers = null;
        if ($headers === null) {
            if (function_exists('getallheaders')) {
                $headers = getallheaders();
            } else {
                $headers = [];
                foreach ($_SERVER as $k => $v) {
                    if (strpos($k, 'HTTP_') === 0) {
                        $headers[str_replace('_', '-', substr($k, 5))] = $v;
                    }
                }
            }
        }
        return $headers[$key] ?? $headers[strtolower($key)] ?? $headers[strtoupper($key)] ?? $default;
    }

    public function bearerToken()
    {
        $header = $this->header('Authorization', '');
        if (preg_match('/Bearer\s+(.+)$/i', $header, $m)) {
            return $m[1];
        }
        return null;
    }

    public function files($key = null)
    {
        return $key === null ? $_FILES : ($_FILES[$key] ?? null);
    }
}