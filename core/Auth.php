<?php
/**
 * Authentication Class
 * Manages user authentication with crypto password hashing
 */

namespace Core;

use Core\Database;
use Core\Session;

class Auth
{
    protected $db;
    protected $session;

    public function __construct(Database $db, Session $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Attempt to log in a user
     */
    public function attempt($credentials)
    {
        // For demo, we check against a stored admin user in the database.
        // In a real system, you would have a users table.
        // Here we assume an 'admins' table with email and password_hash.
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';

        // Fetch admin by email
        $admin = $this->db->table('admins')->where('email', $email)->first();

        if ($admin && password_verify($password, $admin->password_hash)) {
            // Set user in session
            $user = (object)[
                'id' => $admin->id,
                'email' => $admin->email,
                'name' => $admin->name,
                'theme_settings' => $admin->theme_settings ?? '{}',
                'is_admin' => true
            ];
            $this->session->put('user', $user);
            $this->session->put('is_admin', true);
            return true;
        }

        return false;
    }

    /**
     * Get the currently authenticated user
     */
    public function user()
    {
        return $this->session->get('user');
    }

    /**
     * Check if the user is authenticated
     */
    public function check()
    {
        return !is_null($this->session->get('user'));
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin()
    {
        return $this->session->get('is_admin') === true;
    }

    /**
     * Log out the user
     */
    public function logout()
    {
        $this->session->forget('user');
        $this->session->forget('is_admin');
    }

    /**
     * Hash a password using crypto hashing
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password against a hash
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
