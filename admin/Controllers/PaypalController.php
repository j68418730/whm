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

    protected function setSetting($key, $value)
    {
        $r = $this->db->table('automation_settings')->where('setting_key', $key)->first();
        if ($r) $this->db->table('automation_settings')->where('setting_key', $key)->update(['setting_value' => $value]);
        else $this->db->table('automation_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $value]);
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
            'paypal_live_client_id' => $this->getSetting('paypal_live_client_id', ''),
            'paypal_live_secret' => $this->getSetting('paypal_live_secret', ''),
            'paypal_mode' => $this->getSetting('paypal_mode', 'sandbox'),
            'paypal_enabled' => $this->getSetting('paypal_enabled', '0'),
        ]);
    }

    public function settingsSave()
    {
        $this->guard();
        foreach (['paypal_email','paypal_client_id','paypal_secret','paypal_live_client_id','paypal_live_secret','paypal_mode','paypal_enabled'] as $k) {
            $this->setSetting($k, $this->request->post($k, ''));
        }
        $_SESSION['success_message'] = 'PayPal settings saved.';
        $this->response->redirect('/admin/paypal/settings');
    }

    protected function baseUrl()
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com');
    }

    protected function loadPayable($invoiceId = null)
    {
        // Cart orders carry order_id; invoices carry invoice_id (or route segment)
        $orderId = (int)($this->request->get('order_id', 0) ?: 0);
        $invoiceId = (int)($this->request->get('invoice_id', 0) ?: $invoiceId);
        if ($orderId) {
            $order = $this->db->table('billing_orders')->where('id', $orderId)->first();
            if (!$order) return [null, null, 'Order not found'];
            return [$order, 'order', null];
        }
        if ($invoiceId) {
            $invoice = $this->db->table('invoices')->where('id', $invoiceId)->first();
            if (!$invoice) return [null, null, 'Invoice not found'];
            return [$invoice, 'invoice', null];
        }
        return [null, null, 'No payable reference provided.'];
    }

    protected function renderError($title, $message, $backUrl = '/cart.php')
    {
        echo '<!DOCTYPE html><html><head><title>' . htmlspecialchars($title) . '</title>
        <link rel="stylesheet" href="/theme/assets/css/style.css">
        <style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}</style></head><body>
        <div class="card" style="max-width:450px;width:100%;text-align:center">
        <h2 style="color:var(--accent);margin-bottom:8px">' . htmlspecialchars($title) . '</h2>
        <p style="color:#e2e8f0;margin:12px 0">' . htmlspecialchars($message) . '</p>
        <a href="' . htmlspecialchars($backUrl) . '" class="btn secondary" style="margin-top:12px">Back</a>
        </div></body></html>';
        exit;
    }

    // ── Public Payment Flow (REST Orders v2) ──
    public function pay($invoiceId = null)
    {
        require_once BASE_PATH . '/services/PaypalApi.php';

        list($payable, $type, $err) = $this->loadPayable($invoiceId);
        if (!$payable) $this->renderError('Payment Error', $err);
        if (!paypal_is_enabled()) $this->renderError('Payment Unavailable', 'PayPal is not configured. Please contact support or choose another payment method.');

        $amount = (float)($type === 'order' ? $payable->total : $payable->total);
        $label = $type === 'order' ? ('Order #' . $payable->id) : ('Invoice ' . $payable->invoice_number);
        $ref = $type . '_' . $payable->id;
        $returnUrl = $this->baseUrl() . '/paypal/return?ref_type=' . $type . '&ref_id=' . (int)$payable->id;
        $cancelUrl = $this->baseUrl() . '/paypal/cancel?ref_type=' . $type . '&ref_id=' . (int)$payable->id;
        $mode = paypal_mode();

        $orderJson = paypal_create_order($mode, $amount, $ref, $returnUrl, $cancelUrl, $label);
        $approve = paypal_approve_url($orderJson);
        if (!$approve) {
            $detail = $orderJson['message'] ?? 'Unable to create the PayPal order.';
            $this->renderError('Payment Error', $detail, $type === 'order' ? '/cart.php' : '/user/billing');
        }
        header('Location: ' . $approve);
        exit;
    }

    // PayPal redirects back here after the buyer approves → CAPTURE the payment
    public function returnFlow()
    {
        require_once BASE_PATH . '/services/PaypalApi.php';

        $refType = $this->request->get('ref_type', '');
        $refId = (int)($this->request->get('ref_id', 0) ?: 0);
        $paypalOrderId = $this->request->get('token', '');
        $mode = paypal_mode();

        $backOrder = '/cart.php';
        if ($refType === 'invoice') $backOrder = '/user/billing';

        if (!$paypalOrderId || !$refId) {
            $_SESSION['error_message'] = 'Payment return was missing its reference.';
            header('Location: ' . $backOrder); exit;
        }

        $capture = paypal_capture_order($mode, $paypalOrderId);
        $status = $capture['status'] ?? '';
        if ($status !== 'COMPLETED') {
            $detail = $capture['message'] ?? 'PayPal payment was not completed.';
            $_SESSION['error_message'] = $detail;
            header('Location: ' . $backOrder); exit;
        }

        $txnId = '';
        foreach ($capture['purchase_units'] ?? [] as $pu) {
            foreach ($pu['payments']['captures'] ?? [] as $cap) {
                if (isset($cap['id'])) $txnId = $cap['id'];
            }
        }
        $amount = (float)($capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0);
        $payerEmail = $capture['payer']['email_address'] ?? '';

        $existing = $this->db->table('billing_payments')->where('transaction_id', $txnId)->first();
        if ($existing) {
            $_SESSION['success_message'] = 'Payment already recorded.';
            header('Location: ' . ($refType === 'order' ? '/cart.php?action=thankyou&order=' . $refId : '/user/billing'));
            exit;
        }

        if ($refType === 'order') {
            $order = $this->db->table('billing_orders')->where('id', $refId)->first();
            if ($order) {
                $this->db->table('billing_payments')->insertGetId([
                    'user_id' => $order->user_id, 'amount' => $amount ?: $order->total,
                    'method' => 'paypal', 'status' => 'completed',
                    'transaction_id' => $txnId, 'notes' => "PayPal REST Capture Order #{$refId}: {$payerEmail}",
                ]);
                $this->db->table('billing_orders')->where('id', $refId)->update(['status' => 'active']);
                $this->provisionOrder($order->user_id, $order->items, $refId);
                $_SESSION['success_message'] = 'Payment received! Your services are being provisioned.';
                header('Location: /cart.php?action=thankyou&order=' . $refId);
                exit;
            }
            $_SESSION['error_message'] = 'Order not found.';
            header('Location: /cart.php'); exit;
        }

        if ($refType === 'invoice') {
            $invoice = $this->db->table('invoices')->where('id', $refId)->first();
            if ($invoice) {
                $this->db->table('billing_payments')->insertGetId([
                    'user_id' => $invoice->user_id, 'invoice_id' => $refId,
                    'amount' => $amount ?: $invoice->total, 'method' => 'paypal',
                    'status' => 'completed', 'transaction_id' => $txnId,
                    'notes' => "PayPal REST Capture Invoice {$refId}: {$payerEmail}",
                ]);
                if ($invoice->total <= ($amount ?: $invoice->total)) {
                    $this->db->table('invoices')->where('id', $refId)->update(['status' => 'paid']);
                }
                $_SESSION['success_message'] = 'Invoice paid.';
                header('Location: /user/billing');
                exit;
            }
        }
        $_SESSION['error_message'] = 'Could not locate the payment reference.';
        header('Location: ' . $backOrder);
        exit;
    }

    public function cancelFlow()
    {
        $refType = $this->request->get('ref_type', '');
        $backOrder = $refType === 'invoice' ? '/user/billing' : '/cart.php';
        $_SESSION['error_message'] = 'Payment was cancelled. Nothing was charged.';
        header('Location: ' . $backOrder);
        exit;
    }

    // ── Provisioning shared by REST capture and legacy IPN ──
    protected function provisionOrder($userId, $itemsJson, $orderId = 0)
    {
        $items = json_decode($itemsJson, true);
        if (empty($items)) return;
        require_once BASE_PATH . '/services/AutoProvision.php';
        require_once BASE_PATH . '/services/GameProvision.php';
        $hostingProvisioned = false;
        foreach ($items as $item) {
            $itemType = $item['type'] ?? 'hosting';
            if ($itemType === 'game') {
                gameProvision($orderId, $userId, $item);
            } elseif (!$hostingProvisioned) {
                $pkgId = $item['id'] ?? null;
                if ($pkgId && !is_string($pkgId)) {
                    autoProvision($userId, $pkgId);
                    $pkgCheck = $this->db->table('hosting_packages')->where('id', $pkgId)->first();
                    if ($pkgCheck && stripos($pkgCheck->type ?? '', 'icecast') !== false) {
                        require_once BASE_PATH . '/services/RadioProvision.php';
                        radioProvision($userId, $pkgId);
                    }
                }
                $hostingProvisioned = true;
            }
        }
    }

    // ── PayPal IPN Listener (legacy email flow, kept as fallback) ──
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
                    $this->db->table('billing_orders')->where('id', $orderId)->update(['status' => 'active']);
                    $this->provisionOrder($order->user_id, $order->items, $orderId);
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