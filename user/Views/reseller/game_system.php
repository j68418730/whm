<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:var(--card_bg,rgba(8,16,28,.85));border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:var(--text,#e0e0e0);transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h3 style="color:var(--accent);margin-bottom:4px">Game System</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:20px">Manage game server hosting for your clients — game packages, clients, templates, and provisioning.</p>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Game Packages</h3><div class="value"><?php echo (int)$gamePackages; ?></div><div class="label">Active game retail packages</div></div>
<div class="stat-card"><h3>Clients w/ Games</h3><div class="value" style="color:#4ade80"><?php echo (int)$gameClients; ?></div><div class="label">Clients running game servers</div></div>
<div class="stat-card"><h3>Server Limit</h3><div class="value" style="color:#38bdf8"><?php echo (int)$serverLimit; ?></div><div class="label">Your allotted capacity</div></div>
</div>

<div class="section-grid">
<a href="/reseller/packages" class="section-card"><div class="icon">📦</div><div class="name">Game Packages</div><div class="count"><?php echo (int)$gamePackages; ?></div><div class="desc">Define game server retail packages</div></a>
<a href="/reseller/clients" class="section-card"><div class="icon">👥</div><div class="name">Clients</div><div class="desc">Clients &amp; account management</div></a>
<a href="/reseller/clients/create" class="section-card"><div class="icon">➕</div><div class="name">New Client</div><div class="desc">Provision a game client</div></a>
<a href="/reseller/provisioning" class="section-card"><div class="icon">⚙️</div><div class="name">Provisioning</div><div class="desc">Provision &amp; configure services</div></a>
</div>

<div class="card" style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:12px">🎮 Game Capabilities</h4>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:12px">Game server abilities are defined when you create a <b>Game Server</b> package — CPU/RAM, SteamCMD, mods/plugins, players, backups, monitoring, and more. Cost is set on billing products later.</p>
<p style="color:#64748b;font-size:12px;margin:0">Your account is allotted up to <b><?php echo (int)$serverLimit; ?> game servers</b>. Sub-feature pages (server instances, templates, nodes) are being added.</p>
</div>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>
