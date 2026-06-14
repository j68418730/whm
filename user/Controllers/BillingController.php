<?php

namespace User\Controllers;

use Core\Controller;

class BillingController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function loadUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        return $user;
    }

    public function index()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $invoices = $uid ? ($this->db->table('invoices')->where('user_id', $uid)->orderBy('date', 'DESC')->get() ?: []) : [];
        $totalOutstanding = 0;
        foreach ($invoices as $inv) { if ($inv->status === 'sent' || $inv->status === 'overdue') $totalOutstanding += $inv->total; }
        return $this->view('user.billing', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Billing', 'invoices' => $invoices, 'outstanding' => $totalOutstanding]);
    }

    public function invoices()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $invoices = $uid ? ($this->db->table('invoices')->where('user_id', $uid)->get() ?: []) : [];
        return $this->view('user.invoices', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Invoices', 'invoices' => $invoices]);
    }

    public function pay($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $inv = $this->db->table('invoices')->where('id', $id)->where('user_id', $uid)->first();
        if ($inv && ($inv->status === 'sent' || $inv->status === 'overdue')) {
            $this->db->table('invoices')->where('id', $id)->update(['status' => 'paid']);
            $_SESSION['success'] = "Invoice #{$inv->invoice_number} marked as paid.";
        }
        $this->response->redirect('/user/billing');
    }
}
