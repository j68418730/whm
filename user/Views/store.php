<style>
.plan-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;margin:14px 0}
.plan-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;transition:.2s}
.plan-card:hover{border-color:rgba(0,140,255,.2);transform:translateY(-2px)}
.plan-card .icon{font-size:36px;margin-bottom:6px}
.plan-card .name{font-size:16px;font-weight:700}
.plan-card .price{font-size:22px;font-weight:800;color:#0A84FF;margin:6px 0}
.plan-card .price small{font-size:12px;color:#64748b}
.plan-card .features{font-size:11px;color:#94a3b8;margin:8px 0;line-height:1.6}
.plan-card .btn-order{padding:8px 20px;border-radius:6px;border:none;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;margin-top:8px}
</style>

<h2>🛒 Order New Services</h2>
<p style="color:#64748b;margin-bottom:14px;font-size:12px">Choose a service to add to your account.</p>

<?php
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$packages = $pdo->query("SELECT * FROM hosting_packages WHERE is_active = 1 ORDER BY sort_order, monthly_price")->fetchAll(PDO::FETCH_OBJ);
$categories = ['web_hosting' => ['🌐','Web Hosting'], 'icecast' => ['📻','Radio Streaming'], 'game' => ['🎮','Game Servers'], 'builder' => ['🏗️','Website Builder'], 'chat' => ['💬','Chat']];
?>

<?php foreach ($packages as $pkg):
$type = $pkg->type ?? 'web_hosting';
$cat = $categories[$type] ?? $categories['web_hosting'];
$features = [];
if ($pkg->email_accounts != 0) $features[] = $pkg->email_accounts < 0 ? 'Unlimited Email' : $pkg->email_accounts . ' Email';
if ($pkg->ftp_accounts != 0) $features[] = $pkg->ftp_accounts < 0 ? 'Unlimited FTP' : $pkg->ftp_accounts . ' FTP';
if ($pkg->databases != 0) $features[] = $pkg->databases < 0 ? 'Unlimited DB' : $pkg->databases . ' DB';
if ($pkg->disk_space > 0) $features[] = $pkg->disk_space . ' MB Disk';
if ($pkg->bandwidth > 0) $features[] = $pkg->bandwidth . ' MB BW';
if ($pkg->icecast_enabled) $features[] = 'Icecast Radio';
if ($pkg->dj_panel_enabled) $features[] = 'DJ Panel';
if ($pkg->live_chat_enabled) $features[] = 'Live Chat';
if ($pkg->game_enabled) $features[] = 'Game Server';
$price = $pkg->monthly_price > 0 ? '$'.number_format($pkg->monthly_price,2) : 'Free';
?>
<div class="plan-card">
<div class="icon"><?php echo $cat[0]; ?></div>
<div class="name"><?php echo htmlspecialchars($pkg->name); ?></div>
<div class="price"><?php echo $price; ?><small>/mo</small></div>
<?php if ($features): ?><div class="features"><?php echo implode(' • ', $features); ?></div><?php endif; ?>
<a href="#" class="btn-order" onclick="return alert('Order submitted! In production, this would create an invoice.')">Order Now</a>
</div>
<?php endforeach; ?>

<div style="text-align:center;margin-top:16px">
<a href="/user/services" class="btn btn-sm btn-secondary">← Back to Services</a>
</div>
