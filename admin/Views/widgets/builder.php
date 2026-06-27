<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
<h3 class="mb-0"><i class="bi bi-tools"></i> Widget Builder</h3>
<a href="/admin/widgets" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back to Widgets</a>
</div>
<div class="card-body">

<p style="color:#64748b;font-size:13px;margin-bottom:14px">Create custom dashboard widgets without editing code. Choose a type, configure it, and add it to your dashboard.</p>

<div style="display:grid;grid-template-columns:300px 1fr;gap:16px">
<!-- Left: Type selector -->
<div>
<h5 style="font-size:13px;margin:0 0 8px">Widget Type</h5>
<div style="display:grid;gap:4px">
<?php
$types = [
    'stat' => 'Statistics Card', 'table' => 'Table', 'list' => 'List', 'html' => 'HTML',
    'markdown' => 'Markdown', 'progress' => 'Progress Bar', 'status' => 'Status Grid',
    'rss' => 'RSS Feed', 'url' => 'URL Embed', 'iframe' => 'IFrame',
    'sql' => 'SQL Query (Admin)', 'notes' => 'Notes',
];
foreach ($types as $tkey => $tlabel):
?>
<button class="btn btn-sm builder-type-btn" data-type="<?php echo $tkey; ?>" style="text-align:left;padding:6px 10px;font-size:12px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:6px;margin-bottom:2px" onclick="selectType('<?php echo $tkey; ?>',this)">
<?php echo htmlspecialchars($tlabel); ?>
</button>
<?php endforeach; ?>
</div>

<div style="margin-top:14px">
<button class="btn btn-primary btn-sm" onclick="createWidget()" style="width:100%;padding:8px"><i class="bi bi-plus-circle"></i> Create Widget</button>
</div>
</div>

<!-- Right: Config form -->
<div id="builderForm">
<h5 style="font-size:13px;margin:0 0 8px">Configuration</h5>
<div id="builderConfig">
<p style="color:#64748b;font-size:12px">Select a widget type on the left to configure it.</p>
</div>
</div>
</div>

</div>
</div>

<script>
var selectedType = null;

function selectType(type, btn) {
    selectedType = type;
    document.querySelectorAll('.builder-type-btn').forEach(function(b){b.style.borderColor='rgba(255,255,255,.06)';b.style.background='rgba(255,255,255,.03)'});
    if(btn){btn.style.borderColor='var(--primary,#008cff)';btn.style.background='rgba(0,140,255,.08)'}
    loadConfigForm(type);
}

function loadConfigForm(type) {
    var html = '<div style="display:grid;gap:8px;font-size:13px">';
    html += '<label>Widget Name <input id="cf_name" class="form-control" value="My Widget" style="font-size:12px"></label>';
    html += '<label>Icon <select id="cf_icon" class="form-control" style="font-size:12px">';
    var icons = ['bi-box','bi-cpu','bi-bar-chart','bi-gear','bi-broadcast','bi-globe','bi-clock-history','bi-currency-dollar','bi-lightning','bi-shield-check','bi-person','bi-music-note','bi-play','bi-stop','bi-activity','bi-graph-up','bi-table','bi-list','bi-file-text','bi-code','bi-rss','bi-window'];
    icons.forEach(function(ic){html += '<option value="'+ic+'">'+ic+'</option>'});
    html += '</select></label>';

    switch(type) {
        case 'stat':
            html += '<label>Value <input id="cf_value" class="form-control" value="0" style="font-size:12px"></label>';
            html += '<label>Label <input id="cf_label" class="form-control" value="Label" style="font-size:12px"></label>';
            html += '<label>Color <input id="cf_color" type="color" class="form-control" value="#008cff" style="height:36px;padding:3px"></label>';
            break;
        case 'html':
            html += '<label>HTML Content <textarea id="cf_html" class="form-control" rows="6" style="font-size:12px;font-family:monospace">&lt;p&gt;Hello World&lt;/p&gt;</textarea></label>';
            break;
        case 'markdown':
            html += '<label>Markdown <textarea id="cf_markdown" class="form-control" rows="6" style="font-size:12px;font-family:monospace"># Title\n\nContent here</textarea></label>';
            break;
        case 'table':
            html += '<label>Headers (comma-separated) <input id="cf_table_headers" class="form-control" value="Name,Value,Status" style="font-size:12px"></label>';
            html += '<label>Rows (one per line, cells comma-separated) <textarea id="cf_table_rows" class="form-control" rows="4" style="font-size:12px">Item 1,100,Active\nItem 2,200,Inactive</textarea></label>';
            break;
        case 'list':
            html += '<label>Items (one per line) <textarea id="cf_list_items" class="form-control" rows="6" style="font-size:12px">Item 1\nItem 2\nItem 3</textarea></label>';
            break;
        case 'progress':
            html += '<label>Percentage <input id="cf_pct" type="number" class="form-control" value="75" min="0" max="100" style="font-size:12px"></label>';
            html += '<label>Label <input id="cf_prog_label" class="form-control" value="Progress" style="font-size:12px"></label>';
            html += '<label>Color <input id="cf_prog_color" type="color" class="form-control" value="#4ade80" style="height:36px;padding:3px"></label>';
            break;
        case 'status':
            html += '<label>Items (one per line, format: Label|active) <textarea id="cf_status_items" class="form-control" rows="4" style="font-size:12px">Apache|1\nMySQL|1\nRedis|0</textarea></label>';
            html += '<p style="font-size:11px;color:#64748b">Use |1 for active, |0 for inactive</p>';
            break;
        case 'rss':
            html += '<label>Feed URL <input id="cf_rss_url" class="form-control" value="https://example.com/feed.xml" style="font-size:12px"></label>';
            html += '<label>Item Limit <input id="cf_rss_limit" type="number" class="form-control" value="5" min="1" max="20" style="font-size:12px"></label>';
            break;
        case 'url':
        case 'iframe':
            html += '<label>URL <input id="cf_url" class="form-control" value="https://example.com" style="font-size:12px"></label>';
            html += '<label>Height (px) <input id="cf_iframe_h" type="number" class="form-control" value="300" min="100" max="2000" style="font-size:12px"></label>';
            break;
        case 'sql':
            html += '<label>SQL Query <textarea id="cf_sql" class="form-control" rows="4" style="font-size:12px;font-family:monospace">SELECT id, username, email FROM hosting_users LIMIT 10</textarea></label>';
            html += '<p style="font-size:11px;color:#f87171">SQL widgets use database credentials. Only for administrators.</p>';
            break;
        case 'notes':
            html += '<label>Notes Content <textarea id="cf_notes" class="form-control" rows="8" style="font-size:12px">Your notes here...</textarea></label>';
            break;
    }

    html += '<label>Refresh Interval (seconds, 0 = no refresh) <input id="cf_refresh" type="number" class="form-control" value="0" min="0" max="3600" style="font-size:12px"></label>';
    html += '</div>';
    document.getElementById('builderConfig').innerHTML = html;
}

function getConfig() {
    var config = {icon: document.getElementById('cf_icon').value, refresh_interval: parseInt(document.getElementById('cf_refresh').value) || 0};
    if (selectedType === 'stat') {
        config.value = document.getElementById('cf_value').value;
        config.label = document.getElementById('cf_label').value;
        config.color = document.getElementById('cf_color').value;
    } else if (selectedType === 'html') {
        config.html = document.getElementById('cf_html').value;
    } else if (selectedType === 'markdown') {
        config.markdown = document.getElementById('cf_markdown').value;
    } else if (selectedType === 'table') {
        config.headers = document.getElementById('cf_table_headers').value.split(',').map(function(s){return s.trim()});
        config.rows = document.getElementById('cf_table_rows').value.split('\n').filter(function(l){return l.trim()}).map(function(l){return l.split(',').map(function(s){return s.trim()})});
    } else if (selectedType === 'list') {
        config.items = document.getElementById('cf_list_items').value.split('\n').filter(function(l){return l.trim()});
    } else if (selectedType === 'progress') {
        config.percentage = parseInt(document.getElementById('cf_pct').value) || 0;
        config.label = document.getElementById('cf_prog_label').value;
        config.color = document.getElementById('cf_prog_color').value;
    } else if (selectedType === 'status') {
        config.items = document.getElementById('cf_status_items').value.split('\n').filter(function(l){return l.trim()}).map(function(l){var p=l.split('|');return {label:p[0].trim(), active: (p[1]||'0')==='1'}});
    } else if (selectedType === 'rss') {
        config.feed_url = document.getElementById('cf_rss_url').value;
        config.item_limit = parseInt(document.getElementById('cf_rss_limit').value) || 5;
    } else if (selectedType === 'url' || selectedType === 'iframe') {
        config.url = document.getElementById('cf_url').value;
        config.height = parseInt(document.getElementById('cf_iframe_h').value) || 300;
        if (selectedType === 'url') config.iframe_height = config.height;
    } else if (selectedType === 'sql') {
        config.query = document.getElementById('cf_sql').value;
    } else if (selectedType === 'notes') {
        config.html = '<div style="white-space:pre-wrap;font-size:12px;color:#ccc;padding:6px">' + document.getElementById('cf_notes').value.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>';
    }
    return config;
}

function createWidget() {
    if (!selectedType) { alert('Select a widget type first.'); return; }
    var name = document.getElementById('cf_name').value;
    if (!name) { alert('Enter a widget name.'); return; }
    var config = getConfig();
    var fd = new FormData();
    fd.append('name', name);
    fd.append('widget_type', selectedType);
    fd.append('config', JSON.stringify(config));
    fetch('/admin/widgets/builder/create', {method: 'POST', body: fd})
    .then(function(r){return r.json()}).then(function(d){
        if (d.ok) {
            alert('Widget "' + name + '" created! Add it to your dashboard from the widget picker.');
            location.reload();
        } else alert('Failed to create widget');
    }).catch(function(){alert('Error creating widget')});
}
</script>
