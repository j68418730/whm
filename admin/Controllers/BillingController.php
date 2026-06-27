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

    protected function users()
    {
        try { return $this->db->table('hosting_users')->get() ?: []; } catch (\Exception $e) { return []; }
    }

    // ── Reports ──
    public function reports()
    {
        $this->guard();
        $user = $this->auth->user();

        // Revenue by month (last 12)
        $monthlyRevenue = $this->db->pdo()->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total
            FROM billing_payments WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month ORDER BY month
        ")->fetchAll(\PDO::FETCH_OBJ) ?: [];

        // Invoice stats
        $invoiceStats = $this->db->pdo()->query("
            SELECT status, COUNT(*) as count, SUM(total) as total
            FROM invoices GROUP BY status
        ")->fetchAll(\PDO::FETCH_OBJ) ?: [];

        // Payment methods breakdown
        $paymentMethods = $this->db->pdo()->query("
            SELECT method, COUNT(*) as count, SUM(amount) as total
            FROM billing_payments WHERE status = 'completed'
            GROUP BY method ORDER BY total DESC
        ")->fetchAll(\PDO::FETCH_OBJ) ?: [];

        // Top customers by revenue
        $topCustomers = $this->db->pdo()->query("
            SELECT hu.username, hu.domain, SUM(bp.amount) as total_spent, COUNT(bp.id) as payment_count
            FROM billing_payments bp JOIN hosting_users hu ON bp.user_id = hu.id
            WHERE bp.status = 'completed'
            GROUP BY bp.user_id ORDER BY total_spent DESC LIMIT 10
        ")->fetchAll(\PDO::FETCH_OBJ) ?: [];

        // Product sales
        $productSales = $this->db->pdo()->query("
            SELECT bp.name, COUNT(bo.id) as order_count, COALESCE(SUM(bo.total), 0) as total_revenue
            FROM billing_products bp LEFT JOIN billing_orders bo ON bp.id = bo.product_id AND (bo.status IS NULL OR bo.status != 'cancelled')
            GROUP BY bp.id ORDER BY total_revenue DESC
        ")->fetchAll(\PDO::FETCH_OBJ) ?: [];

        // Coupon usage
        $couponStats = $this->db->pdo()->query("
            SELECT code, used_count, max_uses, value, type
            FROM billing_coupons ORDER BY used_count DESC
        ")->fetchAll(\PDO::FETCH_OBJ) ?: [];

        // Tax collected
        $taxTotal = $this->db->pdo()->query("
            SELECT COALESCE(SUM(bt.rate * i.total / 100), 0) as total_tax
            FROM invoices i JOIN billing_taxes bt ON 1=1
            WHERE i.status = 'paid'
        ")->fetch(\PDO::FETCH_OBJ);

        return $this->view('admin.billing.reports', [
            'user' => $user, 'title' => 'Billing Reports', 'theme_settings' => $this->theme(),
            'monthlyRevenue' => $monthlyRevenue, 'invoiceStats' => $invoiceStats,
            'paymentMethods' => $paymentMethods, 'topCustomers' => $topCustomers,
            'productSales' => $productSales, 'couponStats' => $couponStats,
            'taxTotal' => $taxTotal->total_tax ?? 0,
        ]);
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
        $activeServices = 0; $pendingOrders = 0;
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
        $products = $this->db->table('billing_products')->orderBy('sort_order', 'ASC')->get() ?: [];
        return $this->view('admin.billing.products', ['user' => $user, 'title' => 'Billing Products', 'theme_settings' => $this->theme(), 'products' => $products]);
    }

    public function productStore()
    {
        $this->guard();
        $max = $this->db->table('billing_products')->get() ?: [];
        $sort = count($max) + 1;
        $this->db->table('billing_products')->insertGetId([
            'name' => $this->request->post('name', ''), 'description' => $this->request->post('description', ''),
            'type' => $this->request->post('type', 'hosting'), 'price' => $this->request->post('price', 0),
            'setup_fee' => $this->request->post('setup_fee', 0), 'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'is_active' => $this->request->post('is_active', 1), 'sort_order' => $sort,
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

    public function productSort()
    {
        $this->guard();
        $ids = $this->request->post('ids', '');
        foreach (explode(',', $ids) as $i => $id) {
            $id = (int)trim($id);
            if ($id) $this->db->table('billing_products')->where('id', $id)->update(['sort_order' => $i + 1]);
        }
        $this->response->json(['ok' => true])->send();
        exit;
    }

    // ── Orders ──
    public function orders()
    {
        $this->guard();
        $user = $this->auth->user();
        $orders = $this->db->table('billing_orders')->get() ?: [];
        $hostingUsers = $this->users();
        $userMap = [];
        foreach ($hostingUsers as $h) $userMap[$h->id] = $h;
        return $this->view('admin.billing.orders', ['user' => $user, 'title' => 'Orders', 'theme_settings' => $this->theme(), 'orders' => $orders, 'userMap' => $userMap]);
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
        $invoices = $this->db->pdo()->query("SELECT i.*, hu.username, hu.domain FROM invoices i LEFT JOIN hosting_users hu ON i.user_id = hu.id ORDER BY i.id DESC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $hostingUsers = $this->users();
        $unpaidOrders = $this->db->pdo()->query("SELECT o.*, hu.username, hu.domain FROM billing_orders o LEFT JOIN hosting_users hu ON o.user_id = hu.id WHERE o.status IN ('pending','suspended') ORDER BY o.user_id")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $credits = $this->db->table('billing_credits')->get() ?: [];
        $creditsByUser = [];
        foreach ($credits as $c) $creditsByUser[$c->user_id] = ($creditsByUser[$c->user_id] ?? 0) + $c->amount;
        return $this->view('admin.billing.invoices', [
            'user' => $user, 'title' => 'Invoices', 'theme_settings' => $this->theme(),
            'invoices' => $invoices, 'hostingUsers' => $hostingUsers,
            'unpaidOrders' => $unpaidOrders, 'creditsByUser' => $creditsByUser,
        ]);
    }

    public function invoiceCreate()
    {
        $this->guard();
        $uid = (int)$this->request->post('user_id', 0);
        $total = (float)$this->request->post('total', 0);
        $combine = $this->request->post('combine_unpaid', '');
        $num = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        // If combine, add up unpaid order amounts
        if ($combine && $uid) {
            $orders = $this->db->pdo()->query("SELECT SUM(total) as total FROM billing_orders WHERE user_id = {$uid} AND status IN ('pending','suspended')")->fetch(\PDO::FETCH_OBJ);
            if ($orders && $orders->total > 0) $total += (float)$orders->total;
        }
        $this->db->table('invoices')->insertGetId([
            'user_id' => $uid, 'invoice_number' => $num, 'date' => date('Y-m-d'),
            'due_date' => $this->request->post('due_date', date('Y-m-d', strtotime('+30 days'))),
            'subtotal' => $total, 'total' => $total, 'status' => 'draft',
        ]);
        // Mark combined orders as invoiced
        if ($combine && $uid) {
            $this->db->pdo()->prepare("UPDATE billing_orders SET status = 'invoiced' WHERE user_id = ? AND status IN ('pending','suspended')")->execute([$uid]);
        }
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

    public function invoiceDelete($id)
    {
        $this->guard();
        $this->db->table('invoices')->where('id', $id)->delete();
        $this->response->redirect('/admin/billing/invoices');
    }

    // ── Payments ──
    public function payments()
    {
        $this->guard();
        $user = $this->auth->user();
        $payments = $this->db->pdo()->query("SELECT p.*, hu.username, hu.domain FROM billing_payments p LEFT JOIN hosting_users hu ON p.user_id = hu.id ORDER BY p.id DESC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $hostingUsers = $this->users();
        return $this->view('admin.billing.payments', ['user' => $user, 'title' => 'Payments', 'theme_settings' => $this->theme(), 'payments' => $payments, 'hostingUsers' => $hostingUsers]);
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

    public function paymentDelete($id)
    {
        $this->guard();
        $this->db->table('billing_payments')->where('id', $id)->delete();
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
            'is_active' => 1,
        ]);
        $_SESSION['success_message'] = 'Coupon created.';
        $this->response->redirect('/admin/billing/coupons');
    }

    public function couponUpdate($id)
    {
        $this->guard();
        $this->db->table('billing_coupons')->where('id', $id)->update([
            'code' => strtoupper($this->request->post('code', '')),
            'type' => $this->request->post('type', 'percentage'),
            'value' => (float)$this->request->post('value', 0),
            'max_uses' => (int)$this->request->post('max_uses', 0),
            'min_total' => (float)$this->request->post('min_total', 0),
            'expires_at' => $this->request->post('expires_at'),
            'is_active' => (int)$this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = 'Coupon updated.';
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
        $credits = $this->db->pdo()->query("SELECT c.*, hu.username, hu.domain FROM billing_credits c LEFT JOIN hosting_users hu ON c.user_id = hu.id ORDER BY c.id DESC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $hostingUsers = $this->users();
        return $this->view('admin.billing.credits', ['user' => $user, 'title' => 'Credits', 'theme_settings' => $this->theme(), 'credits' => $credits, 'hostingUsers' => $hostingUsers]);
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

    public function creditUpdate($id)
    {
        $this->guard();
        $this->db->table('billing_credits')->where('id', $id)->update([
            'amount' => (float)$this->request->post('amount', 0),
            'description' => $this->request->post('description', ''),
        ]);
        $_SESSION['success_message'] = 'Credit updated.';
        $this->response->redirect('/admin/billing/credits');
    }

    public function creditDelete($id)
    {
        $this->guard();
        $this->db->table('billing_credits')->where('id', $id)->delete();
        $this->response->redirect('/admin/billing/credits');
    }

    // ── Refunds ──
    public function refunds()
    {
        $this->guard();
        $user = $this->auth->user();
        $refunds = $this->db->pdo()->query("SELECT r.*, hu.username, hu.domain FROM billing_refunds r LEFT JOIN hosting_users hu ON r.user_id = hu.id ORDER BY r.id DESC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        return $this->view('admin.billing.refunds', ['user' => $user, 'title' => 'Refunds', 'theme_settings' => $this->theme(), 'refunds' => $refunds]);
    }

    public function refundDelete($id)
    {
        $this->guard();
        $this->db->table('billing_refunds')->where('id', $id)->delete();
        $this->response->redirect('/admin/billing/refunds');
    }
}