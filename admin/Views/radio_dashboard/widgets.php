<style>
.wg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px;margin-top:20px}
.wg-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:14px;overflow:hidden;transition:.25s}
.wg-card:hover{border-color:rgba(0,191,255,.2);box-shadow:0 8px 32px rgba(0,0,0,.3)}
.wg-header{padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.04);display:flex;justify-content:space-between;align-items:center}
.wg-name{font-size:14px;font-weight:700;color:#f1f5f9}
.wg-desc{font-size:11px;color:#64748b;margin-top:2px}
.wg-preview{padding:20px;display:flex;justify-content:center;align-items:center;min-height:100px;background:rgba(0,0,0,.2)}
.wg-code{padding:16px 20px;background:rgba(0,0,0,.3)}
.wg-code textarea{width:100%;background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.08);border-radius:6px;color:#e0e0e0;padding:8px;font-size:11px;font-family:monospace;resize:vertical;min-height:50px}
.wg-tabs{display:flex;gap:4px;margin-bottom:8px}
.wg-tab{padding:4px 12px;border-radius:4px;font-size:11px;cursor:pointer;background:rgba(255,255,255,.04);color:#64748b;border:none;font-family:inherit}
.wg-tab.active{background:rgba(0,140,255,.12);color:#008cff}
.wg-stream-select{margin-bottom:16px}
.wg-stream-select select{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.12);border-radius:8px;color:#e0e0e0;padding:8px 12px;font-size:13px;font-family:inherit;width:100%;max-width:400px}
</style>

<h2 style="margin-bottom:4px">Radio Widgets</h2>
<p style="color:#64748b;margin-bottom:16px;font-size:13px">Embeddable widgets for Icecast & SHOUTcast streams. Auto-detects server type.</p>

<div class="wg-stream-select">
<select id="streamSelect" onchange="updateWidgets()">
<option value="0">-- Select a stream --</option>
<?php foreach ($streams as $s): ?>
<option value="<?php echo $s->id; ?>">#<?php echo $s->id; ?> — <?php echo htmlspecialchars($s->server_name ?: '(unnamed)'); ?> (<?php echo $s->server_type ?: '?'; ?>)</option>
<?php endforeach; ?>
</select>
</div>

<div class="wg-grid" id="widgetGrid">
<?php
$widgets = [
    'nowplaying' => ['name' => 'Now Playing', 'desc' => 'Current song, artist, listener count, bitrate, status', 'icon' => '🎵'],
    'player' => ['name' => 'Mini Player', 'desc' => 'HTML5 audio player with play/pause controls', 'icon' => '▶️'],
    'status' => ['name' => 'Stream Status', 'desc' => 'Online/offline badge with bitrate and uptime', 'icon' => '🔵'],
    'listeners' => ['name' => 'Listener Count', 'desc' => 'Current and peak listener numbers', 'icon' => '👥'],
    'songhistory' => ['name' => 'Song History', 'desc' => 'Last 10 played songs with timestamps', 'icon' => '📋'],
    'stats' => ['name' => 'Statistics', 'desc' => 'Combined stats: listeners, bitrate, songs, uptime', 'icon' => '📊'],
];
foreach ($widgets as $key => $w):
?>
<div class="wg-card" data-widget="<?php echo $key; ?>">
<div class="wg-header">
<div><div class="wg-name"><?php echo $w['icon']; ?> <?php echo $w['name']; ?></div><div class="wg-desc"><?php echo $w['desc']; ?></div></div>
</div>
<div class="wg-preview" id="preview-<?php echo $key; ?>">
<div style="color:#64748b;font-size:12px">Select a stream to preview</div>
</div>
<div class="wg-code">
<div class="wg-tabs">
<button class="wg-tab active" onclick="switchCodeTab(this,'<?php echo $key; ?>','js')">JavaScript</button>
<button class="wg-tab" onclick="switchCodeTab(this,'<?php echo $key; ?>','iframe')">iframe</button>
</div>
<textarea id="code-<?php echo $key; ?>-js" readonly onclick="this.select()" placeholder="Select a stream first"></textarea>
<textarea id="code-<?php echo $key; ?>-iframe" readonly onclick="this.select()" style="display:none" placeholder="Select a stream first"></textarea>
</div>
</div>
<?php endforeach; ?>
</div>

<script>
var baseUrl = '<?php echo $baseUrl; ?>';

function updateWidgets() {
    var id = document.getElementById('streamSelect').value;
    if (id == 0) {
        document.querySelectorAll('.wg-card').forEach(function(c) {
            c.querySelector('.wg-preview').innerHTML = '<div style="color:#64748b;font-size:12px">Select a stream to preview</div>';
            c.querySelectorAll('textarea').forEach(function(t) { t.value = 'Select a stream first'; });
        });
        return;
    }
    var widgets = ['nowplaying','player','status','listeners','songhistory','stats'];
    widgets.forEach(function(w) {
        var jsUrl = baseUrl + '/radio/widgets/' + w + '.php?stream=' + id;
        var iframeUrl = baseUrl + '/radio/widgets/' + w + '.php?stream=' + id + '&layout=iframe';
        var jsEmbed = '<script src="' + jsUrl + '"><\/script>';
        var iframeEmbed = '<iframe src="' + iframeUrl + '" width="100%" height="100" frameborder="0" allowtransparency="true" scrolling="no"><\/iframe>';

        document.getElementById('code-' + w + '-js').value = jsEmbed;
        document.getElementById('code-' + w + '-iframe').value = iframeEmbed;

        // Preview via fetch
        var img = new Image();
        var preview = document.getElementById('preview-' + w);
        preview.innerHTML = '<iframe src="' + iframeUrl + '" width="100%" height="100" frameborder="0" allowtransparency="true" scrolling="no" style="border-radius:6px"></iframe>';
    });
}

function switchCodeTab(btn, widget, type) {
    var container = btn.closest('.wg-card');
    container.querySelectorAll('.wg-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
    container.querySelectorAll('textarea').forEach(function(t) { t.style.display = 'none'; });
    document.getElementById('code-' + widget + '-' + type).style.display = '';
}
</script>
