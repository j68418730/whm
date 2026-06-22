<style>
.widget-card{background:var(--card_bg);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px;transition:.15s}
.widget-card:hover{border-color:var(--primary)}
.widget-card h4{margin:0 0 4px;font-size:14px}
.widget-card p{color:var(--text_muted);font-size:12px;margin:0 0 10px}
.widget-card code{display:block;background:rgba(0,0,0,.4);padding:10px;border-radius:6px;font-size:11px;color:#4ade80;word-break:break-all;max-height:120px;overflow-y:auto}
.widget-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:12px}
</style>

<div class="card">
<h3>Radio Widget Generator</h3>
<p style="color:var(--text_muted);font-size:13px">Select a stream and widget type, then copy the embed code for your website.</p>
<div class="row g-2 mb-3">
<div class="col-md-4"><select id="ws-stream" class="form-select"><?php foreach ($streams as $s): ?><option value="<?php echo $s->id; ?>"><?php echo htmlspecialchars($s->server_name ?? 'Stream #'.$s->id); ?> (<?php echo $s->port; ?>)</option><?php endforeach; ?></select></div>
<div class="col-md-3"><select id="ws-format" class="form-select"><option value="html">HTML</option><option value="js">JavaScript</option><option value="iframe">IFrame</option><option value="wordpress">WordPress</option></select></div>
</div>
</div>

<div class="widget-grid">
<?php
$widgets = [
    'nowplaying' => ['Now Playing', 'bi-music-note', 'Current song, artist, album art, listener count, bitrate, stream status'],
    'djstatus' => ['DJ Status', 'bi-mic', 'Current DJ, avatar, live/offline/autodj status'],
    'player' => ['HTML5 Player', 'bi-play-circle', 'Play/pause, volume, current song, listeners, station info'],
    'miniplayer' => ['Mini Player', 'bi-music-note-beamed', 'Floating bottom-corner player with play button and current song'],
    'songhistory' => ['Song History', 'bi-clock-history', 'Last 10 played songs with artist, time, duration'],
    'listeners' => ['Listener Count', 'bi-people', 'Current, peak, and unique listener counts'],
    'stats' => ['Statistics', 'bi-bar-chart', 'Listener count, peak, total songs, bandwidth, uptime'],
    'albumart' => ['Album Art', 'bi-image', 'Current song artwork that auto-updates'],
    'requests' => ['Song Requests', 'bi-hand-thumbs-up', 'Search and request songs from your library'],
    'schedule' => ['DJ Schedule', 'bi-calendar', 'Weekly DJ schedule with show times and hosts'],
    'streamstatus' => ['Stream Status', 'bi-broadcast', 'Online/offline, uptime, bitrate, codec, mount point'],
    'stationinfo' => ['Station Info', 'bi-info-circle', 'Station name, genre, description, website, social links'],
    'shoutbox' => ['Shoutbox Chat', 'bi-chat-dots', 'User messages and listener chat (like Cbox)'],
    'topsongs' => ['Top Songs', 'bi-trophy', 'Most played, most requested, most liked songs'],
    'social' => ['Social Links', 'bi-link-45deg', 'Facebook, X, Instagram, TikTok, Discord links widget'],
    'favorites' => ['Favorite Song', 'bi-heart', 'Like/dislike current song with rating storage'],
    'currentshow' => ['Current Show', 'bi-tv', 'Current show name, host, description, start/end time'],
    'nextshow' => ['Next Show', 'bi-skip-forward', 'Upcoming show with host and time'],
    'advertisements' => ['Advertisements', 'bi-megaphone', 'Rotating ads and promos for your station'],
    'discord' => ['Discord Widget', 'bi-discord', 'Discord server invite and online member count'],
];
foreach ($widgets as $key => $w): ?>
<div class="widget-card">
<h4><i class="bi <?php echo $w[1]; ?>" style="color:var(--primary);margin-right:6px"></i> <?php echo $w[0]; ?></h4>
<p><?php echo $w[2]; ?></p>
<code id="code-<?php echo $key; ?>">Select a stream and format above, then click Generate</code>
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
    var server = BASE_URL;
    var codes = {
        nowplaying: {html:'<div id="ph-nowplaying" data-stream="'+sid+'"><script src="'+server+'/radio/widgets/nowplaying.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+server+'/radio/widgets/nowplaying.php?stream='+sid+'&layout=iframe" width="320" height="200" frameborder="0"><\/iframe>', js:'var el=document.createElement("script");el.src="'+server+'/radio/widgets/nowplaying.php?stream='+sid+'";document.currentScript.parentNode.insertBefore(el,document.currentScript);', wordpress:'[ph_nowplaying stream="'+sid+'"]'},
        player: {html:'<div id="ph-player" data-stream="'+sid+'"><script src="'+server+'/radio/widgets/player.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+server+'/radio/embed.php?stream='+sid+'" width="320" height="160" frameborder="0"><\/iframe>', js:'var el=document.createElement("script");el.src="'+server+'/radio/widgets/player.php?stream='+sid+'";document.currentScript.parentNode.insertBefore(el,document.currentScript);', wordpress:'[ph_player stream="'+sid+'"]'},
        listeners: {html:'<div id="ph-listeners-'+sid+'"><script src="'+server+'/radio/widgets/listeners.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+server+'/radio/widgets/listeners.php?stream='+sid+'&layout=iframe" width="200" height="100" frameborder="0"><\/iframe>'},
        songhistory: {html:'<div id="ph-history-'+sid+'"><script src="'+server+'/radio/widgets/songhistory.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+server+'/radio/widgets/songhistory.php?stream='+sid+'&layout=iframe" width="320" height="300" frameborder="0"><\/iframe>'},
        streamstatus: {html:'<div id="ph-status-'+sid+'"><script src="'+server+'/radio/widgets/status.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+server+'/radio/widgets/status.php?stream='+sid+'&layout=iframe" width="200" height="80" frameborder="0"><\/iframe>'},
        stats: {html:'<div id="ph-stats-'+sid+'"><script src="'+server+'/radio/widgets/stats.php?stream='+sid+'"><\/script><\/div>', iframe:'<iframe src="'+server+'/radio/widgets/stats.php?stream='+sid+'&layout=iframe" width="320" height="250" frameborder="0"><\/iframe>'},
    };
    if (codes[type] && codes[type][fmt]) { el.textContent = codes[type][fmt]; }
    else if (codes[type]) { el.textContent = codes[type]['html']; }
    else { el.textContent = 'Widget code generation coming soon for ' + type; }
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
