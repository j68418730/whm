<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>Accounts</h2>
<p style="color:#64748b;margin-bottom:20px">Manage hosting accounts, packages, resellers, and administrators.</p>

<div class="section-grid">
<a href="/admin/account/create" class="section-card"><div class="icon">➕</div><div class="name">Create Account</div><div class="desc">New hosting account</div></a>
<a href="/admin/account" class="section-card"><div class="icon">👥</div><div class="count"><?php echo $total_accounts; ?></div><div class="name">Accounts</div><div class="desc">List and manage accounts</div></a>
<a href="/admin/packages" class="section-card"><div class="icon">📦</div><div class="count"><?php echo $total_packages; ?></div><div class="name">Packages</div><div class="desc">Hosting packages & features</div></a>
<a href="/admin/feature-lists" class="section-card"><div class="icon">📋</div><div class="name">Feature Lists</div><div class="desc">Permissions & limits</div></a>
<a href="/admin/reseller" class="section-card"><div class="icon">🤝</div><div class="count"><?php echo $total_resellers; ?></div><div class="name">Resellers</div><div class="desc">Reseller accounts</div></a>
<a href="/admin/admins" class="section-card"><div class="icon">👑</div><div class="count"><?php echo $total_admins; ?></div><div class="name">Admins</div><div class="desc">Administrator accounts</div></a>
<a href="/admin/roles" class="section-card"><div class="icon">🛡️</div><div class="name">Roles</div><div class="desc">Permission roles</div></a>
<a href="/admin/userfeatures" class="section-card"><div class="icon">🔧</div><div class="name">User Features</div><div class="desc">Feature toggles</div></a>
</div>
