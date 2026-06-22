<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
<div>
<h3 style="color:var(--accent);margin:0">Payment Gateways</h3>
<p style="color:var(--text-muted);margin:4px 0 0">Manage payment providers for your billing system.</p>
</div>
<button class="btn primary" onclick="document.getElementById('addForm').style.display='block';document.getElementById('addForm').scrollIntoView({behavior:'smooth'})"><i class="bi bi-plus-circle"></i> Add Gateway</button>
</div>

<!-- Gateway Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:30px">
<?php if (!empty($gateways)): foreach ($gateways as $gw): 
$gwConfig = json_decode($gw->config ?? '{}', true);
$gwName = ucfirst($gw->name);
$color = '#0A84FF';
$icon = 'bi-credit-card';
if ($gw->name === 'paypal') { $color = '#0070BA'; $icon = 'bi-paypal'; }
elseif ($gw->name === 'stripe') { $color = '#635BFF'; $icon = 'bi-credit-card-2-front'; }
elseif ($gw->name === 'square') { $color = '#1EAE5A'; $icon = 'bi-square'; }
elseif ($gw->name === 'authorizenet') { $color = '#E63946'; $icon = 'bi-shield-check'; }
elseif ($gw->name === 'cashapp') { $icon = 'bi-cash'; $color = '#00D632'; }
elseif ($gw->name === 'googlepay') { $color = '#4285F4'; $icon = 'bi-google'; }
elseif ($gw->name === 'applepay') { $color = '#000'; $icon = 'bi-apple'; }
?>
<div style="background:var(--bg-card);border:1px solid <?php echo $gw->enabled ? $color : 'var(--border)'; ?>;border-radius:14px;padding:20px;transition:.2s;position:relative;overflow:hidden">
<div style="position:absolute;top:0;left:0;width:4px;height:100%;background:<?php echo $gw->enabled ? $color : 'var(--text-muted)'; ?>"></div>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
<div style="display:flex;align-items:center;gap:12px">
<div style="width:40px;height:40px;border-radius:10px;background:<?php echo $color; ?>20;display:flex;align-items:center;justify-content:center;font-size:18px"><i class="bi <?php echo $icon; ?>" style="color:<?php echo $color; ?>"></i></div>
<div>
<h4 style="margin:0;font-size:15px"><?php echo htmlspecialchars($gw->display_name, ENT_QUOTES, 'UTF-8'); ?></h4>
<span style="font-size:11px;color:var(--text-muted)"><?php echo $gwName; ?></span>
</div>
</div>
<span class="status-badge status-<?php echo $gw->enabled ? 'active' : 'terminated'; ?>" style="font-size:11px;padding:2px 10px"><?php echo $gw->enabled ? 'Enabled' : 'Disabled'; ?></span>
</div>
<div style="font-size:12px;color:var(--text-secondary);margin-bottom:14px">
<?php if ($gw->test_mode): ?><span style="background:rgba(250,204,21,.12);color:#facc15;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600">TEST MODE</span> <?php endif; ?>
<span>Sort: <?php echo (int)$gw->sort_order; ?></span>
</div>
<div style="font-size:11px;color:var(--text-muted);margin-bottom:16px;font-family:monospace;max-height:60px;overflow:hidden">
<?php if (!empty($gwConfig)): ?>
<?php foreach ($gwConfig as $k => $v): ?>
<div style="display:flex;gap:6px"><span style="color:var(--accent)"><?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>:</span><span style="color:#64748b">••••••••</span></div>
<?php endforeach; ?>
<?php else: ?>
<div style="color:#64748b">No config keys</div>
<?php endif; ?>
</div>
<div style="display:flex;gap:6px;flex-wrap:wrap">
<button class="btn btn-sm primary" onclick='editGateway(<?php echo json_encode(['id' => $gw->id, 'name' => $gw->name, 'display_name' => $gw->display_name, 'enabled' => $gw->enabled, 'test_mode' => $gw->test_mode, 'sort_order' => $gw->sort_order, 'config' => json_encode($gwConfig, JSON_PRETTY_PRINT)]); ?>)'><i class="bi bi-pencil"></i> Edit</button>
<a href="/admin/gateways/test/<?php echo $gw->id; ?>" class="btn btn-sm secondary" onclick="return confirm('Run test for <?php echo htmlspecialchars(addslashes($gw->display_name), ENT_QUOTES, 'UTF-8'); ?>?')"><i class="bi bi-play-circle"></i> Test</a>
<a href="/admin/gateways/delete/<?php echo $gw->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete <?php echo htmlspecialchars(addslashes($gw->display_name), ENT_QUOTES, 'UTF-8'); ?>?')"><i class="bi bi-trash"></i> Delete</a>
</div>
</div>
<?php endforeach; else: ?>
<div style="grid-column:1/-1;text-align:center;padding:60px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px">
<div style="font-size:48px;margin-bottom:16px;opacity:.3"><i class="bi bi-credit-card"></i></div>
<h4 style="color:var(--text-muted);margin-bottom:8px">No Gateways Configured</h4>
<p style="color:var(--text-muted);font-size:13px">Add your first payment gateway to start accepting payments.</p>
</div>
<?php endif; ?>
</div>

<!-- Add/Edit Form -->
<div id="addForm" class="card" style="display:none;max-width:700px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h4 style="margin:0" id="formTitle">Add Payment Gateway</h4>
<button class="btn btn-sm secondary" onclick="resetForm();document.getElementById('addForm').style.display='none'"><i class="bi bi-x"></i></button>
</div>
<form method="POST" action="/admin/gateways/store" id="gatewayForm">
<input type="hidden" name="id" id="gwId" value="">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
<div class="form-group"><label>Provider</label>
<select name="name" id="gwName" onchange="fillDisplayName()" required>
<option value="">-- Select --</option>
<option value="paypal">PayPal</option>
<option value="stripe">Stripe</option>
<option value="square">Square</option>
<option value="authorizenet">Authorize.net</option>
<option value="cashapp">Cash App</option>
<option value="googlepay">Google Pay</option>
<option value="applepay">Apple Pay</option>
</select></div>
<div class="form-group"><label>Display Name</label><input name="display_name" id="gwDisplayName" placeholder="PayPal"></div>
<div class="form-group"><label>Enabled</label><select name="enabled"><option value="1" selected>Yes</option><option value="0">No</option></select></div>
<div class="form-group"><label>Test Mode</label><select name="test_mode"><option value="1" selected>Yes</option><option value="0">No</option></select></div>
</div>
<div class="form-group" style="margin-top:14px"><label>Sort Order</label><input name="sort_order" type="number" value="0" style="width:120px"></div>
<div class="form-group"><label>Config (JSON)</label><textarea name="config" id="gwConfig" rows="6" style="font-family:monospace;font-size:13px" placeholder='{"key": "value"}'></textarea></div>
<div style="display:flex;gap:8px">
<button type="submit" class="btn primary"><i class="bi bi-check-circle"></i> Save Gateway</button>
<button type="button" class="btn secondary" onclick="resetForm()">Reset</button>
</div>
</form>
</div>

<script>
var nameMap = {
    'paypal': 'PayPal', 'stripe': 'Stripe', 'square': 'Square',
    'authorizenet': 'Authorize.net', 'cashapp': 'Cash App',
    'googlepay': 'Google Pay', 'applepay': 'Apple Pay'
};
var configTemplates = {
    'paypal': JSON.stringify({client_id: '', secret: ''}, null, 2),
    'stripe': JSON.stringify({publishable_key: '', secret_key: ''}, null, 2),
    'square': JSON.stringify({application_id: '', access_token: '', location_id: ''}, null, 2),
    'authorizenet': JSON.stringify({api_login_id: '', transaction_key: ''}, null, 2),
    'cashapp': JSON.stringify({client_id: '', client_secret: ''}, null, 2),
    'googlepay': JSON.stringify({merchant_id: '', gateway_merchant_id: ''}, null, 2),
    'applepay': JSON.stringify({merchant_identifier: '', merchant_certificate: ''}, null, 2)
};

function fillDisplayName() {
    var sel = document.getElementById('gwName');
    var display = document.getElementById('gwDisplayName');
    var config = document.getElementById('gwConfig');
    if (sel.value && nameMap[sel.value]) {
        if (!display.value || !document.getElementById('gwId').value) display.value = nameMap[sel.value];
        if (!document.getElementById('gwId').value && configTemplates[sel.value]) config.value = configTemplates[sel.value];
    }
}

function editGateway(data) {
    document.getElementById('addForm').style.display = 'block';
    document.getElementById('formTitle').textContent = 'Edit: ' + data.display_name;
    document.getElementById('gwId').value = data.id;
    document.getElementById('gwName').value = data.name;
    document.getElementById('gwDisplayName').value = data.display_name;
    document.querySelector('[name="enabled"]').value = data.enabled;
    document.querySelector('[name="test_mode"]').value = data.test_mode;
    document.querySelector('[name="sort_order"]').value = data.sort_order;
    document.getElementById('gwConfig').value = data.config;
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetForm() {
    document.getElementById('gwId').value = '';
    document.getElementById('formTitle').textContent = 'Add Payment Gateway';
    document.getElementById('gatewayForm').reset();
    document.getElementById('gwConfig').value = '';
}
</script>
