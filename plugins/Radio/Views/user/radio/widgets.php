<style>
.wc-wrap{max-width:1200px;margin:0 auto}
.wc-header{background:linear-gradient(135deg,rgba(0,140,255,.12),rgba(168,85,247,.06));border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:24px;margin-bottom:20px}
.wc-header h2{margin:0;font-size:20px;color:#e0e0e0}
.wc-header p{margin:4px 0 0;font-size:12px;color:#64748b}
.wc-bar{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;align-items:end}
.wc-bar .fg{margin:0;flex:1;min-width:150px}
.wc-bar label{font-size:10px;text-transform:uppercase;color:#64748b;letter-spacing:1px;margin-bottom:4px;display:block;font-weight:600}
.wc-bar select{width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none}
.wc-bar select option{background:#0a0e1a;color:#e0e0e0}
.wc-tabs{display:flex;gap:4px;margin-bottom:20px;flex-wrap:wrap}
.wc-tab{padding:7px 14px;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;transition:.15s;color:#64748b;background:rgba(255,255,255,.04);border:1px solid transparent}
.wc-tab:hover{background:rgba(0,140,255,.08);color:#0A84FF}
.wc-tab.act{background:rgba(0,140,255,.15);border-color:rgba(0,140,255,.3);color:#0A84FF}
.wc-sec{display:none}
.wc-sec.act{display:block}
.wc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px}
.wc-card{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;transition:.15s}
.wc-card:hover{border-color:rgba(0,140,255,.15)}
.wc-card h4{margin:0 0 4px;font-size:13px;color:#e0e0e0;font-weight:600}
.wc-card p{color:#64748b;font-size:11px;margin:0 0 10px}
.wc-card code{display:block;background:rgba(0,0,0,.4);padding:10px;border-radius:6px;font-size:10px;color:#4ade80;word-break:break-all;max-height:80px;overflow-y:auto;line-height:1.5}
.wc-acts{margin-top:8px;display:flex;gap:6px}
.btn-s{padding:6px 14px;border-radius:6px;font-size:11px;font-weight:600;border:none;cursor:pointer;transition:.15s;display:inline-block}
.btn-p{background:rgba(0,140,255,.2);color:#0A84FF}
.btn-p:hover{background:rgba(0,140,255,.3)}
.btn-s2{background:rgba(255,255,255,.06);color:#94a3b8}
.btn-s2:hover{background:rgba(255,255,255,.1)}
.wc-cat{font-size:14px;font-weight:700;color:#e0e0e0;margin:24px 0 12px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,.06)}
.wc-cat:first-child{margin-top:0}
</style>
<?php $streams = $streams ?? []; ?>
<div class="wc-wrap">
<div class="wc-header">
<h2>Widget Center</h2>
<p>Generate embed codes for your radio station — works with Icecast &amp; SHOUTcast</p>
</div>
<div class="wc-bar">
<div class="fg"><label>Stream</label><select id="ws-s">
<?php foreach($streams as $s): ?>
<option value="<?=$s->id?>"><?=htmlspecialchars($s->name)?> (<?=strtoupper($s->server_type)?>)</option>
<?php endforeach; ?>
</select></div>
<div class="fg" style="flex:0.5"><label>Format</label><select id="ws-f"><option value="html">HTML</option><option value="iframe">Iframe</option></select></div>
<a href="/user/radio" class="btn-s btn-s2" style="text-decoration:none;margin-bottom:2px">Back</a>
</div>
<div class="wc-tabs">
<div class="wc-tab act" onclick="sw(event,'players')">Players</div>
<div class="wc-tab" onclick="sw(event,'np')">Now Playing</div>
<div class="wc-tab" onclick="sw(event,'history')">History</div>
<div class="wc-tab" onclick="sw(event,'djs')">DJs</div>
<div class="wc-tab" onclick="sw(event,'listeners')">Listeners</div>
<div class="wc-tab" onclick="sw(event,'extra')">Extra</div>
</div>

<div class="wc-sec act" id="sec-players">
<div class="wc-cat">HTML5 Players</div>
<div class="wc-grid">
<?php $cards = [
  ['Full HTML5 Player','full','Full player with play/pause, volume, song info, listeners, progress bar'],
  ['Mini Player','mini','Compact player with play/pause and song display'],
  ['Floating Player','float','Fixed at bottom-right of website'],
  ['Popup Player','popup','Opens player in a separate popup window'],
  ['Sidebar Player','side','Vertical player for sidebars'],
  ['Mobile Player','mob','Optimized for phones'],
  ['Dark Player','dark','Dark themed player'],
  ['Light Player','light','Light themed player'],
  ['Transparent Player','trans','Transparent background, no frame'],
];
foreach($cards as $c): ?>
<div class="wc-card"><h4><?=$c[0]?></h4><p><?=$c[2]?></p>
<code id="c-p-<?=$c[1]?>">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('p-<?=$c[1]?>')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-p-<?=$c[1]?>')">Copy</button></div></div>
<?php endforeach; ?>
</div>
<div class="wc-cat">Listen Live Button</div>
<div class="wc-grid">
<div class="wc-card"><h4>Listen Live Button</h4><p>Styled button that opens the player</p>
<code id="c-p-listen">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('p-listen')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-p-listen')">Copy</button></div></div>
</div>
</div>

<div class="wc-sec" id="sec-np">
<div class="wc-cat">Now Playing Widgets</div>
<div class="wc-grid">
<?php foreach([
  ['Basic Now Playing','np-basic','Artist + Title display'],
  ['Large Now Playing','np-large','Album cover + Artist + Title + Album + Genre'],
  ['Compact Now Playing','np-compact','Single line minimalist'],
] as $c): ?>
<div class="wc-card"><h4><?=$c[0]?></h4><p><?=$c[2]?></p>
<code id="c-<?=$c[1]?>">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('<?=$c[1]?>')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-<?=$c[1]?>')">Copy</button></div></div>
<?php endforeach; ?>
</div>
<div class="wc-cat">Station Info</div>
<div class="wc-grid">
<div class="wc-card"><h4>Station Information</h4><p>Name, genre, bitrate, listener count</p>
<code id="c-station-info">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('station-info')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-station-info')">Copy</button></div></div>
<div class="wc-card"><h4>Stream Status</h4><p>Online/offline badge with uptime</p>
<code id="c-stream-status">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('stream-status')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-stream-status')">Copy</button></div></div>
</div>
</div>

<div class="wc-sec" id="sec-history">
<div class="wc-cat">Song History</div>
<div class="wc-grid">
<?php foreach([5,10,25,50] as $n): ?>
<div class="wc-card"><h4>Last <?=$n?> Songs</h4><p>Recently played tracks</p>
<code id="c-history-<?=$n?>">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('history-<?=$n?>')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-history-<?=$n?>')">Copy</button></div></div>
<?php endforeach; ?>
</div>
</div>

<div class="wc-sec" id="sec-djs">
<div class="wc-cat">DJ Widgets</div>
<div class="wc-grid">
<?php foreach([
  ['Live DJ','dj-live','Currently broadcasting DJ info'],
  ['DJ Schedule','dj-schedule','Weekly DJ schedule grid'],
  ['DJ Social Media','dj-social','DJ social media links'],
] as $c): ?>
<div class="wc-card"><h4><?=$c[0]?></h4><p><?=$c[2]?></p>
<code id="c-<?=$c[1]?>">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('<?=$c[1]?>')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-<?=$c[1]?>')">Copy</button></div></div>
<?php endforeach; ?>
</div>
</div>

<div class="wc-sec" id="sec-listeners">
<div class="wc-cat">Listener Widgets</div>
<div class="wc-grid">
<?php foreach([
  ['Listener Count','list-current','Current listener count display'],
  ['Peak Listeners','list-peak','Highest listener count ever'],
  ['Daily Listeners','list-daily','Today\'s listener count'],
] as $c): ?>
<div class="wc-card"><h4><?=$c[0]?></h4><p><?=$c[2]?></p>
<code id="c-<?=$c[1]?>">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('<?=$c[1]?>')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-<?=$c[1]?>')">Copy</button></div></div>
<?php endforeach; ?>
</div>
</div>

<div class="wc-sec" id="sec-extra">
<div class="wc-cat">Extra Widgets</div>
<div class="wc-grid">
<?php $extra = [
  ['Song Request Form','req-form','Allow visitors to request songs'],
  ['Public DJ List','public-djs','Directory of your station DJs'],
  ['Schedule View','schedule-view','Public schedule display'],
  ['Listen Live Button','listen-live','Large styled listen button'],
  ['DJ Panel Login Link','dj-login','Shareable DJ login URL for your DJs'],
  ['Social Media Links','social','Facebook, X, Instagram, Discord links'],
  ['Share Button','share','Share station on social media'],
  ['Donation Button','donate','PayPal / Stripe donation button'],
  ['Podcast Player','podcast','Embed podcast RSS feed player'],
  ['Event Calendar','events','Upcoming event schedule'],
  ['Weather Widget','weather','Local weather display'],
  ['Contact Widget','contact','Contact form for listeners'],
  ['QR Code Generator','qr','QR code linking to your station'],
  ['Embed Code Generator','embed-gen','Generate embed code from URL'],
  ['Advertisements','advertisements','Display station ads'],
  ['DJ Application','apply','DJ application form for your site'],
];
foreach($extra as $c): ?>
<div class="wc-card"><h4><?=$c[0]?></h4><p><?=$c[2]?></p>
<code id="c-<?=$c[1]?>">Select stream &amp; generate</code>
<div class="wc-acts"><button class="btn-s btn-p" onclick="gw('<?=$c[1]?>')">Generate</button><button class="btn-s btn-s2" onclick="cp('c-<?=$c[1]?>')">Copy</button></div></div>
<?php endforeach; ?>
</div>
</div>
</div>
<script>
var BASE_URL = 'https://planet-hosts.com';
var STREAM_HOST = 'planet-hosts.com';
var STATIONS = <?=json_encode(array_map(function($s){return['id'=>$s->id,'name'=>$s->name,'port'=>$s->port,'type'=>$s->server_type??'icecast','mount'=>$s->mount??'/live'];},$streams))?>;
var sid = function(){return parseInt(document.getElementById('ws-s').value)};
var sname = function(){for(var i=0;i<STATIONS.length;i++)if(STATIONS[i].id===sid())return STATIONS[i].name;return'Radio'};
var sUrl = function(){return 'https://'+STREAM_HOST+'/radio/stream-proxy.php?stream='+sid()};
var fmt = function(){return document.getElementById('ws-f').value};
function sw(e,id){
  document.querySelectorAll('.wc-tab').forEach(function(t){t.classList.remove('act')});
  document.querySelectorAll('.wc-sec').forEach(function(s){s.classList.remove('act')});
  e.currentTarget.classList.add('act');
  document.getElementById('sec-'+id).classList.add('act');
}
function gw(type){
  var el=document.getElementById('c-'+type),s=BASE_URL,x=sid(),f=fmt(),sn=sname(),u=sUrl();
  var ifr=function(url,w,h){return f==='iframe'?'<iframe src="'+url+'" width="'+w+'" height="'+h+'" frameborder="0" style="border-radius:10px;max-width:100%"></iframe>':url};
  var codes = {
    'p-full':'<div id="ph-player" data-stream="'+x+'"><script src="'+s+'/radio/widgets/player.php?stream='+x+'"><\/script><\/div>',
    'p-mini':'<div style="background:rgba(8,16,28,.9);border-radius:10px;padding:10px;text-align:center;max-width:200px"><div style="font-size:11px;color:#94a3b8">Now Playing</div><audio src="'+u+'" preload="none" controls style="width:100%;height:30px"></audio></div>',
    'p-float':'<div id="ph-float" style="position:fixed;bottom:20px;right:20px;z-index:9999;width:320px;border-radius:10px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.3)"><script src="'+s+'/radio/widgets/player.php?stream='+x+'"><\/script><\/div>',
    'p-popup':'<a href="'+s+'/radio/embed.php?stream='+x+'" target="_blank" onclick="window.open(\''+s+'/radio/embed.php?stream='+x+'\',\'radio\',\'width=380,height=250\');return false" style="padding:10px 20px;background:#008cff;color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Open Player</a>',
    'p-side':'<div style="max-width:240px"><script src="'+s+'/radio/widgets/player.php?stream='+x+'"><\/script><\/div>',
    'p-mob':'<meta name="viewport" content="width=device-width,initial-scale=1"><div style="max-width:100%"><script src="'+s+'/radio/widgets/player.php?stream='+x+'"><\/script><\/div>',
    'p-dark':'<div style="background:#0a0e1a;color:#e0e0e0;border-radius:12px;padding:16px;font-family:Inter,sans-serif"><div style="font-weight:700;font-size:13px;margin-bottom:6px">'+sn+'</div><audio src="'+u+'" controls style="width:100%"></audio></div>',
    'p-light':'<div style="background:#fff;color:#1a1a2e;border-radius:12px;padding:16px;font-family:Inter,sans-serif;box-shadow:0 2px 12px rgba(0,0,0,.1)"><div style="font-weight:700;font-size:13px;margin-bottom:6px">'+sn+'</div><audio src="'+u+'" controls style="width:100%"></audio></div>',
    'p-trans':'<div style="background:transparent"><audio src="'+u+'" controls style="width:100%"></audio></div>',
    'p-listen':'<a href="'+s+'/radio/embed.php?stream='+x+'" target="_blank" style="display:inline-block;padding:12px 28px;background:#008cff;color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:15px">Listen Live</a>',
    'np-basic':'<div id="ph-nowplaying" data-stream="'+x+'"><script src="'+s+'/radio/widgets/nowplaying.php?stream='+x+'"><\/script><\/div>',
    'np-large':ifr(s+'/radio/widgets/nowplaying.php?stream='+x+'&layout=iframe',320,220),
    'np-compact':'<div id="ph-np-compact" data-stream="'+x+'"><script src="'+s+'/radio/widgets/nowplaying.php?stream='+x+'"><\/script><\/div>',
    'station-info':'<div style="font-size:12px;color:#94a3b8;font-family:Inter,sans-serif"><div id="ph-si-'+x+'"><script src="'+s+'/radio/widgets/nowplaying.php?stream='+x+'"><\/script><\/div></div>',
    'stream-status':'<div id="ph-status-'+x+'"><script src="'+s+'/radio/widgets/status.php?stream='+x+'"><\/script><\/div>',
    'history-5':ifr(s+'/radio/widgets/songhistory.php?stream='+x+'&limit=5',320,200),
    'history-10':ifr(s+'/radio/widgets/songhistory.php?stream='+x+'&limit=10',320,300),
    'history-25':ifr(s+'/radio/widgets/songhistory.php?stream='+x+'&limit=25',320,400),
    'history-50':ifr(s+'/radio/widgets/songhistory.php?stream='+x+'&limit=50',320,500),
    'dj-live':'<div id="ph-dj-live-'+x+'"><script src="'+s+'/radio/djs.php?stream='+x+'"><\/script><\/div>',
    'dj-schedule':'<iframe src="'+s+'/radio/schedule.php?stream='+x+'" width="100%" height="500" frameborder="0" style="border-radius:10px"></iframe>',
    'dj-social':'<div id="ph-dj-social-'+x+'">Social links widget</div>',
    'list-current':'<div id="ph-listeners-'+x+'"><script src="'+s+'/radio/widgets/listeners.php?stream='+x+'"><\/script><\/div>',
    'list-peak':'<div id="ph-stats-'+x+'"><script src="'+s+'/radio/widgets/stats.php?stream='+x+'"><\/script><\/div>',
    'list-daily':'<div id="ph-stats-'+x+'"><script src="'+s+'/radio/widgets/stats.php?stream='+x+'"><\/script><\/div>',
    'req-form':'<div id="ph-request-'+x+'"><script src="'+s+'/radio/request.php?stream='+x+'"><\/script><\/div>',
    'social':'<div id="ph-social-'+x+'"><script src="'+s+'/radio/widgets/social.php?stream='+x+'"><\/script><\/div>',
    'share':'<div style="display:flex;gap:8px"><a href="https://facebook.com/sharer.php?u='+encodeURIComponent(s+'/radio/embed.php?stream='+x)+'" target="_blank" style="text-decoration:none">📘</a><a href="https://twitter.com/intent/tweet?url='+encodeURIComponent(s+'/radio/embed.php?stream='+x)+'" target="_blank" style="text-decoration:none">🐦</a></div>',
    'donate':'<form action="https://www.paypal.com/donate" method="post" target="_blank"><input type="hidden" name="business" value=""><button type="submit" style="padding:10px 20px;background:#ffc439;color:#000;border:none;border-radius:6px;font-weight:600;cursor:pointer">Donate</button></form>',
    'podcast':'<div id="ph-podcast-'+x+'">Add your RSS feed URL</div>',
    'events':'<div id="ph-events-'+x+'">Event calendar widget</div>',
    'weather':'<div id="ph-weather-'+x+'">Weather widget placeholder</div>',
    'contact':'<form style="max-width:300px"><div style="margin-bottom:8px"><input placeholder="Name" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0"></div><div style="margin-bottom:8px"><input placeholder="Email" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0"></div><div style="margin-bottom:8px"><textarea placeholder="Message" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;min-height:60px"></textarea></div><button style="padding:8px 16px;background:#008cff;color:#fff;border:none;border-radius:6px;cursor:pointer">Send</button></form>',
    'qr':'<div id="ph-qr-'+x+'"><script src="'+s+'/radio/qr.php?stream='+x+'"><\/script><\/div>',
    'embed-gen':'<div style="padding:10px;background:rgba(0,0,0,.3);border-radius:8px"><label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:4px">Page URL</label><input id="embed-url" value="'+s+'/radio/embed.php?stream='+x+'" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px"><div style="margin-top:6px"><button class="btn-s btn-p" onclick="navigator.clipboard.writeText(document.getElementById(\'embed-url\').value)">Copy URL</button></div></div>',
    'public-djs':'<div id="ph-public-djs-'+x+'"><script src="'+s+'/radio/public_djs.php?stream='+x+'"><\/script><\/div>',
    'schedule-view':ifr(s+'/radio/schedule.php?stream='+x,320,400),
    'listen-live':'<a href="'+s+'/radio/embed.php?stream='+x+'" target="_blank" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#008cff,#0066cc);color:#fff;border-radius:10px;text-decoration:none;font-weight:700;font-size:16px">Listen Live</a>',
    'dj-login':'<div style="padding:12px;background:rgba(250,204,21,.06);border:1px solid rgba(250,204,21,.15);border-radius:8px;text-align:center"><div style="font-size:13px;font-weight:600;color:#e0e0e0;margin-bottom:4px">DJ Panel Login</div><div style="font-size:11px;color:#94a3b8;margin-bottom:8px">Give this link to your DJs:</div><input id="dj-login-url" value="https://planet-hosts.com/dj_panel.php" readonly style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#4ade80;font-size:11px;text-align:center;font-family:monospace;margin-bottom:6px"><button class="btn-s btn-p" onclick="navigator.clipboard.writeText(document.getElementById(\'dj-login-url\').value)">Copy Link</button></div>',
    'apply':'<div id="ph-apply-'+x+'">DJ application form - create at /user/radio</div>',
    'advertisements':'<div id="ph-ads-'+x+'"><script src="'+s+'/radio/advertisements.php?stream='+x+'"><\/script><\/div>',
  };
  el.textContent = codes[type] || 'Generate failed: unknown type '+type;
}
function cp(id){
  var el=document.getElementById(id);
  navigator.clipboard.writeText(el.textContent).then(function(){el.style.background='rgba(74,222,128,.2)';setTimeout(function(){el.style.background=''},1000)});
}
</script>