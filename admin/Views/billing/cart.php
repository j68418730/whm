<style>
.copy-btn{background:rgba(0,191,255,.1);border:1px solid rgba(0,191,255,.2);color:#00bfff;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:12px;transition:.15s}
.copy-btn:hover{background:rgba(0,191,255,.2)}
.code-block{background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:16px;font-family:monospace;font-size:13px;overflow-x:auto;white-space:pre;color:#e0e0e0;line-height:1.6}
.tab-btn{padding:10px 20px;border:none;background:transparent;color:var(--text-secondary);cursor:pointer;font-size:13px;border-bottom:2px solid transparent;transition:.15s}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-btn:hover{color:var(--text-primary)}
</style>

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Dashboard</a>
<a href="/admin/billing/cart" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">🛒 Cart</a>
<a href="/admin/billing/products" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📦 Products</a>
<a href="/admin/billing/orders" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📋 Orders</a>
<a href="/admin/billing/services" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🖥 Services</a>
<a href="/admin/billing/invoices" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💰 Invoices</a>
<a href="/admin/billing/payments" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💳 Payments</a>
<a href="/admin/billing/taxes" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏛️ Taxes</a>
<a href="/admin/billing/coupons" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🎟️ Coupons</a>
<a href="/admin/billing/credits" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏦 Credits</a>
<a href="/admin/billing/refunds" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">↩️ Refunds</a>
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📈 Reports</a>
</div>

<h3 style="color:var(--accent);margin-bottom:4px">🛒 Shopping Cart Integration</h3>
<p style="color:var(--text-muted);font-size:13px;margin-bottom:20px">Embed a Planet Hosts storefront on your website, or use the API to build a custom checkout.</p>

<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,.06)">
<button class="tab-btn active" onclick="switchCartTab('embed',this)">📋 Embed Code</button>
<button class="tab-btn" onclick="switchCartTab('themes',this)">🎨 Cart Themes</button>
<button class="tab-btn" onclick="switchCartTab('api',this)">🔌 API Integration</button>
<button class="tab-btn" onclick="switchCartTab('preview',this)">👁️ Preview</button>
<button class="tab-btn" onclick="switchCartTab('settings',this)">⚙️ Settings</button>
</div>

<!-- Embed Tab -->
<div id="tab-embed">
<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">JavaScript Embed</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">Add this script tag to your website's <code style="font-size:11px;background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px">&lt;/body&gt;</code> to display a Planet Hosts product catalog and shopping cart.</p>
<div class="code-block" id="embedCode">&lt;!-- Planet Hosts Shopping Cart --&gt;
&lt;div id="ph-cart"&gt;&lt;/div&gt;
&lt;script src="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? primary_domain()); ?>/store/embed.js"&gt;&lt;/script&gt;
&lt;script&gt;
  PHCart.init({
    container: '#ph-cart',
    theme: '<?php echo htmlspecialchars($cartSettings['cart_theme'] ?? 'planethosts'); ?>',
    products: true,
    checkout: true
  });
&lt;/script&gt;</div>
<div style="margin-top:8px"><button class="copy-btn" onclick="copyText('embedCode')">📋 Copy Embed Code</button></div>
</div>

<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">Iframe Embed (Simplest)</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">For sites that can't run JavaScript, use an iframe:</p>
<div class="code-block" id="iframeCode">&lt;iframe src="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com'); ?>/store" width="100%" height="800" style="border:none;border-radius:8px"&gt;&lt;/iframe&gt;</div>
<div style="margin-top:8px"><button class="copy-btn" onclick="copyText('iframeCode')">📋 Copy Iframe Code</button></div>
</div>

<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">WordPress Shortcode</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">If you use WordPress, add this shortcode to any page or post:</p>
<div class="code-block" id="wpCode">[planet_hosts_cart api_key="<?php echo htmlspecialchars(substr(md5($user->id . '-ph-cart'), 0, 16)); ?>"]</div>
<div style="margin-top:8px"><button class="copy-btn" onclick="copyText('wpCode')">📋 Copy WordPress Shortcode</button></div>
</div>
</div>

<!-- Cart Themes Tab -->
<div id="tab-themes" style="display:none">
<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">🎨 Cart Themes</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:16px">Pick one of <?php echo count($themes); ?> storefront styles. This becomes the default theme used by <code style="font-size:11px;background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px">/store</code> and the embed code. Users can also override it per embed with <code style="font-size:11px;background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px">theme:</code> in <code style="font-size:11px;background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px">PHCart.init()</code>.</p>
<?php
$curTheme = $cartSettings['cart_theme'] ?? 'planethosts';
foreach ($themes as $t) {
    echo '<style>' . \Admin\Services\CartThemes::fullCss($t['id']) . '</style>';
}
?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
<?php foreach ($themes as $t): $isCur = $t['id'] === $curTheme; ?>
<div class="card" style="padding:14px;border:1px solid <?php echo $isCur ? 'rgba(0,191,255,.5)' : 'rgba(255,255,255,.08)'; ?>;border-radius:10px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
<strong style="font-size:14px"><?php echo htmlspecialchars($t['name']); ?></strong>
<?php if ($isCur): ?><span style="font-size:10px;background:rgba(0,191,255,.15);color:#00bfff;padding:3px 8px;border-radius:100px">✓ Current</span><?php endif; ?>
</div>
<div class="ph-store phc-<?php echo $t['id']; ?>" style="border-radius:8px;padding:10px;min-height:96px;font-size:11px">
<div class="phc-header" style="margin-bottom:6px;padding:8px"><div class="phc-brand" style="gap:6px"><div><span class="phc-logo-text" style="font-size:11px"><?php echo htmlspecialchars($t['logo_a'] ?? ''); ?><?php if (!empty($t['logo_b'])): ?><b><?php echo htmlspecialchars($t['logo_b']); ?></b><?php endif; ?></span><div class="phc-header-sub" style="font-size:7px"><?php echo htmlspecialchars($t['header_sub'] ?? ''); ?></div></div></div><span class="phc-cart-badge" style="font-size:8px">0</span></div>
<div class="phc-grid" style="grid-template-columns:1fr 1fr;gap:6px">
<div class="phc-product" style="padding:8px"><div class="phc-name">Hosting</div><div class="phc-price">$9.99</div><button class="phc-btn" style="padding:5px 8px;font-size:10px">🛒 Add</button></div>
<div class="phc-product" style="padding:8px"><div class="phc-name">Radio</div><div class="phc-price">$19.99</div><button class="phc-btn" style="padding:5px 8px;font-size:10px">🛒 Add</button></div>
</div>
<div class="phc-footer" style="margin-top:6px;padding:5px;font-size:8px"><?php echo htmlspecialchars(mb_strimwidth((string)($t['footer'] ?? ''), 0, 40, '…')); ?></div>
</div>
<p style="font-size:11px;color:var(--text-muted);margin:10px 0 8px"><?php echo htmlspecialchars($t['description']); ?><?php if ($t['bootstrap']): ?> <span style="color:#0af;font-weight:600">· Bootstrap</span><?php endif; ?></p>
<form method="POST" action="/admin/billing/cart/themes/use/<?php echo $t['id']; ?>">
<button type="submit" class="btn primary" style="width:100%;font-size:12px"><?php echo $isCur ? 'Default Theme' : 'Use This Theme'; ?></button>
</form>
</div>
<?php endforeach; ?>
</div>
</div>
</div>

<!-- API Tab -->
<div id="tab-api" style="display:none">
<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">REST API Reference</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">Use the Planet Hosts API to build a custom shopping cart experience. All endpoints require your API key in the <code style="font-size:11px;background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px">Authorization</code> header.</p>

<div style="margin-bottom:16px">
<h5 style="color:#00bfff;margin:0 0 6px;font-size:13px">Get Products</h5>
<div class="code-block">GET /api/store/products
Header: Authorization: Bearer <?php echo htmlspecialchars(substr(md5($user->id . '-ph-cart'), 0, 16)); ?>

Response:
{
  "products": [
    {
      "id": 1,
      "name": "Hosting Plan",
      "price": 9.99,
      "billing_cycle": "monthly",
      "description": "..."
    }
  ]
}</div>
<button class="copy-btn" style="margin-top:6px" onclick="copyText('apiGetProducts')">📋 Copy</button>
</div>

<div style="margin-bottom:16px">
<h5 style="color:#00bfff;margin:0 0 6px;font-size:13px">Create Order</h5>
<div class="code-block">POST /api/store/orders
Header: Authorization: Bearer <?php echo htmlspecialchars(substr(md5($user->id . '-ph-cart'), 0, 16)); ?>
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 1,
  "customer": {
    "name": "John Doe",
    "email": "john@example.com"
  }
}

Response:
{
  "order_id": 123,
  "total": 9.99,
  "checkout_url": "https://planet-hosts.com/checkout/abc123"
}</div>
<button class="copy-btn" style="margin-top:6px" onclick="copyText('apiCreateOrder')">📋 Copy</button>
</div>

<div style="margin-bottom:16px">
<h5 style="color:#00bfff;margin:0 0 6px;font-size:13px">Checkout Status</h5>
<div class="code-block">GET /api/store/orders/{id}
Header: Authorization: Bearer <?php echo htmlspecialchars(substr(md5($user->id . '-ph-cart'), 0, 16)); ?>

Response:
{
  "order_id": 123,
  "status": "completed",
  "total": 9.99,
  "invoice_url": "https://planet-hosts.com/invoice/INV-..."
}</div>
<button class="copy-btn" style="margin-top:6px" onclick="copyText('apiCheckout')">📋 Copy</button>
</div>
</div>
</div>

<!-- Preview Tab -->
<div id="tab-preview" style="display:none">
<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">Store Preview</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">This is how the embedded cart will look on your customers' sites.</p>
<div style="background:rgba(0,0,0,.3);border-radius:10px;padding:16px;max-width:500px">
<div style="text-align:center;margin-bottom:16px"><span style="font-weight:700;font-size:18px;color:var(--accent)">Planet Hosts Store</span></div>
<?php if (!empty($products)): $shown = 0; foreach ($products as $p): if ($shown >= 3) break; $shown++; ?>
<div style="background:rgba(255,255,255,.04);border-radius:8px;padding:12px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center">
<div><strong style="font-size:14px"><?php echo htmlspecialchars($p->name); ?></strong><br><span style="font-size:11px;color:var(--text-muted)">$<?php echo number_format($p->price, 2); ?>/<?php echo $p->billing_cycle ?? 'mo'; ?></span></div>
<button style="background:var(--accent);border:none;color:#fff;padding:6px 14px;border-radius:6px;font-size:12px;cursor:pointer">Add to Cart</button>
</div>
<?php endforeach; else: ?>
<div style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px">No products available yet.</div>
<?php endif; ?>
<div style="text-align:center;margin-top:8px"><span style="font-size:11px;color:var(--text-muted)">⚡ Powered by Planet Hosts</span></div>
</div>
</div>
</div>

<!-- Settings Tab -->
<div id="tab-settings" style="display:none">
<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">Cart Settings</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:16px">Configure how your shopping cart behaves on external sites.</p>
<form method="POST" action="/admin/billing/cart/settings">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:560px">
<div class="form-group"><label>Cart Theme</label><select name="cart_theme">
<?php foreach ($themes as $t): ?>
<option value="<?php echo htmlspecialchars($t['id']); ?>" <?php echo ($cartSettings['cart_theme'] ?? 'planethosts') === $t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Currency</label><select name="cart_currency">
<?php foreach (['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)'] as $cv => $cl): ?>
<option value="<?php echo $cv; ?>" <?php echo ($cartSettings['cart_currency'] ?? 'USD') === $cv ? 'selected' : ''; ?>><?php echo $cl; ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Show Product Images</label><select name="show_images"><option value="1" <?php echo ($cartSettings['cart_show_images'] ?? '1') === '1' ? 'selected' : ''; ?>>Yes</option><option value="0" <?php echo ($cartSettings['cart_show_images'] ?? '1') === '0' ? 'selected' : ''; ?>>No</option></select></div>
<div class="form-group"><label>Guest Checkout</label><select name="guest_checkout"><option value="1" <?php echo ($cartSettings['cart_guest_checkout'] ?? '1') === '1' ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo ($cartSettings['cart_guest_checkout'] ?? '1') === '0' ? 'selected' : ''; ?>>Disabled</option></select></div>
<div class="form-group" style="grid-column:1/-1"><label>Logo URL <span style="font-weight:400;color:var(--text-muted)">(optional image — themes also have a built-in logo)</span></label><input type="url" name="logo_url" value="<?php echo htmlspecialchars($cartSettings['cart_logo_url'] ?? ''); ?>" placeholder="https://…/logo.png" style="width:100%"></div>
<div class="form-group" style="grid-column:1/-1"><label>Header Image URL <span style="font-weight:400;color:var(--text-muted)">(optional banner behind the header)</span></label><input type="url" name="header_image" value="<?php echo htmlspecialchars($cartSettings['cart_header_image'] ?? ''); ?>" placeholder="https://…/header.png" style="width:100%"></div>
<div class="form-group" style="grid-column:1/-1"><label>Footer Message</label><textarea name="footer_message" rows="2" placeholder="© 2026 Planet Hosts. All rights reserved." style="width:100%"><?php echo htmlspecialchars($cartSettings['cart_footer_message'] ?? ''); ?></textarea>
<div style="font-size:11px;color:var(--text-muted);margin-top:4px">Shown in the footer of every cart theme. Leave empty to use each theme's default.</div></div>
</div>
<button type="submit" class="btn primary" style="margin-top:12px">💾 Save Settings</button>
</form>
</div>
</div>

<script>
function switchCartTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    document.getElementById('tab-embed').style.display = tab === 'embed' ? 'block' : 'none';
    document.getElementById('tab-themes').style.display = tab === 'themes' ? 'block' : 'none';
    document.getElementById('tab-api').style.display = tab === 'api' ? 'block' : 'none';
    document.getElementById('tab-preview').style.display = tab === 'preview' ? 'block' : 'none';
    document.getElementById('tab-settings').style.display = tab === 'settings' ? 'block' : 'none';
}
(function(){
    var t = new URLSearchParams(location.search).get('tab');
    if (t && document.querySelector('.tab-btn')) {
        var b = Array.from(document.querySelectorAll('.tab-btn')).find(function(x){ return x.textContent.toLowerCase().indexOf(t) !== -1; }) || null;
        if (b) switchCartTab(t, b);
    }
})();

function copyText(id) {
    var el = document.getElementById(id);
    if (!el) return;
    var text = el.textContent || el.innerText;
    navigator.clipboard.writeText(text).then(function() {
        var btn = event.target;
        var orig = btn.textContent;
        btn.textContent = '✅ Copied!';
        setTimeout(function() { btn.textContent = orig; }, 1500);
    }).catch(function() {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    });
}
</script>