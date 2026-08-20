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
        $creditBalance = $this->creditBalance($uid);
        $creditHistory = $uid ? ($this->db->pdo()->query("SELECT 'credit' as kind, amount, description, created_at FROM billing_credits WHERE user_id = {$uid} UNION ALL SELECT 'usage' as kind, amount, description, created_at FROM billing_credit_usage WHERE user_id = {$uid} ORDER BY created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_OBJ) ?: []) : [];
        return $this->view('user.billing', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Billing', 'invoices' => $invoices, 'outstanding' => $totalOutstanding, 'creditBalance' => $creditBalance, 'creditHistory' => $creditHistory]);
    }

    protected function creditBalance($uid)
    {
        if (!$uid) return 0;
        try {
            // billing_credit_usage stores NEGATIVE amounts, so available = credits + sum(usage)
            $totalCredits = (float)($this->db->pdo()->query("SELECT COALESCE(SUM(amount),0) FROM billing_credits WHERE user_id = {$uid}")->fetchColumn() ?? 0);
            $usedCredits = (float)($this->db->pdo()->query("SELECT COALESCE(SUM(amount),0) FROM billing_credit_usage WHERE user_id = {$uid}")->fetchColumn() ?? 0);
            return max(0, $totalCredits + $usedCredits);
        } catch (\Exception $e) { return 0; }
    }

    public function creditsAdd()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        if (!$uid) { $this->response->redirect('/user/billing'); exit; }
        $amount = (float)$this->request->post('amount', 0);
        if ($amount <= 0) { $_SESSION['error'] = 'Enter an amount greater than zero.'; $this->response->redirect('/user/billing'); exit; }
        $num = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        $invId = $this->db->table('invoices')->insertGetId([
            'user_id' => $uid,
            'invoice_number' => $num,
            'date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'subtotal' => $amount,
            'total' => $amount,
            'status' => 'sent',
            'notes' => 'CREDIT_DEPOSIT',
        ]);
        if ($invId) {
            $_SESSION['success'] = "Credit deposit invoice {$num} created for \${$amount}. Pay it and your credit balance will be updated.";
        }
        $this->response->redirect('/user/billing');
    }

    public function useCredits($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $inv = $this->db->table('invoices')->where('id', $id)->where('user_id', $uid)->first();
        if ($inv && ($inv->status === 'sent' || $inv->status === 'overdue')) {
            $available = $this->creditBalance($uid);
            if ($available > 0) {
                $apply = min($available, (float)$inv->total);
                if ($apply > 0) {
                    $newTotal = max(0, (float)$inv->total - $apply);
                    $newCreditApplied = (float)($inv->credit_applied ?? 0) + $apply;
                    $newStatus = $newTotal == 0 ? 'paid' : $inv->status;
                    $this->db->table('invoices')->where('id', $id)->update(['total' => $newTotal, 'credit_applied' => $newCreditApplied, 'status' => $newStatus]);
                    $this->db->table('billing_credit_usage')->insertGetId([
                        'user_id' => $uid,
                        'amount' => -$apply,
                        'description' => 'Applied to invoice ' . $inv->invoice_number,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $_SESSION['success'] = "\${$apply} credit applied to invoice {$inv->invoice_number}.";
                } else {
                    $_SESSION['error'] = 'No credits available.';
                }
            } else {
                $_SESSION['error'] = 'You have no available credit balance.';
            }
        }
        $this->response->redirect('/user/billing');
    }

    public function pay($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $inv = $this->db->table('invoices')->where('id', $id)->where('user_id', $uid)->first();
        if ($inv && ($inv->status === 'sent' || $inv->status === 'overdue')) {
            // Credit deposit invoice: add the amount to the user's balance once paid (idempotent by invoice number)
            if (trim((string)($inv->notes ?? '')) === 'CREDIT_DEPOSIT') {
                $marker = 'Credit deposit invoice ' . $inv->invoice_number;
                $existing = $this->db->pdo()->prepare("SELECT COUNT(*) FROM billing_credits WHERE user_id = ? AND description = ?");
                $existing->execute([$uid, $marker]);
                if ((int)$existing->fetchColumn() === 0) {
                    $this->db->table('billing_credits')->insertGetId([
                        'user_id' => $uid,
                        'amount' => (float)$inv->total,
                        'description' => $marker,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $this->db->table('invoices')->where('id', $id)->update(['status' => 'paid']);
                $_SESSION['success'] = "Invoice #{$inv->invoice_number} paid. \${$inv->total} credit added to your balance.";
            } else {
                // Auto-apply available credits when paying a normal invoice
                $available = $this->creditBalance($uid);
                $apply = min($available, (float)$inv->total);
                if ($apply > 0) {
                    $this->db->table('invoices')->where('id', $id)->update(['total' => max(0, (float)$inv->total - $apply), 'credit_applied' => (float)($inv->credit_applied ?? 0) + $apply, 'status' => 'paid']);
                    $this->db->table('billing_credit_usage')->insertGetId([
                        'user_id' => $uid,
                        'amount' => -$apply,
                        'description' => 'Applied to invoice ' . $inv->invoice_number,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $msg = "Invoice #{$inv->invoice_number} marked as paid. \${$apply} credit applied.";
                } else {
                    $this->db->table('invoices')->where('id', $id)->update(['status' => 'paid']);
                    $msg = "Invoice #{$inv->invoice_number} marked as paid.";
                }
                $_SESSION['success'] = $msg;
            }
        }
        $this->response->redirect('/user/billing');
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
}
