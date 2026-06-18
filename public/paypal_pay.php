<?php
session_start();
$orderId = (int)$_GET['order_id'] ?? 0;
if (!$orderId) { header('Location: /cart.php'); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

$order = $pdo->prepare("SELECT * FROM billing_orders WHERE id = ?");
$order->execute([$orderId]);
$order = $order->fetch(PDO::FETCH_OBJ);
if (!$order) { echo 'Order not found'; exit; }

$paypalEmail = '';
$paypalMode = 'sandbox';
$r = $pdo->query("SELECT setting_key, setting_value FROM automation_settings WHERE setting_key IN ('paypal_email','paypal_mode','paypal_enabled')");
foreach ($r as $row) {
    if ($row['setting_key'] === 'paypal_email') $paypalEmail = $row['setting_value'];
    if ($row['setting_key'] === 'paypal_mode') $paypalMode = $row['setting_value'];
}
$actionUrl = $paypalMode === 'live' ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';
?>
<!DOCTYPE html><html><head><title>Pay Order - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}</style></head><body>
<div class="card" style="max-width:450px;width:100%;text-align:center">
<h2 style="color:var(--accent);margin-bottom:8px">Pay Order #<?php echo $orderId; ?></h2>
<p style="font-size:24px;font-weight:700;margin:12px 0">$<?php echo number_format($order->total, 2); ?>/mo</p>
<form action="<?php echo $actionUrl; ?>" method="POST">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="<?php echo htmlspecialchars($paypalEmail); ?>">
<input type="hidden" name="item_name" value="Order #<?php echo $orderId; ?>">
<input type="hidden" name="item_number" value="order_<?php echo $orderId; ?>">
<input type="hidden" name="amount" value="<?php echo $order->total; ?>">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="return" value="http://<?php echo $_SERVER['HTTP_HOST']; ?>/cart.php?action=thankyou&order=<?php echo $orderId; ?>">
<input type="hidden" name="notify_url" value="http://<?php echo $_SERVER['HTTP_HOST']; ?>/paypal/ipn">
<input type="hidden" name="cancel_return" value="http://<?php echo $_SERVER['HTTP_HOST']; ?>/cart.php">
<button type="submit" class="btn primary" style="font-size:18px;padding:14px 40px">Pay with PayPal &rarr;</button>
</form>
<a href="/cart.php" class="btn secondary" style="margin-top:12px">Cancel</a>
</div></body></html>
