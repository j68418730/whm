<?php
/**
 * Session Class
 * Manages session data
 */

namespace Core;

class Session
{
    public function generateCsrfToken()
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public function validateCsrfToken($token)
    {
        if (empty($_SESSION['_csrf_token']) || empty($token)) return false;
        return hash_equals($_SESSION['_csrf_token'], $token);
    }
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function forget($key)
    {
        unset($_SESSION[$key]);
    }

    public function flush()
    {
        $_SESSION = [];
    }

    public function regenerate()
    {
        session_regenerate_id(true);
    }
}