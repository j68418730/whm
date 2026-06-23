<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Domains</h2>
<p style="color:#64748b;margin-bottom:20px">Manage your domains, subdomains, redirects, and DNS.</p>
<div class="section-grid">
<a href="/user/domains" class="section-card"><span class="icon">🌐</span><div class="name">My Domains</div><div class="desc">Domain overview</div></a>
<a href="/user/domains/add" class="section-card"><span class="icon">➕</span><div class="name">Add Domain</div><div class="desc">Register or add domain</div></a>
<a href="/user/subdomains" class="section-card"><span class="icon">🔗</span><div class="name">Subdomains</div><div class="desc">Manage subdomains</div></a>
<a href="/user/redirects" class="section-card"><span class="icon">↪️</span><div class="name">Redirects</div><div class="desc">URL redirection rules</div></a>
<a href="/user/dns" class="section-card"><span class="icon">📡</span><div class="name">DNS Zone</div><div class="desc">Manage DNS records</div></a>
</div>
