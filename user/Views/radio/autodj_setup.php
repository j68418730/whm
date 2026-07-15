<style>
.wizard-wrap{max-width:800px;margin:0 auto}
.wizard-header{text-align:center;padding:30px 20px;background:linear-gradient(135deg,rgba(0,140,255,.08),rgba(168,85,247,.05));border-radius:12px;margin-bottom:20px;border:1px solid rgba(0,191,255,.1)}
.wizard-header h1{font-size:22px;font-weight:700;color:#e0e0e0;margin:0 0 6px}
.wizard-header p{font-size:12px;color:#64748b;margin:0}
.wizard-progress{display:flex;gap:2px;margin-bottom:24px;background:rgba(8,16,28,.6);border-radius:8px;padding:4px;overflow-x:auto}
.wizard-progress .step{flex:1;text-align:center;padding:6px 4px;border-radius:6px;font-size:9px;color:#64748b;white-space:nowrap;min-width:50px}
.wizard-progress .step.active{background:rgba(0,140,255,.2);color:#0A84FF;font-weight:600}
.wizard-progress .step.done{color:#00C853}
.wizard-card{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:24px;margin-bottom:16px}
.wizard-card h2{font-size:16px;font-weight:600;color:#e0e0e0;margin:0 0 4px}
.wizard-card .desc{font-size:11px;color:#64748b;margin-bottom:16px}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:11px;color:#94a3b8;margin-bottom:4px;font-weight:500}
.form-group .hint{font-size:10px;color:#64748b;margin-top:3px}
.inp{padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;width:100%;box-sizing:border-box}
.inp:focus{border-color:rgba(0,140,255,.4)}
.inp-sm{padding:6px 8px;font-size:12px}
select.inp{color:#e0e0e0;cursor:pointer}
select.inp option{background:#0a0e1a;color:#e0e0e0}
textarea.inp{resize:vertical;min-height:60px}
.btn{padding:10px 24px;border-radius:8px;font-size:13px;font-weight:500;border:none;cursor:pointer;transition:.15s;text-decoration:none;display:inline-block}
.btn-primary{background:rgba(0,140,255,.2);color:#0A84FF}
.btn-primary:hover{background:rgba(0,140,255,.3)}
.btn-secondary{background:rgba(255,255,255,.06);color:#94a3b8}
.btn-secondary:hover{background:rgba(255,255,255,.1)}
.btn-success{background:rgba(0,200,83,.15);color:#00C853}
.btn-success:hover{background:rgba(0,200,83,.25)}
.wizard-nav{display:flex;justify-content:space-between;margin-top:20px;gap:10px}
.radio-group{display:flex;gap:10px;flex-wrap:wrap}
.radio-group label{display:flex;align-items:center;gap:8px;padding:10px 16px;background:rgba(0,0,0,.3);border-radius:8px;border:1px solid rgba(255,255,255,.06);cursor:pointer;font-size:12px;color:#c0c0c0;transition:.1s}
.radio-group label:hover{border-color:rgba(0,140,255,.2);color:#e0e0e0}
.radio-group input:checked+span{color:#0A84FF}
.radio-group input[type=radio]{accent-color:#0A84FF}
.check-group{display:flex;flex-wrap:wrap;gap:8px}
.check-group label{display:flex;align-items:center;gap:6px;padding:8px 12px;background:rgba(0,0,0,.3);border-radius:6px;font-size:11px;color:#c0c0c0;cursor:pointer}
.check-group label:hover{color:#e0e0e0}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.upload-area{border:2px dashed rgba(0,140,255,.2);border-radius:10px;padding:40px;text-align:center;color:#64748b;cursor:pointer;transition:.15s}
.upload-area:hover{border-color:rgba(0,140,255,.4);color:#94a3b8}
.feature-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.feature-item{padding:12px;background:rgba(0,0,0,.3);border-radius:8px;text-align:center}
.feature-item .icon{font-size:28px;margin-bottom:4px;opacity:.6}
.feature-item .label{font-size:11px;color:#c0c0c0}
</style>
<?php $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; ?>
<div class="wizard-wrap">
<div class="wizard-header"><h1><?=$step===1?'Welcome to AutoDJ Setup':($step===12?'Setup Complete!':'AutoDJ Setup - Step '.$step.'/12')?></h1><p>Planet Hosts AutoDJ Configuration Wizard</p></div>
<div class="wizard-progress">
<?php for($i=1;$i<=12;$i++): $cls = $i==$step?'active':($i<$step?'done':''); ?>
<div class="step <?=$cls?>"><?=$i<=9?'0':''?><?=$i?></div>
<?php endfor; ?>
</div>
<form method="post" action="/user/radio/autodj/setup?step=<?=$step?>&station_id=<?=$station->id?>" enctype="multipart/form-data">
<input type="hidden" name="station_id" value="<?=$station->id?>">

<?php if ($step === 1): ?>
<div class="wizard-card">
<h2>Welcome to AutoDJ Setup</h2>
<div class="desc">This wizard will guide you through configuring AutoDJ for <strong><?=htmlspecialchars($station->name)?></strong>. We'll set up streaming engine, audio quality, playlists, rotation rules, and more.</div>
<div style="text-align:center;padding:20px 0">
<div style="font-size:60px;margin-bottom:10px">&#127925;</div>
<div style="font-size:14px;color:#c0c0c0;margin-bottom:4px">Estimated setup time: <strong>5 minutes</strong></div>
<div style="font-size:11px;color:#64748b">You can save and continue later at any step</div>
</div>
</div>
<div class="wizard-nav"><div></div><button class="btn btn-primary">Start Setup &raquo;</button></div>

<?php elseif ($step === 2): ?>
<div class="wizard-card">
<h2>Basic Station Information</h2>
<div class="desc">Tell us about your station</div>
<div class="grid-2">
<div class="form-group"><label>Station Name</label><input class="inp" name="station_name" value="<?=htmlspecialchars($config->station_name?:$station->name)?>"></div>
<div class="form-group"><label>Genre</label><input class="inp" name="genre" value="<?=htmlspecialchars($config->genre?:$station->genre?:'')?>" placeholder="Rock, Pop, Talk, etc."></div>
</div>
<div class="form-group"><label>Description</label><textarea class="inp" name="station_description" rows="3"><?=htmlspecialchars($config->station_description?:$station->description?:'')?></textarea></div>
<div class="grid-3">
<div class="form-group"><label>Language</label><input class="inp" name="language" value="<?=htmlspecialchars($config->language?:'English')?>"></div>
<div class="form-group"><label>Country</label><input class="inp" name="country" value="<?=htmlspecialchars($config->country?:'')?>" placeholder="US, GB, etc."></div>
<div class="form-group"><label>Time Zone</label><select class="inp" name="timezone"><option value="UTC" <?=$config->timezone==='UTC'?'selected':''?>>UTC</option><option value="America/New_York" <?=$config->timezone==='America/New_York'?'selected':''?>>Eastern</option><option value="America/Chicago" <?=$config->timezone==='America/Chicago'?'selected':''?>>Central</option><option value="America/Denver" <?=$config->timezone==='America/Denver'?'selected':''?>>Mountain</option><option value="America/Los_Angeles" <?=$config->timezone==='America/Los_Angeles'?'selected':''?>>Pacific</option><option value="Europe/London" <?=$config->timezone==='Europe/London'?'selected':''?>>London</option><option value="Europe/Berlin" <?=$config->timezone==='Europe/Berlin'?'selected':''?>>Berlin</option><option value="Asia/Tokyo" <?=$config->timezone==='Asia/Tokyo'?'selected':''?>>Tokyo</option></select></div>
</div>
<div class="grid-2">
<div class="form-group"><label>Station Website</label><input class="inp" name="station_website" value="<?=htmlspecialchars($config->station_website?:'')?>" placeholder="https://"></div>
<div class="form-group"><label>Station Email</label><input class="inp" type="email" name="station_email" value="<?=htmlspecialchars($config->station_email?:'')?>"></div>
</div>
</div>
<div class="wizard-nav"><a href="/user/radio" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 3): ?>
<?php $engine = $config->wizard_step >= 3 ? $config->streaming_engine : ($station->server_type === 'shoutcast' ? 'shoutcast2' : ($station->server_type === 'shoutcast1' ? 'shoutcast1' : 'icecast')); ?>
<?php $labels = ['icecast' => 'Icecast', 'shoutcast' => 'SHOUTcast', 'shoutcast1' => 'SHOUTcast v1', 'shoutcast2' => 'SHOUTcast v2']; ?>
<div class="wizard-card">
<h2>Streaming Engine</h2>
<div class="desc">Your station is configured to use <strong><?=$labels[$engine]??strtoupper($engine)?></strong></div>
<input type="hidden" name="streaming_engine" value="<?=$engine?>">
<div class="feature-grid">
<div class="feature-item"><div class="icon">&#128264;</div><div class="label"><?=$labels[$engine]??strtoupper($engine)?></div></div>
<div class="feature-item"><div class="icon">&#127911;</div><div class="label">Port <?=$station->port?:'Auto'?></div></div>
</div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Continue &raquo;</button></div>

<?php elseif ($step === 4): ?>
<div class="wizard-card">
<h2>Audio Settings</h2>
<div class="desc">Configure your audio encoding quality</div>
<div class="form-group"><label>Audio Codec</label>
<div class="radio-group">
<label><input type="radio" name="audio_codec" value="mp3" <?=$config->audio_codec==='mp3'?'checked':''?>><span>MP3</span></label>
<label><input type="radio" name="audio_codec" value="aac" <?=$config->audio_codec==='aac'?'checked':''?>><span>AAC</span></label>
<label><input type="radio" name="audio_codec" value="aacp" <?=$config->audio_codec==='aacp'?'checked':''?>><span>AAC+</span></label>
<label><input type="radio" name="audio_codec" value="opus" <?=$config->audio_codec==='opus'?'checked':''?>><span>Opus</span></label>
</div></div>
<div class="grid-3">
<div class="form-group"><label>Bitrate</label><select class="inp" name="bitrate"><?php foreach([64,96,128,192,256,320] as $b): ?><option value="<?=$b?>" <?=($config->bitrate?:128)==$b?'selected':''?>><?=$b?> kbps</option><?php endforeach; ?></select></div>
<div class="form-group"><label>Sample Rate</label><select class="inp" name="sample_rate"><option value="44100" <?=$config->sample_rate==44100?'selected':''?>>44100 Hz</option><option value="48000" <?=$config->sample_rate==48000?'selected':''?>>48000 Hz</option></select></div>
<div class="form-group"><label>Channels</label><select class="inp" name="channels"><option value="stereo" <?=$config->channels==='stereo'?'selected':''?>>Stereo</option><option value="mono" <?=$config->channels==='mono'?'selected':''?>>Mono</option></select></div>
</div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 5): ?>
<div class="wizard-card">
<h2>AutoDJ</h2>
<div class="desc">Configure AutoDJ playback behavior</div>
<div class="check-group" style="margin-bottom:12px">
<label><input type="hidden" name="autodj_enabled" value="0"><input type="checkbox" name="autodj_enabled" value="1" <?=$config->autodj_enabled?'checked':''?>> <span>Enable AutoDJ</span></label>
</div>
<div class="form-group"><label>Playlist Mode</label>
<select class="inp" name="playlist_mode">
<option value="sequential" <?=$config->playlist_mode==='sequential'?'selected':''?>>Sequential</option>
<option value="random" <?=$config->playlist_mode==='random'?'selected':''?>>Random</option>
<option value="weighted" <?=$config->playlist_mode==='weighted'?'selected':''?>>Weighted</option>
</select></div>
<div class="grid-3">
<div class="form-group"><label>Crossfade Time</label><input class="inp" type="number" name="crossfade_time" value="<?=$config->crossfade_time?:5?>"><div class="hint">Seconds</div></div>
<div class="form-group"><label>&nbsp;</label><div class="check-group"><label><input type="hidden" name="crossfade_enabled" value="0"><input type="checkbox" name="crossfade_enabled" value="1" <?=$config->crossfade_enabled?'checked':''?>> <span>Crossfade</span></label></div></div>
</div>
<div class="grid-3">
<div class="check-group"><label><input type="hidden" name="normalize_audio" value="0"><input type="checkbox" name="normalize_audio" value="1" <?=$config->normalize_audio?'checked':''?>> <span>Normalize Audio</span></label></div>
<div class="check-group"><label><input type="hidden" name="replaygain" value="0"><input type="checkbox" name="replaygain" value="1" <?=$config->replaygain?'checked':''?>> <span>ReplayGain</span></label></div>
<div class="check-group"><label><input type="hidden" name="silence_detection" value="0"><input type="checkbox" name="silence_detection" value="1" <?=$config->silence_detection?'checked':''?>> <span>Silence Detection</span></label></div>
</div>
<div class="check-group"><label><input type="hidden" name="remove_duplicates" value="0"><input type="checkbox" name="remove_duplicates" value="1" <?=$config->remove_duplicates?'checked':''?>> <span>Remove Duplicates</span></label></div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 6): ?>
<div class="wizard-card">
<h2>Playlist</h2>
<div class="desc">Create playlists for your station</div>
<div class="grid-2">
<?php foreach(['Morning','Afternoon','Evening','Night','Weekend','Holiday','Emergency','Default'] as $pl): ?>
<label style="display:flex;align-items:center;gap:8px;padding:10px;background:rgba(0,0,0,.3);border-radius:8px;font-size:12px;color:#c0c0c0;cursor:pointer">
<input type="checkbox" name="preset_playlists[]" value="<?=$pl?>"> <span><?=$pl?></span></label>
<?php endforeach; ?>
</div>
<div class="form-group" style="margin-top:12px"><label>Custom Playlist Name</label><input class="inp" name="custom_playlist" placeholder="Enter custom playlist name"></div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 7): ?>
<div class="wizard-card">
<h2>Rotation Rules</h2>
<div class="desc">Control how often songs repeat</div>
<div class="grid-3">
<div class="form-group"><label>Max Artist Repeat</label><select class="inp" name="max_artist_repeat"><option value="15" <?=$config->max_artist_repeat==15?'selected':''?>>15 Minutes</option><option value="30" <?=$config->max_artist_repeat==30?'selected':''?>>30 Minutes</option><option value="60" <?=$config->max_artist_repeat==60?'selected':''?>>1 Hour</option><option value="120" <?=$config->max_artist_repeat==120?'selected':''?>>2 Hours</option><option value="240" <?=$config->max_artist_repeat==240?'selected':''?>>4 Hours</option></select></div>
<div class="form-group"><label>Max Song Repeat</label><select class="inp" name="max_song_repeat"><option value="60" <?=$config->max_song_repeat==60?'selected':''?>>1 Hour</option><option value="120" <?=$config->max_song_repeat==120?'selected':''?>>2 Hours</option><option value="240" <?=$config->max_song_repeat==240?'selected':''?>>4 Hours</option><option value="480" <?=$config->max_song_repeat==480?'selected':''?>>8 Hours</option></select></div>
<div class="form-group"><label>Max Album Repeat</label><select class="inp" name="max_album_repeat"><option value="30" <?=$config->max_album_repeat==30?'selected':''?>>30 Minutes</option><option value="60" <?=$config->max_album_repeat==60?'selected':''?>>1 Hour</option><option value="120" <?=$config->max_album_repeat==120?'selected':''?>>2 Hours</option><option value="240" <?=$config->max_album_repeat==240?'selected':''?>>4 Hours</option></select></div>
</div>
<div class="check-group">
<label><input type="hidden" name="shuffle_enabled" value="0"><input type="checkbox" name="shuffle_enabled" value="1" <?=$config->shuffle_enabled?'checked':''?>> <span>Shuffle</span></label>
<label><input type="hidden" name="weight_new_songs" value="0"><input type="checkbox" name="weight_new_songs" value="1" <?=$config->weight_new_songs?'checked':''?>> <span>Weight New Songs</span></label>
<label><input type="hidden" name="weight_favorites" value="0"><input type="checkbox" name="weight_favorites" value="1" <?=$config->weight_favorites?'checked':''?>> <span>Weight Favorites</span></label>
</div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 8): ?>
<div class="wizard-card">
<h2>DJ Override</h2>
<div class="desc">Configure live DJ integration</div>
<div class="grid-2">
<div class="check-group"><label><input type="hidden" name="allow_live_djs" value="0"><input type="checkbox" name="allow_live_djs" value="1" <?=$config->allow_live_djs?'checked':''?>> <span>Allow Live DJs</span></label></div>
<div class="check-group"><label><input type="hidden" name="auto_switch_dj" value="0"><input type="checkbox" name="auto_switch_dj" value="1" <?=$config->auto_switch_dj?'checked':''?>> <span>Auto-Switch to DJ</span></label></div>
</div>
<div class="grid-2">
<div class="check-group"><label><input type="hidden" name="fallback_autodj" value="0"><input type="checkbox" name="fallback_autodj" value="1" <?=$config->fallback_autodj?'checked':''?>> <span>Fallback to AutoDJ</span></label></div>
<div class="form-group"><label>Reconnect Time</label><select class="inp" name="reconnect_time"><option value="10" <?=$config->reconnect_time==10?'selected':''?>>10 Seconds</option><option value="30" <?=$config->reconnect_time==30?'selected':''?>>30 Seconds</option><option value="60" <?=$config->reconnect_time==60?'selected':''?>>1 Minute</option><option value="300" <?=$config->reconnect_time==300?'selected':''?>>5 Minutes</option></select></div>
</div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 9): ?>
<div class="wizard-card">
<h2>Jingles</h2>
<div class="desc">Configure station IDs, sweepers, and promos</div>
<div class="check-group" style="margin-bottom:12px"><label><input type="hidden" name="jingles_enabled" value="0"><input type="checkbox" name="jingles_enabled" value="1" <?=$config->jingles_enabled?'checked':''?>> <span>Enable Jingles</span></label></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div>
<div class="upload-area" style="padding:20px" onclick="document.getElementById('jingle-upload').click()">Upload Station IDs</div>
<input id="jingle-upload" type="file" name="jingles[]" multiple accept="audio/*" style="display:none">
</div>
<div>
<div class="upload-area" style="padding:20px" onclick="document.getElementById('sweeper-upload').click()">Upload Sweepers</div>
<input id="sweeper-upload" type="file" name="sweepers[]" multiple accept="audio/*" style="display:none">
</div>
</div>
<div class="grid-2" style="margin-top:12px">
<div class="form-group"><label>Play Every</label><select class="inp" name="jingle_play_every"><option value="5" <?=$config->jingle_play_every==5?'selected':''?>>5 Minutes</option><option value="10" <?=$config->jingle_play_every==10?'selected':''?>>10 Minutes</option><option value="15" <?=$config->jingle_play_every==15?'selected':''?>>15 Minutes</option><option value="30" <?=$config->jingle_play_every==30?'selected':''?>>30 Minutes</option><option value="60" <?=$config->jingle_play_every==60?'selected':''?>>1 Hour</option></select></div>
<div class="form-group"><label>Position</label><select class="inp" name="jingle_position"><option value="top" <?=$config->jingle_position==='top'?'selected':''?>>Top of Hour</option><option value="bottom" <?=$config->jingle_position==='bottom'?'selected':''?>>Bottom of Hour</option><option value="random" <?=$config->jingle_position==='random'?'selected':''?>>Random</option></select></div>
</div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 10): ?>
<div class="wizard-card">
<h2>Advertisements</h2>
<div class="desc">Configure ad insertion</div>
<div class="check-group" style="margin-bottom:12px"><label><input type="hidden" name="ads_enabled" value="0"><input type="checkbox" name="ads_enabled" value="1" <?=$config->ads_enabled?'checked':''?>> <span>Enable Ads</span></label></div>
<div class="grid-2">
<div class="form-group"><label>Max Ads Per Hour</label><select class="inp" name="max_ads_per_hour"><option value="1" <?=$config->max_ads_per_hour==1?'selected':''?>>1</option><option value="2" <?=$config->max_ads_per_hour==2?'selected':''?>>2</option><option value="3" <?=$config->max_ads_per_hour==3?'selected':''?>>3</option><option value="4" <?=$config->max_ads_per_hour==4?'selected':''?>>4</option><option value="6" <?=$config->max_ads_per_hour==6?'selected':''?>>6</option><option value="8" <?=$config->max_ads_per_hour==8?'selected':''?>>8</option></select></div>
<div class="upload-area" style="padding:20px" onclick="document.getElementById('ad-upload').click()">Upload Ads</div>
<input id="ad-upload" type="file" name="ads[]" multiple accept="audio/*" style="display:none">
</div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 11): ?>
<div class="wizard-card">
<h2>Song Requests</h2>
<div class="desc">Configure listener song requests</div>
<div class="check-group" style="margin-bottom:12px"><label><input type="hidden" name="requests_enabled" value="0"><input type="checkbox" name="requests_enabled" value="1" <?=$config->requests_enabled?'checked':''?>> <span>Enable Requests</span></label></div>
<div class="grid-3">
<div class="form-group"><label>Request Delay</label><select class="inp" name="request_delay"><option value="0" <?=$config->request_delay==0?'selected':''?>>None</option><option value="15" <?=$config->request_delay==15?'selected':''?>>15 Minutes</option><option value="30" <?=$config->request_delay==30?'selected':''?>>30 Minutes</option><option value="60" <?=$config->request_delay==60?'selected':''?>>1 Hour</option></select></div>
<div class="form-group"><label>Max Requests/Listener</label><select class="inp" name="max_requests_per_listener"><option value="1" <?=$config->max_requests_per_listener==1?'selected':''?>>1</option><option value="2" <?=$config->max_requests_per_listener==2?'selected':''?>>2</option><option value="3" <?=$config->max_requests_per_listener==3?'selected':''?>>3</option><option value="5" <?=$config->max_requests_per_listener==5?'selected':''?>>5</option></select></div>
<div class="form-group"><label>&nbsp;</label><div class="hint" style="margin-top:8px">Manage blacklist after setup</div></div>
</div>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-primary">Save &amp; Continue &raquo;</button></div>

<?php elseif ($step === 12): ?>
<div class="wizard-card">
<h2>Setup Complete!</h2>
<div class="desc">Your AutoDJ is ready to go</div>
<div class="feature-grid">
<div class="feature-item"><div class="icon">&#9989;</div><div class="label">AutoDJ Enabled</div></div>
<div class="feature-item"><div class="icon">&#9989;</div><div class="label">Playlist Loaded</div></div>
<div class="feature-item"><div class="icon">&#9989;</div><div class="label">Live DJ Enabled</div></div>
<div class="feature-item"><div class="icon">&#9989;</div><div class="label">SSL Enabled</div></div>
</div>
<div style="text-align:center;padding:20px 0">
<div style="font-size:48px;margin-bottom:10px">&#127881;</div>
<div style="font-size:16px;color:#c0c0c0;margin-bottom:4px">Station Ready</div>
<div style="font-size:12px;color:#64748b">All settings have been saved. You can now manage your AutoDJ from the dashboard.</div>
</div>
<label class="check-group" style="justify-content:center;margin-bottom:10px"><input type="hidden" name="autodj_enabled" value="0"><input type="checkbox" name="autodj_enabled" value="1" <?=$config->autodj_enabled?'checked':''?>> <span>Start AutoDJ immediately</span></label>
</div>
<div class="wizard-nav"><a href="/user/radio/autodj/setup?step=<?=$step-1?>&station_id=<?=$station->id?>" class="btn btn-secondary">&laquo; Back</a><button class="btn btn-success">Finish Setup &raquo;</button></div>
<?php endif; ?>
</form>
</div>
