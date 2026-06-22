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

    // Payment Methods
    public function paymentMethods()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $methods = [];
        try { $methods = $this->db->table('user_payment_methods')->where('user_id', $uid)->get() ?: []; } catch (\Exception $e) {}
        return $this->view('user.payment_methods', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Payment Methods', 'methods' => $methods]);
    }

    public function addMethod()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        if (!$uid) { $this->response->redirect('/user/billing'); exit; }
        $type = $this->request->post('type', 'card');
        $details = $this->request->post('details', '');
        $billingAddress = $this->request->post('billing_address', '');
        // Create table if needed
        try {
            $this->db->pdo()->exec("CREATE TABLE IF NOT EXISTS user_payment_methods (
                id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
                type VARCHAR(50) NOT NULL DEFAULT 'card',
                details TEXT, billing_address TEXT,
                is_default TINYINT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
        // If first method, make it default
        $existing = $this->db->table('user_payment_methods')->where('user_id', $uid)->get() ?: [];
        $isDefault = count($existing) === 0 ? 1 : 0;
        $this->db->table('user_payment_methods')->insertGetId([
            'user_id' => $uid, 'type' => $type,
            'details' => $details, 'billing_address' => $billingAddress,
            'is_default' => $isDefault,
        ]);
        $_SESSION['success_message'] = 'Payment method added.';
        $this->response->redirect('/user/billing/payment-methods');
        exit;
    }

    public function deleteMethod($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $this->db->table('user_payment_methods')->where('id', $id)->where('user_id', $uid)->delete();
        $_SESSION['success_message'] = 'Payment method removed.';
        $this->response->redirect('/user/billing/payment-methods');
        exit;
    }

    public function defaultMethod($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $this->db->table('user_payment_methods')->where('user_id', $uid)->update(['is_default' => 0]);
        $this->db->table('user_payment_methods')->where('id', $id)->where('user_id', $uid)->update(['is_default' => 1]);
        $_SESSION['success_message'] = 'Default payment method updated.';
        $this->response->redirect('/user/billing/payment-methods');
        exit;
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
