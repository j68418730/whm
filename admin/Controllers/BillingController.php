<?php

namespace Admin\Controllers;

use Core\Controller;

class BillingController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    protected function theme()
    {
        $user = $this->auth->user();
        return json_decode($user->theme_settings ?? '{}', true);
    }

    // ── Dashboard ──
    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $income = $this->db->table('billing_payments')->get() ?: [];
        $totalRevenue = 0;
        foreach ($income as $p) { if ($p->status === 'completed') $totalRevenue += $p->amount; }
        $orders = $this->db->table('billing_orders')->get() ?: [];
        $activeServices = 0;
        $pendingOrders = 0;
        foreach ($orders as $o) { if ($o->status === 'active') $activeServices++; if ($o->status === 'pending') $pendingOrders++; }
        return $this->view('admin.billing.index', [
            'user' => $user, 'title' => 'Billing', 'theme_settings' => $this->theme(),
            'totalRevenue' => $totalRevenue, 'activeServices' => $activeServices, 'pendingOrders' => $pendingOrders,
            'totalInvoices' => count($this->db->table('invoices')->get() ?: []),
        ]);
    }

    // ── Products ──
    public function products()
    {
        $this->guard();
        $user = $this->auth->user();
        $products = $this->db->table('billing_products')->get() ?: [];
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get() ?: [];
        return $this->view('admin.billing.products', [
            'user' => $user, 'title' => 'Billing Products', 'theme_settings' => $this->theme(),
            'products' => $products, 'packages' => $packages
        ]);
    }

    public function productStore()
    {
        $this->guard();
        $this->db->table('billing_products')->insertGetId([
            'name' => $this->request->post('name', ''), 'description' => $this->request->post('description', ''),
            'type' => $this->request->post('type', 'hosting'), 'price' => $this->request->post('price', 0),
            'setup_fee' => $this->request->post('setup_fee', 0), 'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'is_active' => $this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = 'Product created.';
        $this->response->redirect('/admin/billing/products');
    }

    public function productUpdate($id)
    {
        $this->guard();
        $this->db->table('billing_products')->where('id', $id)->update([
            'name' => $this->request->post('name', ''), 'description' => $this->request->post('description', ''),
            'type' => $this->request->post('type', 'hosting'), 'price' => $this->request->post('price', 0),
            'setup_fee' => $this->request->post('setup_fee', 0), 'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'is_active' => $this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = 'Product updated.';
        $this->response->redirect('/admin/billing/products');
    }

    public function productDelete($id)
    {
        $this->guard();
        $this->db->table('billing_products')->where('id', $id)->delete();
        $this->response->redirect('/admin/billing/products');
    }

    // ── Orders ──
    public function orders()
    {
        $this->guard();
        $user = $this->auth->user();
        $orders = $this->db->table('billing_orders')->get() ?: [];
        return $this->view('admin.billing.orders', ['user' => $user, 'title' => 'Orders', 'theme_settings' => $this->theme(), 'orders' => $orders]);
    }

    public function orderUpdate($id)
    {
        $this->guard();
        $status = $this->request->post('status', 'pending');
        $this->db->table('billing_orders')->where('id', $id)->update(['status' => $status]);
        $_SESSION['success_message'] = "Order #{$id} updated to {$status}.";
        $this->response->redirect('/admin/billing/orders');
    }

    // ── Services ──
    public function services()
    {
        $this->guard();
        $user = $this->auth->user();
        $services = $this->db->table('billing_services')->get() ?: [];
        return $this->view('admin.billing.services', ['user' => $user, 'title' => 'Services', 'theme_settings' => $this->theme(), 'services' => $services]);
    }

    public function serviceUpdate($id)
    {
        $this->guard();
        $this->db->table('billing_services')->where('id', $id)->update([
            'status' => $this->request->post('status', 'active'),
            'next_due_date' => $this->request->post('next_due_date', ''),
        ]);
        $_SESSION['success_message'] = 'Service updated.';
        $this->response->redirect('/admin/billing/services');
    }

    // ── Invoices ──
    public function invoices()
    {
        $this->guard();
        $user = $this->auth->user();
        $invoices = $this->db->table('invoices')->get() ?: [];
        return $this->view('admin.billing.invoices', ['user' => $user, 'title' => 'Invoices', 'theme_settings' => $this->theme(), 'invoices' => $invoices]);
    }

    public function invoiceCreate()
    {
        $this->guard();
        $uid = (int)$this->request->post('user_id', 0);
        $total = (float)$this->request->post('total', 0);
        $num = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        $this->db->table('invoices')->insertGetId([
            'user_id' => $uid, 'invoice_number' => $num, 'date' => date('Y-m-d'),
            'due_date' => $this->request->post('due_date', date('Y-m-d', strtotime('+30 days'))),
            'subtotal' => $total, 'total' => $total, 'status' => 'draft',
        ]);
        $_SESSION['success_message'] = "Invoice {$num} created.";
        $this->response->redirect('/admin/billing/invoices');
    }

    public function invoiceUpdateStatus($id)
    {
        $this->guard();
        $this->db->table('invoices')->where('id', $id)->update(['status' => $this->request->post('status', 'draft')]);
        $_SESSION['success_message'] = 'Invoice updated.';
        $this->response->redirect('/admin/billing/invoices');
    }

    // ── Payments ──
    public function payments()
    {
        $this->guard();
        $user = $this->auth->user();
        $payments = $this->db->table('billing_payments')->get() ?: [];
        return $this->view('admin.billing.payments', ['user' => $user, 'title' => 'Payments', 'theme_settings' => $this->theme(), 'payments' => $payments]);
    }

    public function paymentStore()
    {
        $this->guard();
        $this->db->table('billing_payments')->insertGetId([
            'user_id' => (int)$this->request->post('user_id', 0),
            'invoice_id' => $this->request->post('invoice_id') ? (int)$this->request->post('invoice_id') : null,
            'amount' => (float)$this->request->post('amount', 0),
            'method' => $this->request->post('method', 'manual'),
            'status' => 'completed', 'transaction_id' => $this->request->post('transaction_id', ''),
        ]);
        $_SESSION['success_message'] = 'Payment recorded.';
        $this->response->redirect('/admin/billing/payments');
    }

    // ── Taxes ──
    public function taxes()
    {
        $this->guard();
        $user = $this->auth->user();
        $taxes = $this->db->table('billing_taxes')->get() ?: [];
        return $this->view('admin.billing.taxes', ['user' => $user, 'title' => 'Taxes', 'theme_settings' => $this->theme(), 'taxes' => $taxes]);
    }

    public function taxStore()
    {
        $this->guard();
        $this->db->table('billing_taxes')->insertGetId([
            'name' => $this->request->post('name', ''), 'rate' => (float)$this->request->post('rate', 0),
            'country' => $this->request->post('country', ''),
        ]);
        $_SESSION['success_message'] = 'Tax rate added.';
        $this->response->redirect('/admin/billing/taxes');
    }

    public function taxDelete($id)
    {
        $this->guard();
        $this->db->table('billing_taxes')->where('id', $id)->delete();
        $this->response->redirect('/admin/billing/taxes');
    }

    // ── Coupons ──
    public function coupons()
    {
        $this->guard();
        $user = $this->auth->user();
        $coupons = $this->db->table('billing_coupons')->get() ?: [];
        return $this->view('admin.billing.coupons', ['user' => $user, 'title' => 'Coupons', 'theme_settings' => $this->theme(), 'coupons' => $coupons]);
    }

    public function couponStore()
    {
        $this->guard();
        $this->db->table('billing_coupons')->insertGetId([
            'code' => strtoupper($this->request->post('code', '')), 'type' => $this->request->post('type', 'percentage'),
            'value' => (float)$this->request->post('value', 0), 'max_uses' => (int)$this->request->post('max_uses', 0),
            'min_total' => (float)$this->request->post('min_total', 0), 'expires_at' => $this->request->post('expires_at'),
        ]);
        $_SESSION['success_message'] = 'Coupon created.';
        $this->response->redirect('/admin/billing/coupons');
    }

    public function couponDelete($id)
    {
        $this->guard();
        $this->db->table('billing_coupons')->where('id', $id)->delete();
        $this->response->redirect('/admin/billing/coupons');
    }

    // ── Credits ──
    public function credits()
    {
        $this->guard();
        $user = $this->auth->user();
        $credits = $this->db->table('billing_credits')->get() ?: [];
        return $this->view('admin.billing.credits', ['user' => $user, 'title' => 'Credits', 'theme_settings' => $this->theme(), 'credits' => $credits]);
    }

    public function creditStore()
    {
        $this->guard();
        $this->db->table('billing_credits')->insertGetId([
            'user_id' => (int)$this->request->post('user_id', 0),
            'amount' => (float)$this->request->post('amount', 0),
            'description' => $this->request->post('description', ''),
        ]);
        $_SESSION['success_message'] = 'Credit added.';
        $this->response->redirect('/admin/billing/credits');
    }

    // ── Refunds ──
    public function refunds()
    {
        $this->guard();
        $user = $this->auth->user();
        $refunds = $this->db->table('billing_refunds')->get() ?: [];
        return $this->view('admin.billing.refunds', ['user' => $user, 'title' => 'Refunds', 'theme_settings' => $this->theme(), 'refunds' => $refunds]);
    }
}
