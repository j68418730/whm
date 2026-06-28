<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.gw-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:20px;transition:.2s;position:relative;overflow:hidden}
.gw-card:hover{border-color:var(--accent);box-shadow:0 4px 20px rgba(0,140,255,.08)}
.gw-card .bar{position:absolute;top:0;left:0;width:4px;height:100%}
.gw-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px}
.gw-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.gw-name{font-weight:600;font-size:15px;color:var(--text-primary)}
.gw-provider{font-size:11px;color:var(--text-muted)}
.gw-status{font-size:11px;padding:2px 10px;border-radius:20px;font-weight:600}
.gw-stat{font-size:12px;color:var(--text-secondary)}
.gw-stat span{color:var(--accent);font-weight:600}
.gw-section{margin-bottom:16px}
.gw-section-title{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:8px;font-weight:600}
.form-section{margin-bottom:20px;padding:16px;background:rgba(255,255,255,.02);border-radius:10px;border:1px solid rgba(255,255,255,.04)}
.form-section h5{font-size:13px;color:var(--accent);margin:0 0 12px}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
<div>
<h3 style="color:var(--accent);margin:0">Payment Gateways</h3>
<p style="color:var(--text-muted);margin:4px 0 0;font-size:13px">Manage payment providers for your billing system.</p>
</div>
<button class="btn primary" onclick="openAddForm()"><i class="bi bi-plus-circle"></i> Add Gateway</button>
</div>

<!-- Gateway Cards Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;margin-bottom:30px">
<?php
$colors = ['paypal'=>'#0070BA','stripe'=>'#635BFF','square'=>'#1EAE5A','authorizenet'=>'#E63946','cashapp'=>'#00D632','googlepay'=>'#4285F4','applepay'=>'#000'];
$icons = ['paypal'=>'💳','stripe'=>'💳','square'=>'💳','authorizenet'=>'🛡️','cashapp'=>'💵','googlepay'=>'🅿️','applepay'=>'🍎'];
if (!empty($gateways)): foreach ($gateways as $gw):
$color = $colors[$gw->name] ?? '#0A84FF';
$icon = $icons[$gw->name] ?? '💳';
$gwConfig = json_decode($gw->config ?? '{}', true) ?: [];
$plugin = $pluginMap[$gw->name] ?? null;
?>
<div class="gw-card">
<div class="bar" style="background:<?php echo $gw->enabled ? $color : 'var(--text-muted)'; ?>"></div>
<div class="gw-header">
<div style="display:flex;align-items:center;gap:12px">
<div class="gw-icon" style="background:<?php echo $color; ?>15"><?php echo $icon; ?></div>
<div>
<div class="gw-name"><?php echo htmlspecialchars($gw->display_name ?: ucfirst($gw->name)); ?></div>
<div class="gw-provider"><?php echo ucfirst($gw->name); ?> · Sort: <?php echo (int)$gw->sort_order; ?></div>
</div>
</div>
<span class="gw-status" style="background:<?php echo $gw->enabled ? 'rgba(74,222,128,.15)' : 'rgba(100,116,139,.15)'; ?>;color:<?php echo $gw->enabled ? '#4ade80' : '#64748b'; ?>"><?php echo $gw->enabled ? 'Enabled' : 'Disabled'; ?></span>
</div>

<div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap">
<?php if ($gw->test_mode): ?><span style="background:rgba(250,204,21,.12);color:#facc15;padding:2px 10px;border-radius:4px;font-size:10px;font-weight:600">⚡ SANDBOX</span><?php endif; ?>
<?php if ($gw->is_default): ?><span style="background:rgba(0,191,255,.12);color:#00bfff;padding:2px 10px;border-radius:4px;font-size:10px;font-weight:600">★ DEFAULT</span><?php endif; ?>
<?php if (!empty($gw->supported_currencies) && $gw->supported_currencies !== 'USD'): ?><span style="background:rgba(255,255,255,.05);padding:2px 10px;border-radius:4px;font-size:10px;color:var(--text-secondary)"><?php echo htmlspecialchars($gw->supported_currencies); ?></span><?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:14px;font-size:12px">
<div class="gw-stat">Fee: <span><?php echo $gw->processing_fee > 0 ? '$' . number_format($gw->processing_fee, 2) . ($gw->fee_type === 'percentage' ? '%' : '') : 'None'; ?></span></div>
<div class="gw-stat">Min: <span>$<?php echo number_format($gw->min_amount ?? 0, 2); ?></span></div>
<div class="gw-stat">Max: <span>$<?php echo number_format($gw->max_amount ?? 0, 2); ?></span></div>
<div class="gw-stat">Merchant: <span><?php echo $gw->merchant_id ? substr($gw->merchant_id, 0, 8) . '...' : '-'; ?></span></div>
</div>

<?php if ($plugin): $fields = $plugin->getConfigFields(); ?>
<div style="margin-bottom:14px;font-size:11px;color:var(--text-muted)">
<?php foreach ($fields as $key => $meta): $val = $gwConfig[$key] ?? ''; if (empty($val)) continue; ?>
<div style="display:flex;gap:6px;padding:2px 0"><span style="color:var(--accent);min-width:100px"><?php echo htmlspecialchars($meta['label'] ?? $key); ?>:</span><span style="color:#64748b;font-family:monospace"><?php echo str_repeat('•', min(strlen($val), 20)); ?></span></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div style="display:flex;gap:6px;flex-wrap:wrap">
<button class="btn btn-sm primary" onclick="editGateway(<?php echo $gw->id; ?>)">✏ Edit</button>
<a href="/admin/gateways/toggle/<?php echo $gw->id; ?>" class="btn btn-sm secondary"><?php echo $gw->enabled ? 'Disable' : 'Enable'; ?></a>
<a href="/admin/gateways/toggle-test/<?php echo $gw->id; ?>" class="btn btn-sm secondary" style="color:#facc15"><?php echo $gw->test_mode ? '📡 Live' : '⚡ Sandbox'; ?></a>
<a href="/admin/gateways/test/<?php echo $gw->id; ?>" class="btn btn-sm secondary" onclick="return confirm('Test connection for <?php echo htmlspecialchars(addslashes($gw->display_name)); ?>?')">🔌 Test</a>
<a href="/admin/gateways/webhook/<?php echo $gw->id; ?>" class="btn btn-sm secondary">🔗 Webhook</a>
<a href="/admin/gateways/delete/<?php echo $gw->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete <?php echo htmlspecialchars(addslashes($gw->display_name)); ?>?')">🗑 Delete</a>
</div>
</div>
<?php endforeach; else: ?>
<div style="grid-column:1/-1;text-align:center;padding:60px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px">
<div style="font-size:48px;margin-bottom:16px;opacity:.3">💳</div>
<h4 style="color:var(--text-muted);margin-bottom:8px">No Gateways Configured</h4>
<p style="color:var(--text-muted);font-size:13px">Add your first payment gateway to start accepting payments.</p>
</div>
<?php endif; ?>
</div>

<!-- Add/Edit Form Modal -->
<div id="gwModal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.75);align-items:center;justify-content:center;overflow-y:auto" onclick="if(event.target===this)closeForm()">
<div style="width:100%;max-width:800px;margin:40px auto;padding:24px;background:var(--bg-card);border-radius:16px;max-height:90vh;overflow-y:auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
<h4 style="margin:0;color:var(--accent)" id="formTitle">Add Payment Gateway</h4>
<button class="btn btn-sm secondary" onclick="closeForm()">✕</button>
</div>

<form method="POST" action="/admin/gateways/store" id="gwForm">
<input type="hidden" name="id" id="gwId" value="">

<div class="form-section">
<h5>Provider</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Gateway Provider</label>
<select name="name" id="gwName" onchange="onGatewayChange()" required>
<option value="">-- Select Gateway --</option>
<option value="paypal">PayPal</option>
<option value="stripe">Stripe</option>
<option value="square">Square</option>
<option value="authorizenet">Authorize.net</option>
<option value="cashapp">Cash App</option>
<option value="googlepay">Google Pay</option>
<option value="applepay">Apple Pay</option>
</select></div>
<div class="form-group"><label>Display Name</label><input name="display_name" id="gwDisplayName" placeholder="PayPal"></div>
<div class="form-group"><label>Enabled</label><select name="enabled"><option value="1">Yes</option><option value="0" selected>No</option></select></div>
<div class="form-group"><label>Mode</label><select name="test_mode"><option value="1" selected>Sandbox (Test)</option><option value="0">Live (Production)</option></select></div>
</div>
</div>

<div class="form-section">
<h5>General Settings</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Description</label><input name="description" id="gwDescription" placeholder="Payment via PayPal"></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="0" style="width:120px"></div>
<div class="form-group"><label>Merchant ID</label><input name="merchant_id" id="gwMerchantId" placeholder="Merchant account ID"></div>
<div class="form-group"><label>Brand Name</label><input name="brand_name" id="gwBrandName" placeholder="Planet Hosts"></div>
<div class="form-group"><label>Invoice Prefix</label><input name="invoice_prefix" id="gwInvoicePrefix" placeholder="INV-" style="width:120px"></div>
<div class="form-group"><label>Supported Currencies</label><input name="supported_currencies" value="USD" placeholder="USD,EUR"></div>
</div>
</div>

<div class="form-section">
<h5>Limits & Fees</h5>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
<div class="form-group"><label>Min Amount</label><input name="min_amount" type="number" step="0.01" value="0.00"></div>
<div class="form-group"><label>Max Amount</label><input name="max_amount" type="number" step="0.01" value="0.00"></div>
<div class="form-group"><label>Processing Fee</label><input name="processing_fee" type="number" step="0.01" value="0.00"></div>
<div class="form-group"><label>Fee Type</label><select name="fee_type"><option value="fixed">Fixed ($)</option><option value="percentage">Percentage (%)</option><option value="both">Both</option></select></div>
<div class="form-group"><label>Default Gateway</label><select name="is_default"><option value="0">No</option><option value="1">Yes</option></select></div>
</div>
</div>

<!-- PayPal-specific fields (shown/hidden by JS) -->
<div id="paypalFields" style="display:none">
<div class="form-section">
<h5>🔵 PayPal Sandbox Credentials</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Sandbox Client ID</label><input name="sandbox_client_id" id="gwSandboxClientId" placeholder="Sandbox Client ID"></div>
<div class="form-group"><label>Sandbox Secret</label><input name="sandbox_secret" id="gwSandboxSecret" type="password" placeholder="Sandbox Secret"></div>
</div>
</div>
<div class="form-section">
<h5>🔵 PayPal Live Credentials</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Live Client ID</label><input name="live_client_id" id="gwLiveClientId" placeholder="Live Client ID"></div>
<div class="form-group"><label>Live Secret</label><input name="live_secret" id="gwLiveSecret" type="password" placeholder="Live Secret"></div>
</div>
</div>
<div class="form-section">
<h5>🔵 PayPal Webhook</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Webhook ID</label><input name="webhook_id" id="gwWebhookId" placeholder="Webhook ID from PayPal"></div>
</div>
</div>
</div>

<!-- URLs section -->
<div class="form-section">
<h5>URLs & Webhooks</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Webhook URL</label><input name="webhook_url" id="gwWebhookUrl" placeholder="Auto-generated"></div>
<div class="form-group"><label>Webhook Secret</label><input name="webhook_secret" id="gwWebhookSecret" placeholder="Webhook signing secret"></div>
<div class="form-group"><label>Success URL</label><input name="success_url" id="gwSuccessUrl" placeholder="/billing/success"></div>
<div class="form-group"><label>Cancel URL</label><input name="cancel_url" id="gwCancelUrl" placeholder="/billing/cancel"></div>
</div>
</div>

<!-- Extra config (hidden JSON for any additional fields) -->
<input type="hidden" name="extra_config" id="gwExtraConfig" value="{}">

<div style="display:flex;gap:8px;margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
<button type="submit" class="btn primary">💾 Save Gateway</button>
<button type="button" class="btn secondary" onclick="closeForm()">Cancel</button>
<button type="button" class="btn secondary" onclick="resetForm()" style="margin-left:auto">Reset</button>
</div>
</form>
</div>
</div>

<script>
var gatewayData = {};
<?php if (!empty($gateways)): foreach ($gateways as $gw): ?>
gatewayData[<?php echo $gw->id; ?>] = <?php echo json_encode([
    'id' => $gw->id,
    'name' => $gw->name,
    'display_name' => $gw->display_name,
    'description' => $gw->description ?? '',
    'enabled' => $gw->enabled,
    'test_mode' => $gw->test_mode,
    'sort_order' => $gw->sort_order,
    'merchant_id' => $gw->merchant_id ?? '',
    'brand_name' => $gw->brand_name ?? '',
    'invoice_prefix' => $gw->invoice_prefix ?? '',
    'supported_currencies' => $gw->supported_currencies ?? 'USD',
    'min_amount' => $gw->min_amount ?? 0,
    'max_amount' => $gw->max_amount ?? 0,
    'processing_fee' => $gw->processing_fee ?? 0,
    'fee_type' => $gw->fee_type ?? 'fixed',
    'is_default' => $gw->is_default ?? 0,
    'sandbox_client_id' => $gw->sandbox_client_id ?? '',
    'sandbox_secret' => $gw->sandbox_secret ?? '',
    'live_client_id' => $gw->live_client_id ?? '',
    'live_secret' => $gw->live_secret ?? '',
    'webhook_url' => $gw->webhook_url ?? '',
    'webhook_secret' => $gw->webhook_secret ?? '',
    'success_url' => $gw->success_url ?? '',
    'cancel_url' => $gw->cancel_url ?? '',
]); ?>;
<?php endforeach; endif; ?>

var configTemplates = {
    'paypal': {sandbox_client_id: '', sandbox_secret: '', live_client_id: '', live_secret: '', merchant_id: '', brand_name: '', invoice_prefix: 'INV-', webhook_id: ''},
    'stripe': {publishable_key: '', secret_key: '', webhook_secret: '', restricted_key: ''},
    'square': {application_id: '', access_token: '', location_id: '', webhook_signature_key: ''},
    'authorizenet': {api_login_id: '', transaction_key: '', signature_key: ''},
    'cashapp': {client_id: '', client_secret: ''},
    'googlepay': {merchant_id: '', gateway_merchant_id: ''},
    'applepay': {merchant_identifier: '', merchant_certificate: '', merchant_private_key: ''},
};

function onGatewayChange() {
    var sel = document.getElementById('gwName');
    var display = document.getElementById('gwDisplayName');
    var names = {'paypal':'PayPal','stripe':'Stripe','square':'Square','authorizenet':'Authorize.net','cashapp':'Cash App','googlepay':'Google Pay','applepay':'Apple Pay'};
    var paypalFields = document.getElementById('paypalFields');
    
    if (sel.value) {
        if (!display.value || !document.getElementById('gwId').value) {
            display.value = names[sel.value] || sel.value;
        }
        paypalFields.style.display = sel.value === 'paypal' ? 'block' : 'none';
    } else {
        paypalFields.style.display = 'none';
    }
}

function openAddForm() {
    resetForm();
    document.getElementById('gwModal').style.display = 'flex';
}

function closeForm() {
    document.getElementById('gwModal').style.display = 'none';
}

function editGateway(id) {
    var d = gatewayData[id];
    if (!d) return;
    document.getElementById('gwModal').style.display = 'flex';
    document.getElementById('formTitle').textContent = 'Edit: ' + d.display_name;
    document.getElementById('gwId').value = d.id;
    document.getElementById('gwName').value = d.name;
    document.getElementById('gwDisplayName').value = d.display_name;
    document.getElementById('gwDescription').value = d.description || '';
    document.querySelector('[name="enabled"]').value = d.enabled;
    document.querySelector('[name="test_mode"]').value = d.test_mode;
    document.querySelector('[name="sort_order"]').value = d.sort_order;
    document.getElementById('gwMerchantId').value = d.merchant_id || '';
    document.getElementById('gwBrandName').value = d.brand_name || '';
    document.getElementById('gwInvoicePrefix').value = d.invoice_prefix || '';
    document.querySelector('[name="supported_currencies"]').value = d.supported_currencies || 'USD';
    document.querySelector('[name="min_amount"]').value = d.min_amount || 0;
    document.querySelector('[name="max_amount"]').value = d.max_amount || 0;
    document.querySelector('[name="processing_fee"]').value = d.processing_fee || 0;
    document.querySelector('[name="fee_type"]').value = d.fee_type || 'fixed';
    document.querySelector('[name="is_default"]').value = d.is_default || 0;
    document.getElementById('gwSandboxClientId').value = d.sandbox_client_id || '';
    document.getElementById('gwSandboxSecret').value = d.sandbox_secret || '';
    document.getElementById('gwLiveClientId').value = d.live_client_id || '';
    document.getElementById('gwLiveSecret').value = d.live_secret || '';
    document.getElementById('gwWebhookUrl').value = d.webhook_url || '';
    document.getElementById('gwWebhookSecret').value = d.webhook_secret || '';
    document.getElementById('gwSuccessUrl').value = d.success_url || '';
    document.getElementById('gwCancelUrl').value = d.cancel_url || '';
    onGatewayChange();
}

function resetForm() {
    document.getElementById('gwForm').reset();
    document.getElementById('gwId').value = '';
    document.getElementById('formTitle').textContent = 'Add Payment Gateway';
    document.getElementById('gwExtraConfig').value = '{}';
    document.getElementById('paypalFields').style.display = 'none';
}
</script>