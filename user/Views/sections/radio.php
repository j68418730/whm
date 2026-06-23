<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Radio</h2>
<p style="color:#64748b;margin-bottom:20px">Manage your radio station, DJs, and streaming.</p>
<div class="section-grid">
<a href="/user/radio" class="section-card"><span class="icon">📻</span><div class="name">Radio Dashboard</div><div class="desc">Station overview & stats</div></a>
<a href="/user/dj-manager" class="section-card"><span class="icon">🎧</span><div class="name">DJ Manager</div><div class="desc">Manage DJ accounts</div></a>
<a href="/user/autodj" class="section-card"><span class="icon">🤖</span><div class="name">AutoDJ</div><div class="desc">Automated playlist</div></a>
<a href="/user/listen" class="section-card"><span class="icon">🔊</span><div class="name">Listen Live</div><div class="desc">Stream the station</div></a>
<a href="/user/dj-panel" class="section-card"><span class="icon">🎚️</span><div class="name">DJ Panel</div><div class="desc">Live broadcast panel</div></a>
</div>
