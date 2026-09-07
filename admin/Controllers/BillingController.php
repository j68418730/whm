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
        $pdo = $this->db->pdo();

        $totalRevenue = (float)($pdo->query("SELECT COALESCE(SUM(amount),0) FROM billing_payments WHERE status='completed'")->fetchColumn() ?? 0);
        $activeServices = (int)($pdo->query("SELECT COUNT(*) FROM billing_services WHERE status='active'")->fetchColumn() ?? 0);
        $pendingOrders = (int)($pdo->query("SELECT COUNT(*) FROM billing_orders WHERE status='pending'")->fetchColumn() ?? 0);
        $totalInvoices = (int)($pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn() ?? 0);

        $productCount = (int)($pdo->query("SELECT COUNT(*) FROM billing_products")->fetchColumn() ?? 0);
        $orderCount = (int)($pdo->query("SELECT COUNT(*) FROM billing_orders")->fetchColumn() ?? 0);
        $serviceCount = (int)($pdo->query("SELECT COUNT(*) FROM billing_services")->fetchColumn() ?? 0);
        $invoiceCount = $totalInvoices;
        $paymentCount = (int)($pdo->query("SELECT COUNT(*) FROM billing_payments")->fetchColumn() ?? 0);
        $taxCount = (int)($pdo->query("SELECT COUNT(*) FROM billing_taxes")->fetchColumn() ?? 0);
        $couponCount = (int)($pdo->query("SELECT COUNT(*) FROM billing_coupons")->fetchColumn() ?? 0);
        $creditCount = (int)($pdo->query("SELECT COUNT(*) FROM billing_credits")->fetchColumn() ?? 0);
        $refundCount = (int)($pdo->query("SELECT COUNT(*) FROM billing_refunds")->fetchColumn() ?? 0);

        $totalCollected = (float)($pdo->query("SELECT COALESCE(SUM(amount),0) FROM billing_payments WHERE status='completed'")->fetchColumn() ?? 0);
        $outstandingBalance = (float)($pdo->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status IN ('sent','overdue')")->fetchColumn() ?? 0);
        $monthlyRecurring = (float)($pdo->query("SELECT COALESCE(SUM(price),0) FROM billing_services WHERE status='active'")->fetchColumn() ?? 0);

        return $this->view('admin.billing.index', [
            'user' => $user, 'title' => 'Billing', 'theme_settings' => $this->theme(),
            'totalRevenue' => $totalRevenue, 'activeServices' => $activeServices,
            'pendingOrders' => $pendingOrders, 'totalInvoices' => $totalInvoices,
            'productCount' => $productCount, 'orderCount' => $orderCount,
            'serviceCount' => $serviceCount, 'invoiceCount' => $invoiceCount,
            'paymentCount' => $paymentCount, 'taxCount' => $taxCount,
            'couponCount' => $couponCount, 'creditCount' => $creditCount,
            'refundCount' => $refundCount,
            'totalCollected' => $totalCollected, 'outstandingBalance' => $outstandingBalance,
            'monthlyRecurring' => $monthlyRecurring,
        ]);
    }

    // ── Shopping Cart Integration ──
    public function cart()
    {
        $this->guard();
        $user = $this->auth->user();
        $products = $this->db->table('billing_products')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get() ?: [];
        return $this->view('admin.billing.cart', [
            'user' => $user, 'title' => 'Shopping Cart', 'theme_settings' => $this->theme(),
            'products' => $products,
            'themes' => \Admin\Services\CartThemes::all(),
            'cartSettings' => $this->cartSettingsAll(),
        ]);
    }

    // ── Cart Themes & Settings ──
    protected function cartSettingsAll(): array
    {
        $defaults = [
            'cart_theme' => 'planethosts',
            'cart_currency' => 'USD',
            'cart_show_images' => '1',
            'cart_guest_checkout' => '1',
            'cart_footer_message' => '',
            'cart_logo_url' => '',
            'cart_header_image' => '',
        ];
        $out = [];
        foreach ($defaults as $k => $d) {
            $row = $this->db->table('setup_settings')->where('setting_key', $k)->first();
            $out[$k] = $row->setting_value ?? $d;
        }
        return $out;
    }

    protected function cartSetting($key, $value)
    {
        $existing = $this->db->table('setup_settings')->where('setting_key', $key)->first();
        if ($existing) {
            $this->db->table('setup_settings')->where('setting_key', $key)->update(['setting_value' => $value]);
        } else {
            $this->db->table('setup_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    public function cartThemeUse($id)
    {
        $this->guard();
        $theme = \Admin\Services\CartThemes::get($id);
        if ($theme) {
            $this->cartSetting('cart_theme', $theme['id']);
            $_SESSION['success_message'] = 'Default cart theme set to "' . $theme['name'] . '".';
        }
        $this->response->redirect('/admin/billing/cart?tab=themes');
    }

    public function cartSettings()
    {
        $this->guard();
        $theme = $this->request->post('cart_theme', 'planethosts');
        $currency = $this->request->post('cart_currency', 'USD');
        if (!in_array($currency, ['USD', 'EUR', 'GBP'], true)) {
            $currency = 'USD';
        }
        $this->cartSetting('cart_theme', \Admin\Services\CartThemes::get($theme)['id']);
        $this->cartSetting('cart_currency', $currency);
        $this->cartSetting('cart_show_images', $this->request->post('show_images', '1') ? '1' : '0');
        $this->cartSetting('cart_guest_checkout', $this->request->post('guest_checkout', '1') ? '1' : '0');
        $this->cartSetting('cart_footer_message', trim((string)$this->request->post('footer_message', '')));
        $this->cartSetting('cart_logo_url', trim((string)$this->request->post('logo_url', '')));
        $this->cartSetting('cart_header_image', trim((string)$this->request->post('header_image', '')));
        $_SESSION['success_message'] = 'Cart settings saved.';
        $this->response->redirect('/admin/billing/cart?tab=settings');
    }

    // ── Public Storefront (/store) ──
    public function store()
    {
        $settings = $this->cartSettingsAll();
        $theme = $settings['cart_theme'] ?? 'planethosts';
        try {
            $products = $this->db->table('billing_products')->where('is_active', 1)->where('is_visible', 1)->orderBy('sort_order', 'ASC')->get() ?: [];
        } catch (\Throwable $e) {
            $products = $this->db->table('billing_products')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get() ?: [];
        }
        $css = \Admin\Services\CartThemes::fullCss($theme);

        header('Content-Type: text/html; charset=utf-8');
        $links = '';
        if (\Admin\Services\CartThemes::isBootstrap($theme)) {
            $links .= '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        }
        $icons = \Admin\Services\CartThemes::icons($theme);
        if ($icons === 'fa') {
            $links .= '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">';
        } elseif ($icons === 'bi') {
            $links .= '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">';
        }
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . htmlspecialchars(primary_domain()) . ' Store</title>' . $links
            . '<style>' . $css . '</style>'
            . '</head><body style="margin:0;background:var(--phc-bg)">';
        echo $this->storeHtml($products, $theme, $settings);
        echo '</body></html>';
        exit;
    }

    protected function storeHtml($products, string $theme, array $settings): string
    {
        $t = \Admin\Services\CartThemes::get($theme);
        $currency = $settings['cart_currency'] ?? 'USD';
        $symbol = $currency === 'EUR' ? '€' : ($currency === 'GBP' ? '£' : '$');
        $footerMsg = trim($settings['cart_footer_message'] ?? '');
        if ($footerMsg === '') {
            $footerMsg = \Admin\Services\CartThemes::defaultFooter($theme);
        }
        $logoUrl = trim($settings['cart_logo_url'] ?? '');
        $headerImage = trim($settings['cart_header_image'] ?? '');
        $cartIcon = $this->cartIcon($t['icons'] ?? 'fa');

        $logoHtml = $logoUrl !== '' ? '<img class="phc-logo" src="' . htmlspecialchars($logoUrl) . '" alt="logo" onerror="this.remove()">' : '';
        $brandText = '<span class="phc-logo-text">' . htmlspecialchars($t['logo_a'] ?? '') . ($t['logo_b'] !== '' ? '<b>' . htmlspecialchars($t['logo_b']) . '</b>' : '') . '</span>';
        if (!empty($t['header_sub'])) {
            $brandText .= '<div class="phc-header-sub">' . htmlspecialchars($t['header_sub']) . '</div>';
        }
        $headerBg = $headerImage !== '' ? ' style="background:url(' . htmlspecialchars($headerImage) . ') center/cover no-repeat;border:none"' : '';

        $items = '';
        foreach ($products as $p) {
            $items .= '<div class="phc-product" data-id="' . (int)$p->id . '" data-name="' . htmlspecialchars($p->name ?? '') . '" data-price="' . (float)$p->price . '">'
                . '<div class="phc-name">' . htmlspecialchars($p->name ?? '') . '</div>'
                . '<div class="phc-desc">' . htmlspecialchars(mb_strimwidth((string)($p->description ?? ''), 0, 70, '…')) . '</div>'
                . '<div class="phc-price">' . $symbol . number_format((float)$p->price, 2) . ' <small>/' . htmlspecialchars($p->billing_cycle ?? 'mo') . '</small></div>'
                . '<button class="phc-btn" onclick="addToCart(' . (int)$p->id . ')">' . $cartIcon . ' Add to Cart</button>'
                . '</div>';
        }
        $store = '<div class="ph-store phc-' . htmlspecialchars($theme) . '">'
            . '<header class="phc-header"' . $headerBg . '><div class="phc-brand">' . $logoHtml . '<div>' . $brandText . '</div></div><span class="phc-cart-badge" id="phc-count">0 items</span></header>'
            . '<div class="phc-grid">' . ($items ?: '<div class="phc-empty">No products available.</div>') . '</div>'
            . '<div class="phc-cart"><h4>' . $cartIcon . ' Your Cart</h4><div id="phc-cart-items" class="phc-empty">Cart is empty.</div>'
            . '<div class="phc-total">Total: <span id="phc-total">' . $symbol . '0.00</span></div></div>'
            . '<footer class="phc-footer">' . htmlspecialchars($footerMsg) . '</footer>'
            . '<div class="phc-foot">⚡ Powered by Planet Hosts</div>'
            . '</div>';
        $store .= '<script>
            var PHC_PRODUCTS=' . json_encode(array_map(function ($p) {
                return ['id' => (int)$p->id, 'name' => (string)$p->name, 'price' => (float)$p->price];
            }, $products)) . ';
            var PHC_CART = {};
            function money(n){var s=' . json_encode($symbol) . ';return s+n.toFixed(2);}
            function addToCart(id){var p=PHC_PRODUCTS.find(function(x){return x.id===id;});if(!p)return;PHC_CART[id]=(PHC_CART[id]||0)+1;renderCart();}
            function renderCart(){var el=document.getElementById("phc-cart-items");var rows=Object.keys(PHC_CART).map(function(id){var p=PHC_PRODUCTS.find(function(x){return x.id===+id;});var q=PHC_CART[id];return "<div class=\"phc-item\"><span>"+p.name+" x"+q+"</span><span>"+money(p.price*q)+"</span></div>";}).join("");var total=Object.keys(PHC_CART).reduce(function(t,id){var p=PHC_PRODUCTS.find(function(x){return x.id===+id;});return t+p.price*PHC_CART[id];},0);var count=Object.keys(PHC_CART).reduce(function(c,id){return c+PHC_CART[id];},0);el.innerHTML=rows||"<div class=\"phc-empty\">Cart is empty.</div>";document.getElementById("phc-total").textContent=money(total);var b=document.getElementById("phc-count");if(b)b.textContent=count+" item"+(count===1?"":"s");}
        </script>';
        return $store;
    }

    protected function cartIcon(string $icons): string
    {
        if ($icons === 'fa') {
            return '<i class="fa-solid fa-cart-shopping"></i>';
        }
        if ($icons === 'bi') {
            return '<i class="bi bi-cart"></i>';
        }
        if ($icons === 'emoji') {
            return '🛒';
        }
        return '';
    }

    public function storeThemeCss()
    {
        $theme = $_GET['id'] ?? 'planethosts';
        header('Content-Type: text/css; charset=utf-8');
        header('Cache-Control: public, max-age=300');
        echo \Admin\Services\CartThemes::fullCss($theme);
        exit;
    }

    public function storeEmbedJs()
    {
        $settings = $this->cartSettingsAll();
        $theme = $settings['cart_theme'] ?? 'planethosts';
        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: public, max-age=300');
        try {
            $products = $this->db->table('billing_products')->where('is_active', 1)->where('is_visible', 1)->orderBy('sort_order', 'ASC')->get() ?: [];
        } catch (\Throwable $e) {
            $products = $this->db->table('billing_products')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get() ?: [];
        }
        $json = json_encode(array_map(function ($p) {
            return ['id' => (int)$p->id, 'name' => (string)$p->name, 'desc' => (string)$p->description, 'price' => (float)$p->price, 'cycle' => $p->billing_cycle ?? 'mo'];
        }, $products));

        $t = \Admin\Services\CartThemes::get($theme);
        $footerMsg = trim($settings['cart_footer_message'] ?? '');
        if ($footerMsg === '') {
            $footerMsg = \Admin\Services\CartThemes::defaultFooter($theme);
        }
        $currency = $settings['cart_currency'] ?? 'USD';
        $symbol = $currency === 'EUR' ? '€' : ($currency === 'GBP' ? '£' : '$');
        $logoA = $t['logo_a'] ?? '';
        $logoB = $t['logo_b'] ?? '';
        $headerSub = $t['header_sub'] ?? '';
        $icons = $t['icons'] ?? 'fa';
        $cartIcon = $this->cartIcon($icons);
        $bootstrap = (int)\Admin\Services\CartThemes::isBootstrap($theme);

        echo <<<JS
(function(){
  var PRODUCTS = $json;
  var THEME = {$this->jsonStr($theme)};
  var SYM = {$this->jsonStr($symbol)};
  var LOGO_A = {$this->jsonStr($logoA)};
  var LOGO_B = {$this->jsonStr($logoB)};
  var HEADER_SUB = {$this->jsonStr($headerSub)};
  var FOOTER_MSG = {$this->jsonStr($footerMsg)};
  var CART_ICON = {$this->jsonStr($cartIcon)};
  var IS_BOOTSTRAP = $bootstrap;
  var CONTAINER_SELECTOR = '#ph-cart';
  var CONTAINER = null;
  var CART = {};
  function m(n){ return SYM + n.toFixed(2); }
  function loadCss(href){ var l=document.createElement('link'); l.rel='stylesheet'; l.href=href; document.head.appendChild(l); }
  function load(){
    CONTAINER = document.querySelector(CONTAINER_SELECTOR || '#ph-cart');
    if(!CONTAINER) return;
    loadCss('/storefront/theme.css?id='+THEME);
    if(IS_BOOTSTRAP) loadCss('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    if('$icons'==='fa') loadCss('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
    if('$icons'==='bi') loadCss('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css');
    CONTAINER.className='ph-store phc-'+THEME;
    CONTAINER.innerHTML='<header class="phc-header"><div class="phc-brand"><div><span class="phc-logo-text">'+LOGO_A+(LOGO_B?'<b>'+LOGO_B+'</b>':'')+'</span>'+(HEADER_SUB?'<div class="phc-header-sub">'+HEADER_SUB+'</div>':'')+'</div></div><span class="phc-cart-badge" id="phc-count">0 items</span></header>'+
      '<div class="phc-grid">'+PRODUCTS.map(function(p){
        return '<div class="phc-product"><div class="phc-name">'+p.name+'</div><div class="phc-desc">'+p.desc+'</div><div class="phc-price">'+m(p.price)+' <small>/'+p.cycle+'</small></div><button class="phc-btn" onclick="PHCart.add('+p.id+')">'+CART_ICON+' Add to Cart</button></div>';
      }).join('')+'</div>'+
      '<div class="phc-cart"><h4>'+CART_ICON+' Your Cart</h4><div id="phc-items" class="phc-empty">Cart is empty.</div><div class="phc-total">Total: <span id="phc-total">'+SYM+'0.00</span></div></div>'+
      '<footer class="phc-footer">'+FOOTER_MSG+'</footer><div class="phc-foot">⚡ Powered by Planet Hosts</div>';
  }
  window.PHCart = {
    init: function(opts){ CONTAINER_SELECTOR = (opts && opts.container) || '#ph-cart'; THEME = (opts && opts.theme) || THEME; load(); },
    add: function(id){ var p=PRODUCTS.find(function(x){return x.id===id;}); if(!p)return; CART[id]=(CART[id]||0)+1; render(); },
    render: render
  };
  function render(){
    var el=CONTAINER&&CONTAINER.querySelector('#phc-items'); if(!el)return;
    var rows=Object.keys(CART).map(function(id){ var p=PRODUCTS.find(function(x){return x.id===+id;}); var q=CART[id]; return '<div class="phc-item"><span>'+p.name+' x'+q+'</span><span>'+m(p.price*q)+'</span></div>'; }).join('');
    var total=Object.keys(CART).reduce(function(t,id){ var p=PRODUCTS.find(function(x){return x.id===+id;}); return t+p.price*CART[id]; },0);
    var count=Object.keys(CART).reduce(function(c,id){ return c+CART[id]; },0);
    el.innerHTML=rows||'<div class="phc-empty">Cart is empty.</div>';
    var t=CONTAINER.querySelector('#phc-total'); if(t)t.textContent=m(total);
    var b=CONTAINER.querySelector('#phc-count'); if(b)b.textContent=count+' item'+(count===1?'':'s');
  }
  if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', load); } else { load(); }
})();
JS;
        exit;
    }

    protected function jsonStr($v)
    {
        return json_encode($v);
    }

    // ── Products ──
    public function products()
    {
        $this->guard();
        $user = $this->auth->user();
        $products = $this->db->table('billing_products')->orderBy('sort_order', 'ASC')->get() ?: [];

        // Order/service counts per product
        $orderCounts = [];
        try {
            foreach ($this->db->pdo()->query("SELECT product_id, COUNT(*) c FROM billing_orders GROUP BY product_id")->fetchAll(\PDO::FETCH_OBJ) as $r) {
                $orderCounts[(int)$r->product_id] = (int)$r->c;
            }
        } catch (\Exception $e) {}
        $serviceCounts = [];
        try {
            foreach ($this->db->pdo()->query("SELECT product_id, COUNT(*) c FROM billing_services GROUP BY product_id")->fetchAll(\PDO::FETCH_OBJ) as $r) {
                $serviceCounts[(int)$r->product_id] = (int)$r->c;
            }
        } catch (\Exception $e) {}

        // Available hosting packages for linking (package_id)
        $packages = [];
        try { $packages = $this->db->table('hosting_packages')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: []; } catch (\Exception $e) {}

        // Billing categories
        $billingCats = [];
        try { $billingCats = $this->db->table('billing_categories')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get() ?: []; } catch (\Exception $e) {}

        return $this->view('admin.billing.products', [
            'user' => $user, 'title' => 'Billing Products', 'theme_settings' => $this->theme(),
            'products' => $products, 'orderCounts' => $orderCounts, 'serviceCounts' => $serviceCounts,
            'packages' => $packages, 'billingCats' => $billingCats,
        ]);
    }

    public function productStore()
    {
        $this->guard();
        $max = $this->db->table('billing_products')->get() ?: [];
        $sort = count($max) + 1;
        $this->db->table('billing_products')->insertGetId([
            'name' => $this->request->post('name', ''), 'description' => $this->request->post('description', ''),
            'type' => $this->request->post('type', 'hosting'),
            'category' => $this->request->post('category', $this->request->post('type', 'hosting')),
            'price' => $this->request->post('price', 0),
            'setup_fee' => $this->request->post('setup_fee', 0), 'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'package_id' => $this->request->post('package_id') ? (int)$this->request->post('package_id') : null,
            'license_key' => $this->request->post('license_key', ''),
            'image' => $this->request->post('image', ''),
            'is_active' => $this->request->post('is_active', 1),
            'is_visible' => $this->request->post('is_visible', 1),
            'sort_order' => $sort,
        ]);
        $_SESSION['success_message'] = 'Product created.';
        $this->response->redirect('/admin/billing/products');
    }

    public function productUpdate($id)
    {
        $this->guard();
        $this->db->table('billing_products')->where('id', $id)->update([
            'name' => $this->request->post('name', ''), 'description' => $this->request->post('description', ''),
            'type' => $this->request->post('type', 'hosting'),
            'category' => $this->request->post('category', $this->request->post('type', 'hosting')),
            'price' => $this->request->post('price', 0),
            'setup_fee' => $this->request->post('setup_fee', 0), 'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'package_id' => $this->request->post('package_id') ? (int)$this->request->post('package_id') : null,
            'license_key' => $this->request->post('license_key', ''),
            'image' => $this->request->post('image', ''),
            'is_active' => $this->request->post('is_active', 1),
            'is_visible' => $this->request->post('is_visible', 1),
        ]);
        $_SESSION['success_message'] = 'Product updated.';
        $this->response->redirect('/admin/billing/products');
    }

    public function productToggle($id)
    {
        $this->guard();
        $p = $this->db->table('billing_products')->where('id', $id)->first();
        if ($p) {
            $this->db->table('billing_products')->where('id', $id)->update(['is_active' => $p->is_active ? 0 : 1]);
        }
        $this->response->redirect('/admin/billing/products');
    }

    public function productToggleVisible($id)
    {
        $this->guard();
        $p = $this->db->table('billing_products')->where('id', $id)->first();
        if ($p) {
            $this->db->table('billing_products')->where('id', $id)->update(['is_visible' => $p->is_visible ? 0 : 1]);
        }
        $this->response->redirect('/admin/billing/products');
    }

    public function productDelete($id)
    {
        $this->guard();
        $this->db->table('billing_products')->where('id', $id)->delete();
        $this->response->redirect('/admin/billing/products');
    }

    public function productClone($id)
    {
        $this->guard();
        $orig = $this->db->table('billing_products')->where('id', $id)->first();
        if (!$orig) { $this->response->redirect('/admin/billing/products'); exit; }
        $max = $this->db->table('billing_products')->get() ?: [];
        $this->db->table('billing_products')->insertGetId([
            'name' => $orig->name . ' (Clone)',
            'description' => $orig->description,
            'type' => $orig->type,
            'category' => $orig->category,
            'price' => $orig->price,
            'setup_fee' => $orig->setup_fee,
            'billing_cycle' => $orig->billing_cycle,
            'package_id' => $orig->package_id,
            'license_key' => $orig->license_key,
            'image' => $orig->image,
            'is_active' => 0,
            'is_visible' => 0,
            'sort_order' => count($max) + 1,
        ]);
        $_SESSION['success_message'] = 'Product cloned (inactive).';
        $this->response->redirect('/admin/billing/products');
    }

    public function productCopy($id)
    {
        $this->guard();
        $this->response->json(['id' => (int)$id])->send();
        exit;
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

    // ── Product Categories ──
    public function categories()
    {
        $this->guard();
        $user = $this->auth->user();
        $categories = $this->db->table('billing_categories')->orderBy('sort_order', 'ASC')->get() ?: [];
        return $this->view('admin.billing.categories', [
            'user' => $user, 'title' => 'Billing Categories', 'theme_settings' => $this->theme(),
            'categories' => $categories,
        ]);
    }

    public function categoryStore()
    {
        $this->guard();
        $max = $this->db->table('billing_categories')->get() ?: [];
        $this->db->table('billing_categories')->insertGetId([
            'name' => $this->request->post('name', ''),
            'slug' => $this->request->post('slug', ''),
            'icon' => $this->request->post('icon', '📦'),
            'sort_order' => count($max) + 1,
            'is_active' => 1,
        ]);
        $this->response->redirect('/admin/billing/categories');
    }

    public function categoryUpdate($id)
    {
        $this->guard();
        $this->db->table('billing_categories')->where('id', $id)->update([
            'name' => $this->request->post('name', ''),
            'slug' => $this->request->post('slug', ''),
            'icon' => $this->request->post('icon', '📦'),
            'sort_order' => (int)$this->request->post('sort_order', 0),
        ]);
        $this->response->redirect('/admin/billing/categories');
    }

    public function categoryDelete($id)
    {
        $this->guard();
        $this->db->table('billing_categories')->where('id', $id)->delete();
        $this->response->redirect('/admin/billing/categories');
    }

    public function categoryToggle($id)
    {
        $this->guard();
        $c = $this->db->table('billing_categories')->where('id', $id)->first();
        if ($c) {
            $this->db->table('billing_categories')->where('id', $id)->update(['is_active' => $c->is_active ? 0 : 1]);
        }
        $this->response->redirect('/admin/billing/categories');
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
        $products = $this->db->table('billing_products')->orderBy('name', 'ASC')->get() ?: [];
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get() ?: [];
        return $this->view('admin.billing.orders', [
            'user' => $user, 'title' => 'Orders', 'theme_settings' => $this->theme(),
            'orders' => $orders, 'userMap' => $userMap,
            'products' => $products, 'packages' => $packages,
        ]);
    }

    public function orderStore()
    {
        $this->guard();
        $uid = (int)$this->request->post('user_id', 0);
        $pid = (int)$this->request->post('product_id', 0);
        $pkgId = $this->request->post('package_id') ? (int)$this->request->post('package_id') : null;
        $total = (float)$this->request->post('total', 0);
        $desc = $this->request->post('description', '');
        if (!$uid) { $_SESSION['error_message'] = 'Please select a user.'; $this->response->redirect('/admin/billing/orders'); return; }
        $this->db->table('billing_orders')->insert([
            'user_id' => $uid, 'product_id' => $pid ?: null,
            'package_id' => $pkgId, 'total' => $total,
            'description' => $desc, 'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $_SESSION['success_message'] = 'Order created.';
        $this->response->redirect('/admin/billing/orders');
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
        $services = $this->db->pdo()->query("SELECT s.*, hu.username, hu.domain, bp.name as product_name FROM billing_services s LEFT JOIN hosting_users hu ON s.user_id = hu.id LEFT JOIN billing_products bp ON s.product_id = bp.id ORDER BY s.id DESC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
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
        $pastDueByUser = [];
        $overdueInvs = $this->db->pdo()->query("SELECT user_id, SUM(total) as total FROM invoices WHERE status = 'overdue' GROUP BY user_id")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        foreach ($overdueInvs as $oi) $pastDueByUser[$oi->user_id] = (float)$oi->total;
        $userPackageList = $this->db->pdo()->query("SELECT s.user_id, s.billing_cycle, s.price, p.name as product_name FROM billing_services s LEFT JOIN billing_products p ON s.product_id = p.id WHERE s.status = 'active'")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        return $this->view('admin.billing.invoices', [
            'user' => $user, 'title' => 'Invoices', 'theme_settings' => $this->theme(),
            'invoices' => $invoices, 'hostingUsers' => $hostingUsers,
            'unpaidOrders' => $unpaidOrders, 'creditsByUser' => $creditsByUser,
            'pastDueByUser' => $pastDueByUser, 'userPackageList' => $userPackageList,
        ]);
    }

    public function invoiceCreate()
    {
        $this->guard();
        $uid = (int)$this->request->post('user_id', 0);
        $total = (float)$this->request->post('total', 0);
        $combine = $this->request->post('combine_unpaid', '');
        $applyCredit = $this->request->post('apply_credit', '');
        $num = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        // If combine, add up unpaid order amounts
        if ($combine && $uid) {
            $orders = $this->db->pdo()->query("SELECT SUM(total) as total FROM billing_orders WHERE user_id = {$uid} AND status IN ('pending','suspended')")->fetch(\PDO::FETCH_OBJ);
            if ($orders && $orders->total > 0) $total += (float)$orders->total;
        }
        $creditApplied = 0;
        if ($applyCredit && $uid) {
            $totalCredits = (float)$this->db->pdo()->query("SELECT SUM(amount) as total FROM billing_credits WHERE user_id = {$uid}")->fetch(\PDO::FETCH_OBJ)->total ?? 0;
            $usedCredits = (float)$this->db->pdo()->query("SELECT SUM(amount) as total FROM billing_credit_usage WHERE user_id = {$uid}")->fetch(\PDO::FETCH_OBJ)->total ?? 0;
            $available = max(0, $totalCredits + $usedCredits);
            if ($available > 0) {
                $creditApplied = min($available, $total);
                $this->db->table('billing_credit_usage')->insert([
                    'user_id' => $uid, 'amount' => -$creditApplied,
                    'description' => "Applied to invoice {$num}",
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        $this->db->table('invoices')->insertGetId([
            'user_id' => $uid, 'invoice_number' => $num, 'date' => date('Y-m-d'),
            'due_date' => $this->request->post('due_date', date('Y-m-d', strtotime('+30 days'))),
            'subtotal' => $total, 'total' => $total - $creditApplied,
            'credit_applied' => $creditApplied, 'status' => 'draft',
        ]);
        // Mark combined orders as invoiced
        if ($combine && $uid) {
            $this->db->pdo()->prepare("UPDATE billing_orders SET status = 'invoiced' WHERE user_id = ? AND status IN ('pending','suspended')")->execute([$uid]);
        }
        $_SESSION['success_message'] = "Invoice {$num} created." . ($creditApplied > 0 ? " (\${$creditApplied} credit applied)" : '');
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
        $q = trim($this->request->get('q', ''));
        $sql = "SELECT p.*, hu.username, hu.domain FROM billing_payments p LEFT JOIN hosting_users hu ON p.user_id = hu.id";
        $bind = [];
        if ($q) {
            $sql .= " WHERE p.transaction_id LIKE ? OR hu.username LIKE ? OR hu.domain LIKE ? OR p.invoice_id LIKE ? OR p.amount LIKE ?";
            $like = "%{$q}%";
            $bind = [$like, $like, $like, $like, $like];
        }
        $sql .= " ORDER BY p.id DESC";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute($bind);
        $payments = $stmt->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $hostingUsers = $this->users();
        return $this->view('admin.billing.payments', [
            'user' => $user, 'title' => 'Payments', 'theme_settings' => $this->theme(),
            'payments' => $payments, 'hostingUsers' => $hostingUsers, 'searchQuery' => $q,
        ]);
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

    public function taxUpdate($id)
    {
        $this->guard();
        $this->db->table('billing_taxes')->where('id', $id)->update([
            'name' => $this->request->post('name', ''),
            'rate' => (float)$this->request->post('rate', 0),
            'country' => $this->request->post('country', ''),
            'is_active' => (int)$this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = 'Tax rate updated.';
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
        $expires = $this->request->post('expires_at');
        $this->db->table('billing_coupons')->insertGetId([
            'code' => strtoupper($this->request->post('code', '')), 'type' => $this->request->post('type', 'percentage'),
            'value' => (float)$this->request->post('value', 0), 'max_uses' => (int)$this->request->post('max_uses', 0),
            'min_total' => (float)$this->request->post('min_total', 0), 'expires_at' => $expires ?: null,
            'is_active' => 1,
        ]);
        $_SESSION['success_message'] = 'Coupon created.';
        $this->response->redirect('/admin/billing/coupons');
    }

    public function couponUpdate($id)
    {
        $this->guard();
        $expires = $this->request->post('expires_at');
        $this->db->table('billing_coupons')->where('id', $id)->update([
            'code' => strtoupper($this->request->post('code', '')),
            'type' => $this->request->post('type', 'percentage'),
            'value' => (float)$this->request->post('value', 0),
            'max_uses' => (int)$this->request->post('max_uses', 0),
            'min_total' => (float)$this->request->post('min_total', 0),
            'expires_at' => $expires ?: null,
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