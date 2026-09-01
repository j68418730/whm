<style>
.copy-btn{background:rgba(0,191,255,.1);border:1px solid rgba(0,191,255,.2);color:#00bfff;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:12px;transition:.15s}
.copy-btn:hover{background:rgba(0,191,255,.2)}
.code-block{background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:16px;font-family:monospace;font-size:13px;overflow-x:auto;white-space:pre;color:#e0e0e0;line-height:1.6}
.tab-btn{padding:10px 20px;border:none;background:transparent;color:var(--text-secondary);cursor:pointer;font-size:13px;border-bottom:2px solid transparent;transition:.15s}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-btn:hover{color:var(--text-primary)}
.gateway-card{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:20px;margin-bottom:16px}
.gateway-header{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.gateway-icon{font-size:24px}
.gateway-toggle{margin-left:auto}
.gateway-field{margin-bottom:12px}
.gateway-field label{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:4px;font-weight:600}
.gateway-field input,.gateway-field select{width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;box-sizing:border-box}
.gateway-field input:focus{border-color:#0A84FF}
</style>

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<?php foreach ($billingTabs as $tab): ?>
<a href="<?php echo $tab['url']; ?>" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo (basename(parse_url($tab['url'], PHP_URL_PATH)) === basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>"><?php echo $tab['label']; ?></a>
<?php endforeach; ?>
</div>

<h3 style="color:var(--accent);margin-bottom:4px">🛒 Shopping Cart Integration</h3>
<p style="color:var(--text-muted);font-size:13px;margin-bottom:20px">Embed a Planet Hosts storefront on your website, or use the API to build a custom checkout.</p>

<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,.06)">
<button class="tab-btn active" onclick="switchCartTab('embed',this)">📋 Embed Code</button>
<button class="tab-btn" onclick="switchCartTab('api',this)">🔌 API Integration</button>
<button class="tab-btn" onclick="switchCartTab('preview',this)">👁️ Preview</button>
<button class="tab-btn" onclick="switchCartTab('settings',this)">⚙️ Settings</button>
</div>

<!-- Embed Tab -->
<div id="tab-embed">
<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">JavaScript Embed</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">Add this script tag to your website's <code style="font-size:11px;background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px"></body></code> to display a Planet Hosts product catalog and shopping cart.</p>
<div class="code-block" id="embedCode"><!-- Planet Hosts Shopping Cart -->
<div id="ph-cart"></div>
<script src="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com'); ?>/reseller/billing/embed.js?reseller=<?php echo (int)$reseller->id; ?>"></script>
<script>
  PHCart.init({
    resellerId: <?php echo (int)$reseller->id; ?>,
    apiKey: '<?php echo htmlspecialchars(substr(md5($reseller->id . '-ph-reseller-cart'), 0, 16)); ?>',
    container: '#ph-cart',
    theme: '<?php echo htmlspecialchars($cartSettings['cart_theme'] ?? 'dark'); ?>',
    products: true,
    checkout: true
  });
</script></div>
<div style="margin-top:8px"><button class="copy-btn" onclick="copyText('embedCode')">📋 Copy Embed Code</button></div>
</div>

<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">Iframe Embed (Simplest)</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">For sites that can't run JavaScript, use an iframe:</p>
<div class="code-block" id="iframeCode"><iframe src="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com'); ?>/reseller/billing/cart?reseller=<?php echo (int)$reseller->id; ?>" width="100%" height="800" style="border:none;border-radius:8px"></iframe></div>
<div style="margin-top:8px"><button class="copy-btn" onclick="copyText('iframeCode')">📋 Copy Iframe Code</button></div>
</div>

<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">WordPress Shortcode</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">If you use WordPress, add this shortcode to any page or post:</p>
<div class="code-block" id="wpCode">[planet_hosts_cart reseller_id="<?php echo (int)$reseller->id; ?>" api_key="<?php echo htmlspecialchars(substr(md5($reseller->id . '-ph-reseller-cart'), 0, 16)); ?>"]</div>
<div style="margin-top:8px"><button class="copy-btn" onclick="copyText('wpCode')">📋 Copy WordPress Shortcode</button></div>
</div>
</div>

<!-- API Tab -->
<div id="tab-api" style="display:none">
<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">REST API Reference</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">Use the Planet Hosts API to build a custom shopping cart experience. All endpoints require your API key in the <code style="font-size:11px;background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px">Authorization</code> header.</p>

<div style="margin-bottom:16px">
<h5 style="color:#00bfff;margin:0 0 6px;font-size:13px">Get Products</h5>
<div class="code-block">GET /api/reseller/billing/products
Header: Authorization: Bearer <?php echo htmlspecialchars(substr(md5($reseller->id . '-ph-reseller-cart'), 0, 16)); ?>

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
<div class="code-block">POST /api/reseller/billing/orders
Header: Authorization: Bearer <?php echo htmlspecialchars(substr(md5($reseller->id . '-ph-reseller-cart'), 0, 16)); ?>
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
<div class="code-block">GET /api/reseller/billing/orders/{id}
Header: Authorization: Bearer <?php echo htmlspecialchars(substr(md5($reseller->id . '-ph-reseller-cart'), 0, 16)); ?>

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
<div style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px">No products available yet. <a href="/reseller/billing-system/products" style="color:#0A84FF">Add products first</a>.</div>
<?php endif; ?>
<div style="text-align:center;margin-top:8px"><span style="font-size:11px;color:var(--text-muted)">⚡ Powered by Planet Hosts</span></div>
</div>
</div>
</div>

<!-- Settings Tab -->
<div id="tab-settings" style="display:none">
<div class="card" style="margin-bottom:16px;padding:20px">
<h4 style="color:var(--accent);margin:0 0 8px">Cart Appearance</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:16px">Configure how your shopping cart behaves on external sites.</p>
<form method="POST" action="/reseller/billing-system/cart/settings">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:500px">
<div class="form-group"><label>Cart Theme</label><select name="cart_theme"><option value="dark" <?php echo ($cartSettings['cart_theme'] ?? 'dark') === 'dark' ? 'selected' : ''; ?>>Dark</option><option value="light" <?php echo ($cartSettings['cart_theme'] ?? '') === 'light' ? 'selected' : ''; ?>>Light</option><option value="auto" <?php echo ($cartSettings['cart_theme'] ?? '') === 'auto' ? 'selected' : ''; ?>>Auto</option></select></div>
<div class="form-group"><label>Currency</label><select name="cart_currency"><option value="USD" <?php echo ($cartSettings['cart_currency'] ?? 'USD') === 'USD' ? 'selected' : ''; ?>>USD ($)</option><option value="EUR" <?php echo ($cartSettings['cart_currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option><option value="GBP" <?php echo ($cartSettings['cart_currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option></select></div>
<div class="form-group"><label>Show Product Images</label><select name="show_images"><option value="1" <?php echo ($cartSettings['show_images'] ?? 1) ? 'selected' : ''; ?>>Yes</option><option value="0" <?php echo !($cartSettings['show_images'] ?? 1) ? 'selected' : ''; ?>>No</option></select></div>
<div class="form-group"><label>Guest Checkout</label><select name="guest_checkout"><option value="1" <?php echo ($cartSettings['guest_checkout'] ?? 1) ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo !($cartSettings['guest_checkout'] ?? 1) ? 'selected' : ''; ?>>Disabled</option></select></div>
</div>

<h4 style="color:var(--accent);margin:24px 0 8px">Payment Gateways</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:16px">Configure which payment methods are available in your cart. Enable and enter credentials for each gateway.</p>

<!-- PayPal Gateway -->
<div class="gateway-card">
<div class="gateway-header">
<span class="gateway-icon">💙</span>
<strong style="font-size:16px">PayPal</strong>
<label class="gateway-toggle" style="display:flex;align-items:center;gap:6px;font-size:12px;color:#e0e0e0;cursor:pointer">
<input type="checkbox" name="paypal_enabled" value="1" <?php echo ($cartSettings['paypal_enabled'] ?? 0) ? 'checked' : ''; ?>> Enable PayPal
</label>
</div>
<div id="paypalFields" style="<?php echo ($cartSettings['paypal_enabled'] ?? 0) ? '' : 'display:none;'; ?>">
<div class="gateway-field"><label>PayPal Client ID</label><input name="paypal_client_id" type="text" placeholder="Your PayPal App Client ID" value="<?php echo htmlspecialchars($cartSettings['paypal_client_id'] ?? ''); ?>"></div>
<div class="gateway-field"><label>PayPal Secret</label><input name="paypal_secret" type="password" placeholder="Your PayPal App Secret" value="<?php echo htmlspecialchars($cartSettings['paypal_secret'] ?? ''); ?>"></div>
<div class="gateway-field"><label>Mode</label><select name="paypal_mode"><option value="sandbox" <?php echo ($cartSettings['paypal_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testing)</option><option value="live" <?php echo ($cartSettings['paypal_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live (Production)</option></select></div>
<p style="font-size:11px;color:#64748b;margin-top:8px">Create a PayPal App at <a href="https://developer.paypal.com/" target="_blank" style="color:#0A84FF">developer.paypal.com</a> to get Client ID and Secret.</p>
</div>
</div>

<!-- CashApp Gateway -->
<div class="gateway-card">
<div class="gateway-header">
<span class="gateway-icon">💚</span>
<strong style="font-size:16px">Cash App</strong>
<label class="gateway-toggle" style="display:flex;align-items:center;gap:6px;font-size:12px;color:#e0e0e0;cursor:pointer">
<input type="checkbox" name="cashapp_enabled" value="1" <?php echo ($cartSettings['cashapp_enabled'] ?? 0) ? 'checked' : ''; ?>> Enable Cash App
</label>
</div>
<div id="cashappFields" style="<?php echo ($cartSettings['cashapp_enabled'] ?? 0) ? '' : 'display:none;'; ?>">
<div class="gateway-field"><label>Cash App Username (Cashtag)</label><input name="cashapp_username" type="text" placeholder="Your $Cashtag" value="<?php echo htmlspecialchars($cartSettings['cashapp_username'] ?? ''); ?>"></div>
<div class="gateway-field"><label>Cash App Tag/Display Name</label><input name="cashapp_tag" type="text" placeholder="Business name for checkout" value="<?php echo htmlspecialchars($cartSettings['cashapp_tag'] ?? ''); ?>"></div>
<p style="font-size:11px;color:#64748b;margin-top:8px">Customers will be directed to pay via Cash App using your $Cashtag. You'll receive a notification to approve the payment.</p>
</div>
</div>

<button type="submit" class="btn primary" style="margin-top:16px">💾 Save Settings</button>
</form>
</div>
</div>

<script>
function switchCartTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    document.getElementById('tab-embed').style.display = tab === 'embed' ? 'block' : 'none';
    document.getElementById('tab-api').style.display = tab === 'api' ? 'block' : 'none';
    document.getElementById('tab-preview').style.display = tab === 'preview' ? 'block' : 'none';
    document.getElementById('tab-settings').style.display = tab === 'settings' ? 'block' : 'none';
}

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

// Toggle gateway fields
document.querySelectorAll('input[name="paypal_enabled"]').forEach(function(cb){
    cb.addEventListener('change', function(){
        document.getElementById('paypalFields').style.display = this.checked ? '' : 'none';
    });
});
document.querySelectorAll('input[name="cashapp_enabled"]').forEach(function(cb){
    cb.addEventListener('change', function(){
        document.getElementById('cashappFields').style.display = this.checked ? '' : 'none';
    });
});
</script>