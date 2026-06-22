<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>Domains</h2>
<p style="color:#64748b;margin-bottom:20px">Manage domains, DNS zones, nameservers, and IP addresses.</p>

<div class="section-grid">
<a href="/admin/domains" class="section-card"><div class="icon">🔗</div><div class="name">Domains</div><div class="desc">Domain registration & management</div></a>
<a href="/admin/dns" class="section-card"><div class="icon">🌐</div><div class="name">DNS Zones</div><div class="desc">DNS zone management</div></a>
<a href="/admin/dns/nameservers" class="section-card"><div class="icon">📡</div><div class="name">Nameservers</div><div class="desc">Nameserver configuration</div></a>
<a href="/admin/ip" class="section-card"><div class="icon">📶</div><div class="name">IP Manager</div><div class="desc">IP address management</div></a>
</div>
