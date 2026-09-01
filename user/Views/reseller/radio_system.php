<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:var(--card_bg,rgba(8,16,28,.85));border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:var(--text,#e0e0e0);transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h3 style="color:var(--accent);margin-bottom:4px">Radio System</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:20px">Manage radio streaming for your clients — stations, streaming packages, and DJ / AutoDJ capabilities.</p>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Radio Packages</h3><div class="value"><?php echo (int)$radioPackages; ?></div><div class="label">Active radio retail packages</div></div>
<div class="stat-card"><h3>Clients w/ Radio</h3><div class="value" style="color:#4ade80"><?php echo (int)$radioClients; ?></div><div class="label">Clients running radio</div></div>
<div class="stat-card"><h3>Stations</h3><div class="value" style="color:#38bdf8"><?php echo (int)$radioStations; ?></div><div class="label">Radio stations provisioned</div></div>
<div class="stat-card"><h3>Station Limit</h3><div class="value"><?php echo (int)$stationLimit; ?></div><div class="label">Your allotted capacity</div></div>
</div>

<div class="section-grid">
<a href="/reseller/packages" class="section-card"><div class="icon">📦</div><div class="name">Radio Packages</div><div class="count"><?php echo (int)$radioPackages; ?></div><div class="desc">Define radio/music retail packages</div></a>
<a href="/reseller/clients" class="section-card"><div class="icon">👥</div><div class="name">Clients</div><div class="desc">Clients &amp; account management</div></a>
<a href="/reseller/clients/create" class="section-card"><div class="icon">➕</div><div class="name">New Client</div><div class="desc">Provision a radio client</div></a>
<a href="/reseller/provisioning" class="section-card"><div class="icon">⚙️</div><div class="name">Provisioning</div><div class="desc">Provision &amp; configure services</div></a>
</div>

<div class="card" style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:12px">📻 Radio Capabilities</h4>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:12px">Radio server abilities are defined when you create a <b>Radio / Music</b> package — streaming engine, stations, listeners, AutoDJ, DJ accounts, codecs, and more. Cost is set on billing products later.</p>
<p style="color:#64748b;font-size:12px;margin:0">Your account is allotted up to <b><?php echo (int)$stationLimit; ?> radio stations</b>. Sub-feature pages (streams, DJ accounts, AutoDJ) are being added.</p>
</div>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>
