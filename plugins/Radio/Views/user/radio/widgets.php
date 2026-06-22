<style>
.widget-card{background:var(--card_bg);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px}
.widget-card h4{margin:0 0 4px;font-size:14px}
.widget-card p{color:var(--text_muted);font-size:12px;margin:0 0 10px}
.widget-card code{display:block;background:rgba(0,0,0,.4);padding:10px;border-radius:6px;font-size:11px;color:#4ade80;word-break:break-all;max-height:120px;overflow-y:auto}
.widget-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px}
</style>

<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0">Radio Widgets</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">Generate embed code for your radio station website</p></div>
</div>
<div class="row g-2 mt-2">
<div class="col-md-6"><select id="ws-stream" class="form-select"><?php foreach ($streams as $s): ?><option value="<?php echo $s->id; ?>"><?php echo htmlspecialchars($s->server_name ?? 'Stream #'.$s->id); ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><select id="ws-format" class="form-select"><option value="html">HTML</option><option value="js">JavaScript</option><option value="iframe">IFrame</option></select></div>
</div>
</div>

<div class="widget-grid">
<?php
$widgets = [
    'nowplaying' => ['Now Playing', 'bi-music-note', 'Current song, artist, listener count, bitrate, stream status'],
    'player' => ['HTML5 Player', 'bi-play-circle', 'Play/pause, volume, current song, listeners'],
    'listeners' => ['Listener Count', 'bi-people', 'Current, peak listener counts'],
    'songhistory' => ['Song History', 'bi-clock-history', 'Last 10 played songs'],
    'streamstatus' => ['Stream Status', 'bi-broadcast', 'Online/offline, uptime, bitrate'],
    'stats' => ['Statistics', 'bi-bar-chart', 'Listeners, peak, total songs, uptime'],
];
foreach ($widgets as $key => $w): ?>
<div class="widget-card">
<h4><i class="bi <?php echo $w[1]; ?>" style="color:var(--primary);margin-right:6px"></i> <?php echo $w[0]; ?></h4>
<p><?php echo $w[2]; ?></p>
<code id="code-<?php echo $key; ?>">Select stream and format, click Generate</code>
<div style="margin-top:6px"><button class="btn btn-sm btn-primary" onclick="genWidget('<?php echo $key; ?>')">Generate</button> <button class="btn btn-sm btn-secondary" onclick="copyCode('code-<?php echo $key; ?>')">Copy</button></div>
</div>
<?php endforeach; ?>
</div>

<script>
var BASE_URL = 'http://45.61.59.55';
function genWidget(type) {
    var sid = document.getElementById('ws-stream').value;
    var fmt = document.getElementById('ws-format').value;
    var el = document.getElementById('code-' + type);
    var s = BASE_URL;
    var codes = {
        nowplaying: {html:'<div id="ph-nowplaying" data-stream="'+sid+'"><script src="'+s+'/radio/widgets/nowplaying.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+s+'/radio/widgets/nowplaying.php?stream='+sid+'&layout=iframe" width="320" height="200" frameborder="0"><\/iframe>', js:'var el=document.createElement("script");el.src="'+s+'/radio/widgets/nowplaying.php?stream='+sid+'";document.currentScript.parentNode.insertBefore(el,document.currentScript);'},
        player: {html:'<div id="ph-player" data-stream="'+sid+'"><script src="'+s+'/radio/widgets/player.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+s+'/radio/embed.php?stream='+sid+'" width="320" height="160" frameborder="0"><\/iframe>', js:'var el=document.createElement("script");el.src="'+s+'/radio/widgets/player.php?stream='+sid+'";document.currentScript.parentNode.insertBefore(el,document.currentScript);'},
        listeners: {html:'<div id="ph-listeners-'+sid+'"><script src="'+s+'/radio/widgets/listeners.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+s+'/radio/widgets/listeners.php?stream='+sid+'&layout=iframe" width="200" height="100" frameborder="0"><\/iframe>'},
        songhistory: {html:'<div id="ph-history-'+sid+'"><script src="'+s+'/radio/widgets/songhistory.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+s+'/radio/widgets/songhistory.php?stream='+sid+'&layout=iframe" width="320" height="300" frameborder="0"><\/iframe>'},
        streamstatus: {html:'<div id="ph-status-'+sid+'"><script src="'+s+'/radio/widgets/status.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+s+'/radio/widgets/status.php?stream='+sid+'&layout=iframe" width="200" height="80" frameborder="0"><\/iframe>'},
        stats: {html:'<div id="ph-stats-'+sid+'"><script src="'+s+'/radio/widgets/stats.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+s+'/radio/widgets/stats.php?stream='+sid+'&layout=iframe" width="320" height="250" frameborder="0"><\/iframe>'},
    };
    if (codes[type] && codes[type][fmt]) el.textContent = codes[type][fmt];
    else if (codes[type]) el.textContent = codes[type]['html'];
}
function copyCode(id) {
    var el = document.getElementById(id);
    navigator.clipboard.writeText(el.textContent).then(function() {
        var orig = el.style.background;
        el.style.background = 'rgba(74,222,128,.2)';
        setTimeout(function(){el.style.background=orig;},1000);
    });
}
</script>
