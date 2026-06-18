<?php
// Reviews admin controller
namespace Admin\Controllers;
use Core\Controller;

class ReviewsController extends Controller
{
    protected $auth, $db, $response, $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->db = $app->get('db');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $reviews = $this->db->table('reviews')->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('admin.reviews.index', ['reviews' => $reviews, 'title' => 'Reviews']);
    }

    public function approve($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('reviews')->where('id', $id)->update(['approved' => 1]);
        $this->response->redirect('/admin/reviews');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('reviews')->where('id', $id)->delete();
        $this->response->redirect('/admin/reviews');
        exit;
    }
}
