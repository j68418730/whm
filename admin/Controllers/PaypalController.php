<?php

namespace Admin\Controllers;

use Core\Controller;

class PaypalController extends Controller
{
    protected function skipCsrf() { return true; }
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

    protected function getSetting($key, $default = '')
    {
        $r = $this->db->table('automation_settings')->where('setting_key', $key)->first();
        return $r ? $r->setting_value : $default;
    }

    public function settings()
    {
        $this->guard();
        $user = $this->auth->user();
        return $this->view('admin.paypal.settings', [
            'user' => $user, 'title' => 'PayPal Settings',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
            'paypal_email' => $this->getSetting('paypal_email', ''),
            'paypal_client_id' => $this->getSetting('paypal_client_id', ''),
            'paypal_secret' => $this->getSetting('paypal_secret', ''),
            'paypal_mode' => $this->getSetting('paypal_mode', 'sandbox'),
            'paypal_enabled' => $this->getSetting('paypal_enabled', '0'),
        ]);
    }

    public function settingsSave()
    {
        $this->guard();
        foreach (['paypal_email','paypal_client_id','paypal_secret','paypal_mode','paypal_enabled'] as $k) {
            $v = $this->request->post($k, '');
            $r = $this->db->table('automation_settings')->where('setting_key', $k)->first();
            if ($r) $this->db->table('automation_settings')->where('setting_key', $k)->update(['setting_value' => $v]);
            else $this->db->table('automation_settings')->insertGetId(['setting_key' => $k, 'setting_value' => $v]);
        }
        $_SESSION['success_message'] = 'PayPal settings saved.';
        $this->response->redirect('/admin/paypal/settings');
    }

    // ── Public Payment Form ──
    public function pay($invoiceId)
    {
        $invoice = $this->db->table('invoices')->where('id', $invoiceId)->first();
        if (!$invoice) { echo 'Invoice not found'; exit; }
        $paypalEnabled = $this->getSetting('paypal_enabled', '0');
        $paypalEmail = $this->getSetting('paypal_email', '');
        $paypalMode = $this->getSetting('paypal_mode', 'sandbox');
        $actionUrl = $paypalMode === 'live'
            ? 'https://www.paypal.com/cgi-bin/webscr'
            : 'https://www.sandbox.paypal.com/cgi-bin/webscr';

        echo '<!DOCTYPE html><html><head><title>Pay Invoice</title>
        <link rel="stylesheet" href="/theme/assets/css/style.css">
        <style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}</style></head><body>
        <div class="card" style="max-width:450px;width:100%;text-align:center">
        <h2 style="color:var(--accent);margin-bottom:8px">Pay Invoice #' . htmlspecialchars($invoice->invoice_number) . '</h2>
        <p style="font-size:24px;font-weight:700;margin:12px 0">$' . number_format($invoice->total, 2) . '</p>';
        
        if ($paypalEnabled === '1' && $paypalEmail) {
            echo '<form action="' . $actionUrl . '" method="POST">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="' . htmlspecialchars($paypalEmail) . '">
            <input type="hidden" name="item_name" value="Invoice ' . htmlspecialchars($invoice->invoice_number) . '">
            <input type="hidden" name="item_number" value="' . $invoice->id . '">
            <input type="hidden" name="amount" value="' . $invoice->total . '">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="return" value="http://' . $_SERVER['HTTP_HOST'] . '/user/billing">
            <input type="hidden" name="notify_url" value="http://' . $_SERVER['HTTP_HOST'] . '/paypal/ipn">
            <input type="hidden" name="cancel_return" value="http://' . $_SERVER['HTTP_HOST'] . '/user/billing">
            <button type="submit" class="btn primary" style="font-size:18px;padding:14px 40px">Pay with PayPal &rarr;</button>
            </form>';
        } else {
            echo '<p style="color:var(--text-secondary)">PayPal is not configured. Please contact support.</p>';
        }
        echo '<a href="/user/billing" class="btn secondary" style="margin-top:12px">Back</a>
        </div></body></html>';
    }

    // ── PayPal IPN Listener ──
    public function ipn()
    {
        $raw = file_get_contents('php://input');
        $data = $_POST;
        
        // Verify with PayPal
        $data['cmd'] = '_notify-validate';
        $paypalMode = $this->getSetting('paypal_mode', 'sandbox');
        $paypalUrl = $paypalMode === 'live'
            ? 'https://ipnpb.paypal.com/cgi-bin/webscr'
            : 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        
        $ch = curl_init($paypalUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === 'VERIFIED' && ($data['payment_status'] ?? '') === 'Completed') {
            $itemNumber = $data['item_number'] ?? '';
            $txnId = $data['txn_id'] ?? '';
            $amount = (float)($data['mc_gross'] ?? 0);
            $payerEmail = $data['payer_email'] ?? '';
            
            $existing = $this->db->table('billing_payments')
                ->where('transaction_id', $txnId)->first();
            if ($existing) { http_response_code(200); echo 'OK'; exit; }
            
            // Handle order payments (from cart)
            if (strpos($itemNumber, 'order_') === 0) {
                $orderId = (int)substr($itemNumber, 6);
                $order = $this->db->table('billing_orders')->where('id', $orderId)->first();
                if ($order) {
                    $this->db->table('billing_payments')->insertGetId([
                        'user_id' => $order->user_id, 'amount' => $amount,
                        'method' => 'paypal', 'status' => 'completed',
                        'transaction_id' => $txnId, 'notes' => "PayPal IPN Order #{$orderId}: {$payerEmail}",
                    ]);
                    $this->db->table('billing_orders')->where('id', $orderId)->update(['status' => 'paid']);
                    
                    // Auto-provision: create account for the first item in the order
                    $items = json_decode($order->items, true);
                    if (!empty($items)) {
                        require_once BASE_PATH . '/services/AutoProvision.php';
                        $pkgId = $items[0]['id'];
                        autoProvision($order->user_id, $pkgId);
                        // Check if radio package — auto-create Icecast stream
                        $pkgCheck = $this->db->table('hosting_packages')->where('id', $pkgId)->first();
                        if ($pkgCheck && stripos($pkgCheck->type ?? '', 'icecast') !== false) {
                            require_once BASE_PATH . '/services/RadioProvision.php';
                            radioProvision($order->user_id, $pkgId);
                        }
                    }
                }
            } else {
                // Handle invoice payments (existing)
                $invoiceId = (int)$itemNumber;
                if ($invoiceId) {
                    $invoice = $this->db->table('invoices')->where('id', $invoiceId)->first();
                    if ($invoice) {
                        $this->db->table('billing_payments')->insertGetId([
                            'user_id' => $invoice->user_id, 'invoice_id' => $invoiceId,
                            'amount' => $amount, 'method' => 'paypal',
                            'status' => 'completed', 'transaction_id' => $txnId,
                            'notes' => "PayPal IPN: {$payerEmail}",
                        ]);
                        if ($invoice->total <= $amount) {
                            $this->db->table('invoices')->where('id', $invoiceId)->update(['status' => 'paid']);
                        }
                    }
                }
            }
        }
        
        http_response_code(200);
        echo 'OK';
        exit;
    }
}
