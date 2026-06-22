<?php
session_start();
$action = $_GET['action'] ?? 'view';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Initialize cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// ─── Add to Cart ───
if ($action === 'add' && isset($_GET['package'])) {
    $pkgId = (int)$_GET['package'];
    $pkg = $pdo->prepare("SELECT * FROM hosting_packages WHERE id = ? AND is_active = 1");
    $pkg->execute([$pkgId]);
    $pkg = $pkg->fetch(PDO::FETCH_OBJ);
    if ($pkg) {
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $pkgId) { $item['qty']++; $found = true; break; }
        }
        if (!$found) $_SESSION['cart'][] = ['id' => $pkgId, 'name' => $pkg->name, 'price' => (float)$pkg->monthly_price, 'qty' => 1];
    }
    header('Location: /cart.php');
    exit;
}

// ─── Add Game Server to Cart ───
if ($action === 'add_game' && isset($_GET['game_id'])) {
    $gameId = (int)$_GET['game_id'];
    $slots = (int)($_GET['slots'] ?? 10);
    $price = (float)($_GET['price'] ?? 0);
    $setup = (float)($_GET['setup'] ?? 0);
    $pps = (float)($_GET['pps'] ?? 0);
    $pkgId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;
    $pkgName = $_GET['pkg_name'] ?? '';

    $game = $pdo->prepare("SELECT * FROM game_types WHERE id = ? AND is_active = 1");
    $game->execute([$gameId]);
    $game = $game->fetch(PDO::FETCH_OBJ);
    if ($game) {
        $name = $game->name . ($pkgName ? ' - ' . $pkgName : '') . ' (' . $slots . ' slots)';
        $_SESSION['cart'][] = [
            'type' => 'game',
            'game_id' => $gameId,
            'slots' => $slots,
            'price_per_slot' => $pps,
            'setup' => $setup,
            'package_id' => $pkgId,
            'id' => 'game_' . $gameId . '_' . $slots,
            'name' => $name,
            'price' => $price + $setup,
            'qty' => 1,
        ];
    }
    header('Location: /cart.php');
    exit;
}

// ─── Remove from Cart ───
if ($action === 'remove' && isset($_GET['index'])) {
    $idx = (int)$_GET['index'];
    if (isset($_SESSION['cart'][$idx])) array_splice($_SESSION['cart'], $idx, 1);
    header('Location: /cart.php');
    exit;
}

// ─── Update Qty ───
if ($action === 'update' && $_POST) {
    foreach ($_POST['qty'] ?? [] as $idx => $qty) {
        if (isset($_SESSION['cart'][$idx])) $_SESSION['cart'][$idx]['qty'] = max(1, (int)$qty);
    }
    header('Location: /cart.php');
    exit;
}

// ─── Checkout Submit ───
if ($action === 'checkout' && $_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $method = $_POST['method'] ?? 'paypal';
    $errors = [];
    if (!$name) $errors[] = 'Name is required';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required';
    if (strlen($password) < 8) $errors[] = 'Password must be 8+ characters';
    if (empty($_SESSION['cart'])) $errors[] = 'Cart is empty';

    if (empty($errors)) {
        // Check if user exists, if not create
        $existing = $pdo->prepare("SELECT id FROM hosting_users WHERE email = ?");
        $existing->execute([$email]);
        $user = $existing->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            $username = explode('@', $email)[0] . rand(100, 999);
            $pdo->prepare("INSERT INTO hosting_users (username, email, password_hash, name, status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())")->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $name]);
            $userId = $pdo->lastInsertId();
        } else {
            $userId = $user->id;
        }

        // Create order
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $_SESSION['cart']));
        $hasGame = false;
        foreach ($_SESSION['cart'] as $item) {
            if (($item['type'] ?? '') === 'game') { $hasGame = true; break; }
        }
        $itemsJson = json_encode($_SESSION['cart']);
        $pdo->prepare("INSERT INTO billing_orders (user_id, items, total, payment_method, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())")->execute([$userId, $itemsJson, $total, $method]);
        $orderId = $pdo->lastInsertId();

        if ($method === 'paypal') {
            // Redirect to PayPal
            header('Location: /paypal/pay?order_id=' . $orderId);
            exit;
        } else {
            // Manual - needs admin approval
            $_SESSION['order_id'] = $orderId;
            header('Location: /cart.php?action=thankyou&order=' . $orderId);
            exit;
        }
    }
    $_SESSION['cart_errors'] = $errors;
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Shopping Cart - Planet Hosts</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#02050e;color:#fff;font-family:'Inter',sans-serif}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.92),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.container{max-width:800px;margin:0 auto;padding:24px}
h1{font-size:24px;margin-bottom:20px}h1 span{color:#008cff}
.card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:24px;margin-bottom:16px}
table{width:100%;border-collapse:collapse}
th,td{padding:12px 8px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);font-size:14px}
th{color:#64748b;font-size:12px;text-transform:uppercase}
.btn{padding:10px 24px;border-radius:8px;border:none;font-weight:600;font-size:14px;cursor:pointer;transition:.3s;text-decoration:none;display:inline-block;font-family:'Inter',sans-serif}
.btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 0 25px rgba(0,140,255,.3)}
.btn-secondary{background:#333;color:#ccc}
.btn-danger{background:rgba(248,113,113,.15);color:#f87171}
input,select{width:100%;padding:10px 14px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:8px;color:#fff;font-size:14px;outline:none;font-family:'Inter',sans-serif;box-sizing:border-box}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600}
.qty-input{width:60px;padding:6px;text-align:center}
.total-row{font-size:18px;font-weight:700;color:#4ade80}
.alert{padding:12px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
.alert-success{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80}
.thankyou{text-align:center;padding:40px 0}
.thankyou .icon{font-size:64px;margin-bottom:16px}
</style></head><body>
<div class="bg"></div>
<div class="container">

<?php if ($action === 'thankyou'): ?>
<div class="card thankyou">
<div class="icon">🎉</div>
<h2>Order Placed!</h2>
<p style="color:#64748b;margin:12px 0">Your order #<?php echo (int)($_GET['order'] ?? 0); ?> has been received.</p>
<p style="color:#64748b;font-size:13px">If you paid via PayPal, your account will be provisioned automatically once payment clears.<br>
If you selected manual payment, an admin will review and activate your account.</p>
<a href="/" class="btn btn-primary" style="margin-top:16px">Back to Home</a>
<?php unset($_SESSION['cart']); ?>
</div>
<?php exit; endif; ?>

<h1>🛒 Shopping <span>Cart</span></h1>

<?php if (!empty($_SESSION['cart_errors'])): ?>
<div class="alert alert-error"><?php echo implode('<br>', $_SESSION['cart_errors']); unset($_SESSION['cart_errors']); ?></div>
<?php endif; ?>

<?php if (empty($_SESSION['cart'])): ?>
<div class="card" style="text-align:center;padding:40px">
<div style="font-size:48px;margin-bottom:12px">🛒</div>
<p style="color:#64748b;margin-bottom:16px">Your cart is empty.</p>
<a href="/" class="btn btn-primary">Browse Plans</a>
<a href="/game-servers.php" class="btn btn-primary" style="margin-left:8px">Game Servers</a>
</div>
<?php else:
$total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $_SESSION['cart']));
?>
<form method="POST" action="/cart.php?action=update">
<table>
<tr><th>Item</th><th>Type</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
<?php foreach ($_SESSION['cart'] as $idx => $item): ?>
<tr>
<td><strong><?php echo htmlspecialchars($item['name']); ?></strong>
<?php if (($item['type'] ?? '') === 'game'): ?>
<br><small style="color:#64748b"><?php echo $item['slots']; ?> slots @ $<?php echo number_format($item['price_per_slot'] ?? 0, 2); ?>/slot</small>
<?php endif; ?>
</td>
<td><?php echo ($item['type'] ?? 'hosting') === 'game' ? '🎮 Game' : '📦 Hosting'; ?></td>
<td>$<?php echo number_format($item['price'], 2); ?><?php echo $item['setup'] > 0 ? ' + $'.number_format($item['setup'], 2).' setup' : ''; ?></td>
<td><input type="number" name="qty[<?php echo $idx; ?>]" value="<?php echo $item['qty']; ?>" min="1" class="qty-input" onchange="this.form.submit()"></td>
<td>$<?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
<td><a href="/cart.php?action=remove&index=<?php echo $idx; ?>" class="btn btn-danger" style="padding:4px 10px;font-size:12px">✕</a></td>
</tr>
<?php endforeach; ?>
</table>
<div style="text-align:right;margin-top:16px">
<div class="total-row">Total: $<?php echo number_format($total, 2); ?></div>
</div>
</form>

<div class="card" style="margin-top:20px">
<h3 style="margin-bottom:16px">Checkout</h3>
<form method="POST" action="/cart.php?action=checkout">
<div class="form-group"><label>Full Name</label><input name="name" required></div>
<div class="form-group"><label>Email Address</label><input name="email" type="email" required></div>
<div class="form-group"><label>Password (for your account)</label><input name="password" type="password" minlength="8" required></div>
<div class="form-group"><label>Payment Method</label>
<select name="method">
<option value="paypal">PayPal</option>
<option value="manual">Bank Transfer / CashApp (Manual)</option>
</select>
</div>
<button type="submit" class="btn btn-primary" style="width:100%">Place Order — $<?php echo number_format($total, 2); ?>/mo</button>
</form>
</div>
<?php endif; ?>

<p style="text-align:center;margin-top:20px;display:flex;justify-content:center;gap:16px">
<a href="/" style="color:#64748b;font-size:13px">← Browse Hosting</a>
<a href="/game-servers.php" style="color:#64748b;font-size:13px">← Game Servers</a>
</p>
</div></body></html>
