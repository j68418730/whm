<?php

namespace Admin\Controllers;

use Core\Controller;

class TodoController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    public function index()
    {
        $todos = $this->db->table('todos')->get() ?: [];
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.todo.index', [
            'user' => $user, 'todos' => $todos, 'theme_settings' => $theme_settings, 'title' => 'ToDo List'
        ]);
    }

    public function store()
    {
        $this->db->table('todos')->insertGetId([
            'title' => $this->request->post('title', 'Untitled'),
            'description' => $this->request->post('description', ''),
            'category' => $this->request->post('category', 'General'),
            'status' => 'pending', 'progress' => 0,
        ]);
        $_SESSION['success_message'] = 'Task added.';
        $this->response->redirect('/admin/todo');
        exit;
    }

    public function update($id)
    {
        $progress = (int)$this->request->post('progress', 0);
        $status = $progress >= 100 ? 'completed' : ($progress > 0 ? 'in_progress' : 'pending');
        $this->db->table('todos')->where('id', $id)->update([
            'progress' => $progress, 'status' => $status,
            'title' => $this->request->post('title', ''),
            'description' => $this->request->post('description', ''),
        ]);
        $_SESSION['success_message'] = 'Task updated.';
        $this->response->redirect('/admin/todo');
        exit;
    }

    public function destroy($id)
    {
        $this->db->table('todos')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'Task deleted.';
        $this->response->redirect('/admin/todo');
        exit;
    }
}
