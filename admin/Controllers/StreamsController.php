<?php
/**
 * Streams Controller
 * Handles station management: create, delete, restart, suspend, clone station
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class StreamsController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->auth = \Core\Application::getInstance()->get('auth');
        $this->request = \Core\Application::getInstance()->get('request');
        $this->response = \Core\Application::getInstance()->get('response');
    }

    /**
     * Show streams management dashboard
     */
    public function index()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get admin user info
        $user = $this->auth->user();

        $streamsStats = [
            'total_streams' => 0,
            'active_streams' => 0,
            'suspended_streams' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the streams management view
        return $this->view('admin.streams.index', [
            'user' => $user,
            'streamsStats' => $streamsStats,
            'theme_settings' => $theme_settings
        ]);
    }

    /**
     * Show create stream form
     */
    public function create()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get admin user info
        $user = $this->auth->user();

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the create stream view
        return $this->view('admin.streams.create', [
            'user' => $user,
            'theme_settings' => $theme_settings
        ]);
    }

    /**
     * Store new stream
     */
    public function store()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Process form data (in real implementation, save to database)
        $name = $this->request->post('name', '');
        $mount_point = $this->request->post('mount_point', '');
        $bitrate = $this->request->post('bitrate', '128kbps');

        // Redirect back with success message
        $_SESSION['success_message'] = 'Stream created successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    /**
     * Show edit stream form
     */
    public function edit($id)
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get admin user info
        $user = $this->auth->user();

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        return $this->view('admin.streams.edit', [
            'user' => $user,
            'streamId' => $id,
            'theme_settings' => $theme_settings
        ]);
    }

    /**
     * Update stream
     */
    public function update($id)
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Process form data (in real implementation, update in database)
        $name = $this->request->post('name', '');
        $mount_point = $this->request->post('mount_point', '');
        $bitrate = $this->request->post('bitrate', '128kbps');

        // Redirect back with success message
        $_SESSION['success_message'] = 'Stream updated successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    /**
     * Delete stream
     */
    public function delete($id)
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Delete stream from database (in real implementation)
        $_SESSION['success_message'] = 'Stream deleted successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    /**
     * Restart stream
     */
    public function restart($id)
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Restart stream (in real implementation, send signal to Icecast/Liquidsoap)
        $_SESSION['success_message'] = 'Stream restarted successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    /**
     * Suspend stream
     */
    public function suspend($id)
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Suspend stream (in real implementation, update status in database)
        $_SESSION['success_message'] = 'Stream suspended successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    /**
     * Unsuspend stream
     */
    public function unsuspend($id)
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Unsuspend stream (in real implementation, update status in database)
        $_SESSION['success_message'] = 'Stream unsuspended successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }

    /**
     * Clone stream
     */
    public function clone($id)
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Clone stream (in real implementation, duplicate in database with new name)
        $_SESSION['success_message'] = 'Stream cloned successfully!';
        $this->response->redirect('/admin/streams');
        exit;
    }
}